<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Cleaner;
use App\Services\Notification\PushNotificationService;
use App\Events\InstantBookingTimeout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InstantBookingService
{
    private PushNotificationService $pushService;
    private int $timeoutSeconds = 60; // 60 seconds timeout

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Process instant booking with recommendations
     */
    public function process(Booking $booking, array $recommendations): array
    {
        if (empty($recommendations)) {
            return $this->handleNoCleanersAvailable($booking);
        }

        DB::transaction(function () use ($booking, $recommendations) {
            $booking->update([
                'status' => 'searching_cleaner',
                'response_timeout_seconds' => $this->timeoutSeconds,
                'timeout_at' => now()->addSeconds($this->timeoutSeconds),
                'ai_recommendations_list' => $recommendations,
                'max_retry_attempts' => count($recommendations),
            ]);
        });

        // Assign to first recommended cleaner
        $firstCleaner = $recommendations[0];
        $assignment = $this->assignToCleaner($booking, $firstCleaner['cleaner_id']);

        // Dispatch timeout watcher
        InstantBookingTimeout::dispatch($booking)->delay(now()->addSeconds($this->timeoutSeconds));

        return [
            'status' => 'cleaner_assigned',
            'cleaner_id' => $firstCleaner['cleaner_id'],
            'cleaner_name' => $firstCleaner['cleaner_name'],
            'eta_minutes' => $firstCleaner['eta_minutes'] ?? 0,
            'timeout_seconds' => $this->timeoutSeconds,
        ];
    }

    /**
     * Assign booking to a specific cleaner
     */
    private function assignToCleaner(Booking $booking, int $cleanerId): bool
    {
        $cleaner = Cleaner::findOrFail($cleanerId);

        // Verify cleaner is still online
        if (!$cleaner->isEligibleForInstantBooking()) {
            Log::warning('Cleaner no longer available for instant booking', [
                'cleaner_id' => $cleanerId,
                'status' => $cleaner->availability_status,
            ]);
            return false;
        }

        DB::transaction(function () use ($booking, $cleanerId) {
            $booking->update([
                'cleaner_id' => $cleanerId,
                'status' => 'cleaner_assigned',
                'cleaner_assigned_at' => now(),
                'ai_rank_position' => 1,
            ]);
        });

        // Send urgent notification
        $this->pushService->sendToCleaner(
            $cleanerId,
            '🚨 URGENT: Instant Booking Request',
            "New cleaning request! Respond within {$this->timeoutSeconds} seconds.\n" .
            "Distance: {$booking->distance_km}km | Earning: TZS " . number_format($booking->cleaner_payout_amount, 2),
            [
                'type' => 'instant_booking',
                'booking_id' => $booking->id,
                'timeout_seconds' => $this->timeoutSeconds,
                'priority' => 'high',
            ]
        );

        return true;
    }

    /**
     * Assign to next available cleaner when timeout occurs
     */
    public function assignNextCleaner(Booking $booking): ?array
    {
        $recommendations = $booking->ai_recommendations_list ?? [];
        $retryCount = $booking->retry_count;

        if ($retryCount >= count($recommendations)) {
            $booking->update(['status' => 'cancelled']);
            return null;
        }

        // Get next cleaner from recommendations
        $nextCleaner = $recommendations[$retryCount] ?? null;
        
        if (!$nextCleaner) {
            $booking->update(['status' => 'cancelled']);
            return null;
        }

        $assigned = $this->assignToCleaner($booking, $nextCleaner['cleaner_id']);
        
        if ($assigned) {
            $booking->increment('retry_count', 1);
            
            InstantBookingTimeout::dispatch($booking)
                ->delay(now()->addSeconds($this->timeoutSeconds));

            return $nextCleaner;
        }

        // Recursive retry if cleaner unavailable
        return $this->assignNextCleaner($booking);
    }

    /**
     * Handle case when no cleaners available
     */
    private function handleNoCleanersAvailable(Booking $booking): array
    {
        $booking->update(['status' => 'cancelled']);
        
        Log::info('No online cleaners available for instant booking', [
            'booking_id' => $booking->id,
            'city_id' => $booking->city_id,
        ]);

        return [
            'status' => 'no_cleaners_available',
            'message' => 'No available cleaners in your area right now. Please try again or schedule a booking.',
        ];
    }

    /**
     * Get live status of instant booking
     */
    public function getStatus(Booking $booking): array
    {
        $status = [
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'timeout_seconds' => $booking->timeout_at 
                ? max(0, now()->diffInSeconds($booking->timeout_at))
                : 0,
            'retry_count' => $booking->retry_count,
        ];

        if ($booking->cleaner && $booking->status === 'cleaner_assigned') {
            $status['cleaner_location'] = [
                'latitude' => $booking->cleaner->current_latitude,
                'longitude' => $booking->cleaner->current_longitude,
            ];
            $status['eta_seconds'] = ($booking->estimated_travel_time_minutes ?? 0) * 60;
        }

        return $status;
    }
}
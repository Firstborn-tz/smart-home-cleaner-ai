<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Cleaner;
use App\Services\Notification\PushNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledBookingService
{
    private PushNotificationService $pushService;
    private int $responseTimeoutHours = 24;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Process scheduled booking with AI recommendations
     */
    public function process(Booking $booking, array $recommendations): array
    {
        if (empty($recommendations)) {
            return $this->handleNoCleanersAvailable($booking);
        }

        DB::transaction(function () use ($booking, $recommendations) {
            $booking->update([
                'status' => 'pending',
                'response_timeout_seconds' => $this->responseTimeoutHours * 3600,
                'timeout_at' => now()->addHours($this->responseTimeoutHours),
                'ai_recommendations_list' => $recommendations,
                'ai_recommendation_score' => $recommendations[0]['score'] ?? 0,
            ]);
        });

        // Notify top 3 recommended cleaners
        $this->notifyTopCleaners($booking, array_slice($recommendations, 0, 3));

        return [
            'status' => 'pending',
            'message' => 'Booking created. Cleaners will respond within ' . $this->responseTimeoutHours . ' hours.',
            'timeout_hours' => $this->responseTimeoutHours,
            'notified_cleaners' => 3,
        ];
    }

    /**
     * Notify top recommended cleaners about scheduled booking
     */
    private function notifyTopCleaners(Booking $booking, array $recommendations): void
    {
        foreach ($recommendations as $rec) {
            try {
                $this->pushService->sendToCleaner(
                    $rec['cleaner_id'],
                    '📅 New Scheduled Booking',
                    "New cleaning request for {$booking->scheduled_at->format('D, M d, Y - h:i A')}. " .
                    "Distance: {$rec['distance_km']}km | Earning: TZS " . number_format($booking->cleaner_payout_amount, 2),
                    [
                        'type' => 'scheduled_booking',
                        'booking_id' => $booking->id,
                        'scheduled_at' => $booking->scheduled_at,
                        'priority' => 'normal',
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to notify cleaner for scheduled booking', [
                    'cleaner_id' => $rec['cleaner_id'],
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Accept scheduled booking assignment
     */
    public function acceptBooking(Booking $booking, Cleaner $cleaner): bool
    {
        if ($booking->status !== 'pending') {
            return false;
        }

        DB::transaction(function () use ($booking, $cleaner) {
            $booking->update([
                'cleaner_id' => $cleaner->id,
                'status' => 'cleaner_accepted',
                'cleaner_accepted_at' => now(),
                'accepted_cleaner_id' => $cleaner->id,
            ]);

            // Notify homeowner
            $this->pushService->sendToHomeowner(
                $booking->homeowner_id,
                '✅ Cleaner Assigned!',
                "{$cleaner->user->full_name} has accepted your scheduled booking for {$booking->scheduled_at->format('D, M d - h:i A')}.",
                ['type' => 'cleaner_assigned', 'booking_id' => $booking->id]
            );
        });

        return true;
    }

    /**
     * Send reminders for upcoming scheduled bookings
     */
    public function sendReminders(): void
    {
        $reminderWindows = [24, 1]; // Hours before scheduled time

        foreach ($reminderWindows as $hoursBefore) {
            $targetTime = now()->addHours($hoursBefore);
            $windowStart = $targetTime->copy()->subMinutes(5);
            $windowEnd = $targetTime->copy()->addMinutes(5);

            $upcomingBookings = Booking::where('booking_type', 'scheduled')
                ->where('status', 'cleaner_accepted')
                ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
                ->with(['cleaner.user', 'homeowner.user'])
                ->get();

            foreach ($upcomingBookings as $booking) {
                // Remind cleaner
                $this->pushService->sendToCleaner(
                    $booking->cleaner_id,
                    '⏰ Upcoming Booking Reminder',
                    "You have a cleaning service in {$hoursBefore} hour(s) at {$booking->service_address}.",
                    ['type' => 'booking_reminder', 'booking_id' => $booking->id]
                );

                // Remind homeowner
                $this->pushService->sendToHomeowner(
                    $booking->homeowner_id,
                    '🔔 Booking Reminder',
                    "Your cleaner {$booking->cleaner->user->full_name} will arrive in {$hoursBefore} hour(s) for your {$booking->service->name}.",
                    ['type' => 'booking_reminder', 'booking_id' => $booking->id]
                );
            }
        }

        Log::info('Scheduled booking reminders sent', [
            'reminders_sent' => $upcomingBookings->count() ?? 0,
        ]);
    }

    /**
     * Handle expired scheduled bookings
     */
    public function handleExpiredBookings(): void
    {
        $expiredBookings = Booking::where('booking_type', 'scheduled')
            ->where('status', 'pending')
            ->where('scheduled_at', '<', now()->subHours(2))
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'expired']);
            
            $this->pushService->sendToHomeowner(
                $booking->homeowner_id,
                '⚠️ Booking Expired',
                "No cleaner accepted your booking for {$booking->scheduled_at->format('D, M d')}. Please try again.",
                ['type' => 'booking_expired', 'booking_id' => $booking->id]
            );
        }

        Log::info('Expired scheduled bookings processed', [
            'count' => $expiredBookings->count(),
        ]);
    }

    private function handleNoCleanersAvailable(Booking $booking): array
    {
        $booking->update(['status' => 'cancelled']);

        return [
            'status' => 'no_cleaners_available',
            'message' => 'No cleaners available for your scheduled time. Please try a different time slot.',
        ];
    }
}
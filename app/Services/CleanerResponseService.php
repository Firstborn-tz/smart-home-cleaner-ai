<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Cleaner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanerResponseService
{
    /**
     * Cleaner accepts a booking request
     */
    public function acceptRequest(Booking $booking, Cleaner $cleaner): array
    {
        return DB::transaction(function () use ($booking, $cleaner) {
            // Lock the booking row to prevent race conditions
            $lockedBooking = Booking::where('id', $booking->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$lockedBooking) {
                return ['success' => false, 'message' => 'Booking is no longer available'];
            }

            // Assign this cleaner
            $lockedBooking->update([
                'cleaner_id' => $cleaner->id,
                'status' => 'cleaner_assigned',
                'cleaner_accepted_at' => now(),
                'cleaner_responded_at' => now(),
                'response_timeout_seconds' => null,
                'timeout_at' => null,
            ]);

            // Cancel all other pending requests from this homeowner for this service
            Booking::where('homeowner_id', $lockedBooking->homeowner_id)
                ->where('service_id', $lockedBooking->service_id)
                ->where('status', 'pending')
                ->where('id', '!=', $lockedBooking->id)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_by' => 'system',
                    'cancellation_reason' => 'another_cleaner_accepted',
                ]);

            // Update cleaner stats
            $cleaner->update([
                'last_active_at' => now(),
                'consecutive_rejections' => 0,
            ]);
            $this->recalculateResponseRates($cleaner);

            Log::info('Cleaner accepted booking', [
                'booking_id' => $lockedBooking->id,
                'cleaner_id' => $cleaner->id,
            ]);

            return ['success' => true, 'message' => 'Booking accepted', 'booking' => $lockedBooking->fresh()];
        });
    }

    /**
     * Cleaner declines a booking request
     */
    public function declineRequest(Booking $booking, Cleaner $cleaner, string $reason = null): array
    {
        if ($booking->status !== 'pending') {
            return ['success' => false, 'message' => 'Booking is no longer available'];
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_by' => 'cleaner',
            'cancellation_reason' => $reason ?? 'Declined by cleaner',
        ]);

        $this->applyPenalty($cleaner, 10);

        Log::info('Cleaner declined booking', [
            'booking_id' => $booking->id,
            'cleaner_id' => $cleaner->id,
            'reason' => $reason,
        ]);

        return ['success' => true, 'message' => 'Booking declined'];
    }

    /**
     * Handle timeout
     */
    public function handleTimeout(Booking $booking): array
    {
        if ($booking->status !== 'pending') {
            return ['success' => false, 'message' => 'Booking is not pending'];
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancellation_reason' => 'cleaner_timeout',
        ]);

        if ($booking->cleaner_id) {
            $cleaner = Cleaner::find($booking->cleaner_id);
            if ($cleaner) {
                $this->applyPenalty($cleaner, 15);
            }
        }

        return ['success' => true, 'message' => 'Booking timed out'];
    }

    /**
     * Cleaner cancels after accepting
     */
    public function cancelAfterAccept(Booking $booking, string $reason = null): array
    {
        if (!in_array($booking->status, ['cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route'])) {
            return ['success' => false, 'message' => 'Booking cannot be cancelled at this stage'];
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_by' => 'cleaner',
            'cancellation_reason' => $reason ?? 'Cancelled by cleaner',
        ]);

        if ($booking->cleaner_id) {
            $cleaner = Cleaner::find($booking->cleaner_id);
            if ($cleaner) {
                $this->applyPenalty($cleaner, 25);
            }
        }

        return ['success' => true, 'message' => 'Booking cancelled'];
    }

    /**
     * Mark cleaner as no-show
     */
    public function markNoShow(Booking $booking): array
    {
        $booking->update([
            'status' => 'no_show',
            'cancelled_by' => 'system',
            'cancellation_reason' => 'cleaner_no_show',
        ]);

        if ($booking->cleaner_id) {
            $cleaner = Cleaner::find($booking->cleaner_id);
            if ($cleaner) {
                $this->applyPenalty($cleaner, 40);
                $cleaner->update(['availability_status' => 'offline']);
            }
        }

        return ['success' => true, 'message' => 'Cleaner marked as no-show'];
    }

    /**
     * Homeowner cancels the booking
     */
    public function homeownerCancel(Booking $booking): array
    {
        $fiveMinAfterAccept = $booking->cleaner_accepted_at 
            ? $booking->cleaner_accepted_at->addMinutes(5) 
            : null;

        $cancellationFee = 0;
        if ($fiveMinAfterAccept && now()->gt($fiveMinAfterAccept)) {
            $cancellationFee = 5000;
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_by' => 'homeowner',
            'cancellation_reason' => 'Cancelled by homeowner',
            'cancellation_fee' => $cancellationFee,
        ]);

        return ['success' => true, 'message' => 'Booking cancelled', 'fee' => $cancellationFee];
    }

    /**
     * Apply penalty to cleaner
     */
    private function applyPenalty(Cleaner $cleaner, int $points): void
    {
        $cleaner->update([
            'availability_penalty' => min(40, ($cleaner->availability_penalty ?? 0) + $points),
            'consecutive_rejections' => ($cleaner->consecutive_rejections ?? 0) + 1,
            'last_active_at' => now(),
        ]);

        $this->recalculateResponseRates($cleaner);
    }

    /**
     * Recalculate acceptance/rejection rates
     */
    private function recalculateResponseRates(Cleaner $cleaner): void
    {
        $total = Booking::where('cleaner_id', $cleaner->id)
            ->whereIn('status', ['completed', 'cancelled', 'cleaner_assigned', 'cleaner_accepted', 'no_show'])
            ->count();

        if ($total > 0) {
            $accepted = Booking::where('cleaner_id', $cleaner->id)
                ->whereIn('status', ['completed', 'cleaner_assigned', 'cleaner_accepted'])
                ->count();

            $rejected = Booking::where('cleaner_id', $cleaner->id)
                ->whereIn('status', ['cancelled', 'no_show'])
                ->where('cancelled_by', 'cleaner')
                ->count();

            $cleaner->update([
                'acceptance_rate' => round(($accepted / max($total, 1)) * 100, 2),
                'rejection_rate' => round(($rejected / max($total, 1)) * 100, 2),
            ]);
        }
    }

    /**
     * Get cleaner's effective AI penalty
     */
    public function getEffectivePenalty(Cleaner $cleaner): int
    {
        return $cleaner->availability_penalty ?? 0;
    }

    /**
     * Check if cleaner should be suspended
     */
    public function shouldSuspend(Cleaner $cleaner): bool
    {
        return $cleaner->consecutive_rejections >= 5 
            || $cleaner->availability_penalty >= 40;
    }
}
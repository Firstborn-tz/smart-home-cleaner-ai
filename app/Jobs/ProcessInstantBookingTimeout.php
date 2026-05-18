<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\Booking\InstantBookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInstantBookingTimeout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;

    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function handle(InstantBookingService $instantService): void
    {
        $booking = Booking::find($this->bookingId);

        if (!$booking) {
            Log::warning('Booking not found for timeout processing', [
                'booking_id' => $this->bookingId,
            ]);
            return;
        }

        // Only process if still in assigned status
        if ($booking->status !== 'cleaner_assigned') {
            Log::debug('Booking already processed, skipping timeout', [
                'booking_id' => $this->bookingId,
                'status' => $booking->status,
            ]);
            return;
        }

        Log::info('Processing instant booking timeout', [
            'booking_id' => $this->bookingId,
            'retry_count' => $booking->retry_count,
        ]);

        // Try next cleaner
        $result = $instantService->assignNextCleaner($booking);

        if (!$result) {
            // No more cleaners available
            $booking->update(['status' => 'cancelled']);
            
            Log::warning('No more cleaners available for instant booking', [
                'booking_id' => $this->bookingId,
                'max_retries_reached' => true,
            ]);
        }
    }
}
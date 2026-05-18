<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\Notification\PushNotificationService;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = 'Send reminders for upcoming bookings';

    private PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle(): int
    {
        $this->info('Checking for upcoming bookings...');

        // Remind 1 hour before
        $this->sendRemindersForWindow(60, '1 hour');
        
        // Remind 30 minutes before for instant bookings
        $this->sendRemindersForWindow(30, '30 minutes');

        $this->info('Reminders sent successfully.');
        return 0;
    }

    private function sendRemindersForWindow(int $minutesBefore, string $label): void
    {
        $targetTime = now()->addMinutes($minutesBefore);
        $windowStart = $targetTime->copy()->subMinutes(2);
        $windowEnd = $targetTime->copy()->addMinutes(2);

        $bookings = Booking::whereIn('status', ['cleaner_accepted', 'in_progress'])
            ->where('scheduled_at', '>=', $windowStart)
            ->where('scheduled_at', '<=', $windowEnd)
            ->with(['cleaner.user', 'homeowner.user'])
            ->get();

        foreach ($bookings as $booking) {
            $this->pushService->sendToCleaner(
                $booking->cleaner_id,
                "⏰ Booking in {$label}",
                "Reminder: You have a booking in {$label} at {$booking->service_address}",
                ['type' => 'reminder', 'booking_id' => $booking->id]
            );

            $this->info("Sent {$label} reminder for booking {$booking->booking_number}");
        }
    }
}
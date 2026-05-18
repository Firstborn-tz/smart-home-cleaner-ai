<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Booking\ScheduledBookingService;

class ProcessScheduledBookings extends Command
{
    protected $signature = 'bookings:process-scheduled';
    protected $description = 'Process scheduled bookings - send reminders and handle expired ones';

    private ScheduledBookingService $scheduledService;

    public function __construct(ScheduledBookingService $scheduledService)
    {
        parent::__construct();
        $this->scheduledService = $scheduledService;
    }

    public function handle(): int
    {
        $this->info('Processing scheduled bookings...');

        // Send reminders for upcoming bookings
        $this->info('Sending reminders...');
        $this->scheduledService->sendReminders();

        // Handle expired bookings
        $this->info('Checking for expired bookings...');
        $this->scheduledService->handleExpiredBookings();

        $this->info('Scheduled bookings processed successfully.');
        return 0;
    }
}
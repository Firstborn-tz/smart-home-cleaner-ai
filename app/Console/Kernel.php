<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // AI Model Training - Daily at 2 AM
        $schedule->command('ai:train')
            ->dailyAt('02:00')
            ->runInBackground()
            ->withoutOverlapping(3600)
            ->onFailure(function () {
                \Log::channel('ai')->error('Scheduled AI training failed');
            });

        // Process pending scheduled bookings - Every 5 minutes
        $schedule->command('bookings:process-scheduled')
            ->everyFiveMinutes()
            ->withoutOverlapping(300);

        // Send booking reminders - Every minute
        $schedule->command('bookings:send-reminders')
            ->everyMinute()
            ->withoutOverlapping(60);

        // Update cleaner availability (auto offline if inactive) - Every 10 minutes
        $schedule->command('cleaners:update-availability')
            ->everyTenMinutes()
            ->withoutOverlapping(600);

        // Clean expired verification codes - Hourly
        $schedule->command('verification:clean-expired')
            ->hourly();

        // Calculate daily commissions - Daily at midnight
        $schedule->command('commissions:calculate-daily')
            ->dailyAt('00:30')
            ->withoutOverlapping(1800);

        // Generate daily reports - Daily at 1 AM
        $schedule->command('reports:generate-daily')
            ->dailyAt('01:00')
            ->withoutOverlapping(3600);

        // Backup database - Daily at 3 AM
        $schedule->command('backup:run --only-db')
            ->dailyAt('03:00')
            ->onFailure(function () {
                \Log::channel('backup')->error('Database backup failed');
            });

        // Clean old logs - Weekly
        $schedule->command('logs:clean --days=30')
            ->weekly()
            ->sundays()
            ->at('04:00');

        // Prune old telescope entries - Daily
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            $schedule->command('telescope:prune --hours=72')->daily();
        }

        // Horizon metrics snapshot - Every 5 minutes
        if (class_exists(\Laravel\Horizon\Horizon::class)) {
            $schedule->command('horizon:snapshot')->everyFiveMinutes();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     */
    protected function scheduleTimezone(): string
    {
        return 'Dodoma';
    }
}
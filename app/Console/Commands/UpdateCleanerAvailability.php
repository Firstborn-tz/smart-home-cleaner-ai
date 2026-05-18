<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cleaner;
use Illuminate\Support\Facades\Log;

class UpdateCleanerAvailability extends Command
{
    protected $signature = 'cleaners:update-availability {--timeout=10 : Minutes of inactivity before auto-offline}';
    protected $description = 'Auto-set cleaners to offline if inactive for specified time';

    public function handle()
    {
        $timeoutMinutes = (int) $this->option('timeout');
        $cutoffTime = now()->subMinutes($timeoutMinutes);

        $this->info("Checking cleaner activity (timeout: {$timeoutMinutes} minutes)...");

        // Find cleaners who are online but haven't updated location recently
        $inactiveCleaners = Cleaner::whereIn('availability_status', ['online', 'online_busy'])
            ->where(function ($query) use ($cutoffTime) {
                $query->where('updated_at', '<', $cutoffTime)
                    ->orWhereNull('current_latitude')
                    ->orWhereNull('current_longitude');
            })
            ->get();

        $count = 0;
        foreach ($inactiveCleaners as $cleaner) {
            $cleaner->update([
                'availability_status' => 'offline',
                'location_sharing_enabled' => false,
                'current_latitude' => null,
                'current_longitude' => null,
            ]);

            $count++;
            
            Log::channel('cleaner')->info('Auto-set cleaner offline due to inactivity', [
                'cleaner_id' => $cleaner->id,
                'last_active' => $cleaner->updated_at->diffForHumans(),
            ]);
        }

        $this->info("Auto-set {$count} cleaners to offline.");
        Log::channel('cleaner')->info("Availability auto-update completed", [
            'inactive_count' => $count,
            'timeout_minutes' => $timeoutMinutes,
        ]);

        return 0;
    }
}
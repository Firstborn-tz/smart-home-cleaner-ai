<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Cleaner;
use App\Models\Commission;
use App\Models\City;
use Illuminate\Support\Facades\Log;

class GenerateDailyReports extends Command
{
    protected $signature = 'reports:generate-daily';
    protected $description = 'Generate daily platform reports';

    public function handle(): int
    {
        $this->info('Generating daily reports...');

        $yesterday = now()->subDay();
        
        $report = [
            'date' => $yesterday->format('Y-m-d'),
            'generated_at' => now()->toISOString(),
            
            'bookings' => [
                'total' => Booking::whereDate('created_at', $yesterday)->count(),
                'instant' => Booking::whereDate('created_at', $yesterday)->where('booking_type', 'instant')->count(),
                'scheduled' => Booking::whereDate('created_at', $yesterday)->where('booking_type', 'scheduled')->count(),
                'completed' => Booking::whereDate('completed_at', $yesterday)->where('status', 'completed')->count(),
                'cancelled' => Booking::whereDate('created_at', $yesterday)->where('status', 'cancelled')->count(),
                'completion_rate' => $this->calculateCompletionRate($yesterday),
            ],
            
            'revenue' => [
                'total_revenue' => Booking::whereDate('created_at', $yesterday)
                    ->whereIn('status', ['completed', 'in_progress'])
                    ->sum('total_amount'),
                'total_commission' => Booking::whereDate('created_at', $yesterday)
                    ->whereIn('status', ['completed', 'in_progress'])
                    ->sum('commission_amount'),
                'instant_fees' => Booking::whereDate('created_at', $yesterday)
                    ->where('booking_type', 'instant')
                    ->sum('instant_booking_fee'),
            ],
            
            'cleaners' => [
                'total_active' => Cleaner::where('is_verified', true)->count(),
                'online_at_peak' => Cleaner::where('availability_status', 'online')->count(),
                'new_registrations' => Cleaner::whereDate('created_at', $yesterday)->count(),
            ],
            
            'commissions' => [
                'pending' => Commission::where('payment_status', 'pending')->count(),
                'collected' => Commission::whereDate('last_payment_at', $yesterday)->sum('actual_submitted_amount'),
            ],
            
            'cities' => $this->getCityStats($yesterday),
        ];

        // Store report (could be in database or file)
        Log::channel('reports')->info('Daily report generated', $report);

        $this->info("Daily report for {$yesterday->format('Y-m-d')} generated successfully.");
        $this->info("Total Revenue: TZS " . number_format($report['revenue']['total_revenue']));
        $this->info("Total Bookings: {$report['bookings']['total']}");
        $this->info("Online Cleaners: {$report['cleaners']['online_at_peak']}");

        return 0;
    }

    private function calculateCompletionRate($date): float
    {
        $total = Booking::whereDate('created_at', $date)->count();
        $completed = Booking::whereDate('completed_at', $date)
            ->where('status', 'completed')
            ->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    private function getCityStats($date): array
    {
        return City::where('is_active', true)
            ->get()
            ->map(function ($city) use ($date) {
                return [
                    'city' => $city->name,
                    'bookings' => Booking::whereDate('created_at', $date)
                        ->where('city_id', $city->id)
                        ->count(),
                    'revenue' => Booking::whereDate('created_at', $date)
                        ->where('city_id', $city->id)
                        ->whereIn('status', ['completed', 'in_progress'])
                        ->sum('commission_amount'),
                ];
            })
            ->toArray();
    }
}
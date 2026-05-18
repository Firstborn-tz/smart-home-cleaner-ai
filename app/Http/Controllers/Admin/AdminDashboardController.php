<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cleaner;
use App\Models\Booking;
use App\Models\City;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|super_admin']);
    }

    public function index()
    {
        return view('admin.dashboard', [
            'stats' => $this->getDashboardStats(),
            'cityPerformance' => $this->getCityPerformance(),
            'liveCleanerStatus' => $this->getLiveCleanerStatus(),
            'revenueData' => $this->getRevenueChartData(),
            'topCleaners' => $this->getTopCleaners(),
            'recentBookings' => $this->getRecentBookings(),
        ]);
    }

    private function getDashboardStats(): array
    {
        return Cache::remember('admin:dashboard:stats', 60, function () {
            return [
                'total_cleaners' => Cleaner::count(),
                'online_cleaners' => Cleaner::where('availability_status', 'online')->count(),
                'busy_cleaners' => Cleaner::where('availability_status', 'online_busy')->count(),
                'offline_cleaners' => Cleaner::where('availability_status', 'offline')->count(),
                'scheduled_only' => Cleaner::where('availability_status', 'scheduled_only')->count(),
                'verified_cleaners' => Cleaner::where('is_verified', true)->count(),
                'unverified_cleaners' => Cleaner::where('is_verified', false)->count(),
                'today_bookings' => Booking::whereDate('created_at', today())->count(),
                'today_instant' => Booking::whereDate('created_at', today())->where('booking_type', 'instant')->count(),
                'today_scheduled' => Booking::whereDate('created_at', today())->where('booking_type', 'scheduled')->count(),
                'today_revenue' => Booking::whereDate('created_at', today())
                    ->whereIn('status', ['completed', 'in_progress'])
                    ->sum('commission_amount'),
                'monthly_revenue' => Booking::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('commission_amount'),
                'pending_commissions' => Commission::where('payment_status', 'pending')->count(),
                'total_cities' => City::where('is_active', true)->count(),
            ];
        });
    }

    private function getLiveCleanerStatus(): array
    {
        return Cleaner::with('user:id,first_name,last_name')
            ->whereNotNull('current_latitude')
            ->where('location_sharing_enabled', true)
            ->get()
            ->map(function ($cleaner) {
                return [
                    'id' => $cleaner->id,
                    'name' => $cleaner->user->full_name,
                    'cleaner_id' => $cleaner->cleaner_id,
                    'status' => $cleaner->availability_status,
                    'latitude' => $cleaner->current_latitude,
                    'longitude' => $cleaner->current_longitude,
                    'rating' => $cleaner->rating,
                    'completed_jobs' => $cleaner->total_completed_jobs,
                    'last_seen' => $cleaner->updated_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    private function getCityPerformance(): array
    {
        return City::withCount(['cleaners', 'bookings'])
            ->withSum(['bookings' => function ($q) {
                $q->whereMonth('created_at', now()->month)->whereIn('status', ['completed', 'in_progress']);
            }], 'commission_amount')
            ->orderByDesc('bookings_sum_commission_amount')
            ->get()
            ->toArray();
    }

    private function getRevenueChartData(): array
    {
        $days = collect(range(29, 0))->map(function ($day) {
            $date = now()->subDays($day);
            
            return [
                'date' => $date->format('Y-m-d'),
                'revenue' => Booking::whereDate('created_at', $date)
                    ->whereIn('status', ['completed', 'in_progress'])
                    ->sum('commission_amount'),
                'bookings' => Booking::whereDate('created_at', $date)->count(),
            ];
        });

        return $days->toArray();
    }

    private function getTopCleaners(): array
    {
        return Cleaner::with('user:id,first_name,last_name')
            ->orderByDesc('rating')
            ->orderByDesc('total_completed_jobs')
            ->limit(10)
            ->get()
            ->map(function ($cleaner) {
                return [
                    'id' => $cleaner->id,
                    'name' => $cleaner->user->full_name,
                    'cleaner_id' => $cleaner->cleaner_id,
                    'rating' => $cleaner->rating,
                    'completed_jobs' => $cleaner->total_completed_jobs,
                    'completion_rate' => $cleaner->completion_rate,
                    'total_earnings' => $cleaner->total_earnings,
                ];
            })
            ->toArray();
    }

    private function getRecentBookings(): array
    {
        return Booking::with(['service:id,name', 'cleaner.user:id,first_name,last_name', 'homeowner.user:id,first_name,last_name'])
            ->latest()
            ->limit(20)
            ->get()
            ->toArray();
    }
}
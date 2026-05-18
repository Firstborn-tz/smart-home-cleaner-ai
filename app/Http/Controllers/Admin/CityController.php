<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    /**
     * List all cities with statistics
     */
    public function index()
    {
        $cities = City::withCount(['cleaners', 'bookings'])
            ->withSum(['bookings' => function ($q) {
                $q->whereMonth('created_at', now()->month);
            }], 'commission_amount')
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'total' => City::count(),
            'active' => City::where('is_active', true)->count(),
            'inactive' => City::where('is_active', false)->count(),
            'total_cleaners' => \App\Models\Cleaner::count(),
            'total_bookings' => \App\Models\Booking::count(),
        ];

        return view('admin.cities.index', compact('cities', 'stats'));
    }

    /**
     * Show city details with statistics
     */
    public function show(City $city)
    {
        $city->loadCount(['cleaners', 'bookings']);
        
        $monthlyStats = $city->bookings()
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_bookings,
                SUM(total_amount) as total_revenue,
                SUM(commission_amount) as total_commission,
                AVG(total_amount) as avg_booking_value
            ')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $cleanerStats = [
            'total' => $city->cleaners()->count(),
            'online' => $city->cleaners()->where('availability_status', 'online')->count(),
            'verified' => $city->cleaners()->where('is_verified', true)->count(),
            'avg_rating' => $city->cleaners()->avg('rating') ?? 0,
        ];

        return view('admin.cities.show', compact('city', 'monthlyStats', 'cleanerStats'));
    }

    /**
     * Toggle city active status
     */
    public function toggleStatus(City $city)
    {
        $city->update(['is_active' => !$city->is_active]);

        Cache::flush();

        return response()->json([
            'success' => true,
            'is_active' => $city->is_active,
            'message' => $city->is_active ? 'City activated successfully' : 'City deactivated successfully',
        ]);
    }

    /**
     * Update city pricing and settings
     */
    public function updatePricing(Request $request, City $city)
    {
        $request->validate([
            'service_radius_km' => 'required|integer|min:5|max:100',
            'instant_booking_fee_percentage' => 'required|numeric|min:0|max:50',
            'traffic_multiplier' => 'required|numeric|min:0.5|max:3.0',
            'morning_peak' => 'nullable|numeric|min:0.5|max:3.0',
            'evening_peak' => 'nullable|numeric|min:0.5|max:3.0',
        ]);

        $peakHours = [
            'morning' => $request->morning_peak ?? 1.2,
            'evening' => $request->evening_peak ?? 1.4,
        ];

        $city->update([
            'service_radius_km' => $request->service_radius_km,
            'instant_booking_fee_percentage' => $request->instant_booking_fee_percentage,
            'traffic_multiplier' => $request->traffic_multiplier,
            'peak_hours_multiplier' => json_encode($peakHours),
        ]);

        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'City settings updated successfully',
            'city' => $city->fresh(),
        ]);
    }

    /**
     * Get city statistics for dashboard
     */
    public function stats(City $city)
    {
        $todayBookings = $city->bookings()->whereDate('created_at', today())->count();
        $monthlyRevenue = $city->bookings()
            ->whereMonth('created_at', now()->month)
            ->sum('commission_amount');
        $onlineCleaners = $city->cleaners()->where('availability_status', 'online')->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'today_bookings' => $todayBookings,
                'monthly_revenue' => $monthlyRevenue,
                'online_cleaners' => $onlineCleaners,
                'total_cleaners' => $city->cleaners()->count(),
            ]
        ]);
    }

    /**
     * Update city sort order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:cities,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $order) {
            City::where('id', $order['id'])->update(['sort_order' => $order['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'City order updated',
        ]);
    }
}
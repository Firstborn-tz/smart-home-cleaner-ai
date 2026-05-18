<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cleaner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $homeowner = Auth::user()->homeowner;
        
        $stats = [
            'total_bookings' => Booking::where('homeowner_id', $homeowner->id)->count(),
            'completed_bookings' => Booking::where('homeowner_id', $homeowner->id)
                ->where('status', 'completed')->count(),
            'active_bookings' => Booking::where('homeowner_id', $homeowner->id)
                ->whereIn('status', ['cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route', 'in_progress'])
                ->count(),
            'total_spent' => Booking::where('homeowner_id', $homeowner->id)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'average_rating_given' => Booking::where('homeowner_id', $homeowner->id)
                ->whereNotNull('cleaner_rating_given')
                ->avg('cleaner_rating_given') ?? 0,
        ];

        $recentBookings = Booking::with(['service', 'cleaner.user'])
            ->where('homeowner_id', $homeowner->id)
            ->latest()
            ->limit(10)
            ->get();

        $favoriteCleaners = [];
        if ($homeowner->favorite_cleaners) {
            $favoriteCleaners = Cleaner::with('user')
                ->whereIn('id', $homeowner->favorite_cleaners)
                ->get();
        }

        $upcomingBookings = Booking::with(['service', 'cleaner.user'])
            ->where('homeowner_id', $homeowner->id)
            ->where('booking_type', 'scheduled')
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->get();

        $activeBooking = Booking::with(['service', 'cleaner.user'])
            ->where('homeowner_id', $homeowner->id)
            ->whereIn('status', ['cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route', 'in_progress'])
            ->first();

        return view('homeowner.dashboard', compact(
            'stats',
            'recentBookings',
            'favoriteCleaners',
            'upcomingBookings',
            'activeBooking'
        ));
    }

    /**
     * Get active booking tracking data
     */
    public function trackingData(Request $request)
    {
        $homeowner = Auth::user()->homeowner;

        $activeBooking = Booking::with(['cleaner.user', 'service'])
            ->where('homeowner_id', $homeowner->id)
            ->whereIn('status', ['cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route', 'in_progress'])
            ->first();

        if (!$activeBooking) {
            return response()->json([
                'success' => false,
                'message' => 'No active booking',
            ]);
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $activeBooking->id,
                'booking_number' => $activeBooking->booking_number,
                'status' => $activeBooking->status,
                'service_name' => $activeBooking->service->name,
                'cleaner_name' => $activeBooking->cleaner?->user?->full_name,
                'cleaner_rating' => $activeBooking->cleaner?->rating,
                'cleaner_photo' => $activeBooking->cleaner?->user?->avatar_url,
                'eta_minutes' => $activeBooking->estimated_travel_time_minutes,
                'distance_km' => $activeBooking->distance_km,
                'cleaner_location' => $activeBooking->cleaner ? [
                    'latitude' => $activeBooking->cleaner->current_latitude,
                    'longitude' => $activeBooking->cleaner->current_longitude,
                ] : null,
                'verification_code' => null, // Never expose verification code
            ],
        ]);
    }

    /**
     * Toggle favorite cleaner
     */
    public function toggleFavorite(Request $request, Cleaner $cleaner)
    {
        $homeowner = Auth::user()->homeowner;
        
        $favorites = $homeowner->favorite_cleaners ?? [];
        
        if (in_array($cleaner->id, $favorites)) {
            $favorites = array_values(array_diff($favorites, [$cleaner->id]));
            $message = 'Cleaner removed from favorites';
        } else {
            $favorites[] = $cleaner->id;
            $message = 'Cleaner added to favorites';
        }
        
        $homeowner->update([
            'favorite_cleaners' => $favorites,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorite' => in_array($cleaner->id, $favorites),
        ]);
    }
}
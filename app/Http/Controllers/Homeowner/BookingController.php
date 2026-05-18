<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\AI\XGBoostRecommendationService;
use App\Services\Location\GoogleMapsService;

class BookingController extends Controller
{
    protected XGBoostXGBoostRecommendationService $aiService;
    protected GoogleMapsService $mapsService;

    public function __construct(XGBoostXGBoostRecommendationService $aiService, GoogleMapsService $mapsService)
    {
        $this->aiService = $aiService;
        $this->mapsService = $mapsService;
    }

    public function create()
    {
        $services = Service::where('is_active', true)->get();
        $cities = City::where('is_active', true)->get();
        $homeowner = Auth::user()->homeowner;
        
        return view('homeowner.bookings.create', compact('services', 'cities', 'homeowner'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'city_id' => 'nullable|exists:cities,id',
            'booking_type' => 'required|in:instant,scheduled',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'scheduled_at' => 'nullable|date|after:now',
            'special_instructions' => 'nullable|string|max:1000',
            'cleaner_id' => 'nullable|exists:cleaners,id',
            'distance_km' => 'nullable|numeric',
            'eta_minutes' => 'nullable|numeric',
            'ai_score' => 'nullable|numeric',
            'ai_rank' => 'nullable|integer',
            'cleaner_price' => 'nullable|numeric',
        ]);

        $homeowner = Auth::user()->homeowner;
        if (!$homeowner) {
            return response()->json(['success' => false, 'message' => 'Homeowner profile not found'], 404);
        }

        $service = Service::findOrFail($request->service_id);
        
        $cityId = $request->city_id ?? City::where('is_active', true)->first()->id ?? 1;
        $city = City::findOrFail($cityId);

        $instantFee = $request->booking_type === 'instant' ? $service->instant_booking_premium : 0;
        $cleanerPrice = $request->cleaner_price ?? $service->base_price;
        $totalAmount = round(($cleanerPrice + $instantFee) * $city->traffic_multiplier, 2);
        $commissionRate = $city->instant_booking_fee_percentage ?? 15;
        $commissionAmount = round($totalAmount * ($commissionRate / 100), 2);
        $cleanerPayout = $totalAmount - $commissionAmount;

        $booking = Booking::create([
            'booking_number' => 'BKG-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd'),
            'uuid' => (string) Str::uuid(),
            'booking_type' => $request->booking_type,
            'status' => $request->cleaner_id ? 'cleaner_assigned' : 'pending',
            'homeowner_id' => $homeowner->id,
            'cleaner_id' => $request->cleaner_id,
            'service_id' => $service->id,
            'city_id' => $city->id,
            'service_latitude' => $request->latitude,
            'service_longitude' => $request->longitude,
            'service_address' => $request->address,
            'district' => $request->district,
            'ward' => $request->ward,
            'street' => $request->street,
            'service_base_price' => $service->base_price,
            'cleaner_price' => $cleanerPrice,
            'instant_booking_fee' => $instantFee,
            'total_amount' => $totalAmount,
            'commission_percentage' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'cleaner_payout_amount' => $cleanerPayout,
            'special_instructions' => $request->special_instructions,
            'scheduled_at' => $request->scheduled_at,
            'ai_recommendation_score' => $request->ai_score,
            'ai_recommendation_rank' => $request->ai_rank,
            'estimated_distance_km' => $request->distance_km,
            'estimated_eta_minutes' => $request->eta_minutes,
            'response_timeout_seconds' => $request->booking_type === 'instant' ? 60 : 86400,
        ]);

        if ($request->cleaner_id) {
            $booking->update(['cleaner_assigned_at' => now()]);
        }

        $homeowner->increment('total_bookings');

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully!',
            'booking' => $booking->load('service', 'cleaner.user', 'city'),
        ]);
    }

    public function show($id)
    {
        $booking = Booking::with(['service', 'cleaner.user', 'city'])
            ->where('homeowner_id', Auth::user()->homeowner->id)
            ->findOrFail($id);

        return view('homeowner.bookings.show', compact('booking'));
    }

    public function getRecommendations(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'booking_type' => 'required|in:instant,scheduled',
            'city_id' => 'nullable|exists:cities,id',
        ]);

        try {
            $service = Service::findOrFail($request->service_id);
            $homeowner = Auth::user()->homeowner;

            if (!$homeowner) {
                return response()->json(['success' => false, 'message' => 'Homeowner profile not found'], 404);
            }

            // Build temporary booking for AI service
            $tempBooking = new Booking();
            $tempBooking->service_id = $service->id;
            $tempBooking->service_latitude = $request->latitude;
            $tempBooking->service_longitude = $request->longitude;
            $tempBooking->booking_type = $request->booking_type;
            $tempBooking->service_price = $service->base_price;
            $tempBooking->total_amount = $service->base_price;
            $tempBooking->homeowner = $homeowner;

            // Get AI recommendations (real AI + Google Maps road distance)
            $result = $this->aiService->recommendCleaners($tempBooking, 5);
            
            return response()->json([
                'success' => true,
                'recommendations' => $result['recommendations'] ?? [],
                'ai_status' => $result['ai_status'] ?? $this->aiService->getServiceStatus(),
                'total_available' => count($result['recommendations'] ?? []),
                'service_name' => $service->name,
                'service_price' => $service->base_price,
            ]);

        } catch (\Exception $e) {
            \Log::error('getRecommendations error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations',
                'recommendations' => [],
                'total_available' => 0,
            ], 500);
        }
    }
}



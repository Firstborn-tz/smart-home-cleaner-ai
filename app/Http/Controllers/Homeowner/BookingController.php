<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\City;
use App\Models\Cleaner;
use App\Services\CleanerResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\AI\XGBoostRecommendationService;
use App\Services\Location\GoogleMapsService;

class BookingController extends Controller
{
    protected XGBoostRecommendationService $aiService;
    protected GoogleMapsService $mapsService;
    protected CleanerResponseService $responseService;

    public function __construct(
        XGBoostRecommendationService $aiService,
        GoogleMapsService $mapsService,
        CleanerResponseService $responseService
    ) {
        $this->aiService = $aiService;
        $this->mapsService = $mapsService;
        $this->responseService = $responseService;
    }

    public function create()
    {
        $services = Service::where('is_active', true)->get();
        $cities = City::where('is_active', true)->get();
        $homeowner = Auth::user()->homeowner;

        $activeRequests = Booking::where('homeowner_id', $homeowner->id)
            ->whereIn('status', ['pending', 'cleaner_assigned', 'cleaner_accepted'])
            ->count();

        return view('homeowner.bookings.create', compact('services', 'cities', 'homeowner', 'activeRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'city_id' => 'nullable|exists:cities,id',
            'booking_type' => 'required|in:instant,scheduled',
            'pricing_model' => 'required|in:fixed,payg',
            'booked_hours' => 'required_if:pricing_model,fixed|numeric|min:1|max:8',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'scheduled_at' => 'nullable|date|after:now',
            'special_instructions' => 'nullable|string|max:1000',
            'cleaner_id' => 'required|exists:cleaners,id',
            'hourly_rate' => 'required|numeric|min:0',
            'distance_km' => 'nullable|numeric',
            'eta_minutes' => 'nullable|numeric',
            'ai_score' => 'nullable|numeric',
            'ai_rank' => 'nullable|integer',
        ]);

        $homeowner = Auth::user()->homeowner;
        if (!$homeowner) {
            return response()->json(['success' => false, 'message' => 'Homeowner profile not found'], 404);
        }

        // Check max 3 active requests
        $activeCount = Booking::where('homeowner_id', $homeowner->id)
            ->where('status', 'pending')
            ->count();

        if ($activeCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'You have 3 active requests. Cancel one before sending more.'
            ], 422);
        }

        // Check not already requested this cleaner for this service
        $existingRequest = Booking::where('homeowner_id', $homeowner->id)
            ->where('cleaner_id', $request->cleaner_id)
            ->where('service_id', $request->service_id)
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending request to this cleaner.'
            ], 422);
        }

        $service = Service::findOrFail($request->service_id);
        $cityId = $request->city_id ?? City::where('is_active', true)->first()->id ?? 1;
        $city = City::findOrFail($cityId);

        // Calculate timeout: 10 min instant, 30 min scheduled
        $timeoutSeconds = $request->booking_type === 'instant' ? 600 : 1800;
        
        // Grace window: ETA + 15 minutes
        $graceWindow = $request->eta_minutes ? round($request->eta_minutes + 15) : 25;

        // Calculate estimated price
        $estimatedTotal = 0;
        if ($request->pricing_model === 'fixed' && $request->booked_hours) {
            $estimatedTotal = round($request->booked_hours * $request->hourly_rate);
        }

        $booking = Booking::create([
            'booking_number' => 'BKG-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd'),
            'uuid' => (string) Str::uuid(),
            'booking_type' => $request->booking_type,
            'pricing_model' => $request->pricing_model,
            'booked_hours' => $request->booked_hours,
            'hourly_rate' => $request->hourly_rate,
            'status' => 'pending',
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
            'service_base_price' => 0,
            'total_amount' => $estimatedTotal,
            'final_amount' => 0,
            'commission_percentage' => 0,
            'commission_amount' => 0,
            'cleaner_payout_amount' => 0,
            'actual_hours' => 0,
            'billed_hours' => 0,
            'special_instructions' => $request->special_instructions,
            'scheduled_at' => $request->scheduled_at,
            'response_timeout_seconds' => $timeoutSeconds,
            'timeout_at' => now()->addSeconds($timeoutSeconds),
            'distance_km' => $request->distance_km,
            'estimated_travel_time_minutes' => $request->eta_minutes,
            'grace_window_minutes' => $graceWindow,
            'ai_recommendation_score' => $request->ai_score,
            'ai_rank_position' => $request->ai_rank,
            'attempted_cleaners' => [$request->cleaner_id],
            'attempt_count' => 1,
            'cleaner_notified_at' => now(),
        ]);

        $homeowner->increment('total_bookings');

        return response()->json([
            'success' => true,
            'message' => 'Request sent! Waiting for cleaner response (' . ($request->booking_type === 'instant' ? '2 min' : '30 min') . ' timeout).',
            'booking' => $booking->load('service', 'cleaner.user', 'city'),
            'timeout_seconds' => $timeoutSeconds,
            'estimated_total' => $estimatedTotal,
        ]);
    }

    public function show($id)
    {
        $booking = Booking::with(['service', 'cleaner.user', 'city'])
            ->where('homeowner_id', Auth::user()->homeowner->id)
            ->findOrFail($id);

        return view('homeowner.bookings.show', compact('booking'));
    }

    /**
     * Get AI-powered cleaner recommendations (exactly 5)
     */
    public function getRecommendations(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'booking_type' => 'required|in:instant,scheduled',
            'pricing_model' => 'required|in:fixed,payg',
            'booked_hours' => 'required_if:pricing_model,fixed|numeric|min:1|max:8',
            'city_id' => 'nullable|exists:cities,id',
        ]);

        try {
            $service = Service::findOrFail($request->service_id);
            $homeowner = Auth::user()->homeowner;

            if (!$homeowner) {
                return response()->json(['success' => false, 'message' => 'Homeowner profile not found'], 404);
            }

            $tempBooking = new Booking();
            $tempBooking->service_id = $service->id;
            $tempBooking->service_latitude = $request->latitude;
            $tempBooking->service_longitude = $request->longitude;
            $tempBooking->booking_type = $request->booking_type;
            $tempBooking->service_price = 0;
            $tempBooking->total_amount = 0;
            $tempBooking->homeowner = $homeowner;

            // AI returns exactly 5
            $result = $this->aiService->recommendCleaners($tempBooking, 5);

            // Enrich with pricing based on pricing model
            $recommendations = collect($result['recommendations'] ?? [])->map(function ($rec) use ($request) {
                $cleaner = Cleaner::find($rec['cleaner_id']);
                if ($cleaner) {
                    $customPrices = $cleaner->custom_prices;
                    if (is_string($customPrices)) {
                        $customPrices = json_decode($customPrices, true) ?? [];
                    }

                    $hourlyRate = is_array($customPrices) && isset($customPrices[$request->service_id])
                        ? (float) $customPrices[$request->service_id]
                        : 0;

                    $rec['hourly_rate'] = $hourlyRate;

                    if ($request->pricing_model === 'fixed' && $request->booked_hours) {
                        $rec['total_price'] = round($hourlyRate * $request->booked_hours);
                    } else {
                        $rec['total_price'] = $hourlyRate;
                    }
                }
                return $rec;
            })->filter(function ($rec) {
                return ($rec['hourly_rate'] ?? 0) > 0;
            })->values()->toArray();

            // Ensure exactly 5
            $recommendations = array_slice($recommendations, 0, 5);

            return response()->json([
                'success' => true,
                'recommendations' => $recommendations,
                'ai_status' => $result['ai_status'] ?? $this->aiService->getServiceStatus(),
                'total_available' => count($recommendations),
                'service_name' => $service->name,
                'pricing_model' => $request->pricing_model,
                'booked_hours' => $request->booked_hours,
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

    /**
     * Get homeowner's active requests
     */
    public function activeRequests()
    {
        $homeowner = Auth::user()->homeowner;

        $requests = Booking::with(['service', 'cleaner.user'])
            ->where('homeowner_id', $homeowner->id)
            ->whereIn('status', ['pending'])
            ->where('timeout_at', '>', now())
            ->latest()
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'cleaner_name' => $booking->cleaner->user->full_name ?? 'Unknown',
                    'service_name' => $booking->service->name ?? 'Unknown',
                    'status' => $booking->status,
                    'pricing_model' => $booking->pricing_model,
                    'hourly_rate' => $booking->hourly_rate,
                    'booked_hours' => $booking->booked_hours,
                    'time_left_seconds' => $booking->timeout_at ? max(0, now()->diffInSeconds($booking->timeout_at, false)) : 0,
                    'created_at' => $booking->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'requests' => $requests,
            'count' => $requests->count(),
            'max_allowed' => 3,
        ]);
    }

    /**
     * Cancel a pending request
     */
    public function cancelRequest($id)
    {
        $homeowner = Auth::user()->homeowner;
        $booking = Booking::where('homeowner_id', $homeowner->id)
            ->whereIn('status', ['pending'])
            ->findOrFail($id);

        $result = $this->responseService->homeownerCancel($booking);

        return response()->json($result);
    }
}
<?php

namespace App\Services\AI;

use App\Models\Booking;
use App\Models\Cleaner;
use App\Services\Location\GoogleMapsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class XGBoostRecommendationService
{
    private string $aiServiceUrl;
    private FeatureCalculatorService $featureCalculator;
    private GoogleMapsService $mapsService;
    private bool $aiServiceAvailable = false;
    private string $aiServiceStatus = 'unknown';

    public function __construct(FeatureCalculatorService $featureCalculator, GoogleMapsService $mapsService)
    {
        $this->aiServiceUrl = config('services.ai.url', 'http://127.0.0.1:8001');
        $this->featureCalculator = $featureCalculator;
        $this->mapsService = $mapsService;
        $this->checkAIService();
    }

    public function checkAIService(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->aiServiceUrl}/health");
            if ($response->successful()) {
                $data = $response->json();
                $this->aiServiceAvailable = $data['model_loaded'] ?? false;
                $this->aiServiceStatus = $this->aiServiceAvailable ? 'active' : 'model_not_loaded';
                return $this->aiServiceAvailable;
            }
        } catch (\Exception $e) {
            Log::warning('AI Service unavailable', ['error' => $e->getMessage()]);
        }
        $this->aiServiceAvailable = false;
        $this->aiServiceStatus = 'unavailable';
        return false;
    }

    public function getServiceStatus(): array
    {
        return [
            'available' => $this->aiServiceAvailable,
            'status' => $this->aiServiceStatus,
            'url' => $this->aiServiceUrl,
            'model' => $this->aiServiceAvailable ? 'XGBoost (24 features)' : 'Fallback (Google Maps + 8-Factor)',
            'checked_at' => now()->toISOString(),
        ];
    }

    public function recommendCleaners(Booking $booking, int $limit = 5): array
    {
        $this->checkAIService();
        
        $cacheKey = "ai_recs_v6:" . md5(serialize([
            'lat' => $booking->service_latitude,
            'lng' => $booking->service_longitude,
            'service_id' => $booking->service_id,
            'booking_type' => $booking->booking_type,
        ]));

        return Cache::remember($cacheKey, 30, function () use ($booking, $limit) {
            try {
                $eligibleCleaners = $this->getEligibleCleaners($booking);
                
                if ($eligibleCleaners->isEmpty()) {
                    Log::info('No eligible cleaners found');
                    return [
                        'recommendations' => [],
                        'ai_status' => $this->getServiceStatus(),
                        'total_available' => 0,
                    ];
                }

                // Try XGBoost AI first, fall back to 8-factor if unavailable
                if ($this->aiServiceAvailable) {
                    $recommendations = $this->xgboostPredictions($eligibleCleaners, $booking, $limit);
                } else {
                    $recommendations = $this->fallbackRecommendations($eligibleCleaners, $booking, $limit);
                }
                
                // Ensure exactly 5 (or fewer if not enough eligible)
                $recommendations = array_slice($recommendations, 0, 5);
                
                Log::info('Recommendations generated', [
                    'cleaners_found' => $eligibleCleaners->count(),
                    'returned' => count($recommendations),
                    'used_xgboost' => $this->aiServiceAvailable,
                ]);
                
                return [
                    'recommendations' => $recommendations,
                    'ai_status' => $this->getServiceStatus(),
                    'total_available' => count($recommendations),
                ];

            } catch (\Exception $e) {
                Log::error('AI recommendation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [
                    'recommendations' => [],
                    'ai_status' => [
                        'available' => false,
                        'status' => 'error',
                        'error' => $e->getMessage(),
                    ],
                    'total_available' => 0,
                ];
            }
        });
    }

    private function getEligibleCleaners(Booking $booking)
    {
        $cleaners = Cleaner::with('user')
            ->where('availability_status', 'online')
            ->where('is_verified', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();
        
        Log::info('Searching cleaners (Haversine pre-filter + Google Maps)', [
            'total_online' => $cleaners->count(),
            'homeowner_lat' => (float) $booking->service_latitude,
            'homeowner_lng' => (float) $booking->service_longitude,
        ]);
        
        $eligible = collect();
        $preFilterMaxKm = 50;
        
        foreach ($cleaners as $cleaner) {
            $straightDistance = $this->haversineDistance(
                (float) $booking->service_latitude,
                (float) $booking->service_longitude,
                (float) $cleaner->current_latitude,
                (float) $cleaner->current_longitude
            );
            
            $maxRadius = $cleaner->max_service_radius_km ?? 30;
            
            if ($straightDistance > $preFilterMaxKm) {
                continue;
            }
            
            $mapsData = $this->mapsService->getDistanceAndETA(
                (float) $booking->service_latitude,
                (float) $booking->service_longitude,
                (float) $cleaner->current_latitude,
                (float) $cleaner->current_longitude
            );
            
            $distance = $mapsData['distance_km'] ?? $straightDistance * 1.4;
            
            if ($distance <= $maxRadius) {
                $cleaner->temp_distance = round($distance, 2);
                $cleaner->temp_travel_time = $mapsData['duration_minutes'] ?? round(($distance / 35) * 60);
                $cleaner->temp_traffic_delay = $mapsData['traffic_delay_minutes'] ?? 0;
                $cleaner->temp_duration_in_traffic = $mapsData['duration_in_traffic_minutes'] ?? $cleaner->temp_travel_time;
                $cleaner->temp_distance_source = $mapsData['source'] ?? 'haversine_estimate';
                $eligible->push($cleaner);
            }
        }

        $eligible = $eligible->sortBy('temp_distance')->values();
        
        Log::info('Eligible cleaners: ' . $eligible->count());
        foreach ($eligible as $c) {
            Log::info("  - {$c->user->full_name}: {$c->temp_distance}km [{$c->temp_distance_source}]");
        }
        
        return $eligible;
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Real XGBoost AI predictions via Python microservice
     */
    private function xgboostPredictions($eligibleCleaners, Booking $booking, int $limit): array
    {
        try {
            $cleanersWithData = [];
            foreach ($eligibleCleaners as $cleaner) {
                $cleanersWithData[] = [
                    'cleaner' => $cleaner,
                    'distance_km' => $cleaner->temp_distance,
                    'duration_minutes' => $cleaner->temp_travel_time,
                    'traffic_delay_minutes' => $cleaner->temp_traffic_delay,
                ];
            }
            
            $features = $this->featureCalculator->calculateFeatures($cleanersWithData, $booking);
            
            $cleanersPayload = [];
            foreach ($features as $i => $f) {
                $cleaner = $eligibleCleaners[$i] ?? null;
                if (!$cleaner) continue;
                
                $cleanersPayload[] = [
                    'cleaner_id' => $cleaner->id,
                    'cleaner_name' => $cleaner->user->full_name ?? 'Unknown',
                    'cleaner_rating' => $f['cleaner_rating'] ?? 0,
                    'total_completed_jobs' => $f['total_completed_jobs'] ?? 0,
                    'experience_days_active' => $f['experience_days_active'] ?? 0,
                    'avg_response_time_seconds' => $f['avg_response_time_seconds'] ?? 0,
                    'completion_rate' => $f['completion_rate'] ?? 0,
                    'cancellation_rate' => $f['cancellation_rate'] ?? 0,
                    'complaints_count' => $f['complaints_count'] ?? 0,
                    'profile_completion_score' => $f['profile_completion_score'] ?? 0,
                    'price_competitiveness' => $f['price_competitiveness'] ?? 0,
                    'skills_match_score' => $f['skills_match_score'] ?? 0,
                    'real_distance_km' => $f['real_distance_km'] ?? 0,
                    'traffic_delay_minutes' => $f['traffic_delay_minutes'] ?? 0,
                    'travel_time_minutes' => $f['travel_time_minutes'] ?? 0,
                    'service_area_match' => $f['service_area_match'] ?? 0,
                    'route_quality_score' => $f['route_quality_score'] ?? 0,
                    'booking_urgency_hours' => $f['booking_urgency_hours'] ?? 0,
                    'is_instant_booking' => $f['is_instant_booking'] ?? 0,
                    'service_price' => $f['service_price'] ?? 0,
                    'homeowner_rating' => $f['homeowner_rating'] ?? 0,
                    'time_of_day' => $f['time_of_day'] ?? 0,
                    'cleaner_success_rate' => $f['cleaner_success_rate'] ?? 0,
                    'repeat_customer_rate' => $f['repeat_customer_rate'] ?? 0,
                    'avg_job_duration_minutes' => $f['avg_job_duration_minutes'] ?? 0,
                    'last_booking_days_ago' => $f['last_booking_days_ago'] ?? 0,
                ];
            }
            
            $response = Http::timeout(15)
                ->post("{$this->aiServiceUrl}/predict", [
                    'cleaners' => $cleanersPayload,
                    'booking_type' => $booking->booking_type,
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $predictions = $data['predictions'] ?? [];
                
                Log::info('XGBoost AI predictions received', [
                    'count' => count($predictions),
                    'inference_ms' => $data['inference_time_ms'] ?? 0,
                ]);
                
                return collect($predictions)
                    ->take($limit)
                    ->map(function ($pred, $index) use ($eligibleCleaners, $booking) {
                        $cleaner = $eligibleCleaners->firstWhere('id', $pred['cleaner_id']);
                        if (!$cleaner) return null;
                        
                        $servicePrice = 0;
                        $customPrices = $cleaner->custom_prices;
                        if ($customPrices) {
                            if (is_string($customPrices)) {
                                $customPrices = json_decode($customPrices, true) ?? [];
                            }
                            if (is_array($customPrices) && isset($customPrices[$booking->service_id])) {
                                $servicePrice = (float) $customPrices[$booking->service_id];
                            }
                        }
                        if ($servicePrice <= 0) {
                            $servicePrice = (float) ($booking->service_price ?? $booking->total_amount ?? 0);
                        }
                        
                        // Apply availability penalty to AI score
                        $penalty = $cleaner->availability_penalty ?? 0;
                        $adjustedScore = max(0, round((float) ($pred['score'] ?? 0), 1) - $penalty);
                        
                        return [
                            'rank' => $index + 1,
                            'cleaner_id' => $cleaner->id,
                            'cleaner_name' => $cleaner->user->full_name ?? 'Unknown',
                            'cleaner_id_number' => $cleaner->cleaner_id,
                            'rating' => round((float) $cleaner->rating, 1),
                            'completed_jobs' => (int) $cleaner->total_completed_jobs,
                            'completion_rate' => round((float) $cleaner->completion_rate, 1),
                            'cancellation_rate' => round((float) $cleaner->cancellation_rate, 1),
                            'experience_days' => (int) $cleaner->experience_days_active,
                            'distance_km' => $cleaner->temp_distance,
                            'driving_time_minutes' => round($cleaner->temp_travel_time),
                            'traffic_delay_minutes' => round($cleaner->temp_traffic_delay, 1),
                            'eta_minutes' => round($cleaner->temp_duration_in_traffic),
                            'ai_score' => $adjustedScore,
                            'confidence' => round((float) ($pred['confidence'] ?? 0.85), 2),
                            'is_fallback' => false,
                            'service_price' => $servicePrice,
                            'distance_source' => $cleaner->temp_distance_source ?? 'google_maps',
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }
            
            Log::warning('XGBoost predict returned non-200', ['status' => $response->status()]);
            
        } catch (\Exception $e) {
            Log::error('XGBoost prediction failed: ' . $e->getMessage());
        }
        
        Log::info('Falling back to 8-factor scoring');
        return $this->fallbackRecommendations($eligibleCleaners, $booking, $limit);
    }

    /**
     * 8-Factor Google Maps Enhanced Scoring (Fallback)
     */
    private function fallbackRecommendations($eligibleCleaners, Booking $booking, int $limit): array
    {
        if ($eligibleCleaners->isEmpty()) {
            return [];
        }
        
        return $eligibleCleaners->map(function ($cleaner, $index) use ($booking) {
            $distance = $cleaner->temp_distance ?? 0;
            $travelTime = $cleaner->temp_travel_time ?? 0;
            $trafficDelay = $cleaner->temp_traffic_delay ?? 0;
            $etaMinutes = round($cleaner->temp_duration_in_traffic ?? ($travelTime + $trafficDelay));
            
            $score = 0;
            $score += ($cleaner->rating / 5) * 20;
            $maxDistance = 30;
            $score += max(0, (1 - min($distance, $maxDistance) / $maxDistance) * 20);
            if ($trafficDelay <= 5) $score += 15;
            elseif ($trafficDelay <= 10) $score += 12;
            elseif ($trafficDelay <= 15) $score += 8;
            elseif ($trafficDelay <= 20) $score += 4;
            $score += ($cleaner->completion_rate / 100) * 15;
            $score += min(10, ($cleaner->experience_days_active / 365) * 10);
            $score += min(10, ($cleaner->total_completed_jobs / 100) * 10);
            $avgResponse = $cleaner->avg_response_time_seconds ?? 300;
            $score += max(0, 5 - ($avgResponse / 120) * 5);
            $score += ($cleaner->profile_completion_score / 100) * 5;
            
            $currentHour = (int) now()->format('H');
            if (in_array($currentHour, [7,8,9,16,17,18]) && $trafficDelay > 10) {
                $score -= 5;
            }
            
            // Apply availability penalty
            $penalty = $cleaner->availability_penalty ?? 0;
            $score = round(max(0, min(100, $score - $penalty)), 1);
            
            $servicePrice = 0;
            $customPrices = $cleaner->custom_prices;
            if ($customPrices) {
                if (is_string($customPrices)) {
                    $customPrices = json_decode($customPrices, true) ?? [];
                }
                if (is_array($customPrices) && isset($customPrices[$booking->service_id])) {
                    $servicePrice = (float) $customPrices[$booking->service_id];
                }
            }
            if ($servicePrice <= 0) {
                $servicePrice = (float) ($booking->service_price ?? $booking->total_amount ?? 0);
            }
            
            return [
                'rank' => $index + 1,
                'cleaner_id' => $cleaner->id,
                'cleaner_name' => $cleaner->user->full_name ?? 'Unknown',
                'cleaner_id_number' => $cleaner->cleaner_id,
                'rating' => round((float) $cleaner->rating, 1),
                'completed_jobs' => (int) $cleaner->total_completed_jobs,
                'completion_rate' => round((float) $cleaner->completion_rate, 1),
                'cancellation_rate' => round((float) $cleaner->cancellation_rate, 1),
                'experience_days' => (int) $cleaner->experience_days_active,
                'distance_km' => round($distance, 2),
                'driving_time_minutes' => round($travelTime),
                'traffic_delay_minutes' => round($trafficDelay, 1),
                'eta_minutes' => $etaMinutes,
                'ai_score' => $score,
                'confidence' => 0.75,
                'is_fallback' => true,
                'service_price' => $servicePrice,
                'distance_source' => $cleaner->temp_distance_source ?? 'google_maps',
            ];
        })
        ->sortByDesc('ai_score')
        ->take($limit)
        ->values()
        ->toArray();
    }
}
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
            'model' => $this->aiServiceAvailable ? 'XGBoost (24 features)' : 'Fallback (Google Maps + 7-Factor)',
            'checked_at' => now()->toISOString(),
        ];
    }

    public function recommendCleaners(Booking $booking, int $limit = 5): array
    {
        $this->checkAIService();
        
        $cacheKey = "ai_recs_v3:" . md5(serialize([
            'lat' => $booking->service_latitude,
            'lng' => $booking->service_longitude,
            'service_id' => $booking->service_id,
            'booking_type' => $booking->booking_type,
        ]));

        return Cache::remember($cacheKey, 30, function () use ($booking, $limit) {
            try {
                // Get eligible cleaners with Google Maps distance
                $eligibleCleaners = $this->getEligibleCleaners($booking);
                
                if ($eligibleCleaners->isEmpty()) {
                    Log::info('No eligible cleaners found');
                    return [
                        'recommendations' => [],
                        'ai_status' => $this->getServiceStatus(),
                    ];
                }

                // Generate recommendations
                $recommendations = $this->fallbackRecommendations($eligibleCleaners, $limit);
                
                Log::info('Recommendations generated', [
                    'cleaners_found' => $eligibleCleaners->count(),
                    'returned' => count($recommendations),
                ]);
                
                return [
                    'recommendations' => $recommendations,
                    'ai_status' => $this->getServiceStatus(),
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
                ];
            }
        });
    }

    /**
     * Get eligible cleaners using Google Maps real road distance
     * Falls back to Haversine if Google Maps fails
     */
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
    $preFilterMaxKm = 50; // Only call Google Maps for cleaners within 50km straight-line
    
    foreach ($cleaners as $cleaner) {
        // Step 1: Quick Haversine pre-filter (instant, no API call)
        $straightDistance = $this->haversineDistance(
            (float) $booking->service_latitude,
            (float) $booking->service_longitude,
            (float) $cleaner->current_latitude,
            (float) $cleaner->current_longitude
        );
        
        $maxRadius = $cleaner->max_service_radius_km ?? 30;
        
        // Skip cleaners clearly too far away
        if ($straightDistance > $preFilterMaxKm) {
            Log::info("  Skipping {$cleaner->user->full_name}: {$straightDistance}km straight-line (too far)");
            continue;
        }
        
        // Step 2: Get Google Maps real road distance (only for nearby cleaners)
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

/**
 * Quick Haversine distance calculation (no API call)
 */
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
     * 8-Factor Google Maps Enhanced Scoring
     */
    private function fallbackRecommendations($eligibleCleaners, int $limit): array
    {
        if ($eligibleCleaners->isEmpty()) {
            return [];
        }
        
        return $eligibleCleaners->map(function ($cleaner, $index) {
            $distance = $cleaner->temp_distance ?? 0;
            $travelTime = $cleaner->temp_travel_time ?? 0;
            $trafficDelay = $cleaner->temp_traffic_delay ?? 0;
            $etaMinutes = round($cleaner->temp_duration_in_traffic ?? ($travelTime + $trafficDelay));
            
            // ===== 8-FACTOR SCORING WITH GOOGLE MAPS DATA =====
            $score = 0;
            
            // 1. Rating (20 points)
            $score += ($cleaner->rating / 5) * 20;
            
            // 2. Real Road Distance from Google Maps (20 points)
            $maxDistance = 30;
            $score += max(0, (1 - min($distance, $maxDistance) / $maxDistance) * 20);
            
            // 3. Traffic Impact from Google Maps (15 points)
            if ($trafficDelay <= 5) $score += 15;
            elseif ($trafficDelay <= 10) $score += 12;
            elseif ($trafficDelay <= 15) $score += 8;
            elseif ($trafficDelay <= 20) $score += 4;
            else $score += 0;
            
            // 4. Completion Rate (15 points)
            $score += ($cleaner->completion_rate / 100) * 15;
            
            // 5. Experience (10 points)
            $score += min(10, ($cleaner->experience_days_active / 365) * 10);
            
            // 6. Total Jobs (10 points)
            $score += min(10, ($cleaner->total_completed_jobs / 100) * 10);
            
            // 7. Response Time (5 points)
            $avgResponse = $cleaner->avg_response_time_seconds ?? 300;
            $score += max(0, 5 - ($avgResponse / 120) * 5);
            
            // 8. Profile Completion (5 points)
            $score += ($cleaner->profile_completion_score / 100) * 5;
            
            // Rush hour penalty
            $currentHour = (int) now()->format('H');
            if (in_array($currentHour, [7,8,9,16,17,18]) && $trafficDelay > 10) {
                $score -= 5;
            }
            
            // Clamp and round
            $score = round(max(0, min(100, $score)), 1);
            
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
                'confidence' => 0.85,
                'is_fallback' => !$this->aiServiceAvailable,
                'distance_source' => $cleaner->temp_distance_source ?? 'google_maps',
            ];
        })
        ->sortByDesc('ai_score')
        ->take($limit)
        ->values()
        ->toArray();
    }
}

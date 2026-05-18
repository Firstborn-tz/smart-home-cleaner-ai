<?php

namespace App\Services\AI;

use App\Models\Cleaner;
use App\Models\Booking;
use App\Models\City;
use App\Services\Location\GoogleMapsService;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    private GoogleMapsService $mapsService;

    public function __construct(GoogleMapsService $mapsService)
    {
        $this->mapsService = $mapsService;
    }

    /**
     * Get top 5 recommended cleaners with real Google Maps data
     */
    public function recommendCleaners(Booking $booking, int $limit = 5): array
    {
        $city = City::find($booking->city_id);
        $maxRadius = $city->service_radius_km ?? 30;

        // Get available online cleaners in the city
        $cleaners = Cleaner::with('user')
            ->where('city_id', $booking->city_id)
            ->where('availability_status', 'online')
            ->where('is_verified', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();

        if ($cleaners->isEmpty()) {
            return [];
        }

        $recommendations = [];

        foreach ($cleaners as $cleaner) {
            // Get REAL road distance from Google Maps
            $distanceData = $this->mapsService->getDistanceMatrix(
                $cleaner->current_latitude,
                $cleaner->current_longitude,
                $booking->service_latitude,
                $booking->service_longitude
            );

            if (!$distanceData) {
                // Fallback to Haversine if Google Maps fails
                $distanceData = $this->getFallbackDistance(
                    $cleaner->current_latitude,
                    $cleaner->current_longitude,
                    $booking->service_latitude,
                    $booking->service_longitude,
                    $city->traffic_multiplier
                );
            }

            $distanceKm = $distanceData['distance_km'];
            $durationMinutes = $distanceData['duration_minutes'];
            $durationInTrafficMinutes = $distanceData['duration_in_traffic_minutes'] ?? $durationMinutes;
            $trafficDelayMinutes = $distanceData['traffic_delay_minutes'] ?? 0;

            // Skip if outside service radius
            if ($distanceKm > $maxRadius) {
                continue;
            }

            // Calculate AI recommendation score
            $score = $this->calculateScore($cleaner, $distanceKm, $durationInTrafficMinutes, $trafficDelayMinutes);

            // Get ETA range (best case to worst case)
            $etaBest = round($durationMinutes * 0.9); // Best case: 10% faster
            $etaWorst = round($durationMinutes * 1.3); // Worst case: 30% slower

            $recommendations[] = [
                'cleaner_id' => $cleaner->id,
                'cleaner_name' => $cleaner->user->full_name,
                'cleaner_id_number' => $cleaner->cleaner_id,
                'rating' => round($cleaner->rating, 1),
                'total_jobs' => $cleaner->total_completed_jobs,
                'completion_rate' => round($cleaner->completion_rate, 1),
                'cancellation_rate' => round($cleaner->cancellation_rate, 1),
                'avg_response_time' => round($cleaner->avg_response_time_seconds / 60, 1),
                'repeat_customer_rate' => round($cleaner->repeat_customer_rate, 1),
                
                // Real Google Maps Data
                'distance_km' => $distanceKm,
                'distance_text' => $distanceData['distance_text'] ?? $distanceKm . ' km',
                'driving_time_minutes' => $durationMinutes,
                'driving_time_text' => $distanceData['duration_text'] ?? $durationMinutes . ' mins',
                'eta_with_traffic_minutes' => $durationInTrafficMinutes,
                'eta_with_traffic_text' => $distanceData['duration_in_traffic_text'] ?? $durationInTrafficMinutes . ' mins',
                'traffic_delay_minutes' => $trafficDelayMinutes,
                'eta_range' => "{$etaBest}-{$etaWorst} mins",
                'has_traffic' => $trafficDelayMinutes > 5,
                'traffic_level' => $trafficDelayMinutes > 15 ? 'Heavy' : ($trafficDelayMinutes > 5 ? 'Moderate' : 'Light'),
                
                // AI Score
                'ai_score' => round($score, 1),
                'confidence' => $this->calculateConfidence($cleaner, $distanceKm),
                
                // Score Breakdown
                'score_breakdown' => $this->getScoreBreakdown($cleaner, $distanceKm, $durationInTrafficMinutes, $trafficDelayMinutes),
                
                // Cleaner Badges
                'badges' => $this->getCleanerBadges($cleaner, $distanceKm, $durationInTrafficMinutes),
                
                // Experience Level
                'experience_level' => $cleaner->total_completed_jobs > 50 ? 'Expert' : ($cleaner->total_completed_jobs > 20 ? 'Experienced' : 'Beginner'),
            ];
        }

        // Sort by AI score (highest first)
        usort($recommendations, function ($a, $b) {
            return $b['ai_score'] <=> $a['ai_score'];
        });

        // Add ranking
        foreach ($recommendations as $index => &$rec) {
            $rec['rank'] = $index + 1;
        }

        return array_values(array_slice($recommendations, 0, $limit));
    }

    /**
     * Calculate AI recommendation score
     */
    private function calculateScore(Cleaner $cleaner, float $distanceKm, int $etaMinutes, int $trafficDelay): float
    {
        $score = 0;

        // 1. Rating (30% weight) - max 30 points
        $score += $cleaner->rating * 6;

        // 2. Distance (25% weight) - max 25 points
        $score += max(0, 25 - ($distanceKm * 0.8));

        // 3. ETA (15% weight) - max 15 points
        $score += $etaMinutes <= 15 ? 15 : ($etaMinutes <= 30 ? 12 : ($etaMinutes <= 45 ? 8 : ($etaMinutes <= 60 ? 5 : 2)));

        // 4. Traffic Penalty (5% weight) - max 5 points
        $score += $trafficDelay == 0 ? 5 : ($trafficDelay <= 5 ? 4 : ($trafficDelay <= 10 ? 3 : ($trafficDelay <= 15 ? 2 : 1)));

        // 5. Completion Rate (10% weight) - max 10 points
        $score += ($cleaner->completion_rate / 100) * 10;

        // 6. Experience (8% weight) - max 8 points
        $score += min(8, $cleaner->total_completed_jobs * 0.08);

        // 7. Response Time (5% weight) - max 5 points
        $responseMinutes = $cleaner->avg_response_time_seconds / 60;
        $score += max(0, 5 - ($responseMinutes * 0.05));

        // 8. Repeat Customer (2% weight) - max 2 points
        $score += ($cleaner->repeat_customer_rate / 100) * 2;

        return min(100, max(0, round($score, 1)));
    }

    /**
     * Calculate confidence level
     */
    private function calculateConfidence(Cleaner $cleaner, float $distanceKm): float
    {
        $baseConfidence = 0.75;
        
        // Higher confidence for experienced cleaners
        if ($cleaner->total_completed_jobs > 50) $baseConfidence += 0.10;
        elseif ($cleaner->total_completed_jobs > 20) $baseConfidence += 0.05;
        
        // Higher confidence for high-rated cleaners
        if ($cleaner->rating >= 4.5) $baseConfidence += 0.08;
        elseif ($cleaner->rating >= 4.0) $baseConfidence += 0.04;
        
        // Slightly lower confidence for long distances
        if ($distanceKm > 20) $baseConfidence -= 0.05;
        elseif ($distanceKm > 10) $baseConfidence -= 0.02;
        
        return min(1.0, max(0.5, round($baseConfidence, 2)));
    }

    /**
     * Get score breakdown
     */
    private function getScoreBreakdown(Cleaner $cleaner, float $distance, int $eta, int $traffic): array
    {
        return [
            'Rating (' . $cleaner->rating . '/5.0)' => round($cleaner->rating * 6, 1) . '/30',
            'Distance (' . $distance . ' km)' => round(max(0, 25 - ($distance * 0.8)), 1) . '/25',
            'ETA (' . $eta . ' mins)' => round($eta <= 15 ? 15 : ($eta <= 30 ? 12 : ($eta <= 45 ? 8 : ($eta <= 60 ? 5 : 2))), 1) . '/15',
            'Traffic (' . $traffic . ' min delay)' => round($traffic == 0 ? 5 : ($traffic <= 5 ? 4 : ($traffic <= 10 ? 3 : ($traffic <= 15 ? 2 : 1))), 1) . '/5',
            'Completion Rate (' . $cleaner->completion_rate . '%)' => round(($cleaner->completion_rate / 100) * 10, 1) . '/10',
            'Experience (' . $cleaner->total_completed_jobs . ' jobs)' => round(min(8, $cleaner->total_completed_jobs * 0.08), 1) . '/8',
        ];
    }

    /**
     * Get cleaner badges
     */
    private function getCleanerBadges(Cleaner $cleaner, float $distanceKm, int $etaMinutes): array
    {
        $badges = [];
        
        if ($cleaner->rating >= 4.8) $badges[] = '🏆 Top Rated';
        elseif ($cleaner->rating >= 4.5) $badges[] = '⭐ Highly Rated';
        
        if ($distanceKm <= 5) $badges[] = '📍 Very Close';
        elseif ($distanceKm <= 10) $badges[] = '📍 Nearby';
        
        if ($etaMinutes <= 15) $badges[] = '⚡ Fast Arrival';
        elseif ($etaMinutes <= 30) $badges[] = '🕐 Quick ETA';
        
        if ($cleaner->total_completed_jobs > 100) $badges[] = '💪 100+ Jobs';
        elseif ($cleaner->total_completed_jobs > 50) $badges[] = '👍 50+ Jobs';
        
        if ($cleaner->completion_rate >= 98) $badges[] = '✅ Reliable';
        
        return $badges;
    }

    /**
     * Fallback distance calculation using Haversine formula
     */
    private function getFallbackDistance(float $lat1, float $lon1, float $lat2, float $lon2, float $trafficMultiplier): array
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceKm = round($earthRadius * $c, 2);

        // Estimate driving time
        $avgSpeed = 30 / $trafficMultiplier;
        $durationMinutes = round(($distanceKm / $avgSpeed) * 60);
        $trafficDelay = round($durationMinutes * ($trafficMultiplier - 1));
        $durationInTraffic = $durationMinutes + $trafficDelay;

        return [
            'distance_km' => $distanceKm,
            'distance_text' => $distanceKm . ' km',
            'duration_minutes' => $durationMinutes,
            'duration_text' => $durationMinutes . ' mins',
            'duration_in_traffic_minutes' => $durationInTraffic,
            'traffic_delay_minutes' => $trafficDelay,
            'duration_in_traffic_text' => $durationInTraffic . ' mins',
        ];
    }
}
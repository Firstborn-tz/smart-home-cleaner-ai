<?php

namespace App\Services\AI;

use App\Models\Booking;
use App\Models\Cleaner;

class FeatureCalculatorService
{
    /**
     * Calculate all 24 features for XGBoost model
     */
    public function calculateFeatures($cleanersWithDistance, Booking $booking): array
    {
        $featureMatrix = [];

        foreach ($cleanersWithDistance as $data) {
            $cleaner = $data['cleaner'];
            
            $featureMatrix[] = [
                'cleaner_id' => $cleaner->id,
                
                // === Cleaner Features (10) ===
                'cleaner_rating' => $cleaner->rating,
                'total_completed_jobs' => $cleaner->total_completed_jobs,
                'experience_days_active' => $cleaner->experience_days_active,
                'avg_response_time_seconds' => $cleaner->avg_response_time_seconds,
                'completion_rate' => $cleaner->completion_rate,
                'cancellation_rate' => $cleaner->cancellation_rate,
                'complaints_count' => $cleaner->complaints_count,
                'profile_completion_score' => $this->calcProfileCompletion($cleaner),
                'price_competitiveness' => $this->calcPriceCompetitiveness($cleaner, $booking),
                'skills_match_score' => $this->calcSkillsMatch($cleaner, $booking),
                
                // === Location Features (5) ===
                'real_distance_km' => $data['distance_km'],
                'traffic_delay_minutes' => $data['traffic_delay_minutes'] ?? 0,
                'travel_time_minutes' => $data['duration_minutes'],
                'service_area_match' => $this->calcServiceAreaMatch($cleaner, $booking),
                'route_quality_score' => $this->calcRouteQuality($data),
                
                // === Booking Context Features (5) ===
                'booking_urgency_hours' => $this->calcUrgency($booking),
                'is_instant_booking' => $booking->booking_type === 'instant' ? 1 : 0,
                'service_price' => $booking->total_amount,
                'homeowner_rating' => $booking->homeowner->rating ?? 3.0,
                'time_of_day' => (int) now()->format('H'),
                
                // === Historical Performance (4) ===
                'cleaner_success_rate' => $cleaner->success_rate,
                'repeat_customer_rate' => $cleaner->repeat_customer_rate,
                'avg_job_duration_minutes' => $cleaner->avg_job_duration_minutes,
                'last_booking_days_ago' => $this->calcDaysSinceLastBooking($cleaner),
                
                // Metadata
                'distance_km' => $data['distance_km'],
                'duration_minutes' => $data['duration_minutes'],
            ];
        }

        return $featureMatrix;
    }

    private function calcProfileCompletion(Cleaner $cleaner): float
    {
        $fields = [
            'full_address', 'service_skills', 'certifications', 'current_latitude',
            'current_longitude', 'shift_start_time', 'shift_end_time'
        ];
        
        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($cleaner->$field)) {
                $completed++;
            }
        }
        
        return ($completed / count($fields)) * 100;
    }

    private function calcPriceCompetitiveness(Cleaner $cleaner, Booking $booking): float
    {
        // Compare cleaner's average earning per job vs service price
        $avgEarning = $cleaner->total_earnings / max($cleaner->total_completed_jobs, 1);
        $ratio = $booking->total_amount / max($avgEarning, 1);
        
        return min(100, max(0, 100 - abs($ratio - 1) * 50));
    }

    private function calcSkillsMatch(Cleaner $cleaner, Booking $booking): float
    {
        $skills = $cleaner->service_skills ?? [];
        $required = $booking->service->required_skills ?? [];
        
        if (empty($required)) {
            return 80; // General cleaner can handle basic tasks
        }

        $matches = count(array_intersect($skills, $required));
        $total = count($required);
        
        return $total > 0 ? ($matches / $total) * 100 : 100;
    }

    private function calcServiceAreaMatch(Cleaner $cleaner, Booking $booking): float
    {
        $score = 0;
        
        if ($cleaner->district === $booking->district) $score += 40;
        if ($cleaner->ward === $booking->ward) $score += 35;
        if ($cleaner->street === $booking->street) $score += 25;
        
        return $score;
    }

    private function calcRouteQuality(array $data): float
    {
        if ($data['distance_km'] == 0) return 100;
        
        $trafficRatio = ($data['traffic_delay_minutes'] ?? 0) / max($data['duration_minutes'], 1);
        return max(0, min(100, 100 - ($trafficRatio * 100)));
    }

    private function calcUrgency(Booking $booking): float
    {
        if ($booking->booking_type === 'instant') return 0.25; // Very urgent
        
        return $booking->scheduled_at 
            ? max(0.5, now()->diffInHours($booking->scheduled_at))
            : 24;
    }

    private function calcDaysSinceLastBooking(Cleaner $cleaner): int
    {
        if (!$cleaner->last_booking_at) return 365;
        return now()->diffInDays($cleaner->last_booking_at);
    }
}



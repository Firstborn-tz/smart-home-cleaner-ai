<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Cleaner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrainAIModel extends Command
{
    protected $signature = 'ai:train {--force : Force retraining even if minimum samples not met}';
    protected $description = 'Train the XGBoost AI recommendation model';

    public function handle()
    {
        $this->info('Starting AI model training...');

        // Collect training data from completed bookings
        $completedBookings = Booking::where('status', 'completed')
            ->whereNotNull('cleaner_rating_given')
            ->whereNotNull('ai_feature_scores')
            ->with(['cleaner', 'homeowner', 'service'])
            ->get();

        $minSamples = config('ai.xgboost.retraining.min_samples', 100);

        if ($completedBookings->count() < $minSamples && !$this->option('force')) {
            $this->warn("Not enough samples. Have {$completedBookings->count()}, need {$minSamples}.");
            $this->info('Use --force to train anyway.');
            return 1;
        }

        $this->info("Preparing {$completedBookings->count()} training samples...");

        $trainingData = [];

        foreach ($completedBookings as $booking) {
            $features = $booking->ai_feature_scores;
            
            if (!is_array($features) || empty($features)) {
                continue;
            }

            // Target variable: customer satisfaction score (0-100)
            $targetScore = ($booking->cleaner_rating_given / 5) * 100;

            // Extract features from the first recommendation
            $firstRecommendation = is_array($features[0] ?? null) ? $features[0] : $features;

            $trainingData[] = [
                'cleaner_rating' => $booking->cleaner->rating,
                'total_completed_jobs' => $booking->cleaner->total_completed_jobs,
                'experience_days_active' => $booking->cleaner->experience_days_active,
                'avg_response_time_seconds' => $booking->cleaner->avg_response_time_seconds,
                'completion_rate' => $booking->cleaner->completion_rate,
                'cancellation_rate' => $booking->cleaner->cancellation_rate,
                'complaints_count' => $booking->cleaner->complaints_count,
                'profile_completion_score' => $booking->cleaner->profile_completion_score,
                'price_competitiveness' => $booking->cleaner->price_competitiveness,
                'skills_match_score' => $firstRecommendation['skills_match_score'] ?? 80,
                'real_distance_km' => $booking->distance_km ?? 0,
                'traffic_delay_minutes' => $booking->traffic_delay_minutes ?? 0,
                'travel_time_minutes' => $booking->estimated_travel_time_minutes ?? 0,
                'service_area_match' => $firstRecommendation['service_area_match'] ?? 0,
                'route_quality_score' => $booking->route_quality_score ?? 50,
                'booking_urgency_hours' => $booking->booking_type === 'instant' ? 0.25 : 24,
                'is_instant_booking' => $booking->booking_type === 'instant' ? 1 : 0,
                'service_price' => $booking->total_amount,
                'homeowner_rating' => $booking->homeowner->rating ?? 3.0,
                'time_of_day' => (int) $booking->created_at->format('H'),
                'cleaner_success_rate' => $booking->cleaner->success_rate,
                'repeat_customer_rate' => $booking->cleaner->repeat_customer_rate,
                'avg_job_duration_minutes' => $booking->cleaner->avg_job_duration_minutes,
                'last_booking_days_ago' => $booking->cleaner->last_booking_at 
                    ? now()->diffInDays($booking->cleaner->last_booking_at) 
                    : 365,
                'target_score' => $targetScore,
            ];
        }

        $this->info('Sending training data to AI microservice...');

        try {
            $response = Http::timeout(120)
                ->post(config('services.ai.url') . '/train', [
                    'training_data' => $trainingData,
                    'force_retrain' => $this->option('force'),
                    'callback_url' => config('app.url') . '/api/internal/ai/training-complete',
                ]);

            if ($response->successful()) {
                $this->info('✅ AI model training initiated successfully!');
                
                Log::channel('ai')->info('Model training started', [
                    'samples' => count($trainingData),
                    'training_id' => $response->json('training_id'),
                ]);

                return 0;
            }

            $this->error('AI service returned error: ' . $response->body());
            return 1;

        } catch (\Exception $e) {
            $this->error('Failed to connect to AI service: ' . $e->getMessage());
            Log::channel('ai')->error('Training failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
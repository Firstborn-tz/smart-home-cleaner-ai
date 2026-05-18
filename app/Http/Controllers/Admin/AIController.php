<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|super_admin']);
    }

    /**
     * Show AI performance dashboard
     */
    public function performance()
    {
        $metrics = $this->getModelMetrics();
        $featureImportance = $this->getFeatureImportance();
        $predictionAccuracy = $this->getPredictionAccuracy();
        $recommendationStats = $this->getRecommendationStats();

        return view('admin.ai.performance', compact(
            'metrics',
            'featureImportance',
            'predictionAccuracy',
            'recommendationStats'
        ));
    }

    /**
     * Trigger AI model retraining
     */
    public function triggerTraining()
    {
        try {
            // Dispatch training job
            dispatch(new \App\Jobs\TrainAIModelJob());

            Log::channel('ai')->info('AI training triggered manually', [
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'AI model training initiated. This may take a few minutes.',
            ]);
        } catch (\Exception $e) {
            Log::channel('ai')->error('Failed to trigger AI training', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger training: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current model metrics
     */
    private function getModelMetrics(): array
    {
        return Cache::remember('ai:model_metrics', 300, function () {
            $totalPredictions = Booking::whereNotNull('ai_recommendation_score')->count();
            $avgScore = Booking::whereNotNull('ai_recommendation_score')
                ->avg('ai_recommendation_score') ?? 0;
            
            // Calculate RMSE where actual ratings exist
            $ratedBookings = Booking::whereNotNull('ai_recommendation_score')
                ->whereNotNull('cleaner_rating_given')
                ->get();

            $rmse = 0;
            if ($ratedBookings->isNotEmpty()) {
                $squaredErrors = $ratedBookings->map(function ($booking) {
                    $predicted = $booking->ai_recommendation_score / 20; // Scale to 0-5
                    $actual = $booking->cleaner_rating_given;
                    return pow($predicted - $actual, 2);
                });
                $rmse = sqrt($squaredErrors->avg());
            }

            return [
                'total_predictions' => $totalPredictions,
                'average_score' => round($avgScore, 2),
                'rmse' => round($rmse, 3),
                'last_trained' => Cache::get('ai:last_trained', 'Never'),
                'model_version' => '1.0.0',
            ];
        });
    }

    /**
     * Get feature importance from AI service
     */
    private function getFeatureImportance(): array
    {
        return Cache::remember('ai:feature_importance', 3600, function () {
            try {
                $response = Http::timeout(10)
                    ->get(config('services.ai.url') . '/model/info');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['feature_importance'] ?? [];
                }
            } catch (\Exception $e) {
                Log::channel('ai')->error('Failed to fetch feature importance', [
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'cleaner_rating' => 0.25,
                'real_distance_km' => 0.20,
                'completion_rate' => 0.15,
                'avg_response_time_seconds' => 0.10,
                'total_completed_jobs' => 0.08,
                'traffic_delay_minutes' => 0.07,
                'cancellation_rate' => 0.05,
                'booking_urgency_hours' => 0.05,
                'time_of_day' => 0.03,
                'cleaner_success_rate' => 0.02,
            ];
        });
    }

    /**
     * Get prediction accuracy analysis
     */
    private function getPredictionAccuracy(): array
    {
        $completedBookings = Booking::where('status', 'completed')
            ->whereNotNull('ai_recommendation_score')
            ->whereNotNull('cleaner_rating_given')
            ->latest()
            ->limit(100)
            ->get();

        $accuracyBuckets = [
            'excellent' => 0, // Difference < 0.5
            'good' => 0,      // Difference < 1.0
            'fair' => 0,      // Difference < 1.5
            'poor' => 0,      // Difference >= 1.5
        ];

        foreach ($completedBookings as $booking) {
            $predicted = $booking->ai_recommendation_score / 20;
            $actual = $booking->cleaner_rating_given;
            $diff = abs($predicted - $actual);

            if ($diff < 0.5) $accuracyBuckets['excellent']++;
            elseif ($diff < 1.0) $accuracyBuckets['good']++;
            elseif ($diff < 1.5) $accuracyBuckets['fair']++;
            else $accuracyBuckets['poor']++;
        }

        $total = max(count($completedBookings), 1);

        return [
            'buckets' => $accuracyBuckets,
            'percentages' => [
                'excellent' => round(($accuracyBuckets['excellent'] / $total) * 100, 1),
                'good' => round(($accuracyBuckets['good'] / $total) * 100, 1),
                'fair' => round(($accuracyBuckets['fair'] / $total) * 100, 1),
                'poor' => round(($accuracyBuckets['poor'] / $total) * 100, 1),
            ],
            'total_samples' => $total,
        ];
    }

    /**
     * Get recommendation statistics
     */
    private function getRecommendationStats(): array
    {
        return [
            'avg_recommendations_per_booking' => Booking::whereNotNull('ai_recommendations_list')
                ->selectRaw('AVG(JSON_LENGTH(ai_recommendations_list)) as avg')
                ->first()->avg ?? 0,
            'recommendation_acceptance_rate' => Booking::where('ai_rank_position', 1)
                ->where('status', '!=', 'cancelled')
                ->count() / max(Booking::whereNotNull('ai_recommendations_list')->count(), 1) * 100,
            'fallback_usage_rate' => Booking::whereNotNull('ai_recommendations_list')
                ->whereRaw("JSON_EXTRACT(ai_recommendations_list, '$[0].is_fallback') = true")
                ->count(),
        ];
    }
}
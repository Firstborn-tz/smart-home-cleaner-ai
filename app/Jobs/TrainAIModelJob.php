<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TrainAIModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function handle(): void
    {
        Log::channel('ai')->info('========================================');
        Log::channel('ai')->info('AI Training Job Started - Real Data Training');
        Log::channel('ai')->info('========================================');

        try {
            // Collect ALL completed bookings with ratings (real data from database)
            $trainingData = \App\Models\Booking::where('status', 'completed')
                ->whereNotNull('cleaner_rating_given')
                ->whereNotNull('cleaner_id')
                ->with(['cleaner', 'homeowner', 'service'])
                ->get();

            $totalSamples = $trainingData->count();
            
            Log::channel('ai')->info("Training samples available: {\}");

            if ($totalSamples < 10) {
                Log::channel('ai')->warning('AI Training Skipped: Not enough data. Need 10+ rated bookings.');
                return;
            }

            // Build REAL feature matrix from database
            $features = [];
            $targets = [];
            
            foreach ($trainingData as $booking) {
                if (!$booking->cleaner) continue;
                
                $cleaner = $booking->cleaner;
                
                $features[] = [
                    $cleaner->rating ?? 3.0,
                    $cleaner->total_completed_jobs ?? 0,
                    $cleaner->experience_days_active ?? 0,
                    $cleaner->avg_response_time_seconds ?? 120,
                    $cleaner->completion_rate ?? 85,
                    $cleaner->cancellation_rate ?? 5,
                    $cleaner->complaints_count ?? 0,
                    $cleaner->profile_completion_score ?? 70,
                    $cleaner->price_competitiveness ?? 80,
                    80, // skills_match_score (default)
                    $booking->distance_km ?? 5,
                    $booking->traffic_delay_minutes ?? 5,
                    $booking->estimated_travel_time_minutes ?? 15,
                    50, // service_area_match (default)
                    70, // route_quality_score (default)
                    $booking->booking_type === 'instant' ? 0.25 : 24,
                    $booking->booking_type === 'instant' ? 1 : 0,
                    $booking->total_amount ?? 50000,
                    $booking->homeowner->rating ?? 3.0,
                    (int) $booking->created_at->format('H'),
                    $cleaner->success_rate ?? 85,
                    $cleaner->repeat_customer_rate ?? 40,
                    $cleaner->avg_job_duration_minutes ?? 150,
                    $cleaner->last_booking_at ? now()->diffInDays($cleaner->last_booking_at) : 365,
                ];
                
                // Target: cleaner rating converted to 0-100 scale
                $targets[] = ($booking->cleaner_rating_given / 5) * 100;
            }

            $actualSamples = count($features);
            Log::channel('ai')->info("Processed {\} valid training samples from REAL bookings");

            // Send REAL data to Python AI microservice for training
            $aiUrl = config('services.ai.url', 'http://127.0.0.1:8001');
            
            Log::channel('ai')->info("Sending training data to: {\}/train");
            
            $response = Http::timeout(120)
                ->post($aiUrl . '/train', [
                    'training_data' => $features,
                    'targets' => $targets,
                    'force_retrain' => true,
                    'training_date' => now()->format('Y-m-d H:i:s'),
                    'total_samples' => $actualSamples,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::channel('ai')->info('? AI Model trained on REAL data!', $result);
                
                // Notify admin
                try {
                    $admin = \App\Models\User::where('user_type', 'admin')->first();
                    if ($admin) {
                        \App\Models\Notification::create([
                            'user_id' => $admin->id,
                            'type' => 'ai_trained',
                            'title' => 'AI Model Trained on Real Data',
                            'body' => "Model trained with {\} real bookings. MAE: " . ($result['mae'] ?? 'N/A'),
                            'icon' => 'fa-brain',
                            'priority' => 1,
                            'channel' => 'in-app',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not create notification: ' . $e->getMessage());
                }
            } else {
                Log::channel('ai')->warning('AI microservice returned error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('ai')->error('AI Training Job Failed: ' . $e->getMessage());
            throw $e;
        }

        Log::channel('ai')->info('========================================');
        Log::channel('ai')->info('AI Training Job Completed');
        Log::channel('ai')->info('========================================');
    }
}

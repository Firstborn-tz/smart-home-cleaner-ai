<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Microservice Configuration
    |--------------------------------------------------------------------------
    */
    
    'microservice' => [
        'url' => env('AI_MICROSERVICE_URL', 'http://ai-service:8001'),
        'timeout' => env('AI_SERVICE_TIMEOUT', 15),
        'retry_attempts' => env('AI_SERVICE_RETRIES', 3),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | XGBoost Model Configuration
    |--------------------------------------------------------------------------
    */
    
    'xgboost' => [
        'model_path' => env('AI_MODEL_PATH', storage_path('models/xgboost_model.pkl')),
        'scaler_path' => storage_path('models/feature_scaler.pkl'),
        'retraining' => [
            'schedule' => env('AI_RETRAIN_SCHEDULE', '0 2 * * *'), // Daily at 2 AM
            'min_samples' => env('AI_MIN_TRAINING_SAMPLES', 100),
            'validation_split' => 0.2,
        ],
        
        'features' => [
            'cleaner' => [
                'rating', 'total_completed_jobs', 'experience_days_active',
                'avg_response_time_seconds', 'completion_rate', 'cancellation_rate',
                'complaints_count', 'profile_completion_score', 'price_competitiveness',
                'skills_match_score',
            ],
            'location' => [
                'real_distance_km', 'traffic_delay_minutes', 'travel_time_minutes',
                'service_area_match', 'route_quality_score',
            ],
            'booking_context' => [
                'booking_urgency_hours', 'is_instant_booking', 'service_price',
                'homeowner_rating', 'time_of_day',
            ],
            'historical' => [
                'cleaner_success_rate', 'repeat_customer_rate',
                'avg_job_duration_minutes', 'last_booking_days_ago',
            ],
        ],
        
        'hyperparameters' => [
            'n_estimators' => 200,
            'max_depth' => 7,
            'learning_rate' => 0.05,
            'subsample' => 0.8,
            'colsample_bytree' => 0.8,
            'min_child_weight' => 3,
            'gamma' => 0.1,
            'reg_alpha' => 0.1,
            'reg_lambda' => 1.0,
            'objective' => 'reg:squarederror',
            'eval_metric' => 'rmse',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Recommendation Settings
    |--------------------------------------------------------------------------
    */
    
    'recommendation' => [
        'max_cleaners_to_score' => 50,
        'top_cleaners_to_return' => 10,
        'min_recommendation_score' => 40.0, // Minimum score to recommend
        
        'weights' => [
            'distance' => 0.30,
            'rating' => 0.25,
            'completion_rate' => 0.20,
            'experience' => 0.10,
            'response_time' => 0.10,
            'price' => 0.05,
        ],
        
        'instant_booking_priorities' => [
            'distance' => 0.40,  // Higher priority for distance
            'availability' => 0.35,
            'response_time' => 0.25,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cleaner Availability Rules
    |--------------------------------------------------------------------------
    */
    
    'availability' => [
        'online_timeout_minutes' => 10, // Auto-set offline if no heartbeat
        'location_update_interval_seconds' => 30,
        'max_instant_booking_distance_km' => 20,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */
    
    'cache' => [
        'inference_cache_seconds' => 5,
        'feature_cache_seconds' => 300,
        'model_cache_seconds' => 3600,
    ],
];
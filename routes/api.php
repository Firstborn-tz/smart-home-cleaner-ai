<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Smart Home Cleaner AI
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Health check
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'app' => 'SmartClean AI']);
    });

    // Public endpoints
    Route::get('/cities', function () {
        return response()->json(\App\Models\City::where('is_active', true)->get());
    });
    
    Route::get('/services', function () {
        return response()->json(\App\Models\Service::where('is_active', true)->get());
    });
    
    // Protected Routes (require Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::get('/user', function (Request $request) {
            return $request->user()->load('cleaner', 'homeowner');
        });
        
        // AI Status
        Route::get('/ai-status', function () {
            if (class_exists('\App\Services\AI\XGBoostRecommendationService')) {
                $aiService = app(\App\Services\AI\XGBoostRecommendationService::class);
                return response()->json($aiService->getServiceStatus());
            }
            return response()->json(['available' => false]);
        });
    });
});
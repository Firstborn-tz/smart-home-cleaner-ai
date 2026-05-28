<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthController;

// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Registration Pages (Public)
Route::get('/register/cleaner', [AuthController::class, 'showCleanerRegistration'])->name('register.cleaner');
Route::post('/register/cleaner/submit', [AuthController::class, 'registerCleaner'])->name('register.cleaner.submit');
Route::get('/register/homeowner', [AuthController::class, 'showHomeownerRegistration'])->name('register.homeowner');
Route::post('/register/homeowner/submit', [AuthController::class, 'registerHomeowner'])->name('register.homeowner.submit');

// Public Cleaner Profile
Route::get('/cleaner/{id}/profile', function ($id) {
    $cleaner = \App\Models\Cleaner::with(['user', 'city', 'reviews.reviewer.user'])->findOrFail($id);
    return view('cleaner.public-profile', compact('cleaner'));
})->name('cleaner.public-profile');

// AI Status API (public)
Route::get('/api/ai-status', function () {
    if (class_exists('\App\Services\AI\XGBoostRecommendationService')) {
        $aiService = app(\App\Services\AI\XGBoostRecommendationService::class);
        return response()->json($aiService->getServiceStatus());
    }
    return response()->json(['available' => false, 'status' => 'not_configured']);
});

Route::get('/test-ai', function () {
    $homeowner = App\Models\Homeowner::first();
    $service = App\Models\Service::first();
    $aiService = app(App\Services\AI\XGBoostRecommendationService::class);
    
    $tempBooking = new App\Models\Booking([
        'service_id' => $service->id,
        'city_id' => 2,
        'service_latitude' => -6.163,
        'service_longitude' => 35.7516,
        'booking_type' => 'instant',
        'service_price' => 50000,
        'total_amount' => 50000,
    ]);
    $tempBooking->homeowner = $homeowner;
    
    $result = $aiService->recommendCleaners($tempBooking, 5);
    
    return response()->json($result);
});

// ============================================
// PROTECTED ROUTES (Require Authentication)
// ============================================

Route::middleware('auth')->group(function () {

    // ============================================
    // ADMIN ROUTES
    // ============================================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');
        Route::get('/ai-status', function () { return view('admin.ai-status'); })->name('ai-status');

        // Registration Requests
        Route::get('/cleaner-requests', function () { return view('admin.cleaner-requests'); })->name('cleaner-requests');

        // Cleaner Management
        Route::get('/cleaners', [App\Http\Controllers\Admin\CleanerController::class, 'index'])->name('cleaners');
        Route::get('/cleaners/{cleaner}', [App\Http\Controllers\Admin\CleanerController::class, 'show'])->name('cleaners.show');
        Route::get('/cleaners/{cleaner}/details', [App\Http\Controllers\Admin\CleanerController::class, 'details'])->name('cleaners.details');
        Route::post('/cleaners/{cleaner}/approve', [App\Http\Controllers\Admin\CleanerController::class, 'approve'])->name('cleaners.approve');
        Route::post('/cleaners/{cleaner}/reject', [App\Http\Controllers\Admin\CleanerController::class, 'reject'])->name('cleaners.reject');
        Route::post('/cleaners/{cleaner}/suspend', [App\Http\Controllers\Admin\CleanerController::class, 'suspend'])->name('cleaners.suspend');
        Route::post('/cleaners/{cleaner}/status', [App\Http\Controllers\Admin\CleanerController::class, 'updateStatus'])->name('cleaners.status');

        // Commission Management
        Route::get('/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions');
        Route::get('/commissions/{commission}', [App\Http\Controllers\Admin\CommissionController::class, 'show'])->name('commissions.show');
        Route::post('/commissions/{commission}/record-payment', [App\Http\Controllers\Admin\CommissionController::class, 'recordPayment'])->name('commissions.record-payment');
        Route::post('/commissions/generate', [App\Http\Controllers\Admin\CommissionController::class, 'generateCommissions'])->name('commissions.generate');
        Route::get('/commissions/cleaner/{cleaner}/summary', [App\Http\Controllers\Admin\CommissionController::class, 'cleanerSummary'])->name('commissions.cleaner-summary');

        // City Management
        Route::get('/cities', [App\Http\Controllers\Admin\CityController::class, 'index'])->name('cities');
        Route::get('/cities/{city}', [App\Http\Controllers\Admin\CityController::class, 'show'])->name('cities.show');
        Route::post('/cities/{city}/toggle-status', [App\Http\Controllers\Admin\CityController::class, 'toggleStatus'])->name('cities.toggle-status');
        Route::post('/cities/{city}/update-pricing', [App\Http\Controllers\Admin\CityController::class, 'updatePricing'])->name('cities.update-pricing');
        Route::get('/cities/{city}/stats', [App\Http\Controllers\Admin\CityController::class, 'stats'])->name('cities.stats');
        Route::post('/cities/update-order', [App\Http\Controllers\Admin\CityController::class, 'updateOrder'])->name('cities.update-order');
        Route::get('/regions', [App\Http\Controllers\Admin\RegionController::class, 'index'])->name('regions');
        Route::post('/regions/{region}/toggle-registration', [App\Http\Controllers\Admin\RegionController::class, 'toggleRegistration'])->name('regions.toggle-registration');
        Route::post('/ai/retrain', function () {
            \App\Jobs\TrainAIModelJob::dispatch();
            return back()->with('success', 'AI model retraining initiated!');
        })->name('admin.ai.retrain');

        // Services
        Route::get('/services', [App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('services');
        Route::post('/services/store', [App\Http\Controllers\Admin\ServiceController::class, 'store'])->name('services.store');
        Route::post('/services/{service}/update', [App\Http\Controllers\Admin\ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}/delete', [App\Http\Controllers\Admin\ServiceController::class, 'destroy'])->name('services.destroy');
        Route::post('/services/{service}/toggle-status', [App\Http\Controllers\Admin\ServiceController::class, 'toggleStatus'])->name('services.toggle-status');

        // Settings
        Route::post('/settings/update-commission', function (Request $request) {
            $request->validate(['commission_rate' => 'required|numeric|min:0|max:100']);
            \App\Models\Setting::updateOrCreate(['key' => 'commission_rate'], ['value' => $request->commission_rate]);
            return response()->json(['success' => true, 'message' => 'Commission rate updated!']);
        })->name('settings.commission');
    });

    // ============================================
    // CLEANER ROUTES
    // ============================================
    Route::prefix('cleaner')->name('cleaner.')->group(function () {
        
        Route::get('/dashboard', function () { return view('cleaner.dashboard'); })->name('dashboard');

        // Availability
        Route::post('/availability/toggle', [App\Http\Controllers\Cleaner\AvailabilityController::class, 'toggle'])->name('availability.toggle');
        Route::get('/availability/status', [App\Http\Controllers\Cleaner\AvailabilityController::class, 'getStatus'])->name('availability.status');
        Route::post('/location/update', [App\Http\Controllers\Cleaner\LocationController::class, 'update'])->name('location.update');

        // Bookings
        Route::get('/bookings', function () { return view('cleaner.bookings.index'); })->name('bookings');
        Route::get('/bookings/{booking}/detail', function ($id) {
            $booking = \App\Models\Booking::with(['service', 'cleaner.user', 'homeowner.user', 'verificationCodes'])->findOrFail($id);
            return view('cleaner.bookings.show', compact('booking'));
        })->name('bookings.show');

        // Request Management
        Route::get('/requests/pending', [App\Http\Controllers\Cleaner\BookingController::class, 'pendingRequests'])->name('requests.pending');
        Route::post('/bookings/{booking}/accept', [App\Http\Controllers\Cleaner\BookingController::class, 'accept'])->name('bookings.accept');
        Route::post('/bookings/{booking}/decline', [App\Http\Controllers\Cleaner\BookingController::class, 'decline'])->name('bookings.decline');
        Route::post('/bookings/{booking}/arrive', [App\Http\Controllers\Cleaner\BookingController::class, 'confirmArrival'])->name('bookings.arrive');
        Route::post('/bookings/{booking}/start', [App\Http\Controllers\Cleaner\BookingController::class, 'startService'])->name('bookings.start');
        Route::post('/bookings/{booking}/complete', [App\Http\Controllers\Cleaner\BookingController::class, 'completeService'])->name('bookings.complete');

        // Verification
        Route::get('/bookings/{booking}/verify', function ($id) {
            $booking = \App\Models\Booking::findOrFail($id);
            return view('cleaner.verify', compact('booking'));
        })->name('verify-page');
        Route::post('/bookings/{booking}/generate-code', [App\Http\Controllers\Cleaner\VerificationController::class, 'generate'])->name('bookings.generate-code');
        Route::post('/bookings/{booking}/verify', [App\Http\Controllers\Cleaner\VerificationController::class, 'verify'])->name('bookings.verify');
        Route::get('/bookings/{booking}/verification-status', [App\Http\Controllers\Cleaner\VerificationController::class, 'status'])->name('bookings.verification-status');

        // Earnings
        Route::get('/earnings', function () { return view('cleaner.earnings'); })->name('earnings');

        // Services
        Route::get('/services', function () { return view('cleaner.services'); })->name('services');
        Route::post('/services/save', [App\Http\Controllers\Cleaner\ProfileController::class, 'saveServices'])->name('services.save');

        // Profile
        Route::get('/profile', function () { return view('cleaner.profile'); })->name('profile');
        Route::post('/profile/update', [App\Http\Controllers\Cleaner\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/upload-avatar', [App\Http\Controllers\Cleaner\ProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');
        Route::post('/profile/change-password', [App\Http\Controllers\Cleaner\ProfileController::class, 'changePassword'])->name('profile.change-password');

        // Business Profile
        Route::get('/business-profile', function () { return view('cleaner.business-profile'); })->name('business-profile');
        Route::post('/business/save', [App\Http\Controllers\Cleaner\BusinessProfileController::class, 'save'])->name('business.save');
        Route::post('/business/upload-image', [App\Http\Controllers\Cleaner\BusinessProfileController::class, 'uploadImage'])->name('business.upload-image');
        Route::get('/registration-status', function () {
            return view('cleaner.registration-status');
        })->name('registration-status');
    });

    // Cleaner Profile Data API (for homeowner dashboard modal)
    Route::get('/cleaner/{id}/profile/data', function ($id) {
        $cleaner = \App\Models\Cleaner::with('user')->findOrFail($id);
        return response()->json([
            'success' => true,
            'cleaner' => [
                'id' => $cleaner->id,
                'name' => $cleaner->user->full_name,
                'cleaner_id_number' => $cleaner->cleaner_id,
                'rating' => round((float) $cleaner->rating, 1),
                'completed_jobs' => (int) $cleaner->total_completed_jobs,
                'completion_rate' => round((float) $cleaner->completion_rate, 1),
                'cancellation_rate' => round((float) $cleaner->cancellation_rate, 1),
                'experience_days' => (int) $cleaner->experience_days_active,
                'avg_response_time_seconds' => (float) ($cleaner->avg_response_time_seconds ?? 120),
                'business_name' => $cleaner->business_name,
                'years_experience' => $cleaner->years_experience,
                'team_size' => $cleaner->team_size,
                'district' => $cleaner->district,
                'region' => $cleaner->region,
                'custom_prices' => is_string($cleaner->custom_prices) ? json_decode($cleaner->custom_prices, true) : ($cleaner->custom_prices ?? []),
                'service_skills' => is_string($cleaner->service_skills) ? json_decode($cleaner->service_skills, true) : ($cleaner->service_skills ?? []),
            ]
        ]);
    })->name('cleaner.profile.data');

    // ============================================
    // HOMEOWNER ROUTES
    // ============================================
    Route::prefix('homeowner')->name('homeowner.')->group(function () {
        
        Route::get('/dashboard', function () { return view('homeowner.dashboard'); })->name('dashboard');

        // Bookings
        Route::get('/bookings/create', [App\Http\Controllers\Homeowner\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [App\Http\Controllers\Homeowner\BookingController::class, 'store'])->name('bookings.store');
        Route::post('/recommendations', [App\Http\Controllers\Homeowner\BookingController::class, 'getRecommendations'])->name('recommendations');

        // Active Requests & Cancel
        Route::get('/requests/active', [App\Http\Controllers\Homeowner\BookingController::class, 'activeRequests'])->name('requests.active');
        Route::post('/requests/{id}/cancel', [App\Http\Controllers\Homeowner\BookingController::class, 'cancelRequest'])->name('requests.cancel');

        // Service Tracking (BEFORE wildcard)
        Route::get('/bookings/{booking}/track', [App\Http\Controllers\Homeowner\ServiceController::class, 'track'])->name('service.track');
        Route::post('/bookings/{booking}/verify-start', [App\Http\Controllers\Homeowner\ServiceController::class, 'verifyAndStart'])->name('service.verify-start');
        Route::post('/bookings/{booking}/complete', [App\Http\Controllers\Homeowner\ServiceController::class, 'markComplete'])->name('service.complete');
        Route::post('/bookings/{booking}/review', [App\Http\Controllers\Homeowner\ServiceController::class, 'submitReview'])->name('service.review');

        // Booking Details (wildcard LAST)
        Route::get('/bookings/{id}', [App\Http\Controllers\Homeowner\BookingController::class, 'show'])->name('bookings.show');

        // Profile
        Route::get('/profile', function () { return view('homeowner.profile'); })->name('profile');
        Route::post('/profile/update', [App\Http\Controllers\Homeowner\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/upload-avatar', [App\Http\Controllers\Homeowner\ProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');
        Route::post('/profile/update-address', [App\Http\Controllers\Homeowner\ProfileController::class, 'updateAddress'])->name('profile.update-address');
        Route::post('/profile/change-password', [App\Http\Controllers\Homeowner\ProfileController::class, 'changePassword'])->name('profile.change-password');
    });

    // ============================================
    // API ROUTES (Protected)
    // ============================================
    
    // Distance & ETA
    Route::post('/api/get-distance-eta', function (Request $request) {
        $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'dest_lat' => 'required|numeric',
            'dest_lng' => 'required|numeric',
        ]);
        $mapsService = app(\App\Services\Location\GoogleMapsService::class);
        $result = $mapsService->getDistanceAndETA(
            $request->origin_lat, $request->origin_lng,
            $request->dest_lat, $request->dest_lng
        );
        return response()->json(['success' => true, 'data' => $result]);
    })->name('api.distance');

    // Notifications
    Route::get('/api/notifications', function () {
        $notifications = \App\Models\Notification::where('user_id', Auth::id())
            ->latest()->limit(20)->get()
            ->map(function($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'icon' => $n->icon ?? 'fa-bell',
                    'icon_bg' => $n->priority == 2 ? 'bg-red-100 text-red-600' : ($n->priority == 1 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'),
                    'time' => $n->created_at->diffForHumans(),
                    'read' => !is_null($n->read_at),
                ];
            });
        return response()->json(['success' => true, 'notifications' => $notifications]);
    })->name('api.notifications');

    Route::post('/api/notifications/mark-all-read', function () {
        \App\Models\Notification::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    })->name('api.notifications.mark-read');

    Route::get('/admin/sms-balance', function () {
        $sms = new \App\Services\SMS\BeemSMSService();
        $balance = $sms->getBalance();
        return response()->json($balance);
    })->name('admin.sms-balance');
});
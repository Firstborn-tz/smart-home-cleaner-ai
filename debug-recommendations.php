<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CLEANER RECOMMENDATION DEBUG ===\n\n";

// Step 1: Online verified cleaners with GPS
$allCleaners = App\Models\Cleaner::where("availability_status", "online")
    ->where("is_verified", true)
    ->whereNotNull("current_latitude")
    ->whereNotNull("current_longitude")
    ->get();

echo "1. Online verified cleaners with GPS: " . $allCleaners->count() . "\n";
foreach($allCleaners as $c) {
    echo "   - " . $c->user->full_name . " | Lat:" . $c->current_latitude . " | Lng:" . $c->current_longitude . " | Radius:" . $c->max_service_radius_km . "km\n";
}

// Step 2: Homeowner location
$homeowner = App\Models\Homeowner::with("user")->first();
echo "\n2. Homeowner: " . $homeowner->user->full_name . "\n";
echo "   Lat: " . $homeowner->latitude . "\n";
echo "   Lng: " . $homeowner->longitude . "\n";

// Step 3: Distance check
echo "\n3. Distance check:\n";
function calcDist($lat1, $lon1, $lat2, $lon2) {
    $r = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return round($r * $c, 2);
}

foreach($allCleaners as $c) {
    $dist = calcDist((float)$c->current_latitude, (float)$c->current_longitude, (float)$homeowner->latitude, (float)$homeowner->longitude);
    $inRadius = $dist <= ($c->max_service_radius_km ?? 30) ? "YES" : "NO";
    echo "   " . $c->user->full_name . ": " . $dist . "km - Within radius: " . $inRadius . "\n";
}

// Step 4: Test the actual recommendation
echo "\n4. Testing AI Recommendation Service:\n";
$service = App\Models\Service::first();
$tempBooking = new App\Models\Booking([
    "service_id" => $service->id,
    "city_id" => $homeowner->city_id ?? 1,
    "service_latitude" => $homeowner->latitude,
    "service_longitude" => $homeowner->longitude,
    "booking_type" => "instant",
    "service_price" => $service->base_price,
    "total_amount" => $service->base_price,
]);
$tempBooking->homeowner = $homeowner;

try {
    $aiService = app(App\Services\AI\XGBoostRecommendationService::class);
    $results = $aiService->recommendCleaners($tempBooking, 5);
    echo "   Results: " . count($results) . " cleaners found\n";
    foreach($results as $r) {
        echo "   - " . $r["cleaner_name"] . ": Score " . $r["ai_score"] . "%, Distance " . $r["distance_km"] . "km\n";
    }
    if (count($results) === 0) {
        echo "   NO CLEANERS RETURNED!\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";

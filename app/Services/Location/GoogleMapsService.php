<?php

namespace App\Services\Location;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private string $apiKey;
    private string $baseUrl = 'https://maps.googleapis.com/maps/api';

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_MAPS_API_KEY', '');
    }

    public function getDistanceAndETA($originLat, $originLng, $destLat, $destLng): ?array
    {
        if (!$this->isConfigured()) {
            return $this->getFallbackDistance($originLat, $originLng, $destLat, $destLng);
        }

        $cacheKey = "gm_dist:" . md5("{$originLat},{$originLng}:{$destLat},{$destLng}");

        return Cache::remember($cacheKey, 300, function () use ($originLat, $originLng, $destLat, $destLng) {
            try {
                $response = Http::get("{$this->baseUrl}/distancematrix/json", [
                    'origins' => "{$originLat},{$originLng}",
                    'destinations' => "{$destLat},{$destLng}",
                    'mode' => 'driving',
                    'departure_time' => 'now',
                    'traffic_model' => 'best_guess',
                    'key' => $this->apiKey,
                    'units' => 'metric',
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK') {
                    return $this->getFallbackDistance($originLat, $originLng, $destLat, $destLng);
                }

                $element = $data['rows'][0]['elements'][0] ?? null;
                if (!$element || ($element['status'] ?? '') !== 'OK') {
                    return $this->getFallbackDistance($originLat, $originLng, $destLat, $destLng);
                }

                $distanceKm = $element['distance']['value'] / 1000;
                $durationMinutes = $element['duration']['value'] / 60;
                $durationInTraffic = ($element['duration_in_traffic']['value'] ?? $element['duration']['value']) / 60;
                $trafficDelay = max(0, $durationInTraffic - $durationMinutes);

                return [
                    'distance_km' => round($distanceKm, 2),
                    'distance_text' => $element['distance']['text'],
                    'duration_minutes' => round($durationMinutes),
                    'duration_text' => $element['duration']['text'],
                    'duration_in_traffic_minutes' => round($durationInTraffic),
                    'duration_in_traffic_text' => $element['duration_in_traffic']['text'] ?? $element['duration']['text'],
                    'traffic_delay_minutes' => round($trafficDelay),
                    'source' => 'google_maps',
                ];

            } catch (\Exception $e) {
                Log::error('Distance Matrix API failed: ' . $e->getMessage());
                return $this->getFallbackDistance($originLat, $originLng, $destLat, $destLng);
            }
        });
    }

    public function getDirections($originLat, $originLng, $destLat, $destLng): ?array
    {
        if (!$this->isConfigured()) return null;

        $cacheKey = "gm_dir:" . md5("{$originLat},{$originLng}:{$destLat},{$destLng}");

        return Cache::remember($cacheKey, 300, function () use ($originLat, $originLng, $destLat, $destLng) {
            try {
                $response = Http::get("{$this->baseUrl}/directions/json", [
                    'origin' => "{$originLat},{$originLng}",
                    'destination' => "{$destLat},{$destLng}",
                    'mode' => 'driving',
                    'departure_time' => 'now',
                    'traffic_model' => 'best_guess',
                    'key' => $this->apiKey,
                ]);

                $data = $response->json();
                if (($data['status'] ?? '') !== 'OK') return null;

                $route = $data['routes'][0] ?? null;
                if (!$route) return null;

                $leg = $route['legs'][0];

                return [
                    'distance_km' => round($leg['distance']['value'] / 1000, 2),
                    'distance_text' => $leg['distance']['text'],
                    'duration_minutes' => round($leg['duration']['value'] / 60),
                    'duration_text' => $leg['duration']['text'],
                    'duration_in_traffic_minutes' => round(($leg['duration_in_traffic']['value'] ?? $leg['duration']['value']) / 60),
                    'start_address' => $leg['start_address'],
                    'end_address' => $leg['end_address'],
                    'source' => 'google_maps',
                ];
            } catch (\Exception $e) {
                Log::error('Directions API failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function reverseGeocode($lat, $lng): ?array
    {
        if (!$this->isConfigured()) return null;

        $cacheKey = "gm_geo:" . md5("{$lat},{$lng}");

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $response = Http::get("{$this->baseUrl}/geocode/json", [
                    'latlng' => "{$lat},{$lng}",
                    'key' => $this->apiKey,
                    'language' => 'en',
                ]);

                $data = $response->json();
                if (($data['status'] ?? '') !== 'OK') return null;

                $result = $data['results'][0] ?? null;
                if (!$result) return null;

                $components = [];
                foreach ($result['address_components'] as $comp) {
                    foreach ($comp['types'] as $type) {
                        $components[$type] = $comp['long_name'];
                    }
                }

                return [
                    'formatted_address' => $result['formatted_address'],
                    'street' => $components['route'] ?? null,
                    'ward' => $components['sublocality_level_2'] ?? $components['sublocality_level_1'] ?? $components['neighborhood'] ?? null,
                    'district' => $components['administrative_area_level_2'] ?? $components['locality'] ?? null,
                    'region' => $components['administrative_area_level_1'] ?? null,
                    'city' => $components['locality'] ?? $components['administrative_area_level_2'] ?? null,
                    'place_id' => $result['place_id'],
                ];
            } catch (\Exception $e) {
                Log::error('Geocoding API failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function getNearbyPlaces($lat, $lng, int $radius = 1000): ?array
    {
        if (!$this->isConfigured()) return null;

        $cacheKey = "gm_places:" . md5("{$lat},{$lng}:{$radius}");

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng, $radius) {
            try {
                $response = Http::get("{$this->baseUrl}/place/nearbysearch/json", [
                    'location' => "{$lat},{$lng}",
                    'radius' => $radius,
                    'key' => $this->apiKey,
                ]);

                $data = $response->json();
                if (($data['status'] ?? '') !== 'OK') return null;

                $places = $data['results'] ?? [];
                $total = count($places);

                return [
                    'total_places' => $total,
                    'area_density' => $total > 30 ? 'high' : ($total > 10 ? 'medium' : 'low'),
                    'nearby_count' => $total,
                ];
            } catch (\Exception $e) {
                Log::error('Places API failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    private function getFallbackDistance($lat1, $lon1, $lat2, $lon2): array
    {
        $earthRadius = 6371;
        $latDelta = deg2rad((float)$lat2 - (float)$lat1);
        $lonDelta = deg2rad((float)$lon2 - (float)$lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $straightDistance = $earthRadius * $c;
        $distance = $straightDistance * 1.4;
        $durationMinutes = ($distance / 35) * 60;
        $trafficDelay = $durationMinutes * 0.3;

        return [
            'distance_km' => round($distance, 2),
            'distance_text' => round($distance, 1) . ' km',
            'duration_minutes' => round($durationMinutes),
            'duration_text' => round($durationMinutes) . ' mins',
            'duration_in_traffic_minutes' => round($durationMinutes + $trafficDelay),
            'duration_in_traffic_text' => round($durationMinutes + $trafficDelay) . ' mins',
            'traffic_delay_minutes' => round($trafficDelay),
            'source' => 'haversine_estimate',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'YOUR_API_KEY';
    }
}
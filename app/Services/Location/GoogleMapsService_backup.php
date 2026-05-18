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
        $this->apiKey = env('GOOGLE_MAPS_API_KEY', 'AIzaSyB0BdEGf7kQn3DD2PYlBG4ozy6wLc_bd1Y');
    }

    // ============================================
    // 1. DISTANCE MATRIX API (Real Road Distance + Traffic)
    // ============================================
    
    /**
     * Get real road distance and ETA between two points
     */
    public function getDistanceAndETA($originLat, $originLng, $destLat, $destLng): ?array
    {
        if (!$this->isConfigured()) {
            return $this->getFallbackDistance($originLat, $originLng, $destLat, $destLng);
        }

        $cacheKey = "gm_distance:{\},{\}:{\},{\}";

        return Cache::remember($cacheKey, 300, function () use ($originLat, $originLng, $destLat, $destLng) {
            try {
                $response = Http::get("{\->baseUrl}/distancematrix/json", [
                    'origins' => "{\},{\}",
                    'destinations' => "{\},{\}",
                    'mode' => 'driving',
                    'departure_time' => 'now',
                    'traffic_model' => 'best_guess',
                    'key' => $this->apiKey,
                    'units' => 'metric',
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK') {
                    Log::warning('Distance Matrix API error', ['status' => $data['status'] ?? 'UNKNOWN']);
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

    // ============================================
    // 2. DIRECTIONS API (Route Details + Polyline)
    // ============================================

    /**
     * Get directions with road types and route quality
     */
    public function getDirections($originLat, $originLng, $destLat, $destLng): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cacheKey = "gm_directions:{\},{\}:{\},{\}";

        return Cache::remember($cacheKey, 300, function () use ($originLat, $originLng, $destLat, $destLng) {
            try {
                $response = Http::get("{\->baseUrl}/directions/json", [
                    'origin' => "{\},{\}",
                    'destination' => "{\},{\}",
                    'mode' => 'driving',
                    'departure_time' => 'now',
                    'traffic_model' => 'best_guess',
                    'alternatives' => 'true',
                    'key' => $this->apiKey,
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK') {
                    return null;
                }

                $route = $data['routes'][0] ?? null;
                if (!$route) return null;

                $leg = $route['legs'][0];
                
                // Analyze road types from steps
                $roadTypes = $this->analyzeRoadTypes($route);
                
                // Calculate route quality score
                $routeQuality = $this->calculateRouteQuality($route, $roadTypes);

                return [
                    'distance_km' => round($leg['distance']['value'] / 1000, 2),
                    'distance_text' => $leg['distance']['text'],
                    'duration_minutes' => round($leg['duration']['value'] / 60),
                    'duration_text' => $leg['duration']['text'],
                    'duration_in_traffic_minutes' => round(($leg['duration_in_traffic']['value'] ?? $leg['duration']['value']) / 60),
                    'start_address' => $leg['start_address'],
                    'end_address' => $leg['end_address'],
                    'polyline' => $route['overview_polyline']['points'] ?? null,
                    'road_types' => $roadTypes,
                    'route_quality_score' => $routeQuality['score'],
                    'route_quality_factors' => $routeQuality['factors'],
                    'has_highway' => $roadTypes['has_highway'] ?? false,
                    'has_unpaved' => $roadTypes['has_unpaved'] ?? false,
                    'road_complexity' => $roadTypes['complexity'] ?? 'simple',
                ];
            } catch (\Exception $e) {
                Log::error('Directions API failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Analyze road types from route steps
     */
    private function analyzeRoadTypes(array $route): array
    {
        $steps = $route['legs'][0]['steps'] ?? [];
        $roadTypes = [
            'highway' => 0,
            'primary' => 0,
            'secondary' => 0,
            'tertiary' => 0,
            'residential' => 0,
            'unpaved' => 0,
            'total_steps' => count($steps),
            'has_highway' => false,
            'has_unpaved' => false,
        ];
        
        foreach ($steps as $step) {
            $instructions = strtolower($step['html_instructions'] ?? '');
            $maneuver = strtolower($step['maneuver'] ?? '');
            
            // Detect road types from instructions and maneuver
            if (str_contains($instructions, 'highway') || str_contains($instructions, 'motorway') || str_contains($instructions, 'expressway')) {
                $roadTypes['highway']++;
                $roadTypes['has_highway'] = true;
            } elseif (str_contains($instructions, 'primary') || str_contains($maneuver, 'ramp')) {
                $roadTypes['primary']++;
            } elseif (str_contains($instructions, 'secondary') || str_contains($instructions, 'arterial')) {
                $roadTypes['secondary']++;
            } elseif (str_contains($instructions, 'tertiary') || str_contains($instructions, 'local')) {
                $roadTypes['tertiary']++;
            } elseif (str_contains($instructions, 'residential') || str_contains($instructions, 'neighborhood')) {
                $roadTypes['residential']++;
            } elseif (str_contains($instructions, 'unpaved') || str_contains($instructions, 'dirt') || str_contains($instructions, 'gravel')) {
                $roadTypes['unpaved']++;
                $roadTypes['has_unpaved'] = true;
            }
        }
        
        // Determine complexity
        if ($roadTypes['has_highway'] && $roadTypes['total_steps'] <= 5) {
            $roadTypes['complexity'] = 'simple_highway';
        } elseif ($roadTypes['has_highway']) {
            $roadTypes['complexity'] = 'moderate_highway';
        } elseif ($roadTypes['has_unpaved']) {
            $roadTypes['complexity'] = 'rough_terrain';
        } elseif ($roadTypes['total_steps'] <= 3) {
            $roadTypes['complexity'] = 'simple_local';
        } else {
            $roadTypes['complexity'] = 'complex_local';
        }
        
        return $roadTypes;
    }

    /**
     * Calculate route quality score (0-100)
     */
    private function calculateRouteQuality(array $route, array $roadTypes): array
    {
        $score = 100;
        $factors = [];
        
        // Highway bonus: faster, more reliable
        if ($roadTypes['has_highway']) {
            $score += 10;
            $factors[] = 'highway_access';
        }
        
        // Unpaved penalty: slow, unreliable, uncomfortable
        if ($roadTypes['has_unpaved']) {
            $score -= 30;
            $factors[] = 'unpaved_road';
        }
        
        // Complex routes: more chances of getting lost/delayed
        if ($roadTypes['total_steps'] > 8) {
            $score -= 15;
            $factors[] = 'complex_route';
        } elseif ($roadTypes['total_steps'] <= 3) {
            $score += 5;
            $factors[] = 'simple_route';
        }
        
        // Residential areas: slower but safer
        if ($roadTypes['residential'] > 2) {
            $score -= 5;
            $factors[] = 'residential_area';
        }
        
        // Distance factor
        $distanceKm = $route['legs'][0]['distance']['value'] / 1000;
        if ($distanceKm > 20) {
            $score -= 10;
            $factors[] = 'long_distance';
        }
        
        $score = max(0, min(100, $score));
        
        return [
            'score' => $score,
            'factors' => $factors
        ];
    }

    // ============================================
    // 3. GEOCODING API (Address ? Coordinates + Area Details)
    // ============================================

    /**
     * Reverse geocode coordinates to get address details
     */
    public function reverseGeocode($lat, $lng): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cacheKey = "gm_geocode:{\},{\}";

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $response = Http::get("{\->baseUrl}/geocode/json", [
                    'latlng' => "{\},{\}",
                    'key' => $this->apiKey,
                    'language' => 'en',
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK') {
                    return null;
                }

                $result = $data['results'][0] ?? null;
                if (!$result) return null;

                // Extract address components
                $components = $this->extractAddressComponents($result['address_components']);
                
                return [
                    'formatted_address' => $result['formatted_address'],
                    'street' => $components['route'] ?? $components['street_address'] ?? null,
                    'ward' => $components['sublocality_level_2'] ?? $components['sublocality_level_1'] ?? $components['neighborhood'] ?? null,
                    'district' => $components['administrative_area_level_2'] ?? $components['locality'] ?? null,
                    'region' => $components['administrative_area_level_1'] ?? null,
                    'city' => $components['locality'] ?? $components['administrative_area_level_2'] ?? null,
                    'postal_code' => $components['postal_code'] ?? null,
                    'country' => $components['country'] ?? null,
                    'place_id' => $result['place_id'],
                    'location_type' => $result['geometry']['location_type'] ?? null,
                    'types' => $result['types'] ?? [],
                    'is_residential' => in_array('street_address', $result['types']) || in_array('premise', $result['types']),
                    'is_commercial' => in_array('establishment', $result['types']) || in_array('point_of_interest', $result['types']),
                ];
            } catch (\Exception $e) {
                Log::error('Geocoding API failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Forward geocode address to coordinates
     */
    public function geocodeAddress(string $address): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cacheKey = "gm_geocode_addr:" . md5($address);

        return Cache::remember($cacheKey, 86400, function () use ($address) {
            try {
                $response = Http::get("{\->baseUrl}/geocode/json", [
                    'address' => $address . ', Tanzania',
                    'key' => $this->apiKey,
                    'language' => 'en',
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK') {
                    return null;
                }

                $result = $data['results'][0] ?? null;
                if (!$result) return null;

                return [
                    'latitude' => $result['geometry']['location']['lat'],
                    'longitude' => $result['geometry']['location']['lng'],
                    'formatted_address' => $result['formatted_address'],
                    'place_id' => $result['place_id'],
                ];
            } catch (\Exception $e) {
                Log::error('Forward Geocoding failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Extract structured address components
     */
    private function extractAddressComponents(array $components): array
    {
        $result = [];
        
        foreach ($components as $component) {
            $types = $component['types'] ?? [];
            
            foreach ($types as $type) {
                if (!isset($result[$type])) {
                    $result[$type] = $component['long_name'];
                }
            }
        }
        
        return $result;
    }

    // ============================================
    // 4. PLACES API (Nearby Landmarks + Area Type)
    // ============================================

    /**
     * Get nearby places to understand area type
     */
    public function getNearbyPlaces($lat, $lng, int $radius = 1000): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cacheKey = "gm_places:{\},{\}:{\}";

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng, $radius) {
            try {
                $response = Http::get("{\->baseUrl}/place/nearbysearch/json", [
                    'location' => "{\},{\}",
                    'radius' => $radius,
                    'key' => $this->apiKey,
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK') {
                    return null;
                }

                $places = $data['results'] ?? [];
                
                // Categorize nearby places
                $categories = [
                    'restaurant' => 0,
                    'school' => 0,
                    'hospital' => 0,
                    'shopping_mall' => 0,
                    'office' => 0,
                    'bank' => 0,
                    'place_of_worship' => 0,
                    'park' => 0,
                    'university' => 0,
                    'hotel' => 0,
                    'gym' => 0,
                    'supermarket' => 0,
                ];
                
                $totalPlaces = count($places);
                
                foreach ($places as $place) {
                    $types = $place['types'] ?? [];
                    
                    foreach ($types as $type) {
                        if (isset($categories[$type])) {
                            $categories[$type]++;
                            break;
                        }
                    }
                }
                
                // Determine area type
                $areaType = $this->determineAreaType($categories, $totalPlaces);
                
                return [
                    'total_places' => $totalPlaces,
                    'place_categories' => $categories,
                    'area_type' => $areaType['type'],
                    'area_density' => $areaType['density'],
                    'area_affluence' => $areaType['affluence'],
                    'is_commercial_area' => $areaType['type'] === 'commercial',
                    'is_residential_area' => $areaType['type'] === 'residential',
                    'is_mixed_area' => $areaType['type'] === 'mixed',
                    'nearby_landmarks' => array_slice($places, 0, 5),
                ];
            } catch (\Exception $e) {
                Log::error('Places API failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Determine area type from nearby places
     */
    private function determineAreaType(array $categories, int $totalPlaces): array
    {
        $commercialScore = ($categories['restaurant'] + $categories['shopping_mall'] + $categories['office'] + $categories['bank'] + $categories['hotel']);
        $residentialScore = ($categories['school'] + $categories['place_of_worship'] + $categories['park']);
        $institutionalScore = ($categories['hospital'] + $categories['university']);
        
        // Determine density
        if ($totalPlaces > 30) {
            $density = 'high';
        } elseif ($totalPlaces > 10) {
            $density = 'medium';
        } else {
            $density = 'low';
        }
        
        // Determine area type
        if ($commercialScore > $residentialScore * 2) {
            $type = 'commercial';
        } elseif ($residentialScore > $commercialScore * 2) {
            $type = 'residential';
        } elseif ($institutionalScore > max($commercialScore, $residentialScore)) {
            $type = 'institutional';
        } else {
            $type = 'mixed';
        }
        
        // Estimate affluence
        $hasMall = $categories['shopping_mall'] > 0;
        $hasOffice = $categories['office'] > 2;
        $hasGym = $categories['gym'] > 0;
        
        if ($hasMall && $hasOffice && $hasGym) {
            $affluence = 'high';
        } elseif ($hasMall || $hasOffice) {
            $affluence = 'medium';
        } else {
            $affluence = 'standard';
        }
        
        return [
            'type' => $type,
            'density' => $density,
            'affluence' => $affluence
        ];
    }

    // ============================================
    // 5. ROADS API (Road Quality + Speed Data)
    // ============================================

    /**
     * Get road metadata for a specific route
     */
    public function getRoadMetadata(array $path): ?array
    {
        if (!$this->isConfigured() || count($path) < 2) {
            return null;
        }

        $cacheKey = "gm_roads:" . md5(serialize($path));

        return Cache::remember($cacheKey, 3600, function () use ($path) {
            try {
                // Snap points to roads
                $snapped = $this->snapToRoads($path);
                
                if (!$snapped) {
                    return null;
                }
                
                // Get speed limits
                $speedData = $this->getSpeedLimits($snapped);
                
                return [
                    'snapped_points' => $snapped,
                    'speed_limits' => $speedData,
                    'has_speed_data' => !empty($speedData),
                    'average_speed_limit' => $speedData ? round(array_sum(array_column($speedData, 'speedLimit')) / count($speedData)) : null,
                ];
            } catch (\Exception $e) {
                Log::error('Roads API failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Snap GPS points to nearest roads
     */
    private function snapToRoads(array $path): ?array
    {
        $pathStr = implode('|', array_map(function($p) {
            return $p['latitude'] . ',' . $p['longitude'];
        }, $path));

        $response = Http::get('https://roads.googleapis.com/v1/snapToRoads', [
            'path' => $pathStr,
            'interpolate' => 'true',
            'key' => $this->apiKey,
        ]);

        $data = $response->json();
        
        return $data['snappedPoints'] ?? null;
    }

    /**
     * Get speed limits for road segments
     */
    private function getSpeedLimits(array $snappedPoints): ?array
    {
        $placeIds = array_map(function($p) {
            return $p['placeId'];
        }, $snappedPoints);

        $response = Http::get('https://roads.googleapis.com/v1/speedLimits', [
            'placeId' => implode('|', array_slice($placeIds, 0, 100)),
            'key' => $this->apiKey,
        ]);

        $data = $response->json();
        
        return $data['speedLimits'] ?? null;
    }

    // ============================================
    // 6. COMPREHENSIVE LOCATION ANALYSIS
    // ============================================

    /**
     * Get complete location intelligence for cleaner recommendation
     */
    public function getComprehensiveLocationAnalysis($originLat, $originLng, $destLat, $destLng): array
    {
        $result = [
            'distance_data' => null,
            'route_data' => null,
            'origin_area' => null,
            'destination_area' => null,
            'area_compatibility' => 50,
            'route_quality_score' => 50,
            'smart_recommendations' => [],
        ];
        
        // 1. Distance & Traffic
        $result['distance_data'] = $this->getDistanceAndETA($originLat, $originLng, $destLat, $destLng);
        
        // 2. Route Quality
        $result['route_data'] = $this->getDirections($originLat, $originLng, $destLat, $destLng);
        if ($result['route_data']) {
            $result['route_quality_score'] = $result['route_data']['route_quality_score'] ?? 50;
        }
        
        // 3. Origin Area (Homeowner)
        $result['origin_area'] = $this->getNearbyPlaces($originLat, $originLng);
        
        // 4. Destination Area (Cleaner)
        $result['destination_area'] = $this->getNearbyPlaces($destLat, $destLng);
        
        // 5. Area Compatibility Score
        $result['area_compatibility'] = $this->calculateAreaCompatibility(
            $result['origin_area'],
            $result['destination_area']
        );
        
        // 6. Smart Recommendations
        $result['smart_recommendations'] = $this->generateSmartRecommendations($result);
        
        return $result;
    }

    /**
     * Calculate how compatible two areas are (cleaner's area vs homeowner's area)
     */
    private function calculateAreaCompatibility(?array $origin, ?array $destination): float
    {
        if (!$origin || !$destination) return 50;
        
        $score = 50;
        
        // Same area type = good match
        if (($origin['area_type'] ?? '') === ($destination['area_type'] ?? '')) {
            $score += 20;
        }
        
        // Same affluence level = good match
        if (($origin['area_affluence'] ?? '') === ($destination['area_affluence'] ?? '')) {
            $score += 15;
        }
        
        // Residential to residential = best match
        if (($origin['area_type'] ?? '') === 'residential' && ($destination['area_type'] ?? '') === 'residential') {
            $score += 10;
        }
        
        // Commercial area = potentially more traffic
        if (($origin['area_type'] ?? '') === 'commercial') {
            $score -= 5;
        }
        
        // High density = more potential delays
        if (($origin['area_density'] ?? '') === 'high') {
            $score -= 5;
        }
        
        return max(0, min(100, $score));
    }

    /**
     * Generate AI-powered smart recommendations
     */
    private function generateSmartRecommendations(array $analysis): array
    {
        $recs = [];
        
        $distance = $analysis['distance_data']['distance_km'] ?? 0;
        $traffic = $analysis['distance_data']['traffic_delay_minutes'] ?? 0;
        $routeQuality = $analysis['route_quality_score'] ?? 50;
        $areaType = $analysis['origin_area']['area_type'] ?? 'unknown';
        $areaDensity = $analysis['origin_area']['area_density'] ?? 'unknown';
        
        // Traffic-based recommendations
        if ($traffic > 15) {
            $recs[] = [
                'type' => 'traffic_warning',
                'icon' => 'fa-traffic-light',
                'message' => 'Heavy traffic detected. Consider scheduling outside rush hour.',
                'impact' => 'negative',
                'score_adjustment' => -10
            ];
        } elseif ($traffic < 5) {
            $recs[] = [
                'type' => 'traffic_good',
                'icon' => 'fa-road',
                'message' => 'Light traffic - quick arrival expected.',
                'impact' => 'positive',
                'score_adjustment' => 5
            ];
        }
        
        // Route quality recommendations
        if ($routeQuality < 40) {
            $recs[] = [
                'type' => 'route_warning',
                'icon' => 'fa-exclamation-triangle',
                'message' => 'Route includes unpaved roads. May be difficult during rain.',
                'impact' => 'negative',
                'score_adjustment' => -15
            ];
        } elseif ($routeQuality > 80) {
            $recs[] = [
                'type' => 'route_good',
                'icon' => 'fa-highway',
                'message' => 'Good road quality with highway access.',
                'impact' => 'positive',
                'score_adjustment' => 10
            ];
        }
        
        // Area type recommendations
        if ($areaType === 'commercial' && $areaDensity === 'high') {
            $recs[] = [
                'type' => 'area_commercial',
                'icon' => 'fa-building',
                'message' => 'Commercial area with high density - parking may be limited.',
                'impact' => 'neutral',
                'score_adjustment' => -5
            ];
        } elseif ($areaType === 'residential') {
            $recs[] = [
                'type' => 'area_residential',
                'icon' => 'fa-home',
                'message' => 'Residential area - easy access and parking available.',
                'impact' => 'positive',
                'score_adjustment' => 5
            ];
        }
        
        // Long distance recommendations
        if ($distance > 20) {
            $recs[] = [
                'type' => 'distance_long',
                'icon' => 'fa-route',
                'message' => 'Cleaner is far away (' . round($distance) . 'km). May affect punctuality.',
                'impact' => 'negative',
                'score_adjustment' => -10
            ];
        } elseif ($distance < 5) {
            $recs[] = [
                'type' => 'distance_close',
                'icon' => 'fa-thumbs-up',
                'message' => 'Cleaner is nearby (' . round($distance, 1) . 'km). Fast arrival guaranteed.',
                'impact' => 'positive',
                'score_adjustment' => 10
            ];
        }
        
        return $recs;
    }

    // ============================================
    // FALLBACK (No API Key)
    // ============================================

    /**
     * Fallback using Haversine formula
     */
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
        $roadFactor = 1.4;
        $distance = $straightDistance * $roadFactor;
        $avgSpeed = 35;
        $durationMinutes = ($distance / $avgSpeed) * 60;
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

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'YOUR_API_KEY';
    }
}

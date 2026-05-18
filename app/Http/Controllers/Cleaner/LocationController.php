<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use App\Models\Cleaner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Update cleaner's current location
     */
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
        ]);

        $cleaner = Auth::user()->cleaner;

        if (!$cleaner) {
            return response()->json([
                'success' => false,
                'message' => 'Cleaner profile not found'
            ], 404);
        }

        // Update location
        $cleaner->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
        ]);

        // Cache location for quick access
        Cache::put("cleaner:{$cleaner->id}:location", [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'updated_at' => now()->toISOString(),
            'accuracy' => $request->accuracy,
            'speed' => $request->speed,
            'heading' => $request->heading,
        ], now()->addMinutes(5));

        // Update Firebase for real-time tracking if enabled
        if ($cleaner->location_sharing_enabled) {
            $this->updateFirebaseLocation($cleaner, $request);
        }

        // Log location update (sampled to avoid excessive logging)
        if (rand(1, 10) === 1) {
            Log::channel('location')->info('Cleaner location updated', [
                'cleaner_id' => $cleaner->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Location updated',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Toggle location sharing on/off
     */
    public function toggleSharing(Request $request)
    {
        $cleaner = Auth::user()->cleaner;

        $newState = !$cleaner->location_sharing_enabled;

        $cleaner->update([
            'location_sharing_enabled' => $newState,
        ]);

        // If turning off sharing, clear location from cache
        if (!$newState) {
            Cache::forget("cleaner:{$cleaner->id}:location");
            
            // Optionally clear current location
            $cleaner->update([
                'current_latitude' => null,
                'current_longitude' => null,
            ]);
        }

        activity()
            ->performedOn($cleaner)
            ->withProperties(['location_sharing' => $newState])
            ->log('location_sharing_toggled');

        return response()->json([
            'success' => true,
            'location_sharing' => $newState,
            'message' => $newState ? 'Location sharing enabled' : 'Location sharing disabled',
        ]);
    }

    /**
     * Get nearby cleaners (for admin monitoring)
     */
    public function getNearbyCleaners(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_km' => 'nullable|numeric|min:1|max:100',
            'status' => 'nullable|in:online,online_busy,offline,scheduled_only',
        ]);

        $radius = $request->radius_km ?? 20;
        $status = $request->status ?? 'online';

        $cleaners = Cleaner::with('user:id,first_name,last_name,phone')
            ->where('location_sharing_enabled', true)
            ->where('availability_status', $status)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->selectRaw("
                *, 
                (6371 * acos(
                    cos(radians(?)) * cos(radians(current_latitude)) * 
                    cos(radians(current_longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(current_latitude))
                )) AS distance_km
            ", [$request->latitude, $request->longitude, $request->latitude])
            ->having('distance_km', '<=', $radius)
            ->orderBy('distance_km')
            ->get();

        return response()->json([
            'success' => true,
            'cleaners' => $cleaners,
            'total' => $cleaners->count(),
            'radius_km' => $radius,
        ]);
    }

    /**
     * Update location in Firebase for real-time tracking
     */
    private function updateFirebaseLocation(Cleaner $cleaner, Request $request): void
    {
        try {
            // Firebase Realtime Database or Firestore update
            // This requires kreait/laravel-firebase package
            
            if (app()->bound('firebase.database')) {
                $database = app('firebase.database');
                
                $database->getReference("cleaner_locations/{$cleaner->id}")
                    ->set([
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'status' => $cleaner->availability_status,
                        'cleaner_id' => $cleaner->cleaner_id,
                        'name' => $cleaner->user->full_name,
                        'updated_at' => now()->toISOString(),
                    ]);
            }
        } catch (\Exception $e) {
            Log::channel('firebase')->error('Firebase location update failed', [
                'cleaner_id' => $cleaner->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
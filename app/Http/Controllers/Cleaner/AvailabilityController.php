<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AvailabilityController extends Controller
{
    /**
     * Toggle cleaner availability status
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'status' => 'required|in:online,offline,online_busy,scheduled_only',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $cleaner = Auth::user()->cleaner;

        if (!$cleaner) {
            return response()->json([
                'success' => false,
                'message' => 'Cleaner profile not found'
            ], 404);
        }

        $newStatus = $request->status;

        $updateData = ['availability_status' => $newStatus];

        // Update location if going online
        if ($newStatus === 'online' && $request->latitude && $request->longitude) {
            $updateData['current_latitude'] = $request->latitude;
            $updateData['current_longitude'] = $request->longitude;
            $updateData['location_sharing_enabled'] = true;
        }

        // Clear location if going offline
        if ($newStatus === 'offline') {
            $updateData['current_latitude'] = null;
            $updateData['current_longitude'] = null;
            $updateData['location_sharing_enabled'] = false;
        }

        $cleaner->update($updateData);

        Log::info('Cleaner status changed', [
            'cleaner_id' => $cleaner->id,
            'new_status' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Status changed to ' . ucfirst(str_replace('_', ' ', $newStatus)),
        ]);
    }

    /**
     * Get current availability status
     */
    public function getStatus()
    {
        $cleaner = Auth::user()->cleaner;

        if (!$cleaner) {
            return response()->json([
                'success' => false,
                'message' => 'Cleaner profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $cleaner->availability_status,
                'is_online' => $cleaner->availability_status === 'online',
                'location_sharing' => $cleaner->location_sharing_enabled,
                'last_updated' => $cleaner->updated_at->diffForHumans(),
            ]
        ]);
    }
}
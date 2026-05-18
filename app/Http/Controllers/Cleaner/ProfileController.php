<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $cleaner = $user->cleaner;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15|unique:users,phone,' . $user->id,
            'city_id' => 'nullable|exists:cities,id',
            'full_address' => 'nullable|string|max:500',
            'max_service_radius_km' => 'nullable|integer|min:5|max:100',
            'service_skills' => 'nullable|array',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $cleaner->update([
            'city_id' => $request->city_id,
            'full_address' => $request->full_address,
            'max_service_radius_km' => $request->max_service_radius_km,
            'service_skills' => $request->service_skills,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
        ]);
    }

    /**
     * Upload profile avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        // Store the avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $avatarUrl = Storage::url($path);

        $user->update(['avatar_url' => $avatarUrl]);

        return response()->json([
            'success' => true,
            'avatar_url' => $avatarUrl,
            'message' => 'Profile picture updated!',
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!',
        ]);
    }

    /**
     * Update shift schedule
     */
    public function updateShift(Request $request)
    {
        $request->validate([
            'shift_start' => 'required|date_format:H:i',
            'shift_end' => 'required|date_format:H:i',
            'working_days' => 'nullable|array',
        ]);

        $cleaner = Auth::user()->cleaner;

        $cleaner->update([
            'shift_start_time' => $request->shift_start,
            'shift_end_time' => $request->shift_end,
            'working_days' => $request->working_days,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift schedule updated!',
        ]);
    }
    /**
 * Save cleaner services and custom prices
 */
public function saveServices(Request $request)
{
    $request->validate([
        'service_skills' => 'nullable|array',
        'custom_prices' => 'nullable|array',
    ]);

    $cleaner = Auth::user()->cleaner;

    if ($cleaner) {
        $cleaner->update([
            'service_skills' => $request->service_skills ?? [],
            'custom_prices' => $request->custom_prices ?? [],
        ]);

        // Recalculate profile completion score
        $completionScore = 50; // Base score
        if (!empty($request->service_skills)) $completionScore += 20;
        if ($cleaner->full_address) $completionScore += 10;
        if ($cleaner->shift_start_time) $completionScore += 10;
        if ($cleaner->working_days) $completionScore += 10;
        
        $cleaner->update(['profile_completion_score' => min(100, $completionScore)]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Services and prices saved successfully!',
    ]);
}
}
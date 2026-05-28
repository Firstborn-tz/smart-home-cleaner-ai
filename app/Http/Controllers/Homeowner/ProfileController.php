<?php

namespace App\Http\Controllers\Homeowner;

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
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|unique:users,phone,' . $user->id,
        ]);
        
        $user->update($request->only(['first_name', 'last_name', 'email', 'phone']));
        
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
            'avatar' => 'required|image|max:2048',
        ]);
        
        $path = $request->file('avatar')->store('avatars', 'public');
        $url = Storage::url($path);
        
        Auth::user()->update(['avatar_url' => $url]);
        
        return response()->json([
            'success' => true,
            'avatar_url' => $url,
            'message' => 'Profile picture updated!',
        ]);
    }

    /**
     * Update home address
     */
    public function updateAddress(Request $request)
    {
        $homeowner = Auth::user()->homeowner;
        
        if (!$homeowner) {
            return response()->json([
                'success' => false,
                'message' => 'Homeowner profile not found',
            ], 404);
        }
        
        $homeowner->update([
            'street' => $request->street,
            'ward' => $request->ward,
            'full_address' => $request->street,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully!',
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
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!',
        ]);
    }
}
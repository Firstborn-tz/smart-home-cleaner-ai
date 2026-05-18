<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BusinessProfileController extends Controller
{
    /**
     * Save business profile
     */
    public function save(Request $request)
    {
        $cleaner = Auth::user()->cleaner;

        if (!$cleaner) {
            return response()->json(['success' => false, 'message' => 'Cleaner profile not found'], 404);
        }

        $cleaner->update([
            'business_name' => $request->business_name,
            'business_description' => $request->business_description,
            'business_phone' => $request->business_phone,
            'business_email' => $request->business_email,
            'years_experience' => $request->years_experience,
            'team_size' => $request->team_size,
            'languages' => $request->languages,
            'certifications' => $request->certifications,
            'service_areas' => $request->service_areas,
            'portfolio_images' => $request->portfolio_images,
        ]);

        return response()->json(['success' => true, 'message' => 'Business profile saved!']);
    }

    /**
     * Upload image (cover or portfolio)
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'type' => 'required|in:cover,portfolio',
        ]);

        $cleaner = Auth::user()->cleaner;
        $path = $request->file('image')->store('business/' . $cleaner->id, 'public');
        $url = Storage::url($path);

        // If cover photo, update cleaner record
        if ($request->type === 'cover') {
            $cleaner->update(['cover_photo' => $url]);
        }

        return response()->json(['success' => true, 'url' => $url]);
    }
}
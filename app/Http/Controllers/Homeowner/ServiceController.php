<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Show the service tracking page
     */
    public function track(Booking $booking)
    {
        $homeowner = Auth::user()->homeowner;
        
        if ($booking->homeowner_id !== $homeowner->id) {
            abort(403, 'Unauthorized');
        }

        $booking->load(['service', 'cleaner.user']);
        
        return view('homeowner.service.track', compact('booking'));
    }

    /**
     * Mark service as complete (homeowner confirms work is done)
     */
    public function markComplete(Booking $booking)
    {
        if ($booking->homeowner_id !== Auth::user()->homeowner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $booking->update([
            'status' => 'completed',
            'completed_at' => now(),
            'service_ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service marked as complete. Please rate your cleaner.',
        ]);
    }

    /**
     * Submit rating and review for cleaner
     */
    public function submitReview(Request $request, Booking $booking)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'complaints' => 'nullable|string|max:1000',
        ]);

        if ($booking->homeowner_id !== Auth::user()->homeowner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Save rating to booking
        $booking->update([
            'cleaner_rating_given' => $request->rating,
            'review_text' => $request->review_text,
        ]);

        // Create review record
        Review::create([
            'booking_id' => $booking->id,
            'reviewer_id' => Auth::user()->homeowner->id,
            'reviewer_type' => 'homeowner',
            'reviewee_id' => $booking->cleaner_id,
            'reviewee_type' => 'cleaner',
            'rating' => $request->rating,
            'body' => $request->review_text,
            'status' => 'approved',
        ]);

        // Update cleaner's average rating
        if ($booking->cleaner_id) {
            $avgRating = Review::where('reviewee_id', $booking->cleaner_id)
                ->where('reviewee_type', 'cleaner')
                ->avg('rating');

            $booking->cleaner->update(['rating' => round($avgRating ?? 0, 2)]);
        }

        // Handle complaints
        if ($request->complaints && $booking->cleaner_id) {
            $booking->cleaner->increment('complaints_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your review!',
        ]);
    }
    /**
 * Verify cleaner's code and start the service
 */
   public function verifyAndStart(Request $request, Booking $booking)
   {
    $request->validate(['verification_code' => 'required|string|size:6']);

    if ($booking->homeowner_id !== Auth::user()->homeowner->id) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($booking->status !== 'cleaner_accepted') {
        return response()->json(['success' => false, 'message' => 'Service cannot be started in current status'], 422);
    }

    // Verify the code using VerificationCodeService
    $verificationService = app(\App\Services\Verification\VerificationCodeService::class);
    $verified = $verificationService->verifyCode($booking, $request->verification_code);

    if (!$verified) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code. Please ask the cleaner for a new code.',
        ], 422);
    }

    // Start the service
    $booking->update([
        'status' => 'in_progress',
        'service_started_at' => now(),
        'verification_completed' => true,
        'verification_completed_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Cleaner verified! Service has started.',
    ]);
 }
}
<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Setting;
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
        
        // Calculate estimated price for display
        $estimatedPrice = 0;
        if ($booking->pricing_model === 'fixed' && $booking->booked_hours) {
            $estimatedPrice = round($booking->booked_hours * ($booking->hourly_rate ?? 0));
        }
        
        return view('homeowner.service.track', compact('booking', 'estimatedPrice'));
    }

    /**
     * Mark service as complete with billing calculation
     */
    public function markComplete(Booking $booking)
    {
        if ($booking->homeowner_id !== Auth::user()->homeowner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($booking->status, ['in_progress', 'cleaner_arrived'])) {
            return response()->json(['success' => false, 'message' => 'Service cannot be completed in current status'], 422);
        }

        $now = now();
        
        // Calculate actual hours from service start (or arrival if not started via verification)
        $startTime = $booking->service_started_at ?? $booking->cleaner_arrived_at ?? $booking->created_at;
        $actualHours = $startTime->diffInMinutes($now) / 60;

        // Calculate billed hours based on pricing model
        if ($booking->pricing_model === 'payg') {
            // Round up to nearest 0.5 hour (30 minutes), minimum 0.5
            $billedHours = ceil($actualHours * 2) / 2;
            if ($billedHours < 0.5) $billedHours = 0.5;
        } elseif ($booking->pricing_model === 'fixed') {
            // Fixed: use booked hours, add overtime if exceeded
            $billedHours = max($booking->booked_hours ?? 0, ceil($actualHours * 2) / 2);
        } else {
            // Fallback: use actual hours rounded up to 0.5
            $billedHours = ceil($actualHours * 2) / 2;
        }

        $hourlyRate = $booking->hourly_rate ?? 0;
        $finalAmount = round($billedHours * $hourlyRate);

        // Calculate commission from admin settings
        $commissionRate = (float) Setting::get('commission_rate', 15);
        $commissionAmount = round($finalAmount * ($commissionRate / 100), 2);
        $cleanerPayout = $finalAmount - $commissionAmount;

        $booking->update([
            'status' => 'completed',
            'completed_at' => $now,
            'service_ended_at' => $now,
            'actual_hours' => round($actualHours, 1),
            'billed_hours' => $billedHours,
            'final_amount' => $finalAmount,
            'total_amount' => $finalAmount,
            'commission_percentage' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'cleaner_payout_amount' => $cleanerPayout,
        ]);

        // Update cleaner's total earnings
        if ($booking->cleaner) {
            $booking->cleaner->update([
                'total_earnings' => ($booking->cleaner->total_earnings ?? 0) + $cleanerPayout,
                'pending_payout' => ($booking->cleaner->pending_payout ?? 0) + $cleanerPayout,
            ]);
        }

        // Update homeowner stats
        if ($booking->homeowner) {
            $booking->homeowner->increment('total_completed_bookings');
        }

        return response()->json([
            'success' => true,
            'message' => 'Service completed. Please rate your cleaner.',
            'actual_hours' => round($actualHours, 1),
            'billed_hours' => $billedHours,
            'hourly_rate' => $hourlyRate,
            'final_amount' => $finalAmount,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'cleaner_payout' => $cleanerPayout,
        ]);
    }

    /**
     * Submit rating and review for cleaner only
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

        if ($booking->cleaner_rating_given) {
            return response()->json(['success' => false, 'message' => 'You have already rated this cleaner'], 422);
        }

        // Save rating to booking
        $booking->update([
            'cleaner_rating_given' => $request->rating,
            'review_text' => $request->review_text,
        ]);

        // Create review record (homeowner reviews cleaner only)
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
            'message' => 'Thank you for your review! Your rating helps improve our AI matching.',
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

        if (!in_array($booking->status, ['cleaner_accepted', 'cleaner_assigned', 'cleaner_arrived'])) {
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

        // Record arrival if not already recorded
        if (!$booking->cleaner_arrived_at) {
            $booking->update(['cleaner_arrived_at' => now()]);
        }

        // Start the service
        $booking->update([
            'status' => 'in_progress',
            'service_started_at' => now(),
            'verification_completed' => true,
            'verification_completed_at' => now(),
            'arrival_confirmed_by' => 'homeowner_code',
            'arrival_verification_code' => $request->verification_code,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cleaner verified! Service has started. Billing timer is now running.',
            'started_at' => now()->toISOString(),
        ]);
    }
}
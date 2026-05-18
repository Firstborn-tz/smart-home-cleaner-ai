<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Verification\VerificationCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    private VerificationCodeService $verificationService;

    public function __construct(VerificationCodeService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Accept a booking request
     */
    public function accept(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Not assigned to you'], 403);
        }

        if (!in_array($booking->status, ['pending', 'cleaner_assigned'])) {
            return response()->json(['success' => false, 'message' => 'Cannot accept in current status'], 422);
        }

        DB::transaction(function () use ($booking, $cleaner) {
            $booking->update([
                'status' => 'cleaner_accepted',
                'cleaner_accepted_at' => now(),
            ]);

            $cleaner->update(['availability_status' => 'online_busy']);

            // Generate verification code for service start
            $this->verificationService->generateCode($booking);
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking accepted! Verification code generated.',
        ]);
    }

    /**
     * Decline a booking
     */
    public function decline(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Not assigned to you'], 403);
        }

        $booking->update(['status' => 'declined', 'cancelled_by' => 'cleaner']);

        return response()->json(['success' => true, 'message' => 'Booking declined']);
    }

    /**
     * Start service (cleaner arrived - needs homeowner verification)
     */
    public function startService(Request $request, Booking $booking)
    {
        $request->validate(['verification_code' => 'required|string|size:6']);
        
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Not assigned to you'], 403);
        }

        // Verify the code (cleaner enters code on homeowner's device)
        $verified = $this->verificationService->verifyCode($booking, $request->verification_code);

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
                'remaining_attempts' => $this->verificationService->getRemainingAttempts($booking),
            ], 422);
        }

        $booking->update([
            'status' => 'in_progress',
            'service_started_at' => now(),
            'verification_completed' => true,
            'verification_completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification successful! Service started.',
        ]);
    }

    /**
     * Complete service (homeowner marks as complete)
     */
    public function completeService(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Not assigned to you'], 403);
        }

        if ($booking->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Service not in progress'], 422);
        }

        DB::transaction(function () use ($booking, $cleaner) {
            $booking->update([
                'status' => 'completed',
                'completed_at' => now(),
                'service_ended_at' => now(),
            ]);

            // Update cleaner stats
            $cleaner->update([
                'availability_status' => 'online',
                'total_completed_jobs' => $cleaner->total_completed_jobs + 1,
                'last_booking_at' => now(),
            ]);

            // Recalculate completion rate
            $totalJobs = $cleaner->total_completed_jobs + $cleaner->total_cancellations;
            if ($totalJobs > 0) {
                $cleaner->update([
                    'completion_rate' => round(($cleaner->total_completed_jobs / $totalJobs) * 100, 2),
                ]);
            }

            // Create commission record
            \App\Models\Commission::create([
                'booking_id' => $booking->id,
                'cleaner_id' => $cleaner->id,
                'service_id' => $booking->service_id,
                'expected_total_amount' => $booking->total_amount,
                'actual_submitted_amount' => 0,
                'remaining_unpaid_amount' => $booking->total_amount,
                'overpayment_amount' => 0,
                'commission_percentage' => $booking->commission_percentage,
                'commission_amount' => $booking->commission_amount,
                'cleaner_balance' => 0,
                'payment_status' => 'pending',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Service completed! Commission record created.',
        ]);
    }
}
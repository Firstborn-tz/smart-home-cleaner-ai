<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Verification\VerificationCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    private VerificationCodeService $verificationService;

    public function __construct(VerificationCodeService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Generate verification code for a booking
     */
    public function generate(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!in_array($booking->status, ['cleaner_accepted', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => 'Code can only be generated during active service'
            ], 422);
        }

        try {
            $code = $this->verificationService->generateCode($booking);

            return response()->json([
                'success' => true,
                'message' => 'Verification code generated successfully',
                'code' => $code,
                'expires_at' => now()->addMinutes(30)->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verify code and complete service
     */
    public function verify(Request $request, Booking $booking)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $verified = $this->verificationService->verifyCode($booking, $request->code);

        if (!$verified) {
            $remaining = $this->verificationService->getRemainingAttempts($booking);
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
                'remaining_attempts' => $remaining,
                'can_regenerate' => $this->verificationService->canRegenerate($booking),
            ], 422);
        }

        DB::transaction(function () use ($booking, $cleaner) {
            $booking->update([
                'status' => 'completed',
                'verification_completed' => true,
                'verification_completed_at' => now(),
                'completed_at' => now(),
                'service_ended_at' => now(),
            ]);

            $cleaner->update([
                'availability_status' => 'online',
                'total_completed_jobs' => $cleaner->total_completed_jobs + 1,
                'last_booking_at' => now(),
            ]);

            $totalJobs = $cleaner->total_completed_jobs + $cleaner->total_cancellations;
            if ($totalJobs > 0) {
                $cleaner->update([
                    'completion_rate' => round(($cleaner->total_completed_jobs / $totalJobs) * 100, 2),
                ]);
            }

            if (!$booking->commission) {
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
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Service completed successfully!',
        ]);
    }

    /**
     * Get verification status
     */
    public function status(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'remaining_attempts' => $this->verificationService->getRemainingAttempts($booking),
                'can_regenerate' => $this->verificationService->canRegenerate($booking),
                'verification_completed' => $booking->verification_completed,
                'booking_status' => $booking->status,
            ]
        ]);
    }
}
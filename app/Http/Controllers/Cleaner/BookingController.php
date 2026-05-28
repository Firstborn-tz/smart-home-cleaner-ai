<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Setting;
use App\Services\CleanerResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected CleanerResponseService $responseService;

    public function __construct(CleanerResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Cleaner accepts a booking
     */
    public function accept(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if (!$cleaner) {
            return response()->json(['success' => false, 'message' => 'Cleaner profile not found'], 404);
        }

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'This request is not for you'], 403);
        }

        $result = $this->responseService->acceptRequest($booking, $cleaner);

        return response()->json($result);
    }

    /**
     * Cleaner declines a booking
     */
    public function decline(Booking $booking, Request $request)
    {
        $cleaner = Auth::user()->cleaner;

        if (!$cleaner) {
            return response()->json(['success' => false, 'message' => 'Cleaner profile not found'], 404);
        }

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'This request is not for you'], 403);
        }

        $reason = $request->input('reason', 'Declined by cleaner');
        $result = $this->responseService->declineRequest($booking, $cleaner, $reason);

        return response()->json($result);
    }

    /**
     * Cleaner confirms arrival at location
     * Uses ETA + 15 min grace window for late detection
     */
    public function confirmArrival(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Not your booking'], 403);
        }

        $now = now();
        
        // Determine expected arrival time
        if ($booking->booking_type === 'scheduled' && $booking->scheduled_at) {
            $expectedTime = $booking->scheduled_at;
        } else {
            // Instant: expected = accepted_at + ETA
            $expectedTime = $booking->cleaner_accepted_at 
                ? $booking->cleaner_accepted_at->addMinutes($booking->estimated_travel_time_minutes ?? 10)
                : $now;
        }

        // Grace window = ETA + 15 minutes
        $graceWindowMinutes = ($booking->estimated_travel_time_minutes ?? 10) + 15;
        $deadlineTime = $expectedTime->copy()->addMinutes($graceWindowMinutes);
        $earlyWindowStart = $booking->booking_type === 'scheduled' 
            ? $expectedTime->copy()->subMinutes(15) 
            : $booking->cleaner_accepted_at;

        // Check if early or late
        $wasEarly = $booking->booking_type === 'scheduled' && $now->lt($earlyWindowStart);
        $wasLate = $now->gt($deadlineTime);
        $minutesEarly = $wasEarly ? $earlyWindowStart->diffInMinutes($now) : 0;
        $minutesLate = $wasLate ? $deadlineTime->diffInMinutes($now) : 0;

        // No-show detection: 45 minutes past deadline
        $isNoShow = $wasLate && $minutesLate >= 45;

        $booking->update([
            'cleaner_arrived_at' => $now,
            'was_early' => $wasEarly,
            'was_late' => $wasLate,
            'minutes_early' => $minutesEarly,
            'minutes_late' => $minutesLate,
            'grace_window_minutes' => $graceWindowMinutes,
        ]);

        if ($isNoShow) {
            $this->responseService->markNoShow($booking);
            return response()->json([
                'success' => false,
                'message' => 'You are 45+ minutes late. Booking marked as no-show.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Arrival confirmed. Waiting for homeowner verification.',
            'was_early' => $wasEarly,
            'was_late' => $wasLate,
            'minutes_early' => $minutesEarly,
            'minutes_late' => $minutesLate,
            'deadline' => $deadlineTime->toISOString(),
            'grace_window_minutes' => $graceWindowMinutes,
        ]);
    }

    /**
     * Cleaner starts service
     */
    public function startService(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Not your booking'], 403);
        }

        if (!$booking->cleaner_arrived_at) {
            return response()->json(['success' => false, 'message' => 'Arrival not confirmed yet'], 422);
        }

        $booking->update([
            'status' => 'in_progress',
            'service_started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service started',
            'started_at' => now()->toISOString(),
        ]);
    }

    /**
     * Cleaner completes service with commission calculation
     */
    public function completeService(Booking $booking)
    {
        $cleaner = Auth::user()->cleaner;

        if ($booking->cleaner_id !== $cleaner->id) {
            return response()->json(['success' => false, 'message' => 'Not your booking'], 403);
        }

        if (!$booking->service_started_at) {
            return response()->json(['success' => false, 'message' => 'Service not started yet'], 422);
        }

        $now = now();
        $actualHours = $booking->service_started_at->diffInMinutes($now) / 60;

        // Calculate billed hours based on pricing model
        if ($booking->pricing_model === 'payg') {
            // Round up to nearest 0.5 hour (30 minutes), minimum 0.5
            $billedHours = ceil($actualHours * 2) / 2;
            if ($billedHours < 0.5) $billedHours = 0.5;
        } else {
            // Fixed: use booked hours, add overtime if exceeded
            $billedHours = max($booking->booked_hours ?? 0, ceil($actualHours * 2) / 2);
        }

        $hourlyRate = $booking->hourly_rate ?? 0;
        $finalAmount = round($billedHours * $hourlyRate);

        // Calculate commission from admin settings
        $commissionRate = (float) Setting::get('commission_rate', 15);
        $commissionAmount = round($finalAmount * ($commissionRate / 100), 2);
        $cleanerPayout = $finalAmount - $commissionAmount;

        $booking->update([
            'status' => 'completed',
            'service_ended_at' => $now,
            'completed_at' => $now,
            'actual_hours' => round($actualHours, 1),
            'billed_hours' => $billedHours,
            'final_amount' => $finalAmount,
            'total_amount' => $finalAmount,
            'commission_percentage' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'cleaner_payout_amount' => $cleanerPayout,
        ]);

        // Update cleaner's total earnings
        $cleaner->update([
            'total_earnings' => ($cleaner->total_earnings ?? 0) + $cleanerPayout,
            'pending_payout' => ($cleaner->pending_payout ?? 0) + $cleanerPayout,
        ]);

        // Recover penalty points for successful completion
        if ($cleaner->availability_penalty > 0) {
            $cleaner->update([
                'availability_penalty' => max(0, ($cleaner->availability_penalty ?? 0) - 5),
                'consecutive_rejections' => max(0, ($cleaner->consecutive_rejections ?? 0) - 1),
            ]);
        }

        // Update homeowner stats
        if ($booking->homeowner) {
            $booking->homeowner->increment('total_completed_bookings');
        }

        return response()->json([
            'success' => true,
            'message' => 'Service completed',
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
     * Get pending requests for this cleaner
     */
    public function pendingRequests()
    {
        $cleaner = Auth::user()->cleaner;

        $requests = Booking::with(['service', 'homeowner.user'])
            ->where('cleaner_id', $cleaner->id)
            ->where('status', 'pending')
            ->where('timeout_at', '>', now())
            ->latest()
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'service_name' => $booking->service->name ?? 'Unknown',
                    'homeowner_name' => $booking->homeowner->user->full_name ?? 'Unknown',
                    'address' => $booking->service_address,
                    'pricing_model' => $booking->pricing_model,
                    'hourly_rate' => $booking->hourly_rate,
                    'booked_hours' => $booking->booked_hours,
                    'distance_km' => $booking->distance_km,
                    'eta_minutes' => $booking->estimated_travel_time_minutes,
                    'time_left_seconds' => max(0, $booking->timeout_at ? now()->diffInSeconds($booking->timeout_at, false) : 0),
                    'timeout_seconds' => $booking->response_timeout_seconds ?? 120,
                    'created_at' => $booking->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'requests' => $requests,
            'count' => $requests->count(),
        ]);
    }
}
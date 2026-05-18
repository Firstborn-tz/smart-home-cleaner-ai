<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Cleaner;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * List all commissions with filters
     */
    public function index(Request $request)
    {
        $query = Commission::with(['booking.service', 'cleaner.user'])
            ->when($request->status, function ($q, $status) {
                return $q->where('payment_status', $status);
            })
            ->when($request->cleaner_id, function ($q, $cleanerId) {
                return $q->where('cleaner_id', $cleanerId);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                return $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                return $q->whereDate('created_at', '<=', $dateTo);
            });

        $commissions = $query->latest()->paginate(20);
        $cleaners = Cleaner::with('user')->get();

        // Summary statistics
        $summary = [
            'total_expected' => Commission::sum('expected_total_amount'),
            'total_submitted' => Commission::sum('actual_submitted_amount'),
            'total_remaining' => Commission::sum('remaining_unpaid_amount'),
            'total_overpayment' => Commission::sum('overpayment_amount'),
            'pending_count' => Commission::where('payment_status', 'pending')->count(),
            'partial_count' => Commission::where('payment_status', 'partially_paid')->count(),
            'paid_count' => Commission::where('payment_status', 'fully_paid')->count(),
            'overpaid_count' => Commission::where('payment_status', 'overpaid')->count(),
        ];

        return view('admin.commissions.index', compact('commissions', 'cleaners', 'summary'));
    }

    /**
     * Show single commission details
     */
    public function show(Commission $commission)
    {
        $commission->load(['booking.service', 'booking.homeowner.user', 'cleaner.user', 'recordedBy']);
        
        return view('admin.commissions.show', compact('commission'));
    }

    /**
     * Record a payment from cleaner
     */
    public function recordPayment(Request $request, Commission $commission)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = $request->amount;

        DB::transaction(function () use ($commission, $amount, $request) {
            // Update submitted amount
            $newSubmitted = $commission->actual_submitted_amount + $amount;
            $remaining = $commission->expected_total_amount - $newSubmitted;
            $overpayment = $remaining < 0 ? abs($remaining) : 0;

            // Determine payment status
            if ($remaining <= 0 && $overpayment > 0) {
                $status = 'overpaid';
            } elseif ($remaining <= 0) {
                $status = 'fully_paid';
            } elseif ($newSubmitted > 0) {
                $status = 'partially_paid';
            } else {
                $status = 'pending';
            }

            // Calculate cleaner balance
            $cleanerBalance = $newSubmitted - $commission->commission_amount;
            if ($cleanerBalance < 0) $cleanerBalance = 0;

            // Build payment history
            $paymentHistory = $commission->payment_history ?? [];
            $paymentHistory[] = [
                'amount' => $amount,
                'date' => now()->toISOString(),
                'recorded_by' => auth()->id(),
                'notes' => $request->notes,
            ];

            $commission->update([
                'actual_submitted_amount' => $newSubmitted,
                'remaining_unpaid_amount' => max(0, $remaining),
                'overpayment_amount' => $overpayment,
                'cleaner_balance' => $cleanerBalance,
                'payment_status' => $status,
                'payment_history' => $paymentHistory,
                'recorded_by_admin_id' => auth()->id(),
                'last_payment_at' => now(),
                'admin_notes' => $request->notes,
            ]);

            // Update cleaner's pending payout
            $totalBalance = Commission::where('cleaner_id', $commission->cleaner_id)
                ->whereIn('payment_status', ['fully_paid', 'overpaid', 'partially_paid'])
                ->sum('cleaner_balance');

            Cleaner::where('id', $commission->cleaner_id)->update([
                'pending_payout' => $totalBalance,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment of TZS ' . number_format($amount) . ' recorded successfully',
            'commission' => $commission->fresh(),
        ]);
    }

    /**
     * Get cleaner commission summary
     */
    public function cleanerSummary(Cleaner $cleaner)
    {
        $commissions = Commission::where('cleaner_id', $cleaner->id)->get();

        $summary = [
            'cleaner_name' => $cleaner->user->full_name,
            'total_expected' => $commissions->sum('expected_total_amount'),
            'total_submitted' => $commissions->sum('actual_submitted_amount'),
            'total_remaining' => $commissions->sum('remaining_unpaid_amount'),
            'total_overpayment' => $commissions->sum('overpayment_amount'),
            'total_balance' => $commissions->sum('cleaner_balance'),
            'pending_count' => $commissions->where('payment_status', 'pending')->count(),
            'paid_count' => $commissions->where('payment_status', 'fully_paid')->count(),
            'total_commissions' => $commissions->count(),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Generate commissions for completed bookings
     */
    public function generateCommissions()
    {
        $bookings = Booking::where('status', 'completed')
            ->whereDoesntHave('commission')
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            if ($booking->cleaner_id) {
                Commission::create([
                    'booking_id' => $booking->id,
                    'cleaner_id' => $booking->cleaner_id,
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
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Generated {$count} commission records",
            'count' => $count,
        ]);
    }
}
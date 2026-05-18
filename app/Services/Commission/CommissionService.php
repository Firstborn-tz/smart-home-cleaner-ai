<?php

namespace App\Services\Commission;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Cleaner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Record commission for a completed booking
     * IMPORTANT: No direct payment - admin manually enters amount
     */
    public function createCommission(Booking $booking): Commission
    {
        return Commission::create([
            'booking_id' => $booking->id,
            'cleaner_id' => $booking->cleaner_id,
            'service_id' => $booking->service_id,
            'expected_total_amount' => $booking->total_amount,
            'actual_submitted_amount' => 0.00,
            'remaining_unpaid_amount' => $booking->total_amount,
            'overpayment_amount' => 0.00,
            'commission_percentage' => $booking->commission_percentage,
            'commission_amount' => $booking->commission_amount,
            'cleaner_balance' => 0.00,
            'payment_status' => 'pending',
        ]);
    }
    
    /**
     * Admin manually records amount submitted by cleaner
     */
    public function recordPayment(int $commissionId, float $amount, int $adminId, string $notes = null): Commission
    {
        return DB::transaction(function () use ($commissionId, $amount, $adminId, $notes) {
            $commission = Commission::findOrFail($commissionId);
            
            // Update submitted amount
            $newSubmittedAmount = $commission->actual_submitted_amount + $amount;
            
            // Calculate remaining
            $remaining = $commission->expected_total_amount - $newSubmittedAmount;
            $overpayment = $remaining < 0 ? abs($remaining) : 0;
            
            // Determine payment status
            $status = 'pending';
            if ($remaining <= 0) {
                $status = $overpayment > 0 ? 'overpaid' : 'fully_paid';
            } elseif ($newSubmittedAmount > 0) {
                $status = 'partially_paid';
            }
            
            // Calculate cleaner balance
            $cleanerBalance = $newSubmittedAmount - $commission->commission_amount;
            if ($cleanerBalance < 0) $cleanerBalance = 0;
            
            // Update commission
            $commission->update([
                'actual_submitted_amount' => $newSubmittedAmount,
                'remaining_unpaid_amount' => max(0, $remaining),
                'overpayment_amount' => $overpayment,
                'cleaner_balance' => $cleanerBalance,
                'payment_status' => $status,
                'recorded_by_admin_id' => $adminId,
                'last_payment_at' => now(),
                'admin_notes' => $notes,
            ]);
            
            // Update cleaner's pending payout
            $this->updateCleanerPayout($commission->cleaner_id);
            
            Log::info('Commission payment recorded', [
                'commission_id' => $commissionId,
                'amount' => $amount,
                'admin_id' => $adminId,
                'new_status' => $status,
            ]);
            
            return $commission;
        });
    }
    
    /**
     * Update cleaner's pending payout balance
     */
    protected function updateCleanerPayout(int $cleanerId): void
    {
        $totalBalance = Commission::where('cleaner_id', $cleanerId)
            ->whereIn('payment_status', ['fully_paid', 'overpaid', 'partially_paid'])
            ->sum('cleaner_balance');
        
        Cleaner::where('id', $cleanerId)->update([
            'pending_payout' => $totalBalance,
        ]);
    }
    
    /**
     * Get commission summary for a cleaner
     */
    public function getCleanerCommissionSummary(int $cleanerId): array
    {
        $commissions = Commission::where('cleaner_id', $cleanerId)->get();
        
        return [
            'total_expected' => $commissions->sum('expected_total_amount'),
            'total_submitted' => $commissions->sum('actual_submitted_amount'),
            'total_remaining' => $commissions->sum('remaining_unpaid_amount'),
            'total_overpayment' => $commissions->sum('overpayment_amount'),
            'total_cleaner_balance' => $commissions->sum('cleaner_balance'),
            'pending_count' => $commissions->where('payment_status', 'pending')->count(),
            'paid_count' => $commissions->where('payment_status', 'fully_paid')->count(),
            'partial_count' => $commissions->where('payment_status', 'partially_paid')->count(),
        ];
    }
    
    /**
     * Get city-based commission rates
     */
    public function getCityCommissionRate(int $cityId): float
    {
        // This could be dynamic based on city settings
        $defaultRate = 15.00;
        
        $citySetting = \App\Models\CitySetting::where('city_id', $cityId)
            ->where('setting_key', 'commission_rate')
            ->first();
        
        return $citySetting ? (float) $citySetting->setting_value : $defaultRate;
    }
}
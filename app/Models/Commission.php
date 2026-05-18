<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'cleaner_id',
        'service_id',
        'expected_total_amount',
        'actual_submitted_amount',
        'remaining_unpaid_amount',
        'overpayment_amount',
        'commission_percentage',
        'commission_amount',
        'cleaner_balance',
        'payment_status',
        'payment_history',
        'admin_notes',
        'recorded_by_admin_id',
        'last_payment_at',
    ];

    protected $casts = [
        'expected_total_amount' => 'decimal:2',
        'actual_submitted_amount' => 'decimal:2',
        'remaining_unpaid_amount' => 'decimal:2',
        'overpayment_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'cleaner_balance' => 'decimal:2',
        'payment_history' => 'array',
        'last_payment_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function cleaner()
    {
        return $this->belongsTo(Cleaner::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_admin_id');
    }

    /**
     * Check if commission is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'fully_paid';
    }

    /**
     * Check if commission has overpayment
     */
    public function hasOverpayment(): bool
    {
        return $this->overpayment_amount > 0;
    }

    /**
     * Scope pending commissions
     */
    public function scopePending($query)
    {
        return $query->whereIn('payment_status', ['pending', 'partially_paid']);
    }

    /**
     * Scope commissions by cleaner
     */
    public function scopeForCleaner($query, int $cleanerId)
    {
        return $query->where('cleaner_id', $cleanerId);
    }
}
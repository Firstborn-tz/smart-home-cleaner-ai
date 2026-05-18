<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    protected $fillable = [
        'booking_id',
        'code_hash',
        'expires_at',
        'verified_at',
        'is_used',
        'generation_count',
        'attempt_count',
        'last_attempt_at',
        'delivery_method',
        'delivery_confirmed',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'is_used' => 'boolean',
        'delivery_confirmed' => 'boolean',
        'generation_count' => 'integer',
        'attempt_count' => 'integer',
    ];

    protected $hidden = [
        'code_hash',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Check if code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if code is still valid
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired() && $this->attempt_count < 3;
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttemptsAttribute(): int
    {
        return max(0, 3 - $this->attempt_count);
    }

    /**
     * Scope unused codes
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope non-expired codes
     */
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', now());
    }
}
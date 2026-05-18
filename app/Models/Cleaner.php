<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Cleaner extends Model
{
    use HasFactory, SoftDeletes;
    // Removed: LogsActivity trait - install spatie/laravel-activitylog to enable

    protected $fillable = [
        'user_id',
        'cleaner_id',
        'city_id',
        'availability_status',
        'is_verified',
        'background_checked',
        'verified_at',
        'registration_status', 
        'registration_notes',
        'rating',
        'total_completed_jobs',
        'total_cancellations',
        'total_no_shows',
        'completion_rate',
        'cancellation_rate',
        'complaints_count',
        'experience_days_active',
        'avg_response_time_seconds',
        'price_competitiveness',
        'profile_completion_score',
        'success_rate',
        'repeat_customer_rate',
        'avg_job_duration_minutes',
        'last_booking_at',
        'current_latitude',
        'current_longitude',
        'google_place_id',
        'region',
        'district',
        'ward',
        'street',
        'full_address',
        'location_sharing_enabled',
        'service_skills',
        'certifications',
        'languages',
        'wallet_balance',
        'total_earnings',
        'pending_payout',
        'shift_start_time',
        'shift_end_time',
        'max_service_radius_km',
        'working_days',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'background_checked' => 'boolean',
        'verified_at' => 'datetime',
        'rating' => 'decimal:2',
        'total_completed_jobs' => 'integer',
        'total_cancellations' => 'integer',
        'completion_rate' => 'decimal:2',
        'cancellation_rate' => 'decimal:2',
        'current_latitude' => 'decimal:7',
        'current_longitude' => 'decimal:7',
        'location_sharing_enabled' => 'boolean',
        'service_skills' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
        'working_days' => 'array',
        'last_booking_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($cleaner) {
            $cleaner->cleaner_id = 'CLN-' . strtoupper(Str::random(8));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function reviews()
    {
    return $this->morphMany(\App\Models\Review::class, 'reviewee');
    }

    public function isOnline(): bool
    {
        return $this->availability_status === 'online';
    }

    public function isAvailable(): bool
    {
        return in_array($this->availability_status, ['online', 'online_busy', 'scheduled_only']);
    }

    public function isEligibleForInstantBooking(): bool
    {
        return $this->availability_status === 'online' && 
               $this->is_verified && 
               $this->location_sharing_enabled;
    }

    public function scopeOnline($query)
    {
        return $query->where('availability_status', 'online');
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('availability_status', ['online', 'online_busy', 'scheduled_only']);
    }

    public function scopeInCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
    /**
 * Check if cleaner registration is pending
 */
     public function isPending(): bool
    {
    return !$this->is_verified && $this->registration_status !== 'rejected';
    }

/**
 * Check if cleaner was rejected
 */
public function isRejected(): bool
{
    return $this->registration_status === 'rejected';
}


    public function scopeWithinRadius($query, $latitude, $longitude, $radiusKm)
    {
        // Haversine formula for initial filtering
        return $query->selectRaw("
            *, 
            (6371 * acos(
                cos(radians(?)) * cos(radians(current_latitude)) * 
                cos(radians(current_longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(current_latitude))
            )) AS distance_km
        ", [$latitude, $longitude, $latitude])
        ->having('distance_km', '<=', $radiusKm)
        ->orderBy('distance_km');
    }
}
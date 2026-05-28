<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Cleaner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'cleaner_id', 'city_id', 'availability_status',
        'is_verified', 'background_checked', 'verified_at',
        'rating', 'total_completed_jobs', 'total_cancellations', 'total_no_shows',
        'completion_rate', 'cancellation_rate', 'complaints_count',
        'experience_days_active', 'avg_response_time_seconds',
        'price_competitiveness', 'profile_completion_score',
        'success_rate', 'repeat_customer_rate', 'avg_job_duration_minutes',
        'last_booking_at', 'current_latitude', 'current_longitude',
        'google_place_id', 'region', 'district', 'ward', 'street',
        'full_address', 'location_sharing_enabled',
        'service_skills', 'custom_prices', 'certifications', 'languages',
        'wallet_balance', 'total_earnings', 'pending_payout',
        'shift_start_time', 'shift_end_time', 'max_service_radius_km',
        'working_days', 'business_name', 'business_description',
        'business_phone', 'business_email', 'years_experience', 'team_size',
        'cover_photo', 'portfolio_images', 'service_areas',
        'registration_status', 'registration_notes', 'national_id',
        'date_of_birth', 'gender', 'has_equipment', 'bio', 'cleaning_tools',        
        'acceptance_rate', 'rejection_rate', 'consecutive_rejections',
        'availability_penalty', 'last_active_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean', 'background_checked' => 'boolean',
        'verified_at' => 'datetime', 'rating' => 'decimal:2',
        'total_completed_jobs' => 'integer', 'total_cancellations' => 'integer',
        'completion_rate' => 'decimal:2', 'cancellation_rate' => 'decimal:2',
        'current_latitude' => 'decimal:7', 'current_longitude' => 'decimal:7',
        'location_sharing_enabled' => 'boolean',
        'service_skills' => 'array', 'custom_prices' => 'array',
        'certifications' => 'array', 'languages' => 'array',
        'working_days' => 'array', 'service_areas' => 'array',
        'portfolio_images' => 'array', 'cleaning_tools' => 'array',
        'last_booking_at' => 'datetime',        'acceptance_rate' => 'decimal:2',
        'rejection_rate' => 'decimal:2',
        'consecutive_rejections' => 'integer',
        'availability_penalty' => 'integer',
        'last_active_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($cleaner) {
            $cleaner->cleaner_id = 'CLN-' . strtoupper(Str::random(8));
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function city() { return $this->belongsTo(City::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function commissions() { return $this->hasMany(Commission::class); }
    public function reviews() { return $this->hasMany(Review::class); }

    public function isOnline(): bool { return $this->availability_status === 'online'; }
    public function isAvailable(): bool { return in_array($this->availability_status, ['online', 'online_busy', 'scheduled_only']); }
    public function isEligibleForInstantBooking(): bool { return $this->isOnline() && $this->is_verified && $this->location_sharing_enabled; }

    public function scopeOnline($query) { return $query->where('availability_status', 'online'); }
    public function scopeAvailable($query) { return $query->whereIn('availability_status', ['online', 'online_busy', 'scheduled_only']); }
    public function scopeInCity($query, $cityId) { return $query->where('city_id', $cityId); }
    public function scopeVerified($query) { return $query->where('is_verified', true); }
}

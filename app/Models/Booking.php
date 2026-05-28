<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use SoftDeletes;
    // Removed LogsActivity - install spatie/laravel-activitylog if needed

        protected $fillable = [
        'booking_number', 'uuid', 'booking_type', 'pricing_model',
        'booked_hours', 'hourly_rate', 'actual_hours', 'billed_hours',
        'final_amount', 'status',
        'homeowner_id', 'cleaner_id', 'accepted_cleaner_id',
        'service_id', 'city_id',
        'service_latitude', 'service_longitude', 'google_place_id',
        'service_address', 'district', 'ward', 'street',
        'location_details',
        'attempted_cleaners', 'attempt_count',
        'distance_km', 'estimated_travel_time_minutes', 'traffic_delay_minutes',
        'route_quality_score',
        'scheduled_at', 'completed_at', 'cleaner_assigned_at',
        'cleaner_accepted_at', 'cleaner_notified_at', 'cleaner_responded_at',
        'cleaner_departed_at', 'cleaner_arrived_at',
        'arrival_confirmed_by', 'arrival_verification_code',
        'was_early', 'was_late', 'minutes_early', 'minutes_late',
        'grace_window_minutes',
        'service_started_at', 'service_ended_at',
        'ai_recommendation_score', 'ai_feature_scores',
        'ai_recommendations_list', 'ai_rank_position',
        'service_base_price', 'instant_booking_fee', 'distance_fee',
        'weekend_premium', 'peak_hour_surcharge', 'total_amount',
        'commission_percentage', 'commission_amount', 'cleaner_payout_amount',
        'special_instructions', 'additional_requirements', 'access_instructions',
        'verification_code_hash', 'verification_completed', 'verification_completed_at',
        'cleaner_rating_given', 'homeowner_rating_given',
        'review_text', 'review_tags',
        'response_timeout_seconds', 'timeout_at', 'retry_count', 'max_retry_attempts',
        'cancelled_by', 'cancellation_reason', 'cancellation_fee',
    ];

        protected $casts = [
        'service_latitude' => 'decimal:7',
        'service_longitude' => 'decimal:7',
        'booked_hours' => 'decimal:1',
        'hourly_rate' => 'decimal:2',
        'actual_hours' => 'decimal:1',
        'billed_hours' => 'decimal:1',
        'final_amount' => 'decimal:2',
        'location_details' => 'array',
        'attempted_cleaners' => 'array',
        'ai_feature_scores' => 'array',
        'ai_recommendations_list' => 'array',
        'additional_requirements' => 'array',
        'access_instructions' => 'array',
        'review_tags' => 'array',
        'verification_completed' => 'boolean',
        'was_early' => 'boolean',
        'was_late' => 'boolean',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'cleaner_assigned_at' => 'datetime',
        'cleaner_accepted_at' => 'datetime',
        'cleaner_notified_at' => 'datetime',
        'cleaner_responded_at' => 'datetime',
        'cleaner_departed_at' => 'datetime',
        'cleaner_arrived_at' => 'datetime',
        'service_started_at' => 'datetime',
        'service_ended_at' => 'datetime',
        'timeout_at' => 'datetime',
        'verification_completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($booking) {
            $booking->uuid = (string) Str::uuid();
            $booking->booking_number = 'BKG-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd');
        });
    }

    public function homeowner()
    {
        return $this->belongsTo(Homeowner::class);
    }

    public function cleaner()
    {
        return $this->belongsTo(Cleaner::class);
    }

    public function acceptedCleaner()
    {
        return $this->belongsTo(Cleaner::class, 'accepted_cleaner_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function bookingHistory()
    {
        return $this->hasMany(BookingHistory::class);
    }

    public function isInstant(): bool
    {
        return $this->booking_type === 'instant';
    }

    public function isScheduled(): bool
    {
        return $this->booking_type === 'scheduled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'searching_cleaner', 'cleaner_found']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }


    public function isActive(): bool
    {
        return in_array($this->status, [
            'cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route',
            'cleaner_arrived', 'in_progress'
        ]);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Homeowner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'homeowner_id',
        'region',
        'district',
        'ward',
        'street',
        'full_address',
        'latitude',
        'longitude',
        'google_place_id',
        'saved_addresses',
        'favorite_cleaners',
        'rating',
        'total_bookings',
        'total_completed_bookings',
        'total_cancellations',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'saved_addresses' => 'array',
        'favorite_cleaners' => 'array',
        'rating' => 'decimal:2',
        'total_bookings' => 'integer',
        'total_completed_bookings' => 'integer',
        'total_cancellations' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($homeowner) {
            $homeowner->homeowner_id = 'HMO-' . strtoupper(Str::random(8));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getCancellationRateAttribute(): float
    {
        if ($this->total_bookings === 0) return 0;
        return round(($this->total_cancellations / $this->total_bookings) * 100, 2);
    }

    /**
     * Check if a cleaner is in favorites
     */
    public function isFavoriteCleaner(int $cleanerId): bool
    {
        return in_array($cleanerId, $this->favorite_cleaners ?? []);
    }

    /**
     * Get the full address as a formatted string
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->ward,
            $this->district,
            $this->region,
        ]);

        return implode(', ', $parts);
    }
}
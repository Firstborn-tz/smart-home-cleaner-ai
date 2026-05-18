<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'region',
        'code',
        'latitude',
        'longitude',
        'service_radius_km',
        'instant_booking_fee_percentage',
        'traffic_multiplier',
        'peak_hours_multiplier',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'peak_hours_multiplier' => 'array',
        'instant_booking_fee_percentage' => 'decimal:2',
        'traffic_multiplier' => 'decimal:2',
    ];

    /**
     * Get all cleaners in this city
     */
    public function cleaners()
    {
        return $this->hasMany(Cleaner::class);
    }

    /**
     * Get all bookings in this city
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope active cities only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the number of online cleaners in this city
     */
    public function getOnlineCleanersCountAttribute()
    {
        return $this->cleaners()->where('availability_status', 'online')->count();
    }
}
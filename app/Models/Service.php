<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'icon',
        'base_price',
        'instant_booking_premium',
        'weekend_premium',
        'holiday_premium',
        'estimated_duration_minutes',
        'min_duration_minutes',
        'max_duration_minutes',
        'required_skills',
        'equipment_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'instant_booking_premium' => 'decimal:2',
        'weekend_premium' => 'decimal:2',
        'holiday_premium' => 'decimal:2',
        'required_skills' => 'array',
        'equipment_required' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Calculate total price based on booking type
     */
    public function calculatePrice(string $bookingType, array $options = []): float
    {
        $price = $this->base_price;

        if ($bookingType === 'instant') {
            $price += $this->instant_booking_premium;
        }

        if (($options['is_weekend'] ?? false) && $this->weekend_premium > 0) {
            $price += $this->weekend_premium;
        }

        if (($options['is_holiday'] ?? false) && $this->holiday_premium > 0) {
            $price += $this->holiday_premium;
        }

        return $price;
    }

    /**
     * Scope active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope services by category
     */
    public function scopeInCategory($query, $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }
}
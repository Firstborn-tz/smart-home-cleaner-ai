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
     * Get the cleaner's custom price for this service
     * Used when displaying prices to homeowners
     */
    public function getCleanerPrice($cleanerId): ?float
    {
        $cleaner = Cleaner::find($cleanerId);
        if (!$cleaner || !$cleaner->custom_prices) {
            return null;
        }

        $customPrices = is_string($cleaner->custom_prices) 
            ? json_decode($cleaner->custom_prices, true) 
            : $cleaner->custom_prices;

        return $customPrices[$this->id] ?? null;
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name', 'code', 'is_active', 'allow_registration'];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_registration' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAllowsRegistration($query)
    {
        return $query->where('allow_registration', true)->where('is_active', true);
    }

    /**
     * Check if a region name is allowed for registration
     */
    public static function isRegionAllowed(string $regionName): bool
    {
        $region = self::where('name', 'like', "%{$regionName}%")
            ->orWhere('code', 'like', "%{$regionName}%")
            ->first();

        if (!$region) {
            return true; // If region not in database, allow by default
        }

        return $region->is_active && $region->allow_registration;
    }
}
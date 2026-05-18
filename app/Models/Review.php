<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'reviewer_id',
        'reviewer_type',
        'reviewee_id',
        'reviewee_type',
        'rating',
        'title',
        'body',
        'tags',
        'is_verified',
        'is_featured',
        'helpful_count',
        'reported_count',
        'status',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'tags' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'helpful_count' => 'integer',
        'reported_count' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer()
{
    return $this->morphTo();
}

    public function reviewee()
    {
        if ($this->reviewee_type === 'cleaner') {
            return $this->belongsTo(Cleaner::class, 'reviewee_id');
        }
        return $this->belongsTo(Homeowner::class, 'reviewee_id');
    }

    /**
     * Scope approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope featured reviews
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get formatted rating as stars
     */
    public function getStarsHtmlAttribute(): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= round($this->rating) 
                ? '<i class="fas fa-star text-yellow-400"></i>' 
                : '<i class="far fa-star text-gray-300"></i>';
        }
        return $html;
    }
}
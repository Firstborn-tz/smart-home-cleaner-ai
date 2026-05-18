<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'user_type',
        'avatar_url',
        'firebase_uid',
        'device_token',
        'fcm_token',
        'status',
        'last_login_at',
        'last_login_ip',
        'is_online',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    public function cleaner()
    {
        return $this->hasOne(Cleaner::class);
    }

    public function homeowner()
    {
        return $this->hasOne(Homeowner::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isAdmin(): bool
    {
        return in_array($this->user_type, ['admin', 'super_admin']);
    }

    public function isCleaner(): bool
    {
        return $this->user_type === 'cleaner';
    }

    public function isHomeowner(): bool
    {
        return $this->user_type === 'homeowner';
    }

    public function getProfileCompletionPercentageAttribute(): float
    {
        $fields = ['email', 'phone', 'avatar_url'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }
        
        return ($completed / count($fields)) * 100;
    }
}
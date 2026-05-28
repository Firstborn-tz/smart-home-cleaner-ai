<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'icon',
        'action_url',
        'read_at',
        'sent_at',
        'channel',
        'status',
        'priority',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'data' => 'array',
    ];
}

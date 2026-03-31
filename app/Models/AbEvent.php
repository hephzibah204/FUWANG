<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbEvent extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'experiment',
        'variant',
        'event_name',
        'page',
        'meta',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}


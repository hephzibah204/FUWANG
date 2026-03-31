<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailWebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'event_type',
        'message_id',
        'recipient',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}


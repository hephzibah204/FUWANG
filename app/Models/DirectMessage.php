<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'message',
        'channels',
        'audience_type',
        'audience',
        'status',
        'recipient_count',
        'delivered_count',
        'failed_count',
        'sent_at',
    ];

    protected $casts = [
        'channels' => 'array',
        'audience' => 'array',
        'sent_at' => 'datetime',
    ];
}


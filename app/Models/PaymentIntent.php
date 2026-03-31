<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentIntent extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'gateway',
        'amount_expected',
        'currency',
        'status',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'amount_expected' => 'float',
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];
}


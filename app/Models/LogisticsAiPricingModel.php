<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsAiPricingModel extends Model
{
    protected $fillable = [
        'version',
        'feature_keys',
        'weights',
        'multiplier',
        'metrics',
        'trained_at',
        'is_active',
    ];

    protected $casts = [
        'feature_keys' => 'array',
        'weights' => 'array',
        'metrics' => 'array',
        'trained_at' => 'datetime',
        'is_active' => 'boolean',
        'multiplier' => 'decimal:6',
    ];
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsInventoryItem extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'description',
        'quantity',
        'location',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_active' => 'boolean',
    ];
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsCenter extends Model
{
    protected $fillable = [
        'name',
        'type',
        'state',
        'city',
        'address',
        'lat',
        'lng',
        'availability_status',
        'is_active',
        'capacity_per_day',
        'current_load',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_active' => 'boolean',
        'capacity_per_day' => 'integer',
        'current_load' => 'integer',
    ];
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMetric extends Model
{
    protected $fillable = [
        'metric_key',
        'metric_value',
    ];

    protected $casts = [
        'metric_value' => 'array',
    ];
}

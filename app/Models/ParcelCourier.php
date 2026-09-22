<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelCourier extends Model
{
    protected $fillable = [
        'name',
        'adapter_class',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

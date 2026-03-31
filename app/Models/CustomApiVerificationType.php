<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomApiVerificationType extends Model
{
    protected $fillable = [
        'custom_api_id',
        'type_key',
        'label',
        'price',
        'status',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'status' => 'boolean',
        'meta' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(CustomApi::class, 'custom_api_id');
    }
}


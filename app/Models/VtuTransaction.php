<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VtuTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'custom_api_id',
        'service_type',
        'direction',
        'amount',
        'fee',
        'total',
        'transaction_id',
        'status',
        'request_payload',
        'response_payload',
        'provider_reference',
        'error_message',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'amount' => 'float',
        'fee' => 'float',
        'total' => 'float',
    ];
}


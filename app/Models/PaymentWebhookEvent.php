<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'event_type',
        'provider_event_id',
        'reference',
        'email',
        'amount',
        'currency',
        'signature_valid',
        'signature',
        'payload',
        'processing_status',
        'processing_error',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];
}


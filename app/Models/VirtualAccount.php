<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gateway',
        'account_number',
        'bank_name',
        'account_name',
        'currency',
        'status',
        'reference',
        'provider_customer_reference',
        'provider_account_reference',
        'meta',
        'error_message',
        'activated_at',
        'last_synced_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'activated_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


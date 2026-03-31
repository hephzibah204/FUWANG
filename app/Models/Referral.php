<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'referral_code',
        'status',
        'registered_at',
        'first_funded_at',
        'reward_amount',
        'reward_status',
        'reward_transaction_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'registered_at' => 'datetime',
        'first_funded_at' => 'datetime',
        'reward_amount' => 'decimal:2',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}


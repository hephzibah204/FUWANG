<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'referral_id',
        'actor_user_id',
        'referrer_user_id',
        'referred_user_id',
        'action',
        'status',
        'message',
        'context',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
}


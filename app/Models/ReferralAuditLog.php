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
        'user_id',
        'action',
        'created_at',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
}

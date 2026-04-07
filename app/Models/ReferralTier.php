<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'commission_rate',
        'minimum_referrals',
    ];
}

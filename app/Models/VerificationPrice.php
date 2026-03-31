<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VerificationPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'nin_by_nin_price',
        'nin_by_number_price',
        'nin_by_demography_price',
        'bvn_by_bvn',
        'bvn_by_number',
        'verify_by_tracking_id',
        'validation_price',
        'ipe_clearance_price',
        'personalization_price',
    ];
}

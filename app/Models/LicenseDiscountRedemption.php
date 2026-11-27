<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseDiscountRedemption extends Model
{
    protected $fillable = [
        'license_discount_code_id',
        'user_id',
        'agent_id',
        'reference',
        'amount_saved',
        'status',
    ];

    protected $casts = [
        'amount_saved' => 'decimal:2',
    ];

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(LicenseDiscountCode::class, 'license_discount_code_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(EnrollmentAgent::class, 'agent_id');
    }
}

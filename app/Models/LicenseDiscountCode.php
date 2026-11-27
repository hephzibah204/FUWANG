<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseDiscountCode extends Model
{
    protected $fillable = [
        'code',
        'percent',
        'max_uses',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'percent' => 'integer',
        'max_uses' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(LicenseDiscountRedemption::class);
    }

    public function completedRedemptions(): HasMany
    {
        return $this->redemptions()->where('status', 'completed');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    /** Total reservations (pending + completed) counting against max_uses. */
    public function reservedCount(): int
    {
        return $this->redemptions()->count();
    }
}

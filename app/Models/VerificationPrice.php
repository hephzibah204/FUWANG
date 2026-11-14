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
        'agent_nin_by_nin_price',
        'agent_nin_by_number_price',
        'agent_nin_by_demography_price',
        'agent_verify_by_tracking_id',
        'agent_validation_price',
        'agent_ipe_clearance_price',
        'agent_personalization_price',
        'agent_bvn_by_bvn',
        'agent_bvn_by_number',
    ];

    /**
     * Resolve the price for a service key, applying the agent discount/override if requested.
     */
    public function getPriceForService(string $serviceKey, bool $isAgent = false, ?float $fallback = null): float
    {
        $stdCol = $serviceKey;
        $agentCol = 'agent_' . $serviceKey;

        $standardPrice = isset($this->{$stdCol}) && is_numeric($this->{$stdCol})
            ? (float) $this->{$stdCol}
            : ($fallback ?? 0.0);

        if (!$isAgent) {
            return $standardPrice;
        }

        // Explicit agent price override
        if (isset($this->{$agentCol}) && is_numeric($this->{$agentCol}) && (float) $this->{$agentCol} > 0) {
            return (float) $this->{$agentCol};
        }

        // Default 15% discount for enrollment agents
        $discountPct = (float) SystemSetting::get('agent_discount_percentage', 15.0);
        $factor = max(0.0, 1.0 - ($discountPct / 100.0));

        return round($standardPrice * $factor, 2);
    }
}

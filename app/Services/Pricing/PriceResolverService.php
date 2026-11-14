<?php

namespace App\Services\Pricing;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationPrice;

class PriceResolverService
{
    /**
     * Default discount percentage for approved enrollment agents (15%).
     */
    public const DEFAULT_AGENT_DISCOUNT_PERCENT = 15.0;

    /**
     * Resolve the effective price for a user and service.
     */
    public static function resolve(?User $user, string $serviceKey, ?float $fallback = null): float
    {
        $standardPrice = static::getStandardPrice($serviceKey, $fallback);

        if ($user && static::isEligibleAgent($user)) {
            return static::getAgentPrice($serviceKey, $standardPrice);
        }

        return $standardPrice;
    }

    /**
     * Check if a user is an approved enrollment agent eligible for wholesale pricing.
     */
    public static function isEligibleAgent(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return (bool) ($user->enrollmentAgent && $user->enrollmentAgent->isApproved());
    }

    /**
     * Get the standard public price for a service.
     */
    public static function getStandardPrice(string $serviceKey, ?float $fallback = null): float
    {
        $vp = VerificationPrice::first();
        $normalizedKey = static::normalizeKey($serviceKey);

        // Check standard column on verification_prices
        if ($vp && isset($vp->{$normalizedKey}) && is_numeric($vp->{$normalizedKey}) && (float) $vp->{$normalizedKey} > 0) {
            return (float) $vp->{$normalizedKey};
        }

        // Check SystemSetting
        $settingKey = static::getSystemSettingKey($serviceKey);
        if ($settingKey) {
            $val = SystemSetting::get($settingKey);
            if ($val !== null && is_numeric($val) && (float) $val > 0) {
                return (float) $val;
            }
        }

        return $fallback ?? static::getDefaultFallback($serviceKey);
    }

    /**
     * Get the agent wholesale price for a service.
     * Hierarchy: Explicit agent override in verification_prices/SystemSetting -> 15% discount off standard price.
     */
    public static function getAgentPrice(string $serviceKey, ?float $standardPrice = null): float
    {
        if ($standardPrice === null) {
            $standardPrice = static::getStandardPrice($serviceKey);
        }

        $normalizedKey = static::normalizeKey($serviceKey);
        $agentCol = 'agent_' . $normalizedKey;
        $vp = VerificationPrice::first();

        // 1. Explicit agent column override
        if ($vp && isset($vp->{$agentCol}) && is_numeric($vp->{$agentCol}) && (float) $vp->{$agentCol} > 0) {
            return (float) $vp->{$agentCol};
        }

        // 2. Explicit SystemSetting override (e.g. agent_nin_validation_price)
        $agentSettingKey = 'agent_' . static::getSystemSettingKey($serviceKey);
        $settingVal = SystemSetting::get($agentSettingKey);
        if ($settingVal !== null && is_numeric($settingVal) && (float) $settingVal > 0) {
            return (float) $settingVal;
        }

        // 3. Dynamic 15% discount policy for enrollment agents
        $discountPct = (float) SystemSetting::get('agent_discount_percentage', self::DEFAULT_AGENT_DISCOUNT_PERCENT);
        if ($discountPct > 0 && $standardPrice > 0) {
            $factor = max(0.0, 1.0 - ($discountPct / 100.0));
            return round($standardPrice * $factor, 2);
        }

        return (float) $standardPrice;
    }

    /**
     * Get a map of all standard and agent prices for NIN Suite services.
     *
     * @return array<string, array{standard: float, agent: float, effective: float, is_discounted: bool}>
     */
    public static function getNinSuitePrices(?User $user = null): array
    {
        $isAgent = static::isEligibleAgent($user);
        $keys = [
            'nin' => 'nin_by_nin_price',
            'phone' => 'nin_by_number_price',
            'demographic' => 'nin_by_demography_price',
            'tracking' => 'verify_by_tracking_id',
            'validation' => 'validation_price',
            'selfie' => 'nin_face_verification_price',
            'clearance' => 'ipe_clearance_price',
            'personalization' => 'personalization_price',
            'modification' => 'nin_modification_price',
        ];

        $result = [];
        foreach ($keys as $alias => $col) {
            $std = static::getStandardPrice($col);
            $agent = static::getAgentPrice($col, $std);
            $effective = $isAgent ? $agent : $std;

            $result[$alias] = [
                'standard' => $std,
                'agent' => $agent,
                'effective' => $effective,
                'is_discounted' => $isAgent && ($agent < $std),
            ];
        }

        return $result;
    }

    /**
     * Map common aliases to verification_prices table columns.
     */
    private static function normalizeKey(string $key): string
    {
        return match ($key) {
            'nin', 'nin_by_nin' => 'nin_by_nin_price',
            'phone', 'nin_by_number', 'nin_by_phone' => 'nin_by_number_price',
            'demographic', 'nin_by_demography' => 'nin_by_demography_price',
            'tracking', 'verify_by_tracking_id' => 'verify_by_tracking_id',
            'validation', 'nin_validation' => 'validation_price',
            'clearance', 'ipe_clearance' => 'ipe_clearance_price',
            'personalization' => 'personalization_price',
            'bvn', 'bvn_by_bvn' => 'bvn_by_bvn',
            'bvn_phone', 'bvn_by_number' => 'bvn_by_number',
            default => $key,
        };
    }

    /**
     * Map alias to SystemSetting key.
     */
    private static function getSystemSettingKey(string $key): string
    {
        return match ($key) {
            'validation', 'nin_validation', 'validation_price' => 'nin_validation_price',
            'selfie', 'nin_face', 'nin_face_verification_price' => 'nin_face_verification_price',
            'modification', 'nin_modification', 'nin_modification_price' => 'nin_modification_price',
            'bvn_retrieval', 'bvn_retrieval_price' => 'bvn_retrieval_price',
            default => $key,
        };
    }

    /**
     * Hardcoded fallbacks if no database price exists.
     */
    private static function getDefaultFallback(string $key): float
    {
        return match ($key) {
            'nin', 'nin_by_nin', 'nin_by_nin_price' => 200.0,
            'phone', 'nin_by_number', 'nin_by_number_price' => 200.0,
            'demographic', 'nin_by_demography', 'nin_by_demography_price' => 200.0,
            'tracking', 'verify_by_tracking_id' => 300.0,
            'validation', 'nin_validation', 'validation_price' => 200.0,
            'selfie', 'nin_face', 'nin_face_verification_price' => 500.0,
            'clearance', 'ipe_clearance', 'ipe_clearance_price' => 700.0,
            'personalization', 'personalization_price' => 100.0,
            'modification', 'nin_modification', 'nin_modification_price' => 2500.0,
            'bvn', 'bvn_by_bvn' => 100.0,
            'bvn_phone', 'bvn_by_number' => 120.0,
            'bvn_retrieval', 'bvn_retrieval_price' => 800.0,
            default => 100.0,
        };
    }
}

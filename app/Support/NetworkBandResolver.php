<?php

namespace App\Support;

use App\Models\CustomApi;

final class NetworkBandResolver
{
    /**
     * Map known provider identifiers and keywords to stable, deterministic band numbers.
     */
    private const KNOWN_BANDS = [
        // Identity & Verification Providers
        'vuvaa' => 1,
        'dataverify' => 2,
        'robost' => 3,
        'robosttech' => 3,
        'verifyme' => 4,
        'prembly' => 5,
        'identitypass' => 5,
        'qoreid' => 6,

        // Payment & Banking Gateways
        'monnify' => 1,
        'flutterwave' => 2,
        'paystack' => 3,
        'payvessel' => 4,
        'palmpay' => 5,
        'paymentpoint' => 6,

        // VTU & Utility Vendors
        'vtpass' => 1,
        'clubkonnect' => 2,
    ];

    /**
     * Resolve a user-facing band name (e.g., "Band 1", "Band 2", "Band 3", "Band 4")
     * from a CustomApi instance, provider string, or priority/index.
     */
    public static function resolve(mixed $provider, ?int $fallbackIndex = null): string
    {
        if (empty($provider) && $fallbackIndex === null) {
            return 'Band 1';
        }

        if ($fallbackIndex !== null && $fallbackIndex > 0) {
            return 'Band ' . $fallbackIndex;
        }

        if ($provider instanceof CustomApi) {
            if (! empty($provider->priority) && (int) $provider->priority > 0) {
                return 'Band ' . (int) $provider->priority;
            }

            $searchStr = strtolower(($provider->provider_identifier ?: '') . ' ' . ($provider->name ?: ''));
            foreach (self::KNOWN_BANDS as $key => $bandNum) {
                if (str_contains($searchStr, $key)) {
                    return 'Band ' . $bandNum;
                }
            }

            $hash = abs(crc32((string) ($provider->provider_identifier ?: $provider->name ?: $provider->id)));
            return 'Band ' . (($hash % 6) + 1);
        }

        $str = trim((string) $provider);
        if ($str === '') {
            return 'Band 1';
        }

        // If it already starts with "Band X", keep it as "Band X"
        if (preg_match('/^Band\s*(\d+)/i', $str, $matches)) {
            return 'Band ' . $matches[1];
        }

        $lower = strtolower($str);
        foreach (self::KNOWN_BANDS as $key => $bandNum) {
            if (str_contains($lower, $key)) {
                return 'Band ' . $bandNum;
            }
        }

        $hash = abs(crc32($lower));
        return 'Band ' . (($hash % 6) + 1);
    }
}

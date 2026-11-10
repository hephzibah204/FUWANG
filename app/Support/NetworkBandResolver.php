<?php

namespace App\Support;

use App\Models\CustomApi;

final class NetworkBandResolver
{
    /**
     * Recognized payment gateways whose names are public, user-trusted brands
     * and are explicitly allowed to be shown on the user dashboard.
     */
    public const PAYMENT_GATEWAYS = [
        'monnify' => 'Monnify',
        'flutterwave' => 'Flutterwave',
        'paystack' => 'Paystack',
        'payvessel' => 'PayVessel',
        'palmpay' => 'PalmPay',
        'paymentpoint' => 'PaymentPoint',
    ];

    /**
     * Map internal API verification and VTU provider identifiers to distinct band numbers.
     */
    private const KNOWN_BANDS = [
        // Identity & Verification API Providers
        'vuvaa' => 1,
        'dataverify' => 2,
        'robost' => 3,
        'robosttech' => 3,
        'verifyme' => 4,
        'prembly' => 5,
        'identitypass' => 5,
        'qoreid' => 6,

        // VTU & Utility API Vendors
        'vtpass' => 1,
        'clubkonnect' => 2,
    ];

    /**
     * Determine if a given provider is a payment gateway.
     */
    public static function isPaymentGateway(mixed $provider): bool
    {
        return self::getPaymentGatewayLabel($provider) !== null;
    }

    /**
     * Get the formatted payment gateway label if applicable.
     */
    public static function getPaymentGatewayLabel(mixed $provider): ?string
    {
        $str = strtolower((string) ($provider instanceof CustomApi ? ($provider->provider_identifier ?: $provider->name) : $provider));
        foreach (self::PAYMENT_GATEWAYS as $key => $label) {
            if (str_contains($str, $key)) {
                return $label;
            }
        }

        return null;
    }

    /**
     * Resolve a user-facing band name (e.g., "Band 1", "Band 2", "Band 3", "Band 4")
     * from a CustomApi instance, provider string, or priority/index.
     * Payment gateways retain their friendly names.
     */
    public static function resolve(mixed $provider, ?int $fallbackIndex = null): string
    {
        // 1. Payment gateways are explicitly allowed to show their authentic brand names
        if ($gateway = self::getPaymentGatewayLabel($provider)) {
            return $gateway;
        }

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


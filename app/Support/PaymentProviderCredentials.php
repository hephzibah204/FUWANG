<?php

namespace App\Support;

use App\Models\ApiCenter;
use App\Models\PaymentGateway;

/**
 * Resolves payment keys from Api Center (DB), optional PaymentGateway JSON config, then config/services (.env).
 */
final class PaymentProviderCredentials
{
    private static function nonEmpty(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private static function gatewayConfig(string $name): array
    {
        $gw = PaymentGateway::where('name', $name)->first();

        return (array) ($gw?->config ?? []);
    }

    /**
     * @return array{public_key: ?string, secret_key: ?string}
     */
    public static function flutterwave(?ApiCenter $apiCenter = null): array
    {
        $apiCenter ??= ApiCenter::first();
        $gwc = self::gatewayConfig('flutterwave');

        $public = self::nonEmpty($apiCenter?->flutterwave_public_key)
            ?? self::nonEmpty($gwc['public_key'] ?? null)
            ?? self::nonEmpty(config('services.flutterwave.public'));

        $secret = self::nonEmpty($apiCenter?->flutterwave_secret_key)
            ?? self::nonEmpty($gwc['secret_key'] ?? null)
            ?? self::nonEmpty(config('services.flutterwave.secret'));

        return ['public_key' => $public, 'secret_key' => $secret];
    }

    /**
     * @return array{
     *     api_key: ?string,
     *     secret_key: ?string,
     *     contract_code: ?string,
     *     endpoint_auth: string,
     *     endpoint_reserve: string,
     *     sandbox: bool
     * }
     */
    public static function monnify(?ApiCenter $apiCenter = null): array
    {
        $apiCenter ??= ApiCenter::first();
        $gwc = self::gatewayConfig('monnify');

        $apiKey = self::nonEmpty($apiCenter?->monnify_api_key)
            ?? self::nonEmpty($gwc['api_key'] ?? null)
            ?? self::nonEmpty(config('services.monnify.api_key'));

        $secret = self::nonEmpty($apiCenter?->monnify_secret_key)
            ?? self::nonEmpty($gwc['secret_key'] ?? null)
            ?? self::nonEmpty(config('services.monnify.secret_key'));

        $contract = self::nonEmpty($apiCenter?->monnify_contract_code)
            ?? self::nonEmpty($gwc['contract_code'] ?? null)
            ?? self::nonEmpty(config('services.monnify.contract_code'));

        $sandbox = (bool) config('services.monnify.sandbox', false);
        $defaultAuth = $sandbox
            ? 'https://sandbox.monnify.com/api/v1/auth/login'
            : 'https://api.monnify.com/api/v1/auth/login';
        $defaultReserve = $sandbox
            ? 'https://sandbox.monnify.com/api/v2/bank-transfer/reserved-accounts'
            : 'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts';

        $authEndpoint = self::nonEmpty($apiCenter?->monnify_endpoint_auth)
            ?? self::nonEmpty($gwc['endpoint_auth'] ?? null)
            ?? self::nonEmpty(config('services.monnify.endpoint_auth'))
            ?? $defaultAuth;

        $reserveEndpoint = self::nonEmpty($apiCenter?->monnify_endpoint_reserve)
            ?? self::nonEmpty($gwc['endpoint_reserve'] ?? null)
            ?? self::nonEmpty(config('services.monnify.endpoint_reserve'))
            ?? $defaultReserve;

        return [
            'api_key' => $apiKey,
            'secret_key' => $secret,
            'contract_code' => $contract,
            'endpoint_auth' => $authEndpoint,
            'endpoint_reserve' => $reserveEndpoint,
            'sandbox' => $sandbox,
        ];
    }

    /**
     * @return array{public_key: ?string, secret_key: ?string}
     */
    public static function paystack(?ApiCenter $apiCenter = null): array
    {
        $apiCenter ??= ApiCenter::first();
        $gwc = self::gatewayConfig('paystack');

        $public = self::nonEmpty($apiCenter?->paystack_public_key)
            ?? self::nonEmpty($gwc['public_key'] ?? null)
            ?? self::nonEmpty(config('services.paystack.public'));

        $secret = self::nonEmpty($apiCenter?->paystack_secret_key)
            ?? self::nonEmpty($gwc['secret_key'] ?? null)
            ?? self::nonEmpty(config('services.paystack.secret'));

        return ['public_key' => $public, 'secret_key' => $secret];
    }

    /**
     * @return array{api_key: ?string, endpoint: ?string, business_id: ?string}
     */
    public static function payvessel(?ApiCenter $apiCenter = null): array
    {
        $apiCenter ??= ApiCenter::first();
        $gwc = self::gatewayConfig('payvessel');

        $apiKey = self::nonEmpty($apiCenter?->payvessel_api_key)
            ?? self::nonEmpty($gwc['api_key'] ?? null)
            ?? self::nonEmpty(config('services.payvessel.api_key'));

        $endpoint = self::nonEmpty($apiCenter?->payvessel_endpoint)
            ?? self::nonEmpty($gwc['endpoint'] ?? null)
            ?? self::nonEmpty(config('services.payvessel.endpoint'));

        $businessId = self::nonEmpty($apiCenter?->payvessel_businessid)
            ?? self::nonEmpty($gwc['business_id'] ?? null)
            ?? self::nonEmpty(config('services.payvessel.business_id'));

        return [
            'api_key' => $apiKey,
            'endpoint' => $endpoint,
            'business_id' => $businessId,
        ];
    }
}

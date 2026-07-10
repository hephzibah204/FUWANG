<?php

namespace App\Services;

use App\Models\User;
use App\Support\PaymentApiUserMessage;
use App\Support\PaymentProviderCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountKycIdentityService
{
    public const SERVICE_NIN = 'kyc_tier_nin';

    public const SERVICE_BVN = 'kyc_tier_bvn';

    public function ninPrice(): float
    {
        return 0.0;
    }

    public function bvnPrice(): float
    {
        return 0.0;
    }

    /**
     * @return array{status: bool, message: string, data?: array, result_id?: int}
     *
     * @throws \RuntimeException
     */
    public function verifyNinForTier(User $user, string $nin, ?string $firstname = null, ?string $lastname = null, ?string $dob = null): array
    {
        $nin = $this->normalizeIdentityDigits($nin, 'NIN');

        return $this->verifyIdentityViaVirtualAccount($user, 'nin', $nin, self::SERVICE_NIN, 'KYC_NIN');
    }

    /**
     * @return array{status: bool, message: string, data?: array, result_id?: int, image?: mixed}
     *
     * @throws \RuntimeException
     */
    public function verifyBvnForTier(User $user, string $bvn): array
    {
        $bvn = $this->normalizeIdentityDigits($bvn, 'BVN');

        return $this->verifyIdentityViaVirtualAccount($user, 'bvn', $bvn, self::SERVICE_BVN, 'KYC_BVN');
    }

    /**
     * @return array{status: bool, message: string, data?: array, result_id?: int}
     */
    private function verifyIdentityViaVirtualAccount(
        User $user,
        string $identityType,
        string $identityValue,
        string $serviceType,
        string $referencePrefix
    ): array {
        $attempts = [
            fn (User $u, string $t, string $v) => $this->tryMonnify($u, $t, $v),
            fn (User $u, string $t, string $v) => $this->tryFlutterwave($u, $t, $v),
            fn (User $u, string $t, string $v) => $this->tryPaystack($u, $t, $v),
        ];

        $lastMessage = 'Unable to validate identity via virtual account providers right now.';
        foreach ($attempts as $attempt) {
            $result = $attempt($user, $identityType, $identityValue);
            if ($result['ok'] === true) {
                $payload = [
                    'identity_type' => $identityType,
                    'identity_last4' => substr($identityValue, -4),
                    'provider' => $result['provider'],
                    'virtual_account' => $result['data'],
                ];

                $vr = app(VerificationResultService::class)->create(
                    $user,
                    $serviceType,
                    $identityValue,
                    $result['provider'],
                    $payload,
                    'success',
                    $referencePrefix
                );

                return [
                    'status' => true,
                    'message' => strtoupper($identityType).' verified successfully. Tier 2 has been updated.',
                    'data' => $payload,
                    'result_id' => $vr->id,
                ];
            }

            if (! empty($result['message'])) {
                $lastMessage = (string) $result['message'];
            }
        }

        throw new \RuntimeException($lastMessage);
    }

    private function normalizeIdentityDigits(string $value, string $label): string
    {
        $digits = preg_replace('/\D+/', '', trim($value));
        if (! is_string($digits) || strlen($digits) !== 11) {
            throw new \RuntimeException($label.' must be exactly 11 digits.');
        }

        return $digits;
    }

    /**
     * @return array{ok: bool, provider: string, data?: array, message?: string}
     */
    private function tryMonnify(User $user, string $identityType, string $identityValue): array
    {
        $m = PaymentProviderCredentials::monnify();
        $apiKey = (string) ($m['api_key'] ?? '');
        $secretKey = (string) ($m['secret_key'] ?? '');
        $contractCode = (string) ($m['contract_code'] ?? '');
        $authEndpoint = (string) ($m['endpoint_auth'] ?? '');
        $reserveEndpoint = (string) ($m['endpoint_reserve'] ?? '');

        if ($apiKey === '' || $secretKey === '' || $contractCode === '' || $authEndpoint === '' || $reserveEndpoint === '') {
            return ['ok' => false, 'provider' => 'monnify', 'message' => 'Monnify is not configured.'];
        }

        try {
            $authRes = Http::timeout(45)
                ->acceptJson()
                ->withBasicAuth($apiKey, $secretKey)
                ->post($authEndpoint);
            $authJson = $authRes->json();
            if (! $authRes->successful()) {
                return [
                    'ok' => false,
                    'provider' => 'monnify',
                    'message' => PaymentApiUserMessage::monnify(is_array($authJson) ? $authJson : null, 'Monnify auth failed'),
                ];
            }

            $token = is_array($authJson)
                ? ($authJson['responseBody']['accessToken'] ?? $authJson['responseBody']['token'] ?? null)
                : null;
            if (! is_string($token) || $token === '') {
                return ['ok' => false, 'provider' => 'monnify', 'message' => 'Monnify token missing.'];
            }

            $payload = [
                'accountReference' => 'KYC-'.Str::upper(Str::random(12)),
                'accountName' => $user->fullname ?? $user->username ?? 'Fuwa Wallet',
                'currencyCode' => 'NGN',
                'contractCode' => $contractCode,
                'customerEmail' => $user->email,
                'customerName' => $user->fullname ?? $user->username ?? $user->email,
                'getAllAvailableBanks' => true,
                $identityType => $identityValue,
            ];

            $reserveRes = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withToken($token)
                ->post($reserveEndpoint, $payload);
            $reserveJson = $reserveRes->json();

            if (! $reserveRes->successful() || (is_array($reserveJson) && ($reserveJson['requestSuccessful'] ?? true) === false)) {
                return [
                    'ok' => false,
                    'provider' => 'monnify',
                    'message' => PaymentApiUserMessage::monnify(is_array($reserveJson) ? $reserveJson : null, 'Monnify rejected the provided identity.'),
                ];
            }

            $body = is_array($reserveJson)
                ? ($reserveJson['responseBody'] ?? $reserveJson['data'] ?? $reserveJson)
                : [];
            $accounts = is_array($body) ? ($body['accounts'] ?? $body['bankAccounts'] ?? $body['reservedAccounts'] ?? []) : [];
            $first = null;
            foreach ((array) $accounts as $account) {
                $acct = (string) ($account['accountNumber'] ?? $account['account_number'] ?? '');
                if ($acct !== '') {
                    $first = $account;
                    break;
                }
            }
            if (! is_array($first)) {
                return ['ok' => false, 'provider' => 'monnify', 'message' => 'Monnify did not return a valid virtual account for this identity.'];
            }

            return [
                'ok' => true,
                'provider' => 'Monnify',
                'data' => [
                    'gateway' => 'monnify',
                    'account_number' => (string) ($first['accountNumber'] ?? $first['account_number'] ?? ''),
                    'bank_name' => (string) ($first['bankName'] ?? $first['bank'] ?? 'Monnify'),
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Tier KYC Monnify check failed', ['error' => $e->getMessage()]);

            return ['ok' => false, 'provider' => 'monnify', 'message' => 'Monnify is currently unavailable.'];
        }
    }

    /**
     * @return array{ok: bool, provider: string, data?: array, message?: string}
     */
    private function tryFlutterwave(User $user, string $identityType, string $identityValue): array
    {
        $secret = (string) (PaymentProviderCredentials::flutterwave()['secret_key'] ?? '');
        if ($secret === '') {
            return ['ok' => false, 'provider' => 'flutterwave', 'message' => 'Flutterwave is not configured.'];
        }

        [$firstName, $lastName] = $this->resolveNameParts($user);
        $payload = array_filter([
            'currency' => 'NGN',
            'email' => $user->email,
            'tx_ref' => 'KYC-'.Str::uuid()->toString(),
            'is_permanent' => true,
            'phonenumber' => $user->number ?? null,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'narration' => 'KYC tier identity validation',
            $identityType => $identityValue,
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $res = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['Authorization' => 'Bearer '.$secret])
                ->post('https://api.flutterwave.com/v3/virtual-account-numbers', $payload);
            $json = $res->json();

            $ok = $res->successful() && is_array($json) && (($json['status'] ?? '') === 'success');
            if (! $ok) {
                return [
                    'ok' => false,
                    'provider' => 'flutterwave',
                    'message' => PaymentApiUserMessage::flutterwave(is_array($json) ? $json : null, 'Flutterwave rejected the provided identity.'),
                ];
            }

            $data = is_array($json) ? ($json['data'] ?? []) : [];
            $acct = (string) ($data['account_number'] ?? '');
            if ($acct === '') {
                return ['ok' => false, 'provider' => 'flutterwave', 'message' => 'Flutterwave returned no account number for this identity.'];
            }

            return [
                'ok' => true,
                'provider' => 'Flutterwave',
                'data' => [
                    'gateway' => 'flutterwave',
                    'account_number' => $acct,
                    'bank_name' => (string) ($data['bank_name'] ?? 'Flutterwave'),
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Tier KYC Flutterwave check failed', ['error' => $e->getMessage()]);

            return ['ok' => false, 'provider' => 'flutterwave', 'message' => 'Flutterwave is currently unavailable.'];
        }
    }

    /**
     * @return array{ok: bool, provider: string, data?: array, message?: string}
     */
    private function tryPaystack(User $user, string $identityType, string $identityValue): array
    {
        $credentials = PaymentProviderCredentials::paystack();
        $secret = (string) ($credentials['secret_key'] ?? '');
        if ($secret === '') {
            return ['ok' => false, 'provider' => 'paystack', 'message' => 'Paystack is not configured.'];
        }

        [$firstName, $lastName] = $this->resolveNameParts($user);
        $customerPayload = array_filter([
            'email' => $user->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $user->number ?? null,
            $identityType => $identityValue,
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $customerRes = Http::timeout(45)
                ->acceptJson()
                ->asJson()
                ->withToken($secret)
                ->post('https://api.paystack.co/customer', $customerPayload);
            $customerJson = $customerRes->json();

            $customerCode = is_array($customerJson)
                ? ($customerJson['data']['customer_code'] ?? null)
                : null;
            if (! is_string($customerCode) || $customerCode === '') {
                return [
                    'ok' => false,
                    'provider' => 'paystack',
                    'message' => PaymentApiUserMessage::paystack(is_array($customerJson) ? $customerJson : null, 'Paystack customer creation failed.'),
                ];
            }

            $dedicatedRes = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withToken($secret)
                ->post('https://api.paystack.co/dedicated_account', [
                    'customer' => $customerCode,
                    'preferred_bank' => 'wema-bank',
                ]);
            $dedicatedJson = $dedicatedRes->json();

            if (! $dedicatedRes->successful() || ! (is_array($dedicatedJson) && ($dedicatedJson['status'] ?? false))) {
                return [
                    'ok' => false,
                    'provider' => 'paystack',
                    'message' => PaymentApiUserMessage::paystack(is_array($dedicatedJson) ? $dedicatedJson : null, 'Paystack rejected the provided identity.'),
                ];
            }

            $data = is_array($dedicatedJson) ? ($dedicatedJson['data'] ?? []) : [];
            $acct = (string) ($data['account_number'] ?? '');
            if ($acct === '') {
                return ['ok' => false, 'provider' => 'paystack', 'message' => 'Paystack returned no account number for this identity.'];
            }

            return [
                'ok' => true,
                'provider' => 'Paystack',
                'data' => [
                    'gateway' => 'paystack',
                    'account_number' => $acct,
                    'bank_name' => (string) ($data['bank']['name'] ?? 'Paystack'),
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Tier KYC Paystack check failed', ['error' => $e->getMessage()]);

            return ['ok' => false, 'provider' => 'paystack', 'message' => 'Paystack is currently unavailable.'];
        }
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolveNameParts(User $user): array
    {
        if (! empty($user->fullname)) {
            $parts = preg_split('/\s+/', trim((string) $user->fullname));
            $first = $parts[0] ?? 'Customer';
            $last = $parts[1] ?? $first;

            return [$first, $last];
        }

        $first = $user->username ?? 'Customer';

        return [$first, $first];
    }
}

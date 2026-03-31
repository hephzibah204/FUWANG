<?php

namespace App\Services\Vuvaa;

use App\Models\CustomApi;
use App\Models\SystemSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VuvaaClient
{
    private const DEFAULT_DEMO_KEY = 'FD!-F=15B46BAD21';
    private const DEFAULT_DEMO_IV = '0123456789012345';
    private const TOKEN_TTL_SECONDS = 10800;

    private readonly array $cfg;
    private readonly VuvaaCrypto $crypto;

    public function __construct(private readonly CustomApi $provider)
    {
        $this->cfg = is_array($provider->config) ? $provider->config : [];

        $key = trim((string) ($this->cfg['encryption_key'] ?? ''));
        if ($key === '') {
            $key = (string) (SystemSetting::get('vuvaa_encryption_key') ?? self::DEFAULT_DEMO_KEY);
        }

        $iv = trim((string) ($this->cfg['encryption_iv'] ?? ''));
        if ($iv === '') {
            $iv = (string) (SystemSetting::get('vuvaa_encryption_iv') ?? self::DEFAULT_DEMO_IV);
        }
        $this->crypto = new VuvaaCrypto($key, $iv);
    }

    public function verifyNin(string $nin, ?string $referenceId = null): array
    {
        $referenceId = $referenceId ?: $this->generateReferenceId();

        $data = [
            'username' => $this->username(),
            'nin' => $nin,
            'reference_id' => $referenceId,
        ];

        $result = $this->postEncrypted('/verify_nin', $data, true);
        if (!$result['ok']) {
            return $result;
        }

        $payload = $result['data'];
        $code = (string) ($payload['code'] ?? $payload['status_code'] ?? $payload['statusCode'] ?? '');
        $ok = $code === '' ? $this->looksSuccessful($payload) : $code === '00';

        return [
            'ok' => $ok,
            'data' => $payload,
            'message' => $ok ? 'Successful' : (string) ($payload['message'] ?? $payload['detail'] ?? 'Verification failed.'),
        ];
    }

    public function verifyInPerson(string $nin, string $selfieBase64, ?string $referenceId = null, ?string $pathOverride = null): array
    {
        $referenceId = $referenceId ?: $this->generateReferenceId();
        $selfieBase64 = $this->normalizeBase64($selfieBase64);

        $path = $pathOverride ?: (string) ($this->cfg['in_person_path'] ?? 'in_person_verification');
        $path = '/' . ltrim($path, '/');

        $data = [
            'username' => $this->username(),
            'nin' => $nin,
            $this->selfieField() => $selfieBase64,
            'reference_id' => $referenceId,
        ];

        $result = $this->postEncrypted($path, $data, true);
        if (!$result['ok']) {
            return $result;
        }

        $payload = $result['data'];
        $code = (string) ($payload['code'] ?? $payload['status_code'] ?? $payload['statusCode'] ?? '');
        $ok = $code === '' ? $this->looksSuccessful($payload) : $code === '00';

        return [
            'ok' => $ok,
            'data' => $payload,
            'message' => $ok ? 'Successful' : (string) ($payload['message'] ?? $payload['detail'] ?? 'Verification failed.'),
        ];
    }

    public function verifyShareCode(string $shareCode, ?string $referenceId = null, ?string $pathOverride = null): array
    {
        $referenceId = $referenceId ?: $this->generateReferenceId();

        $path = $pathOverride ?: (string) ($this->cfg['share_code_path'] ?? 'share_code');
        $path = '/' . ltrim($path, '/');

        $codeField = $this->shareCodeField();

        $data = [
            'username' => $this->username(),
            $codeField => $shareCode,
            'reference_id' => $referenceId,
        ];

        $result = $this->postEncrypted($path, $data, true);
        if (!$result['ok']) {
            return $result;
        }

        $payload = $result['data'];
        $code = (string) ($payload['code'] ?? $payload['status_code'] ?? $payload['statusCode'] ?? '');
        $ok = $code === '' ? $this->looksSuccessful($payload) : $code === '00';

        return [
            'ok' => $ok,
            'data' => $payload,
            'message' => $ok ? 'Successful' : (string) ($payload['message'] ?? $payload['detail'] ?? 'Verification failed.'),
        ];
    }

    public function requery(string $referenceId, ?string $pathOverride = null): array
    {
        $path = $pathOverride ?: (string) ($this->cfg['requery_path'] ?? 'requery');
        $path = '/' . ltrim($path, '/');

        $data = [
            'username' => $this->username(),
            'reference_id' => $referenceId,
        ];

        $result = $this->postEncrypted($path, $data, true);
        if (!$result['ok']) {
            return $result;
        }

        $payload = $result['data'];
        $code = (string) ($payload['code'] ?? $payload['status_code'] ?? $payload['statusCode'] ?? '');
        $ok = $code === '' ? $this->looksSuccessful($payload) : $code === '00';

        return [
            'ok' => $ok,
            'data' => $payload,
            'message' => $ok ? 'Successful' : (string) ($payload['message'] ?? $payload['detail'] ?? 'Requery failed.'),
        ];
    }

    public function getWalletDetails(array $payload = [], ?string $pathOverride = null): array
    {
        $path = $pathOverride ?: (string) ($this->cfg['wallet_path'] ?? 'wallet_details');
        $path = '/' . ltrim($path, '/');

        $data = array_merge(['username' => $this->username()], $payload);

        return $this->postEncrypted($path, $data, true);
    }

    public function transactionHistory(array $payload = [], ?string $pathOverride = null): array
    {
        $path = $pathOverride ?: (string) ($this->cfg['transaction_history_path'] ?? 'transaction_history');
        $path = '/' . ltrim($path, '/');

        $data = array_merge(['username' => $this->username()], $payload);

        return $this->postEncrypted($path, $data, true);
    }

    public function createUser(array $payload, ?string $pathOverride = null): array
    {
        $path = $pathOverride ?: (string) ($this->cfg['create_user_path'] ?? 'create_user');
        $path = '/' . ltrim($path, '/');

        return $this->postEncrypted($path, $payload, false);
    }

    public function getNimcReasons(array $payload = [], ?string $pathOverride = null): array
    {
        $path = $pathOverride ?: (string) ($this->cfg['reasons_path'] ?? 'nimc_reasons');
        $path = '/' . ltrim($path, '/');

        $data = array_merge(['username' => $this->username()], $payload);

        return $this->postEncrypted($path, $data, true);
    }

    public function postEncrypted(string $path, array $data, bool $authenticated): array
    {
        $url = $this->url($path);
        $headers = $this->headers();

        if ($authenticated) {
            $headers['Authorization'] = 'Bearer ' . $this->getAccessToken();
        }

        $response = $this->http($headers)->post($url, [
            'payload' => $this->crypto->encryptToBase64($data),
        ]);

        if ($authenticated && $response->status() === 401) {
            $this->forgetToken();
            $headers['Authorization'] = 'Bearer ' . $this->getAccessToken();
            $response = $this->http($headers)->post($url, [
                'payload' => $this->crypto->encryptToBase64($data),
            ]);
        }

        return $this->decodeResponse($response);
    }

    public function login(): string
    {
        $url = $this->url('/login');
        $headers = $this->headers();

        $loginPayload = [
            'username' => $this->username(),
            'password' => $this->password(),
        ];

        $response = $this->http($headers)->post($url, [
            'payload' => $this->crypto->encryptToBase64($loginPayload),
        ]);

        $decoded = $this->decodeResponse($response);
        if (!$decoded['ok']) {
            throw new \RuntimeException((string) ($decoded['message'] ?? 'Login failed.'));
        }

        $data = $decoded['data'];
        $token = $this->extractToken($data);
        if ($token === null || $token === '') {
            throw new \RuntimeException('Login response did not include an access token.');
        }

        $ttl = (int) ($this->cfg['token_ttl_seconds'] ?? self::TOKEN_TTL_SECONDS);
        $buffer = (int) ($this->cfg['token_ttl_buffer_seconds'] ?? 300);
        $ttl = max(60, $ttl - max(0, $buffer));

        Cache::put($this->tokenCacheKey(), $token, $ttl);

        return $token;
    }

    public function getAccessToken(): string
    {
        $existing = Cache::get($this->tokenCacheKey());
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }
        return $this->login();
    }

    public function forgetToken(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    public static function isVuvaaProvider(CustomApi $provider): bool
    {
        $pid = strtolower((string) ($provider->provider_identifier ?? ''));
        if ($pid !== '' && str_contains($pid, 'vuvaa')) {
            return true;
        }
        $endpoint = strtolower((string) ($provider->endpoint ?? ''));
        return $endpoint !== '' && str_contains($endpoint, 'vuvaa.com');
    }

    private function decodeResponse(Response $response): array
    {
        if (!$response->successful()) {
            return [
                'ok' => false,
                'message' => $response->json('message') ?? $response->json('detail') ?? ('HTTP ' . $response->status()),
                'status' => $response->status(),
                'data' => $response->json() ?: $response->body(),
            ];
        }

        $payload = $response->json('payload');
        if (!is_string($payload) || $payload === '') {
            $json = $response->json();
            if (is_array($json)) {
                return [
                    'ok' => $this->looksSuccessful($json),
                    'data' => $json,
                    'message' => (string) ($json['message'] ?? 'OK'),
                ];
            }

            return [
                'ok' => false,
                'message' => 'Missing encrypted payload in response.',
                'data' => $response->body(),
            ];
        }

        try {
            $data = $this->crypto->decryptBase64ToArray($payload);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Failed to decrypt provider response.',
                'data' => ['error' => $e->getMessage()],
            ];
        }

        return [
            'ok' => $this->looksSuccessful($data),
            'data' => $data,
            'message' => (string) ($data['message'] ?? 'OK'),
        ];
    }

    private function looksSuccessful(array $data): bool
    {
        $code = (string) ($data['code'] ?? $data['status_code'] ?? $data['statusCode'] ?? '');
        if ($code !== '') {
            return $code === '00';
        }

        $status = $data['status'] ?? null;
        if ($status === true || $status === 'success') {
            return true;
        }

        return (bool) Arr::get($data, 'data.status', false);
    }

    private function extractToken(array $data): ?string
    {
        $candidates = [
            'accessToken',
            'access_token',
            'token',
            'data.accessToken',
            'data.access_token',
            'data.token',
        ];

        foreach ($candidates as $path) {
            $v = Arr::get($data, $path);
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        return null;
    }

    private function url(string $path): string
    {
        return rtrim((string) $this->provider->endpoint, '/') . '/' . ltrim($path, '/');
    }

    private function headers(): array
    {
        $headers = is_array($this->provider->headers) ? $this->provider->headers : [];
        if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
            $headers['Content-Type'] = 'application/json';
        }
        return $headers;
    }

    private function http(array $headers)
    {
        $timeout = (int) ($this->provider->timeout_seconds ?? 60);
        $http = Http::timeout($timeout);
        if (!empty($headers)) {
            $http = $http->withHeaders($headers);
        }
        return $http;
    }

    private function username(): string
    {
        $username = trim((string) ($this->cfg['username'] ?? ''));
        if ($username === '') {
            $username = trim((string) ($this->provider->api_key ?? ''));
        }
        if ($username === '') {
            $username = trim((string) (SystemSetting::get('vuvaa_username') ?? ''));
        }
        if ($username === '') {
            throw new \RuntimeException('VUVAA username not configured.');
        }
        return $username;
    }

    private function password(): string
    {
        $password = trim((string) ($this->cfg['password'] ?? ''));
        if ($password === '') {
            $password = trim((string) ($this->provider->secret_key ?? ''));
        }
        if ($password === '') {
            $password = trim((string) (SystemSetting::get('vuvaa_password') ?? ''));
        }
        if ($password === '') {
            throw new \RuntimeException('VUVAA password not configured.');
        }
        return $password;
    }

    private function selfieField(): string
    {
        $field = (string) ($this->cfg['selfie_field'] ?? $this->cfg['image_field'] ?? 'image');
        $field = trim($field);
        return $field !== '' ? $field : 'image';
    }

    private function shareCodeField(): string
    {
        $field = (string) ($this->cfg['share_code_field'] ?? 'share_code');
        $field = trim($field);
        return $field !== '' ? $field : 'share_code';
    }

    private function normalizeBase64(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $comma = strpos($value, ',');
        if (str_starts_with($value, 'data:') && $comma !== false) {
            return substr($value, $comma + 1);
        }

        return $value;
    }

    private function tokenCacheKey(): string
    {
        return 'vuvaa.token.' . (string) ($this->provider->id ?: md5((string) $this->provider->endpoint));
    }

    private function generateReferenceId(): string
    {
        $prefix = (string) ($this->cfg['reference_prefix'] ?? 'REF');
        $rand = strtoupper(bin2hex(random_bytes(4)));
        return $prefix . date('YmdHis') . $rand;
    }
}

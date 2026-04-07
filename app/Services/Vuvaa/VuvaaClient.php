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
    private const TOKEN_TTL_SECONDS = 10800;

    private readonly array $cfg;
    private readonly VuvaaCrypto $crypto;
    private readonly string $endpoint;

    public function __construct(private readonly CustomApi $provider)
    {
        $this->cfg = is_array($provider->config) ? $provider->config : [];

        $this->endpoint = $provider->endpoint ?? env('VUVAA_LIVE_URL');
        if (!$this->endpoint) {
            throw new \RuntimeException('VUVAA endpoint not configured.');
        }

        $key = trim((string) ($this->cfg['encryption_key'] ?? env('VUVAA_ENCRYPTION_KEY') ?? ''));
        $iv = trim((string) ($this->cfg['encryption_iv'] ?? env('VUVAA_ENCRYPTION_IV') ?? ''));

        if ($key === '' || $iv === '') {
            throw new \RuntimeException('VUVAA encryption key/IV not configured.');
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

        return $this->postEncrypted('/verify_nin', $data, true);
    }

    public function verifyInPerson(string $nin, string $selfieBase64, ?string $referenceId = null): array
    {
        $referenceId = $referenceId ?: $this->generateReferenceId();
        $selfieBase64 = $this->normalizeBase64($selfieBase64);

        $data = [
            'username' => $this->username(),
            'nin' => $nin,
            'selfie' => $selfieBase64,
            'reference_id' => $referenceId,
        ];

        return $this->postEncrypted('/in_person_verification', $data, true);
    }

    public function verifyShareCode(string $shareCode, ?string $referenceId = null): array
    {
        $referenceId = $referenceId ?: $this->generateReferenceId();

        $data = [
            'username' => $this->username(),
            'share_code' => $shareCode,
            'reference_id' => $referenceId,
        ];

        return $this->postEncrypted('/share_code', $data, true);
    }

    public function requery(string $referenceId): array
    {
        $data = [
            'username' => $this->username(),
            'reference_id' => $referenceId,
        ];

        return $this->postEncrypted('/requery', $data, true);
    }

    public function getWalletDetails(): array
    {
        return $this->postEncrypted('/get_wallet_details', ['username' => $this->username()], true);
    }

    public function transactionHistory(array $filters = []): array
    {
        $data = array_merge(['username' => $this->username()], $filters);
        return $this->postEncrypted('/transaction_history', $data, true);
    }

    public function createUser(array $payload): array
    {
        return $this->postEncrypted('/create_user', $payload, false);
    }

    public function getReasons(): array
    {
        return $this->postEncrypted('/getReasons', ['username' => $this->username()], true);
    }

    private function postEncrypted(string $path, array $data, bool $authenticated): array
    {
        $url = $this->url($path);
        $headers = $this->headers();

        if ($authenticated) {
            $token = $this->getAccessToken();
            if ($token === null) {
                return ['ok' => false, 'message' => 'Authentication failed.', 'data' => []];
            }
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $payload = ['payload' => $this->crypto->encryptToBase64($data)];

        $response = $this->http($headers)->post($url, $payload);

        if ($authenticated && $response->status() === 401) {
            $this->forgetToken();
            $token = $this->getAccessToken();
            if ($token === null) {
                return ['ok' => false, 'message' => 'Re-authentication failed.', 'data' => []];
            }
            $headers['Authorization'] = 'Bearer ' . $token;
            $response = $this->http($headers)->post($url, $payload);
        }

        return $this->decodeResponse($response);
    }

    public function login(): ?string
    {
        $loginPayload = [
            'username' => $this->username(),
            'password' => $this->password(),
        ];

        $response = $this->http($this->headers())->post($this->url('/login'), [
            'payload' => $this->crypto->encryptToBase64($loginPayload),
        ]);

        $decoded = $this->decodeResponse($response);
        if (!$decoded['ok']) {
            // Consider logging the failure reason
            return null;
        }

        $token = Arr::get($decoded, 'data.data.access_token') ?? Arr::get($decoded, 'data.access_token');
        if (!is_string($token) || $token === '') {
            return null;
        }

        Cache::put($this->tokenCacheKey(), $token, now()->addSeconds(self::TOKEN_TTL_SECONDS - 300));

        return $token;
    }

    public function getAccessToken(): ?string
    {
        $token = Cache::get($this->tokenCacheKey());
        if (is_string($token) && $token !== '') {
            return $token;
        }
        return $this->login();
    }

    public function forgetToken(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    private function decodeResponse(Response $response): array
    {
        $status = $response->status();
        $body = $response->body();

        if (!$response->successful()) {
            $message = 'HTTP Error ' . $status;
            $data = $response->json();
            if (is_array($data) && (isset($data['message']) || isset($data['detail']))) {
                $message = $data['message'] ?? $data['detail'];
            }
            return ['ok' => false, 'message' => $message, 'data' => $data ?: $body, 'status' => $status];
        }

        $json = $response->json();
        if (!is_array($json) || !isset($json['payload'])) {
            return ['ok' => false, 'message' => 'Invalid response format: missing payload.', 'data' => $json ?: $body];
        }

        try {
            $decrypted = $this->crypto->decryptBase64ToArray($json['payload']);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Decryption failed: ' . $e->getMessage(), 'data' => []];
        }

        $statusCode = (string) ($decrypted['statusCode'] ?? $decrypted['status_code'] ?? '');
        $ok = $statusCode === '00';

        return [
            'ok' => $ok,
            'message' => $decrypted['message'] ?? ($ok ? 'Success' : 'Failed'),
            'data' => $decrypted,
        ];
    }

    private function url(string $path): string
    {
        return rtrim($this->endpoint, '/') . '/' . ltrim($path, '/');
    }

    private function headers(): array
    {
        return array_merge($this->provider->headers ?? [], ['Content-Type' => 'application/json']);
    }

    private function http(array $headers)
    {
        return Http::timeout((int) ($this->provider->timeout_seconds ?? 60))->withHeaders($headers);
    }

    private function username(): string
    {
        return trim((string) ($this->cfg['username'] ?? $this->provider->api_key ?? env('VUVAA_USERNAME') ?? ''));
    }

    private function password(): string
    {
        return trim((string) ($this->cfg['password'] ?? $this->provider->secret_key ?? env('VUVAA_PASSWORD') ?? ''));
    }

    private function normalizeBase64(string $value): string
    {
        if (str_contains($value, ',')) {
            return last(explode(',', $value));
        }
        return $value;
    }

    private function tokenCacheKey(): string
    {
        return 'vuvaa.token.' . md5($this->username());
    }

    private function generateReferenceId(): string
    {
        return ($this->cfg['reference_prefix'] ?? 'REF') . date('YmdHis') . strtoupper(bin2hex(random_bytes(4)));
    }
}

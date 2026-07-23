<?php

namespace App\Services\DataVerify;

use App\Models\ApiCenter;
use App\Models\CustomApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DataVerifyClient
{
    public function __construct(private readonly CustomApi $provider)
    {
    }

    public static function isDataVerifyProvider(CustomApi $provider): bool
    {
        $identifier = strtolower((string) ($provider->provider_identifier ?? ''));
        if (str_contains($identifier, 'dataverify')) {
            return true;
        }

        return str_contains(strtolower((string) ($provider->endpoint ?? '')), 'dataverify.com.ng');
    }

    /**
     * @param array{
     *   number?: string,
     *   firstname?: string,
     *   lastname?: string,
     *   dob?: string,
     *   gender?: string
     * } $input
     * @return array{ok:bool,message:string,data:array}
     */
    public function verify(string $mode, array $input, ?string $requestedType = null): array
    {
        if (!in_array($mode, ['nin', 'phone', 'demographic'], true)) {
            return ['ok' => false, 'message' => 'Selected mode is not supported by DataVerify.', 'data' => []];
        }

        $configuredEndpoint = (string) $this->provider->endpoint;
        $path = $this->resolvePath($mode, $requestedType);
        $url = $this->resolveEndpoint($configuredEndpoint, $path);

        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'DataVerify API key is missing.', 'data' => []];
        }

        $headers = is_array($this->provider->headers) ? $this->provider->headers : [];
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';

        $payload = ['api_key' => $apiKey];
        if ($mode === 'nin') {
            $payload['nin'] = trim((string) ($input['number'] ?? ''));
        } elseif ($mode === 'phone') {
            $payload['phone'] = trim((string) ($input['number'] ?? ''));
        } else {
            $payload['firstname'] = strtoupper(trim((string) ($input['firstname'] ?? '')));
            $payload['lastname'] = strtoupper(trim((string) ($input['lastname'] ?? '')));
            $payload['dob'] = $this->formatDob((string) ($input['dob'] ?? ''));
            $payload['gender'] = $this->normalizeGender((string) ($input['gender'] ?? ''));
        }
        $res = Http::timeout((int) ($this->provider->timeout_seconds ?: 60))
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->post($url, $payload);
        $json = $res->json();

        if (!$res->successful()) {
            Log::warning('DataVerify NIN call failed', [
                'provider_id' => $this->provider->id,
                'status' => $res->status(),
                'mode' => $mode,
                'url' => $url,
                'body' => $res->body(),
            ]);

            return [
                'ok' => false,
                'message' => (is_array($json) ? ($json['message'] ?? $json['detail'] ?? null) : null) ?: 'DataVerify verification failed.',
                'data' => is_array($json) ? $json : [],
            ];
        }

        if (!is_array($json)) {
            return ['ok' => false, 'message' => 'DataVerify returned invalid response.', 'data' => []];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $responseCode = (string) ($json['response_code'] ?? '');
        $looksSuccessful = $statusVal === 'success' || $statusVal === 'true' || $responseCode === '00' || $responseCode === '0';
        if (!$looksSuccessful) {
            return [
                'ok' => false,
                'message' => (string) ($json['message'] ?? 'DataVerify verification was not successful.'),
                'data' => $json,
            ];
        }

        return [
            'ok' => true,
            'message' => (string) ($json['message'] ?? 'Verification successful'),
            'data' => is_array($json['data'] ?? null) ? $json['data'] : (is_array($json['user_data'] ?? null) ? $json['user_data'] : $json),
        ];
    }

    /**
     * @return array{ok:bool,message:string,data:array}
     */
    public function verifyBvn(string $bvn, ?string $requestedType = null): array
    {
        $path = $this->resolveBvnPath($requestedType);
        $url = $this->resolveBvnEndpoint((string) $this->provider->endpoint, $path);
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'DataVerify API key is missing.', 'data' => []];
        }

        $headers = is_array($this->provider->headers) ? $this->provider->headers : [];
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';

        $payload = [
            'api_key' => $apiKey,
            'bvn' => trim($bvn),
        ];

        $res = Http::timeout((int) ($this->provider->timeout_seconds ?: 60))
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->post($url, $payload);
        $json = $res->json();

        if (!$res->successful()) {
            Log::warning('DataVerify BVN call failed', [
                'provider_id' => $this->provider->id,
                'status' => $res->status(),
                'url' => $url,
                'body' => $res->body(),
            ]);

            return [
                'ok' => false,
                'message' => (is_array($json) ? ($json['message'] ?? $json['detail'] ?? null) : null) ?: 'DataVerify BVN verification failed.',
                'data' => is_array($json) ? $json : [],
            ];
        }

        if (!is_array($json)) {
            return ['ok' => false, 'message' => 'DataVerify returned invalid response.', 'data' => []];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $responseCode = (string) ($json['response_code'] ?? '');
        $looksSuccessful = $statusVal === 'success' || $responseCode === '00';
        if (!$looksSuccessful) {
            return [
                'ok' => false,
                'message' => (string) ($json['message'] ?? 'DataVerify BVN verification was not successful.'),
                'data' => $json,
            ];
        }

        return [
            'ok' => true,
            'message' => (string) ($json['message'] ?? 'BVN verified'),
            'data' => is_array($json['user_data'] ?? null) ? $json['user_data'] : $json,
        ];
    }

    private function resolvePath(string $mode, ?string $requestedType): string
    {
        $pathMap = [
            'nin' => 'nin_premium',
            'phone' => 'nin_premium_phone',
            'demographic' => 'nin_premium_demo.php',
        ];
        $path = $pathMap[$mode] ?? 'nin_premium';

        $typeKey = trim((string) $requestedType);
        if ($typeKey === '') {
            return $path;
        }

        $type = $this->provider->verificationTypes()->where('type_key', $typeKey)->first();
        $suffix = trim((string) data_get($type, 'meta.path_suffix', ''));
        if ($suffix !== '') {
            return $this->normalizePhpPathSuffix($suffix);
        }

        return $path;
    }

    /**
     * Prefer the selected provider's credential, while retaining compatibility
     * with installations that still store DataVerify credentials in api_centers.
     */
    private function apiKey(): string
    {
        $apiKey = trim((string) ($this->provider->api_key ?? ''));
        if ($apiKey !== '') {
            return $apiKey;
        }

        return trim((string) (ApiCenter::query()->value('dataverify_api_key') ?? ''));
    }

    private function resolveBvnPath(?string $requestedType): string
    {
        $path = 'bvn_premium.php';

        $typeKey = trim((string) $requestedType);
        if ($typeKey === '') {
            return $path;
        }

        $type = $this->provider->verificationTypes()->where('type_key', $typeKey)->first();
        $suffix = trim((string) data_get($type, 'meta.path_suffix', ''));
        if ($suffix !== '') {
            return $this->normalizeBvnPathSuffix($suffix);
        }

        return $path;
    }

    private function resolveEndpoint(string $configured, string $path): string
    {
        $configured = trim($configured);
        $suffix = ltrim($path, '/');
        if (str_starts_with(strtolower($suffix), 'nin_slips/')) {
            $suffix = substr($suffix, strlen('nin_slips/'));
        }

        if ($configured === '') {
            return 'https://dataverify.com.ng/developers/nin_slips/' . $suffix;
        }

        $configuredHost = strtolower((string) parse_url($configured, PHP_URL_HOST));
        if (in_array($configuredHost, ['api.dataverify.com.ng', 'api.dataverify.ng'], true)) {
            return 'https://dataverify.com.ng/developers/nin_slips/' . $suffix;
        }

        $trimmed = rtrim($configured, '/');
        $lowerTrimmed = strtolower($trimmed);
        if (str_ends_with(strtolower($trimmed), '.php')) {
            if (str_ends_with($lowerTrimmed, '/nin_api.php')) {
                $base = (string) preg_replace('#/nin_api\.php$#i', '', $trimmed);
                return rtrim($base, '/') . '/nin_slips/' . $suffix;
            }
            return preg_replace('#/[^/]+\.php$#i', '/' . $suffix, $trimmed) ?? ('https://dataverify.com.ng/developers/nin_slips/' . $suffix);
        }

        $last = strtolower((string) basename($trimmed));
        $known = ['nin_premium', 'nin_premium_phone', 'nin_premium_demo', 'nin_by_phone', 'nin_api', 'nin'];
        if (in_array($last, $known, true)) {
            $base = (string) preg_replace('#/[^/]+$#', '', $trimmed);
            if ($last === 'nin_api') {
                return rtrim($base, '/') . '/nin_slips/' . $suffix;
            }
            return rtrim($base, '/') . '/' . $suffix;
        }

        return $trimmed . '/' . $suffix;
    }

    private function normalizePhpPathSuffix(string $suffix): string
    {
        $normalized = ltrim(trim($suffix), '/');
        if ($normalized === '') {
            return 'nin_premium';
        }

        if (!str_ends_with(strtolower($normalized), '.php')) {
            $normalized .= '.php';
        }

        return $normalized;
    }

    private function normalizeBvnPathSuffix(string $suffix): string
    {
        $normalized = ltrim(trim($suffix), '/');
        if ($normalized === '') {
            return 'bvn_premium.php';
        }

        if (!str_ends_with(strtolower($normalized), '.php') && !str_contains(basename($normalized), '.')) {
            $normalized .= '.php';
        }

        return $normalized;
    }

    private function resolveBvnEndpoint(string $configured, string $path): string
    {
        $configured = trim($configured);
        $suffix = ltrim($path, '/');

        if ($configured === '') {
            return 'https://dataverify.com.ng/developers/bvn_slip/' . $suffix;
        }

        $trimmed = rtrim($configured, '/');
        if (str_ends_with(strtolower($trimmed), '.php')) {
            return preg_replace('#/[^/]+\.php$#i', '/' . $suffix, $trimmed) ?? ('https://dataverify.com.ng/developers/bvn_slip/' . $suffix);
        }

        $last = strtolower((string) basename($trimmed));
        $known = ['bvn_premium', 'bvn_standard', 'bvn', 'bvn_slip', 'bvn_premium.php', 'bvn_standard.php'];
        if (in_array($last, $known, true)) {
            $base = (string) preg_replace('#/[^/]+$#', '', $trimmed);
            return rtrim($base, '/') . '/' . $suffix;
        }

        return $trimmed . '/' . $suffix;
    }

    private function formatDob(string $dob): string
    {
        $dob = trim($dob);
        if ($dob === '') {
            return $dob;
        }

        try {
            return \Carbon\Carbon::parse($dob)->format('d-m-Y');
        } catch (\Throwable $e) {
            return $dob;
        }
    }

    private function normalizeGender(string $gender): string
    {
        $value = strtolower(trim($gender));
        if ($value === 'male' || $value === 'm') {
            return 'm';
        }
        if ($value === 'female' || $value === 'f') {
            return 'f';
        }

        return $value;
    }
}

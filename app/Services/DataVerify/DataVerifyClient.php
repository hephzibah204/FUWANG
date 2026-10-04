<?php

namespace App\Services\DataVerify;

use App\Models\ApiCenter;
use App\Models\CustomApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DataVerifyClient
{
    public function __construct(private readonly ?CustomApi $provider = null)
    {
    }

    public static function normalizeDomain(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        // Migrate all requests from legacy dataverify domains to dataverify.org
        return (string) preg_replace(
            '#https?://(?:api\.)?dataverify\.(?:com\.ng|ng)#i',
            'https://dataverify.org',
            $url
        );
    }

    public static function isDataVerifyProvider(?CustomApi $provider): bool
    {
        if (!$provider) {
            return false;
        }

        $identifier = strtolower((string) ($provider->provider_identifier ?? ''));
        if (str_contains($identifier, 'dataverify')) {
            return true;
        }

        $endpoint = strtolower((string) ($provider->endpoint ?? ''));

        return str_contains($endpoint, 'dataverify.org')
            || str_contains($endpoint, 'dataverify.com.ng')
            || str_contains($endpoint, 'dataverify.ng');
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
            $payload['nin'] = preg_replace('/\D/', '', (string) ($input['number'] ?? ''));
            if (!empty($input['validation_type'])) {
                $payload['validation_type'] = trim((string) $input['validation_type']);
            }
        } elseif ($mode === 'phone') {
            $payload['phone'] = preg_replace('/\D/', '', (string) ($input['number'] ?? ''));
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
            $message = (is_array($json) ? ($json['message'] ?? $json['detail'] ?? null) : null)
                ?: 'DataVerify verification failed.';

            Log::warning('DataVerify NIN call failed', [
                'provider_id' => $this->provider->id,
                'status' => $res->status(),
                'mode' => $mode,
                'url' => $url,
                'body' => $res->body(),
            ]);

            return [
                'ok' => false,
                'message' => $message,
                'data' => is_array($json) ? $json : [],
                'terminal' => $this->isTerminalProviderError($message),
            ];
        }

        if (!is_array($json)) {
            return ['ok' => false, 'message' => 'DataVerify returned invalid response.', 'data' => []];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $responseCode = (string) ($json['response_code'] ?? '');
        $looksSuccessful = $statusVal === 'success' || $statusVal === 'true' || $responseCode === '00' || $responseCode === '0';
        if (!$looksSuccessful) {
            $message = (string) ($json['message'] ?? 'DataVerify verification was not successful.');

            return [
                'ok' => false,
                'message' => $message,
                'data' => $json,
                'terminal' => $this->isTerminalProviderError($message),
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

    /**
     * Submit a BVN Retrieval request to DataVerify
     *
     * @param string $phone
     * @param string $fullName
     * @param string|null $dob
     * @return array{ok: bool, provider_transaction_id: ?string, message: string, data: array, terminal?: bool}
     */
    public function submitBvnRetrieval(string $phone, string $fullName, ?string $dob = null): array
    {
        $url = $this->resolveBvnRetrievalEndpoint();
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return [
                'ok' => false,
                'provider_transaction_id' => null,
                'message' => 'DataVerify API key is missing.',
                'data' => [],
            ];
        }

        $headers = is_array($this->provider?->headers) ? $this->provider->headers : [];
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';

        $payload = [
            'api_key' => $apiKey,
            'phone' => trim($phone),
            'phone_number' => trim($phone),
            'name' => trim($fullName),
            'owner_name' => trim($fullName),
            'full_name' => trim($fullName),
        ];

        if (!empty($dob)) {
            $payload['dob'] = $this->formatDob($dob);
        }

        $timeout = (int) ($this->provider?->timeout_seconds ?: 60);

        try {
            $res = Http::timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->withHeaders($headers)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('DataVerify BVN Retrieval submission network error', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'provider_transaction_id' => null,
                'message' => 'Network error connecting to DataVerify BVN Retrieval service: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        $json = $res->json();

        if (!$res->successful()) {
            $message = (is_array($json) ? ($json['message'] ?? $json['detail'] ?? null) : null)
                ?: 'DataVerify BVN retrieval submission failed with HTTP status ' . $res->status();

            Log::warning('DataVerify BVN retrieval submission HTTP failed', [
                'status' => $res->status(),
                'url' => $url,
                'body' => $res->body(),
            ]);

            return [
                'ok' => false,
                'provider_transaction_id' => null,
                'message' => $message,
                'data' => is_array($json) ? $json : [],
                'terminal' => $this->isTerminalProviderError($message),
            ];
        }

        if (!is_array($json)) {
            return [
                'ok' => false,
                'provider_transaction_id' => null,
                'message' => 'DataVerify returned an invalid response.',
                'data' => [],
            ];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $responseCode = (string) ($json['response_code'] ?? '');
        $looksSuccessful = in_array($statusVal, ['success', 'true', 'pending', 'processing', 'ok', 'queued'], true)
            || in_array($responseCode, ['00', '0', '01'], true);

        if (!$looksSuccessful) {
            $message = (string) ($json['message'] ?? 'DataVerify BVN retrieval submission failed.');

            return [
                'ok' => false,
                'provider_transaction_id' => null,
                'message' => $message,
                'data' => $json,
                'terminal' => $this->isTerminalProviderError($message),
            ];
        }

        $providerTxId = $json['transaction_id'] 
            ?? $json['reference'] 
            ?? $json['data']['transaction_id'] 
            ?? $json['data']['reference'] 
            ?? $json['id'] 
            ?? null;

        return [
            'ok' => true,
            'provider_transaction_id' => $providerTxId ? (string) $providerTxId : null,
            'message' => (string) ($json['message'] ?? 'BVN retrieval request submitted successfully.'),
            'data' => is_array($json['data'] ?? null) ? $json['data'] : $json,
        ];
    }

    /**
     * Check BVN Retrieval status on DataVerify
     *
     * @param string $transactionId
     * @return array{ok: bool, status: string, bvn: ?string, message: string, data: array, refund?: bool}
     */
    public function checkBvnRetrievalStatus(string $transactionId): array
    {
        $url = $this->resolveBvnRetrievalStatusEndpoint();
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return [
                'ok' => false,
                'status' => 'error',
                'bvn' => null,
                'message' => 'DataVerify API key is missing.',
                'data' => [],
            ];
        }

        $headers = is_array($this->provider?->headers) ? $this->provider->headers : [];
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';

        $payload = [
            'api_key' => $apiKey,
            'transaction_id' => trim($transactionId),
            'reference' => trim($transactionId),
        ];

        $timeout = (int) ($this->provider?->timeout_seconds ?: 60);

        try {
            $res = Http::timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->withHeaders($headers)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('DataVerify BVN Retrieval status check network error', [
                'url' => $url,
                'tx_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => 'pending',
                'bvn' => null,
                'message' => 'Unable to reach provider to check status: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        $json = $res->json();

        if (!$res->successful() || !is_array($json)) {
            Log::warning('DataVerify BVN retrieval status check non-success response', [
                'status' => $res->status(),
                'url' => $url,
                'body' => $res->body(),
            ]);

            return [
                'ok' => false,
                'status' => 'pending',
                'bvn' => null,
                'message' => is_array($json) ? ($json['message'] ?? 'Failed to check status') : 'Invalid response from provider',
                'data' => is_array($json) ? $json : [],
            ];
        }

        $statusVal = strtolower((string) ($json['status'] ?? $json['data']['status'] ?? ''));
        $message = (string) ($json['message'] ?? $json['detail'] ?? '');
        $data = is_array($json['data'] ?? null) ? $json['data'] : (is_array($json['user_data'] ?? null) ? $json['user_data'] : $json);

        $retrievedBvn = $data['bvn'] 
            ?? $data['bvn_number'] 
            ?? $data['response']['bvn'] 
            ?? $json['bvn'] 
            ?? null;

        // Check for Completed / Success
        if (
            in_array($statusVal, ['completed', 'successful', 'success', 'resolved'], true) 
            || (!empty($retrievedBvn) && strlen(trim((string)$retrievedBvn)) === 11)
        ) {
            return [
                'ok' => true,
                'status' => 'completed',
                'bvn' => (string) $retrievedBvn,
                'message' => $message ?: 'BVN retrieved successfully.',
                'data' => $data,
            ];
        }

        // Check for Not Found / Failed / Refundable condition
        $lowerMsg = strtolower($message);
        $isNotFound = in_array($statusVal, ['not_found', 'not found', 'failed', 'declined', 'rejected', 'unresolved'], true)
            || str_contains($lowerMsg, 'not found')
            || str_contains($lowerMsg, 'no record')
            || str_contains($lowerMsg, 'cannot find')
            || str_contains($lowerMsg, 'could not be retrieved');

        if ($isNotFound) {
            return [
                'ok' => false,
                'status' => 'not_found',
                'bvn' => null,
                'message' => $message ?: 'BVN record not found.',
                'data' => $data,
                'refund' => true,
            ];
        }

        // Still pending / processing
        return [
            'ok' => true,
            'status' => 'pending',
            'bvn' => null,
            'message' => $message ?: 'BVN retrieval is currently in progress.',
            'data' => $data,
        ];
    }

    public function resolveBvnRetrievalEndpoint(): string
    {
        $configured = trim((string) (
            \App\Models\SystemSetting::get('dataverify_endpoint_bvn_retrieval')
            ?? ApiCenter::query()->value('dataverify_endpoint_bvn_retrieval')
            ?? ''
        ));

        if ($configured !== '') {
            return self::normalizeDomain($configured);
        }

        return 'https://dataverify.org/api/developers/bvn_retrieval.php';
    }

    public function resolveBvnRetrievalStatusEndpoint(): string
    {
        $configured = trim((string) (
            \App\Models\SystemSetting::get('dataverify_endpoint_bvn_retrieval_status')
            ?? ApiCenter::query()->value('dataverify_endpoint_bvn_retrieval_status')
            ?? ''
        ));

        if ($configured !== '') {
            return self::normalizeDomain($configured);
        }

        return 'https://dataverify.org/api/developers/bvn_retrieval_status.php';
    }

    /**
     * Check wallet balance on DataVerify (free inquiry)
     *
     * @return array{ok: bool, balance: ?float, message: string, data: array}
     */
    public function checkBalance(): array
    {
        $url = 'https://dataverify.org/api/developers/balance.php';
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return [
                'ok' => false,
                'balance' => null,
                'message' => 'DataVerify API key is missing.',
                'data' => [],
            ];
        }

        $headers = is_array($this->provider?->headers) ? $this->provider->headers : [];
        $headers['Authorization'] = 'Bearer ' . $apiKey;
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';

        try {
            $res = Http::timeout((int) ($this->provider?->timeout_seconds ?: 30))
                ->acceptJson()
                ->withHeaders($headers)
                ->get($url, [
                    'api_key' => $apiKey,
                ]);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'balance' => null,
                'message' => 'Failed to reach DataVerify balance endpoint: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        $json = $res->json();
        if (!$res->successful() || !is_array($json)) {
            return [
                'ok' => false,
                'balance' => null,
                'message' => is_array($json) ? ($json['message'] ?? 'Failed to check balance') : 'Invalid response from DataVerify',
                'data' => is_array($json) ? $json : [],
            ];
        }

        $balance = $json['balance'] ?? $json['data']['balance'] ?? $json['wallet_balance'] ?? null;

        return [
            'ok' => true,
            'balance' => is_numeric($balance) ? (float) $balance : null,
            'message' => (string) ($json['message'] ?? 'Balance fetched successfully.'),
            'data' => $json,
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

        $type = $this->provider?->verificationTypes()->where('type_key', $typeKey)->first();
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
        $apiKey = trim((string) ($this->provider?->api_key ?? ''));
        if ($apiKey !== '') {
            return $apiKey;
        }

        $apiCenterKey = trim((string) (ApiCenter::query()->value('dataverify_api_key') ?? ''));
        if ($apiCenterKey !== '') {
            return $apiCenterKey;
        }

        return trim((string) (env('DATAVERIFY_API_KEY') ?: \App\Models\SystemSetting::get('dataverify_api_key', '')));
    }

    private function isTerminalProviderError(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'insufficient balance')
            || str_contains($message, 'insufficient fund')
            || str_contains($message, 'invalid api key')
            || str_contains($message, 'ip address blocked');
    }

    private function resolveBvnPath(?string $requestedType): string
    {
        $path = 'bvn_premium.php';

        $typeKey = trim((string) $requestedType);
        if ($typeKey === '') {
            return $path;
        }

        $type = $this->provider?->verificationTypes()->where('type_key', $typeKey)->first();
        $suffix = trim((string) data_get($type, 'meta.path_suffix', ''));
        if ($suffix !== '') {
            return $this->normalizeBvnPathSuffix($suffix);
        }

        return $path;
    }

    private function resolveEndpoint(string $configured, string $path): string
    {
        $configured = self::normalizeDomain(trim($configured));
        $suffix = ltrim($path, '/');
        if (str_starts_with(strtolower($suffix), 'nin_slips/')) {
            $suffix = substr($suffix, strlen('nin_slips/'));
        }

        if ($configured === '') {
            return 'https://dataverify.org/developers/nin_slips/' . $suffix;
        }

        $configuredHost = strtolower((string) parse_url($configured, PHP_URL_HOST));
        if (in_array($configuredHost, ['api.dataverify.org', 'dataverify.org', 'api.dataverify.com.ng', 'api.dataverify.ng', 'dataverify.com.ng', 'dataverify.ng'], true)) {
            if ($configured === 'https://dataverify.org' || $configured === 'https://dataverify.org/nin' || str_ends_with($configured, '/nin')) {
                return 'https://dataverify.org/developers/nin_slips/' . $suffix;
            }
        }

        $trimmed = rtrim($configured, '/');
        $lowerTrimmed = strtolower($trimmed);
        if (str_ends_with(strtolower($trimmed), '.php')) {
            if (str_ends_with($lowerTrimmed, '/nin_api.php')) {
                $base = (string) preg_replace('#/nin_api\.php$#i', '', $trimmed);
                return rtrim($base, '/') . '/nin_slips/' . $suffix;
            }
            return preg_replace('#/[^/]+\.php$#i', '/' . $suffix, $trimmed) ?? ('https://dataverify.org/developers/nin_slips/' . $suffix);
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
        $configured = self::normalizeDomain(trim($configured));
        $suffix = ltrim($path, '/');

        if ($configured === '') {
            return 'https://dataverify.org/developers/bvn_slip/' . $suffix;
        }

        $trimmed = rtrim($configured, '/');
        if (str_ends_with(strtolower($trimmed), '.php')) {
            return preg_replace('#/[^/]+\.php$#i', '/' . $suffix, $trimmed) ?? ('https://dataverify.org/developers/bvn_slip/' . $suffix);
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

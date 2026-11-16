<?php

namespace App\Services;

use App\Models\CustomApi;
use App\Models\VtuTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Datasub247Service
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected ?string $username;
    protected ?string $password;

    public const NETWORK_MTN = 1;
    public const NETWORK_GLO = 2;
    public const NETWORK_AIRTEL = 3;
    public const NETWORK_9MOBILE = 4;

    public const CABLE_GOTV = 1;
    public const CABLE_DSTV = 2;
    public const CABLE_STARTIMES = 3;

    public const EXAM_WAEC = 1;
    public const EXAM_NECO = 2;
    public const EXAM_NABTEB = 3;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.datasub247.base_url', env('DATASUB247_BASE_URL', 'https://datasub247.com')), '/');
        $this->apiKey = config('services.datasub247.api_key', env('DATASUB247_API_KEY'));
        $this->username = config('services.datasub247.username', env('DATASUB247_USERNAME'));
        $this->password = config('services.datasub247.password', env('DATASUB247_PASSWORD'));
    }

    /**
     * Resolve the active API token (from config, CustomApi model, or basic auth exchange).
     */
    public function getApiKey(): ?string
    {
        if (!empty($this->apiKey)) {
            return $this->apiKey;
        }

        // Check if there is an active CustomApi configured for datasub247
        try {
            $customApi = CustomApi::where('provider_identifier', 'datasub247')
                ->whereNotNull('api_key')
                ->where('api_key', '!=', '')
                ->first();

            if ($customApi && !empty($customApi->api_key)) {
                return $customApi->api_key;
            }
        } catch (\Throwable $e) {
            // DB fallback if database is not reachable
        }

        // If username and password are provided, retrieve token via /api/user
        if (!empty($this->username) && !empty($this->password)) {
            return Cache::remember('datasub247_token_' . md5($this->username), 3600 * 12, function () {
                $res = $this->authenticateWithCredentials($this->username, $this->password);
                return $res['AccessToken'] ?? null;
            });
        }

        return null;
    }

    /**
     * Authenticate via Basic Auth to generate or fetch token and balance
     */
    public function authenticateWithCredentials(string $username, string $password): array
    {
        try {
            $basic = base64_encode($username . ':' . $password);
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Basic ' . $basic,
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/user');

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && ($data['status'] ?? '') === 'success') {
                    if (!empty($data['AccessToken'])) {
                        $this->apiKey = $data['AccessToken'];
                    }
                    return [
                        'status' => true,
                        'AccessToken' => $data['AccessToken'] ?? null,
                        'balance' => $data['balance'] ?? null,
                        'username' => $data['username'] ?? null,
                        'raw' => $data,
                    ];
                }
                return ['status' => false, 'message' => $data['message'] ?? 'Authentication failed.'];
            }

            return ['status' => false, 'message' => 'Datasub247 returned HTTP ' . $response->status()];
        } catch (\Throwable $e) {
            Log::error('Datasub247 authentication error: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get account balance and user info
     */
    public function getUserInfo(): array
    {
        $token = $this->getApiKey();
        if (!$token && !empty($this->username) && !empty($this->password)) {
            return $this->authenticateWithCredentials($this->username, $this->password);
        }

        if (!$token) {
            return ['status' => false, 'message' => 'Datasub247 API Key not configured.'];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post($this->baseUrl . '/api/user');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => true,
                    'balance' => $data['balance'] ?? 0,
                    'username' => $data['username'] ?? '',
                    'data' => $data,
                ];
            }

            return ['status' => false, 'message' => 'Unable to fetch user info: ' . $response->body()];
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Purchase Airtime (VTU or SNS)
     */
    public function purchaseAirtime(string $network, string $phone, float $amount, string $planType = 'VTU', ?string $requestId = null, bool $bypass = false): array
    {
        $networkId = $this->resolveNetworkId($network);
        $requestId = $requestId ?: ('Airtime_' . time() . '_' . Str::random(6));

        $payload = [
            'network' => $networkId,
            'phone' => $phone,
            'plan_type' => strtoupper($planType),
            'bypass' => $bypass,
            'amount' => (int) round($amount),
            'request-id' => $requestId,
        ];

        return $this->postJson('/api/topup', $payload, $requestId);
    }

    /**
     * Purchase Data Bundle
     */
    public function purchaseData(string $network, string $phone, int|string $planId, ?string $requestId = null, bool $bypass = false): array
    {
        $networkId = $this->resolveNetworkId($network);
        $requestId = $requestId ?: ('Data_' . time() . '_' . Str::random(6));

        $payload = [
            'network' => $networkId,
            'phone' => $phone,
            'data_plan' => (int) $planId,
            'bypass' => $bypass,
            'request-id' => $requestId,
        ];

        return $this->postJson('/api/data', $payload, $requestId);
    }

    /**
     * Validate Cable Smartcard / IUC Number
     */
    public function validateCable(string|int $cable, string $iuc): array
    {
        $cableId = $this->resolveCableId($cable);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/api/cable/cable-validation', [
                    'iuc' => $iuc,
                    'cable' => $cableId,
                ]);

            $data = $response->json();
            if (is_array($data) && strtolower((string) ($data['status'] ?? '')) === 'success') {
                return [
                    'status' => true,
                    'name' => $data['name'] ?? 'Verified Customer',
                    'raw' => $data,
                ];
            }

            return [
                'status' => false,
                'message' => is_array($data) ? ($data['message'] ?? $data['error'] ?? 'Card validation failed.') : 'Unable to validate card at this time.',
            ];
        } catch (\Throwable $e) {
            Log::error('Datasub247 Cable Validation Error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Validation error: ' . $e->getMessage()];
        }
    }

    /**
     * Purchase Cable TV Subscription
     */
    public function purchaseCable(string|int $cable, string $iuc, int|string $planId, ?string $requestId = null, bool $bypass = false): array
    {
        $cableId = $this->resolveCableId($cable);
        $requestId = $requestId ?: ('Cable_' . time() . '_' . Str::random(6));

        $payload = [
            'cable' => $cableId,
            'iuc' => $iuc,
            'cable_plan' => (int) $planId,
            'bypass' => $bypass,
            'request-id' => $requestId,
        ];

        return $this->postJson('/api/cable', $payload, $requestId);
    }

    /**
     * Validate Electricity Meter
     */
    public function validateMeter(string|int $disco, string $meterNumber, string $meterType = 'prepaid'): array
    {
        $discoId = $this->resolveDiscoId($disco);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/api/bill/bill-validation', [
                    'meter_number' => $meterNumber,
                    'disco' => $discoId,
                    'meter_type' => strtolower($meterType),
                ]);

            $data = $response->json();
            if (is_array($data) && strtolower((string) ($data['status'] ?? '')) === 'success') {
                return [
                    'status' => true,
                    'name' => $data['name'] ?? 'Verified Customer',
                    'address' => $data['address'] ?? null,
                    'raw' => $data,
                ];
            }

            return [
                'status' => false,
                'message' => is_array($data) ? ($data['message'] ?? $data['error'] ?? 'Meter validation failed.') : 'Unable to validate meter at this time.',
            ];
        } catch (\Throwable $e) {
            Log::error('Datasub247 Meter Validation Error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Meter validation error: ' . $e->getMessage()];
        }
    }

    /**
     * Purchase Electricity Bill
     */
    public function purchaseElectricity(string|int $disco, string $meterNumber, string $meterType, float $amount, ?string $requestId = null, bool $bypass = false): array
    {
        $discoId = $this->resolveDiscoId($disco);
        $requestId = $requestId ?: ('Bill_' . time() . '_' . Str::random(6));

        $payload = [
            'disco' => $discoId,
            'meter_type' => strtolower($meterType),
            'meter_number' => $meterNumber,
            'amount' => (int) round($amount),
            'bypass' => $bypass,
            'request-id' => $requestId,
        ];

        $res = $this->postJson('/api/bill', $payload, $requestId);

        // Extract token if generated for prepaid
        if ($res['status'] && isset($res['data'])) {
            $token = $res['data']['token'] ?? $res['data']['Token'] ?? null;
            if ($token) {
                $res['token'] = $token;
            }
        }

        return $res;
    }

    /**
     * Purchase Exam Pin (WAEC, NECO, NABTEB)
     */
    public function purchaseExamPin(string|int $exam, int $quantity = 1, ?string $requestId = null): array
    {
        $examId = $this->resolveExamId($exam);
        $requestId = $requestId ?: ('Exam_' . time() . '_' . Str::random(6));

        $payload = [
            'exam' => $examId,
            'quantity' => max(1, $quantity),
        ];

        $res = $this->postJson('/api/exam', $payload, $requestId);

        if ($res['status'] && isset($res['data']['pin'])) {
            $res['pin'] = $res['data']['pin'];
        }

        return $res;
    }

    /**
     * Send Bulk SMS
     */
    public function sendBulkSms(string $sender, string|array $numbers, string $message, ?string $requestId = null): array
    {
        $formattedNumbers = is_array($numbers) ? implode(',', $numbers) : $numbers;
        $requestId = $requestId ?: ('BULKSMS_' . time() . '_' . Str::random(6));

        $payload = [
            'sender' => substr($sender, 0, 11),
            'number' => $formattedNumbers,
            'message' => $message,
        ];

        return $this->postJson('/api/bulksms', $payload, $requestId);
    }

    /**
     * Purchase Data Card Printing
     */
    public function purchaseDataCard(string|int $network, int $planType, int $quantity, string $cardName, ?string $requestId = null): array
    {
        $networkId = $this->resolveNetworkId($network);
        $requestId = $requestId ?: ('Data_card_' . time() . '_' . Str::random(6));

        $payload = [
            'network' => $networkId,
            'plan_type' => $planType,
            'quantity' => $quantity,
            'card_name' => $cardName,
        ];

        return $this->postJson('/api/data_card', $payload, $requestId);
    }

    /**
     * Purchase Recharge Card Printing (Airtime PINs)
     */
    public function purchaseRechargeCard(string|int $network, int $planType, int $quantity, string $cardName, ?string $requestId = null): array
    {
        $networkId = $this->resolveNetworkId($network);
        $requestId = $requestId ?: ('Recharge_card_' . time() . '_' . Str::random(6));

        $payload = [
            'network' => $networkId,
            'plan_type' => $planType,
            'quantity' => $quantity,
            'card_name' => $cardName,
        ];

        return $this->postJson('/api/recharge_card', $payload, $requestId);
    }

    /**
     * Handle Webhook Notification from Datasub247
     */
    public function handleWebhook(array $payload): array
    {
        $requestId = $payload['request-id'] ?? $payload['request_id'] ?? null;
        $status = strtolower((string) ($payload['status'] ?? ''));
        $responseMsg = (string) ($payload['response'] ?? $payload['message'] ?? '');

        if (!$requestId) {
            return ['status' => false, 'message' => 'Missing request-id.'];
        }

        $vtuTx = VtuTransaction::where('transaction_id', $requestId)->first();
        if ($vtuTx) {
            if (in_array($status, ['success', 'successful', 'true', '1'], true)) {
                $vtuTx->status = 'success';
                $vtuTx->response_payload = array_merge((array) $vtuTx->response_payload, $payload);
                $vtuTx->save();

                app(WalletService::class)->markTransactionSuccess($requestId);
            } elseif (in_array($status, ['failed', 'failure', 'rejected'], true)) {
                $vtuTx->status = 'failed';
                $vtuTx->error_message = $responseMsg ?: 'Transaction failed at provider.';
                $vtuTx->response_payload = array_merge((array) $vtuTx->response_payload, $payload);
                $vtuTx->save();

                if ($vtuTx->direction === 'debit' && $vtuTx->user) {
                    app(WalletService::class)->failAndRefund(
                        $vtuTx->user,
                        (float) $vtuTx->total,
                        'VTU Failed (Webhook)',
                        $requestId
                    );
                }
            }

            return ['status' => true, 'message' => 'Transaction updated successfully.'];
        }

        return ['status' => false, 'message' => 'Transaction not found for reference: ' . $requestId];
    }

    /**
     * Low-level JSON POST handler
     */
    protected function postJson(string $path, array $payload, string $requestId): array
    {
        $token = $this->getApiKey();
        if (!$token) {
            return [
                'status' => false,
                'message' => 'Datasub247 API Token is not configured. Please add DATASUB247_API_KEY to your environment.',
            ];
        }

        $url = $this->baseUrl . $path;

        try {
            $response = Http::timeout(60)
                ->withHeaders($this->getHeaders())
                ->post($url, $payload);

            $json = $response->json();

            if (!is_array($json)) {
                Log::error("Datasub247 Non-JSON Response ($path): " . $response->body());
                return [
                    'status' => false,
                    'message' => 'Invalid response from Datasub247.',
                    'raw' => $response->body(),
                    'reference' => $requestId,
                ];
            }

            $statusVal = strtolower((string) ($json['status'] ?? ''));
            $isSuccess = in_array($statusVal, ['success', 'successful', 'true', '1'], true);

            if ($response->successful() && $isSuccess) {
                return [
                    'status' => true,
                    'message' => $json['message'] ?? 'Transaction completed successfully.',
                    'data' => $json,
                    'reference' => $json['request-id'] ?? $requestId,
                ];
            }

            $errorMessage = $json['message'] ?? $json['msg'] ?? $json['error'] ?? 'Transaction failed at provider.';
            Log::warning("Datasub247 Failure ($path): ", ['payload' => $payload, 'response' => $json]);

            return [
                'status' => false,
                'message' => $errorMessage,
                'data' => $json,
                'reference' => $requestId,
            ];
        } catch (\Throwable $e) {
            Log::error("Datasub247 Request Exception ($path): " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'reference' => $requestId,
            ];
        }
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Token ' . $this->getApiKey(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Map network name or string to Datasub247 integer ID (1: MTN, 2: GLO, 3: AIRTEL, 4: 9MOBILE)
     */
    public function resolveNetworkId(string|int $network): int
    {
        if (is_numeric($network) && in_array((int)$network, [1, 2, 3, 4], true)) {
            return (int) $network;
        }

        $net = strtoupper(trim((string) $network));
        return match ($net) {
            'MTN', '01' => self::NETWORK_MTN,
            'GLO', '02' => self::NETWORK_GLO,
            'AIRTEL', '04' => self::NETWORK_AIRTEL,
            '9MOBILE', 'ETISALAT', '03' => self::NETWORK_9MOBILE,
            default => self::NETWORK_MTN,
        };
    }

    /**
     * Map cable name to Datasub247 integer ID (1: GOTV, 2: DSTV, 3: STARTIMES)
     */
    public function resolveCableId(string|int $cable): int
    {
        if (is_numeric($cable) && in_array((int)$cable, [1, 2, 3], true)) {
            return (int) $cable;
        }

        $c = strtolower(trim((string) $cable));
        return match ($c) {
            'gotv' => self::CABLE_GOTV,
            'dstv' => self::CABLE_DSTV,
            'startimes', 'startime' => self::CABLE_STARTIMES,
            default => self::CABLE_GOTV,
        };
    }

    /**
     * Map exam name to Datasub247 integer ID (1: WAEC, 2: NECO, 3: NABTEB)
     */
    public function resolveExamId(string|int $exam): int
    {
        if (is_numeric($exam) && in_array((int)$exam, [1, 2, 3], true)) {
            return (int) $exam;
        }

        $e = strtolower(trim((string) $exam));
        return match ($e) {
            'waec', 'waecdirect', 'waec-registration' => self::EXAM_WAEC,
            'neco', 'neco-direct' => self::EXAM_NECO,
            'nabteb', 'nabteb-direct' => self::EXAM_NABTEB,
            default => self::EXAM_WAEC,
        };
    }

    /**
     * Map disco identifier to Datasub247 integer ID
     */
    public function resolveDiscoId(string|int $disco): int
    {
        if (is_numeric($disco)) {
            return (int) $disco;
        }

        $d = strtolower(trim((string) $disco));
        return match ($d) {
            'ikeja-electric', 'ikedc' => 1,
            'eko-electric', 'ekedc' => 2,
            'abuja-electric', 'aedc' => 3,
            'kano-electric', 'kedco' => 4,
            'portharcourt-electric', 'phed' => 5,
            'jos-electric', 'jed' => 6,
            'kaduna-electric', 'kaedco' => 7,
            'enugu-electric', 'eedc' => 8,
            'ibadan-electric', 'ibedc' => 9,
            'benin-electric', 'bedc' => 10,
            'yola-electric', 'yedc' => 11,
            default => 1,
        };
    }
}

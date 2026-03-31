<?php

namespace App\Services;

use App\Models\CustomApi;
use App\Models\ApiCenter;
use App\Models\VtuTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VtuHubService
{
    protected $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
    }

    public function validateCustomer(string $serviceType, array $payload, ?int $providerId = null, ?array $providerServiceTypes = null): array
    {
        $catalog = (array) config('vtu_services.service_types', []);
        $def = (array) ($catalog[$serviceType] ?? []);
        if ($serviceType === '' || empty($def)) {
            return ['status' => false, 'message' => 'Unsupported VTU service type.'];
        }

        $rules = (array) ($def['validation_rules'] ?? $def['rules'] ?? []);
        $validator = Validator::make($payload, $rules);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $serviceTypeCandidates = [];
        if (is_array($providerServiceTypes)) {
            foreach ($providerServiceTypes as $t) {
                if (is_string($t) && $t !== '') {
                    $serviceTypeCandidates[] = $t;
                }
            }
        }
        if (empty($serviceTypeCandidates)) {
            $serviceTypeCandidates = match ($serviceType) {
                'vtu_cable_tv' => ['vtu_cable_tv', 'cable_tv'],
                'vtu_electricity' => ['vtu_electricity', 'electricity_bills'],
                default => [$serviceType],
            };
        }
        if (!in_array($serviceType, $serviceTypeCandidates, true)) {
            array_unshift($serviceTypeCandidates, $serviceType);
        }

        $provider = null;
        if ($providerId) {
            $provider = CustomApi::where('id', $providerId)
                ->whereIn('service_type', $serviceTypeCandidates)
                ->where('status', true)
                ->first();
        }
        if (!$provider) {
            $provider = CustomApi::whereIn('service_type', $serviceTypeCandidates)
                ->where('status', true)
                ->orderBy('priority', 'asc')
                ->first();
        }

        if (!$provider) {
            return ['status' => false, 'message' => 'Validation currently unavailable.'];
        }

        $apiKey = $provider->api_key;
        $endpoint = $provider->endpoint;
        $headers = ['Content-Type' => 'application/json'];
        if (is_array($provider->headers)) {
            $headers = array_merge($headers, $provider->headers);
        }
        $providerConfig = is_array($provider->config) ? $provider->config : [];

        $validateEndpoint = isset($providerConfig['validate_endpoint']) ? (string) $providerConfig['validate_endpoint'] : '';
        if ($validateEndpoint === '') {
            $validateEndpoint = $endpoint;
        }

        $basePayload = $payload;
        $payloadToSend = $basePayload;
        if (isset($providerConfig['validate_payload_map']) && is_array($providerConfig['validate_payload_map'])) {
            $mapped = [];
            foreach ($providerConfig['validate_payload_map'] as $fromKey => $toKey) {
                if (!is_string($fromKey) || !is_string($toKey)) {
                    continue;
                }
                if (array_key_exists($fromKey, $basePayload)) {
                    $mapped[$toKey] = $basePayload[$fromKey];
                }
            }
            $payloadToSend = $mapped;
        }
        if (isset($providerConfig['validate_static_payload']) && is_array($providerConfig['validate_static_payload'])) {
            $payloadToSend = array_merge($payloadToSend, $providerConfig['validate_static_payload']);
        }

        $url = rtrim($validateEndpoint, '/');
        $pathSuffix = isset($providerConfig['validate_path_suffix']) ? (string) $providerConfig['validate_path_suffix'] : '';
        if ($pathSuffix !== '') {
            $url = $url . '/' . ltrim($pathSuffix, '/');
        }

        $query = isset($providerConfig['validate_query']) && is_array($providerConfig['validate_query']) ? $providerConfig['validate_query'] : [];
        if (!empty($query)) {
            $url = $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $timeout = $provider->timeout_seconds ?? 60;
        $http = Http::withHeaders(array_merge($headers, [
            'Authorization' => 'Bearer ' . $apiKey,
        ]))->timeout($timeout);

        $method = strtoupper((string) ($providerConfig['validate_method'] ?? 'POST'));
        $response = match ($method) {
            'GET' => $http->get($url, $payloadToSend),
            default => $http->post($url, $payloadToSend),
        };

        if (!$response->successful()) {
            return ['status' => false, 'message' => 'Unable to validate meter at this time.'];
        }

        $json = $response->json();
        if (!is_array($json)) {
            return ['status' => false, 'message' => 'Invalid validation response.'];
        }

        $dataPath = isset($providerConfig['validate_data_path']) ? (string) $providerConfig['validate_data_path'] : '';
        $data = $dataPath !== '' ? $this->extractArrayFromResponse($json, $dataPath) : $json;

        $name = $this->extractStringFromResponse($data, (string) ($providerConfig['validate_customer_name_path'] ?? 'data.name'));
        $address = $this->extractStringFromResponse($data, (string) ($providerConfig['validate_customer_address_path'] ?? 'data.address'));

        return [
            'status' => true,
            'message' => 'Meter validated.',
            'data' => $json,
            'customer' => [
                'name' => $name ?: null,
                'address' => $address ?: null,
            ],
        ];
    }

    /**
     * Unified processor for all VTU services
     */
    public function processRequest(array $params)
    {
        $user = Auth::user();
        $serviceType = (string) ($params['service_type'] ?? '');
        $payload = (array) ($params['payload'] ?? []);
        $requestedAmount = isset($params['amount']) ? (float) $params['amount'] : (float) ($payload['amount'] ?? 0);

        $catalog = (array) config('vtu_services.service_types', []);
        $def = (array) ($catalog[$serviceType] ?? []);
        if ($serviceType === '' || empty($def)) {
            return ['status' => false, 'message' => 'Unsupported VTU service type.'];
        }

        $rules = (array) ($def['rules'] ?? []);
        $validator = Validator::make($payload, $rules);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $direction = (string) ($def['direction'] ?? 'debit');
        $orderType = (string) ($params['order_type'] ?? $def['order_type'] ?? 'VTU Transaction');
        $txPrefix = (string) ($params['tx_prefix'] ?? $def['tx_prefix'] ?? 'VTU');

        $limits = (array) ($def['limits'] ?? []);
        $min = isset($limits['min']) ? (float) $limits['min'] : 0;
        $max = isset($limits['max']) ? (float) $limits['max'] : 0;
        if ($requestedAmount <= 0) {
            return ['status' => false, 'message' => 'Amount is required.'];
        }
        if ($min > 0 && $requestedAmount < $min) {
            return ['status' => false, 'message' => 'Minimum amount is ' . number_format($min, 2) . '.'];
        }
        if ($max > 0 && $requestedAmount > $max) {
            return ['status' => false, 'message' => 'Maximum amount is ' . number_format($max, 2) . '.'];
        }

        // 1. Identify Provider
        $providerId = isset($params['provider_id']) ? (int) $params['provider_id'] : null;
        $serviceTypeCandidates = match ($serviceType) {
            'vtu_cable_tv' => ['vtu_cable_tv', 'cable_tv'],
            'vtu_electricity' => ['vtu_electricity', 'electricity_bills'],
            default => [$serviceType],
        };
        if (isset($params['provider_service_types']) && is_array($params['provider_service_types']) && !empty($params['provider_service_types'])) {
            $serviceTypeCandidates = array_values(array_filter($params['provider_service_types'], fn ($v) => is_string($v) && $v !== ''));
        }
        $provider = null;
        if ($providerId) {
            $provider = CustomApi::where('id', $providerId)
                ->whereIn('service_type', $serviceTypeCandidates)
                ->where('status', true)
                ->first();
        }
        if (!$provider) {
            $provider = CustomApi::whereIn('service_type', $serviceTypeCandidates)->where('status', true)->orderBy('priority', 'asc')->first();
        }
        
        $apiKey = null;
        $endpoint = null;
        $headers = ['Content-Type' => 'application/json'];
        $providerConfig = [];
        $fee = 0.0;

        if ($provider) {
            $apiKey = $provider->api_key;
            $endpoint = $provider->endpoint;
            if (is_array($provider->headers)) {
                $headers = array_merge($headers, $provider->headers);
            }
            $providerConfig = is_array($provider->config) ? $provider->config : [];
        } else {
            // Fallback to legacy ApiCenter
            $apiCenter = ApiCenter::first();
            if ($apiCenter && $apiCenter->ade_apikey) {
                $apiKey = $apiCenter->ade_apikey;
                $endpoint = $this->getLegacyEndpoint($apiCenter, $serviceType);
            }
        }

        if (!$apiKey || !$endpoint) {
            return ['status' => false, 'message' => 'Service currently unavailable. Please try again later.'];
        }

        $minOverride = isset($providerConfig['min_amount']) ? (float) $providerConfig['min_amount'] : null;
        $maxOverride = isset($providerConfig['max_amount']) ? (float) $providerConfig['max_amount'] : null;
        if ($minOverride !== null && $minOverride > 0 && $requestedAmount < $minOverride) {
            return ['status' => false, 'message' => 'Minimum amount is ' . number_format($minOverride, 2) . '.'];
        }
        if ($maxOverride !== null && $maxOverride > 0 && $requestedAmount > $maxOverride) {
            return ['status' => false, 'message' => 'Maximum amount is ' . number_format($maxOverride, 2) . '.'];
        }

        $feeType = (string) ($providerConfig['fee_type'] ?? $providerConfig['commission_type'] ?? 'flat');
        $feeValue = (float) ($providerConfig['fee_value'] ?? $providerConfig['commission_value'] ?? 0);
        if ($feeValue > 0) {
            if ($feeType === 'percent') {
                $fee = round(($requestedAmount * $feeValue) / 100, 2);
            } else {
                $fee = round($feeValue, 2);
            }
        }

        $total = $direction === 'credit'
            ? max(0, $requestedAmount - $fee)
            : ($requestedAmount + $fee);

        $txId = (string) ($params['request_id'] ?? '');
        if ($txId !== '') {
            $existing = VtuTransaction::where('transaction_id', $txId)->first();
            if ($existing) {
                return [
                    'status' => $existing->status === 'success',
                    'message' => $existing->status === 'success' ? 'Transaction successful!' : ($existing->error_message ?: 'Transaction pending/failed.'),
                    'data' => $existing->response_payload,
                    'reference' => $existing->transaction_id,
                ];
            }
        }

        $txId = $txId !== '' ? $txId : ($txPrefix . '-' . strtoupper(bin2hex(random_bytes(4))));

        $vtuTx = VtuTransaction::create([
            'user_id' => $user?->id,
            'custom_api_id' => $provider?->id,
            'service_type' => $serviceType,
            'direction' => $direction,
            'amount' => $requestedAmount,
            'fee' => $fee,
            'total' => $total,
            'transaction_id' => $txId,
            'status' => 'pending',
            'request_payload' => $payload,
        ]);

        if ($direction === 'debit') {
            $debit = $this->wallet->debit($user, $total, $orderType, $txPrefix, $txId);
            if (!$debit['ok']) {
                $vtuTx->status = 'failed';
                $vtuTx->error_message = (string) ($debit['message'] ?? 'Unable to debit wallet.');
                $vtuTx->save();
                return ['status' => false, 'message' => $vtuTx->error_message];
            }
        }

        // 3. Call API
        try {
            $basePayload = $payload;
            $basePayload['request_id'] = $txId;

            $payloadToSend = $basePayload;
            if (isset($providerConfig['payload_map']) && is_array($providerConfig['payload_map'])) {
                $mapped = [];
                foreach ($providerConfig['payload_map'] as $fromKey => $toKey) {
                    if (!is_string($fromKey) || !is_string($toKey)) {
                        continue;
                    }
                    if (array_key_exists($fromKey, $basePayload)) {
                        $mapped[$toKey] = $basePayload[$fromKey];
                    }
                }
                $payloadToSend = $mapped;
            }
            if (isset($providerConfig['static_payload']) && is_array($providerConfig['static_payload'])) {
                $payloadToSend = array_merge($payloadToSend, $providerConfig['static_payload']);
            }

            $url = rtrim($endpoint, '/');
            $pathSuffix = isset($providerConfig['path_suffix']) ? (string) $providerConfig['path_suffix'] : '';
            if ($pathSuffix !== '') {
                $url = $url . '/' . ltrim($pathSuffix, '/');
            }

            $query = isset($providerConfig['query']) && is_array($providerConfig['query']) ? $providerConfig['query'] : [];
            if (!empty($query)) {
                $url = $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
            }

            $timeout = $provider?->timeout_seconds ?? 60;
            $tries = max(1, ((int) ($provider?->retry_count ?? 0)) + 1);
            $delayMs = max(0, (int) ($provider?->retry_delay_ms ?? 0));

            $http = Http::withHeaders(array_merge($headers, [
                'Authorization' => 'Bearer ' . $apiKey,
            ]))->timeout($timeout);

            $method = strtoupper((string) ($providerConfig['method'] ?? 'POST'));
            
            $response = Http::withHeaders(array_merge($headers, [
                'Authorization' => 'Bearer ' . $apiKey,
            ]))
            ->timeout($timeout)
            ->retry($tries, $delayMs)
            ->send($method, $url, [
                'json' => $payloadToSend,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $providerRef = is_array($json) ? (string) ($json['reference'] ?? $json['transaction_id'] ?? $json['data']['reference'] ?? '') : '';

                if ($direction === 'credit') {
                    $creditAmount = $this->extractAmountFromResponse($json, (string) ($providerConfig['credit_amount_path'] ?? 'data.amount'));
                    if ($creditAmount <= 0) {
                        $creditAmount = $total;
                    }
                    $creditAmount = max(0, $creditAmount);
                    $credit = $this->wallet->credit($user, $creditAmount, $orderType, $txId);
                    if (!$credit['ok']) {
                        $vtuTx->status = 'failed';
                        $vtuTx->error_message = 'Unable to credit wallet.';
                        $vtuTx->response_payload = $json;
                        $vtuTx->provider_reference = $providerRef ?: null;
                        $vtuTx->save();
                        return ['status' => false, 'message' => $vtuTx->error_message];
                    }
                } else {
                    $this->wallet->markTransactionSuccess($txId);
                }

                $vtuTx->status = 'success';
                $vtuTx->response_payload = $json;
                $vtuTx->provider_reference = $providerRef ?: null;
                $vtuTx->save();
                return [
                    'status' => true, 
                    'message' => 'Transaction successful!',
                    'data' => $json,
                    'reference' => $txId,
                    'breakdown' => [
                        'direction' => $direction,
                        'amount' => $requestedAmount,
                        'fee' => $fee,
                        'total' => $total,
                    ],
                ];
            } else {
                Log::error("VTU API Error ($serviceType): ", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'endpoint' => $endpoint
                ]);
                throw new \Exception('Upstream Service Error');
            }
        } catch (\Exception $e) {
            // 4. Refund on Failure
            if ($direction === 'debit') {
                $this->wallet->failAndRefund($user, $total, $orderType, $txId);
            }
            $vtuTx->status = 'failed';
            $vtuTx->error_message = 'Transaction failed. ' . ($direction === 'debit' ? 'Your wallet has been refunded.' : '');
            $vtuTx->save();
            return ['status' => false, 'message' => $vtuTx->error_message, 'reference' => $txId];
        }
    }

    protected function getLegacyEndpoint($apiCenter, $serviceType)
    {
        switch ($serviceType) {
            case 'vtu_airtime': return $apiCenter->ade_endpoint_airtime;
            case 'vtu_data': return $apiCenter->ade_endpoint_data;
            case 'vtu_cable_tv': return $apiCenter->ade_endpoint_bill; // Using bill for cable in legacy
            case 'vtu_electricity': return $apiCenter->ade_endpoint_bill;
            case 'vtu_internet': return $apiCenter->ade_endpoint_bill;
            case 'vtu_betting': return $apiCenter->ade_endpoint_bill;
            case 'vtu_epin': return $apiCenter->ade_endpoint_bill;
            case 'vtu_airtime_to_cash': return $apiCenter->ade_endpoint_bill;
            case 'education_waec':
            case 'education_neco':
            case 'education_nabteb':
            case 'education_jamb':
                return $apiCenter->ade_endpoint_exam;
            default: return null;
        }
    }

    protected function extractAmountFromResponse(mixed $payload, string $path): float
    {
        if (!is_array($payload) || $path === '') {
            return 0.0;
        }

        $segments = array_values(array_filter(explode('.', $path), fn ($s) => $s !== ''));
        $cursor = $payload;
        foreach ($segments as $seg) {
            if (!is_array($cursor) || !array_key_exists($seg, $cursor)) {
                return 0.0;
            }
            $cursor = $cursor[$seg];
        }

        if (is_numeric($cursor)) {
            return (float) $cursor;
        }
        return 0.0;
    }

    protected function extractArrayFromResponse(mixed $payload, string $path): array
    {
        if (!is_array($payload) || $path === '') {
            return [];
        }
        $segments = array_values(array_filter(explode('.', $path), fn ($s) => $s !== ''));
        $cursor = $payload;
        foreach ($segments as $seg) {
            if (!is_array($cursor) || !array_key_exists($seg, $cursor)) {
                return [];
            }
            $cursor = $cursor[$seg];
        }
        return is_array($cursor) ? $cursor : [];
    }

    protected function extractStringFromResponse(mixed $payload, string $path): string
    {
        if (!is_array($payload) || $path === '') {
            return '';
        }
        $segments = array_values(array_filter(explode('.', $path), fn ($s) => $s !== ''));
        $cursor = $payload;
        foreach ($segments as $seg) {
            if (!is_array($cursor) || !array_key_exists($seg, $cursor)) {
                return '';
            }
            $cursor = $cursor[$seg];
        }
        return is_scalar($cursor) ? (string) $cursor : '';
    }
}

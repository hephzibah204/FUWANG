<?php

namespace App\Services;

use App\Models\CustomApi;
use App\Models\ApiCenter;
use App\Models\VtuTransaction;
use App\Services\WalletService;
use App\Services\Datasub247Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Support\VtuProviderStatus;

class VtuHubService
{
    protected $wallet;
    protected Datasub247Service $datasub247;

    public function __construct(WalletService $wallet, Datasub247Service $datasub247)
    {
        $this->wallet = $wallet;
        $this->datasub247 = $datasub247;
    }

    public function getDatasub247(): Datasub247Service
    {
        return $this->datasub247;
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


        if (!app(VtuProviderService::class)->supports($serviceType)) {
            return ['status' => false, 'message' => 'This service is not available through Datasub247 yet.'];
        }
        if ($providerId && !CustomApi::whereKey($providerId)->where('provider_identifier', 'datasub247')->where('status', true)->exists()) {
            return ['status' => false, 'message' => 'Only Datasub247 is available.'];
        }
        $provider = null;
        if ($providerId) {
            $provider = CustomApi::where('provider_identifier', 'datasub247')->where('id', $providerId)
                ->whereIn('service_type', $serviceTypeCandidates)
                ->where('status', true)
                ->first();
        }
        if ($providerId && !$provider) {
            return ['status' => false, 'message' => 'Selected band is unavailable. Please choose another band.'];
        }
        if (!$provider) {
            $provider = CustomApi::where('provider_identifier', 'datasub247')->whereIn('service_type', $serviceTypeCandidates)
                ->where('status', true)
                ->orderBy('priority', 'asc')
                ->first();
        }

        if (!$provider && CustomApi::where('provider_identifier', 'datasub247')->whereIn('service_type', $serviceTypeCandidates)->exists()) {
            return ['status' => false, 'message' => 'All bands for this service are disabled.'];
        }

        $isDatasub247 = ($provider && $provider->provider_identifier === 'datasub247')
            || (!$provider && !empty($this->datasub247->getApiKey()));

        if (!$isDatasub247) {
            return ['status' => false, 'message' => 'Datasub247 is not configured for this service.'];
        }
        if ($isDatasub247) {
            $adapter = $provider ? $this->datasub247->forProvider($provider) : $this->datasub247;
            if ($serviceType === 'vtu_electricity' || in_array('electricity_bills', $serviceTypeCandidates, true)) {
                $disco = $payload['serviceID'] ?? $payload['disco'] ?? 1;
                $meterNum = $payload['meter_number'] ?? '';
                $meterType = $payload['variation_code'] ?? $payload['meter_type'] ?? 'prepaid';
                $valRes = $adapter->validateMeter($disco, $meterNum, $meterType);
                if ($valRes['status']) {
                    return [
                        'status' => true,
                        'message' => 'Meter validated.',
                        'data' => $valRes['raw'] ?? [],
                        'customer' => [
                            'name' => $valRes['name'] ?? null,
                            'address' => $valRes['address'] ?? null,
                        ],
                    ];
                }
                return $valRes;
            }

            if ($serviceType === 'vtu_cable_tv' || in_array('cable_tv', $serviceTypeCandidates, true)) {
                $cable = $payload['serviceID'] ?? $payload['cable'] ?? 1;
                $iuc = $payload['smart_card_number'] ?? $payload['iuc'] ?? '';
                $valRes = $adapter->validateCable($cable, $iuc);
                if ($valRes['status']) {
                    return [
                        'status' => true,
                        'message' => 'Card validated.',
                        'data' => $valRes['raw'] ?? [],
                        'customer' => [
                            'name' => $valRes['name'] ?? null,
                        ],
                    ];
                }
                return $valRes;
            }
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
        try {
        $response = match ($method) {
            'GET' => $http->get($url, $payloadToSend),
            default => $http->post($url, $payloadToSend),
        };
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => 'Unable to contact validation provider. Please try again.'];
        }

        if (!$response->successful()) {
            return ['status' => false, 'message' => 'Unable to validate meter at this time.'];
        }

        $json = $response->json();
        if (VtuProviderStatus::state($json) !== 'success') {
            return ['status' => false, 'message' => 'Customer could not be verified.'];
        }

        $dataPath = isset($providerConfig['validate_data_path']) ? (string) $providerConfig['validate_data_path'] : '';
        $data = $dataPath !== '' ? $this->extractArrayFromResponse($json, $dataPath) : $json;

        $name = $this->extractStringFromResponse($data, (string) ($providerConfig['validate_customer_name_path'] ?? 'data.name'));
        $address = $this->extractStringFromResponse($data, (string) ($providerConfig['validate_customer_address_path'] ?? 'data.address'));

        if ($name === '') {
            return ['status' => false, 'message' => 'Provider did not return a customer name.'];
        }
        return [
            'status' => true,
            'message' => 'Customer verified.',
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
        $user = $params['user'] ?? Auth::user();
        if (!$user) { return ['status' => false, 'message' => 'Unauthenticated user.']; }
        $key = (string) ($params['request_id'] ?? request()->header('Idempotency-Key') ?? request()->input('request_id', ''));
        if ($key === '') {
            $fingerprint = hash('sha256', json_encode([$user->id, $params['service_type'] ?? '', $params['provider_id'] ?? null, $params['payload'] ?? []]));
            $key = Cache::remember('vtu-retry:'.$fingerprint, 120, fn () => 'VTU-'.bin2hex(random_bytes(12)));
        }
        if (strlen($key) > 100 || !preg_match('/^[A-Za-z0-9_-]+$/', $key)) {
            return ['status' => false, 'message' => 'Invalid request reference.'];
        }
        $params['request_id'] = $key;
        $lock = Cache::lock('vtu-request:'.hash('sha256', $key), 180);
        if (!$lock->get()) {
            return ['status' => false, 'pending' => true, 'reference' => $key, 'message' => 'This request is already processing. Do not repeat the purchase.'];
        }
        try {
            $existing = VtuTransaction::where('transaction_id', $key)->first();
            if ($existing) {
                if ((int) $existing->user_id !== (int) $user->id || $existing->service_type !== ($params['service_type'] ?? '')
                    || (!empty($params['provider_id']) && (int) $existing->custom_api_id !== (int) $params['provider_id'])
                    || $existing->request_payload !== (array) ($params['payload'] ?? [])) {
                    return ['status' => false, 'message' => 'Reference already used for another request.'];
                }
                return $this->transactionResult($existing);
            }
            return $this->processRequestUnlocked($params);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ['status' => false, 'message' => $e->errors()[array_key_first($e->errors())][0], 'errors' => $e->errors()];
        } finally {
            $lock->release();
        }
    }

    public function transactionResult(VtuTransaction $tx): array
    {
        $data = (array) $tx->response_payload;
        return ['status' => $tx->status === 'success', 'pending' => $tx->status === 'pending',
            'message' => $tx->status === 'success' ? 'Transaction successful.' : ($tx->error_message ?: 'Transaction pending confirmation.'),
            'data' => $data, 'reference' => $tx->transaction_id,
            'pin' => $data['pin'] ?? data_get($data, 'data.pin'),
            'token' => $data['token'] ?? data_get($data, 'data.token'),
            'breakdown' => ['amount' => $tx->amount, 'fee' => $tx->fee, 'total' => $tx->total, 'direction' => $tx->direction, 'discount' => $tx->direction === 'debit' ? max(0, round($tx->amount + $tx->fee - $tx->total, 2)) : 0]];
    }

    protected function processRequestUnlocked(array $params)
    {
        $user = $params['user'] ?? Auth::user();
        $serviceType = (string) ($params['service_type'] ?? '');
        $payload = (array) ($params['payload'] ?? []);
        $originalPayload = $payload;
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
        if (!is_finite($requestedAmount) || $requestedAmount <= 0) {
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
        if (!app(VtuProviderService::class)->supports($serviceType)) {
            return ['status' => false, 'message' => 'This service is not available through Datasub247 yet.'];
        }
        if ($providerId && !CustomApi::whereKey($providerId)->where('provider_identifier', 'datasub247')->where('status', true)->exists()) {
            return ['status' => false, 'message' => 'Only Datasub247 is available.'];
        }

        $serviceTypeCandidates = match ($serviceType) {
            'vtu_cable_tv' => ['vtu_cable_tv', 'cable_tv'],
            'vtu_electricity' => ['vtu_electricity', 'electricity_bills'],
            default => [$serviceType],
        };
        if (isset($params['provider_service_types']) && is_array($params['provider_service_types']) && !empty($params['provider_service_types'])) {
            $serviceTypeCandidates = array_values(array_filter($params['provider_service_types'], fn ($v) => is_string($v) && $v !== ''));
        }
        $provider = null;
        if ($serviceType === 'vtu_data') {
            $provider = app(VtuCatalogService::class)->dataProvider($providerId);
            if ($providerId && !$provider) {
                $provider = app(VtuCatalogService::class)->dataProvider(null);
            }
        } else {
            if ($providerId) {
                $provider = CustomApi::where('provider_identifier', 'datasub247')->where('id', $providerId)
                    ->whereIn('service_type', $serviceTypeCandidates)
                    ->where('status', true)
                    ->first();
            }
            if ($providerId && !$provider) {
                return ['status' => false, 'message' => 'Selected band is unavailable. Please choose another band.'];
            }
            if (!$provider) {
                $provider = CustomApi::where('provider_identifier', 'datasub247')->whereIn('service_type', $serviceTypeCandidates)->where('status', true)->orderBy('priority', 'asc')->first();
            }
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
            $apiCenter = \Illuminate\Support\Facades\Schema::hasTable('api_centers') ? ApiCenter::first() : null;
            if ($apiCenter && $apiCenter->ade_apikey) {
                $apiKey = $apiCenter->ade_apikey;
                $endpoint = $this->getLegacyEndpoint($apiCenter, $serviceType);
            }
        }

        $isDatasub247 = ($provider && $provider->provider_identifier === 'datasub247')
            || (!$provider && !empty($this->datasub247->getApiKey()));

        if (!$provider && CustomApi::where('provider_identifier', 'datasub247')->whereIn('service_type', $serviceTypeCandidates)->exists()) {
            return ['status' => false, 'message' => 'All bands for this service are disabled.'];
        }

        if ($isDatasub247 && $provider && empty($provider->api_key)) {
            $provider->api_key = $this->datasub247->getApiKey();
            if (empty($provider->api_key)) {
                return ['status' => false, 'message' => 'Selected band credentials are not configured.'];
            }
        }
        $isClubkonnect = false;
        if (!$isDatasub247 && !$endpoint) {
            return ['status' => false, 'message' => 'Service currently unavailable. Please try again later.'];
        }

        if ($isDatasub247 && !in_array($serviceType, ['vtu_airtime', 'vtu_data', 'vtu_cable_tv', 'vtu_electricity', 'vtu_epin', 'vtu_bulksms', 'vtu_data_card', 'vtu_recharge_card'], true)) {
            return ['status' => false, 'message' => 'This service needs a compatible band configured by the administrator.'];
        }
        if ($isDatasub247 && $serviceType === 'vtu_epin' && !in_array(strtolower((string) ($payload['serviceID'] ?? '')), ['waec', 'neco', 'nabteb'], true)) {
            return ['status' => false, 'message' => 'This exam product is not supported by the selected band.'];
        }
        if (in_array($serviceType, ['vtu_data', 'vtu_cable_tv', 'vtu_internet', 'vtu_epin', 'vtu_data_card', 'vtu_recharge_card'], true)) {
            $requestedAmount = app(VtuCatalogService::class)->price($serviceType, $payload, $provider?->id);
            $payload['amount'] = $requestedAmount;
            if (!is_finite($requestedAmount) || $requestedAmount <= 0 || ($min > 0 && $requestedAmount < $min) || ($max > 0 && $requestedAmount > $max)) {
                return ['status' => false, 'message' => 'Product price is outside the allowed transaction limits.'];
            }
        }
        if ($serviceType === 'vtu_bulksms') {
            $numbers = \App\Support\SmsSegments::recipients($payload['recipients']);
            $payload['recipients'] = implode(',', $numbers);
            $rate = (float) \App\Models\SystemSetting::get('vtu_bulk_sms_rate', 4);
            $requestedAmount = round(count($numbers) * \App\Support\SmsSegments::count($payload['message']) * $rate, 2);
            $payload['amount'] = $requestedAmount;
            if (!is_finite($requestedAmount) || $requestedAmount <= 0 || $requestedAmount > $max) {
                return ['status' => false, 'message' => 'SMS total is outside transaction limits.'];
            }
        }
        if ($isDatasub247) {
            $id = match ($serviceType) {
                'vtu_data' => $payload['plan_id'] ?? null,
                'vtu_cable_tv' => $payload['variation_code'] ?? null,
                'vtu_data_card', 'vtu_recharge_card' => $payload['plan_type'] ?? null,
                default => null,
            };
            if ($serviceType === 'vtu_data' && (!ctype_digit((string) $id) || (int) $id < 1)) {
                if (!empty($payload['catalog_plan_id']) && ctype_digit((string) $payload['catalog_plan_id'])) {
                    $payload['plan_id'] = $payload['catalog_plan_id'];
                    $id = $payload['plan_id'];
                }
            }
            if ($id !== null && (!ctype_digit((string) $id) || (int) $id < 1)) {
                return ['status' => false, 'message' => 'This product needs a valid provider plan mapping.'];
            }
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

        if ($serviceType === 'vtu_electricity') {
            $fee += max(0, (float) \App\Models\SystemSetting::get('vtu_electricity_fee', 0));
        }
        $discount = 0.0;
        $isDeveloper = ($params['is_developer_api'] ?? request()->is('api/*'))
            || ($user?->api_access_status === 'approved')
            || ($user?->hasCustomApiPricing() ?? false);

        if ($isDeveloper && $user) {
            if ($serviceType === 'vtu_airtime') {
                $discRate = (float) $user->getDeveloperApiPrice('developer_api_vtu_airtime_discount', (float) \App\Models\SystemSetting::get('developer_api_vtu_airtime_discount', 2.0));
                if ($discRate > 0) {
                    $discount = round(($requestedAmount * $discRate) / 100, 2);
                }
            } elseif ($serviceType === 'vtu_data') {
                $discRate = (float) $user->getDeveloperApiPrice('developer_api_vtu_data_discount', (float) \App\Models\SystemSetting::get('developer_api_vtu_data_discount', 3.0));
                if ($discRate > 0) {
                    $discount = round(($requestedAmount * $discRate) / 100, 2);
                }
            } elseif ($serviceType === 'vtu_cable_tv') {
                $customFee = $user->getDeveloperApiPrice('developer_api_vtu_cable_fee', (float) \App\Models\SystemSetting::get('developer_api_vtu_cable_fee', $fee));
                $fee = (float) $customFee;
            } elseif ($serviceType === 'vtu_electricity') {
                $customFee = $user->getDeveloperApiPrice('developer_api_vtu_electricity_fee', (float) \App\Models\SystemSetting::get('developer_api_vtu_electricity_fee', $fee));
                $fee = (float) $customFee;
            }
        }

        if ($serviceType === 'vtu_airtime' && !$isDeveloper) {
            $rate = max(0, min(99, (float) \App\Models\SystemSetting::get('vtu_airtime_discount_percent', 2)));
            $discount = round($requestedAmount * $rate / 100, 2);
        }
        if ($discount < 0 || $discount >= $requestedAmount || $fee < 0) {
            return ['status' => false, 'message' => 'Invalid pricing configuration.'];
        }
        $total = $direction === 'credit'
            ? max(0, $requestedAmount - $fee)
            : max(0, ($requestedAmount - $discount) + $fee);

        if (!is_finite($total) || $total <= 0) {
            return ['status' => false, 'message' => 'Invalid total. Please contact support.'];
        }
        if ($params['quote_only'] ?? false) {
            return ['status' => true, 'breakdown' => compact('direction', 'requestedAmount', 'discount', 'fee', 'total') + ['amount' => $requestedAmount]];
        }

        $txId = (string) ($params['request_id'] ?? '');
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
            'request_payload' => $originalPayload,
            'response_payload' => $isClubkonnect ? ['_provider' => 'clubkonnect'] : null,
        ]);

        if ($direction === 'debit') {
            if (!$user) {
                $vtuTx->status = 'failed';
                $vtuTx->error_message = 'Unauthenticated user.';
                $vtuTx->save();
                return ['status' => false, 'message' => 'Unauthenticated user.'];
            }
            try {
                $debit = $this->wallet->debit($user, $total, $orderType, $txPrefix, $txId);
            } catch (\Throwable $e) {
                $vtuTx->update(['status' => 'failed', 'error_message' => 'Wallet transaction could not be started.']);
                throw $e;
            }
            if (!$debit['ok']) {
                $vtuTx->status = 'failed';
                $vtuTx->error_message = (string) ($debit['message'] ?? 'Unable to debit wallet.');
                $vtuTx->save();
                return ['status' => false, 'message' => $vtuTx->error_message];
            }
        }

        // 3. Call API
        try {
            if ($isClubkonnect) {
                $result = app(ClubkonnectPrintingService::class)->purchase($serviceType, $payload, $txId);
                $state = ($result['pending'] ?? false) ? 'pending' : (($result['status'] ?? false) ? 'success' : 'failed');
                return $this->transactionResult(app(VtuSettlementService::class)->settle($vtuTx, $state, $result['data'] ?? [], $result['reference'] ?? $txId));
            }
            if ($isDatasub247) {
                $apiResult = $this->executeDatasub247($serviceType, $payload, $requestedAmount, $txId, $provider ? $this->datasub247->forProvider($provider) : null);
                $json = (array) ($apiResult['data'] ?? []);
                $state = ($apiResult['pending'] ?? false) ? 'pending' : (($apiResult['status'] ?? false) ? 'success' : 'failed');
                if ($state === 'success' && in_array($serviceType, ['vtu_data_card', 'vtu_recharge_card'], true) && empty($json['pin']) && empty($json['pins'])) {
                    $state = 'pending';
                }
                $settled = app(VtuSettlementService::class)->settle($vtuTx, $state, $json, (string) ($json['request-id'] ?? $json['reference'] ?? $txId));
                return $this->transactionResult($settled);
            }

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
            $method = strtoupper((string) ($providerConfig['method'] ?? 'POST'));
            
            $response = Http::withHeaders(array_merge($headers, [
                'Authorization' => 'Bearer ' . $apiKey,
            ]))
            ->timeout($timeout)
            ->send($method, $url, [
                $method === 'GET' ? 'query' : 'json' => $payloadToSend,
            ]);

            $json = $response->json();
            $json = is_array($json) ? $this->normalizeResponse($json, $providerConfig) : $json;
            $state = $response->successful() ? VtuProviderStatus::state($json, $providerConfig) : 'pending';
            $json = is_array($json) ? $json : [];
            if ($direction === 'credit' && $state === 'success') {
                $creditAmount = $this->extractAmountFromResponse($json, (string) ($providerConfig['credit_amount_path'] ?? 'data.amount'));
                data_set($json, 'data.amount', $creditAmount);
            }
            $providerRef = (string) ($json['reference'] ?? $json['transaction_id'] ?? data_get($json, 'data.reference', ''));
            return $this->transactionResult(app(VtuSettlementService::class)->settle($vtuTx, $state, $json, $providerRef));
        } catch (\Throwable $e) {
            Log::warning('VTU requires reconciliation', ['reference' => $txId, 'error' => $e->getMessage()]);
            return $this->transactionResult(app(VtuSettlementService::class)->settle($vtuTx, 'pending'));
        }
    }

    public function normalizeResponse(array $json, array $config = []): array
    {
        foreach (['pin', 'pins', 'token'] as $key) {
            if (!empty($config[$key.'_path'])) {
                $json[$key] = data_get($json, $config[$key.'_path']);
            }
        }
        return $json;
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

    protected function isPayloadSuccessful(mixed $json): bool
    {
        return VtuProviderStatus::state($json) === 'success';
    }

    /**
     * Dispatch transaction to Datasub247 service
     */
    public function executeDatasub247(string $serviceType, array $payload, float $amount, string $txId, ?Datasub247Service $adapter = null): array
    {
        $adapter ??= $this->datasub247;
        return match ($serviceType) {
            'vtu_airtime' => $adapter->purchaseAirtime(
                (string) ($payload['network'] ?? 1),
                (string) ($payload['phone'] ?? ''),
                $amount,
                (string) ($payload['plan_type'] ?? 'VTU'),
                $txId,
                (bool) ($payload['bypass'] ?? false)
            ),
            'vtu_data' => $adapter->purchaseData(
                (string) ($payload['network'] ?? 1),
                (string) ($payload['phone'] ?? ''),
                $payload['plan_id'] ?? $payload['data_plan'] ?? 1,
                $txId,
                (bool) ($payload['bypass'] ?? false)
            ),
            'vtu_cable_tv' => $adapter->purchaseCable(
                $payload['serviceID'] ?? $payload['cable'] ?? 1,
                (string) ($payload['smart_card_number'] ?? $payload['iuc'] ?? ''),
                $payload['variation_code'] ?? $payload['cable_plan'] ?? 1,
                $txId,
                (bool) ($payload['bypass'] ?? false)
            ),
            'vtu_electricity' => $adapter->purchaseElectricity(
                $payload['serviceID'] ?? $payload['disco'] ?? 1,
                (string) ($payload['meter_number'] ?? ''),
                (string) ($payload['variation_code'] ?? $payload['meter_type'] ?? 'prepaid'),
                $amount,
                $txId,
                (bool) ($payload['bypass'] ?? false)
            ),
            'vtu_epin', 'education_waec', 'education_neco', 'education_nabteb', 'education_jamb' => $adapter->purchaseExamPin(
                $payload['serviceID'] ?? $payload['exam'] ?? 'waec',
                (int) ($payload['quantity'] ?? 1),
                $txId
            ),
            'vtu_bulksms' => $adapter->sendBulkSms(
                (string) ($payload['sender'] ?? 'FUWA'),
                $payload['recipients'] ?? $payload['number'] ?? $payload['phone'] ?? '',
                (string) ($payload['message'] ?? ''),
                $txId
            ),
            'vtu_data_card' => $adapter->purchaseDataCard(
                $payload['network'] ?? 1,
                (int) ($payload['plan_type'] ?? 1),
                (int) ($payload['quantity'] ?? 1),
                (string) ($payload['card_name'] ?? 'Fuwa.NG'),
                $txId
            ),
            'vtu_recharge_card' => $adapter->purchaseRechargeCard(
                $payload['network'] ?? 1,
                (int) ($payload['plan_type'] ?? 1),
                (int) ($payload['quantity'] ?? 1),
                (string) ($payload['card_name'] ?? 'Fuwa.NG'),
                $txId
            ),
            default => [
                'status' => false,
                'message' => 'Unsupported service type for Datasub247: ' . $serviceType,
            ],
        };
    }
}

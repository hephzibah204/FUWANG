<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Services\PhotoComplianceFilter;
use App\Services\VuvaaStatusMapper;
use Illuminate\Http\Request;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use App\Services\DataVerify\DataVerifyClient;
use App\Services\PaidActionService;
use App\Services\Vuvaa\VuvaaClient;
use App\Services\VerificationResultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NINController extends Controller
{
    public function suiteIndex()
    {
        return view('services.identity.nin_suite');
    }

    public function index(Request $request)
    {
        $legacyPricing = VerificationPrice::first();

        $ninProviders = CustomApi::whereIn('service_type', ['nin_verification', 'nin_face_verification'])
                            ->where('status', true)
                            ->orderBy('priority', 'asc')
                            ->get();

        $providerModes = $ninProviders->mapWithKeys(function ($provider) {
            $modes = is_array($provider->supported_modes) ? $provider->supported_modes : [];
            if (empty($modes)) {
                $modes = $provider->service_type === 'nin_face_verification'
                    ? ['selfie']
                    : ['nin', 'phone', 'demographic', 'tracking', 'vnin'];
            }
            if (VuvaaClient::isVuvaaProvider($provider) && $provider->service_type === 'nin_verification') {
                $modes = array_values(array_unique(array_merge($modes, ['share_code', 'requery'])));
            }
            if ((string) $provider->provider_identifier === 'robosttech') {
                $modes = array_values(array_unique(array_merge($modes, ['validation', 'validation_status'])));
            }
            return [$provider->id => $modes];
        });

        $prices = [
            'nin' => (float) ($legacyPricing->nin_by_nin_price ?? 200),
            'phone' => (float) ($legacyPricing->nin_by_number_price ?? 200),
            'tracking' => (float) ($legacyPricing->verify_by_tracking_id ?? 300),
            'validation' => (float) (\App\Models\SystemSetting::get('nin_validation_price', 200)),
            'validation_status' => 0.0,
            'selfie' => (float) (\App\Models\SystemSetting::get('nin_face_verification_price', 500)),
            'share_code' => (float) ($legacyPricing->nin_by_nin_price ?? 200),
            'requery' => 0.0,
        ];

        $myResults = VerificationResult::where('user_id', Auth::id())
                        ->whereIn('service_type', ['nin_verification', 'nin_face_verification', 'nin_validation'])
                        ->latest()
                        ->get();

        $apiCenter = ApiCenter::first();

        $allowedModes = ['nin', 'selfie', 'phone', 'tracking', 'validation', 'validation_status', 'demographic', 'share_code', 'requery'];
        $initialMode = (string) $request->query('mode', 'nin');
        if (!in_array($initialMode, $allowedModes, true)) {
            $initialMode = 'nin';
        }

        return view('services.identity.nin', compact('prices', 'ninProviders', 'providerModes', 'myResults', 'apiCenter', 'initialMode'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'mode' => ['required', 'in:nin,phone,tracking,demographic,selfie,share_code,requery,validation,validation_status'],
            'number' => ['required_if:mode,nin,phone,tracking,selfie,validation,validation_status', 'nullable', 'string'],
            'share_code' => ['required_if:mode,share_code', 'nullable', 'string', 'max:64'],
            'share_reason' => ['required_if:mode,share_code', 'nullable', 'string', 'max:120'],
            'share_reason_other' => ['nullable', 'string', 'max:120'],
            'firstname' => ['required_if:mode,demographic', 'nullable', 'string'],
            'lastname' => ['required_if:mode,demographic', 'nullable', 'string'],
            'dob' => ['required_if:mode,demographic', 'nullable', 'string'],
            'gender' => ['required_if:mode,demographic', 'nullable', 'string'],
            'selfie' => ['required_if:mode,selfie', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
            'validation_reason' => ['required_if:mode,validation', 'nullable', 'string', 'max:120'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
            'output_type' => ['nullable', 'string', 'in:info_page,standard_slip,regular_slip,premium_slip,vnin_slip'],
        ]);

        $user = Auth::user();
        $mode = $request->input('mode', 'nin');

        // Do not treat `_nin_verify_json` alone as "wants JSON": a normal form POST includes
        // that hidden field and would otherwise render the JSON body as a full HTML page.
        $xhr = strtolower((string) $request->header('X-Requested-With', '')) === 'xmlhttprequest';
        $wantsJsonResponse = $request->expectsJson()
            || $request->ajax()
            || $request->wantsJson()
            || ($request->boolean('_nin_verify_json') && $xhr);

        try {
            $provider = $this->pickProviderForMode($mode, (int) $request->input('api_provider_id'));
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($provider && VuvaaClient::isVuvaaProvider($provider)) {
            try {
                $client = new VuvaaClient($provider);
                $walletResp = $client->getWalletDetails();
                
                if (!$walletResp['ok']) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to check wallet balance. ' . $walletResp['message'],
                    ], 402);
                }
                
                $unitsAvailable = (int) (
                    $walletResp['data']['wallet_units']
                    ?? $walletResp['data']['data'][0]['validation_units']
                    ?? $walletResp['data']['data']['validation_units']
                    ?? 0
                );
                if ($unitsAvailable < 1) {  // ← Adjust threshold as needed
                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient units — please top up wallet',
                        'units_available' => $unitsAvailable,
                    ], 402);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wallet check failed: ' . $e->getMessage(),
                ], 500);
            }
        }

        if ($mode === 'selfie' && (!$provider || $provider->service_type !== 'nin_face_verification')) {
            return response()->json(['status' => false, 'message' => 'A provider supporting selfie verification is required.'], 422);
        }

        if (!$provider && $mode !== 'nin' && $mode !== 'phone' && $mode !== 'tracking') {
             return response()->json(['status' => false, 'message' => 'No active verification provider configured for this mode. Contact admin.'], 503);
        }

        $price = $this->determinePrice($mode, $provider, $request->input('verification_type'));
        if ($mode === 'validation_status') {
            $price = 0.0;
        }

        $orderType = 'NIN Verification';
        if ($mode === 'validation') {
            $orderType = 'NIN Validation';
        } elseif ($mode === 'validation_status') {
            $orderType = 'NIN Validation Status';
        } elseif ($mode !== 'nin') {
            $orderType = 'NIN ' . ucfirst($mode) . ' Verification';
        }

        $paid = app(PaidActionService::class)->run($user, $price, $orderType, 'NIN', function () use ($mode, $provider, $request, $user) {
            $selfieMeta = null;
            if ($mode === 'selfie') {
                $selfieMeta = $this->processSelfie($request, $user->id);
            }

            // --- SMART ROUTING LOGIC ---
            $activeProviders = [];
            if ($provider) {
                // If user specifically picked a provider, try it first
                $activeProviders = [$provider];
            } else {
                // Otherwise, get all active providers for this mode
                $serviceType = $mode === 'selfie' ? 'nin_face_verification' : 'nin_verification';
                $query = CustomApi::where('service_type', $serviceType)
                    ->where('status', true)
                    ->orderBy('priority', 'asc');

                if (in_array($mode, ['validation', 'validation_status'], true)) {
                    $query->where('provider_identifier', 'robosttech');
                }

                $activeProviders = $query->get();
            }

            $response = ['status' => false, 'message' => 'No active verification provider is configured.'];
            $errors = [];

            // Try each custom provider in order
            foreach ($activeProviders as $p) {
                try {
                    $response = $this->callCustomProvider($p, $request, $mode, $selfieMeta);
                    if ($response['status']) {
                        break; // Success!
                    }
                    $errors[] = $p->name . ': ' . ($response['message'] ?? 'Unknown error');
                } catch (\Throwable $e) {
                    $errors[] = $p->name . ': ' . $e->getMessage();
                    Log::warning("Provider failover: {$p->name} failed.", ['error' => $e->getMessage()]);
                }
            }

            // Fallback to Legacy API if all custom providers failed and no specific provider was requested
            if (!$response['status'] && !$request->filled('api_provider_id') && !in_array($mode, ['validation', 'validation_status'], true)) {
                $apiCenter = ApiCenter::first();
                if ($apiCenter) {
                    try {
                        $response = $this->callLegacyApi($apiCenter, $request, $mode);
                        if (!$response['status']) {
                            $errors[] = 'Legacy API: ' . ($response['message'] ?? 'Unknown error');
                        }
                    } catch (\Throwable $e) {
                        $errors[] = 'Legacy API: ' . $e->getMessage();
                    }
                }
            }

            if ($response['status']) {
                $data = $response['data'];
                $photoBase64 = null;
                if ($mode === 'selfie') {
                    $photoBase64 = $data['photo'] ?? null;
                    $data = PhotoComplianceFilter::sanitize($data);
                }

                if (is_array($data)) {
                    $data['_verification_mode'] = $mode;
                    $data['_requested_output_type'] = (string) $request->input('output_type', 'info_page');
                    if ($mode === 'validation') {
                        $data['_validation_reason'] = (string) $request->input('validation_reason', '');
                    }
                }

                $result = app(VerificationResultService::class)->create(
                    $user,
                    $mode === 'selfie'
                        ? 'nin_face_verification'
                        : ($mode === 'validation' || $mode === 'validation_status' ? 'nin_validation' : 'nin_verification'),
                    (string) ($request->number ?? $request->share_code ?? $request->reference_id),
                    (string) ($response['provider'] ?? 'Unknown Provider'),
                    $data,
                    ($mode === 'validation' || $mode === 'validation_status') ? 'pending' : 'success',
                    'NIN'
                );

                $outputType = (string) $request->input('output_type', 'info_page');
                $slipTypes = ['standard_slip', 'regular_slip', 'premium_slip', 'vnin_slip'];
                $wantsSlip = in_array($outputType, $slipTypes, true);

                $payload = [
                    'status' => true,
                    'message' => ($mode === 'validation' || $mode === 'validation_status')
                        ? ($response['message'] ?? 'Validation submitted.')
                        : 'Verification Successful',
                    'output_type' => $outputType,
                    'data' => $data,
                    'photo' => $photoBase64,
                    'result_id' => $result->id,
                    'reference_id' => $result->reference_id,
                ];

                if ($wantsSlip && !in_array($mode, ['validation', 'validation_status'], true)) {
                    $payload['slip_url'] = route('services.nin.slip', ['id' => $result->id, 'type' => $outputType]);
                    // Full record stays in DB; omit huge base64 from JSON so responses stay parseable.
                    $payload['data'] = $this->stripLargeMediaFromNinPayload($data);
                    $payload['photo'] = null;
                }

                if ($mode === 'validation') {
                    $payload['ui_state'] = 'validation_submitted';
                    $payload['status_url'] = route('services.nin.validation.status');
                }

                return $payload;
            }

            $finalMessage = !empty($errors) ? implode(' | ', $errors) : ($response['message'] ?? 'Verification failed.');
            throw new \Exception($finalMessage);
        });

        if (!$paid['ok']) {
            if ($wantsJsonResponse) {
                return response()->json(['status' => false, 'message' => $paid['message']]);
            }

            return back()->withErrors(['nin' => $paid['message']])->withInput();
        }

        $result = $paid['result'];
        $outputType = (string) $request->input('output_type', 'info_page');
        $slipTypes = ['standard_slip', 'regular_slip', 'premium_slip', 'vnin_slip'];

        if (!$wantsJsonResponse) {
            if (in_array($outputType, $slipTypes, true) && !empty($result['result_id'])) {
                return redirect()
                    ->route('services.nin')
                    ->with('status', $result['message'] ?? 'Verification successful.')
                    ->with('nin_slip_download_url', route('services.nin.slip', ['id' => $result['result_id'], 'type' => $outputType]))
                    ->with('nin_result', $result['data'] ?? null)
                    ->with('nin_result_id', $result['result_id'] ?? null)
                    ->with('nin_reference_id', $result['reference_id'] ?? null);
            }

            return redirect()
                ->route('services.nin')
                ->with('status', $result['message'] ?? 'Verification Successful')
                ->with('nin_result', $result['data'] ?? null)
                ->with('nin_result_id', $result['result_id'] ?? null)
                ->with('nin_reference_id', $result['reference_id'] ?? null);
        }

        return response()->json($result);
    }

    public function requery(Request $request)
    {
        $validated = $request->validate([
            'reference_id' => 'required|string',
        ]);
        
        $provider = $this->pickProviderForMode('nin');
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No provider configured'], 400);
        }
        
        $client = new VuvaaClient($provider);
        $response = $client->requery($validated['reference_id']);
        
        $statusCode = (string) ($response['data']['statusCode'] ?? $response['data']['status'] ?? '99');
        $mapped = VuvaaStatusMapper::map($statusCode);
        
        return response()->json([
            'status' => $mapped['status'] === 'success',
            'message' => $mapped['message'],
            'ui_state' => $mapped['uiState'],
            'data' => $response['data'] ?? []
        ]);
    }

    private function pickProviderForMode(string $mode, ?int $providerId = null): ?CustomApi
    {
        $serviceType = $mode === 'selfie' ? 'nin_face_verification' : 'nin_verification';
        
        if ($providerId) {
            $provider = CustomApi::find($providerId);
            
            if ($provider && $provider->service_type !== $serviceType) {
                throw new \RuntimeException(
                    "Provider {$providerId} does not support service type '{$serviceType}'. " .
                    "It is configured for '{$provider->service_type}'."
                );
            }
            
            return $provider;
        }

        if (in_array($mode, ['validation', 'validation_status'], true)) {
            return CustomApi::where('service_type', $serviceType)
                ->where('status', true)
                ->where('provider_identifier', 'robosttech')
                ->orderBy('priority', 'asc')
                ->first();
        }

        return CustomApi::where('service_type', $serviceType)
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->first();
    }

    private function callCustomProvider(CustomApi $provider, Request $request, string $mode, ?array $selfieMeta): array
    {
        if (VuvaaClient::isVuvaaProvider($provider)) {
            $client = new VuvaaClient($provider);
            if ($mode === 'selfie') {
                $result = $client->verifyInPerson((string) $request->number, base64_encode(Storage::disk('local')->get($selfieMeta['path'])));
            } elseif ($mode === 'share_code') {
                $shareCode = (string) ($request->input('share_code') ?: $request->input('number'));
                $reason = trim((string) $request->input('share_reason'));
                if ($reason === 'other') {
                    $reason = trim((string) $request->input('share_reason_other'));
                }
                $result = $client->verifyShareCode($shareCode, null, $reason !== '' ? $reason : null);
            } elseif ($mode === 'requery') {
                $referenceId = (string) ($request->input('reference_id') ?: $request->input('number'));
                $result = $client->requery($referenceId);
            } else {
                $value = (string) $request->input('number');
                if ($mode === 'nin') {
                    $result = $client->verifyNin($value);
                } else {
                    return ['status' => false, 'message' => 'Selected mode is not supported by VUVAA.'];
                }
            }
            return ['status' => $result['ok'], 'message' => $result['message'], 'data' => $result['data'], 'provider' => $provider->name];
        }
        if (strtolower((string) $provider->provider_identifier) === 'robosttech') {
            return $this->callRobostTechProvider($provider, $request, $mode);
        }
        if (DataVerifyClient::isDataVerifyProvider($provider)) {
            $client = new DataVerifyClient($provider);
            $result = $client->verify($mode, [
                'number' => (string) $request->input('number'),
                'firstname' => (string) $request->input('firstname'),
                'lastname' => (string) $request->input('lastname'),
                'dob' => (string) $request->input('dob'),
                'gender' => (string) $request->input('gender'),
            ], (string) $request->input('verification_type', ''));

            return [
                'status' => $result['ok'],
                'message' => $result['message'],
                'data' => $result['data'],
                'provider' => $provider->name,
            ];
        }
        // ... other provider logic can go here ...
        return ['status' => false, 'message' => 'Provider not supported'];
    }

    private function callRobostTechProvider(CustomApi $provider, Request $request, string $mode): array
    {
        $modePath = [
            'nin' => 'nin_verify',
            'phone' => 'nin_phone',
            'demographic' => 'nin_demo',
            'tracking' => 'validation_status',
            'validation' => 'validation',
            'validation_status' => 'validation_status',
            'clearance' => 'clearance',
            'clearance_status' => 'clearance_status',
        ];
        $path = $modePath[$mode] ?? 'nin_verify';
        $url = $this->resolveRobostEndpoint((string) $provider->endpoint, $path);

        $headers = is_array($provider->headers) ? $provider->headers : [];
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';
        if (!empty($provider->api_key) && empty($headers['api-key'])) {
            $headers['api-key'] = (string) $provider->api_key;
        }

        $payload = [];
        if (in_array($mode, ['nin', 'tracking', 'validation', 'validation_status'], true)) {
            $payload = ['nin' => trim((string) $request->input('number'))];
            if ($mode === 'validation') {
                $reason = trim((string) $request->input('validation_reason'));
                if ($reason !== '') {
                    $payload['reason'] = $reason;
                    $payload['validation_reason'] = $reason;
                }
            }
        } elseif ($mode === 'phone') {
            $payload = ['phone' => trim((string) $request->input('number'))];
        } elseif ($mode === 'demographic') {
            $payload = [
                'firstname' => strtoupper(trim((string) $request->input('firstname'))),
                'lastname' => strtoupper(trim((string) $request->input('lastname'))),
                'middlename' => strtoupper(trim((string) $request->input('middlename', ''))),
                'gender' => strtolower(trim((string) $request->input('gender'))),
                'dateOfBirth' => trim((string) $request->input('dob')),
            ];
        } elseif ($mode === 'clearance' || $mode === 'clearance_status') {
            $payload = ['tracking_id' => trim((string) ($request->input('tracking_id') ?: $request->input('number')))];
        }

        $res = Http::timeout((int) ($provider->timeout_seconds ?: 60))
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->post($url, $payload);
        $json = $res->json();

        if (! $res->successful()) {
            Log::warning('RobostTech NIN call failed', [
                'status' => $res->status(),
                'mode' => $mode,
                'url' => $url,
                'body' => $res->body(),
            ]);
            $msg = null;
            if (is_array($json)) {
                $m = $json['message'] ?? $json['detail'] ?? null;
                if (is_array($m)) {
                    $msg = $m['balance'] ?? (is_string(reset($m)) ? reset($m) : null);
                    if ($msg === null) {
                        $msg = json_encode($m);
                    }
                } elseif (is_string($m)) {
                    $msg = $m;
                }
            }
            return [
                'status' => false,
                'message' => $msg ?: 'RobostTech verification failed.',
            ];
        }

        if (! is_array($json)) {
            return ['status' => false, 'message' => 'RobostTech returned invalid response.'];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $looksSuccessful = $statusVal === 'success' || $statusVal === 'true' || $statusVal === 'ok';
        if (! $looksSuccessful && isset($json['status']) && $json['status'] !== true) {
            return [
                'status' => false,
                'message' => is_string($json['message'] ?? null) ? (string) $json['message'] : 'RobostTech verification was not successful.',
            ];
        }

        return [
            'status' => true,
            'message' => (string) ($json['message'] ?? 'Verification successful'),
            'data' => $json['data'] ?? $json,
            'provider' => $provider->name,
        ];
    }

    public function validationStatus(Request $request)
    {
        $validated = $request->validate([
            'nin' => ['required', 'string', 'max:32'],
            'result_id' => ['nullable', 'integer'],
        ]);

        $user = Auth::user();
        $provider = $this->pickProviderForMode('validation_status');
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No RobostTech provider configured for validation status.'], 503);
        }

        $req = new Request(['number' => $validated['nin']]);
        $response = $this->callRobostTechProvider($provider, $req, 'validation_status');

        if (!$response['status']) {
            return response()->json(['status' => false, 'message' => $response['message'] ?? 'Status check failed.'], 502);
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $success = (bool) ($data['success'] ?? false);
        $inProgress = (bool) ($data['in-progress'] ?? $data['in_progress'] ?? false);
        $uiState = $success ? 'completed' : ($inProgress ? 'in_progress' : 'pending');

        if (!empty($validated['result_id'])) {
            $result = VerificationResult::where('id', (int) $validated['result_id'])
                ->where('user_id', $user->id)
                ->first();
            if ($result) {
                $payload = is_array($result->response_data) ? $result->response_data : [];
                $payload['validation_status'] = $data;
                $result->response_data = $payload;
                $result->status = $success ? 'success' : ($inProgress ? 'pending' : 'failed');
                $result->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => $response['message'] ?? 'Status fetched.',
            'ui_state' => $uiState,
            'data' => $data,
        ]);
    }

    private function resolveRobostEndpoint(string $configured, string $path): string
    {
        $configured = trim($configured);
        if ($configured === '') {
            return 'https://robosttech.com/api/' . $path;
        }

        $trimmed = rtrim($configured, '/');
        foreach (['nin_verify', 'nin_phone', 'nin_demo', 'validation', 'validation_status'] as $knownPath) {
            if (str_ends_with(strtolower($trimmed), '/' . $knownPath) || strtolower($trimmed) === 'https://robosttech.com/api/' . $knownPath) {
                return preg_replace('#/' . preg_quote($knownPath, '#') . '$#i', '/' . $path, $trimmed) ?? ('https://robosttech.com/api/' . $path);
            }
        }

        return $trimmed . '/' . $path;
    }


    // Legacy Dataverify NIN: JSON body + Content-Type: application/json (per provider docs).
    private function callLegacyApi(ApiCenter $apiCenter, Request $request, string $mode): array
    {
        $endpoint = $apiCenter->dataverify_endpoint_nin;
        if (! $endpoint || ! $apiCenter->dataverify_api_key) {
            return ['status' => false, 'message' => 'Legacy NIN endpoint or API key not configured.'];
        }

        $response = Http::timeout(45)->asJson()->post($endpoint, [
            'api_key' => $apiCenter->dataverify_api_key,
            'nin' => $request->number,
        ]);

        if (! $response->successful()) {
            return [
                'status' => false,
                'message' => 'Legacy NIN API returned an error: '.$response->status(),
            ];
        }

        $json = $response->json();
        if (! is_array($json)) {
            return ['status' => false, 'message' => 'Legacy NIN API returned an invalid response.'];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $responseCode = (string) ($json['response_code'] ?? '');
        $looksSuccessful = $statusVal === 'success' || $statusVal === 'true' || $responseCode === '00' || $responseCode === '0' || ($json['status'] ?? null) === true;

        if (! $looksSuccessful) {
            return ['status' => false, 'message' => $json['message'] ?? 'Legacy provider error'];
        }

        $data = null;
        if (isset($json['data']) && is_array($json['data'])) {
            $data = $json['data'];
        } elseif (isset($json['user_data']) && is_array($json['user_data'])) {
            $data = $json['user_data'];
        }

        return ['status' => true, 'data' => $data ?? $json, 'provider' => 'Legacy API'];
    }

    private function determinePrice(string $mode, ?CustomApi $provider, ?string $typeKey): float
    {
        if ($provider && $typeKey) {
            $type = $provider->verificationTypes()->where('type_key', $typeKey)->first();
            if ($type) return (float) $type->price;
        }
        if ($provider && $provider->price) {
            return (float) $provider->price;
        }
        $legacyPricing = VerificationPrice::first();
        return (float) ($legacyPricing->{'nin_by_' . $mode . '_price'} ?? 200);
    }

    /**
     * Remove oversized base64 image fields for API responses (vault still has full payload).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function stripLargeMediaFromNinPayload(array $data): array
    {
        $copy = $data;
        foreach (['photo', 'image', 'photoId'] as $key) {
            if (!isset($copy[$key]) || !is_string($copy[$key])) {
                continue;
            }
            if (strlen($copy[$key]) > 500) {
                $copy[$key] = null;
            }
        }

        return $copy;
    }

    private function processSelfie(Request $request, int $userId): array
    {
        $file = $request->file('selfie');
        if (!$file) throw new \Exception('Selfie image is required.');

        $path = $file->store('private/selfies/' . $userId, 'local');

        return [
            'disk' => 'local',
            'path' => $path,
            'size' => $file->getSize(),
            'sha256' => hash_file('sha256', Storage::disk('local')->path($path)),
        ];
    }
}

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
    private const VUVAA_MODES = ['nin', 'selfie', 'share_code', 'requery'];

    public function suiteIndex()
    {
        return view('services.identity.nin_suite');
    }

    public function index(Request $request)
    {
        $legacyPricing = VerificationPrice::first();

        $ninProviders = CustomApi::whereIn('service_type', ['nin', 'nin_verification', 'nin_face_verification', 'nin_validation', 'validation', 'identity'])
                            ->where('status', true)
                            ->orderBy('priority', 'asc')
                            ->get();

        try {
            $this->ensureDefaultNinProviders();
            CustomApi::where('provider_identifier', '!=', 'vuvaa')->where('priority', '<=', 1)->update(['priority' => 10]);
            CustomApi::where('provider_identifier', 'vuvaa')->update(['priority' => 1, 'status' => true]);

            $ninProviders = CustomApi::whereIn('service_type', ['nin', 'nin_verification', 'nin_face_verification', 'nin_validation', 'validation', 'identity'])
                                ->where('status', true)
                                ->orderBy('priority', 'asc')
                                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NINController: failed initializing providers: ' . $e->getMessage());
        }

        $providerModes = $ninProviders->mapWithKeys(function ($provider) {
            $modes = is_array($provider->supported_modes) ? $provider->supported_modes : [];
            if (VuvaaClient::isVuvaaProvider($provider)) {
                $modes = self::VUVAA_MODES;
            } elseif (empty($modes)) {
                $modes = $provider->service_type === 'nin_face_verification'
                    ? ['selfie']
                    : ['nin', 'phone', 'demographic', 'tracking', 'vnin'];
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

        // Sanitize input number: strip non-digits for nin, phone, and selfie modes
        if ($request->filled('number')) {
            if (in_array($mode, ['nin', 'phone', 'selfie'], true)) {
                $cleanedNumber = preg_replace('/\D/', '', (string) $request->input('number'));
                $request->merge(['number' => $cleanedNumber]);
            } else {
                $request->merge(['number' => trim((string) $request->input('number'))]);
            }
        }

        try {
            $this->ensureDefaultNinProviders();
        } catch (\Throwable $e) {
            Log::warning('NINController verify: failed ensuring providers: ' . $e->getMessage());
        }

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
            if ($wantsJsonResponse) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 200);
            }
            return back()->withErrors(['nin' => $e->getMessage()])->withInput();
        }

        if ($provider && VuvaaClient::isVuvaaProvider($provider)) {
            try {
                $client = new VuvaaClient($provider);
                $walletResp = $client->getWalletDetails();
                
                $unitsAvailable = (int) (
                    $walletResp['data']['wallet_units']
                    ?? $walletResp['data']['data'][0]['validation_units']
                    ?? $walletResp['data']['data']['validation_units']
                    ?? 0
                );

                if (!$walletResp['ok'] || $unitsAvailable < 1) {
                    $walletMsg = !$walletResp['ok']
                        ? ('Wallet check failed: ' . ($walletResp['message'] ?? 'Unknown error'))
                        : 'Insufficient units — please top up wallet';

                    if ($request->filled('api_provider_id')) {
                        if ($wantsJsonResponse) {
                            return response()->json([
                                'status' => false,
                                'message' => $walletMsg,
                                'units_available' => $unitsAvailable,
                            ], 402);
                        }
                        return back()->withErrors(['nin' => $walletMsg])->withInput();
                    }

                    // Auto-select mode: bypass Vuvaa and failover to next provider
                    Log::warning("Vuvaa bypassed due to wallet status: {$walletMsg}");
                    $provider = null;
                }
            } catch (\Throwable $e) {
                Log::warning('Vuvaa wallet check failed: ' . $e->getMessage());
                if ($request->filled('api_provider_id')) {
                    $msg = $this->isTechnicalError($e->getMessage())
                        ? 'Verification provider temporarily unavailable. Please try again shortly.'
                        : ('Wallet check failed: ' . $e->getMessage());
                    if ($wantsJsonResponse) {
                        return response()->json([
                            'status' => false,
                            'message' => $msg,
                        ], 503);
                    }
                    return back()->withErrors(['nin' => $msg])->withInput();
                }
                // Auto-select mode: proceed with failover
                $provider = null;
            }
        }

        if ($mode === 'selfie' && (!$provider || !$this->providerSupportsMode($provider, 'selfie'))) {
            $msg = 'A provider supporting selfie verification is required.';
            if ($wantsJsonResponse) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['nin' => $msg])->withInput();
        }

        if ($provider && !$this->providerSupportsMode($provider, $mode)) {
            $msg = "Selected provider ({$provider->name}) does not support the '{$mode}' verification mode.";
            if ($wantsJsonResponse) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['nin' => $msg])->withInput();
        }

        if (!$provider && $mode !== 'nin' && $mode !== 'phone' && $mode !== 'tracking') {
             $msg = 'No active verification provider configured for this mode. Contact admin.';
             if ($wantsJsonResponse) {
                 return response()->json(['status' => false, 'message' => $msg], 503);
             }
             return back()->withErrors(['nin' => $msg])->withInput();
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
            $query = CustomApi::whereIn('service_type', ['nin', 'nin_verification', 'nin_face_verification', 'nin_validation', 'validation', 'identity'])
                ->where('status', true)
                ->orderBy('priority', 'asc');

            if (in_array($mode, ['validation', 'validation_status'], true)) {
                $query->where('provider_identifier', 'robosttech');
            }

            $allActiveProviders = $query->get()
                ->filter(fn (CustomApi $candidate) => $this->providerSupportsMode($candidate, $mode))
                ->values();

            if ($provider) {
                // If user specifically picked a provider, try it first, followed by others as failover
                $activeProviders = collect([$provider])
                    ->concat($allActiveProviders->reject(fn (CustomApi $c) => $c->id === $provider->id))
                    ->values();
            } else {
                $activeProviders = $allActiveProviders;
            }

            $response = ['status' => false, 'message' => 'No active verification provider is configured.'];
            $errors = [];

            // Try each custom provider in order
            foreach ($activeProviders as $p) {
                try {
                    $res = $this->callCustomProvider($p, $request, $mode, $selfieMeta);
                    if (!empty($res['status'])) {
                        $response = $res;
                        break; // Success!
                    }
                    $errMsg = $res['message'] ?? 'Verification failed';
                    $errors[] = $p->name . ': ' . $errMsg;
                    Log::info("Provider {$p->name} unsuccessful for NIN mode {$mode}: {$errMsg}");
                } catch (\Throwable $e) {
                    $errors[] = $p->name . ': ' . $e->getMessage();
                    Log::warning("Provider failover: {$p->name} failed.", ['error' => $e->getMessage()]);
                }
            }

            // Fallback to Legacy API if all custom providers failed and mode is supported by Legacy API
            if (!$response['status'] && !in_array($mode, ['validation', 'validation_status', 'selfie', 'share_code', 'requery'], true)) {
                $apiCenter = ApiCenter::first();
                if ($apiCenter && ($apiCenter->dataverify_api_key || $apiCenter->dataverify_endpoint_nin || env('DATAVERIFY_API_KEY') || \App\Models\SystemSetting::get('dataverify_api_key'))) {
                    try {
                        $legacyRes = $this->callLegacyApi($apiCenter, $request, $mode);
                        if (!empty($legacyRes['status'])) {
                            $response = $legacyRes;
                        } else {
                            $errors[] = 'Legacy API: ' . ($legacyRes['message'] ?? 'Unknown error');
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

            if (!empty($errors)) {
                Log::warning('NIN verification providers failed', [
                    'user_id' => $user->id,
                    'mode' => $mode,
                    'errors' => $errors,
                ]);
            }

            $finalMessage = $this->formatVerificationError($errors, (string) ($response['message'] ?? 'Verification failed.'));
            if ($request->filled('api_provider_id') && count($errors) === 1 && str_contains($errors[0], ': ')) {
                $candidate = explode(': ', $errors[0], 2)[1];
                if (!$this->isTechnicalError($candidate)) {
                    $finalMessage = $candidate;
                }
            }
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
        if ($providerId) {
            $provider = CustomApi::find($providerId);

            if ($provider && (
                !$provider->status
                || !in_array($provider->service_type, ['nin', 'nin_verification', 'nin_face_verification', 'nin_validation', 'validation', 'identity'], true)
                || !$this->providerSupportsMode($provider, $mode)
            )) {
                throw new \RuntimeException(
                    "Provider {$providerId} does not support the '{$mode}' verification mode."
                );
            }
            
            return $provider;
        }

        if (in_array($mode, ['validation', 'validation_status'], true)) {
            return CustomApi::whereIn('service_type', ['nin_verification', 'nin', 'nin_validation', 'validation', 'identity'])
                ->where('status', true)
                ->where('provider_identifier', 'robosttech')
                ->orderBy('priority', 'asc')
                ->first()
                ?? CustomApi::whereIn('service_type', ['nin_verification', 'nin', 'nin_validation', 'validation', 'identity'])
                ->where('status', true)
                ->orderBy('priority', 'asc')
                ->first();
        }

        return CustomApi::whereIn('service_type', ['nin', 'nin_verification', 'nin_face_verification', 'nin_validation', 'validation', 'identity'])
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->get()
            ->first(fn (CustomApi $candidate) => $this->providerSupportsMode($candidate, $mode));
    }

    private function providerSupportsMode(CustomApi $provider, string $mode): bool
    {
        if (VuvaaClient::isVuvaaProvider($provider)) {
            return in_array($mode, self::VUVAA_MODES, true);
        }

        $modes = is_array($provider->supported_modes) ? $provider->supported_modes : [];
        if (!empty($modes)) {
            return in_array($mode, $modes, true);
        }

        if ((string) $provider->provider_identifier === 'robosttech') {
            return in_array($mode, ['nin', 'phone', 'demographic', 'tracking', 'vnin', 'validation', 'validation_status', 'clearance'], true);
        }

        return $provider->service_type === 'nin_face_verification'
            ? $mode === 'selfie'
            : in_array($mode, ['nin', 'phone', 'demographic', 'tracking', 'vnin', 'validation', 'validation_status'], true);
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
            $vuvaaData = $result['data'];
            if (is_array($vuvaaData) && isset($vuvaaData['data']) && is_array($vuvaaData['data'])) {
                $vuvaaData = array_merge($vuvaaData, $vuvaaData['data']);
            }
            if (is_array($vuvaaData)) {
                // Normalize field keys so they align with the frontend and slip generator
                $vuvaaData['firstname'] = $vuvaaData['firstname'] ?? $vuvaaData['firstName'] ?? null;
                $vuvaaData['surname'] = $vuvaaData['surname'] ?? $vuvaaData['lastName'] ?? null;
                $vuvaaData['middlename'] = $vuvaaData['middlename'] ?? $vuvaaData['middleName'] ?? null;
                $vuvaaData['telephoneno'] = $vuvaaData['telephoneno'] ?? $vuvaaData['phone1'] ?? $vuvaaData['phone'] ?? null;
                $vuvaaData['birthdate'] = $vuvaaData['birthdate'] ?? $vuvaaData['dateOfBirth'] ?? null;
                $vuvaaData['residence_address'] = $vuvaaData['residence_address'] ?? $vuvaaData['residence_AdressLine1'] ?? $vuvaaData['residenceAddressLine1'] ?? null;
                $vuvaaData['residence_state'] = $vuvaaData['residence_state'] ?? $vuvaaData['residenceTown'] ?? null;
                $vuvaaData['gender'] = !empty($vuvaaData['gender']) ? strtoupper((string) $vuvaaData['gender']) : null;
                if (empty($vuvaaData['nin']) && in_array($mode, ['nin', 'selfie'], true) && $request->filled('number')) {
                    $vuvaaData['nin'] = (string) $request->input('number');
                }
            }
            $isTerminal = $this->isTerminalIdentityError((string) ($result['message'] ?? ''));
            return [
                'status' => $result['ok'],
                'message' => $result['message'],
                'data' => $vuvaaData,
                'provider' => $provider->name,
                'terminal' => $isTerminal,
            ];
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
                'terminal' => ($result['terminal'] ?? false) || $this->isTerminalIdentityError((string) ($result['message'] ?? '')),
            ];
        }
        if (!empty($provider->endpoint)) {
            try {
                $headers = is_array($provider->headers) ? $provider->headers : [];
                $response = Http::timeout((int)($provider->timeout_seconds ?: 30))
                    ->withHeaders($headers)
                    ->post($provider->endpoint, $request->all());

                $resData = $response->json();
                $msg = $resData['message'] ?? $resData['error'] ?? 'Verification Failed';
                if (!str_starts_with($msg, 'Verification Failed:')) {
                    $msg = 'Verification Failed: ' . $msg;
                }

                return [
                    'status' => $response->successful() && (($resData['status'] ?? false) === true || ($resData['status'] ?? '') === 'success'),
                    'message' => $msg,
                    'data' => $resData['data'] ?? null,
                    'provider' => $provider->name,
                ];
            } catch (\Throwable $e) {
                return ['status' => false, 'message' => 'Verification Failed: ' . $e->getMessage()];
            }
        }

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
            $finalMsg = $msg ?: 'RobostTech verification failed.';
            return [
                'status' => false,
                'message' => $finalMsg,
                'terminal' => $this->isTerminalIdentityError($finalMsg),
            ];
        }

        if (! is_array($json)) {
            return ['status' => false, 'message' => 'RobostTech returned invalid response.'];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $looksSuccessful = $statusVal === 'success' || $statusVal === 'true' || $statusVal === 'ok';
        if (! $looksSuccessful && isset($json['status']) && $json['status'] !== true) {
            $failureMsg = is_string($json['message'] ?? null) ? (string) $json['message'] : 'RobostTech verification was not successful.';
            return [
                'status' => false,
                'message' => $failureMsg,
                'terminal' => $this->isTerminalIdentityError($failureMsg),
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
        $endpoint = DataVerifyClient::normalizeDomain((string) $apiCenter->dataverify_endpoint_nin);
        if (! $endpoint) {
            $endpoint = 'https://dataverify.org/developers/nin_api/';
        }
        $apiKey = $apiCenter->dataverify_api_key ?: env('DATAVERIFY_API_KEY') ?: \App\Models\SystemSetting::get('dataverify_api_key');
        if (! $apiKey) {
            return ['status' => false, 'message' => 'Legacy NIN endpoint or API key not configured.'];
        }

        try {
            $response = Http::timeout(45)->asJson()->post($endpoint, [
                'api_key' => $apiKey,
                'nin' => $request->number,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Legacy NIN API call failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => 'Legacy verification provider unavailable.',
            ];
        }

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

    private function ensureDefaultNinProviders(): void
    {
        try {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
                \Illuminate\Support\Facades\DB::statement(
                    'ALTER TABLE `custom_apis` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'
                );
            }
        } catch (\Throwable $e) {
            // Silently continue if permissions restrict ALTER TABLE or if it's already configured
        }

        $defaults = [
            [
                'name' => 'VUVAA Identity API',
                'service_type' => 'nin_verification',
                'provider_identifier' => 'vuvaa',
                'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
                'config' => [
                    'username' => 'fuwa_demo_0417190741',
                    'password' => 'Password',
                    'encryption_key' => 'FD!-F=15B46BAD21',
                    'encryption_iv' => '0123456789012345',
                    'verify_nin_path' => 'verify_nin',
                    'in_person_path' => 'in_person_verification',
                    'share_code_path' => 'share_code',
                    'requery_path' => 'requery',
                    'reason' => 'nyscCheck',
                ],
                'status' => true,
                'priority' => 1,
                'supported_modes' => ['nin', 'selfie', 'share_code', 'requery'],
            ],
            [
                'name' => 'Dataverify API',
                'service_type' => 'nin_verification',
                'provider_identifier' => 'dataverify',
                'endpoint' => 'https://dataverify.org/developers/nin_slips/nin_premium',
                'status' => true,
                'priority' => 10,
                'supported_modes' => ['nin', 'phone', 'demographic', 'tracking'],
            ],
            [
                'name' => 'RobostTech API',
                'service_type' => 'nin_verification',
                'provider_identifier' => 'robosttech',
                'endpoint' => 'https://robosttech.com/api/nin_verify',
                'status' => true,
                'priority' => 20,
                'supported_modes' => ['nin', 'phone', 'validation', 'validation_status', 'clearance'],
            ],
            [
                'name' => 'VerifyMe NG',
                'service_type' => 'nin_verification',
                'provider_identifier' => 'verifyme',
                'endpoint' => 'https://v2.verifyme.ng/api/v1/verifications/identities/nin',
                'status' => true,
                'priority' => 30,
                'supported_modes' => ['nin', 'phone', 'demographic'],
            ],
        ];

        foreach ($defaults as $data) {
            try {
                $existing = CustomApi::where('provider_identifier', $data['provider_identifier'])->first();
                if (!$existing) {
                    $nextId = (int) (\Illuminate\Support\Facades\DB::table('custom_apis')->max('id') ?? 0) + 1;
                    $data['id'] = $nextId;
                    CustomApi::create($data);
                } elseif ($data['provider_identifier'] === 'vuvaa') {
                    $needsUpdate = empty($existing->endpoint)
                        || str_contains($existing->endpoint, 'api.vuvaa.com/v1')
                        || empty($existing->config)
                        || !$existing->status
                        || $existing->priority !== 1;
                    if ($needsUpdate) {
                        $existing->update([
                            'endpoint' => $data['endpoint'],
                            'config' => array_merge($existing->config ?? [], $data['config'] ?? []),
                            'supported_modes' => $data['supported_modes'] ?? ['nin', 'selfie', 'share_code', 'requery'],
                            'status' => true,
                            'priority' => 1,
                        ]);
                    }
                } elseif ($data['provider_identifier'] === 'dataverify') {
                    $normalized = DataVerifyClient::normalizeDomain((string) $existing->endpoint);
                    if ($normalized !== $existing->endpoint) {
                        $existing->update(['endpoint' => $normalized]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('NINController: failed ensuring provider ' . ($data['provider_identifier'] ?? 'unknown') . ': ' . $e->getMessage());
            }
        }
    }

    private function isTerminalIdentityError(string $message): bool
    {
        $lower = strtolower($message);

        $terminalPhrases = [
            'invalid nin',
            'nin not found',
            'record not found',
            'no record found',
            'record does not exist',
            'identity not found',
            'invalid bvn',
            'bvn not found',
            'invalid vnin',
            'vnin not found',
            'invalid phone',
            'phone not found',
            'face mismatch',
            'selfie does not match',
            'photo mismatch',
            'facial match failed',
            'face verification failed',
            'demographic mismatch',
            'unmatched demographic',
            'tracking id not found',
            'invalid tracking id',
        ];

        foreach ($terminalPhrases as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function isTechnicalError(string $message): bool
    {
        $lower = strtolower($message);

        return str_contains($lower, 'curl error')
            || str_contains($lower, 'could not resolve host')
            || str_contains($lower, 'connection timed out')
            || str_contains($lower, 'failed to connect')
            || str_contains($lower, 'sqlstate')
            || str_contains($lower, 'exception');
    }

    private function formatVerificationError(array $errors, string $fallback = 'Verification failed.'): string
    {
        if (empty($errors)) {
            return $fallback;
        }

        // 1. Separate provider prefix and message
        $cleaned = [];
        foreach ($errors as $err) {
            $msg = $err;
            if (str_contains($err, ': ')) {
                $msg = explode(': ', $err, 2)[1];
            }
            $cleaned[] = trim($msg);
        }

        // 2. Prioritize explicit user/identity errors over technical/infrastructure messages
        foreach ($cleaned as $msg) {
            if ($this->isTerminalIdentityError($msg)) {
                return $msg;
            }
        }

        // 3. Filter out technical network/cURL/SQL errors from being displayed to users
        $cleanMessages = [];
        foreach ($cleaned as $msg) {
            if ($this->isTechnicalError($msg)) {
                continue;
            }
            $cleanMessages[] = $msg;
        }

        if (!empty($cleanMessages)) {
            $unique = array_unique($cleanMessages);
            return implode(' | ', $unique);
        }

        return 'Verification service is temporarily unavailable. Please try again shortly.';
    }
}

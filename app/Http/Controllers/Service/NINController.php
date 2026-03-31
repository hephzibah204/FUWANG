<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use App\Services\PaidActionService;
use App\Services\Vuvaa\VuvaaClient;
use App\Services\VerificationResultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NINController extends Controller
{
    /**
     * Consolidated NIN Suite Subpage
     * Displays options for Validation, Personalize, IPE clearance, Print NIN Slip, and NIN verify.
     */
    public function suiteIndex()
    {
        return view('services.identity.nin_suite');
    }

    /**
     * Consolidated NIN Verification page.
     * Supports 3 lookup modes: NIN, Phone, Tracking ID (from legacy workflow).
     */
    public function index()
    {
        // Legacy pricing from verification_price table
        $legacyPricing = VerificationPrice::first();

        // Laravel providers from custom_apis
        $ninProviders = CustomApi::where('service_type', 'nin_verification')
                            ->where('status', true)
                            ->orderBy('priority', 'asc')
                            ->get();

        $faceProviders = CustomApi::where('service_type', 'nin_face_verification')
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->get();

        // Combine providers, remove duplicates by ID, and load their verification types
        $ninProviders = $ninProviders->concat($faceProviders)
                                     ->unique('id')
                                     ->values()
                                     ->load('verificationTypes');

        // Extract supported modes into a frontend-friendly map
        $providerModes = $ninProviders->mapWithKeys(function ($provider) {
            $modes = is_array($provider->supported_modes) ? $provider->supported_modes : [];
            // Fallback: If no modes are defined, assume legacy defaults based on service_type
            if (empty($modes)) {
                if ($provider->service_type === 'nin_face_verification') {
                    $modes = ['selfie'];
                } else {
                    $modes = ['nin', 'phone', 'demographic', 'tracking', 'vnin'];
                }
            }
            return [$provider->id => $modes];
        });

        // Determine default price per mode (legacy-first, fallback to provider)
        $prices = [
            'nin'      => (float) ($legacyPricing->nin_by_nin_price ?? $ninProviders->first()?->price ?? 200),
            'phone'    => (float) ($legacyPricing->nin_by_number_price ?? $ninProviders->first()?->price ?? 200),
            'tracking' => (float) ($legacyPricing->verify_by_tracking_id ?? $ninProviders->first()?->price ?? 300),
            'selfie'   => (float) (\App\Models\SystemSetting::get('nin_face_verification_price', $faceProviders->first()?->price ?? 500)),
            'share_code' => (float) ($legacyPricing->nin_by_nin_price ?? $ninProviders->first()?->price ?? 200),
            'requery' => 0.0,
        ];

        // Vault history for this user
        $myResults = VerificationResult::where('user_id', Auth::id())
                        ->whereIn('service_type', ['nin_verification', 'nin_face_verification'])
                        ->latest()
                        ->get();

        // Legacy API center for endpoint fallback
        $apiCenter = ApiCenter::first();

        return view('services.identity.nin', compact('prices', 'ninProviders', 'providerModes', 'myResults', 'apiCenter'));
    }

    /**
     * Handle NIN Verification.
     * Legacy workflow: deduct balance → API call → refund on failure.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'mode'       => ['required', 'in:nin,phone,tracking,demographic,selfie,share_code,requery'],
            'number'     => ['required_if:mode,nin,phone,tracking,selfie', 'nullable', 'string'],
            'firstname'  => ['required_if:mode,demographic', 'nullable', 'string'],
            'lastname'   => ['required_if:mode,demographic', 'nullable', 'string'],
            'gender'     => ['required_if:mode,demographic', 'nullable', 'in:M,F,male,female'],
            'dob'        => ['required_if:mode,demographic', 'nullable', 'string'],
            'selfie'     => ['required_if:mode,selfie', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
            'share_code' => ['required_if:mode,share_code', 'nullable', 'string', 'max:64'],
            'reference_id' => ['required_if:mode,requery', 'nullable', 'string', 'max:120'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
            'verification_type' => ['nullable', 'string', 'max:80'],
            'output_type' => ['nullable', 'in:info_page,standard_slip,regular_slip,premium_slip,vnin_slip'],
        ]);

        $user = Auth::user();
        $mode = $request->input('mode', 'nin');
        $selfieMeta = null;
        if ($mode === 'selfie') {
            $file = $request->file('selfie');
            if (!$file) {
                return response()->json(['status' => false, 'message' => 'Selfie image is required.'], 422);
            }
        }

        // ── Step 1: Determine Price (Legacy-first) ─────────────────
        $legacyPricing = VerificationPrice::first();
        $priceMap = [
            'nin'      => (float) ($legacyPricing->nin_by_nin_price ?? 200),
            'phone'    => (float) ($legacyPricing->nin_by_number_price ?? 200),
            'tracking' => (float) ($legacyPricing->verify_by_tracking_id ?? 300),
            'selfie'   => (float) (\App\Models\SystemSetting::get('nin_face_verification_price', 500)),
            'share_code' => (float) ($legacyPricing->nin_by_nin_price ?? 200),
            'requery' => 0.0,
        ];
        $price = $priceMap[$mode] ?? 200;

        // ── Step 2: Determine Provider ──────────────────────────────
        $provider = null;
        $type = null;

        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = $this->pickProviderForMode($mode);
        }

        // Override price from provider/type if set
        if ($provider) {
            $typeKey = $request->input('verification_type');
            if ($typeKey) {
                $type = $provider->verificationTypes()
                    ->where('status', true)
                    ->where('type_key', $typeKey)
                    ->first();
                if ($type) {
                    $price = (float) $type->price;
                }
            } elseif ($provider->price) {
                // Only override if provider has an explicit price
                // Legacy pricing takes precedence when provider price is null
            }
        }

        // Fallback to legacy ApiCenter if no custom provider
        $apiCenter = null;
        if (!$provider) {
            if ($mode === 'selfie') {
                return response()->json(['status' => false, 'message' => 'No active verification provider configured for selfie verification. Contact admin.'], 503);
            }
            $apiCenter = ApiCenter::first();
            if (!$apiCenter) {
                return response()->json(['status' => false, 'message' => 'No active verification provider configured. Contact admin.']);
            }
        }

        // ── Step 3: Deduct Balance First (Legacy pattern) ───────────
        $orderType = match($mode) {
            'phone'    => 'NIN by Phone Verification',
            'tracking' => 'NIN by Tracking ID',
            'demographic' => 'NIN by Demographic',
            'selfie' => 'NIN In-Person Verification (Selfie)',
            'share_code' => 'NIN Share Code Verification',
            'requery' => 'NIN Requery',
            default    => 'NIN Verification',
        };

        $paid = app(PaidActionService::class)->run($user, $price, $orderType, 'NIN', function () use ($mode, $provider, $type, $request, $apiCenter, $user, &$selfieMeta) {
            if ($mode === 'demographic' && !$provider) {
                throw new \Exception('Demographic search requires an active provider integration.');
            }
            if ($mode === 'selfie' && !$provider) {
                throw new \Exception('Selfie verification requires an active provider integration.');
            }
            if (($mode === 'share_code' || $mode === 'requery') && !$provider) {
                throw new \Exception('This verification mode requires an active provider integration.');
            }

            if ($mode === 'selfie') {
                $file = $request->file('selfie');
                if (!$file) {
                    throw new \Exception('Selfie image is required.');
                }

                $bytes = @file_get_contents($file->getRealPath());
                if ($bytes === false) {
                    throw new \Exception('Unable to read selfie image.');
                }

                $image = @imagecreatefromstring($bytes);
                $jpegBytes = $bytes;
                $mime = $file->getClientMimeType();
                $webpBytes = null;

                if ($image !== false) {
                    ob_start();
                    imagejpeg($image, null, 80);
                    $jpegBytes = (string) ob_get_clean();
                    $mime = 'image/jpeg';

                    if (function_exists('imagewebp')) {
                        ob_start();
                        imagewebp($image, null, 80);
                        $webpBytes = (string) ob_get_clean();
                    }

                    imagedestroy($image);
                }

                $request->attributes->set('selfie_base64', base64_encode($jpegBytes));

                $base = 'nin-selfie-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
                $dir = 'private/selfies/' . $user->id;
                $path = $dir . '/' . $base . '.jpg';
                Storage::disk('local')->put($path, $jpegBytes);

                $webpPath = null;
                if (is_string($webpBytes) && $webpBytes !== '') {
                    $webpPath = $dir . '/' . $base . '.webp';
                    Storage::disk('local')->put($webpPath, $webpBytes);
                }

                $selfieMeta = [
                    'disk' => 'local',
                    'path' => $path,
                    'webp_path' => $webpPath,
                    'mime' => $mime,
                    'size' => strlen($jpegBytes),
                    'sha256' => hash('sha256', $jpegBytes),
                ];
                $request->attributes->set('selfie_meta', $selfieMeta);
            }

            $response = $provider
                ? $this->callCustomProvider($provider, $type, $request, $mode)
                : $this->callLegacyApi($apiCenter, $request, $mode);

            if ($response['status']) {
                $data = $response['data'];
                if ($mode === 'selfie' && is_array($data)) {
                    $data['_selfie'] = $selfieMeta;
                }
                $serviceType = $mode === 'selfie' ? 'nin_face_verification' : 'nin_verification';
                $data['_verification_mode'] = $mode;
                if ($mode === 'demographic') {
                    $data['_lookup'] = [
                        'mode' => $mode,
                        'firstname' => $request->firstname,
                        'lastname' => $request->lastname,
                        'dob' => $request->dob,
                        'gender' => $request->gender,
                    ];
                }

                // Store result in verification vault
                $result = app(VerificationResultService::class)->create(
                    $user,
                    $serviceType,
                    (string) ($request->number ?? $request->share_code ?? $request->reference_id ?? ($request->firstname . ' ' . $request->lastname . ' ' . $request->dob)),
                    (string) ($response['provider'] ?? 'Legacy API'),
                    $data,
                    'success',
                    'NIN'
                );

                $payload = [
                    'status'    => true,
                    'message'   => $mode === 'selfie' ? 'Identity Verified' : ($mode === 'requery' ? 'Record Retrieved' : 'NIN Record Found'),
                    'image'     => $data['photo'] ?? $data['image'] ?? null,
                    'nin'       => $data['nin'] ?? $request->number ?? null,
                    'data'      => $data,
                    'result_id' => $result->id,
                ];

                // Attach slip URLs if requested
                $outputType = $request->input('output_type');
                if (in_array($outputType, ['standard_slip', 'regular_slip', 'premium_slip', 'vnin_slip'])) {
                    $payload['slip_url'] = route('services.nin.slip', ['id' => $result->id, 'type' => $outputType]);
                } elseif ($outputType === 'info_page') {
                    $payload['report_url'] = route('services.verification.report', $result->id);
                }

                return $payload;
            }

            throw new \Exception($response['message'] ?? 'Verification failed — no matching record found.');
        });

        if (!$paid['ok']) {
            Log::warning('NIN Verification Failed', [
                'user'    => $user->email,
                'mode'    => $mode,
                'number'  => $request->number,
                'error'   => $paid['message'],
            ]);

            return response()->json(['status' => false, 'message' => $paid['message']]);
        }

        if ($mode === 'selfie') {
            Log::info('NIN Selfie Verification Completed', [
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'result_id' => $paid['result']['result_id'] ?? null,
                'selfie_path' => $selfieMeta['path'] ?? null,
            ]);
        }

        return response()->json($paid['result']);
    }

    /**
     * Call the CustomApi provider (Laravel system).
     * Includes retry logic, verification types, and multi-provider support.
     */
    private function callCustomProvider(CustomApi $provider, $type, Request $request, string $mode): array
    {
        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $timeout = (int) ($provider->timeout_seconds ?? 45);
        $retryCount = (int) ($provider->retry_count ?? 0);
        $retryDelayMs = (int) ($provider->retry_delay_ms ?? 0);

        // Build payload based on mode
        $payload = [];

        if (VuvaaClient::isVuvaaProvider($provider)) {
            $client = new VuvaaClient($provider);
            $pathOverride = null;
            if ($type && is_array($type->meta)) {
                $pathOverride = (string) ($type->meta['path_suffix'] ?? '');
                $pathOverride = $pathOverride !== '' ? '/' . ltrim($pathOverride, '/') : null;
            }

            if ($mode === 'nin') {
                $result = $client->verifyNin((string) $request->number);
            } elseif ($mode === 'selfie') {
                $img = (string) $request->attributes->get('selfie_base64', '');
                if ($img === '') {
                    return [
                        'status' => false,
                        'message' => 'Selfie image is missing.',
                    ];
                }
                $result = $client->verifyInPerson((string) $request->number, $img, null, $pathOverride);
            } elseif ($mode === 'share_code') {
                $code = (string) $request->share_code;
                if ($code === '') {
                    return [
                        'status' => false,
                        'message' => 'Share code is required.',
                    ];
                }
                $result = $client->verifyShareCode($code, null, $pathOverride);
            } elseif ($mode === 'requery') {
                $ref = (string) $request->reference_id;
                if ($ref === '') {
                    return [
                        'status' => false,
                        'message' => 'Reference ID is required.',
                    ];
                }
                $result = $client->requery($ref, $pathOverride);
            } else {
                return [
                    'status' => false,
                    'message' => 'Selected provider does not support this verification mode.',
                ];
            }

            if (!$result['ok']) {
                return [
                    'status' => false,
                    'message' => (string) ($result['message'] ?? 'Provider error.'),
                ];
            }

            return [
                'status' => true,
                'data' => $result['data'],
                'provider' => $provider->name,
            ];
        }

        if (str_contains($endpoint, 'verifyme.ng')) {
            // VerifyMe requires firstname, lastname, dob in payload and number in URL
            if ($mode === 'demographic') {
                // Some providers support pure data validation without number
                $endpoint = rtrim($endpoint, '/');
                $payload = [
                    'firstname' => $request->firstname,
                    'lastname'  => $request->lastname,
                    'dob'       => $request->dob,
                    'gender'    => $request->gender,
                ];
            } else {
                $slug = $mode === 'phone' ? 'nin_phone' : 'nin';
                $endpoint = str_replace('/nin', '/' . $slug, rtrim($endpoint, '/')) . '/' . $request->number;
                $payload = [
                    'firstname' => $request->firstname,
                    'lastname'  => $request->lastname,
                    'dob'       => $request->dob,
                ];
            }
        } elseif (str_contains($provider->provider_identifier ?? '', 'dataverify')) {
            // DataVerify legacy-style
            if ($mode === 'demographic') {
                $payload = [
                    'api_key'   => $provider->api_key,
                    'firstname' => $request->firstname,
                    'lastname'  => $request->lastname,
                    'gender'    => $request->gender,
                    'dob'       => $request->dob,
                ];
            } else {
                $payload = [
                    'api_key' => $provider->api_key,
                    $mode === 'phone' ? 'phone' : ($mode === 'tracking' ? 'tracking_id' : 'nin') => $request->number,
                ];
            }
        } else {
            // Generic provider
            if ($mode === 'demographic') {
                $payload = [
                    'firstname' => $request->firstname,
                    'lastname'  => $request->lastname,
                    'gender'    => $request->gender,
                    'dob'       => $request->dob,
                ];
            } elseif ($mode === 'selfie') {
                $payload = [
                    'number' => (string) $request->number,
                    'image' => (string) $request->attributes->get('selfie_base64', ''),
                ];
            } else {
                $payload = ['number' => $request->number];
                if ($request->firstname) $payload['firstname'] = $request->firstname;
                if ($request->lastname) $payload['lastname'] = $request->lastname;
                if ($request->dob) $payload['dob'] = $request->dob;
            }
        }

        // Apply verification type meta overrides
        if ($type && is_array($type->meta)) {
            $payload = array_merge($payload, (array) ($type->meta['payload'] ?? []));
            $extraHeaders = (array) ($type->meta['headers'] ?? []);
            if (!empty($extraHeaders)) $headers = array_merge($headers, $extraHeaders);

            $pathSuffix = (string) ($type->meta['path_suffix'] ?? '');
            if ($pathSuffix !== '') {
                $endpoint = rtrim($endpoint, '/') . '/' . ltrim($pathSuffix, '/');
            }
            $query = (array) ($type->meta['query'] ?? []);
            if (!empty($query)) {
                $endpoint .= (str_contains($endpoint, '?') ? '&' : '?') . http_build_query($query);
            }
        }

        // Infer common authorization headers if not explicitly supplied
        $autoHeaders = [];
        $pid = strtolower((string) ($provider->provider_identifier ?? ''));
        $cfg = is_array($provider->config) ? $provider->config : [];
        if (empty($headers) || (!isset($headers['Authorization']) && !isset($headers['authorization']))) {
            if (str_contains($pid, 'verifyme') || str_contains($endpoint, 'verifyme.ng')) {
                if (!empty($provider->secret_key)) $autoHeaders['Authorization'] = 'Bearer ' . $provider->secret_key;
            } elseif (str_contains($pid, 'youverify') || str_contains($endpoint, 'youverify')) {
                if (!empty($provider->secret_key)) $autoHeaders['Authorization'] = 'Bearer ' . $provider->secret_key;
                if (!empty($provider->api_key)) $autoHeaders['X-API-KEY'] = $provider->api_key;
            } elseif (str_contains($pid, 'dojah') || str_contains($endpoint, 'dojah')) {
                if (!empty($cfg['app_id'] ?? null)) $autoHeaders['AppId'] = $cfg['app_id'];
                if (!empty($cfg['app_key'] ?? null)) $autoHeaders['AppKey'] = $cfg['app_key'];
                if (!empty($provider->secret_key)) $autoHeaders['Authorization'] = 'Bearer ' . $provider->secret_key;
            } elseif (str_contains($pid, 'smile') || str_contains($endpoint, 'smileid') || str_contains($endpoint, 'smile.id')) {
                if (!empty($cfg['partner_id'] ?? null)) $autoHeaders['X-Partner-ID'] = $cfg['partner_id'];
                if (!empty($provider->secret_key)) $autoHeaders['Authorization'] = 'Bearer ' . $provider->secret_key;
            }
        }
        if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
            $autoHeaders['Content-Type'] = 'application/json';
        }
        $headers = array_merge($autoHeaders, $headers ?? []);

        // Make HTTP request with retry
        $http = Http::timeout($timeout);
        if (!empty($headers)) $http = $http->withHeaders($headers);

        $response = null;
        for ($attempt = 0; $attempt <= $retryCount; $attempt++) {
            $response = $http->post($endpoint, $payload);
            if ($response->successful()) break;
            if ($attempt < $retryCount && $retryDelayMs > 0) {
                usleep($retryDelayMs * 1000);
            }
        }

        if (!$response || !$response->successful()) {
            return [
                'status'  => false,
                'message' => $response?->json()['message'] ?? $response?->json()['detail'] ?? 'Provider connection error.',
            ];
        }

        $resData = $response->json();
        $unifiedData = null;

        // Map response to unified format
        if (isset($resData['status']) && $resData['status'] === 'success' && isset($resData['data'])) {
            // VerifyMe style
            $unifiedData = $resData['data'];
        } elseif (isset($resData['status']) && $resData['status'] === true && isset($resData['data'])) {
            // DataVerify / generic style
            $unifiedData = $resData['data'];
        } elseif (isset($resData['response']) && is_array($resData['response'])) {
            // Legacy response format (array of records)
            $unifiedData = $resData['response'][0] ?? $resData['response'];
        }

        if (!$unifiedData) {
            return [
                'status'  => false,
                'message' => 'Verification returned empty or unrecognized data format.',
            ];
        }

        return [
            'status'   => true,
            'data'     => $unifiedData,
            'provider' => $provider->name,
        ];
    }

    private function pickProviderForMode(string $mode): ?CustomApi
    {
        $serviceType = $mode === 'selfie' ? 'nin_face_verification' : 'nin_verification';

        $providers = CustomApi::where('service_type', $serviceType)
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($providers->isEmpty()) {
            return null;
        }

        $ordered = $providers->sort(function (CustomApi $a, CustomApi $b) use ($mode) {
            $aIsV = VuvaaClient::isVuvaaProvider($a);
            $bIsV = VuvaaClient::isVuvaaProvider($b);
            if (in_array($mode, ['nin', 'selfie', 'share_code', 'requery'], true) && $aIsV !== $bIsV) {
                return $aIsV ? -1 : 1;
            }
            return $a->priority <=> $b->priority;
        })->values();

        foreach ($ordered as $p) {
            if (VuvaaClient::isVuvaaProvider($p) && !in_array($mode, ['nin', 'selfie', 'share_code', 'requery'], true)) {
                continue;
            }
            return $p;
        }

        return null;
    }

    /**
     * Call the Legacy API Center endpoints (fallback when no CustomApi provider exists).
     * Mirrors the exact cURL patterns from verify_nin.php and nin_by_phone.php.
     */
    private function callLegacyApi(ApiCenter $apiCenter, Request $request, string $mode): array
    {
        $apiKey = $apiCenter->dataverify_api_key;

        $endpoint = match($mode) {
            'phone'    => $apiCenter->dataverify_endpoint_phone,
            'tracking' => $apiCenter->dataverify_endpoint_tid,
            default    => $apiCenter->dataverify_endpoint_nin,
        };

        if (!$endpoint || !$apiKey) {
            return ['status' => false, 'message' => 'Legacy API endpoint not configured for this mode.'];
        }

        $payload = ['api_key' => $apiKey];
        $payload[$mode === 'phone' ? 'phone' : ($mode === 'tracking' ? 'tracking_id' : 'nin')] = $request->number;

        try {
            $response = Http::timeout(45)->post($endpoint, $payload);

            if ($response->successful()) {
                $resData = $response->json();

                if (isset($resData['status']) && $resData['status'] === true && isset($resData['data'])) {
                    return [
                        'status'   => true,
                        'data'     => $resData['data'],
                        'provider' => 'DataVerify (Legacy)',
                    ];
                } elseif (isset($resData['response']) && is_array($resData['response'])) {
                    return [
                        'status'   => true,
                        'data'     => $resData['response'][0] ?? $resData['response'],
                        'provider' => 'DataVerify (Legacy)',
                    ];
                }

                return [
                    'status'  => false,
                    'message' => $resData['message'] ?? 'No matching NIN record found.',
                ];
            }

            return [
                'status'  => false,
                'message' => 'API Error: ' . ($response->json()['message'] ?? 'Connection failed.'),
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'cURL Error: ' . $e->getMessage()];
        }
    }
}

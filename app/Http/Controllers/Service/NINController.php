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

    public function index()
    {
        $legacyPricing = VerificationPrice::first();

        $ninProviders = CustomApi::whereIn('service_type', ['nin_verification', 'nin_face_verification'])
                            ->where('status', true)
                            ->orderBy('priority', 'asc')
                            ->get();

        $providerModes = $ninProviders->mapWithKeys(function ($provider) {
            $modes = is_array($provider->supported_modes) ? $provider->supported_modes : [];
            if (empty($modes)) {
                $modes = $provider->service_type === 'nin_face_verification' ? ['selfie'] : ['nin', 'phone', 'demographic', 'tracking', 'vnin'];
            }
            return [$provider->id => $modes];
        });

        $prices = [
            'nin' => (float) ($legacyPricing->nin_by_nin_price ?? 200),
            'phone' => (float) ($legacyPricing->nin_by_number_price ?? 200),
            'tracking' => (float) ($legacyPricing->verify_by_tracking_id ?? 300),
            'selfie' => (float) (\App\Models\SystemSetting::get('nin_face_verification_price', 500)),
            'share_code' => (float) ($legacyPricing->nin_by_nin_price ?? 200),
            'requery' => 0.0,
        ];

        $myResults = VerificationResult::where('user_id', Auth::id())
                        ->whereIn('service_type', ['nin_verification', 'nin_face_verification'])
                        ->latest()
                        ->get();

        $apiCenter = ApiCenter::first();

        return view('services.identity.nin', compact('prices', 'ninProviders', 'providerModes', 'myResults', 'apiCenter'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'mode' => ['required', 'in:nin,phone,tracking,demographic,selfie,share_code,requery'],
            'number' => ['required_if:mode,nin,phone,tracking,selfie', 'nullable', 'string'],
            'selfie' => ['required_if:mode,selfie', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $user = Auth::user();
        $mode = $request->input('mode', 'nin');

        $provider = $this->pickProviderForMode($mode, (int) $request->input('api_provider_id'));

        if ($provider) {
            try {
                $client = new VuvaaClient($provider);
                $walletResp = $client->getWalletDetails();
                
                if (!$walletResp['ok']) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to check wallet balance. ' . $walletResp['message'],
                    ], 402);
                }
                
                $unitsAvailable = (int) ($walletResp['data']['wallet_units'] ?? 0);
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

        $orderType = 'NIN Verification';
        if($mode !== 'nin') $orderType = 'NIN ' . ucfirst($mode) . ' Verification';

        $paid = app(PaidActionService::class)->run($user, $price, $orderType, 'NIN', function () use ($mode, $provider, $request, $user) {
            $selfieMeta = null;
            if ($mode === 'selfie') {
                $selfieMeta = $this->processSelfie($request, $user->id);
            }

            $apiCenter = $provider ? null : ApiCenter::first();
            if (!$provider && !$apiCenter) {
                throw new \Exception('No active verification provider is configured.');
            }

            $response = $provider
                ? $this->callCustomProvider($provider, $request, $mode, $selfieMeta)
                : $this->callLegacyApi($apiCenter, $request, $mode);

            if ($response['status']) {
                $data = $response['data'];
                $photoBase64 = null;
                if ($mode === 'selfie') {
                    $photoBase64 = $data['photo'] ?? null;
                    $data = PhotoComplianceFilter::sanitize($data);
                }

                $result = app(VerificationResultService::class)->create(
                    $user,
                    $mode === 'selfie' ? 'nin_face_verification' : 'nin_verification',
                    (string) ($request->number ?? $request->share_code ?? $request->reference_id),
                    (string) ($response['provider'] ?? 'Legacy API'),
                    $data,
                    'success',
                    'NIN'
                );

                return [
                    'status' => true,
                    'message' => 'Verification Successful',
                    'data' => $data,
                    'photo' => $photoBase64,
                    'result_id' => $result->id,
                ];
            }

            throw new \Exception($response['message'] ?? 'Verification failed.');
        });

        if (!$paid['ok']) {
            return response()->json(['status' => false, 'message' => $paid['message']]);
        }

        return response()->json($paid['result']);
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
        
        $statusCode = $response['data']['statusCode'] ?? '99';
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
            } else {
                $method = 'verify' . ucfirst($mode); // verifyNin, verifyShareCode, etc.
                $result = $client->$method((string) $request->input($mode === 'nin' ? 'number' : $mode));
            }
            return ['status' => $result['ok'], 'message' => $result['message'], 'data' => $result['data'], 'provider' => $provider->name];
        }
        // ... other provider logic can go here ...
        return ['status' => false, 'message' => 'Provider not supported'];
    }

    // Simplified legacy API call
    private function callLegacyApi(ApiCenter $apiCenter, Request $request, string $mode): array
    {
        $endpoint = $apiCenter->dataverify_endpoint_nin; // Simplified
        $response = Http::post($endpoint, ['api_key' => $apiCenter->dataverify_api_key, 'nin' => $request->number]);
        if($response->successful() && $response->json()['status']) {
            return ['status' => true, 'data' => $response->json()['data'], 'provider' => 'Legacy API'];
        }
        return ['status' => false, 'message' => $response->json()['message'] ?? 'Legacy provider error'];
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

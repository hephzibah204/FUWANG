<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use App\Services\PaidActionService;
use App\Services\VerificationResultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BVNController extends Controller
{
    /**
     * Display the Consolidated BVN Verification Index Page
     */
    public function index()
    {
        // 1. Load CustomApi Providers for the 3 BVN Modes
        $bvnProviders = CustomApi::where('service_type', 'bvn_verification')->where('status', true)->get();
        $matchProviders = CustomApi::where('service_type', 'bvn_matching')->where('status', true)->get();
        $combinedProviders = CustomApi::where('service_type', 'bvn_nin_phone_verification')->where('status', true)->get();

        // 2. Load legacy fallback ApiCenter credentials (if needed by views)
        $apiCenter = ApiCenter::first();

        // 3. Load Verification History (Vault) for all BVN mode types
        $myResults = VerificationResult::where('user_id', Auth::id())
            ->whereIn('service_type', ['bvn_verification', 'bvn_matching', 'bvn_nin_phone_verification'])
            ->latest()
            ->get();

        // 4. Load Pricing Setup
        $legacyPrice = VerificationPrice::first();
        $prices = [
            'bvn_by_bvn' => $bvnProviders->first()->price ?? ($legacyPrice->bvn_by_bvn ?? 100),
            'basic' => \App\Models\SystemSetting::get('bvn_basic_price', 100),
            'premium' => \App\Models\SystemSetting::get('bvn_premium_price', 500),
            'match' => \App\Models\SystemSetting::get('bvn_match_price', 150),
            'combined' => \App\Models\SystemSetting::get('bvn_nin_phone_price', 250),
        ];

        return view('services.identity.bvn', compact(
            'bvnProviders', 'matchProviders', 'combinedProviders', 
            'apiCenter', 'myResults', 'prices'
        ));
    }

    /**
     * Handle Consolidated BVN Verification (Legacy-First Workflow)
     */
    public function verify(Request $request)
    {
        // Require mode: standard, match, combi
        $request->validate([
            'mode' => ['required', 'string', 'in:standard,match,combi'],
            'number' => ['required', 'string'], // BVN or Phone
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $mode = $request->input('mode');
        $user = Auth::user();

        // 1. Determine Identity & Price
        list($price, $orderType, $serviceType, $txReference) = $this->determinePricing($request, $mode);

        $paid = app(PaidActionService::class)->run($user, (float) $price, (string) $orderType, (string) $txReference, function () use ($request, $mode, $serviceType, $user) {
            $provider = null;
            if ($request->filled('api_provider_id')) {
                $provider = CustomApi::find($request->api_provider_id);
            } else {
                $provider = CustomApi::where('service_type', $serviceType)->where('status', true)->first();
            }

            $response = null;
            if ($provider) {
                // Call Modern Custom Provider
                $response = $this->callCustomProvider($provider, $request, $mode);
            } else {
                // Call Legacy Fallback (cURL mirror to BVN script)
                $response = $this->callLegacyApi($request, $mode);
            }

            // 4. Check Response and Apply Results
            if ($response && $response['status']) {
                $data = $response['data'];
                
                // For BVN, try to extract photo and construct safe names
                $image = $data['photoId'] ?? $data['image'] ?? $data['photo'] ?? null;
                $bvnData = $data['response'] ?? $data;

                // 5. Store Verification Result for Vault
                $result = app(VerificationResultService::class)->create(
                    $user,
                    (string) $serviceType,
                    (string) $request->number,
                    (string) ($provider ? $provider->name : 'NIBSS/Gateway'),
                    array_merge($bvnData, ['photo' => $image]),
                    'success',
                    'BVN'
                );

                return [
                    'status' => true,
                    'image' => $image,
                    'bvn' => $bvnData['bvn'] ?? $request->number,
                    'data' => $bvnData,
                    'result_id' => $result->id,
                    'message' => 'BVN verified successfully'
                ];
            }

            throw new \Exception($response['message'] ?? 'Identity record not found.');
        });

        if (!$paid['ok']) {
            Log::error('BVN Verification Failed: ' . $paid['message'], ['user' => $user->id]);
            return response()->json(['status' => false, 'message' => $paid['message']]);
        }

        return response()->json($paid['result']);
    }

    /**
     * Determine Pricing based on Mode and System Settings
     */
    private function determinePricing(Request $request, $mode)
    {
        $price = 100; // Default fallback
        $orderType = 'BVN Verification';
        $serviceType = 'bvn_verification';
        $txReference = 'BVN';

        if ($mode === 'standard') {
            $type = $request->input('verification_type', 'basic'); // basic or premium
            $price = $type === 'premium' 
                ? \App\Models\SystemSetting::get('bvn_premium_price', 500)
                : \App\Models\SystemSetting::get('bvn_basic_price', 100);
            
            $orderType = 'BVN Lookup (' . ucfirst($type) . ')';
            $serviceType = 'bvn_verification';
        } 
        elseif ($mode === 'match') {
            $price = \App\Models\SystemSetting::get('bvn_match_price', 150);
            $orderType = 'BVN Identity Match';
            $serviceType = 'bvn_matching';
            $txReference = 'BVNMATCH';
            
            // Validate match specific fields
            $request->validate([
                'firstname' => 'required|string',
                'lastname' => 'required|string'
            ]);
        } 
        elseif ($mode === 'combi') {
            $price = \App\Models\SystemSetting::get('bvn_nin_phone_price', 250);
            $orderType = 'Combined BVN/NIN/Phone Lookup';
            $serviceType = 'bvn_nin_phone_verification';
            $txReference = 'COMBINED';
            
            // Validate combi specific fields
            $request->validate([
                'id_type' => 'required|in:nin,bvn,frsc'
            ]);
        }

        return [$price, $orderType, $serviceType, $txReference];
    }

    /**
     * Call Laravel CustomApi Provider using HTTP client
     */
    private function callCustomProvider($provider, Request $request, $mode)
    {
        $payload = [];
        $url = rtrim($provider->endpoint, '/') . '/' . urlencode($request->number);
        $headers = is_array($provider->headers) ? $provider->headers : [];

        // Build Payload depending on Mode
        if ($mode === 'standard') {
            $typeKey = $request->input('verification_type', 'basic');
            $typeConfig = $provider->verificationTypes()->where('status', true)->where('type_key', $typeKey)->first();
            
            $payload = [
                'firstname' => $request->firstname ?? '',
                'lastname' => $request->lastname ?? '',
                'dob' => $request->dob ?? '',
            ];

            if ($typeConfig && is_array($typeConfig->meta)) {
                $payload = array_merge($payload, (array) ($typeConfig->meta['payload'] ?? []));
                $extraHeaders = (array) ($typeConfig->meta['headers'] ?? []);
                if (!empty($extraHeaders)) {
                    $headers = array_merge($headers, $extraHeaders);
                }
                $query = (array) ($typeConfig->meta['query'] ?? []);
                if (!empty($query)) {
                    $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
                }
            } else {
                if (in_array($typeKey, ['basic', 'premium'])) {
                    $url .= '?type=' . $typeKey;
                }
            }
        } 
        elseif ($mode === 'match') {
            $payload = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
            ];
            if ($request->filled('dob')) {
                $payload['dob'] = $request->dob;
            }
        } 
        elseif ($mode === 'combi') {
            $payload = [
                'id_type' => $request->id_type,
            ];
        }

        // Retry config
        $timeout = (int) ($provider->timeout_seconds ?? 60);
        $retryCount = (int) ($provider->retry_count ?? 1);
        $retryDelayMs = (int) ($provider->retry_delay_ms ?? 100);

        $http = Http::timeout($timeout)->withHeaders($headers);
        $response = null;

        for ($attempt = 0; $attempt <= $retryCount; $attempt++) {
            try {
                if ($mode === 'combi' || !empty($payload)) {
                    $response = $http->post($url, $payload);
                } else {
                    $response = $http->get($url);
                }

                if ($response->successful()) {
                    break;
                }
            } catch (\Exception $e) {
                // If this is the last attempt, just let it fail naturally
                Log::warning("BVN Provider Attempt {$attempt} failed: " . $e->getMessage());
            }

            if ($attempt < $retryCount && $retryDelayMs > 0) {
                usleep($retryDelayMs * 1000);
            }
        }

        if ($response && $response->successful() && (isset($response['status']) && $response['status'] === 'success' || isset($response['data']))) {
            return [
                'status' => true,
                'data' => $response->json()['data'] ?? $response->json()
            ];
        }

        return [
            'status' => false,
            'message' => $response['message'] ?? 'Provider Error'
        ];
    }

    /**
     * Fallback to Legacy API logic (mirroring bvn_api.php)
     */
    private function callLegacyApi(Request $request, $mode)
    {
        $apiCenter = ApiCenter::first();
        if (!$apiCenter) {
            return ['status' => false, 'message' => 'Legacy API Center not configured.'];
        }

        $apiKey = $apiCenter->dataverify_api_key;
        $endpoint = $apiCenter->dataverify_endpoint_bvn;

        if (!$endpoint || !$apiKey) {
            return ['status' => false, 'message' => 'Legacy BVN endpoint not configured.'];
        }

        $payload = [
            'api_key' => $apiKey,
            'bvn' => $request->number,
        ];

        if ($mode === 'match') {
            $payload['firstname'] = $request->firstname;
            $payload['lastname'] = $request->lastname;
            if ($request->filled('dob')) {
                $payload['dob'] = $request->dob;
            }
        } elseif ($mode === 'combi') {
            $payload['id_type'] = $request->id_type;
        }

        $http = Http::timeout(45)->post($endpoint, $payload);

        if ($http->successful()) {
            $resData = $http->json();

            if (isset($resData['status']) && $resData['status'] === true && isset($resData['data'])) {
                return [
                    'status' => true,
                    'data' => $resData['data'],
                ];
            }

            if (isset($resData['data']) && is_array($resData['data'])) {
                return [
                    'status' => true,
                    'data' => $resData['data'],
                ];
            }

            return [
                'status' => false,
                'message' => $resData['message'] ?? 'Legacy BVN API returned an unrecognized response.',
            ];
        }

        return [
            'status' => false,
            'message' => 'Legacy BVN API returned an error: ' . $http->status(),
        ];
    }
}

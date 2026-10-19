<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApi;
use App\Models\ApiCenter;
use App\Models\AccountBalance;
use App\Models\Transaction;
use App\Models\VerificationPrice;
use App\Services\WalletService;
use App\Services\VerificationResultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Models\VerificationResult;

class VerificationController extends Controller
{
    /**
     * Generate PDF Report for a verification result
     */
    public function generateReport($id)
    {
        $result = VerificationResult::findOrFail($id);
        
        // Ensure user owns this result
        if ($result->user_id !== Auth::id()) {
            abort(403);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.verification_report', compact('result'));
        
        return $pdf->download('Verification_Report_' . $result->reference_id . '.pdf');
    }

    /**
     * Helper to store verification results for persistence and PDF reporting
     */
    private function storeResult($user, $serviceType, $identifier, $providerName, $responseData, $status = 'success')
    {
        return app(VerificationResultService::class)->create(
            $user,
            (string) $serviceType,
            (string) $identifier,
            (string) $providerName,
            $responseData,
            (string) $status
        );
    }

    /**
     * Display the Unified Verification Hub
     */
    public function hubIndex(Request $request)
    {
        $serviceTypes = [
            'drivers_license', 'biometric_verification', 'cac_verification',
            'tin_verification', 'passport_verification', 'voters_card_verification',
            'plate_number_verification', 'address_verification', 'nin_face',
            'credit_bureau_advance', 'validation', 'clearance', 'ipe_clearance', 'personalization'
        ];

        $providers = CustomApi::whereIn('service_type', $serviceTypes)
            ->where('status', true)
            ->get()
            ->groupBy('service_type');

        $history = VerificationResult::where('user_id', Auth::id())
            ->whereIn('service_type', $serviceTypes)
            ->latest()
            ->get();

        $vPrice = class_exists(\App\Models\VerificationPrice::class) ? \App\Models\VerificationPrice::first() : null;

        $prices = [
            'drivers_license' => \App\Models\SystemSetting::get('drivers_license_price', 300),
            'cac_verification' => \App\Models\SystemSetting::get('cac_verification_price', 500),
            'tin_verification' => \App\Models\SystemSetting::get('tin_verification_price', 200),
            'passport_verification' => \App\Models\SystemSetting::get('passport_verification_price', 500),
            'voters_card_verification' => \App\Models\SystemSetting::get('voters_card_verification_price', 200),
            'plate_number_verification' => \App\Models\SystemSetting::get('plate_number_verification_price', 200),
            'address_verification' => \App\Models\SystemSetting::get('address_verification_price', 1000),
            'nin_face_verification' => \App\Models\SystemSetting::get('nin_face_verification_price', 500),
            'credit_bureau_advance' => \App\Models\SystemSetting::get('credit_bureau_price', 1000),
            'biometric_verification' => \App\Models\SystemSetting::get('biometric_verification_price', 500),
            'validation' => $vPrice->validation_price ?? 700,
            'clearance' => $vPrice->ipe_clearance_price ?? 400,
            'personalization' => $vPrice->personalization_price ?? 100,
        ];

        // Ensure we group all address verifications pending to show
        $pendingAddresses = Transaction::where('user_email', Auth::user()->email)
            ->where('order_type', 'like', '%Address Verification%')
            ->latest()
            ->take(10)
            ->get();

        $activeTab = 'drivers_license';
        if ($request->routeIs('services.cac_verify')) $activeTab = 'cac_verification';
        if ($request->routeIs('services.tin_verify')) $activeTab = 'tin_verification';
        if ($request->routeIs('services.passport')) $activeTab = 'passport_verification';
        if ($request->routeIs('services.voters_card')) $activeTab = 'voters_card_verification';
        if ($request->routeIs('services.plate_number')) $activeTab = 'plate_number_verification';
        if ($request->routeIs('services.address_verify')) $activeTab = 'address_verification';
        if ($request->routeIs('services.nin_face')) $activeTab = 'nin_face_verification';
        if ($request->routeIs('services.credit_bureau')) $activeTab = 'credit_bureau_advance';
        if ($request->routeIs('services.biometric')) $activeTab = 'biometric_verification';
        if ($request->routeIs('services.validation')) $activeTab = 'validation';
        if ($request->routeIs('services.clearance')) $activeTab = 'clearance';
        if ($request->routeIs('services.personalization')) $activeTab = 'personalization';

        // Override with query param if present
        if ($request->has('service')) {
            $activeTab = $request->input('service');
        }

        return view('services.identity.hub.index', compact('providers', 'history', 'prices', 'activeTab', 'pendingAddresses'));
    }

    /**
     * Display the Driver's License Index Page
     */
    public function driversLicenseIndex()
    {
        $dlProviders = CustomApi::where('service_type', 'drivers_license')
                            ->where('status', true)
                            ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
                        ->where('service_type', 'drivers_license')
                        ->latest()
                        ->get();

        $price = \App\Models\SystemSetting::get('drivers_license_price', 300);

        return view('services.identity.drivers_license', compact('dlProviders', 'myResults', 'price'));
    }

    /**
     * Handle Driver's License Verification
     */
    public function verifyDriversLicense(Request $request)
    {
        $request->validate([
            'license_no' => ['required', 'string'],
            'dob' => ['required', 'string'],
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
            'verification_type' => ['nullable', 'string', 'max:80']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('drivers_license_price', 300);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'drivers_license')->where('status', true)->first();
        }

        if (!$provider || !$provider->status || $provider->service_type !== 'drivers_license') {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Driver\'s License verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $typeKey = $request->input('verification_type');
        $type = null;
        if ($typeKey) {
            $type = $provider->verificationTypes()->where('status', true)->where('type_key', $typeKey)->first();
            if (!$type) {
                return response()->json(['status' => false, 'message' => 'Invalid verification type selected.'], 422);
            }
            $price = (float) $type->price;
        } else {
            $price = (float) ($provider->price ?? $price);
        }
        $payload = [];

        // Detect Provider and Setup Payload/Endpoint
        if (str_contains($endpoint, 'verifyme.ng')) {
            $endpoint = rtrim($endpoint, '/') . '/' . $request->license_no;
            $payload = [
                'dob' => $request->dob,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
            ];
        } elseif (str_contains($endpoint, 'myidentitypay.com')) {
            $payload = [
                'number' => $request->license_no,
                'dob' => $request->dob,
            ];
        }
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Driver License Verification', 'DL');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $timeout = (int) ($provider->timeout_seconds ?? 45);
            $retryCount = (int) ($provider->retry_count ?? 0);
            $retryDelayMs = (int) ($provider->retry_delay_ms ?? 0);

            $http = Http::timeout($timeout);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            if ($type && is_array($type->meta)) {
                $payload = array_merge($payload, (array) ($type->meta['payload'] ?? []));
                $extraHeaders = (array) ($type->meta['headers'] ?? []);
                if (!empty($extraHeaders)) {
                    $http = $http->withHeaders($extraHeaders);
                }
                $pathSuffix = (string) ($type->meta['path_suffix'] ?? '');
                if ($pathSuffix !== '') {
                    $endpoint = rtrim($endpoint, '/') . '/' . ltrim($pathSuffix, '/');
                }
                $query = (array) ($type->meta['query'] ?? []);
                if (!empty($query)) {
                    $endpoint = $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . http_build_query($query);
                }
            }

            $response = null;
            for ($attempt = 0; $attempt <= $retryCount; $attempt++) {
                $response = $http->post($endpoint, $payload);
                if ($response->successful()) {
                    break;
                }
                if ($attempt < $retryCount && $retryDelayMs > 0) {
                    usleep($retryDelayMs * 1000);
                }
            }

            if ($response->successful()) {
                $resData = $response->json();
                $unifiedData = null;

                // Map VerifyMe Response
                if (isset($resData['status']) && $resData['status'] === 'success') {
                    $unifiedData = $resData['data'];
                } 
                // Map IdentityPay Response
                elseif (isset($resData['status']) && $resData['status'] === true && isset($resData['frsc_data'])) {
                    $frsc = $resData['frsc_data'];
                    $unifiedData = [
                        'firstname' => $frsc['firstName'] ?? '',
                        'lastname' => $frsc['lastName'] ?? '',
                        'middlename' => $frsc['middleName'] ?? '',
                        'licenseNo' => $frsc['licenseNo'] ?? $request->license_no,
                        'gender' => $frsc['gender'] ?? '',
                        'expiryDate' => $frsc['expiryDate'] ?? '',
                        'stateOfIssue' => $frsc['stateOfIssue'] ?? '',
                        'birthdate' => $frsc['birthDate'] ?? '',
                        'photo' => $frsc['photo'] ?? null,
                    ];
                }

                if ($unifiedData) {
                    $unifiedData['_meta'] = [
                        'provider_id' => $provider->id,
                        'verification_type' => $type?->type_key,
                        'verification_type_label' => $type?->label,
                        'price' => (float) $price,
                    ];
                    $result = $this->storeResult($user, 'drivers_license', $request->license_no, $provider->name, $unifiedData);
                    $wallet->markTransactionSuccess($debit['txId']);

                    return response()->json([
                        'status' => true,
                        'message' => 'Verification Successful',
                        'data' => $unifiedData,
                        'result_id' => $result->id
                    ]);
                } else {
                    throw new \Exception('Verification Failed: Data parsing error or provider mismatch.');
                }
            } else {
                throw new \Exception('Verification Failed: ' . ($response['message'] ?? $response['detail'] ?? 'Provider Error'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Driver License Verification', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the Biometric Verification Index Page
     */
    public function biometricIndex()
    {
        $bioProviders = CustomApi::where('service_type', 'biometric_verification')
                             ->where('status', true)
                             ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
            ->where('service_type', 'biometric_verification')
            ->latest()
            ->get();

        return view('services.identity.biometric', compact('bioProviders', 'myResults'));
    }

    /**
     * Handle Biometric Identity Verification
     */
    public function verifyBiometric(Request $request)
    {
        $request->validate([
            'id_number' => ['required', 'string'],
            'id_type' => ['required', 'string', 'in:nin,bvn,frsc'],
            'photo' => ['required', 'image', 'max:1024'], // Max 1MB
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('biometric_verification_price', 1000);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'biometric_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Biometric verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Biometric Identity Verification', 'BIO');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            // Using multipart for photo upload
            $file = $request->file('photo');
            $response = $http->attach(
                'photo', file_get_contents($file->path()), $file->getClientOriginalName()
            )->post($endpoint, [
                'idNumber' => $request->id_number,
                'idType' => $request->id_type,
            ]);

            if ($response->successful() && isset($response['status']) && $response['status'] === 'success') {
                $data = $response->json()['data'];
                $result = $this->storeResult($user, 'biometric_verification', $request->id_number, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Biometric Verification Successful',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                $errorMessage = $response['message'] ?? 'Identity not found or biometric mismatch.';
                throw new \Exception('Biometric Search Failed: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Biometric Identity Verification', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the Stamp Duty Verification Index Page
     */
    public function stampDutyIndex()
    {
        $stampProviders = CustomApi::where('service_type', 'stamp_duty')
                              ->where('status', true)
                              ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
            ->where('service_type', 'stamp_duty')
            ->latest()
            ->get();

        return view('services.identity.stamp_duty', compact('stampProviders', 'myResults'));
    }

    /**
     * Handle Stamp Duty Verification
     */
    public function verifyStampDuty(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'customer_name' => ['required', 'string'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('stamp_duty_verification_price', 200);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'stamp_duty')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Stamp Duty verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Stamp Duty Verification', 'STAMP');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        $customerReference = 'SD-' . time() . '-' . rand(1000, 9999);

        try {
            $http = Http::timeout(45);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->post($endpoint, [
                'number' => $request->number,
                'customer_name' => $request->customer_name,
                'customer_reference' => $customerReference,
            ]);

            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                $data = $response->json()['data'];
                $result = $this->storeResult($user, 'stamp_duty', $request->number, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Verification Successful',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['detail'] ?? 'Provider Error'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Stamp Duty Verification', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the Plate Number Verification Index Page
     */
    public function plateNumberIndex()
    {
        $plateProviders = CustomApi::where('service_type', 'plate_number_verification')
                               ->where('status', true)
                               ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
            ->where('service_type', 'plate_number_verification')
            ->latest()
            ->get();

        return view('services.identity.plate_number', compact('plateProviders', 'myResults'));
    }

    /**
     * Handle Plate Number Verification
     */
    public function verifyPlateNumber(Request $request)
    {
        $request->validate([
            'vehicle_number' => ['required', 'string'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('plate_number_verification_price', 200);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'plate_number_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Plate Number verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Plate Number Verification', 'PLATE');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $http = Http::timeout(45);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->post($endpoint, [
                'vehicle_number' => $request->vehicle_number,
            ]);

            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                $data = $response->json()['data'];
                $result = $this->storeResult($user, 'plate_number_verification', $request->vehicle_number, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => $response->json()['message'] ?? 'Verification Successful',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['message'] ?? 'Provider Error'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Plate Number Verification', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the CAC Verification Index Page
     */
    public function cacIndex()
    {
        $cacProviders = CustomApi::where('service_type', 'cac_verification')
                             ->where('status', true)
                             ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
                        ->where('service_type', 'cac_verification')
                        ->latest()
                        ->get();

        $price = \App\Models\SystemSetting::get('cac_verification_price', 500);

        return view('services.identity.cac', compact('cacProviders', 'myResults', 'price'));
    }

    /**
     * Handle CAC Verification
     */
    public function verifyCac(Request $request)
    {
        $request->validate([
            'rc_number' => ['required', 'string'],
            'company_type' => ['nullable', 'string', 'in:BN,RC,IT,LL,LLP'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
            'verification_type' => ['nullable', 'string', 'max:80']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('cac_verification_price', 500);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'cac_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status || $provider->service_type !== 'cac_verification') {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for CAC verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $typeKey = $request->input('verification_type');
        $type = null;
        if ($typeKey) {
            $type = $provider->verificationTypes()->where('status', true)->where('type_key', $typeKey)->first();
            if (!$type) {
                return response()->json(['status' => false, 'message' => 'Invalid verification type selected.'], 422);
            }
            $price = (float) $type->price;
        } else {
            $price = (float) ($provider->price ?? $price);
        }

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'CAC Business Verification', 'CAC');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $timeout = (int) ($provider->timeout_seconds ?? 60);
            $retryCount = (int) ($provider->retry_count ?? 0);
            $retryDelayMs = (int) ($provider->retry_delay_ms ?? 0);

            $http = Http::timeout($timeout);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $payload = [
                'rc_number' => $request->rc_number,
                'company_type' => $request->company_type ?? 'RC',
            ];

            if ($type && is_array($type->meta)) {
                $payload = array_merge($payload, (array) ($type->meta['payload'] ?? []));
                $extraHeaders = (array) ($type->meta['headers'] ?? []);
                if (!empty($extraHeaders)) {
                    $http = $http->withHeaders($extraHeaders);
                }
                $pathSuffix = (string) ($type->meta['path_suffix'] ?? '');
                if ($pathSuffix !== '') {
                    $endpoint = rtrim($endpoint, '/') . '/' . ltrim($pathSuffix, '/');
                }
                $query = (array) ($type->meta['query'] ?? []);
                if (!empty($query)) {
                    $endpoint = $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . http_build_query($query);
                }
            }

            $response = null;
            for ($attempt = 0; $attempt <= $retryCount; $attempt++) {
                $response = $http->post($endpoint, $payload);
                if ($response->successful()) {
                    break;
                }
                if ($attempt < $retryCount && $retryDelayMs > 0) {
                    usleep($retryDelayMs * 1000);
                }
            }

            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                $data = $response->json()['data'];
                if (is_array($data)) {
                    $data['_meta'] = [
                        'provider_id' => $provider->id,
                        'verification_type' => $type?->type_key,
                        'verification_type_label' => $type?->label,
                        'price' => (float) $price,
                    ];
                }
                $result = $this->storeResult($user, 'cac_verification', $request->rc_number, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'CAC Record Verified',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['message'] ?? $response['detail'] ?? 'Company details not found.'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'CAC Business Verification', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the TIN Verification Index Page
     */
    public function tinIndex()
    {
        $tinProviders = CustomApi::where('service_type', 'tin_verification')
                             ->where('status', true)
                             ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
                        ->where('service_type', 'tin_verification')
                        ->latest()
                        ->get();

        return view('services.identity.tin', compact('tinProviders', 'myResults'));
    }

    /**
     * Handle TIN Verification
     */
    public function verifyTin(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'channel' => ['required', 'string', 'in:TIN,CAC,Phone'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('tin_verification_price', 200);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'tin_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for TIN verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'TIN Identification Verification', 'TIN');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $http = Http::timeout(45);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->post($endpoint, [
                'number' => $request->number,
                'channel' => $request->channel,
            ]);

            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                $data = $response->json()['data'];
                $result = $this->storeResult($user, 'tin_verification', $request->number, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'TIN Record Verified',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['message'] ?? $response['detail'] ?? 'TIN details not found.'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'TIN Identification Verification', $debit['txId']);
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the NIN with Face Verification Index Page
     */
    public function ninFaceIndex()
    {
        $faceProviders = CustomApi::where('service_type', 'nin_face_verification')
                             ->where('status', true)
                             ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
            ->where('service_type', 'nin_face_verification')
            ->latest()
            ->get();

        return view('services.identity.nin_face', compact('faceProviders', 'myResults'));
    }

    /**
     * Handle NIN with Face Verification
     */
    public function verifyNinFace(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'image' => ['required', 'string'], // Expecting URL or base64
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('nin_face_verification_price', 500);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'nin_face_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for NIN Face verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'NIN with Face Verification', 'NINFACE');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->post($endpoint, [
                'number' => $request->number,
                'image' => $request->image,
            ]);

            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                $data = $response->json();

                $result = $this->storeResult($user, 'nin_face_verification', $request->number, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => $data['detail'] ?? 'Verification Successful',
                    'face_data' => $data['face_data'] ?? null,
                    'nin_data' => $data['nin_data'] ?? null,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['detail'] ?? $response['message'] ?? 'Identity not found or face mismatch.'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'NIN with Face Verification', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the Credit Bureau Verification Index Page
     */
    public function creditBureauIndex()
    {
        $bureauProviders = CustomApi::where('service_type', 'credit_bureau_advance')
                                ->where('status', true)
                                ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
            ->where('service_type', 'credit_bureau_advance')
            ->latest()
            ->get();

        return view('services.identity.credit_bureau', compact('bureauProviders', 'myResults'));
    }

    /**
     * Handle Credit Bureau Verification
     */
    public function verifyCreditBureau(Request $request)
    {
        $request->validate([
            'mode' => ['required', 'string', 'in:ID,BIO'],
            'number' => ['required_if:mode,ID', 'nullable', 'string'],
            'dob' => ['required_if:mode,BIO', 'nullable', 'string'],
            'customer_name' => ['required', 'string'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('credit_bureau_price', 1000);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'credit_bureau_advance')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Credit Bureau verification.');
        }

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Credit Bureau Advance Report', 'CREDIT');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        $customerRef = 'REF-' . strtoupper(bin2hex(random_bytes(5)));

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $payload = [
                'mode' => $request->mode,
                'customer_name' => $request->customer_name,
                'customer_reference' => $customerRef,
            ];

            if ($request->mode === 'ID') {
                $payload['number'] = $request->number;
            } else {
                $payload['dob'] = $request->dob;
            }

            $response = $http->post($endpoint, $payload);

            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                $data = $response->json()['data'];

                $identifier = $request->mode === 'ID' ? ($request->number ?? '') : ($request->dob ?? '');
                $result = $this->storeResult($user, 'credit_bureau_advance', $identifier, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Credit Check Successful',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['detail'] ?? $response['message'] ?? 'Unable to retrieve credit records.'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Credit Bureau Advance Report', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the International Passport Verification Index Page
     */
    public function passportIndex()
    {
        $passportProviders = CustomApi::where('service_type', 'passport_verification')
                                 ->where('status', true)
                                 ->get();

        $history = VerificationResult::where('user_id', Auth::id())
                        ->where('service_type', 'passport_verification')
                        ->latest()
                        ->get();

        return view('services.identity.passport', compact('passportProviders', 'history'));
    }

    /**
     * Display the Validation Index Page
     */
    public function validationIndex()
    {
        $history = VerificationResult::where('user_id', Auth::id())
                        ->whereIn('service_type', ['validation', 'nin_validation'])
                        ->latest()
                        ->get();

        $price = VerificationPrice::first()->validation_price ?? 700;
        $validationMode = \App\Models\SystemSetting::get('nin_validation_mode', 'auto');
        $activeProvider = $this->preferredValidationProvider();
        if ($validationMode === 'manual') {
            $providerLabel = 'Manual Reporting';
        } elseif (in_array($validationMode, ['auto', 'api'], true)) {
            $providerLabel = 'Auto (' . ($activeProvider?->name ?: 'Smart API Failover') . ')';
        } else {
            $providerLabel = $activeProvider?->name ?: (ucfirst($validationMode) . ' API');
        }

        return view('services.identity.validation', compact('history', 'price', 'providerLabel', 'validationMode'));
    }

    /**
     * Handle Validation Verification (Manual Admin Review or Robosttech API)
     */
    public function verifyValidation(Request $request)
    {
        $expectsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson();

        $request->validate([
            'number' => ['required', 'string', 'max:120'],
            'mode' => ['nullable', 'string', 'in:submit,status'],
            'validation_reason' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $mode = $request->input('mode', 'submit');
        $user = Auth::user();
        $ninNumber = trim((string) $request->number);
        $reason = trim((string) $request->input('validation_reason', 'NIN Validation'));
        $validationMode = \App\Models\SystemSetting::get('nin_validation_mode', 'auto');

        // Status check mode
        if ($mode === 'status') {
            $existing = VerificationResult::where(function ($query) use ($ninNumber) {
                    $query->where('identifier', $ninNumber)
                          ->orWhere('reference_id', $ninNumber)
                          ->orWhere('response_data->nin', $ninNumber);
                })
                ->whereIn('service_type', ['validation', 'nin_validation'])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhereNull('user_id');
                })
                ->latest()
                ->first();

            $providerIdent = strtolower((string) ($existing?->provider_name ?? ''));
            $exData = $existing?->response_data ?? [];

            $dvProvider = CustomApi::where('provider_identifier', 'dataverify')->where('status', true)->first();
            $robostProvider = CustomApi::where('provider_identifier', 'robosttech')->where('status', true)->first();
            $hasDv = $this->hasDataVerifyCredentials($dvProvider);
            $hasRobost = $this->hasRobosttechCredentials($robostProvider);

            $isManualMode = ($validationMode === 'manual');
            $hasApiEngine = ($hasDv || $hasRobost);
            $recordIsExplicitlyManual = $existing && in_array($existing->provider_name, ['ADMIN_MANUAL', 'Manual Reporting', 'Manual Vetting', 'Manual Admin Review'], true);

            // If system is explicitly in manual mode, OR if no API credentials exist and record was created for manual vetting
            $isManualRecord = $isManualMode || (!$hasApiEngine && $recordIsExplicitlyManual);

            if ($isManualRecord) {
                if (!$existing) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No validation request found with NIN/Reference: ' . $ninNumber . '. Please check the number or submit a new request.'
                    ]);
                }

                $readableStatus = match($existing->status) {
                    'successful' => 'Validated / Successful',
                    'failed' => 'Rejected / Failed',
                    default => 'Under Review / In Progress'
                };

                return response()->json([
                    'status' => true,
                    'message' => 'Validation status retrieved.',
                    'data' => [
                        'nin' => $existing->identifier,
                        'reference_id' => $existing->reference_id,
                        'status' => $existing->status,
                        'status_label' => $readableStatus,
                        'can_print_slip' => ($existing->status === 'successful'),
                        'slip_page_url' => route('services.nin', ['nin' => $existing->identifier]),
                        'validation_reason' => $exData['validation_reason'] ?? 'NIN Validation',
                        'admin_note' => $existing->admin_note ?: 'Your application is currently being reviewed by the admin team.',
                        'submitted_at' => $existing->created_at->format('M d, Y H:i:s'),
                        'updated_at' => $existing->updated_at->format('M d, Y H:i:s'),
                        'details' => $exData,
                    ],
                    'result_id' => $existing->id
                ]);
            }

            $dvProvider = CustomApi::where('provider_identifier', 'dataverify')->where('status', true)->first();
            $robostProvider = CustomApi::where('provider_identifier', 'robosttech')->where('status', true)->first();
            $hasDv = $this->hasDataVerifyCredentials($dvProvider);
            $hasRobost = $this->hasRobosttechCredentials($robostProvider);

            $shouldCheckDataVerify = str_contains($providerIdent, 'dataverify') 
                || !empty($exData['transaction_id']) 
                || !empty($exData['api_response']['transaction_id'])
                || ($hasDv && !str_contains($providerIdent, 'robosttech'));

            // 1. DataVerify Provider Status Check
            if ($existing && $shouldCheckDataVerify && $hasDv) {
                try {
                    $dvClient = new \App\Services\DataVerify\DataVerifyClient($dvProvider);
                    $txId = $exData['transaction_id'] ?? $exData['api_response']['transaction_id'] ?? null;
                    $ninToQuery = $existing->identifier ?: ($exData['nin'] ?? $ninNumber);

                    $statusRes = $dvClient->checkNinValidationStatus($ninToQuery, $txId);

                    if ($statusRes['ok'] || !empty($statusRes['data'])) {
                        $exData['dataverify_last_status'] = $statusRes['data'];
                        if ($statusRes['status'] === 'successful') {
                            $existing->status = 'successful';
                            $existing->admin_note = $statusRes['message'] ?: 'Record found and validated successfully on NIMC database.';
                        } elseif ($statusRes['status'] === 'failed') {
                            $existing->status = 'failed';
                            $existing->admin_note = $statusRes['error_detail'] ?: $statusRes['message'] ?: 'Validation Failed on NIMC Database.';
                            if (empty($exData['refunded'])) {
                                $refundAmount = (float) ($exData['amount_paid'] ?? VerificationPrice::first()->validation_price ?? 700);
                                $wallet = app(WalletService::class);
                                $ref = $wallet->failAndRefund($user, $refundAmount, 'NIN Validation Failed: ' . $existing->admin_note, $existing->reference_id);
                                if ($ref['ok'] ?? false) {
                                    $exData['refunded'] = true;
                                    $exData['refunded_amount'] = $refundAmount;
                                    $exData['refunded_at'] = now()->toDateTimeString();
                                }
                            }
                        }
                        $existing->response_data = $exData;
                        $existing->save();
                    }
                } catch (\Throwable $e) {
                    Log::warning('DataVerify validation status check error: ' . $e->getMessage());
                }
            }
            // 2. Robosttech Provider Status Check
            elseif ($existing && (str_contains($providerIdent, 'robosttech') || (!$shouldCheckDataVerify && $hasRobost)) && in_array($existing->status, ['pending', 'waiting_for_review'])) {
                if ($hasRobost) {
                    try {
                        $apiCenter = ApiCenter::first();
                        $robostKey = trim((string) (
                            $robostProvider?->api_key 
                            ?: $apiCenter?->robosttech_api_key 
                            ?: env('ROBOSTTECH_API_KEY') 
                            ?: \App\Models\SystemSetting::get('robosttech_api_key', '')
                        ));
                        $endpoint = $this->resolveRobostEndpoint(
                            (string) ($robostProvider?->endpoint ?: $apiCenter?->robosttech_endpoint_validation ?: 'https://robosttech.com/api'),
                            'validation_status'
                        );
                        $ninToQuery = $existing ? ($exData['nin'] ?? $existing->identifier) : $ninNumber;
                        $apiRes = Http::timeout(25)->withHeaders([
                            'api-key' => $robostKey,
                            'Content-Type' => 'application/json'
                        ])->post($endpoint, [
                            'nin' => $ninToQuery,
                            'number' => $ninToQuery,
                        ]);

                        if ($apiRes->successful()) {
                            $apiData = $apiRes->json();
                            $apiStatus = strtolower((string) ($apiData['status'] ?? $apiData['data']['status'] ?? ''));
                            $isSuccess = ($apiData['success'] ?? false) === true || in_array($apiStatus, ['successful', 'success', 'cleared', 'completed', 'valid', 'true', '1']);
                            $isInProgress = ($apiData['in-progress'] ?? $apiData['in_progress'] ?? false) === true || in_array($apiStatus, ['sent', 'uploaded', 'pending', 'processing']);
                            $isFailed = ($apiData['success'] ?? true) === false && !$isInProgress && in_array($apiStatus, ['failed', 'rejected', 'error', 'invalid', 'false', '0']);

                            if ($existing) {
                                $exData['robosttech_last_status'] = $apiData;
                                if ($isSuccess) {
                                    $existing->status = 'successful';
                                    $existing->admin_note = $apiData['message'] ?? 'Validated via Robosttech API.';
                                } elseif ($isFailed) {
                                    $existing->status = 'failed';
                                    $existing->admin_note = $apiData['message'] ?? 'Rejected via Robosttech API.';
                                    if (empty($exData['refunded'])) {
                                        $refundAmount = (float) ($exData['amount_paid'] ?? VerificationPrice::first()->validation_price ?? 700);
                                        $wallet = app(WalletService::class);
                                        $ref = $wallet->failAndRefund($user, $refundAmount, 'NIN Validation Rejected (API): ' . $existing->admin_note, $existing->reference_id);
                                        if ($ref['ok'] ?? false) {
                                            $exData['refunded'] = true;
                                            $exData['refunded_amount'] = $refundAmount;
                                            $exData['refunded_at'] = now()->toDateTimeString();
                                        }
                                    }
                                }
                                $existing->response_data = $exData;
                                $existing->save();
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Robosttech validation status check error: ' . $e->getMessage());
                    }
                }
            }

            if (!$existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'No validation request found with NIN/Reference: ' . $ninNumber . '. Please check the number or submit a new request.'
                ]);
            }

            $readableStatus = match($existing->status) {
                'successful' => 'Validated / Successful',
                'failed' => 'Rejected / Failed',
                default => 'Under Review / In Progress'
            };

            $existingData = $existing->response_data ?? [];

            return response()->json([
                'status' => true,
                'message' => 'Validation status retrieved.',
                'data' => [
                    'nin' => $existing->identifier,
                    'reference_id' => $existing->reference_id,
                    'status' => $existing->status,
                    'status_label' => $readableStatus,
                    'can_print_slip' => ($existing->status === 'successful'),
                    'slip_page_url' => route('services.nin', ['nin' => $existing->identifier]),
                    'validation_reason' => $existingData['validation_reason'] ?? 'NIN Validation',
                    'admin_note' => $existing->admin_note ?: 'Your application is currently being processed by the validation provider. Check back in less than 24 hours.',
                    'submitted_at' => $existing->created_at->format('M d, Y H:i:s'),
                    'updated_at' => $existing->updated_at->format('M d, Y H:i:s'),
                    'details' => $existingData,
                ],
                'result_id' => $existing->id
            ]);
        }

        // Mode: Submit new validation request
        $defaultValPrice = (float) (VerificationPrice::first()->validation_price ?? 500);
        $price = $user ? (float) $user->getDeveloperApiPrice('developer_api_nin_validation_price', $defaultValPrice) : $defaultValPrice;
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Document Validation (' . $ninNumber . ')', 'VAL');
        if (!$debit['ok']) {
            if ($expectsJson) {
                return response()->json(['status' => false, 'message' => $debit['message']]);
            }
            return back()->withErrors(['number' => $debit['message']])->withInput();
        }

        try {
            $referenceId = 'VAL-' . strtoupper(bin2hex(random_bytes(4)));

            if (in_array($validationMode, ['robosttech', 'api', 'dataverify', 'auto'], true)) {
                // Map reason to official validation categories
                $normalizedValidationType = match(strtolower($reason)) {
                    'sim_validation', 'sim', 'sim card', 'sim validation', 'sim card validation' => 'sim_validation',
                    'bank_validation', 'bank', 'bvn', 'bvn validation', 'bank validation' => 'bank_validation',
                    'modification_validation', 'modification', 'data modification', 'name modification' => 'modification_validation',
                    'photographic_error', 'photo', 'photo error', 'photographic error' => 'photographic_error',
                    default => 'no_record_found'
                };

                $dvProvider = CustomApi::query()->whereIn('service_type', ['validation', 'nin_validation', 'nin_verification'])->where('status', true)->where(function ($q) {
                    $q->where('provider_identifier', 'dataverify')->orWhere('endpoint', 'like', '%dataverify%');
                })->first();

                $robostProvider = CustomApi::query()->whereIn('service_type', ['validation', 'nin_validation', 'nin_verification'])->where('status', true)->where(function ($q) {
                    $q->where('provider_identifier', 'robosttech')->orWhere('endpoint', 'like', '%robosttech%');
                })->first();

                $hasDv = $this->hasDataVerifyCredentials($dvProvider);
                $hasRobost = $this->hasRobosttechCredentials($robostProvider);

                $executeDataVerify = function () use ($dvProvider, $ninNumber, $normalizedValidationType, $reason, $user, $price, $request) {
                    $client = new \App\Services\DataVerify\DataVerifyClient($dvProvider);
                    $subRes = $client->submitNinValidation($ninNumber, $normalizedValidationType);
                    if (!$subRes['ok']) {
                        return [
                            'ok' => false,
                            'message' => $subRes['message'] ?: 'DataVerify validation submission failed.',
                            'data' => $subRes['data'] ?? [],
                        ];
                    }
                    $data = $subRes['data'];
                    $providerName = $dvProvider?->name ?: 'DataVerify';
                    return [
                        'ok' => true,
                        'provider_name' => $providerName,
                        'status' => 'waiting_for_review',
                        'admin_note' => $data['message'] ?? 'Submitted to DataVerify validation team (processing).',
                        'data' => $data,
                        'response_data' => [
                            'nin' => $ninNumber,
                            'validation_reason' => $reason,
                            'validation_type' => $normalizedValidationType,
                            'transaction_id' => $subRes['transaction_id'] ?? null,
                            'user_name' => $user->fullname,
                            'user_email' => $user->email,
                            'user_phone' => $user->phone,
                            'remarks' => $request->input('remarks'),
                            'service_mode' => 'DataVerify API',
                            'amount_paid' => (float) $price,
                            'api_response' => $data,
                        ],
                    ];
                };

                $executeRobosttech = function () use ($robostProvider, $ninNumber, $reason, $user, $price, $request) {
                    $apiCenter = ApiCenter::first();
                    $robostKey = trim((string) (
                        $robostProvider?->api_key 
                        ?: $apiCenter?->robosttech_api_key 
                        ?: env('ROBOSTTECH_API_KEY') 
                        ?: \App\Models\SystemSetting::get('robosttech_api_key', '')
                    ));
                    if ($robostKey === '') {
                        return [
                            'ok' => false,
                            'message' => 'Robosttech API credentials not configured in Admin Settings.',
                            'data' => [],
                        ];
                    }

                    $providerName = $robostProvider?->name ?: 'Robosttech';
                    $endpoint = $this->resolveRobostEndpoint(
                        (string) ($robostProvider?->endpoint ?: $apiCenter?->robosttech_endpoint_validation ?: 'https://robosttech.com/api'),
                        'validation'
                    );

                    $headers = [
                        'api-key' => $robostKey,
                        'Content-Type' => 'application/json'
                    ];
                    $payload = [
                        'nin' => $ninNumber,
                        'number' => $ninNumber,
                        'validation_reason' => $reason,
                    ];

                    $response = Http::timeout(45)->withHeaders($headers)->post($endpoint, $payload);
                    $data = $response->json();
                    if (!$response->successful() || (is_array($data) && isset($data['success']) && $data['success'] === false)) {
                        $msg = (is_array($data) ? ($data['message'] ?? $data['detail'] ?? null) : null)
                            ?: ('Robosttech validation failed (HTTP ' . $response->status() . ')');
                        return [
                            'ok' => false,
                            'message' => $msg,
                            'data' => is_array($data) ? $data : [],
                        ];
                    }

                    $apiStatus = strtolower((string) ($data['status'] ?? $data['data']['status'] ?? 'waiting_for_review'));
                    $isSuccess = in_array($apiStatus, ['validated', 'completed', 'valid']) || !empty($data['nin_data']);
                    $initialStatus = $isSuccess ? 'successful' : 'waiting_for_review';

                    return [
                        'ok' => true,
                        'provider_name' => $providerName,
                        'status' => $initialStatus,
                        'admin_note' => $data['message'] ?? 'Submitted to validation API.',
                        'data' => $data,
                        'response_data' => [
                            'nin' => $ninNumber,
                            'validation_reason' => $reason,
                            'user_name' => $user->fullname,
                            'user_email' => $user->email,
                            'user_phone' => $user->phone,
                            'remarks' => $request->input('remarks'),
                            'service_mode' => 'Robosttech API',
                            'amount_paid' => (float) $price,
                            'api_response' => $data,
                        ],
                    ];
                };

                // Determine primary engine based on configuration and active credentials
                $primary = 'dataverify';
                if ($validationMode === 'robosttech') {
                    $primary = $hasRobost ? 'robosttech' : ($hasDv ? 'dataverify' : 'robosttech');
                } elseif ($validationMode === 'dataverify') {
                    $primary = $hasDv ? 'dataverify' : ($hasRobost ? 'robosttech' : 'dataverify');
                } else { // auto or api
                    if ($hasDv && !$hasRobost) {
                        $primary = 'dataverify';
                    } elseif ($hasRobost && !$hasDv) {
                        $primary = 'robosttech';
                    } else {
                        $primary = 'dataverify';
                    }
                }

                $attempt = null;
                if ($primary === 'dataverify') {
                    if ($hasDv) {
                        $attempt = $executeDataVerify();
                    }
                    if ((!$attempt || !$attempt['ok']) && $hasRobost) {
                        Log::warning('DataVerify validation submission unsuccessful or missing key; failing over to Robosttech', [
                            'error' => $attempt['message'] ?? 'No credentials',
                            'nin' => $ninNumber,
                        ]);
                        $attempt = $executeRobosttech();
                    }
                } else { // primary === 'robosttech'
                    if ($hasRobost) {
                        $attempt = $executeRobosttech();
                    }
                    if ((!$attempt || !$attempt['ok']) && $hasDv) {
                        Log::warning('Robosttech validation submission unsuccessful or missing key; failing over to DataVerify', [
                            'error' => $attempt['message'] ?? 'No credentials',
                            'nin' => $ninNumber,
                        ]);
                        $attempt = $executeDataVerify();
                    }
                }

                if (!$attempt || !$attempt['ok']) {
                    $errorMsg = $attempt['message'] ?? 'Validation API credentials not configured in Admin Settings. Please configure DataVerify or Robosttech credentials.';
                    throw new \Exception($errorMsg);
                }

                $providerName = $attempt['provider_name'];
                $initialStatus = $attempt['status'];
                $resPayload = $attempt['response_data'];
                $data = $attempt['data'];

                $result = VerificationResult::create([
                    'user_id' => $user->id,
                    'service_type' => 'validation',
                    'identifier' => $ninNumber,
                    'provider_name' => $providerName,
                    'status' => $initialStatus,
                    'reference_id' => $referenceId,
                    'admin_note' => $attempt['admin_note'],
                    'response_data' => $resPayload,
                ]);

                $wallet->markTransactionSuccess($debit['txId']);

                if ($expectsJson) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Validation request submitted successfully to ' . $providerName . ' queue.',
                        'provider' => $providerName,
                        'data' => [
                            'nin' => $ninNumber,
                            'reference_id' => $referenceId,
                            'status' => $initialStatus,
                            'status_label' => $initialStatus === 'successful' ? 'Validated / Successful' : 'Processing via Provider Queue',
                            'admin_note' => $attempt['admin_note'],
                        ],
                        'result_id' => $result->id,
                        'reference_id' => $result->reference_id,
                    ]);
                }

                return redirect()
                    ->route('services.validation')
                    ->with('status', 'Validation Submitted Successfully')
                    ->with('validation_provider', $providerName)
                    ->with('validation_result', $data)
                    ->with('validation_result_id', $result->id)
                    ->with('validation_reference_id', $result->reference_id);
            } else {
                // Manual Review Mode
                $resPayload = [
                    'nin' => $ninNumber,
                    'validation_reason' => $reason,
                    'user_name' => $user->fullname,
                    'user_email' => $user->email,
                    'user_phone' => $user->phone,
                    'remarks' => $request->input('remarks'),
                    'service_mode' => 'Manual Vetting',
                    'amount_paid' => (float) $price,
                ];

                $result = VerificationResult::create([
                    'user_id' => $user->id,
                    'service_type' => 'validation',
                    'identifier' => $ninNumber,
                    'provider_name' => 'ADMIN_MANUAL',
                    'status' => 'waiting_for_review',
                    'reference_id' => $referenceId,
                    'admin_note' => 'Awaiting admin vetting and validation. Results typically available in less than 24 hours.',
                    'response_data' => $resPayload,
                ]);

                $wallet->markTransactionSuccess($debit['txId']);

                if ($expectsJson) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Validation request submitted successfully. You will see results under Validation Vault in less than 24 hours.',
                        'provider' => 'ADMIN_MANUAL',
                        'data' => [
                            'nin' => $ninNumber,
                            'reference_id' => $referenceId,
                            'status' => 'waiting_for_review',
                            'status_label' => 'Under Review by Admin',
                            'admin_note' => 'Awaiting admin vetting and validation. Results typically available in less than 24 hours.',
                        ],
                        'result_id' => $result->id,
                        'reference_id' => $result->reference_id,
                    ]);
                }

                return redirect()
                    ->route('services.validation')
                    ->with('status', 'Validation request submitted successfully. Check Validation Vault in less than 24 hours.')
                    ->with('validation_provider', 'ADMIN_MANUAL')
                    ->with('validation_result_id', $result->id)
                    ->with('validation_reference_id', $result->reference_id);
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Document Validation Failure', $debit['txId']);
            if ($expectsJson) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to process validation: ' . $e->getMessage(),
                ]);
            }

            return back()->withErrors(['number' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the IPE Clearance Index Page
     */
    public function clearanceIndex()
    {
        $history = VerificationResult::where('user_id', Auth::id())
                        ->whereIn('service_type', ['clearance', 'ipe_clearance', 'clearance_request', 'clearance_status'])
                        ->latest()
                        ->get();

        $price = VerificationPrice::first()->ipe_clearance_price ?? 700;
        $ipeMode = \App\Models\SystemSetting::get('ipe_clearance_mode', 'robosttech');

        return view('services.identity.clearance', compact('history', 'price', 'ipeMode'));
    }

    /**
     * Handle IPE Clearance Submission & Status Query (Manual Admin Processing or Robosttech API)
     */
    public function verifyClearance(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:150'],
            'mode' => ['nullable', 'string', 'in:submit,status'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $mode = $request->input('mode', 'submit');
        $user = Auth::user();
        $trackingId = trim((string) ($request->input('tracking_id') ?: $request->input('number')));
        $category = trim((string) $request->input('category', 'Improcessing Error'));
        $ipeClearanceMode = \App\Models\SystemSetting::get('ipe_clearance_mode', 'robosttech');
        $wantsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson();

        // Status check mode: retrieve existing review record and optionally sync from API
        if ($mode === 'status') {
            $existing = VerificationResult::where(function ($query) use ($trackingId) {
                    $query->where('identifier', $trackingId)
                          ->orWhere('reference_id', $trackingId);
                })
                ->whereIn('service_type', ['ipe_clearance', 'clearance', 'clearance_request', 'clearance_status'])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhereNull('user_id');
                })
                ->latest()
                ->first();

            $providerNameLower = strtolower((string) ($existing?->provider_name ?? ''));
            $isDataVerify = ($ipeClearanceMode === 'dataverify' || str_contains($providerNameLower, 'dataverify'));
            $isRobost = ($ipeClearanceMode === 'robosttech' || str_contains($providerNameLower, 'robosttech'));

            // If DataVerify mode or existing request was submitted via DataVerify, check DataVerify status API
            if ($isDataVerify && ($existing ? in_array($existing->status, ['pending', 'waiting_for_review']) : true)) {
                try {
                    $dvClient = new \App\Services\DataVerify\DataVerifyClient();
                    $exData = $existing ? ($existing->response_data ?? []) : [];
                    $txId = $exData['transaction_id'] ?? null;
                    $dvRes = $dvClient->checkIpeClearanceStatus($trackingId, $txId);

                    if ($dvRes['ok'] ?? false) {
                        $apiData = $dvRes['data'] ?? [];
                        $newTid = \App\Models\VerificationResult::extractNewTrackingId($apiData, $trackingId)
                            ?: ($dvRes['new_tracking_id'] ?? null);

                        if ($existing) {
                            $exData['provider_last_status'] = $apiData;
                            if ($dvRes['status'] === 'successful') {
                                $existing->status = 'successful';
                                if ($newTid) {
                                    $exData['new_tracking_id'] = $newTid;
                                }
                                if (!empty($dvRes['new_nin'])) {
                                    $exData['new_nin'] = $dvRes['new_nin'];
                                }
                                $existing->admin_note = $dvRes['message'] ?: 'Cleared successfully via DataVerify verification.';
                            } elseif ($dvRes['status'] === 'failed') {
                                $existing->status = 'failed';
                                $existing->admin_note = $dvRes['message'] ?: 'Clearance rejected by DataVerify.';
                                if (empty($exData['refunded'])) {
                                    $refundAmount = (float) ($exData['amount_paid'] ?? VerificationPrice::first()->ipe_clearance_price ?? 700);
                                    $wallet = app(WalletService::class);
                                    $ref = $wallet->refund($user, $refundAmount, 'IPE Clearance Rejected: ' . $existing->admin_note, 'IPE-REFUND');
                                    if ($ref['ok'] ?? false) {
                                        $exData['refunded'] = true;
                                        $exData['refunded_amount'] = $refundAmount;
                                        $exData['refunded_at'] = now()->toDateTimeString();
                                    }
                                }
                            }
                            $existing->response_data = $exData;
                            $existing->save();
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('DataVerify clearance status check error: ' . $e->getMessage());
                }
            }
            // If Robosttech mode or existing request was submitted via Robosttech, check provider status API
            elseif ($isRobost && ($existing ? in_array($existing->status, ['pending', 'waiting_for_review']) : true)) {
                $apiCenter = ApiCenter::first();
                $customRobost = CustomApi::where('provider_identifier', 'robosttech')->where('status', true)->first();
                $apiKey = $customRobost?->api_key ?: $apiCenter?->robosttech_api_key ?: env('ROBOSTTECH_API_KEY') ?: \App\Models\SystemSetting::get('robosttech_api_key');

                if ($apiKey) {
                    try {
                        $endpoint = $this->resolveRobostEndpoint(
                            (string) ($apiCenter?->robosttech_endpoint_clearance_status ?: $customRobost?->endpoint ?: 'https://robosttech.com/api'),
                            'clearance_status'
                        );
                        $apiRes = Http::timeout(25)->withHeaders([
                            'api-key' => $apiKey,
                            'Content-Type' => 'application/json'
                        ])->post($endpoint, [
                            'tracking_id' => $trackingId,
                            'number' => $trackingId,
                        ]);

                        if ($apiRes->successful()) {
                            $apiData = $apiRes->json();
                            $apiStatus = strtolower((string) ($apiData['status'] ?? $apiData['data']['status'] ?? ''));
                            $newTid = \App\Models\VerificationResult::extractNewTrackingId($apiData, $trackingId);

                            if ($existing) {
                                $exData = $existing->response_data ?? [];
                                $exData['provider_last_status'] = $apiData;
                                if (in_array($apiStatus, ['successful', 'success', 'cleared', 'completed', 'true', '1'])) {
                                    $existing->status = 'successful';
                                    if ($newTid) {
                                        $exData['new_tracking_id'] = $newTid;
                                    }
                                    $existing->admin_note = $apiData['message'] ?? 'Cleared successfully via verification desk.';
                                } elseif (in_array($apiStatus, ['failed', 'rejected', 'error', 'false', '0'])) {
                                    $existing->status = 'failed';
                                    $existing->admin_note = $apiData['message'] ?? 'Clearance rejected during verification.';
                                    if (empty($exData['refunded'])) {
                                        $refundAmount = (float) ($exData['amount_paid'] ?? VerificationPrice::first()->ipe_clearance_price ?? 700);
                                        $wallet = app(WalletService::class);
                                        $ref = $wallet->refund($user, $refundAmount, 'IPE Clearance Rejected: ' . $existing->admin_note, 'IPE-REFUND');
                                        if ($ref['ok'] ?? false) {
                                            $exData['refunded'] = true;
                                            $exData['refunded_amount'] = $refundAmount;
                                            $exData['refunded_at'] = now()->toDateTimeString();
                                        }
                                    }
                                }
                                $existing->response_data = $exData;
                                $existing->save();
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Clearance status check error: ' . $e->getMessage());
                    }
                }
            }

            if (!$existing) {
                if ($wantsJson) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No clearance request found with Tracking ID: ' . $trackingId . '. Please verify the ID or submit a new request.'
                    ]);
                }
                return back()->with('error', 'No clearance request found with Tracking ID: ' . $trackingId . '. Please verify the ID or submit a new request.')->withInput();
            }

            $readableStatus = match($existing->status) {
                'successful' => 'Cleared / Successful',
                'failed' => 'Rejected / Failed',
                default => 'Processing Application'
            };

            $existingData = $existing->response_data ?? [];
            $resolvedNewTid = $existing->resolved_new_tracking_id ?? ($existingData['new_tracking_id'] ?? null);

            if (!$wantsJson) {
                return redirect()->route('services.clearance.report', $existing->id);
            }

            return response()->json([
                'status' => true,
                'message' => 'Clearance status retrieved.',
                'report_url' => route('services.clearance.report', $existing->id),
                'data' => [
                    'tracking_id' => $existing->identifier,
                    'new_tracking_id' => $resolvedNewTid,
                    'nin' => $existingData['nin'] ?? null,
                    'reference_id' => $existing->reference_id,
                    'status' => $existing->status,
                    'status_label' => $readableStatus,
                    'category' => $existingData['category'] ?? 'Improcessing Error',
                    'admin_note' => $existing->admin_note ?: 'Your application is currently being processed. Check back in less than 24 hours.',
                    'submitted_at' => $existing->created_at->format('M d, Y H:i:s'),
                    'updated_at' => $existing->updated_at->format('M d, Y H:i:s'),
                    'details' => $existingData,
                    'report_url' => route('services.clearance.report', $existing->id),
                ],
                'result_id' => $existing->id
            ]);
        }

        // Mode: Submit new clearance request
        $defaultClearPrice = (float) (VerificationPrice::first()->ipe_clearance_price ?? 700);
        $price = $user ? (float) $user->getDeveloperApiPrice('developer_api_ipe_clearance_price', $defaultClearPrice) : $defaultClearPrice;
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'IPE Clearance Submission (' . $trackingId . ')', 'CLEAR');
        if (!$debit['ok']) {
            if ($wantsJson) {
                return response()->json(['status' => false, 'message' => $debit['message']]);
            }
            return back()->with('error', $debit['message'])->withInput();
        }

        try {
            $referenceId = 'CLR-' . strtoupper(bin2hex(random_bytes(4)));

            if ($ipeClearanceMode === 'dataverify') {
                $dvClient = new \App\Services\DataVerify\DataVerifyClient();
                $dvRes = $dvClient->submitIpeClearance($trackingId);

                if ($dvRes['ok'] ?? false) {
                    $apiData = $dvRes['data'] ?? [];
                    $newTid = \App\Models\VerificationResult::extractNewTrackingId($apiData, $trackingId);
                    $isSuccess = ($dvRes['status'] ?? '') === 'successful' || !empty($newTid);
                    $initialStatus = $isSuccess ? 'successful' : 'waiting_for_review';

                    $payload = [
                        'tracking_id' => $trackingId,
                        'new_tracking_id' => $newTid,
                        'transaction_id' => $dvRes['transaction_id'] ?? null,
                        'category' => $category,
                        'user_name' => $user->fullname,
                        'user_email' => $user->email,
                        'user_phone' => $user->phone,
                        'remarks' => $request->input('remarks'),
                        'service_mode' => 'Automated Processing (DataVerify)',
                        'amount_paid' => (float) $price,
                        'api_response' => $apiData,
                    ];

                    $result = VerificationResult::create([
                        'user_id' => $user->id,
                        'service_type' => 'ipe_clearance',
                        'identifier' => $trackingId,
                        'provider_name' => 'DataVerify',
                        'status' => $initialStatus,
                        'reference_id' => $referenceId,
                        'admin_note' => $dvRes['message'] ?? 'Submitted to DataVerify. Processing in progress.',
                        'response_data' => $payload,
                    ]);

                    $wallet->markTransactionSuccess($debit['txId']);

                    if (!$wantsJson) {
                        return redirect()->route('services.clearance.report', $result->id)
                            ->with('status', 'Clearance request submitted successfully.');
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Clearance request submitted successfully. Please check status under IPE Results in a few minutes.',
                        'report_url' => route('services.clearance.report', $result->id),
                        'data' => [
                            'tracking_id' => $trackingId,
                            'reference_id' => $referenceId,
                            'category' => $category,
                            'status' => $initialStatus,
                            'status_label' => $isSuccess ? 'Cleared / Successful' : 'Processing Application',
                            'admin_note' => $dvRes['message'] ?? 'Submitted for verification.',
                            'new_tracking_id' => $newTid,
                            'report_url' => route('services.clearance.report', $result->id),
                        ],
                        'result_id' => $result->id
                    ]);
                } else {
                    $cleanErr = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', (string) ($dvRes['message'] ?? 'DataVerify submission failed.'));
                    throw new \Exception($cleanErr);
                }
            } elseif ($ipeClearanceMode === 'robosttech') {
                $apiCenter = ApiCenter::first();
                $customRobost = CustomApi::where('provider_identifier', 'robosttech')->where('status', true)->first();
                $apiKey = $customRobost?->api_key ?: $apiCenter?->robosttech_api_key ?: env('ROBOSTTECH_API_KEY') ?: \App\Models\SystemSetting::get('robosttech_api_key');

                if (!$apiKey) {
                    throw new \App\Exceptions\ServiceNotConfiguredException('Clearance service is temporarily unavailable. Please try again later.');
                }

                $endpoint = $this->resolveRobostEndpoint(
                    (string) ($apiCenter?->robosttech_endpoint_clearance ?: $customRobost?->endpoint ?: 'https://robosttech.com/api'),
                    'clearance'
                );

                $response = Http::timeout(45)->withHeaders([
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json'
                ])->post($endpoint, [
                    'tracking_id' => $trackingId,
                    'number' => $trackingId,
                    'category' => $category,
                ]);

                if ($response->successful()) {
                    $apiData = $response->json();
                    $apiStatus = strtolower((string) ($apiData['status'] ?? $apiData['data']['status'] ?? 'waiting_for_review'));
                    $newTid = \App\Models\VerificationResult::extractNewTrackingId($apiData, $trackingId);
                    $isSuccess = in_array($apiStatus, ['cleared', 'completed', 'resolved']) || !empty($newTid);
                    $initialStatus = $isSuccess ? 'successful' : 'waiting_for_review';

                    $payload = [
                        'tracking_id' => $trackingId,
                        'new_tracking_id' => $newTid,
                        'category' => $category,
                        'user_name' => $user->fullname,
                        'user_email' => $user->email,
                        'user_phone' => $user->phone,
                        'remarks' => $request->input('remarks'),
                        'service_mode' => 'Automated Processing',
                        'amount_paid' => (float) $price,
                        'api_response' => $apiData,
                    ];

                    $result = VerificationResult::create([
                        'user_id' => $user->id,
                        'service_type' => 'ipe_clearance',
                        'identifier' => $trackingId,
                        'provider_name' => 'Robosttech',
                        'status' => $initialStatus,
                        'reference_id' => $referenceId,
                        'admin_note' => $apiData['message'] ?? 'Submitted successfully. Verification is in progress.',
                        'response_data' => $payload,
                    ]);

                    $wallet->markTransactionSuccess($debit['txId']);

                    if (!$wantsJson) {
                        return redirect()->route('services.clearance.report', $result->id)
                            ->with('status', 'Clearance request submitted successfully.');
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Clearance request submitted successfully. Please check status under IPE Results in a few minutes.',
                        'report_url' => route('services.clearance.report', $result->id),
                        'data' => [
                            'tracking_id' => $trackingId,
                            'reference_id' => $referenceId,
                            'category' => $category,
                            'status' => $initialStatus,
                            'status_label' => $isSuccess ? 'Cleared / Successful' : 'Processing Application',
                            'admin_note' => $apiData['message'] ?? 'Submitted for verification.',
                            'new_tracking_id' => $newTid,
                            'report_url' => route('services.clearance.report', $result->id),
                        ],
                        'result_id' => $result->id
                    ]);
                } else {
                    $payload = $response->json();
                    $rawErr = (is_array($payload) ? ($payload['message'] ?? $payload['detail'] ?? null) : null)
                        ?: ('Verification gateway connection failed (HTTP ' . $response->status() . ')');
                    $cleanErr = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', (string) $rawErr);
                    throw new \Exception($cleanErr);
                }
            } else {
                // Manual Review Mode
                $payload = [
                    'tracking_id' => $trackingId,
                    'new_tracking_id' => null,
                    'category' => $category,
                    'user_name' => $user->fullname,
                    'user_email' => $user->email,
                    'user_phone' => $user->phone,
                    'remarks' => $request->input('remarks'),
                    'service_mode' => 'Verification Desk Review',
                    'amount_paid' => (float) $price,
                ];

                $result = VerificationResult::create([
                    'user_id' => $user->id,
                    'service_type' => 'ipe_clearance',
                    'identifier' => $trackingId,
                    'provider_name' => 'ADMIN_MANUAL',
                    'status' => 'waiting_for_review',
                    'reference_id' => $referenceId,
                    'admin_note' => 'Awaiting vetting and clearance. Results typically available in less than 24 hours.',
                    'response_data' => $payload,
                ]);

                $wallet->markTransactionSuccess($debit['txId']);

                if (!$wantsJson) {
                    return redirect()->route('services.clearance.report', $result->id)
                        ->with('status', 'Clearance request submitted successfully. You will see results under IPE Results in less than 24 hours.');
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Clearance request submitted successfully. You will see results under IPE Results in less than 24 hours.',
                    'report_url' => route('services.clearance.report', $result->id),
                    'data' => [
                        'tracking_id' => $trackingId,
                        'reference_id' => $referenceId,
                        'category' => $category,
                        'status' => 'waiting_for_review',
                        'status_label' => 'Under Review',
                        'admin_note' => 'Awaiting vetting and clearance. Results typically available in less than 24 hours.',
                        'report_url' => route('services.clearance.report', $result->id),
                    ],
                    'result_id' => $result->id
                ]);
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'IPE Clearance Submission Failure', $debit['txId']);
            $msg = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', $e->getMessage());
            if ($wantsJson) {
                return response()->json(['status' => false, 'message' => 'Failed to process submission: ' . $msg]);
            }
            return back()->with('error', 'Failed to process submission: ' . $msg)->withInput();
        }
    }

    /**
     * Display the Personalization Index Page
     */
    public function personalizationIndex()
    {
        $history = VerificationResult::where('user_id', Auth::id())
                        ->where('service_type', 'personalization')
                        ->latest()
                        ->get();

        $price = 100;
        if (Schema::hasTable('verification_prices')) {
            $price = VerificationPrice::first()->personalization_price ?? 100;
        }

        return view('services.identity.personalization', compact('history', 'price'));
    }

    /**
     * Handle Personalization Verification
     */
    public function verifyPersonalization(Request $request)
    {
        $request->validate([
            'tracking_id' => ['required_without:number', 'string', 'max:30', 'regex:/^[A-Za-z0-9]+$/'],
            'number' => ['nullable', 'string', 'max:30'],
            'mode' => ['nullable', 'string', 'in:submit,status'],
            'result_id' => ['nullable', 'integer'],
        ]);

        $user = Auth::user();
        $apiCenter = ApiCenter::first();

        if (!$apiCenter || !$apiCenter->robosttech_api_key) {
            throw new \App\Exceptions\ServiceNotConfiguredException('Personalization service is currently unavailable. Please contact support.');
        }

        $trackingId = trim((string) ($request->tracking_id ?: $request->number));
        $isStatusCheck = $request->input('mode') === 'status' || $request->input('action') === 'status';

        // STATUS CHECK / POLLING MODE (Free - does not debit wallet)
        if ($isStatusCheck) {
            $result = null;
            if ($request->filled('result_id')) {
                $result = VerificationResult::where('user_id', $user->id)->where('id', $request->result_id)->first();
            }
            if (!$result) {
                $result = VerificationResult::where('user_id', $user->id)
                    ->where('service_type', 'personalization')
                    ->where('identifier', $trackingId)
                    ->latest()
                    ->first();
            }

            if (!$result) {
                return response()->json([
                    'status' => false,
                    'message' => 'No personalization submission found for Tracking ID ' . $trackingId,
                ], 404);
            }

            $endpoint = $this->resolveRobostEndpoint(
                (string) ($apiCenter->robosttech_endpoint_personalization ?: 'https://robosttech.com/api'),
                'personalization_status'
            );

            try {
                $response = Http::timeout(35)->withHeaders([
                    'api-key' => $apiCenter->robosttech_api_key,
                    'Content-Type' => 'application/json'
                ])->post($endpoint, [
                    'tracking_id' => $trackingId,
                    'number' => $trackingId,
                ]);

                if ($response->successful()) {
                    $apiData = $response->json();
                    $rawReqStatus = $apiData['request_status'] ?? $apiData['data']['request_status'] ?? null;
                    if (!$rawReqStatus && is_string($apiData['status'] ?? null)) {
                        $rawReqStatus = $apiData['status'];
                    }
                    if (!$rawReqStatus && is_string($apiData['data']['status'] ?? null)) {
                        $rawReqStatus = $apiData['data']['status'];
                    }
                    $resStatus = strtolower(trim((string) $rawReqStatus));

                    $hasCompletedPayload = !empty($apiData['nin'])
                        || !empty($apiData['data']['nin'])
                        || !empty($apiData['user_data']['nin'])
                        || !empty($apiData['pdf_base64'])
                        || !empty($apiData['data']['pdf_base64'])
                        || (!empty($apiData['user_data']) && is_array($apiData['user_data']) && (!empty($apiData['user_data']['firstname']) || !empty($apiData['user_data']['nin'])));

                    $isCompleted = in_array($resStatus, ['completed', 'complete', 'successful', 'success', 'done', 'approved', 'active'], true) || $hasCompletedPayload;
                    $isFailed = in_array($resStatus, ['failed', 'fail', 'rejected', 'error', 'record_not_found', 'not_found'], true);

                    $currentData = is_array($result->response_data) ? $result->response_data : [];
                    $mergedData = array_merge($currentData, is_array($apiData) ? $apiData : []);

                    $userData = $apiData['user_data'] ?? $apiData['data']['user_data'] ?? $apiData['data'] ?? [];
                    if (is_array($userData)) {
                        $mergedData['user_data'] = $userData;
                        foreach (['firstname', 'first_name', 'lastname', 'last_name', 'middlename', 'middle_name', 'dob', 'birthdate', 'gender', 'sex', 'photo', 'image', 'photo_base64', 'photoBase64', 'nin', 'phone', 'telephoneno'] as $f) {
                            if (isset($userData[$f]) && (!isset($mergedData[$f]) || $mergedData[$f] === '')) {
                                $mergedData[$f] = $userData[$f];
                            }
                        }
                    }

                    $nin = $apiData['nin'] ?? $apiData['data']['nin'] ?? ($userData['nin'] ?? null);
                    if ($nin) {
                        $mergedData['nin'] = $nin;
                    }

                    $pdfBase64 = $apiData['pdf_base64'] ?? $apiData['data']['pdf_base64'] ?? ($userData['pdf_base64'] ?? null);
                    if (!empty($pdfBase64)) {
                        $mergedData['pdf_base64'] = $pdfBase64;
                    }

                    if ($isCompleted) {
                        $result->status = 'successful';
                        $result->admin_note = $apiData['message'] ?? 'Personalization completed. NIN and profile active on NIMC.';
                    } elseif ($isFailed) {
                        $result->status = 'failed';
                        $result->admin_note = $apiData['message'] ?? 'Personalization request failed or record not found.';
                    } else {
                        $result->status = 'pending';
                        $result->admin_note = $apiData['message'] ?? 'Personalization in progress. Awaiting NIMC gateway completion.';
                    }

                    $result->response_data = $mergedData;
                    $result->save();

                    $cleanMsg = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', (string) ($apiData['message'] ?? ($isCompleted ? 'Personalization completed.' : 'Personalization in progress.')));

                    return response()->json([
                        'status' => true,
                        'request_status' => $result->status === 'successful' ? 'completed' : ($result->status === 'failed' ? 'failed' : 'pending'),
                        'is_completed' => $result->status === 'successful',
                        'is_pending' => in_array($result->status, ['pending', 'waiting_for_review', 'processing']),
                        'is_failed' => $result->status === 'failed',
                        'message' => $cleanMsg,
                        'nin' => $mergedData['nin'] ?? null,
                        'user_data' => $mergedData['user_data'] ?? null,
                        'data' => $mergedData,
                        'result_id' => $result->id,
                        'reference_id' => $result->reference_id,
                        'tracking_id' => $trackingId,
                        'report_url' => route('services.personalization.report', $result->id),
                        'slips' => [
                            'regular' => route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'print' => 1]),
                            'premium' => route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip', 'print' => 1]),
                            'standard' => route('services.nin.slip', ['id' => $result->id, 'type' => 'standard_slip', 'print' => 1]),
                            'pdf_download' => route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'download' => 1]),
                        ]
                    ]);
                } else {
                    $payload = $response->json();
                    $rawMsg = (is_array($payload) ? ($payload['message'] ?? $payload['detail'] ?? null) : null)
                        ?: ('Status check failed (HTTP ' . $response->status() . ')');
                    $cleanMsg = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', (string) $rawMsg);
                    return response()->json([
                        'status' => false,
                        'is_pending' => true,
                        'message' => $cleanMsg,
                        'result_id' => $result->id,
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'is_pending' => true,
                    'message' => preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', $e->getMessage()),
                    'result_id' => $result->id,
                ]);
            }
        }

        // SUBMIT MODE (Charged on submission, not refunded per provider billing terms)
        $price = 100;
        if (Schema::hasTable('verification_prices')) {
            $price = VerificationPrice::first()->personalization_price ?? 100;
        }

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Personalization', 'PERS');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $endpoint = $this->resolveRobostEndpoint((string) ($apiCenter->robosttech_endpoint_personalization ?: 'https://robosttech.com/api'), 'personalization');
            $response = Http::timeout(45)
                ->withHeaders([
                    'api-key' => $apiCenter->robosttech_api_key,
                    'Content-Type' => 'application/json'
                ])
                ->post($endpoint, [
                    'tracking_id' => $trackingId,
                    'number' => $trackingId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    $data['_transaction_id'] = $debit['txId'];
                }

                $rawReqStatus = $data['request_status'] ?? $data['data']['request_status'] ?? null;
                if (!$rawReqStatus && is_string($data['status'] ?? null)) {
                    $rawReqStatus = $data['status'];
                }
                if (!$rawReqStatus && is_string($data['data']['status'] ?? null)) {
                    $rawReqStatus = $data['data']['status'];
                }
                $resStatus = strtolower(trim((string) $rawReqStatus));

                $hasCompletedPayload = !empty($data['nin']) 
                    || !empty($data['data']['nin']) 
                    || !empty($data['user_data']['nin']) 
                    || !empty($data['pdf_base64']) 
                    || !empty($data['data']['pdf_base64'])
                    || (!empty($data['user_data']) && is_array($data['user_data']) && (!empty($data['user_data']['firstname']) || !empty($data['user_data']['nin'])));

                $isCompleted = in_array($resStatus, ['completed', 'complete', 'successful', 'success', 'done', 'approved', 'active'], true) || $hasCompletedPayload;
                $isFailed = in_array($resStatus, ['failed', 'fail', 'rejected', 'error'], true);
                $initialStatus = $isCompleted ? 'successful' : ($isFailed ? 'failed' : 'pending');

                $userData = $data['user_data'] ?? $data['data']['user_data'] ?? $data['data'] ?? [];
                if (is_array($userData)) {
                    $data['user_data'] = $userData;
                    foreach (['firstname', 'first_name', 'lastname', 'last_name', 'middlename', 'middle_name', 'dob', 'birthdate', 'gender', 'sex', 'photo', 'image', 'photo_base64', 'photoBase64', 'nin', 'phone', 'telephoneno'] as $f) {
                        if (isset($userData[$f]) && (!isset($data[$f]) || $data[$f] === '')) {
                            $data[$f] = $userData[$f];
                        }
                    }
                }

                $nin = $data['nin'] ?? $data['data']['nin'] ?? ($userData['nin'] ?? null);
                if ($nin) {
                    $data['nin'] = $nin;
                }

                $pdfBase64 = $data['pdf_base64'] ?? $data['data']['pdf_base64'] ?? ($userData['pdf_base64'] ?? null);
                if (!empty($pdfBase64)) {
                    $data['pdf_base64'] = $pdfBase64;
                }

                $result = $this->storeResult($user, 'personalization', $trackingId, 'Robosttech', $data);
                $result->status = $initialStatus;
                $result->save();

                $wallet->markTransactionSuccess($debit['txId']);

                $reportUrl = route('services.personalization.report', $result->id);
                $apiMsg = is_array($data) && !empty($data['message']) 
                    ? $data['message'] 
                    : ($isCompleted 
                        ? 'Identity record successfully personalized and activated on NIMC database.' 
                        : 'Personalization ticket accepted for processing.');
                $cleanMsg = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', (string) $apiMsg);

                return response()->json([
                    'status' => true,
                    'request_status' => $initialStatus === 'successful' ? 'completed' : ($initialStatus === 'failed' ? 'failed' : 'pending'),
                    'is_completed' => $initialStatus === 'successful',
                    'is_pending' => $initialStatus === 'pending',
                    'is_failed' => $initialStatus === 'failed',
                    'message' => $cleanMsg,
                    'data' => $data,
                    'result_id' => $result->id,
                    'reference_id' => $result->reference_id,
                    'tracking_id' => $trackingId,
                    'report_url' => $reportUrl,
                    'slips' => [
                        'regular' => route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'print' => 1]),
                        'premium' => route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip', 'print' => 1]),
                        'standard' => route('services.nin.slip', ['id' => $result->id, 'type' => 'standard_slip', 'print' => 1]),
                        'pdf_download' => route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'download' => 1]),
                    ]
                ]);
            } else {
                $payload = $response->json();
                $rawMsg = (is_array($payload) ? ($payload['message'] ?? $payload['detail'] ?? null) : null)
                    ?: ('Verification failed (HTTP ' . $response->status() . ')');
                $cleanMsg = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', (string) $rawMsg);
                throw new \Exception($cleanMsg);
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Personalization', $debit['txId']);
            $msg = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', $e->getMessage());
            return response()->json(['status' => false, 'message' => $msg]);
        }
    }

    /**
     * Handle International Passport Verification
     */
    public function verifyPassport(Request $request)
    {
        $request->validate([
            'mode' => ['required', 'string', 'in:sync,image'],
            'number' => ['required_if:mode,sync', 'nullable', 'string'],
            'last_name' => ['required_if:mode,sync', 'nullable', 'string'],
            'image' => ['required_if:mode,image', 'nullable', 'string'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('passport_verification_price', 500);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'passport_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Passport verification.');
        }

        $headers = is_array($provider->headers) ? $provider->headers : [];
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'International Passport Verification', 'PASSPORT');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            if ($request->mode === 'sync') {
                $endpoint = $provider->endpoint; // Default or Sync endpoint
                $response = $http->post($endpoint, [
                    'number' => $request->number,
                    'last_name' => $request->last_name,
                ]);
            } else {
                // Image mode uses a different endpoint usually, but we'll try to deduce or use a variation
                // Based on user docs, it's /national_passport_image
                $endpoint = str_replace('national_passport', 'national_passport_image', $provider->endpoint);
                $response = $http->post($endpoint, [
                    'image' => $request->image,
                    'customer_reference' => 'PASS-REF-' . time(),
                    'customer_name' => $user->fullname ?? $user->username ?? 'Customer',
                ]);
            }

            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                $data = $response->json()['data'];
                $result = $this->storeResult($user, 'passport_verification', $request->number ?? 'IMAGE_OCR', $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Passport Record Verified',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['detail'] ?? $response['message'] ?? 'Passport details not found.'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'International Passport Verification', $debit['txId']);

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function resolveRobostEndpoint(string $configured, string $path): string
    {
        $configured = trim($configured);
        $path = ltrim(trim($path), '/');

        if ($configured === '') {
            return 'https://robosttech.com/api/' . $path;
        }

        $trimmed = rtrim($configured, '/');

        if (str_ends_with(strtolower($trimmed), '/' . strtolower($path))) {
            return $trimmed;
        }

        $knownActions = [
            'nin_verify',
            'nin_phone',
            'nin_demo',
            'validation_status',
            'validation',
            'clearance_status',
            'clearance',
            'personalization_status',
            'personalization',
            'balance',
        ];

        foreach ($knownActions as $action) {
            if (str_ends_with(strtolower($trimmed), '/' . $action)) {
                $trimmed = substr($trimmed, 0, -strlen('/' . $action));
                break;
            }
        }

        return rtrim($trimmed, '/') . '/' . $path;
    }

    public function hasRobosttechCredentials(?CustomApi $provider = null): bool
    {
        $key = trim((string) ($provider?->api_key ?? ''));
        if ($key !== '') {
            return true;
        }

        $apiCenter = ApiCenter::first();
        $acKey = trim((string) ($apiCenter?->robosttech_api_key ?? ''));
        if ($acKey !== '') {
            return true;
        }

        $envKey = trim((string) (env('ROBOSTTECH_API_KEY') ?: ''));
        if ($envKey !== '') {
            return true;
        }

        $settingKey = trim((string) (\App\Models\SystemSetting::get('robosttech_api_key', '')));
        return $settingKey !== '';
    }

    public function hasDataVerifyCredentials(?CustomApi $provider = null): bool
    {
        $key = trim((string) ($provider?->api_key ?? ''));
        if ($key !== '') {
            return true;
        }

        $apiCenter = ApiCenter::first();
        $acKey = trim((string) ($apiCenter?->dataverify_api_key ?? ''));
        if ($acKey !== '') {
            return true;
        }

        $envKey = trim((string) (env('DATAVERIFY_API_KEY') ?: ''));
        if ($envKey !== '') {
            return true;
        }

        $settingKey = trim((string) (\App\Models\SystemSetting::get('dataverify_api_key', '')));
        return $settingKey !== '';
    }

    public function preferredValidationProvider(): ?CustomApi
    {
        $mode = \App\Models\SystemSetting::get('nin_validation_mode', 'auto');
        if ($mode === 'api') {
            $mode = 'auto';
        }

        $valTypes = ['validation', 'nin_validation', 'nin_verification', 'nin', 'identity'];

        $dvProvider = CustomApi::query()
            ->whereIn('service_type', $valTypes)
            ->where('status', true)
            ->where(function ($q) {
                $q->where('provider_identifier', 'dataverify')
                  ->orWhere('endpoint', 'like', '%dataverify%');
            })
            ->orderBy('priority', 'asc')
            ->first();

        $robostProvider = CustomApi::query()
            ->whereIn('service_type', $valTypes)
            ->where('status', true)
            ->where(function ($q) {
                $q->where('provider_identifier', 'robosttech')
                  ->orWhere('endpoint', 'like', '%robosttech%');
            })
            ->orderBy('priority', 'asc')
            ->first();

        $hasDv = $this->hasDataVerifyCredentials($dvProvider);
        $hasRobost = $this->hasRobosttechCredentials($robostProvider);

        if ($mode === 'dataverify') {
            if ($dvProvider && $hasDv) {
                return $dvProvider;
            }
            if ($robostProvider && $hasRobost) {
                return $robostProvider;
            }
            return $dvProvider ?: $robostProvider;
        }

        if ($mode === 'robosttech') {
            if ($robostProvider && $hasRobost) {
                return $robostProvider;
            }
            if ($dvProvider && $hasDv) {
                return $dvProvider;
            }
            return $robostProvider ?: $dvProvider;
        }

        // Auto / API mode: prioritize whichever provider actually has active credentials configured
        if ($hasDv && !$hasRobost) {
            return $dvProvider ?: $robostProvider;
        }
        if ($hasRobost && !$hasDv) {
            return $robostProvider ?: $dvProvider;
        }
        if ($hasDv && $hasRobost) {
            if ($robostProvider && $dvProvider && $robostProvider->priority < $dvProvider->priority) {
                return $robostProvider;
            }
            return $dvProvider ?: $robostProvider;
        }

        return $dvProvider ?: $robostProvider ?: CustomApi::query()
            ->whereIn('service_type', $valTypes)
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->first();
    }


    /**
     * Display the Voters Card Verification Index Page
     */
    public function votersCardIndex()
    {
        $voterProviders = CustomApi::where('service_type', 'voters_card_verification')
                                ->where('status', true)
                                ->get();

        $history = VerificationResult::where('user_id', Auth::id())
                        ->where('service_type', 'voters_card_verification')
                        ->latest()
                        ->get();

        return view('services.identity.voters_card', compact('voterProviders', 'history'));
    }

    /**
     * Handle Voters Card Verification
     */
    public function verifyVotersCard(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'], // VIN
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'dob' => ['required', 'string', 'date_format:Y-m-d'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();
        $price = \App\Models\SystemSetting::get('voters_card_verification_price', 200);

        // Determine Provider
        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'voters_card_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Voters Card verification.');
        }

        $endpoint = $provider->endpoint;
        // VerifyMe uses Path Params for VIN
        $url = rtrim($endpoint, '/') . '/' . $request->number;
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Voters Card Verification', 'VOTER');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->post($url, [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'dob' => $request->dob,
            ]);

            if ($response->successful() && isset($response['status']) && $response['status'] === 'success') {
                $data = $response->json()['data'];
                $result = $this->storeResult($user, 'voters_card_verification', $request->number, $provider->name, $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Voters Card Verified',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed: ' . ($response['message'] ?? 'Unable to retrieve voter record.'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Voters Card Verification', $debit['txId']);
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }



    // NIN verification methods consolidated into NINController




    /**
     * Display the Address Verification Index Page
     */
    public function addressIndex()
    {
        $addressProviders = CustomApi::where('service_type', 'address_verification')
                                    ->where('status', true)
                                    ->get();

        // Fetch recent requests for this user (using Transactions as proxy if no specific model exists)
        $recentRequests = Transaction::where('user_email', Auth::user()->email)
                                    ->where('order_type', 'like', '%Address Verification%')
                                    ->latest()
                                    ->take(10)
                                    ->get();

        $myResults = VerificationResult::where('user_id', Auth::id())
            ->where('service_type', 'address_verification')
            ->latest()
            ->get();

        return view('services.identity.address', compact('addressProviders', 'recentRequests', 'myResults'));
    }

    /**
     * Submit Address for Verification (VerifyMe)
     */
    public function submitAddressVerification(Request $request)
    {
        $request->validate([
            'street' => ['required', 'string'],
            'lga' => ['required', 'string'],
            'state' => ['required', 'string'],
            'landmark' => ['required', 'string'],
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'dob' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'idType' => ['required', 'in:BVN,NIN,KYC'],
            'idNumber' => ['required', 'string'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id']
        ]);

        $user = Auth::user();

        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'address_verification')->where('status', true)->first();
        }

        if (!$provider || !$provider->status) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active provider for Address verification.');
        }

        $price = $provider->price ?? \App\Models\SystemSetting::get('address_verification_price', 1000);

        $endpoint = $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $reference = 'VMN_' . strtoupper(bin2hex(random_bytes(6)));
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Address Verification (Submitted)', 'ADDR', $reference);
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) { $http = $http->withHeaders($headers); }

            $response = $http->post($endpoint, [
                'reference' => $reference,
                'street' => $request->street,
                'lga' => $request->lga,
                'state' => $request->state,
                'landmark' => $request->landmark,
                'applicant' => [
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'dob' => $request->dob,
                    'phone' => $request->phone,
                    'idType' => $request->idType,
                    'idNumber' => $request->idNumber,
                ]
            ]);

            if ($response->successful() && isset($response['status']) && $response['status'] === 'success') {
                $result = $this->storeResult($user, 'address_verification', $reference, $provider->name, $response->json()['data'] ?? $response->json(), 'pending');

                return response()->json(['status' => true, 'message' => 'Address verification submitted. Reference: ' . $reference, 'result_id' => $result->id]);
            } else {
                throw new \Exception('Submission Failed: ' . ($response['message'] ?? 'Service unavailable.'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Address Verification (Submitted)', $reference);
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * View Detailed Address Verification Status
     */
    public function viewAddressDetails($id)
    {
        $provider = CustomApi::where('service_type', 'address_verification')->where('status', true)->first();
        
        if (!$provider) {
            return redirect()->back()->with('error', 'Address verification provider not configured.');
        }

        $endpoint = rtrim($provider->endpoint, '/');
        // If the endpoint is the submission endpoint, we might need to adjust it to get the details endpoint
        // VerifyMe Details endpoint is usually https://vapi.verifyme.ng/v1/verifications/addresses/:id
        $baseUrl = str_replace('/addresses', '', $endpoint); 
        $url = $endpoint . '/' . $id;
        
        $headers = is_array($provider->headers) ? $provider->headers : [];

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) { $http = $http->withHeaders($headers); }

            $response = $http->get($url);

            if ($response->successful() && isset($response['status']) && $response['status'] === 'success') {
                $data = $response->json()['data'];
                return view('services.identity.address_details', compact('data'));
            } else {
                return redirect()->back()->with('error', 'Could not retrieve verification details.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gateway timeout.');
        }
    }

    /**
     * Get All Address Verifications
     */
    public function getAllAddressVerifications(Request $request)
    {
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 0);

        $provider = CustomApi::where('service_type', 'address_verification')->where('status', true)->first();
        if (!$provider) {
            throw new \App\Exceptions\ServiceNotConfiguredException('Provider not configured.');
        }

        $endpoint = rtrim($provider->endpoint, '/');
        $url = $endpoint . "?limit={$limit}&offset={$offset}";
        $headers = is_array($provider->headers) ? $provider->headers : [];

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) { $http = $http->withHeaders($headers); }

            $response = $http->get($url);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['status' => false, 'message' => 'Failed to fetch verifications.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gateway timeout.']);
        }
    }

    /**
     * Cancel Address Verification
     */
    public function cancelAddressVerification($id)
    {
        $provider = CustomApi::where('service_type', 'address_verification')->where('status', true)->first();
        if (!$provider) {
            throw new \App\Exceptions\ServiceNotConfiguredException('Provider not configured.');
        }

        $endpoint = rtrim($provider->endpoint, '/');
        $url = $endpoint . '/' . $id;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) { $http = $http->withHeaders($headers); }

            $response = $http->delete($url);

            if ($response->successful()) {
                // Update local transaction status if exists
                Transaction::where('transaction_id', $id)->update(['status' => 'cancelled']);
                return response()->json(['status' => true, 'message' => 'Verification cancelled successfully.']);
            } else {
                return response()->json(['status' => false, 'message' => 'Failed to cancel verification or verification not found.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gateway timeout.']);
        }
    }

    /**
     * Fetch Address By Identity (Marketplace)
     */
    public function fetchAddressByIdentity(Request $request)
    {
        $request->validate([
            'maxAddressAge' => 'required|string',
            'lastname'      => 'required|string',
            'firstname'     => 'required|string',
            'idNumber'      => 'required|string',
            'idType'        => 'required|string|in:FRSC,NIN',
        ]);

        $provider = CustomApi::where('service_type', 'address_verification')->where('status', true)->first();
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'Provider not configured.']);
        }

        $endpoint = rtrim($provider->endpoint, '/') . '/marketplace';
        $headers = is_array($provider->headers) ? $provider->headers : [];

        try {
            $http = Http::timeout(60);
            if (!empty($headers)) { $http = $http->withHeaders($headers); }

            $response = $http->post($endpoint, $request->only(['maxAddressAge', 'lastname', 'firstname', 'idNumber', 'idType']));

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['status' => false, 'message' => 'Address not found for this identity within specified period.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gateway timeout.']);
        }
    }

    /**
     * Handle Address Verification Webhook
     */
    public function handleAddressWebhook(Request $request)
    {
        $raw = (string) $request->getContent();
        $ip = (string) $request->ip();

        $secret = \App\Models\SystemSetting::get('verifyme_webhook_secret', null);

        if (!$secret) {
            \Log::error('VerifyMe webhook secret not configured', ['ip' => $ip]);
            return response()->json(['status' => 'misconfigured'], 503);
        }

        $signature = $request->header('X-VerifyMe-Signature')
            ?? $request->header('X-Signature')
            ?? $request->header('VERIFYME_SIGNATURE')
            ?? $request->header('WEBHOOK_SIGNATURE');

        if (!$signature) {
            \Log::warning('VerifyMe webhook missing signature', ['ip' => $ip]);
            return response()->json(['status' => 'forbidden'], 403);
        }

        $sig = trim((string) $signature);
        $sig = str_contains($sig, '=') ? trim(explode('=', $sig, 2)[1]) : $sig;
        $h256 = hash_hmac('sha256', $raw, (string) $secret);
        $h512 = hash_hmac('sha512', $raw, (string) $secret);
        $sigAllowed = hash_equals($h256, $sig) || hash_equals($h512, $sig);

        if (!$sigAllowed) {
            \Log::warning('VerifyMe webhook invalid signature', ['ip' => $ip]);
            return response()->json(['status' => 'forbidden'], 403);
        }

        $ref = $request->input('data.reference');
        $status = (string) ($request->input('data.status.status') ?? 'unknown');

        if (!$ref) {
            return response()->json(['status' => 'received'], 200);
        }

        \Log::info('VerifyMe Address Webhook Received', [
            'reference' => $ref,
            'status' => $status,
            'ip' => $ip,
        ]);

        $normalized = strtolower($status);
        $txStatus = $normalized === 'completed' ? 'success' : 'pending';

        $transaction = Transaction::where('transaction_id', $ref)->first();
        if ($transaction) {
            $transaction->update([
                'status' => $txStatus,
            ]);
        }

        $result = VerificationResult::where('service_type', 'address_verification')
            ->where('identifier', $ref)
            ->latest()
            ->first();

        if ($result) {
            $payload = $request->input('data') ?? $request->all();
            $result->update([
                'status' => $txStatus === 'success' ? 'success' : 'pending',
                'response_data' => $payload,
            ]);
        }

        return response()->json(['status' => 'received'], 200);
    }
}

<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApi;
use App\Models\AccountBalance;
use App\Models\Transaction;
use App\Services\WalletService;
use App\Services\VerificationResultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'credit_bureau_advance', 'validation', 'clearance', 'personalization'
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
                        ->where('service_type', 'validation')
                        ->latest()
                        ->get();

        $price = VerificationPrice::first()->validation_price ?? 700;

        return view('services.identity.validation', compact('history', 'price'));
    }

    /**
     * Handle Validation Verification
     */
    public function verifyValidation(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $price = VerificationPrice::first()->validation_price ?? 700;
        $apiCenter = ApiCenter::first();

        if (!$apiCenter || !$apiCenter->robosttech_api_key) {
            throw new \App\Exceptions\ServiceNotConfiguredException('Robosttech API credentials not configured');
        }

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Document Validation', 'VAL');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $endpoint = ($apiCenter->robosttech_endpoint_validation ?: 'https://robosttech.com/api') . '/validation';
            $response = Http::timeout(45)
                ->withHeaders([
                    'api-key' => $apiCenter->robosttech_api_key,
                    'Content-Type' => 'application/json'
                ])
                ->post($endpoint, [
                    'number' => $request->number,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = $this->storeResult($user, 'validation', $request->number, 'Robosttech', $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Validation Successful',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed');
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Document Validation', $debit['txId']);
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the IPE Clearance Index Page
     */
    public function clearanceIndex()
    {
        $history = VerificationResult::where('user_id', Auth::id())
                        ->where('service_type', 'clearance')
                        ->latest()
                        ->get();

        $price = VerificationPrice::first()->ipe_clearance_price ?? 400;

        return view('services.identity.clearance', compact('history', 'price'));
    }

    /**
     * Handle IPE Clearance Verification
     */
    public function verifyClearance(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'mode' => ['nullable', 'string', 'in:submit,status']
        ]);

        $mode = $request->input('mode', 'submit');
        $user = Auth::user();
        $price = VerificationPrice::first()->ipe_clearance_price ?? 400;
        $apiCenter = ApiCenter::first();

        if (!$apiCenter || !$apiCenter->robosttech_api_key) {
            throw new \App\Exceptions\ServiceNotConfiguredException('Robosttech API credentials not configured');
        }

        // Only charge for initial submission
        $debit = null;
        if ($mode === 'submit') {
            $wallet = app(WalletService::class);
            $debit = $wallet->debit($user, (float) $price, 'IPE Clearance Submission', 'CLEAR');
            if (!$debit['ok']) {
                return response()->json(['status' => false, 'message' => $debit['message']]);
            }
        }

        try {
            $endpoint = ($mode === 'status') 
                ? ($apiCenter->robosttech_endpoint_clearance_status ?: 'https://robosttech.com/api/clearance_status')
                : ($apiCenter->robosttech_endpoint_clearance ?: 'https://robosttech.com/api/clearance');

            $response = Http::timeout(45)
                ->withHeaders([
                    'api-key' => $apiCenter->robosttech_api_key,
                    'Content-Type' => 'application/json'
                ])
                ->post($endpoint, [
                    'tracking_id' => $request->number,
                ]);

            // Robosttech API might return 200 even for failed lookups or empty responses as per documentation sample
            // The documentation says "This request doesn't return any response body" for some cases? 
            // Wait, the "Example Response" section in the user input says "No response body". 
            // This is strange for a verification API. Let me re-read the doc.
            // "and update the result within some minutes, then you send request to check the status."
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($mode === 'submit') {
                    $result = $this->storeResult($user, 'clearance_request', $request->number, 'Robosttech', $data);
                    $wallet->markTransactionSuccess($debit['txId']);
                    return response()->json([
                        'status' => true,
                        'message' => 'Clearance request submitted successfully. Please check status in a few minutes.',
                        'data' => $data,
                        'result_id' => $result->id
                    ]);
                } else {
                    // Status check
                    $result = $this->storeResult($user, 'clearance_status', $request->number, 'Robosttech', $data);
                    return response()->json([
                        'status' => true,
                        'message' => 'Status retrieved successfully.',
                        'data' => $data,
                        'result_id' => $result->id
                    ]);
                }
            } else {
                throw new \Exception('API Error: ' . ($response->json()['message'] ?? 'Provider connection failed'));
            }
        } catch (\Exception $e) {
            if ($mode === 'submit' && isset($wallet) && isset($debit)) {
                $wallet->failAndRefund($user, (float) $price, 'IPE Clearance Submission', $debit['txId']);
            }
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
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

        $price = VerificationPrice::first()->personalization_price ?? 100;

        return view('services.identity.personalization', compact('history', 'price'));
    }

    /**
     * Handle Personalization Verification
     */
    public function verifyPersonalization(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $price = VerificationPrice::first()->personalization_price ?? 100;
        $apiCenter = ApiCenter::first();

        if (!$apiCenter || !$apiCenter->robosttech_api_key) {
            throw new \App\Exceptions\ServiceNotConfiguredException('Robosttech API credentials not configured');
        }

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Personalization', 'PERS');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            $endpoint = ($apiCenter->robosttech_endpoint_personalization ?: 'https://robosttech.com/api') . '/personalization';
            $response = Http::timeout(45)
                ->withHeaders([
                    'api-key' => $apiCenter->robosttech_api_key,
                    'Content-Type' => 'application/json'
                ])
                ->post($endpoint, [
                    'number' => $request->number,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = $this->storeResult($user, 'personalization', $request->number, 'Robosttech', $data);
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Personalization Successful',
                    'data' => $data,
                    'result_id' => $result->id
                ]);
            } else {
                throw new \Exception('Verification Failed');
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Personalization', $debit['txId']);
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Services\Vuvaa\VuvaaClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminSandboxServicesController extends Controller
{
    private function catalog(): array
    {
        return [
            'drivers_license' => [
                'title' => "Driver's License (Sandbox)",
                'custom_api_service_type' => 'drivers_license',
                'fields' => [
                    ['name' => 'license_no', 'label' => 'License Number', 'type' => 'text', 'required' => true],
                    ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'text', 'required' => true, 'placeholder' => 'YYYY-MM-DD'],
                    ['name' => 'firstname', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['name' => 'lastname', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'license_no' => ['required', 'string'],
                    'dob' => ['required', 'string'],
                    'firstname' => ['required', 'string'],
                    'lastname' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'biometric' => [
                'title' => 'Biometric Verification (Sandbox)',
                'custom_api_service_type' => 'biometric_verification',
                'fields' => [
                    ['name' => 'id_number', 'label' => 'ID Number', 'type' => 'text', 'required' => true],
                    ['name' => 'id_type', 'label' => 'ID Type', 'type' => 'select', 'required' => true, 'options' => [
                        ['value' => 'nin', 'label' => 'NIN'],
                        ['value' => 'bvn', 'label' => 'BVN'],
                        ['value' => 'frsc', 'label' => 'FRSC'],
                    ]],
                    ['name' => 'photo', 'label' => 'Photo', 'type' => 'file', 'required' => true],
                ],
                'validation' => [
                    'id_number' => ['required', 'string'],
                    'id_type' => ['required', 'string', 'in:nin,bvn,frsc'],
                    'photo' => ['required', 'file', 'max:1024'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'stamp_duty' => [
                'title' => 'Stamp Duty (Sandbox)',
                'custom_api_service_type' => 'stamp_duty',
                'fields' => [
                    ['name' => 'number', 'label' => 'Number', 'type' => 'text', 'required' => true],
                    ['name' => 'customer_name', 'label' => 'Customer Name', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'customer_name' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'plate_number' => [
                'title' => 'Plate Number (Sandbox)',
                'custom_api_service_type' => 'plate_number_verification',
                'fields' => [
                    ['name' => 'vehicle_number', 'label' => 'Vehicle Number', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'vehicle_number' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'cac' => [
                'title' => 'CAC Verification (Sandbox)',
                'custom_api_service_type' => 'cac_verification',
                'fields' => [
                    ['name' => 'rc_number', 'label' => 'RC Number', 'type' => 'text', 'required' => true],
                    ['name' => 'company_type', 'label' => 'Company Type', 'type' => 'select', 'required' => false, 'options' => [
                        ['value' => 'RC', 'label' => 'RC'],
                        ['value' => 'BN', 'label' => 'BN'],
                        ['value' => 'IT', 'label' => 'IT'],
                        ['value' => 'LL', 'label' => 'LL'],
                        ['value' => 'LLP', 'label' => 'LLP'],
                    ]],
                ],
                'validation' => [
                    'rc_number' => ['required', 'string'],
                    'company_type' => ['nullable', 'string', 'in:BN,RC,IT,LL,LLP'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'tin' => [
                'title' => 'TIN Verification (Sandbox)',
                'custom_api_service_type' => 'tin_verification',
                'fields' => [
                    ['name' => 'number', 'label' => 'Number', 'type' => 'text', 'required' => true],
                    ['name' => 'channel', 'label' => 'Channel', 'type' => 'select', 'required' => true, 'options' => [
                        ['value' => 'TIN', 'label' => 'TIN'],
                        ['value' => 'CAC', 'label' => 'CAC'],
                        ['value' => 'Phone', 'label' => 'Phone'],
                    ]],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'channel' => ['required', 'string', 'in:TIN,CAC,Phone'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'nin_face' => [
                'title' => 'NIN Face (Sandbox)',
                'custom_api_service_type' => 'nin_face_verification',
                'fields' => [
                    ['name' => 'number', 'label' => 'NIN', 'type' => 'text', 'required' => true],
                    ['name' => 'image', 'label' => 'Image (base64 or URL)', 'type' => 'textarea', 'required' => true],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'image' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'credit_bureau' => [
                'title' => 'Credit Bureau (Sandbox)',
                'custom_api_service_type' => 'credit_bureau_advance',
                'fields' => [
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'required' => true, 'options' => [
                        ['value' => 'ID', 'label' => 'ID'],
                        ['value' => 'BIO', 'label' => 'BIO'],
                    ]],
                    ['name' => 'number', 'label' => 'Number (required for ID mode)', 'type' => 'text', 'required' => false],
                    ['name' => 'dob', 'label' => 'DOB (required for BIO mode)', 'type' => 'text', 'required' => false, 'placeholder' => 'YYYY-MM-DD'],
                    ['name' => 'customer_name', 'label' => 'Customer Name', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'mode' => ['required', 'string', 'in:ID,BIO'],
                    'number' => ['required_if:mode,ID', 'nullable', 'string'],
                    'dob' => ['required_if:mode,BIO', 'nullable', 'string'],
                    'customer_name' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'passport' => [
                'title' => 'Passport Verification (Sandbox)',
                'custom_api_service_type' => 'passport_verification',
                'fields' => [
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'required' => true, 'options' => [
                        ['value' => 'sync', 'label' => 'Sync'],
                        ['value' => 'image', 'label' => 'Image OCR'],
                    ]],
                    ['name' => 'number', 'label' => 'Passport Number (sync)', 'type' => 'text', 'required' => false],
                    ['name' => 'last_name', 'label' => 'Last Name (sync)', 'type' => 'text', 'required' => false],
                    ['name' => 'image', 'label' => 'Image (base64 or URL) (image mode)', 'type' => 'textarea', 'required' => false],
                ],
                'validation' => [
                    'mode' => ['required', 'string', 'in:sync,image'],
                    'number' => ['required_if:mode,sync', 'nullable', 'string'],
                    'last_name' => ['required_if:mode,sync', 'nullable', 'string'],
                    'image' => ['required_if:mode,image', 'nullable', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'combined_verify' => [
                'title' => 'Combined Verify (Sandbox)',
                'custom_api_service_type' => 'bvn_nin_phone_verification',
                'fields' => [
                    ['name' => 'number', 'label' => 'BVN', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'voters_card' => [
                'title' => 'Voters Card (Sandbox)',
                'custom_api_service_type' => 'voters_card_verification',
                'fields' => [
                    ['name' => 'number', 'label' => 'VIN', 'type' => 'text', 'required' => true],
                    ['name' => 'firstname', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['name' => 'lastname', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
                    ['name' => 'dob', 'label' => 'DOB', 'type' => 'text', 'required' => true, 'placeholder' => 'YYYY-MM-DD'],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'firstname' => ['required', 'string'],
                    'lastname' => ['required', 'string'],
                    'dob' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'bvn_match' => [
                'title' => 'BVN Match (Sandbox)',
                'custom_api_service_type' => 'bvn_matching',
                'fields' => [
                    ['name' => 'number', 'label' => 'BVN', 'type' => 'text', 'required' => true],
                    ['name' => 'firstname', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['name' => 'lastname', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
                    ['name' => 'dob', 'label' => 'DOB', 'type' => 'text', 'required' => true],
                    ['name' => 'phoneNumber', 'label' => 'Phone (optional)', 'type' => 'text', 'required' => false],
                    ['name' => 'emailAddress', 'label' => 'Email (optional)', 'type' => 'text', 'required' => false],
                    ['name' => 'gender', 'label' => 'Gender (optional)', 'type' => 'text', 'required' => false],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'firstname' => ['required', 'string'],
                    'lastname' => ['required', 'string'],
                    'dob' => ['required', 'string'],
                    'phoneNumber' => ['nullable', 'string'],
                    'emailAddress' => ['nullable', 'email'],
                    'gender' => ['nullable', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'bvn_verify' => [
                'title' => 'BVN Verify (Sandbox)',
                'custom_api_service_type' => 'bvn_verification',
                'fields' => [
                    ['name' => 'number', 'label' => 'BVN', 'type' => 'text', 'required' => true],
                    ['name' => 'firstname', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['name' => 'lastname', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
                    ['name' => 'dob', 'label' => 'DOB', 'type' => 'text', 'required' => true],
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => true, 'options' => [
                        ['value' => 'basic', 'label' => 'Basic'],
                        ['value' => 'premium', 'label' => 'Premium'],
                    ]],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'firstname' => ['required', 'string'],
                    'lastname' => ['required', 'string'],
                    'dob' => ['required', 'string'],
                    'type' => ['required', 'in:basic,premium'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'nin_verify' => [
                'title' => 'NIN Verify (Sandbox)',
                'custom_api_service_type' => 'nin_verification',
                'fields' => [
                    ['name' => 'number', 'label' => 'NIN or Phone', 'type' => 'text', 'required' => true],
                    ['name' => 'firstname', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['name' => 'lastname', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
                    ['name' => 'dob', 'label' => 'DOB', 'type' => 'text', 'required' => true],
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'required' => true, 'options' => [
                        ['value' => 'nin', 'label' => 'NIN'],
                        ['value' => 'phone', 'label' => 'Phone'],
                    ]],
                ],
                'validation' => [
                    'number' => ['required', 'string'],
                    'firstname' => ['required', 'string'],
                    'lastname' => ['required', 'string'],
                    'dob' => ['required', 'string'],
                    'mode' => ['required', 'in:nin,phone'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'address_submit' => [
                'title' => 'Address Verify (Submit) (Sandbox)',
                'custom_api_service_type' => 'address_verification',
                'fields' => [
                    ['name' => 'street', 'label' => 'Street', 'type' => 'text', 'required' => true],
                    ['name' => 'lga', 'label' => 'LGA', 'type' => 'text', 'required' => true],
                    ['name' => 'state', 'label' => 'State', 'type' => 'text', 'required' => true],
                    ['name' => 'landmark', 'label' => 'Landmark', 'type' => 'text', 'required' => true],
                    ['name' => 'firstname', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['name' => 'lastname', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
                    ['name' => 'dob', 'label' => 'DOB', 'type' => 'text', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true],
                    ['name' => 'idType', 'label' => 'ID Type', 'type' => 'select', 'required' => true, 'options' => [
                        ['value' => 'BVN', 'label' => 'BVN'],
                        ['value' => 'NIN', 'label' => 'NIN'],
                        ['value' => 'KYC', 'label' => 'KYC'],
                    ]],
                    ['name' => 'idNumber', 'label' => 'ID Number', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
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
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'airtime' => [
                'title' => 'VTU Airtime (Sandbox)',
                'custom_api_service_type' => null,
                'fields' => [
                    ['name' => 'network', 'label' => 'Network', 'type' => 'text', 'required' => true],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'network' => ['required', 'string'],
                    'amount' => ['required', 'numeric', 'min:50'],
                    'phone' => ['required', 'string'],
                ],
            ],
            'data' => [
                'title' => 'VTU Data (Sandbox)',
                'custom_api_service_type' => null,
                'fields' => [
                    ['name' => 'network', 'label' => 'Network', 'type' => 'text', 'required' => true],
                    ['name' => 'plan_id', 'label' => 'Plan ID', 'type' => 'text', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'network' => ['required', 'string'],
                    'plan_id' => ['required', 'string'],
                    'phone' => ['required', 'string'],
                ],
            ],
            'waec' => [
                'title' => 'WAEC Result PIN (Sandbox)',
                'custom_api_service_type' => 'education_waec',
                'fields' => [
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'phone' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'waec_registration' => [
                'title' => 'WAEC Registration PIN (Sandbox)',
                'custom_api_service_type' => 'education_waec_registration',
                'fields' => [
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'phone' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
            'insurance_motor' => [
                'title' => 'Motor Insurance (Sandbox)',
                'custom_api_service_type' => 'insurance_motor',
                'fields' => [
                    ['name' => 'variation_code', 'label' => 'Variation Code', 'type' => 'text', 'required' => true],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'required' => true],
                    ['name' => 'insured_name', 'label' => 'Insured Name', 'type' => 'text', 'required' => true],
                    ['name' => 'plate_number', 'label' => 'Plate Number', 'type' => 'text', 'required' => true],
                    ['name' => 'chasis_number', 'label' => 'Chasis Number', 'type' => 'text', 'required' => true],
                    ['name' => 'engine_capacity', 'label' => 'Engine Capacity', 'type' => 'text', 'required' => true],
                    ['name' => 'vehicle_make', 'label' => 'Vehicle Make', 'type' => 'text', 'required' => true],
                    ['name' => 'vehicle_model', 'label' => 'Vehicle Model', 'type' => 'text', 'required' => true],
                    ['name' => 'vehicle_color', 'label' => 'Vehicle Color', 'type' => 'text', 'required' => true],
                    ['name' => 'year_of_make', 'label' => 'Year of Make', 'type' => 'text', 'required' => true],
                    ['name' => 'state', 'label' => 'State', 'type' => 'text', 'required' => true],
                    ['name' => 'lga', 'label' => 'LGA', 'type' => 'text', 'required' => true],
                ],
                'validation' => [
                    'variation_code' => ['required', 'string'],
                    'amount' => ['required', 'numeric'],
                    'phone' => ['required', 'string'],
                    'email' => ['required', 'email'],
                    'insured_name' => ['required', 'string'],
                    'plate_number' => ['required', 'string'],
                    'chasis_number' => ['required', 'string'],
                    'engine_capacity' => ['required', 'string'],
                    'vehicle_make' => ['required', 'string'],
                    'vehicle_model' => ['required', 'string'],
                    'vehicle_color' => ['required', 'string'],
                    'year_of_make' => ['required', 'string'],
                    'state' => ['required', 'string'],
                    'lga' => ['required', 'string'],
                    'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
                ],
            ],
        ];
    }

    public function index()
    {
        $services = $this->catalog();
        return view('admin.sandbox.services.index', compact('services'));
    }

    public function show(string $service)
    {
        $services = $this->catalog();
        abort_unless(isset($services[$service]), 404);

        $serviceConfig = $services[$service];
        $providers = collect();
        if (!empty($serviceConfig['custom_api_service_type'])) {
            $providers = CustomApi::where('service_type', $serviceConfig['custom_api_service_type'])
                ->where('status', true)
                ->orderBy('name')
                ->get();
        }

        return view('admin.sandbox.services.show', compact('service', 'serviceConfig', 'providers'));
    }

    public function run(Request $request, string $service)
    {
        $services = $this->catalog();
        abort_unless(isset($services[$service]), 404);

        $serviceConfig = $services[$service];
        $request->validate($serviceConfig['validation']);

        $provider = null;
        if (!empty($serviceConfig['custom_api_service_type'])) {
            if ($request->filled('api_provider_id')) {
                $provider = CustomApi::find($request->input('api_provider_id'));
            } else {
                $provider = CustomApi::where('service_type', $serviceConfig['custom_api_service_type'])
                    ->where('status', true)
                    ->first();
            }
        }

        $headers = [];
        $endpoint = null;
        $apiKey = null;
        $apiCenter = ApiCenter::first();

        if ($provider && $provider->status) {
            $endpoint = $provider->endpoint;
            $apiKey = $provider->api_key;
            $headers = is_array($provider->headers) ? $provider->headers : [];
        }

        return match ($service) {
            'drivers_license' => $this->runDriversLicense($request, $endpoint, $headers),
            'biometric' => $this->runBiometric($request, $endpoint, $headers),
            'stamp_duty' => $this->runStampDuty($request, $endpoint, $headers),
            'plate_number' => $this->runPlateNumber($request, $endpoint, $headers),
            'cac' => $this->runCac($request, $endpoint, $headers),
            'tin' => $this->runTin($request, $endpoint, $headers),
            'nin_face' => $this->runNinFace($request, $endpoint, $headers),
            'credit_bureau' => $this->runCreditBureau($request, $endpoint, $headers),
            'passport' => $this->runPassport($request, $endpoint, $headers),
            'combined_verify' => $this->runCombined($request, $endpoint, $headers),
            'voters_card' => $this->runVotersCard($request, $endpoint, $headers),
            'bvn_match' => $this->runBvnMatch($request, $endpoint, $headers),
            'bvn_verify' => $this->runBvnVerify($request, $endpoint, $headers),
            'nin_verify' => $this->runNinVerify($request, $provider, $endpoint, $headers),
            'address_submit' => $this->runAddressSubmit($request, $endpoint, $headers),
            'airtime' => $this->runAirtime($request, $apiCenter),
            'data' => $this->runData($request, $apiCenter),
            'waec' => $this->runWaec($request, $endpoint, $headers),
            'waec_registration' => $this->runWaecRegistration($request, $endpoint, $headers),
            'insurance_motor' => $this->runMotorInsurance($request, $endpoint, $headers),
            default => response()->json(['status' => false, 'message' => 'Sandbox not configured'], 500),
        };
    }

    private function httpClient(array $headers, int $timeoutSeconds = 60)
    {
        $http = Http::timeout($timeoutSeconds);
        if (!empty($headers)) {
            $http = $http->withHeaders($headers);
        }
        return $http;
    }

    private function requireEndpoint(?string $endpoint, string $message)
    {
        if (!$endpoint) {
            return response()->json(['status' => false, 'message' => $message], 422);
        }
        return null;
    }

    private function runDriversLicense(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Driver License.')) {
            return $missing;
        }

        $payload = [];
        $url = $endpoint;

        if (str_contains($endpoint, 'verifyme.ng')) {
            $url = rtrim($endpoint, '/') . '/' . $request->license_no;
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
        } else {
            $payload = [
                'license_no' => $request->license_no,
                'dob' => $request->dob,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
            ];
        }

        $response = $this->httpClient($headers, 45)->post($url, $payload);
        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }

        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runBiometric(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Biometric.')) {
            return $missing;
        }

        $file = $request->file('photo');
        $http = $this->httpClient($headers, 60);
        $response = $http->attach(
            'photo',
            file_get_contents($file->path()),
            $file->getClientOriginalName()
        )->post($endpoint, [
            'idNumber' => $request->id_number,
            'idType' => $request->id_type,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }

        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runStampDuty(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Stamp Duty.')) {
            return $missing;
        }

        $response = $this->httpClient($headers, 45)->post($endpoint, [
            'number' => $request->number,
            'customer_name' => $request->customer_name,
            'customer_reference' => 'SD-SANDBOX-' . now()->format('YmdHis'),
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }

        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runPlateNumber(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Plate Number.')) {
            return $missing;
        }

        $response = $this->httpClient($headers, 45)->post($endpoint, [
            'vehicle_number' => $request->vehicle_number,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }

        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runCac(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for CAC.')) {
            return $missing;
        }

        $response = $this->httpClient($headers, 60)->post($endpoint, [
            'rc_number' => $request->rc_number,
            'company_type' => $request->company_type ?? 'RC',
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }

        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runTin(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for TIN.')) {
            return $missing;
        }

        $response = $this->httpClient($headers, 45)->post($endpoint, [
            'number' => $request->number,
            'channel' => $request->channel,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }

        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runNinFace(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for NIN Face.')) {
            return $missing;
        }

        $response = $this->httpClient($headers, 60)->post($endpoint, [
            'number' => $request->number,
            'image' => $request->image,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }

        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runCreditBureau(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Credit Bureau.')) {
            return $missing;
        }

        $payload = [
            'mode' => $request->mode,
            'customer_name' => $request->customer_name,
            'customer_reference' => 'CR-SANDBOX-' . now()->format('YmdHis'),
        ];
        if ($request->mode === 'ID') {
            $payload['number'] = $request->number;
        } else {
            $payload['dob'] = $request->dob;
        }

        $response = $this->httpClient($headers, 60)->post($endpoint, $payload);
        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runPassport(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Passport.')) {
            return $missing;
        }

        $http = $this->httpClient($headers, 60);

        if ($request->mode === 'sync') {
            $response = $http->post($endpoint, [
                'number' => $request->number,
                'last_name' => $request->last_name,
            ]);
        } else {
            $url = str_replace('national_passport', 'national_passport_image', $endpoint);
            $response = $http->post($url, [
                'image' => $request->image,
                'customer_reference' => 'PASS-SANDBOX-' . now()->format('YmdHis'),
                'customer_name' => 'Admin Sandbox',
            ]);
        }

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runCombined(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Combined Verify.')) {
            return $missing;
        }

        $response = $this->httpClient($headers, 60)->post($endpoint, [
            'number' => $request->number,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runVotersCard(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Voters Card.')) {
            return $missing;
        }

        $url = rtrim($endpoint, '/') . '/' . $request->number;
        $response = $this->httpClient($headers, 60)->post($url, [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'dob' => $request->dob,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runBvnMatch(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for BVN Match.')) {
            return $missing;
        }

        $url = rtrim($endpoint, '/') . '/' . $request->number . '/match';
        $response = $this->httpClient($headers, 60)->post($url, [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'dob' => $request->dob,
            'phoneNumber' => $request->phoneNumber,
            'emailAddress' => $request->emailAddress,
            'gender' => $request->gender,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runBvnVerify(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for BVN Verify.')) {
            return $missing;
        }

        $url = rtrim($endpoint, '/') . '/' . $request->number . '?type=' . $request->type;
        $response = $this->httpClient($headers, 60)->post($url, [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'dob' => $request->dob,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runNinVerify(Request $request, ?CustomApi $provider, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for NIN Verify.')) {
            return $missing;
        }

        if ($provider && VuvaaClient::isVuvaaProvider($provider)) {
            if ($request->mode !== 'nin') {
                return response()->json(['status' => false, 'message' => 'Selected provider does not support this verification mode.'], 422);
            }

            $client = new VuvaaClient($provider);
            $result = $client->verifyNin((string) $request->number);
            if (!$result['ok']) {
                return response()->json(['status' => false, 'message' => $result['message'] ?? 'Verification failed.', 'data' => $result['data'] ?? null], 502);
            }

            return response()->json(['status' => true, 'data' => $result['data']]);
        }

        $slug = $request->mode === 'phone' ? 'nin_phone' : 'nin';
        $url = str_replace('/nin', '/' . $slug, rtrim($endpoint, '/')) . '/' . $request->number;

        $response = $this->httpClient($headers, 60)->post($url, [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'dob' => $request->dob,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'data' => $response->json() ?: $response->body()]);
    }

    private function runAddressSubmit(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Address Verification.')) {
            return $missing;
        }

        $reference = 'ADDR-SANDBOX-' . strtoupper(bin2hex(random_bytes(6)));
        $response = $this->httpClient($headers, 60)->post($endpoint, [
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
            ],
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'reference' => $reference, 'data' => $response->json() ?: $response->body()]);
    }

    private function runAirtime(Request $request, ?ApiCenter $apiCenter)
    {
        if (!$apiCenter || !$apiCenter->ade_apikey) {
            return response()->json(['status' => false, 'message' => 'VTU provider not configured'], 422);
        }

        $endpoint = $apiCenter->ade_endpoint_airtime ?? 'https://ade.com/api/airtime';
        $requestId = 'VTU-SANDBOX-' . strtoupper(bin2hex(random_bytes(4)));
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiCenter->ade_apikey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($endpoint, [
            'network' => $request->network,
            'amount' => $request->amount,
            'phone' => $request->phone,
            'request_id' => $requestId,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'request_id' => $requestId, 'data' => $response->json() ?: $response->body()]);
    }

    private function runData(Request $request, ?ApiCenter $apiCenter)
    {
        if (!$apiCenter || !$apiCenter->ade_apikey) {
            return response()->json(['status' => false, 'message' => 'VTU provider not configured'], 422);
        }

        $endpoint = $apiCenter->ade_endpoint_data ?? 'https://ade.com/api/data';
        $requestId = 'DATA-SANDBOX-' . strtoupper(bin2hex(random_bytes(4)));
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiCenter->ade_apikey,
        ])->timeout(60)->post($endpoint, [
            'network' => $request->network,
            'plan_id' => $request->plan_id,
            'phone' => $request->phone,
            'request_id' => $requestId,
        ]);

        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'request_id' => $requestId, 'data' => $response->json() ?: $response->body()]);
    }

    private function runWaec(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for WAEC.')) {
            return $missing;
        }

        $requestId = now()->format('YmdHi') . bin2hex(random_bytes(3));
        $payload = [
            'request_id' => $requestId,
            'serviceID' => 'waec',
            'variation_code' => 'waecdirect',
            'amount' => 900,
            'phone' => $request->phone,
            'quantity' => 1,
        ];

        $response = $this->httpClient($headers, 45)->post(rtrim($endpoint, '/'), $payload);
        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'request_id' => $requestId, 'data' => $response->json() ?: $response->body()]);
    }

    private function runWaecRegistration(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for WAEC Registration.')) {
            return $missing;
        }

        $requestId = now()->format('YmdHi') . bin2hex(random_bytes(3));
        $payload = [
            'request_id' => $requestId,
            'serviceID' => 'waec-registration',
            'variation_code' => 'waec-registration',
            'amount' => 18000,
            'phone' => $request->phone,
            'quantity' => 1,
        ];

        $response = $this->httpClient($headers, 45)->post(rtrim($endpoint, '/'), $payload);
        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'request_id' => $requestId, 'data' => $response->json() ?: $response->body()]);
    }

    private function runMotorInsurance(Request $request, ?string $endpoint, array $headers)
    {
        if ($missing = $this->requireEndpoint($endpoint, 'No active provider configured for Motor Insurance.')) {
            return $missing;
        }

        $requestId = now()->format('YmdHi') . bin2hex(random_bytes(3));
        $payload = [
            'request_id' => $requestId,
            'serviceID' => 'ui-insure',
            'billersCode' => $request->plate_number,
            'variation_code' => $request->variation_code,
            'amount' => $request->amount,
            'phone' => $request->phone,
            'Insured_Name' => $request->insured_name,
            'engine_capacity' => $request->engine_capacity,
            'Chasis_Number' => $request->chasis_number,
            'Plate_Number' => $request->plate_number,
            'vehicle_make' => $request->vehicle_make,
            'vehicle_color' => $request->vehicle_color,
            'vehicle_model' => $request->vehicle_model,
            'YearofMake' => $request->year_of_make,
            'state' => $request->state,
            'lga' => $request->lga,
            'email' => $request->email,
        ];

        $response = $this->httpClient($headers, 60)->post($endpoint, $payload);
        if (!$response->successful()) {
            return response()->json(['status' => false, 'message' => 'Provider error', 'data' => $response->json() ?: $response->body()], 502);
        }
        return response()->json(['status' => true, 'request_id' => $requestId, 'data' => $response->json() ?: $response->body()]);
    }
}

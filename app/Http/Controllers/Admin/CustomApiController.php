<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomApi;
use App\Models\CustomApiVerificationType;
use Illuminate\Http\Request;

class CustomApiController extends Controller
{
    /**
     * Display a listing of custom APIs.
     */
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index(Request $request)
    {
        $apisQuery = CustomApi::query()->with(['verificationTypes' => function ($q) {
            $q->orderBy('sort_order')->orderBy('label');
        }]);
        if ($request->filled('service_type')) {
            $apisQuery->where('service_type', $request->string('service_type')->toString());
        }

        $apis = $apisQuery->orderBy('service_type')->orderBy('name')->get();
        return view('admin.custom_apis.index', compact('apis'));
    }

    /**
     * Store a newly created custom API.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|unique:custom_apis,name|max:255',
            'provider_identifier' => 'nullable|string|max:100',
            'service_type' => 'required|string|max:100',
            'endpoint'     => 'required|url|max:255',
            'api_key'      => 'nullable|string|max:255',
            'secret_key'   => 'nullable|string|max:255',
            'headers'      => 'nullable|string', 
            'config'       => 'nullable|string', 
            'supported_modes' => 'nullable|array',
            'supported_modes.*' => 'string|max:50',
            'price'        => 'required|numeric|min:0',
            'priority'     => 'required|integer|min:1',
            'timeout_seconds' => 'nullable|integer|min:1|max:300',
            'retry_count' => 'nullable|integer|min:0|max:10',
            'retry_delay_ms' => 'nullable|integer|min:0|max:10000',
            'fee_type' => 'nullable|in:flat,percent',
            'fee_value' => 'nullable|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'service_code' => 'nullable|string|max:120',
            'credit_amount_path' => 'nullable|string|max:120',
        ]);

        // Attempt to parse headers as JSON if provided
        $headersArray = [];
        if (!empty($request->headers)) {
            $parsed = json_decode((string)$request->headers, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $headersArray = $parsed;
            } else {
                return back()->with('error', __('errors.invalid_json'));
            }
        }

        $configArray = [];
        if (!empty($request->config)) {
            $parsed = json_decode((string)$request->config, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $configArray = $parsed;
            } else {
                return back()->with('error', __('errors.invalid_json'));
            }
        }

        if ($request->filled('fee_type')) {
            $configArray['fee_type'] = $request->string('fee_type')->toString();
        }
        if ($request->filled('fee_value')) {
            $configArray['fee_value'] = (float) $request->input('fee_value');
        }
        if ($request->filled('min_amount')) {
            $configArray['min_amount'] = (float) $request->input('min_amount');
        }
        if ($request->filled('max_amount')) {
            $configArray['max_amount'] = (float) $request->input('max_amount');
        }
        if ($request->filled('service_code')) {
            $configArray['service_code'] = $request->string('service_code')->toString();
        }
        if ($request->filled('credit_amount_path')) {
            $configArray['credit_amount_path'] = $request->string('credit_amount_path')->toString();
        }

        CustomApi::create([
            'name'         => $request->name,
            'provider_identifier' => $request->provider_identifier,
            'service_type' => $request->service_type,
            'endpoint'     => $request->endpoint,
            'api_key'      => $request->api_key,
            'secret_key'   => $request->secret_key,
            'headers'      => $headersArray,
            'config'       => $configArray,
            'supported_modes' => $request->supported_modes ?? [],
            'price'        => $request->price,
            'priority'     => $request->priority,
            'status'       => $request->has('status'),
            'timeout_seconds' => $request->input('timeout_seconds', 60),
            'retry_count' => $request->input('retry_count', 0),
            'retry_delay_ms' => $request->input('retry_delay_ms', 0),
        ]);

        return back()->with('success', 'Custom API configuration added successfully.');
    }

    public function storeTemplate(Request $request, string $template)
    {
        $templates = [
            'dataverify_nin' => [
                'name' => 'DataVerify (NIN)',
                'provider_identifier' => 'dataverify',
                'service_type' => 'nin_verification',
                'endpoint' => 'https://dataverify.com.ng/developers/nin_api/',
                'headers' => [],
                'config' => [],
                'price' => 200,
                'priority' => 1,
                'types' => [
                    ['type_key' => 'by_nin', 'label' => 'By NIN', 'meta' => ['path_suffix' => '']],
                    ['type_key' => 'by_phone', 'label' => 'By Phone', 'meta' => ['path_suffix' => 'fetch_by_phone']],
                    ['type_key' => 'by_tracking', 'label' => 'By Tracking', 'meta' => ['path_suffix' => 'fetch_by_tid']],
                    ['type_key' => 'demographic', 'label' => 'Demographic', 'meta' => ['path_suffix' => '']],
                ],
            ],
            'verifyme_nin' => [
                'name' => 'VerifyMe (NIN)',
                'provider_identifier' => 'verifyme',
                'service_type' => 'nin_verification',
                'endpoint' => 'https://vapi.verifyme.ng/v1/verifications/identities/nin',
                'headers' => ['Content-Type' => 'application/json'],
                'config' => [],
                'price' => 250,
                'priority' => 2,
                'types' => [
                    ['type_key' => 'by_nin', 'label' => 'By NIN', 'meta' => ['path_suffix' => 'nin']],
                    ['type_key' => 'by_phone', 'label' => 'By Phone', 'meta' => ['path_suffix' => 'nin_phone']],
                    ['type_key' => 'demographic', 'label' => 'Demographic', 'meta' => ['path_suffix' => 'nin']],
                ],
            ],
            'youverify_nin' => [
                'name' => 'Youverify (NIN)',
                'provider_identifier' => 'youverify',
                'service_type' => 'nin_verification',
                'endpoint' => 'https://api.youverify.co/v2/api/identities',
                'headers' => ['Content-Type' => 'application/json'],
                'config' => [],
                'price' => 250,
                'priority' => 3,
                'types' => [
                    ['type_key' => 'by_nin', 'label' => 'By NIN', 'meta' => ['path_suffix' => 'nin']],
                    ['type_key' => 'vnin', 'label' => 'vNIN', 'meta' => ['path_suffix' => 'vnin']],
                    ['type_key' => 'demographic', 'label' => 'Demographic', 'meta' => ['path_suffix' => 'nin']],
                ],
            ],
            'dojah_nin' => [
                'name' => 'Dojah (NIN)',
                'provider_identifier' => 'dojah',
                'service_type' => 'nin_verification',
                'endpoint' => 'https://api.dojah.io/api/v1/kyc',
                'headers' => ['Content-Type' => 'application/json'],
                'config' => ['app_id' => '', 'app_key' => ''],
                'price' => 250,
                'priority' => 4,
                'types' => [
                    ['type_key' => 'by_nin', 'label' => 'By NIN', 'meta' => ['path_suffix' => 'nin']],
                    ['type_key' => 'by_phone', 'label' => 'By Phone', 'meta' => ['path_suffix' => 'nin/phone']],
                    ['type_key' => 'vnin', 'label' => 'vNIN', 'meta' => ['path_suffix' => 'vnin']],
                    ['type_key' => 'demographic', 'label' => 'Demographic', 'meta' => ['path_suffix' => 'nin']],
                ],
            ],
            'smileid_nin' => [
                'name' => 'Smile ID (NIN)',
                'provider_identifier' => 'smileid',
                'service_type' => 'nin_verification',
                'endpoint' => 'https://api.smileidentity.com/v1/id_verification',
                'headers' => ['Content-Type' => 'application/json'],
                'config' => ['partner_id' => ''],
                'price' => 300,
                'priority' => 5,
                'types' => [
                    ['type_key' => 'by_nin', 'label' => 'By NIN', 'meta' => ['path_suffix' => 'nin']],
                    ['type_key' => 'vnin', 'label' => 'vNIN', 'meta' => ['path_suffix' => 'vnin']],
                    ['type_key' => 'demographic', 'label' => 'Demographic', 'meta' => ['path_suffix' => 'nin']],
                ],
            ],
            'generic_sms' => [
                'name' => 'Generic SMS Gateway',
                'provider_identifier' => 'sms_generic',
                'service_type' => 'sms_gateway',
                'endpoint' => 'https://api.provider.com/sms/send',
                'headers' => ['Content-Type' => 'application/json'],
                'config' => [
                    'to_key' => 'to',
                    'message_key' => 'message',
                    'sender_key' => 'from',
                    'api_key_key' => 'api_key',
                    'payload' => [],
                ],
                'price' => 0,
                'priority' => 10,
                'types' => [],
            ],
            'vuvaa_nin' => [
                'name' => 'VUVAA (NIN)',
                'provider_identifier' => 'vuvaa',
                'service_type' => 'nin_verification',
                'endpoint' => 'https://api.vuvaa.com',
                'headers' => ['Content-Type' => 'application/json'],
                'config' => [
                    'username' => '',
                    'password' => '',
                    'encryption_key' => '',
                    'encryption_iv' => '',
                    'in_person_path' => 'in_person_verification',
                    'share_code_path' => 'share_code',
                    'requery_path' => 'requery',
                ],
                'price' => 200,
                'priority' => 1,
                'types' => [
                    ['type_key' => 'nin', 'label' => 'NIN (Standard)', 'meta' => []],
                    ['type_key' => 'selfie', 'label' => 'NIN (Selfie)', 'meta' => ['path_suffix' => 'in_person_verification']],
                    ['type_key' => 'share_code', 'label' => 'NIN (Share Code)', 'meta' => ['path_suffix' => 'share_code']],
                    ['type_key' => 'requery', 'label' => 'NIN (Requery)', 'meta' => ['path_suffix' => 'requery']],
                ],
            ],
        ];

        if (!isset($templates[$template])) {
            return back()->with('error', 'Unknown template.');
        }

        $t = $templates[$template];
        if (CustomApi::query()->where('name', $t['name'])->exists()) {
            return back()->with('error', 'Template already exists: ' . $t['name']);
        }

        $api = CustomApi::create([
            'name' => $t['name'],
            'provider_identifier' => $t['provider_identifier'],
            'service_type' => $t['service_type'],
            'endpoint' => $t['endpoint'],
            'headers' => $t['headers'],
            'config' => $t['config'],
            'price' => $t['price'],
            'priority' => $t['priority'],
            'status' => false,
            'timeout_seconds' => 60,
            'retry_count' => 0,
            'retry_delay_ms' => 0,
        ]);

        if (!empty($t['types']) && is_array($t['types'])) {
            foreach ($t['types'] as $type) {
                CustomApiVerificationType::create([
                    'custom_api_id' => $api->id,
                    'type_key' => $type['type_key'],
                    'label' => $type['label'],
                    'price' => $t['price'],
                    'status' => true,
                    'sort_order' => 0,
                    'meta' => $type['meta'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Template added. Configure keys and enable the provider.');
    }

    /**
     * Update the specified custom API.
     */
    public function update(Request $request, $id)
    {
        $api = CustomApi::findOrFail($id);
        
        $request->validate([
            'name'         => 'required|string|max:255|unique:custom_apis,name,'.$api->id,
            'provider_identifier' => 'nullable|string|max:100',
            'service_type' => 'required|string|max:100',
            'endpoint'     => 'required|url|max:255',
            'api_key'      => 'nullable|string|max:255',
            'secret_key'   => 'nullable|string|max:255',
            'headers'      => 'nullable|string',
            'config'       => 'nullable|string',
            'supported_modes' => 'nullable|array',
            'supported_modes.*' => 'string|max:50',
            'price'        => 'required|numeric|min:0',
            'priority'     => 'required|integer|min:1',
            'timeout_seconds' => 'nullable|integer|min:1|max:300',
            'retry_count' => 'nullable|integer|min:0|max:10',
            'retry_delay_ms' => 'nullable|integer|min:0|max:10000',
            'fee_type' => 'nullable|in:flat,percent',
            'fee_value' => 'nullable|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'service_code' => 'nullable|string|max:120',
            'credit_amount_path' => 'nullable|string|max:120',
        ]);

        $headersArray = [];
        if (!empty($request->headers)) {
            $parsed = json_decode((string)$request->headers, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $headersArray = $parsed;
            } else {
                return back()->with('error', __('errors.invalid_json'));
            }
        }

        $configArray = [];
        if (!empty($request->config)) {
            $parsed = json_decode((string)$request->config, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $configArray = $parsed;
            } else {
                return back()->with('error', __('errors.invalid_json'));
            }
        }

        if ($request->filled('fee_type')) {
            $configArray['fee_type'] = $request->string('fee_type')->toString();
        }
        if ($request->filled('fee_value')) {
            $configArray['fee_value'] = (float) $request->input('fee_value');
        }
        if ($request->filled('min_amount')) {
            $configArray['min_amount'] = (float) $request->input('min_amount');
        }
        if ($request->filled('max_amount')) {
            $configArray['max_amount'] = (float) $request->input('max_amount');
        }
        if ($request->filled('service_code')) {
            $configArray['service_code'] = $request->string('service_code')->toString();
        }
        if ($request->filled('credit_amount_path')) {
            $configArray['credit_amount_path'] = $request->string('credit_amount_path')->toString();
        }

        $api->update([
            'name'         => $request->name,
            'provider_identifier' => $request->provider_identifier,
            'service_type' => $request->service_type,
            'endpoint'     => $request->endpoint,
            'api_key'      => $request->api_key,
            'secret_key'   => $request->secret_key,
            'headers'      => $headersArray,
            'config'       => $configArray,
            'supported_modes' => $request->supported_modes ?? [],
            'price'        => $request->price,
            'priority'     => $request->priority,
            'status'       => $request->has('status'),
            'timeout_seconds' => $request->input('timeout_seconds', $api->timeout_seconds ?? 60),
            'retry_count' => $request->input('retry_count', $api->retry_count ?? 0),
            'retry_delay_ms' => $request->input('retry_delay_ms', $api->retry_delay_ms ?? 0),
        ]);

        return back()->with('success', 'Custom API configuration updated successfully.');
    }

    /**
     * Remove the specified custom API.
     */
    public function destroy($id)
    {
        $api = CustomApi::findOrFail($id);
        $api->delete();

        return back()->with('success', 'Custom API configuration removed.');
    }

    public function storeVerificationType(Request $request, $id)
    {
        $api = CustomApi::findOrFail($id);
        $request->validate([
            'type_key' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_\\-]+$/i'],
            'label' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'meta' => ['nullable', 'string'],
        ]);

        $meta = null;
        if ($request->filled('meta')) {
            $decoded = json_decode((string) $request->meta, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Meta must be valid JSON.');
            }
            $meta = $decoded;
        }

        CustomApiVerificationType::updateOrCreate(
            [
                'custom_api_id' => $api->id,
                'type_key' => $request->type_key,
            ],
            [
                'label' => $request->label,
                'price' => $request->price,
                'status' => $request->has('status'),
                'sort_order' => $request->input('sort_order', 0),
                'meta' => $meta,
            ]
        );

        return back()->with('success', 'Verification type saved.');
    }

    public function destroyVerificationType(Request $request, $id, $typeId)
    {
        $api = CustomApi::findOrFail($id);
        $type = CustomApiVerificationType::query()
            ->where('custom_api_id', $api->id)
            ->where('id', $typeId)
            ->firstOrFail();

        $type->delete();

        return back()->with('success', 'Verification type removed.');
    }
}

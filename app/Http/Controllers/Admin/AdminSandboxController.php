<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminSandboxController extends Controller
{
    public function index()
    {
        return view('admin.sandbox.index');
    }

    public function nin()
    {
        $providers = CustomApi::where('service_type', 'nin')->where('status', true)->orderBy('name')->get();
        return view('admin.sandbox.nin', compact('providers'));
    }

    public function verifyNin(Request $request)
    {
        $request->validate([
            'nin' => ['required', 'string', 'digits:11'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $endpoint = null;
        $apiKey = null;
        $headers = [];

        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
            if ($provider && $provider->status) {
                $endpoint = $provider->endpoint;
                $apiKey = $provider->api_key;
                $headers = is_array($provider->headers) ? $provider->headers : [];
            }
        }

        if (!$endpoint || !$apiKey) {
            $apiCenter = ApiCenter::first();
            if (!$apiCenter || !$apiCenter->dataverify_api_key) {
                return response()->json(['status' => false, 'message' => 'Service currently unavailable']);
            }

            $apiKey = $apiCenter->dataverify_api_key;
            $endpoint = 'http://dataverify.com.ng/developers/fetch_script_prices/index.php';
        }

        try {
            $http = Http::timeout(30);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->post($endpoint, [
                'api_key' => $apiKey,
                'nin' => $request->nin,
            ]);

            if (!$response->successful()) {
                return response()->json(['status' => false, 'message' => 'Provider error'], 502);
            }

            $data = $response->json();
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Sandbox request failed'], 500);
        }
    }

    public function bvn()
    {
        $providers = CustomApi::where('service_type', 'bvn_verification')->where('status', true)->orderBy('name')->get();
        return view('admin.sandbox.bvn', compact('providers'));
    }

    public function verifyBvn(Request $request)
    {
        $request->validate([
            'bvn' => ['required', 'string', 'digits:11'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $endpoint = null;
        $apiKey = null;
        $headers = [];

        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
            if ($provider && $provider->status) {
                $endpoint = $provider->endpoint;
                $apiKey = $provider->api_key;
                $headers = is_array($provider->headers) ? $provider->headers : [];
            }
        }

        $apiCenter = ApiCenter::first();
        if (!$endpoint || !$apiKey) {
            if (!$apiCenter || !$apiCenter->dataverify_api_key) {
                return response()->json(['status' => false, 'message' => 'Service currently unavailable']);
            }
            $apiKey = $apiCenter->dataverify_api_key;
            $endpoint = $apiCenter->dataverify_endpoint_bvn ?? 'http://dataverify.com.ng/api/bvn';
        }

        try {
            $http = Http::timeout(30);
            if (!empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            $response = $http->post($endpoint, [
                'api_key' => $apiKey,
                'bvn' => $request->bvn,
            ]);

            if (!$response->successful()) {
                return response()->json(['status' => false, 'message' => 'Provider error'], 502);
            }

            $data = $response->json();
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Sandbox request failed'], 500);
        }
    }
}


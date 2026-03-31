<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApi;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{
    /**
     * Display the Motor Insurance Index Page
     */
    public function motorIndex()
    {
        $insuranceProviders = CustomApi::where('service_type', 'insurance_motor')
                                ->where('status', true)
                                ->get();

        return view('services.insurance.motor', compact('insuranceProviders'));
    }

    /**
     * Proxy method to fetch VTpass Motor Options (Colors, Brands, etc.)
     */
    public function getMotorOptions(Request $request)
    {
        $type = $request->query('type'); // color, brand, state, engine-capacity, model, lga
        $id = $request->query('id'); // for model/{brandCode} or lga/{stateCode}

        $provider = CustomApi::where('service_type', 'insurance_motor')->where('status', true)->first();
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active Motor Insurance provider found.']);
        }

        $endpoint = "https://sandbox.vtpass.com/api"; // Default to sandbox base
        if ($type === 'variation') {
            $url = "{$endpoint}/service-variations?serviceID=ui-insure";
        } elseif (in_array($type, ['model', 'lga'])) {
            $url = "{$endpoint}/universal-insurance/options/{$type}/{$id}";
        } else {
            $url = "{$endpoint}/universal-insurance/options/{$type}";
        }

        try {
            $response = Http::withHeaders($provider->headers ?? [])->get($url);
            if ($response->successful()) {
                return response()->json([
                    'status' => true,
                    'data' => $response->json()['content'] ?? []
                ]);
            }
            return response()->json(['status' => false, 'message' => 'Failed to fetch options from provider.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Purchase Motor Insurance
     */
    public function buyMotorInsurance(Request $request)
    {
        $request->validate([
            'variation_code' => 'required',
            'amount' => 'required|numeric',
            'phone' => 'required|digits:11',
            'email' => 'required|email',
            'insured_name' => 'required|string',
            'plate_number' => 'required|string',
            'chasis_number' => 'required|string',
            'engine_capacity' => 'required',
            'vehicle_make' => 'required',
            'vehicle_model' => 'required',
            'vehicle_color' => 'required',
            'year_of_make' => 'required|digits:4',
            'state' => 'required',
            'lga' => 'required',
            'api_provider_id' => 'nullable|exists:custom_apis,id'
        ]);

        $user = Auth::user();
        $totalAmount = $request->amount;

        if ($request->filled('api_provider_id')) {
            $provider = CustomApi::find($request->api_provider_id);
        } else {
            $provider = CustomApi::where('service_type', 'insurance_motor')->where('status', true)->first();
        }

        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'Insurance provider not available.']);
        }

        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $totalAmount, 'Motor Insurance', 'INS');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        $requestId = now()->format('YmdHi') . bin2hex(random_bytes(3));

        try {
            $response = Http::withHeaders($provider->headers ?? [])
                ->timeout(60)
                ->post($provider->endpoint, [
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
                ]);

            if ($response->successful() && isset($response['code']) && $response['code'] === '000') {
                $data = $response->json();
                $wallet->markTransactionSuccess($debit['txId']);

                return response()->json([
                    'status' => true,
                    'message' => 'Insurance purchased successfully!',
                    'certUrl' => $data['certUrl'] ?? null,
                    'reference' => $requestId
                ]);
            } else {
                throw new \Exception('API Error: ' . ($response['response_description'] ?? 'Unknown Error'));
            }
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $totalAmount, 'Motor Insurance', $debit['txId']);
            return response()->json(['status' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiCenter;
use App\Services\VtuHubService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CustomApi;
use App\Models\VtuTransaction;
use App\Services\EpinCatalogService;
use App\Services\EducationEpinConsolidationService;

class VTUController extends Controller
{
    protected $vtuHub;

    public function __construct(VtuHubService $vtuHub)
    {
        $this->vtuHub = $vtuHub;
    }

    public function hubIndex()
    {
        $user = Auth::user();
        if ($user) {
            $user->load('balance');
        }

        $walletBalance = (float) (($user?->balance?->user_balance) ?? 0);

        $base = VtuTransaction::query();
        if ($user) {
            $base->where('user_id', $user->id);
        } else {
            $base->whereRaw('1 = 0');
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $monthSpend = (float) (clone $base)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('direction', 'debit')
            ->where('status', 'success')
            ->sum('total');

        $monthSuccessCount = (int) (clone $base)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'success')
            ->count();

        $pendingCount = (int) (clone $base)
            ->where('status', 'pending')
            ->count();

        $failedCount = (int) (clone $base)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'failed')
            ->count();

        $recentVtu = (clone $base)
            ->latest()
            ->take(8)
            ->get();

        $lastVtu = $recentVtu->first();

        return view('services.vtu.hub', compact(
            'walletBalance',
            'monthSpend',
            'monthSuccessCount',
            'pendingCount',
            'failedCount',
            'recentVtu',
            'lastVtu'
        ));
    }

    public function providers(Request $request, string $serviceType)
    {
        $catalog = (array) config('vtu_services.service_types', []);
        if (!isset($catalog[$serviceType])) {
            return response()->json(['status' => false, 'message' => 'Unknown service type.'], 404);
        }

        $serviceTypeCandidates = match ($serviceType) {
            'vtu_cable_tv' => ['vtu_cable_tv', 'cable_tv'],
            'vtu_electricity' => ['vtu_electricity', 'electricity_bills'],
            default => [$serviceType],
        };

        if ($serviceType === 'vtu_epin' && $request->filled('serviceID') && $request->filled('variation_code')) {
            $p = app(EpinCatalogService::class)->findByService(
                $request->string('serviceID')->toString(),
                $request->string('variation_code')->toString()
            );
            if ($p && isset($p['provider_service_types']) && is_array($p['provider_service_types']) && !empty($p['provider_service_types'])) {
                $serviceTypeCandidates = array_values(array_filter($p['provider_service_types'], fn ($v) => is_string($v) && $v !== ''));
            }
        }

        $providers = CustomApi::whereIn('service_type', $serviceTypeCandidates)
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->get()
            ->map(function ($p) {
                $cfg = is_array($p->config) ? $p->config : [];
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'fee_type' => $cfg['fee_type'] ?? $cfg['commission_type'] ?? 'flat',
                    'fee_value' => (float) ($cfg['fee_value'] ?? $cfg['commission_value'] ?? 0),
                    'min_amount' => isset($cfg['min_amount']) ? (float) $cfg['min_amount'] : null,
                    'max_amount' => isset($cfg['max_amount']) ? (float) $cfg['max_amount'] : null,
                    'service_code' => $cfg['service_code'] ?? null,
                ];
            })
            ->values();

        return response()->json(['status' => true, 'providers' => $providers]);
    }

    public function airtimeIndex()
    {
        return view('services.vtu.airtime');
    }

    public function dataIndex()
    {
        // Fetch all plans and group by network
        $plans = DB::table('price_list')->get()->groupBy(function($item) {
            return strtoupper($item->network);
        });

        return view('services.vtu.data', compact('plans'));
    }

    public function cableIndex()
    {
        return view('services.vtu.cable');
    }

    public function electricityIndex()
    {
        return view('services.vtu.electricity');
    }

    public function validateElectricity(Request $request)
    {
        $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
            'serviceID' => ['required', 'string'],
            'variation_code' => ['required', 'string'],
            'meter_number' => ['required', 'string'],
        ]);

        $payload = [
            'serviceID' => $request->serviceID,
            'variation_code' => $request->variation_code,
            'meter_number' => $request->meter_number,
        ];

        $res = $this->vtuHub->validateCustomer('vtu_electricity', $payload, $request->input('provider_id'));
        if (!$res['status']) {
            return response()->json($res, 422);
        }

        $token = bin2hex(random_bytes(16));
        $request->session()->put('vtu_electricity_validation', [
            'token' => $token,
            'serviceID' => $request->serviceID,
            'variation_code' => $request->variation_code,
            'meter_number' => $request->meter_number,
            'provider_id' => $request->input('provider_id'),
            'customer' => $res['customer'] ?? null,
            'at' => now()->timestamp,
        ]);

        return response()->json([
            'status' => true,
            'message' => $res['message'] ?? 'Meter validated.',
            'validation_token' => $token,
            'customer' => $res['customer'] ?? null,
        ]);
    }

    public function airtimeToCashIndex()
    {
        return view('services.vtu.airtime_to_cash');
    }

    public function submitAirtimeToCash(Request $request)
    {
        $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
            'network' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:200'],
            'phone' => ['required', 'string', 'digits:11'],
            'bank_code' => ['required', 'string'],
            'account_number' => ['required', 'string', 'digits:10'],
            'account_name' => ['nullable', 'string', 'max:120'],
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_airtime_to_cash',
            'amount' => (float) $request->amount,
            'order_type' => 'Airtime to Cash',
            'tx_prefix' => 'A2C',
            'provider_id' => $request->input('provider_id'),
            'payload' => [
                'network' => $request->network,
                'amount' => (float) $request->amount,
                'phone' => $request->phone,
                'bank_code' => $request->bank_code,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
            ],
        ]);

        return response()->json($result);
    }

    public function internetIndex()
    {
        return view('services.vtu.internet');
    }

    public function buyInternet(Request $request)
    {
        $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
            'serviceID' => ['required', 'string'],
            'variation_code' => ['required', 'string'],
            'customer_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
            'phone' => ['required', 'string', 'digits:11'],
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_internet',
            'amount' => (float) $request->amount,
            'order_type' => 'Internet Subscription',
            'tx_prefix' => 'NET',
            'provider_id' => $request->input('provider_id'),
            'payload' => [
                'serviceID' => $request->serviceID,
                'variation_code' => $request->variation_code,
                'customer_id' => $request->customer_id,
                'amount' => (float) $request->amount,
                'phone' => $request->phone,
            ],
        ]);

        return response()->json($result);
    }

    public function bettingIndex()
    {
        return view('services.vtu.betting');
    }

    public function fundBetting(Request $request)
    {
        $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
            'serviceID' => ['required', 'string'],
            'customer_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
            'phone' => ['required', 'string', 'digits:11'],
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_betting',
            'amount' => (float) $request->amount,
            'order_type' => 'Betting Funding',
            'tx_prefix' => 'BET',
            'provider_id' => $request->input('provider_id'),
            'payload' => [
                'serviceID' => $request->serviceID,
                'customer_id' => $request->customer_id,
                'amount' => (float) $request->amount,
                'phone' => $request->phone,
            ],
        ]);

        return response()->json($result);
    }

    public function epinIndex()
    {
        return view('services.vtu.epin');
    }

    public function buyEpin(Request $request)
    {
        app(EducationEpinConsolidationService::class)->ensureConsolidated();

        $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
            'serviceID' => ['required', 'string'],
            'variation_code' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'amount' => ['required', 'numeric', 'min:100'],
            'phone' => ['required', 'string', 'digits:11'],
        ]);

        $serviceID = $request->string('serviceID')->toString();
        $variation = $request->string('variation_code')->toString();
        $product = app(EpinCatalogService::class)->findByService($serviceID, $variation);

        $amount = $product ? (float) ($product['amount'] ?? $request->amount) : (float) $request->amount;
        $quantity = $product ? (int) ($product['quantity'] ?? $request->quantity) : (int) $request->quantity;
        $orderType = $product ? (string) ($product['order_type'] ?? 'ePIN Purchase') : 'ePIN Purchase';
        $txPrefix = $product ? (string) ($product['tx_prefix'] ?? 'EPIN') : 'EPIN';
        $providerServiceTypes = $product && is_array($product['provider_service_types'] ?? null) ? $product['provider_service_types'] : ['vtu_epin'];

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_epin',
            'amount' => $amount,
            'order_type' => $orderType,
            'tx_prefix' => $txPrefix,
            'provider_id' => $request->input('provider_id'),
            'provider_service_types' => $providerServiceTypes,
            'payload' => [
                'serviceID' => $serviceID,
                'variation_code' => $variation,
                'quantity' => $quantity,
                'amount' => $amount,
                'phone' => $request->phone,
            ],
        ]);

        return response()->json($result);
    }

    public function rechargePrintingIndex()
    {
        return view('services.vtu.recharge_printing');
    }

    public function generateRechargePins(Request $request)
    {
        $request->validate([
            'pin_type' => ['required', 'string', 'in:airtime,data'],
            'network' => ['required', 'string', 'in:01,02,03,04'],
            'amount' => ['required_if:pin_type,airtime', 'numeric', 'in:100,200,500'],
            'data_plan' => ['required_if:pin_type,data', 'string'],
            'data_plan_price' => ['required_if:pin_type,data', 'numeric'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        
        $unitCost = $request->pin_type === 'data' ? (float) $request->data_plan_price : (float) $request->amount;
        $totalCost = $unitCost * (int) $request->quantity;

        // Check user balance
        $balance = \Illuminate\Support\Facades\DB::table('account_balances')->where('user_id', $user->id)->first();
        if (!$balance || $balance->user_balance < $totalCost) {
            return response()->json(['status' => false, 'message' => 'Insufficient wallet balance.']);
        }

        // Get API Keys from Admin Settings
        $apiCenter = \Illuminate\Support\Facades\DB::table('api_centers')->first();
        $userId = $apiCenter->clubkonnect_userid ?? null;
        $apiKey = $apiCenter->clubkonnect_apikey ?? null;

        if (!$userId || !$apiKey) {
            return response()->json(['status' => false, 'message' => 'Recharge Card Printing is not configured by the administrator yet.']);
        }

        $requestId = 'RCP_' . time() . '_' . \Illuminate\Support\Str::random(6);

        if ($request->pin_type === 'data') {
            // Data PIN API Call
            $response = \Illuminate\Support\Facades\Http::get('https://www.nellobytesystems.com/APIDatabundleEPINV1.asp', [
                'UserID' => $userId,
                'APIKey' => $apiKey,
                'MobileNetwork' => $request->network,
                'DataPlan' => $request->data_plan,
                'Quantity' => $request->quantity,
                'RequestID' => $requestId,
            ]);
            $data = $response->json();
            
            // Check for errors
            if (isset($data['status']) && in_array($data['status'], ['API_ERROR', 'INVALID_CREDENTIALS', 'MISSING_CREDENTIALS', 'INVALID_DATAPLAN', 'MISSING_DATAPLAN'])) {
                 return response()->json(['status' => false, 'message' => 'Provider error: ' . ($data['status'] ?? 'Unknown')]);
            }

            if (isset($data['status']) && $data['status'] === 'ORDER_RECEIVED') {
                // Deduct balance
                \Illuminate\Support\Facades\DB::table('account_balances')->where('user_id', $user->id)->update([
                    'user_balance' => $balance->user_balance - $totalCost
                ]);

                // Log Transaction
                \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'reference' => $requestId,
                    'type' => 'Data PIN Printing',
                    'amount' => $totalCost,
                    'status' => 'pending',
                    'description' => "Ordered {$request->quantity} Data PINs (OrderID: {$data['orderid']})",
                    'provider_reference' => $data['orderid'] ?? null,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Data PIN order received successfully. They are being processed.',
                    'order_id' => $data['orderid'],
                    'async' => true
                ]);
            }
        } else {
            // Airtime PIN API Call
            $response = \Illuminate\Support\Facades\Http::get('https://www.nellobytesystems.com/APIEPINV1.asp', [
                'UserID' => $userId,
                'APIKey' => $apiKey,
                'MobileNetwork' => $request->network,
                'Value' => $request->amount,
                'Quantity' => $request->quantity,
                'RequestID' => $requestId,
            ]);
            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'API_ERROR') {
                 return response()->json(['status' => false, 'message' => 'Provider error: ' . ($data['msg'] ?? 'Unknown')]);
            }

            if (isset($data['TXN_EPIN']) && is_array($data['TXN_EPIN'])) {
                // Deduct balance
                \Illuminate\Support\Facades\DB::table('account_balances')->where('user_id', $user->id)->update([
                    'user_balance' => $balance->user_balance - $totalCost
                ]);

                // Log Transaction
                \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'reference' => $requestId,
                    'type' => 'Recharge Card Printing',
                    'amount' => $totalCost,
                    'status' => 'success',
                    'description' => "Printed {$request->quantity} x ₦{$request->amount} Recharge Cards",
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Pins generated successfully.',
                    'pins' => $data['TXN_EPIN'],
                    'async' => false
                ]);
            }
        }

        return response()->json(['status' => false, 'message' => 'Failed to generate pins. Please try again or contact support.']);
    }

    public function queryRechargeOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string'
        ]);

        $apiCenter = \Illuminate\Support\Facades\DB::table('api_centers')->first();
        $userId = $apiCenter->clubkonnect_userid ?? null;
        $apiKey = $apiCenter->clubkonnect_apikey ?? null;

        if (!$userId || !$apiKey) {
            return response()->json(['status' => false, 'message' => 'Provider not configured']);
        }

        $response = \Illuminate\Support\Facades\Http::get('https://www.nellobytesystems.com/APIQueryV1.asp', [
            'UserID' => $userId,
            'APIKey' => $apiKey,
            'OrderID' => $request->order_id,
        ]);

        $data = $response->json();

        if (isset($data['TXN_EPIN_DATABUNDLE']) && is_array($data['TXN_EPIN_DATABUNDLE'])) {
            // Update transaction status
            \App\Models\Transaction::where('provider_reference', $request->order_id)->update(['status' => 'success']);
            
            return response()->json([
                'status' => true,
                'pins' => $data['TXN_EPIN_DATABUNDLE']
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Order is still processing or failed'
        ]);
    }

    public function fetchDatabundlePlans()
    {
        $apiCenter = \Illuminate\Support\Facades\DB::table('api_centers')->first();
        $userId = $apiCenter->clubkonnect_userid ?? null;

        if (!$userId) {
            return response()->json(['status' => false, 'message' => 'Provider not configured']);
        }

        // Cache the plans for 1 hour to prevent hitting the provider too often
        $plans = \Illuminate\Support\Facades\Cache::remember('clubkonnect_databundle_plans', 3600, function () use ($userId) {
            $response = \Illuminate\Support\Facades\Http::get('https://www.nellobytesystems.com/APIDatabundlePlansV2.asp', [
                'UserID' => $userId,
            ]);
            return $response->json();
        });

        return response()->json([
            'status' => true,
            'data' => $plans
        ]);
    }

    // ── Education Hub (WAEC/NECO/NABTEB/JAMB) ────────────────

    public function waecIndex()
    {
        $waecProviders = CustomApi::where('service_type', 'education_waec')->where('status', true)->get();
        return view('services.education.waec', compact('waecProviders'));
    }

    public function buyWaecPin(Request $request)
    {
        app(EducationEpinConsolidationService::class)->ensureConsolidated();

        $request->validate([
            'phone' => ['required', 'string', 'digits:11'],
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
        ]);

        $amount = 950;
        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_epin',
            'amount' => $amount,
            'order_type' => 'WAEC Result Checker',
            'tx_prefix' => 'WAEC',
            'provider_id' => $request->input('provider_id'),
            'provider_service_types' => ['vtu_epin', 'education_waec'],
            'payload' => [
                'serviceID' => 'waec',
                'variation_code' => 'waecdirect',
                'phone' => $request->phone,
                'quantity' => 1,
                'amount' => $amount,
            ]
        ]);

        return response()->json($result);
    }

    public function waecRegistrationIndex()
    {
        $waecProviders = CustomApi::where('service_type', 'education_waec_registration')->where('status', true)->get();
        return view('services.education.waec_registration', compact('waecProviders'));
    }

    public function buyWaecRegistrationPin(Request $request)
    {
        app(EducationEpinConsolidationService::class)->ensureConsolidated();

        $request->validate([
            'phone' => ['required', 'string', 'digits:11'],
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
        ]);

        $amount = 18500;
        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_epin',
            'amount' => $amount,
            'order_type' => 'WAEC Registration PIN',
            'tx_prefix' => 'WAECREG',
            'provider_id' => $request->input('provider_id'),
            'provider_service_types' => ['vtu_epin', 'education_waec_registration'],
            'payload' => [
                'serviceID' => 'waec-registration',
                'variation_code' => 'waec-registration',
                'phone' => $request->phone,
                'quantity' => 1,
                'amount' => $amount,
            ]
        ]);

        return response()->json($result);
    }

    public function necoIndex()
    {
        $providers = CustomApi::where('service_type', 'education_neco')->where('status', true)->get();
        return view('services.education.neco', compact('providers'));
    }

    public function buyNecoPin(Request $request)
    {
        app(EducationEpinConsolidationService::class)->ensureConsolidated();

        $request->validate([
            'phone' => ['required', 'string', 'digits:11'],
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
        ]);

        $amount = 950;
        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_epin',
            'amount' => $amount,
            'order_type' => 'NECO Result Checker',
            'tx_prefix' => 'NECO',
            'provider_id' => $request->input('provider_id'),
            'provider_service_types' => ['vtu_epin', 'education_neco'],
            'payload' => [
                'serviceID' => 'neco',
                'variation_code' => 'neco-direct',
                'phone' => $request->phone,
                'quantity' => 1,
                'amount' => $amount,
            ]
        ]);

        return response()->json($result);
    }

    public function nabtebIndex()
    {
        $providers = CustomApi::where('service_type', 'education_nabteb')->where('status', true)->get();
        return view('services.education.nabteb', compact('providers'));
    }

    public function buyNabtebPin(Request $request)
    {
        app(EducationEpinConsolidationService::class)->ensureConsolidated();

        $request->validate([
            'phone' => ['required', 'string', 'digits:11'],
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
        ]);

        $amount = 950;
        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_epin',
            'amount' => $amount,
            'order_type' => 'NABTEB Result Checker',
            'tx_prefix' => 'NABTEB',
            'provider_id' => $request->input('provider_id'),
            'provider_service_types' => ['vtu_epin', 'education_nabteb'],
            'payload' => [
                'serviceID' => 'nabteb',
                'variation_code' => 'nabteb-direct',
                'phone' => $request->phone,
                'quantity' => 1,
                'amount' => $amount,
            ]
        ]);

        return response()->json($result);
    }

    public function jambIndex()
    {
        $providers = CustomApi::where('service_type', 'education_jamb')->where('status', true)->get();
        return view('services.education.jamb', compact('providers'));
    }

    public function buyJambPin(Request $request)
    {
        app(EducationEpinConsolidationService::class)->ensureConsolidated();

        $request->validate([
            'phone' => ['required', 'string', 'digits:11'],
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
        ]);

        $amount = 4700;
        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_epin',
            'amount' => $amount,
            'order_type' => 'JAMB Profile/PIN',
            'tx_prefix' => 'JAMB',
            'provider_id' => $request->input('provider_id'),
            'provider_service_types' => ['vtu_epin', 'education_jamb'],
            'payload' => [
                'serviceID' => 'jamb',
                'variation_code' => 'utme',
                'phone' => $request->phone,
                'quantity' => 1,
                'amount' => $amount,
            ]
        ]);

        return response()->json($result);
    }

    // ── Regular VTU Methods ────────────────

    public function buyAirtime(Request $request)
    {
        $request->validate([
            'network' => 'required|string',
            'amount' => 'required|numeric|min:50',
            'phone' => 'required|string|digits:11',
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_airtime',
            'amount' => $request->amount,
            'order_type' => 'Airtime Purchase',
            'tx_prefix' => 'VTU',
            'payload' => [
                'network' => $request->network,
                'amount' => $request->amount,
                'phone' => $request->phone,
            ]
        ]);

        return response()->json($result);
    }

    public function buyData(Request $request)
    {
        $request->validate([
            'network' => 'required|string',
            'plan_id' => 'required|string',
            'amount' => 'required|numeric',
            'phone' => 'required|string|digits:11',
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_data',
            'amount' => $request->amount,
            'order_type' => 'Data Purchase',
            'tx_prefix' => 'DATA',
            'payload' => [
                'network' => $request->network,
                'plan_id' => $request->plan_id,
                'phone' => $request->phone,
            ]
        ]);

        return response()->json($result);
    }

    public function buyCable(Request $request)
    {
        $request->validate([
            'serviceID' => 'required|string', // e.g., dstv, gotv, startimes
            'variation_code' => 'required|string',
            'smart_card_number' => 'required|string',
            'amount' => 'required|numeric',
            'phone' => 'required|string|digits:11',
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_cable_tv',
            'amount' => $request->amount,
            'order_type' => 'Cable TV Subscription',
            'tx_prefix' => 'CABLE',
            'payload' => [
                'serviceID' => $request->serviceID,
                'variation_code' => $request->variation_code,
                'smart_card_number' => $request->smart_card_number,
                'phone' => $request->phone,
            ]
        ]);

        return response()->json($result);
    }

    public function buyElectricity(Request $request)
    {
        $request->validate([
            'serviceID' => 'required|string', // e.g., ikeja-electric, eedc
            'variation_code' => 'required|string', // prepaid/postpaid
            'meter_number' => 'required|string',
            'amount' => 'required|numeric|min:500',
            'phone' => 'required|string|digits:11',
            'provider_id' => ['nullable', 'integer', 'exists:custom_apis,id'],
            'validation_token' => ['required', 'string'],
        ]);

        $v = (array) $request->session()->get('vtu_electricity_validation', []);
        if (
            ($v['token'] ?? null) !== $request->validation_token ||
            ($v['serviceID'] ?? null) !== $request->serviceID ||
            ($v['variation_code'] ?? null) !== $request->variation_code ||
            ($v['meter_number'] ?? null) !== $request->meter_number
        ) {
            return response()->json(['status' => false, 'message' => 'Please verify meter before payment.'], 422);
        }

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_electricity',
            'amount' => $request->amount,
            'order_type' => 'Electricity Bill',
            'tx_prefix' => 'ELEC',
            'provider_id' => $request->input('provider_id'),
            'payload' => [
                'serviceID' => $request->serviceID,
                'variation_code' => $request->variation_code,
                'meter_number' => $request->meter_number,
                'phone' => $request->phone,
            ]
        ]);

        if (($result['status'] ?? false) === true) {
            $request->session()->forget('vtu_electricity_validation');
        }

        return response()->json($result);
    }
}

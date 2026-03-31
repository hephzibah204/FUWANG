<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VtuHubService;
use Illuminate\Http\Request;

class VtuApiController extends Controller
{
    protected $vtuHub;

    public function __construct(VtuHubService $vtuHub)
    {
        $this->vtuHub = $vtuHub;
    }

    public function airtime(Request $request)
    {
        $request->validate([
            'network' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:50'],
            'phone' => ['required', 'string', 'digits:11'],
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_airtime',
            'amount' => (float) $request->amount,
            'order_type' => 'Airtime Top-up',
            'tx_prefix' => 'AIR',
            'payload' => [
                'network' => $request->network,
                'amount' => (float) $request->amount,
                'phone' => $request->phone,
            ],
        ]);

        if (!$result['status']) {
            return response()->json($result, 400);
        }
        return response()->json($result);
    }

    public function data(Request $request)
    {
        $request->validate([
            'network' => ['required', 'string'],
            'plan_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:50'],
            'phone' => ['required', 'string', 'digits:11'],
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_data',
            'amount' => (float) $request->amount,
            'order_type' => 'Data Top-up',
            'tx_prefix' => 'DAT',
            'payload' => [
                'network' => $request->network,
                'plan_id' => $request->plan_id,
                'amount' => (float) $request->amount,
                'phone' => $request->phone,
            ],
        ]);

        if (!$result['status']) {
            return response()->json($result, 400);
        }
        return response()->json($result);
    }

    public function cable(Request $request)
    {
        $request->validate([
            'provider' => ['required', 'string'],
            'smartcard_number' => ['required', 'string'],
            'plan_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_cable_tv',
            'amount' => (float) $request->amount,
            'order_type' => 'Cable TV Subscription',
            'tx_prefix' => 'CAB',
            'payload' => [
                'provider' => $request->provider,
                'smartcard_number' => $request->smartcard_number,
                'plan_id' => $request->plan_id,
                'amount' => (float) $request->amount,
            ],
        ]);

        if (!$result['status']) {
            return response()->json($result, 400);
        }
        return response()->json($result);
    }

    public function electricity(Request $request)
    {
        $request->validate([
            'provider' => ['required', 'string'],
            'meter_number' => ['required', 'string'],
            'meter_type' => ['required', 'string', 'in:prepaid,postpaid'],
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        $result = $this->vtuHub->processRequest([
            'service_type' => 'vtu_electricity',
            'amount' => (float) $request->amount,
            'order_type' => 'Electricity Payment',
            'tx_prefix' => 'ELE',
            'payload' => [
                'provider' => $request->provider,
                'meter_number' => $request->meter_number,
                'meter_type' => $request->meter_type,
                'amount' => (float) $request->amount,
            ],
        ]);

        if (!$result['status']) {
            return response()->json($result, 400);
        }
        return response()->json($result);
    }
}

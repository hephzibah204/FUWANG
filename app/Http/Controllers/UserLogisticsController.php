<?php

namespace App\Http\Controllers;

use App\Models\LogisticsRequest;
use App\Models\SystemSetting;
use App\Services\WalletService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserLogisticsController extends Controller
{
    /**
     * User's Logistics Dashboard
     */
    public function dashboard()
    {
        $myShipments = LogisticsRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => LogisticsRequest::where('user_id', Auth::id())->count(),
            'active' => LogisticsRequest::where('user_id', Auth::id())->whereIn('status', ['processing', 'in_transit', 'out_for_delivery'])->count(),
            'delivered' => LogisticsRequest::where('user_id', Auth::id())->where('status', 'delivered')->count(),
        ];

        return view('services.logistics.dashboard', compact('myShipments', 'stats'));
    }

    /**
     * Booking Form
     */
    public function book()
    {
        $logisticsPricing = [
            'base' => (float) SystemSetting::get('logistics_base_cost', 1500),
            'weight_multiplier' => (float) SystemSetting::get('logistics_weight_multiplier', 500),
            'standard_multiplier' => (float) SystemSetting::get('logistics_std_mult', 1),
            'express_multiplier' => (float) SystemSetting::get('logistics_exp_mult', 1.5),
            'overnight_multiplier' => (float) SystemSetting::get('logistics_ovn_mult', 2),
        ];

        return view('services.logistics.book', compact('logisticsPricing'));
    }

    /**
     * Store Shipment
     */
    public function store(Request $request)
    {
        $request->validate([
            'sender_name'      => 'required|string|max:100',
            'sender_address'   => 'required|string|max:255',
            'recipient_name'   => 'required|string|max:100',
            'recipient_address'=> 'required|string|max:255',
            'weight'           => 'required|numeric|min:0.1',
            'description'      => 'required|string|max:255',
            'delivery_type'    => 'required|string|in:standard,express,overnight',
        ]);

        $base = SystemSetting::get('logistics_base_cost', 1500) + (ceil($request->weight) * SystemSetting::get('logistics_weight_multiplier', 500));
        $multipliers = [
            'standard' => (float) SystemSetting::get('logistics_std_mult', 1), 
            'express' => (float) SystemSetting::get('logistics_exp_mult', 1.5), 
            'overnight' => (float) SystemSetting::get('logistics_ovn_mult', 2)
        ];
        $cost = $base * ($multipliers[$request->delivery_type] ?? 1);

        // Wallet deduction
        $wallet = app(WalletService::class);
        $debit = $wallet->debit(Auth::user(), $cost, 'Logistics/Post Office - ' . $request->description);

        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        // Persist logistics request
        $shipment = LogisticsRequest::create([
            'user_id' => Auth::id(),
            'sender_name' => $request->sender_name,
            'sender_address' => $request->sender_address,
            'recipient_name' => $request->recipient_name,
            'recipient_address' => $request->recipient_address,
            'weight' => $request->weight,
            'description' => $request->description,
            'delivery_type' => $request->delivery_type,
            'amount' => $cost,
            'tracking_id' => 'NXS-' . strtoupper(bin2hex(random_bytes(3))),
            'status' => 'processing',
        ]);

        // Generate Waybill PDF
        $waybillPath = $this->generateWaybillPdf($shipment);
        $shipment->update(['waybill_path' => $waybillPath]);

        return response()->json([
            'status'      => true,
            'message'     => 'Shipment booked successfully!',
            'tracking_id' => $shipment->tracking_id,
            'cost'        => number_format($cost, 2),
            'balance'     => number_format($debit['newBalance'], 2),
            'eta'         => match($request->delivery_type) {
                'express'   => '1–2 business days',
                'overnight' => 'Next day delivery',
                default     => '3–5 business days',
            },
            'waybill_url' => Storage::url($waybillPath),
        ]);
    }

    /**
     * Waybill Generator
     */
    private function generateWaybillPdf(LogisticsRequest $shipment)
    {
        $data = [
            'reference' => $shipment->tracking_id,
            'date'      => $shipment->created_at->format('F d, Y - H:i'),
            'sender_name' => $shipment->sender_name,
            'sender_address' => $shipment->sender_address,
            'recipient_name' => $shipment->recipient_name,
            'recipient_address' => $shipment->recipient_address,
            'description' => $shipment->description,
            'weight' => $shipment->weight,
            'delivery_type' => $shipment->delivery_type,
        ];

        $pdf = Pdf::loadView('pdf.waybill', $data);
        $path = 'logistics/waybills/' . $shipment->tracking_id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}

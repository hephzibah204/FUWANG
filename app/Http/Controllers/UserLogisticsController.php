<?php

namespace App\Http\Controllers;

use App\Models\LogisticsRequest;
use App\Models\SystemSetting;
use App\Notifications\NewLogisticsOrderAlert;
use App\Services\Logistics\LogisticsPricingEngine;
use App\Services\WalletService;
use App\Support\NigeriaLocations;
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
        $user = Auth::user();

        // 1. Sent Shipments (user created/sent)
        $myShipments = LogisticsRequest::where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'sent_page');

        // 2. Incoming Packages (fulfillment packages addressed to this user for pickup/delivery)
        $incomingPackages = LogisticsRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('recipient_name', 'LIKE', "%{$user->fullname}%");
                if (!empty($user->number)) {
                    $q->orWhere('recipient_name', 'LIKE', "%{$user->number}%");
                }
                if (!empty($user->email)) {
                    $q->orWhere('recipient_name', 'LIKE', "%{$user->email}%");
                }
            })
            ->where('user_id', '!=', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'incoming_page');

        $readyForCollection = LogisticsRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('recipient_name', 'LIKE', "%{$user->fullname}%");
                if (!empty($user->number)) {
                    $q->orWhere('recipient_name', 'LIKE', "%{$user->number}%");
                }
                if (!empty($user->email)) {
                    $q->orWhere('recipient_name', 'LIKE', "%{$user->email}%");
                }
            })
            ->where('user_id', '!=', $user->id)
            ->whereIn('status', ['processing', 'in_transit', 'out_for_delivery', 'arrived_at_center', 'ready_for_collection', 'awaiting_pickup'])
            ->count();

        $stats = [
            'total' => LogisticsRequest::where('user_id', $user->id)->count(),
            'active' => LogisticsRequest::where('user_id', $user->id)->whereIn('status', ['processing', 'in_transit', 'out_for_delivery', 'ready_for_collection', 'awaiting_pickup'])->count(),
            'delivered' => LogisticsRequest::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'incoming_ready' => $readyForCollection,
        ];

        return view('services.logistics.dashboard', compact('myShipments', 'incomingPackages', 'stats'));
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

        $nigeriaStates = NigeriaLocations::stateNames();

        return view('services.logistics.book', compact('logisticsPricing', 'nigeriaStates'));
    }

    /**
     * Store Shipment
     */
    public function store(Request $request)
    {
        $states = NigeriaLocations::stateNames();
        $request->validate([
            'sender_name'      => 'required|string|max:100',
            'sender_state'     => ['required', 'string', \Illuminate\Validation\Rule::in($states)],
            'sender_address'   => 'nullable|string|max:255',
            'recipient_name'   => 'required|string|max:100',
            'recipient_phone'  => 'required|string|max:50',
            'recipient_email'  => 'nullable|email|max:255',
            'recipient_state'  => ['required', 'string', \Illuminate\Validation\Rule::in($states)],
            'recipient_address'=> 'nullable|string|max:255',
            'pickup_method'    => 'required|string|in:center_dropoff,home_pickup',
            'delivery_method'  => 'required|string|in:home_delivery,center_pickup',
            'pickup_center_id' => 'nullable|string',
            'dropoff_center_id'=> 'nullable|string',
            'weight'           => 'required|numeric|min:0.1',
            'description'      => 'required|string|max:255',
            'delivery_type'    => 'required|string|in:standard,express,overnight,same_day',
        ]);

        if ($request->pickup_method === 'center_dropoff' && ! $request->filled('pickup_center_id')) {
            return response()->json(['status' => false, 'message' => 'Pickup center is required for center drop-off.'], 422);
        }
        if ($request->pickup_method === 'home_pickup' && ! $request->filled('sender_address')) {
            return response()->json(['status' => false, 'message' => 'Sender address is required for home pickup.'], 422);
        }
        if ($request->delivery_method === 'center_pickup' && ! $request->filled('dropoff_center_id')) {
            return response()->json(['status' => false, 'message' => 'Drop-off center is required for center pickup.'], 422);
        }
        if ($request->delivery_method === 'home_delivery' && ! $request->filled('recipient_address')) {
            return response()->json(['status' => false, 'message' => 'Recipient address is required for home delivery.'], 422);
        }

        $quote = app(LogisticsPricingEngine::class)->quote($request->only([
            'sender_state',
            'recipient_state',
            'pickup_method',
            'delivery_method',
            'pickup_center_id',
            'dropoff_center_id',
            'sender_address',
            'recipient_address',
            'weight',
            'delivery_type',
            'package_length_cm',
            'package_width_cm',
            'package_height_cm',
        ]), ['require_geocode' => true]);
        if (! ($quote['ok'] ?? false)) {
            return response()->json([
                'status' => false,
                'message' => (string) ($quote['message'] ?? 'Unable to calculate shipping price.'),
            ], 422);
        }
        $cost = (float) ($quote['total'] ?? 0);

        // Wallet deduction
        $wallet = app(WalletService::class);
        $debit = $wallet->debit(Auth::user(), $cost, 'Logistics/Post Office - ' . $request->description);

        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        // Parse center/shop prefixes
        $pickup_center_id = null;
        $pickup_shop_id = null;
        if ($request->filled('pickup_center_id')) {
            $parts = explode('_', $request->pickup_center_id);
            if ($parts[0] === 'shop') $pickup_shop_id = $parts[1];
            else if ($parts[0] === 'center') $pickup_center_id = $parts[1];
            else $pickup_center_id = $request->pickup_center_id; // fallback
        }

        $dropoff_center_id = null;
        $dropoff_shop_id = null;
        if ($request->filled('dropoff_center_id')) {
            $parts = explode('_', $request->dropoff_center_id);
            if ($parts[0] === 'shop') $dropoff_shop_id = $parts[1];
            else if ($parts[0] === 'center') $dropoff_center_id = $parts[1];
            else $dropoff_center_id = $request->dropoff_center_id; // fallback
        }

        // Persist logistics request
        $shipment = LogisticsRequest::create([
            'user_id' => Auth::id(),
            'sender_name' => $request->sender_name,
            'sender_address' => $request->sender_address,
            'recipient_name' => $request->recipient_name,
            'recipient_address' => $request->recipient_address,
            'recipient_phone' => $request->recipient_phone,
            'recipient_email' => $request->recipient_email,
            'sender_state' => $request->sender_state,
            'recipient_state' => $request->recipient_state,
            'pickup_method' => $request->pickup_method,
            'delivery_method' => $request->delivery_method,
            'pickup_center_id' => $pickup_center_id,
            'pickup_shop_id' => $pickup_shop_id,
            'dropoff_center_id' => $dropoff_center_id,
            'dropoff_shop_id' => $dropoff_shop_id,
            'distance_km' => $quote['distance_km'] ?? null,
            'weight' => $request->weight,
            'description' => $request->description,
            'delivery_type' => $request->delivery_type,
            'amount' => $cost,
            'price_breakdown' => $quote['breakdown'] ?? null,
            'tracking_id' => 'NXS-' . strtoupper(bin2hex(random_bytes(3))),
            'status' => 'processing',
            'last_status_updated_at' => now(),
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
                'same_day'  => 'Same-day delivery',
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
        $options = new \chillerlan\QRCode\QROptions([
            'version'      => \chillerlan\QRCode\Common\Version::AUTO,
            'outputType'   => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,
            'imageBase64'  => true,
            'eccLevel'     => \chillerlan\QRCode\Common\EccLevel::L,
            'addQuietzone' => false,
        ]);
        
        $qrcode = (new \chillerlan\QRCode\QRCode($options))->render($shipment->tracking_id);

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
            'qrcode' => $qrcode,
        ];

        $pdf = Pdf::loadView('pdf.waybill', $data);
        $path = 'logistics/waybills/' . $shipment->tracking_id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}

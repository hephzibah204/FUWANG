<?php

namespace App\Http\Controllers;

use App\Services\Logistics\LogisticsPricingEngine;
use App\Support\NigeriaLocations;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LogisticsPricingController extends Controller
{
    public function quote(Request $request, LogisticsPricingEngine $engine)
    {
        $states = NigeriaLocations::stateNames();

        $validated = $request->validate([
            'sender_state' => ['required', 'string', Rule::in($states)],
            'recipient_state' => ['required', 'string', Rule::in($states)],
            'pickup_method' => ['required', 'string', Rule::in(['center_dropoff', 'home_pickup'])],
            'delivery_method' => ['required', 'string', Rule::in(['home_delivery', 'center_pickup'])],
            'pickup_center_id' => ['nullable', 'integer'],
            'dropoff_center_id' => ['nullable', 'integer'],
            'sender_address' => ['nullable', 'string', 'max:255'],
            'recipient_address' => ['nullable', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0.1'],
            'package_length_cm' => ['nullable', 'numeric', 'min:0'],
            'package_width_cm' => ['nullable', 'numeric', 'min:0'],
            'package_height_cm' => ['nullable', 'numeric', 'min:0'],
            'delivery_type' => ['required', 'string', Rule::in(['standard', 'express', 'overnight', 'same_day'])],
        ]);

        if ($validated['pickup_method'] === 'center_dropoff' && empty($validated['pickup_center_id'])) {
            return response()->json(['status' => false, 'message' => 'Pickup center is required for center drop-off.'], 422);
        }
        if ($validated['pickup_method'] === 'home_pickup' && empty($validated['sender_address'])) {
            return response()->json(['status' => false, 'message' => 'Sender address is required for home pickup.'], 422);
        }
        if ($validated['delivery_method'] === 'center_pickup' && empty($validated['dropoff_center_id'])) {
            return response()->json(['status' => false, 'message' => 'Drop-off center is required for center pickup.'], 422);
        }
        if ($validated['delivery_method'] === 'home_delivery' && empty($validated['recipient_address'])) {
            return response()->json(['status' => false, 'message' => 'Recipient address is required for home delivery.'], 422);
        }

        $start = microtime(true);
        $quote = $engine->quote($validated, ['require_geocode' => true]);
        if (! ($quote['ok'] ?? false)) {
            return response()->json([
                'status' => false,
                'message' => (string) ($quote['message'] ?? 'Unable to calculate price.'),
            ], 422);
        }
        $ms = (int) round((microtime(true) - $start) * 1000);

        return response()->json([
            'status' => true,
            'total' => $quote['total'],
            'breakdown' => $quote['breakdown'],
            'distance_km' => $quote['distance_km'],
            'eta' => match($validated['delivery_type']) {
                'express' => '1–2 business days',
                'overnight' => 'Next day delivery',
                'same_day' => 'Same-day delivery',
                default => '3–5 business days',
            },
            'response_time_ms' => $ms,
        ]);
    }
}

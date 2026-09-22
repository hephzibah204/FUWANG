<?php

namespace App\Http\Controllers\Parcels;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelCustodyEvent;
use App\Services\Parcels\Adapters\FuwaPostAdapter;
use Illuminate\Http\Request;

class DropOffController extends Controller
{
    /**
     * Show the drop off form (supports barcode scanning via standard input)
     */
    public function showCustomerDropOff()
    {
        return view('parcels.dropoff.customer');
    }

    /**
     * Process a customer dropping off a parcel at the agent shop.
     */
    public function processCustomerDropOff(Request $request, FuwaPostAdapter $adapter)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'condition' => 'required|in:good,damaged',
        ]);

        $agent = $request->user()->parcelAgent;
        $trackingNumber = $request->input('tracking_number');

        // 1. Validate with Courier API (Adapter)
        $details = $adapter->validateTrackingNumber($trackingNumber);
        
        if (!$details) {
            return back()->with('error', 'Tracking number not found or invalid.');
        }

        // 2. Prevent duplicate scan / already dropped off
        $existing = Parcel::where('tracking_number', $trackingNumber)->first();
        if ($existing) {
            if ($existing->status !== 'rejected') {
                return back()->with('error', 'This parcel is already active in the network and cannot be dropped off again.');
            }
        }

        // 3. Fetch extra details
        $parcelInfo = $adapter->fetchParcelDetails($trackingNumber);

        // 3.5. Get Courier ID dynamically
        $courierId = \App\Models\ParcelCourier::where('name', 'FuwaPost')->value('id') ?? 1;

        // 4. Create or update Parcel record in Shop Inventory
        $parcel = Parcel::updateOrCreate(
            ['tracking_number' => $trackingNumber],
            [
                'courier_id' => $courierId,
                'shop_id' => $agent->shop_id,
                'status' => 'customer_dropped_off',
                'condition' => $request->input('condition'),
                'weight' => $details['weight'] ?? null,
                'price' => $details['price'] ?? null,
                'sender_data' => $parcelInfo['sender'],
                'receiver_data' => $parcelInfo['receiver'],
            ]
        );

        // 5. Log Chain of Custody Event
        ParcelCustodyEvent::create([
            'parcel_id' => $parcel->id,
            'agent_id' => $agent->id,
            'event_type' => 'customer_to_agent',
            'notes' => 'Condition: ' . $parcel->condition,
        ]);

        // 6. Sync back to Courier
        $adapter->updateParcelStatus($trackingNumber, 'customer_dropped_off');

        return redirect()->route('parcels.dashboard')->with('success', "Parcel {$trackingNumber} successfully dropped off and added to inventory.");
    }

    /**
     * Show the driver drop off form
     */
    public function showDriverDropOff()
    {
        return view('parcels.dropoff.driver');
    }

    /**
     * Process a driver dropping off a parcel for customer pickup
     */
    public function processDriverDropOff(Request $request, FuwaPostAdapter $adapter)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'condition' => 'required|in:good,damaged',
        ]);

        $agent = $request->user()->parcelAgent;
        $trackingNumber = $request->input('tracking_number');

        $details = $adapter->validateTrackingNumber($trackingNumber);
        if (!$details) {
            return back()->with('error', 'Tracking number not found or invalid.');
        }

        $existing = Parcel::where('tracking_number', $trackingNumber)->first();
        if ($existing && $existing->shop_id === $agent->shop_id && $existing->status === 'driver_dropped_off') {
            return back()->with('error', 'This parcel is already in your Customer Collection inventory.');
        }

        $parcelInfo = $adapter->fetchParcelDetails($trackingNumber);

        $courierId = \App\Models\ParcelCourier::where('name', 'FuwaPost')->value('id') ?? 1;

        $parcel = Parcel::updateOrCreate(
            ['tracking_number' => $trackingNumber],
            [
                'courier_id' => $courierId,
                'shop_id' => $agent->shop_id,
                'status' => 'driver_dropped_off', // Ready for customer pickup
                'condition' => $request->input('condition'),
                'weight' => $details['weight'] ?? null,
                'price' => $details['price'] ?? null,
                'sender_data' => $parcelInfo['sender'],
                'receiver_data' => $parcelInfo['receiver'],
            ]
        );

        ParcelCustodyEvent::create([
            'parcel_id' => $parcel->id,
            'agent_id' => $agent->id,
            'event_type' => 'driver_to_agent',
            'notes' => 'Condition: ' . $parcel->condition,
        ]);

        $adapter->updateParcelStatus($trackingNumber, 'driver_dropped_off');

        return redirect()->route('parcels.dashboard')->with('success', "Parcel {$trackingNumber} received from driver. Ready for customer pickup.");
    }
}

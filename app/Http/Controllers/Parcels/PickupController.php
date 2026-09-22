<?php

namespace App\Http\Controllers\Parcels;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelCustodyEvent;
use App\Services\Parcels\Adapters\FuwaPostAdapter;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    /**
     * Show the pickup form.
     */
    public function showCustomerPickup()
    {
        return view('parcels.pickup.customer');
    }

    /**
     * Process a customer picking up a parcel from the agent shop.
     */
    public function processCustomerPickup(Request $request, FuwaPostAdapter $adapter)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'id_type' => 'required|string',
            'signature_data' => 'required|string', // Base64 canvas data
        ]);

        $agent = $request->user()->parcelAgent;
        $trackingNumber = $request->input('tracking_number');

        $parcel = Parcel::where('tracking_number', $trackingNumber)
            ->where('shop_id', $agent->shop_id)
            ->first();

        if (!$parcel || $parcel->status !== 'driver_dropped_off') {
            return back()->with('error', 'Parcel not found in shop inventory or not ready for customer pickup.');
        }

        // 1. Save Signature Image
        $base64 = $request->input('signature_data');
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64)) {
            return back()->with('error', 'Invalid signature format submitted.');
        }
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = str_replace(' ', '+', $image);
        
        $decoded = base64_decode($image, true);
        if ($decoded === false) {
            return back()->with('error', 'Signature could not be decoded properly.');
        }

        $imageName = 'signature_' . time() . '_' . uniqid() . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/' . $imageName, $decoded);
        $signaturePath = 'signatures/' . $imageName;

        // 2. Update internal Parcel status
        $parcel->status = 'customer_collected';
        $parcel->save();

        // 3. Log Chain of Custody Event
        ParcelCustodyEvent::create([
            'parcel_id' => $parcel->id,
            'agent_id' => $agent->id,
            'event_type' => 'agent_to_customer',
            'notes' => 'ID Type: ' . $request->input('id_type') . ' | Signature File: ' . $signaturePath,
        ]);

        // 4. Sync back to Courier
        $adapter->updateParcelStatus($trackingNumber, 'customer_collected');

        return redirect()->route('parcels.dashboard')->with('success', "Parcel {$trackingNumber} successfully handed over to customer.");
    }

    /**
     * Show the driver pickup form.
     */
    public function showDriverPickup()
    {
        return view('parcels.pickup.driver');
    }

    /**
     * Process a driver picking up a parcel from the agent shop.
     */
    public function processDriverPickup(Request $request, FuwaPostAdapter $adapter)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'condition' => 'required|in:good,damaged',
        ]);

        $agent = $request->user()->parcelAgent;
        $trackingNumber = $request->input('tracking_number');

        $parcel = Parcel::where('tracking_number', $trackingNumber)
            ->where('shop_id', $agent->shop_id)
            ->first();

        if (!$parcel || !in_array($parcel->status, ['customer_dropped_off', 'rejected'])) {
            return back()->with('error', 'Parcel not found in shop inventory or not ready for driver pickup.');
        }

        $parcel->status = 'driver_collected';
        $parcel->condition = $request->input('condition');
        $parcel->save();

        ParcelCustodyEvent::create([
            'parcel_id' => $parcel->id,
            'agent_id' => $agent->id,
            'event_type' => 'agent_to_driver',
            'notes' => 'Handover Condition: ' . $parcel->condition,
        ]);

        $adapter->updateParcelStatus($trackingNumber, 'driver_collected');

        return redirect()->route('parcels.dashboard')->with('success', "Parcel {$trackingNumber} successfully handed over to driver.");
    }
}

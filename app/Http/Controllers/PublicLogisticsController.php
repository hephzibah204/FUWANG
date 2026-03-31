<?php

namespace App\Http\Controllers;

use App\Models\LogisticsRequest;
use Illuminate\Http\Request;

class PublicLogisticsController extends Controller
{
    /**
     * Logistics Hub Landing Page
     */
    public function index()
    {
        return view('public.logistics.index');
    }

    /**
     * Public Tracking Interface
     */
    public function track(Request $request)
    {
        $request->validate(['tracking_id' => 'required|string']);

        $shipment = LogisticsRequest::where('tracking_id', $request->tracking_id)->first();

        if (!$shipment) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid tracking ID. Please check and try again.'
            ]);
        }

        // Build timeline dynamically based on current state
        $statuses = ['processing', 'in_transit', 'out_for_delivery', 'delivered'];
        $statusIndex = array_search($shipment->status, $statuses);
        if ($statusIndex === false) $statusIndex = 0;

        return response()->json([
            'status' => true,
            'tracking' => [
                'id' => $shipment->tracking_id,
                'status' => ucwords(str_replace('_', ' ', $shipment->status)),
                'location' => $shipment->recipient_address,
                'updated' => $shipment->updated_at->diffForHumans(),
                'timeline' => [
                    ['event' => 'Shipment Booked', 'time' => $shipment->created_at->format('M d, H:i'), 'done' => true],
                    ['event' => 'Processing', 'time' => $shipment->status !== 'processing' ? 'Processed' : 'Current', 'done' => $statusIndex >= 0],
                    ['event' => 'In Transit', 'time' => $statusIndex >= 1 ? 'Updated' : '–', 'done' => $statusIndex >= 1],
                    ['event' => 'Out for Delivery', 'time' => $statusIndex >= 2 ? 'Updated' : '–', 'done' => $statusIndex >= 2],
                    ['event' => 'Delivered', 'time' => $statusIndex === 3 ? 'Finalized' : '–', 'done' => $statusIndex === 3],
                ],
            ]
        ]);
    }
}

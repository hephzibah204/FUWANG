<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogisticsRequest;

class PublicLogisticsController extends Controller
{
    public function track(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|string|max:50'
        ]);

        $shipment = LogisticsRequest::where('tracking_id', $request->tracking_id)->first();
        if (!$shipment) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid tracking ID'
            ]);
        }

        $timeline = [
            ['event' => 'Shipment Booked', 'time' => $shipment->created_at->format('M d, H:i'), 'done' => true],
        ];

        $parcel = \App\Models\Parcel::where('tracking_number', $shipment->tracking_id)->first();
        if ($parcel) {
            $events = \App\Models\ParcelCustodyEvent::where('parcel_id', $parcel->id)->orderBy('created_at', 'asc')->get();
            foreach ($events as $event) {
                $timeline[] = [
                    'event' => ucwords(str_replace('_', ' ', $event->status)),
                    'time' => $event->created_at->format('M d, H:i'),
                    'done' => true
                ];
            }
            if ($shipment->status === 'delivered') {
                $timeline[] = ['event' => 'Delivered', 'time' => 'Finalized', 'done' => true];
            }
        } else {
            $statuses = ['processing', 'in_transit', 'out_for_delivery', 'delivered'];
            $statusIndex = array_search($shipment->status, $statuses);
            if ($statusIndex === false) $statusIndex = 0;
            
            $timeline[] = ['event' => 'Processing', 'time' => $shipment->status !== 'processing' ? 'Processed' : 'Current', 'done' => $statusIndex >= 0];
            $timeline[] = ['event' => 'In Transit', 'time' => $statusIndex >= 1 ? 'Updated' : '---', 'done' => $statusIndex >= 1];
            $timeline[] = ['event' => 'Out for Delivery', 'time' => $statusIndex >= 2 ? 'Updated' : '---', 'done' => $statusIndex >= 2];
            $timeline[] = ['event' => 'Delivered', 'time' => $statusIndex === 3 ? 'Finalized' : '---', 'done' => $statusIndex === 3];
        }

        return response()->json([
            'status' => true,
            'tracking' => [
                'id' => $shipment->tracking_id,
                'status' => ucwords(str_replace('_', ' ', $shipment->status)),
                'location' => $shipment->recipient_address,
                'updated' => $shipment->updated_at->diffForHumans(),
                'timeline' => $timeline,
            ]
        ]);
    }
}

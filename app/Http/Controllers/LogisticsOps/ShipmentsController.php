<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ShipmentsController extends Controller
{
    public function index(Request $request)
    {
        $staff = Auth::guard('logistics_staff')->user();

        $query = LogisticsRequest::query()->latest();
        if ($staff instanceof LogisticsStaff && $staff->hasRole('logistics_officer')) {
            $query->where('assigned_officer_id', $staff->id);
        }

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('tracking_id', 'like', "%{$search}%")
                ->orWhere('route_code', 'like', "%{$search}%");
        }

        $shipments = $query->paginate(20)->withQueryString();

        return view('logistics.ops.shipments.index', compact('staff', 'shipments'));
    }

    public function update(Request $request, LogisticsRequest $shipment)
    {
        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff && $staff->hasRole('logistics_officer') && (int) $shipment->assigned_officer_id !== (int) $staff->id) {
            abort(403);
        }

        $request->validate([
            'scheduled_pickup_at' => ['nullable', 'date'],
            'route_code' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::in(['processing', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled'])],
        ]);

        $canSchedule = $staff instanceof LogisticsStaff && $staff->hasPermission('logistics.shipments.schedule');
        $canAssignRoute = $staff instanceof LogisticsStaff && $staff->hasPermission('logistics.shipments.assign_routes');

        if (($canSchedule || $canAssignRoute) && $request->has('scheduled_pickup_at')) {
            $shipment->scheduled_pickup_at = $request->input('scheduled_pickup_at');
        }
        if (($canSchedule || $canAssignRoute) && $request->has('route_code')) {
            $shipment->route_code = $request->input('route_code');
        }
        if ($request->filled('status')) {
            $shipment->status = $request->input('status');
            $shipment->last_status_updated_at = now();
        }

        $shipment->save();

        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_shipments.updated', "Updated shipment {$shipment->tracking_id}");
        }

        return back()->with('success', 'Shipment updated.');
    }
}

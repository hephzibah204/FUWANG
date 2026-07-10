<?php

namespace App\Http\Controllers\Api\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrdersApiController extends Controller
{
    public function index(Request $request)
    {
        $staff = Auth::guard('logistics_staff')->user();
        if (! ($staff instanceof LogisticsStaff)) {
            return response()->json(['status' => 'error', 'message' => 'Authentication required.'], 401);
        }

        $query = LogisticsRequest::query()->latest();
        if ($staff->hasRole('logistics_officer')) {
            $query->where('assigned_officer_id', $staff->id);
        }

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    public function updateStatus(Request $request, LogisticsRequest $order)
    {
        $staff = Auth::guard('logistics_staff')->user();
        if (! ($staff instanceof LogisticsStaff)) {
            return response()->json(['status' => 'error', 'message' => 'Authentication required.'], 401);
        }

        if ($staff->hasRole('logistics_officer') && (int) $order->assigned_officer_id !== (int) $staff->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'status' => ['required', 'string', Rule::in(['processing', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled'])],
        ]);

        $order->status = $request->input('status');
        $order->last_status_updated_at = now();
        $order->save();

        $staff->logActivity('logistics_orders.status_updated', "Order {$order->tracking_id} status={$order->status}");

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }
}


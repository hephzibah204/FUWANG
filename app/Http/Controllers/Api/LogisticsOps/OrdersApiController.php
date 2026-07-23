<?php

namespace App\Http\Controllers\Api\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use Illuminate\Http\Request;

class OrdersApiController extends Controller
{
    public function index(Request $request)
    {
        $staff = $request->attributes->get('logistics_staff');

        $query = LogisticsRequest::query()->latest();

        if ($staff && $staff->hasRole('logistics_officer')) {
            $query->where('assigned_officer_id', $staff->id);
        }

        $orders = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = LogisticsRequest::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order,
        ]);
    }
}

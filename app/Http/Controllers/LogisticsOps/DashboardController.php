<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $staff = Auth::guard('logistics_staff')->user();

        $ordersQuery = LogisticsRequest::query()->latest();
        if ($staff instanceof \App\Models\LogisticsStaff && $staff->hasRole('logistics_officer')) {
            $ordersQuery->where('assigned_officer_id', $staff->id);
        }

        $recentOrders = $ordersQuery->take(10)->get();

        $base = LogisticsRequest::query();
        if ($staff instanceof \App\Models\LogisticsStaff && $staff->hasRole('logistics_officer')) {
            $base->where('assigned_officer_id', $staff->id);
        }

        $stats = [
            'total' => (clone $base)->count(),
            'processing' => (clone $base)->where('status', 'processing')->count(),
            'in_transit' => (clone $base)->where('status', 'in_transit')->count(),
            'delivered' => (clone $base)->where('status', 'delivered')->count(),
        ];

        return view('logistics.ops.dashboard', [
            'staff' => $staff,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }
}


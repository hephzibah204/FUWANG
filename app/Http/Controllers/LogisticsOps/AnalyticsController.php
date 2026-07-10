<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $staff = Auth::guard('logistics_staff')->user();

        $base = LogisticsRequest::query();
        if ($staff instanceof LogisticsStaff && $staff->hasRole('logistics_officer')) {
            $base->where('assigned_officer_id', $staff->id);
        }

        $from = $request->date('from');
        $to = $request->date('to');
        if ($from) {
            $base->where('created_at', '>=', $from->startOfDay());
        }
        if ($to) {
            $base->where('created_at', '<=', $to->endOfDay());
        }

        $summary = [
            'orders' => (clone $base)->count(),
            'revenue' => (float) (clone $base)->sum('amount'),
            'delivered' => (clone $base)->where('status', 'delivered')->count(),
            'in_transit' => (clone $base)->where('status', 'in_transit')->count(),
        ];

        $byStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return view('logistics.ops.analytics.index', [
            'staff' => $staff,
            'summary' => $summary,
            'byStatus' => $byStatus,
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
        ]);
    }
}


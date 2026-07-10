<?php

namespace App\Http\Controllers\LogisticsAgent;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\LogisticsRequest;
use Illuminate\Support\Facades\Auth;

class AgentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user || ! ($user instanceof \App\Models\User)) {
            abort(403);
        }

        $agent = DeliveryAgent::query()->where('user_id', $user->id)->first();
        if (! $agent) {
            abort(403);
        }

        $assigned = LogisticsRequest::query()
            ->where('assigned_delivery_agent_id', $agent->id)
            ->latest()
            ->take(10)
            ->get();

        $base = LogisticsRequest::query()->where('assigned_delivery_agent_id', $agent->id);

        $stats = [
            'assigned' => (clone $base)->count(),
            'in_transit' => (clone $base)->where('status', 'in_transit')->count(),
            'out_for_delivery' => (clone $base)->where('status', 'out_for_delivery')->count(),
            'delivered' => (clone $base)->where('status', 'delivered')->count(),
        ];

        return view('logistics.agent.dashboard', [
            'agent' => $agent,
            'stats' => $stats,
            'assignedOrders' => $assigned,
        ]);
    }
}


<?php

namespace App\Http\Controllers\LogisticsAgent;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\LogisticsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentEarningsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! ($user instanceof \App\Models\User)) {
            abort(403);
        }

        $agent = DeliveryAgent::query()->where('user_id', $user->id)->firstOrFail();

        $delivered = LogisticsRequest::query()
            ->where('assigned_delivery_agent_id', $agent->id)
            ->where('status', 'delivered')
            ->where('agent_assignment_status', 'accepted')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $earned = LogisticsRequest::query()
            ->where('assigned_delivery_agent_id', $agent->id)
            ->where('status', 'delivered')
            ->where('agent_assignment_status', 'accepted');

        $pending = LogisticsRequest::query()
            ->where('assigned_delivery_agent_id', $agent->id)
            ->whereIn('status', ['processing', 'in_transit', 'out_for_delivery'])
            ->where('agent_assignment_status', 'accepted');

        $totals = [
            'earned_total' => (float) $earned->sum('agent_commission_amount'),
            'paid_total' => (float) (clone $earned)->whereNotNull('agent_paid_at')->sum('agent_commission_amount'),
            'pending_total' => (float) $pending->sum('agent_commission_amount'),
        ];

        return view('logistics.agent.earnings.index', [
            'agent' => $agent,
            'totals' => $totals,
            'delivered' => $delivered,
        ]);
    }
}


<?php

namespace App\Http\Controllers\LogisticsAgent;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\LogisticsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AgentOrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! ($user instanceof \App\Models\User)) {
            abort(403);
        }

        $agent = DeliveryAgent::query()->where('user_id', $user->id)->firstOrFail();

        $query = LogisticsRequest::query()
            ->where('assigned_delivery_agent_id', $agent->id)
            ->latest();

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('logistics.agent.orders.index', [
            'agent' => $agent,
            'orders' => $orders,
        ]);
    }

    public function show(LogisticsRequest $order)
    {
        $agent = $this->agentOrFail();

        if ((int) $order->assigned_delivery_agent_id !== (int) $agent->id) {
            abort(403);
        }

        return view('logistics.agent.orders.show', [
            'agent' => $agent,
            'order' => $order,
        ]);
    }

    public function accept(LogisticsRequest $order)
    {
        $agent = $this->agentOrFail(true);

        if ((int) $order->assigned_delivery_agent_id !== (int) $agent->id) {
            abort(403);
        }
        if (($order->agent_assignment_status ?? 'pending') !== 'pending') {
            return back()->with('error', 'This assignment can no longer be accepted.');
        }

        $order->agent_assignment_status = 'accepted';
        $order->agent_assignment_responded_at = now();

        if ($order->agent_fee_amount === null) {
            $fees = [
                'standard' => (float) config('services.logistics.agent_fee_standard', 1500),
                'express' => (float) config('services.logistics.agent_fee_express', 2500),
                'overnight' => (float) config('services.logistics.agent_fee_overnight', 3500),
            ];
            $order->agent_fee_amount = $fees[$order->delivery_type] ?? $fees['standard'];
        }

        if ($order->agent_commission_amount === null) {
            $order->agent_commission_amount = $order->agent_fee_amount;
        }

        $order->save();

        return back()->with('success', 'Assignment accepted.');
    }

    public function decline(LogisticsRequest $order)
    {
        $agent = $this->agentOrFail(true);

        if ((int) $order->assigned_delivery_agent_id !== (int) $agent->id) {
            abort(403);
        }
        if (($order->agent_assignment_status ?? 'pending') !== 'pending') {
            return back()->with('error', 'This assignment can no longer be declined.');
        }

        $order->agent_assignment_status = 'declined';
        $order->agent_assignment_responded_at = now();
        $order->save();

        return back()->with('success', 'Assignment declined.');
    }

    public function updateStatus(Request $request, LogisticsRequest $order)
    {
        $agent = $this->agentOrFail(true);

        if ((int) $order->assigned_delivery_agent_id !== (int) $agent->id) {
            abort(403);
        }
        if (($order->agent_assignment_status ?? 'pending') !== 'accepted') {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'string', Rule::in(['in_transit', 'out_for_delivery', 'delivered'])],
        ]);

        $order->status = $request->input('status');
        $order->last_status_updated_at = now();
        $order->save();

        return back()->with('success', 'Status updated.');
    }

    public function updateAvailability(Request $request)
    {
        $agent = $this->agentOrFail();

        $request->validate([
            'availability_status' => ['required', 'string', Rule::in(['available', 'on_delivery', 'offline'])],
        ]);

        $agent->availability_status = $request->input('availability_status');
        $agent->save();

        return back()->with('success', 'Availability updated.');
    }

    private function agentOrFail(bool $requireApproved = false): DeliveryAgent
    {
        $user = Auth::user();
        if (! $user || ! ($user instanceof \App\Models\User)) {
            abort(403);
        }

        $agent = DeliveryAgent::query()->where('user_id', $user->id)->firstOrFail();
        if ($requireApproved && $agent->approval_status !== 'approved') {
            abort(403);
        }

        return $agent;
    }
}

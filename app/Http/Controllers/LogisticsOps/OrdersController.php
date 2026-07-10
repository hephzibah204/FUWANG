<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\LogisticsRequest;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class OrdersController extends Controller
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
                ->orWhere('sender_name', 'like', "%{$search}%")
                ->orWhere('recipient_name', 'like', "%{$search}%");
        }

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('logistics.ops.orders.index', [
            'staff' => $staff,
            'orders' => $orders,
        ]);
    }

    public function create()
    {
        return view('logistics.ops.orders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_address' => ['required', 'string', 'max:1000'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_address' => ['required', 'string', 'max:1000'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'delivery_type' => ['required', 'string', Rule::in(['standard', 'express', 'overnight'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tracking = $this->generateTrackingId();

        $order = LogisticsRequest::query()->create([
            'user_id' => (int) $request->input('user_id'),
            'sender_name' => $request->input('sender_name'),
            'sender_address' => $request->input('sender_address'),
            'recipient_name' => $request->input('recipient_name'),
            'recipient_address' => $request->input('recipient_address'),
            'weight' => $request->input('weight'),
            'description' => $request->input('description'),
            'delivery_type' => $request->input('delivery_type'),
            'amount' => $request->input('amount'),
            'tracking_id' => $tracking,
            'status' => 'processing',
            'last_status_updated_at' => now(),
        ]);

        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_orders.created', "Created order {$tracking}");
        }

        return redirect()->route('logistics.ops.orders.index')->with('success', 'Order created successfully.');
    }

    public function edit(LogisticsRequest $order)
    {
        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff && $staff->hasRole('logistics_officer') && (int) $order->assigned_officer_id !== (int) $staff->id) {
            abort(403);
        }

        $officers = LogisticsStaff::query()
            ->role('logistics_officer', 'logistics_staff')
            ->where('is_active', true)
            ->orderBy('fullname')
            ->get();
        $agents = DeliveryAgent::query()->with('user')->where('approval_status', 'approved')->orderByDesc('rating')->get();

        return view('logistics.ops.orders.edit', compact('order', 'officers', 'agents'));
    }

    public function update(Request $request, LogisticsRequest $order)
    {
        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff && $staff->hasRole('logistics_officer') && (int) $order->assigned_officer_id !== (int) $staff->id) {
            abort(403);
        }

        $request->validate([
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_address' => ['required', 'string', 'max:1000'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_address' => ['required', 'string', 'max:1000'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'delivery_type' => ['required', 'string', Rule::in(['standard', 'express', 'overnight'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'scheduled_pickup_at' => ['nullable', 'date'],
            'route_code' => ['nullable', 'string', 'max:50'],
            'assigned_officer_id' => ['nullable', 'integer', 'exists:logistics_staff,id'],
            'assigned_delivery_agent_id' => ['nullable', 'integer', 'exists:delivery_agents,id'],
            'agent_fee_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order->fill($request->only([
            'sender_name',
            'sender_address',
            'recipient_name',
            'recipient_address',
            'weight',
            'description',
            'delivery_type',
            'amount',
        ]));

        $canAssign = $staff instanceof LogisticsStaff && $staff->hasPermission('logistics.orders.assign');
        if ($canAssign) {
            $previousAgentId = $order->assigned_delivery_agent_id;

            $order->fill($request->only([
                'scheduled_pickup_at',
                'route_code',
                'assigned_officer_id',
                'assigned_delivery_agent_id',
                'agent_fee_amount',
            ]));

            if ((string) $previousAgentId !== (string) $order->assigned_delivery_agent_id) {
                $order->agent_assignment_status = $order->assigned_delivery_agent_id ? 'pending' : 'pending';
                $order->agent_assignment_responded_at = null;
                $order->agent_commission_amount = null;
            }
        }
        $order->save();

        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_orders.updated', "Updated order {$order->tracking_id}");
        }

        return redirect()->route('logistics.ops.orders.edit', $order->id)->with('success', 'Order updated successfully.');
    }

    public function updateStatus(Request $request, LogisticsRequest $order)
    {
        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff && $staff->hasRole('logistics_officer') && (int) $order->assigned_officer_id !== (int) $staff->id) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'string', Rule::in(['processing', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled'])],
        ]);

        $order->status = $request->input('status');
        $order->last_status_updated_at = now();
        $order->save();

        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_orders.status_updated', "Order {$order->tracking_id} status={$order->status}");
        }

        return back()->with('success', 'Status updated.');
    }

    private function generateTrackingId(): string
    {
        do {
            $value = 'FUP-' . Str::upper(Str::random(8));
        } while (LogisticsRequest::query()->where('tracking_id', $value)->exists());

        return $value;
    }
}

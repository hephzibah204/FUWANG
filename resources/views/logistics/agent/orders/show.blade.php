@extends('layouts.postoffice')

@section('title', 'Delivery Details')

@section('content')
@include('logistics.agent.partials.nav', ['title' => 'Delivery ' . $order->tracking_id, 'subtitle' => 'Update status and view delivery details'])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ session('error') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="text-white-50 small">Tracking ID</div>
                    <div class="h4 mb-0 font-weight-bold"><code class="text-primary">{{ $order->tracking_id }}</code></div>
                </div>
                <span class="badge badge-pill badge-dark px-3">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="text-white-50 small">Pickup</div>
                    <div class="font-weight-bold">{{ $order->sender_name }}</div>
                    <div class="text-white-50 small">{{ $order->sender_address }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="text-white-50 small">Dropoff</div>
                    <div class="font-weight-bold">{{ $order->recipient_name }}</div>
                    <div class="text-white-50 small">{{ $order->recipient_address }}</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-2">
                    <div class="text-white-50 small">Route</div>
                    <div class="font-weight-bold">{{ $order->route_code ?: '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="text-white-50 small">Pickup time</div>
                    <div class="font-weight-bold">{{ $order->scheduled_pickup_at ? $order->scheduled_pickup_at->toDayDateTimeString() : '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="text-white-50 small">Last update</div>
                    <div class="font-weight-bold">{{ $order->last_status_updated_at ? $order->last_status_updated_at->toDayDateTimeString() : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="glass-card p-4">
            <h5 class="font-weight-bold mb-2">Assignment</h5>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="text-white-50 small">Assignment status</div>
                <span class="badge badge-pill badge-dark px-3">{{ strtoupper($order->agent_assignment_status ?? 'PENDING') }}</span>
            </div>

            @if(($agent->approval_status ?? '') === 'approved' && ($order->agent_assignment_status ?? 'pending') === 'pending')
                <form method="POST" action="{{ route('logistics.agent.orders.accept', $order->id) }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-po-primary btn-block">
                        <i class="fa fa-circle-check mr-2"></i> Accept Assignment
                    </button>
                </form>
                <form method="POST" action="{{ route('logistics.agent.orders.decline', $order->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-block" style="border-radius: 12px;">
                        <i class="fa fa-circle-xmark mr-2" style="color: var(--po-primary);"></i> Decline
                    </button>
                </form>
            @elseif(($order->agent_assignment_status ?? 'pending') === 'declined')
                <div class="text-white-50 small">You declined this assignment. Ops can reassign to another agent.</div>
            @elseif(($order->agent_assignment_status ?? 'pending') === 'accepted')
                <div class="text-white-50 small">Assignment accepted. You can now update delivery status.</div>
            @endif

            <hr style="border-color: rgba(255,255,255,0.08);">

            <h5 class="font-weight-bold mb-2">Update delivery status</h5>
            @if(($agent->approval_status ?? '') !== 'approved')
                <div class="text-white-50 small">Your agent account is not approved yet.</div>
            @elseif(($order->agent_assignment_status ?? 'pending') !== 'accepted')
                <div class="text-white-50 small">Accept the assignment before updating status.</div>
            @else
                <form method="POST" action="{{ route('logistics.agent.orders.status', $order->id) }}">
                    @csrf
                    <div class="form-group">
                        <label class="text-white-50 small">New status</label>
                        <select name="status" class="form-control tracking-input" required>
                            <option value="in_transit" @selected(old('status') === 'in_transit')>IN TRANSIT</option>
                            <option value="out_for_delivery" @selected(old('status') === 'out_for_delivery')>OUT FOR DELIVERY</option>
                            <option value="delivered" @selected(old('status') === 'delivered')>DELIVERED</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-po-primary btn-block">
                        <i class="fa fa-circle-check mr-2"></i> Update
                    </button>
                </form>
            @endif

            <div class="mt-3">
                <a href="{{ route('logistics.agent.orders.index') }}" class="btn btn-outline-light btn-block" style="border-radius: 12px;">Back to My Deliveries</a>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.nexus')

@section('title', 'Edit Order - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Order ' . $order->tracking_id, 'subtitle' => 'Edit, assign, and update status'])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card glass-card border-0 p-4">
            <form method="POST" action="{{ route('logistics.ops.orders.update', $order->id) }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="text-white-50 small">Sender name</label>
                        <input type="text" name="sender_name" class="form-control" required value="{{ old('sender_name', $order->sender_name) }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="text-white-50 small">Recipient name</label>
                        <input type="text" name="recipient_name" class="form-control" required value="{{ old('recipient_name', $order->recipient_name) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="text-white-50 small">Sender address</label>
                        <textarea name="sender_address" class="form-control" rows="3" required>{{ old('sender_address', $order->sender_address) }}</textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="text-white-50 small">Recipient address</label>
                        <textarea name="recipient_address" class="form-control" rows="3" required>{{ old('recipient_address', $order->recipient_address) }}</textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="text-white-50 small">Delivery type</label>
                        <select name="delivery_type" class="form-control" required>
                            @foreach(['standard','express','overnight'] as $t)
                                <option value="{{ $t }}" @selected(old('delivery_type', $order->delivery_type)===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="text-white-50 small">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" class="form-control" value="{{ old('weight', $order->weight) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="text-white-50 small">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $order->amount) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small">Description</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $order->description) }}">
                </div>

                @if(auth('logistics_staff')->user()?->hasPermission('logistics.orders.assign'))
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="text-white-50 small">Scheduled pickup</label>
                            <input type="datetime-local" name="scheduled_pickup_at" class="form-control" value="{{ old('scheduled_pickup_at', $order->scheduled_pickup_at ? $order->scheduled_pickup_at->format('Y-m-d\\TH:i') : '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="text-white-50 small">Route code</label>
                            <input type="text" name="route_code" class="form-control" value="{{ old('route_code', $order->route_code) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="text-white-50 small">Assigned officer</label>
                            <select name="assigned_officer_id" class="form-control">
                                <option value="">Unassigned</option>
                                @foreach($officers as $o)
                                    <option value="{{ $o->id }}" @selected((string) old('assigned_officer_id', $order->assigned_officer_id) === (string) $o->id)>{{ $o->fullname ?: $o->email }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="text-white-50 small">Assigned delivery agent</label>
                            <select name="assigned_delivery_agent_id" class="form-control">
                                <option value="">Unassigned</option>
                                @foreach($agents as $a)
                                    <option value="{{ $a->id }}" @selected((string) old('assigned_delivery_agent_id', $order->assigned_delivery_agent_id) === (string) $a->id)>{{ $a->user?->fullname ?: ('Agent #' . $a->id) }}</option>
                                @endforeach
                            </select>
                            <small class="text-white-50 d-block mt-1">Assignment status: {{ strtoupper($order->agent_assignment_status ?? 'PENDING') }}</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="text-white-50 small">Agent fee (commission)</label>
                            <input type="number" step="0.01" name="agent_fee_amount" class="form-control" value="{{ old('agent_fee_amount', $order->agent_fee_amount) }}" placeholder="e.g., 2500">
                            <small class="text-white-50 d-block mt-1">If empty, default fee is used when agent accepts.</small>
                        </div>
                    </div>
                @endif

                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between">
                    <button class="btn btn-primary px-4" type="submit"><i class="fa fa-save mr-2"></i>Save</button>
                    <a class="btn btn-outline-secondary mt-3 mt-md-0 px-4" href="{{ route('logistics.ops.orders.index') }}">Back</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card glass-card border-0 p-4">
            <h5 class="text-white font-weight-bold mb-3">Update status</h5>
            <form method="POST" action="{{ route('logistics.ops.orders.status', $order->id) }}">
                @csrf
                <div class="form-group">
                    <label class="text-white-50 small">Current status</label>
                    <select name="status" class="form-control" required>
                        @foreach(['processing','in_transit','out_for_delivery','delivered','cancelled'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $order->status)===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-outline-light btn-block" type="submit" style="border-radius: 12px;">
                    <i class="fa fa-circle-check mr-2" style="color: var(--clr-primary);"></i> Update
                </button>
            </form>

            <div class="mt-4">
                <div class="text-white-50 small">Last status update</div>
                <div class="text-white">{{ $order->last_status_updated_at ? $order->last_status_updated_at->toDayDateTimeString() : '—' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

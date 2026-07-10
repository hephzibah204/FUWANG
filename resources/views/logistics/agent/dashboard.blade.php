@extends('layouts.postoffice')

@section('title', 'Delivery Agent Dashboard')

@section('content')
@include('logistics.agent.partials.nav', ['title' => 'Delivery Agent Dashboard', 'subtitle' => 'Assigned deliveries and status updates'])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif
@if($agent->approval_status !== 'approved')
    <div class="alert alert-warning border-0" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25) !important; color: #fff;">
        <div class="font-weight-bold mb-1">Your delivery agent profile is {{ strtoupper($agent->approval_status) }}</div>
        <div class="small text-white-50">Complete your verification in your profile and wait for approval before you can update delivery statuses.</div>
        <div class="mt-2">
            <a href="{{ route('profile') }}" class="btn btn-sm btn-outline-light" style="border-radius: 12px;">Go to Profile</a>
        </div>
    </div>
@endif

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255,255,255,0.05); color: var(--po-primary);">
                <i class="fa fa-box-open fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Assigned</small>
                <h4 class="font-weight-bold mb-0">{{ $stats['assigned'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); color: var(--po-accent);">
                <i class="fa fa-truck-fast fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">In Transit</small>
                <h4 class="font-weight-bold mb-0 text-primary">{{ $stats['in_transit'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); color: var(--po-primary);">
                <i class="fa fa-route fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Out for Delivery</small>
                <h4 class="font-weight-bold mb-0" style="color: var(--po-primary);">{{ $stats['out_for_delivery'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <i class="fa fa-check-circle fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Delivered</small>
                <h4 class="font-weight-bold mb-0 text-success">{{ $stats['delivered'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="glass-card overflow-hidden">
    <div class="p-4 border-bottom border-white-10 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 font-weight-bold">Recent Assigned Deliveries</h5>
        <a href="{{ route('logistics.agent.orders.index') }}" class="btn btn-sm btn-outline-glass">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="color: #eee;">
            <thead class="bg-black-10 small text-uppercase" style="letter-spacing: 1px;">
                <tr>
                    <th class="border-0 px-4">Tracking ID</th>
                    <th class="border-0">Pickup</th>
                    <th class="border-0">Dropoff</th>
                    <th class="border-0">Status</th>
                    <th class="border-0 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignedOrders as $o)
                    <tr class="border-white-05">
                        <td class="px-4"><code class="text-primary">{{ $o->tracking_id }}</code></td>
                        <td>
                            <div class="font-weight-bold">{{ $o->sender_name }}</div>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($o->sender_address, 30) }}</small>
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $o->recipient_name }}</div>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($o->recipient_address, 30) }}</small>
                        </td>
                        <td>
                            <span class="badge badge-pill badge-dark px-3">{{ strtoupper(str_replace('_', ' ', $o->status)) }}</span>
                        </td>
                        <td class="text-right px-4">
                            <a href="{{ route('logistics.agent.orders.show', $o->id) }}" class="btn btn-sm btn-outline-glass">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white-50 italic">
                            No assigned deliveries yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


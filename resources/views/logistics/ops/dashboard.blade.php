@extends('layouts.nexus')

@section('title', 'Logistics Ops Dashboard')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Logistics Ops Dashboard', 'subtitle' => $staff ? ('Signed in as ' . ($staff->fullname ?: $staff->email)) : null])

<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">Total Orders</div>
            <div class="h3 mb-0 text-white font-weight-bold">{{ $stats['total'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">Processing</div>
            <div class="h3 mb-0 text-white font-weight-bold">{{ $stats['processing'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">In Transit</div>
            <div class="h3 mb-0 text-white font-weight-bold">{{ $stats['in_transit'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">Delivered</div>
            <div class="h3 mb-0 text-white font-weight-bold">{{ $stats['delivered'] ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 p-0 mt-3">
    <div class="p-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="text-white mb-0 font-weight-bold">Recent Orders</h5>
            <a href="{{ route('logistics.ops.orders.index') }}" class="btn btn-sm btn-outline-secondary">View all</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Tracking</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Sender</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Recipient</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Status</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $o)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4">{{ $o->tracking_id }}</td>
                        <td class="py-3">{{ $o->sender_name }}</td>
                        <td class="py-3">{{ $o->recipient_name }}</td>
                        <td class="py-3"><span class="badge badge-pill badge-secondary px-3 py-1">{{ $o->status }}</span></td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('logistics.ops.orders.edit', $o->id) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white-50">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


@extends('layouts.nexus')

@section('title', 'Orders - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Orders', 'subtitle' => 'Create, edit, track, and process orders'])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif

<div class="card glass-card border-0 rounded-lg p-0 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="p-4 border-bottom border-secondary d-flex flex-column flex-md-row justify-content-between" style="border-color: rgba(255,255,255,0.05) !important;">
        <form class="d-flex flex-wrap align-items-center" method="GET" action="{{ route('logistics.ops.orders.index') }}">
            <div class="input-group mr-2 mb-2" style="max-width: 360px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fa fa-search"></i></span>
                </div>
                <input type="text" name="search" class="form-control form-control-sm bg-transparent border-secondary text-white shadow-none" placeholder="Search tracking, sender, recipient..." value="{{ request('search') }}" style="border-radius: 20px; border-color: rgba(255,255,255,0.1) !important;">
            </div>
            <select name="status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 200px; border-radius: 20px; border-color: rgba(255,255,255,0.1) !important;">
                <option value="">All statuses</option>
                @foreach(['processing','in_transit','out_for_delivery','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary mb-2" type="submit">Filter</button>
            @if(request('search') || request('status'))
                <a class="btn btn-sm btn-outline-secondary ml-2 mb-2" href="{{ route('logistics.ops.orders.index') }}">Clear</a>
            @endif
        </form>

        @if(auth('logistics_staff')->user()?->hasPermission('logistics.orders.create'))
            <a href="{{ route('logistics.ops.orders.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 mb-2">
                <i class="fa fa-plus-circle mr-2"></i> New Order
            </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Tracking</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Sender</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Recipient</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Delivery</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Status</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4 font-weight-bold">{{ $o->tracking_id }}</td>
                        <td class="py-3">{{ $o->sender_name }}</td>
                        <td class="py-3">{{ $o->recipient_name }}</td>
                        <td class="py-3 text-white-50">{{ $o->delivery_type }}</td>
                        <td class="py-3"><span class="badge badge-pill badge-secondary px-3 py-1">{{ $o->status }}</span></td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('logistics.ops.orders.edit', $o->id) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-white-50">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="p-4 border-top border-secondary d-flex justify-content-center" style="border-color: rgba(255,255,255,0.05) !important;">
            {{ $orders->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection


@extends('layouts.postoffice')

@section('title', 'My Deliveries')

@section('content')
@include('logistics.agent.partials.nav', ['title' => 'My Deliveries', 'subtitle' => 'Orders assigned to you'])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif

<div class="glass-card overflow-hidden">
    <div class="p-4 border-bottom border-white-10 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="mb-3 mb-md-0 font-weight-bold">Assigned Orders</h5>
        <form method="GET" action="{{ route('logistics.agent.orders.index') }}" class="d-flex flex-wrap">
            <select name="status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="border-radius: 12px; border-color: rgba(255,255,255,0.1) !important; min-width: 190px;">
                <option value="">All statuses</option>
                @foreach(['processing','in_transit','out_for_delivery','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ strtoupper(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-glass mb-2">Filter</button>
            @if(request('status'))
                <a href="{{ route('logistics.agent.orders.index') }}" class="btn btn-sm btn-outline-glass ml-2 mb-2">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0" style="color: #eee;">
            <thead class="bg-black-10 small text-uppercase" style="letter-spacing: 1px;">
                <tr>
                    <th class="border-0 px-4">Tracking ID</th>
                    <th class="border-0">Recipient</th>
                    <th class="border-0">Route</th>
                    <th class="border-0">Status</th>
                    <th class="border-0 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    <tr class="border-white-05">
                        <td class="px-4"><code class="text-primary">{{ $o->tracking_id }}</code></td>
                        <td>
                            <div class="font-weight-bold">{{ $o->recipient_name }}</div>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($o->recipient_address, 30) }}</small>
                        </td>
                        <td class="text-white-50">{{ $o->route_code ?: '—' }}</td>
                        <td><span class="badge badge-pill badge-dark px-3">{{ strtoupper(str_replace('_', ' ', $o->status)) }}</span></td>
                        <td class="text-right px-4">
                            <a href="{{ route('logistics.agent.orders.show', $o->id) }}" class="btn btn-sm btn-outline-glass">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white-50 italic">
                            No assigned deliveries found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="p-4 border-top border-white-10">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection


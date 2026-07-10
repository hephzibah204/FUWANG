@extends('layouts.nexus')

@section('title', 'Analytics - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Analytics', 'subtitle' => 'Real-time analytics and reporting'])

<div class="card glass-card border-0 p-4 mb-4">
    <form method="GET" action="{{ route('logistics.ops.analytics.index') }}" class="d-flex flex-wrap align-items-end">
        <div class="form-group mr-2 mb-2">
            <label class="text-white-50 small">From</label>
            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="form-group mr-2 mb-2">
            <label class="text-white-50 small">To</label>
            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="mb-2">
            <button class="btn btn-outline-secondary" type="submit">Apply</button>
            @if(!empty($filters['from']) || !empty($filters['to']))
                <a class="btn btn-outline-secondary ml-2" href="{{ route('logistics.ops.analytics.index') }}">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">Orders</div>
            <div class="h3 mb-0 text-white font-weight-bold">{{ $summary['orders'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">Revenue</div>
            <div class="h3 mb-0 text-white font-weight-bold">₦{{ number_format($summary['revenue'] ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">Delivered</div>
            <div class="h3 mb-0 text-white font-weight-bold">{{ $summary['delivered'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card border-0 p-3">
            <div class="text-white-50 small">In Transit</div>
            <div class="h3 mb-0 text-white font-weight-bold">{{ $summary['in_transit'] ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 p-0 mt-3">
    <div class="p-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <h5 class="text-white mb-0 font-weight-bold">Orders by Status</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Status</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byStatus as $row)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4">{{ $row->status }}</td>
                        <td class="py-3 px-4 text-right font-weight-bold">{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center py-5 text-white-50">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


@extends('layouts.nexus')

@section('title', 'Shipments - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Shipments', 'subtitle' => 'Schedule shipments, assign routes, monitor delivery status'])

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

<div class="card glass-card border-0 rounded-lg p-0 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="p-4 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.05) !important;">
        <form class="d-flex flex-wrap align-items-center" method="GET" action="{{ route('logistics.ops.shipments.index') }}">
            <div class="input-group mr-2 mb-2" style="max-width: 360px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fa fa-search"></i></span>
                </div>
                <input type="text" name="search" class="form-control form-control-sm bg-transparent border-secondary text-white shadow-none" placeholder="Search tracking or route..." value="{{ request('search') }}" style="border-radius: 20px; border-color: rgba(255,255,255,0.1) !important;">
            </div>
            <button class="btn btn-sm btn-outline-secondary mb-2" type="submit">Filter</button>
            @if(request('search'))
                <a class="btn btn-sm btn-outline-secondary ml-2 mb-2" href="{{ route('logistics.ops.shipments.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Tracking</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Pickup</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Route</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Agent</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Status</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $s)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4 font-weight-bold">{{ $s->tracking_id }}</td>
                        <td class="py-3 text-white-50">{{ $s->scheduled_pickup_at ? $s->scheduled_pickup_at->toDayDateTimeString() : '—' }}</td>
                        <td class="py-3 text-white-50">{{ $s->route_code ?: '—' }}</td>
                        <td class="py-3 text-white-50">
                            @php $agent = $agents->firstWhere('id', $s->assigned_delivery_agent_id); @endphp
                            {{ $agent?->user?->fullname ?: '—' }}
                        </td>
                        <td class="py-3"><span class="badge badge-pill badge-secondary px-3 py-1">{{ $s->status }}</span></td>
                        <td class="py-3 px-4 text-right">
                            <form method="POST" action="{{ route('logistics.ops.shipments.update', $s->id) }}" class="d-flex flex-column flex-md-row justify-content-end">
                                @csrf
                                @method('PUT')
                                <input type="datetime-local" name="scheduled_pickup_at" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 220px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" value="{{ $s->scheduled_pickup_at ? $s->scheduled_pickup_at->format('Y-m-d\\TH:i') : '' }}">
                                <input type="text" name="route_code" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 160px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" placeholder="Route" value="{{ $s->route_code }}">
                                <select name="assigned_delivery_agent_id" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 220px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;">
                                    <option value="">Unassigned</option>
                                    @foreach($agents as $a)
                                        <option value="{{ $a->id }}" @selected((string) $s->assigned_delivery_agent_id === (string) $a->id)>{{ $a->user?->fullname ?: ('Agent #' . $a->id) }}</option>
                                    @endforeach
                                </select>
                                <select name="status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 190px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;">
                                    <option value="">Keep status</option>
                                    @foreach(['processing','in_transit','out_for_delivery','delivered','cancelled'] as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-light mb-2" type="submit" style="border-radius: 12px;">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-white-50">No shipments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($shipments->hasPages())
        <div class="p-4 border-top border-secondary d-flex justify-content-center" style="border-color: rgba(255,255,255,0.05) !important;">
            {{ $shipments->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection


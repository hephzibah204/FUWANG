@extends('layouts.nexus')

@section('title', 'Inventory - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Inventory', 'subtitle' => 'Inventory control and stock visibility'])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif

<div class="card glass-card border-0 rounded-lg p-0 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="p-4 border-bottom border-secondary d-flex flex-column flex-md-row justify-content-between" style="border-color: rgba(255,255,255,0.05) !important;">
        <form class="d-flex flex-wrap align-items-center" method="GET" action="{{ route('logistics.ops.inventory.index') }}">
            <div class="input-group mr-2 mb-2" style="max-width: 360px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fa fa-search"></i></span>
                </div>
                <input type="text" name="search" class="form-control form-control-sm bg-transparent border-secondary text-white shadow-none" placeholder="Search SKU, name, location..." value="{{ request('search') }}" style="border-radius: 20px; border-color: rgba(255,255,255,0.1) !important;">
            </div>
            <button class="btn btn-sm btn-outline-secondary mb-2" type="submit">Filter</button>
            @if(request('search'))
                <a class="btn btn-sm btn-outline-secondary ml-2 mb-2" href="{{ route('logistics.ops.inventory.index') }}">Clear</a>
            @endif
        </form>

        @if(auth('logistics_staff')->user()?->hasPermission('logistics.inventory.manage'))
            <a href="{{ route('logistics.ops.inventory.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 mb-2">
                <i class="fa fa-plus-circle mr-2"></i> New Item
            </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">SKU</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Name</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Qty</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Location</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Status</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4 font-weight-bold">{{ $i->sku }}</td>
                        <td class="py-3">{{ $i->name }}</td>
                        <td class="py-3 text-white-50">{{ $i->quantity }}</td>
                        <td class="py-3 text-white-50">{{ $i->location ?: '—' }}</td>
                        <td class="py-3">
                            @if($i->is_active)
                                <span class="badge badge-pill badge-success px-3 py-1">Active</span>
                            @else
                                <span class="badge badge-pill badge-danger px-3 py-1">Disabled</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if(auth('logistics_staff')->user()?->hasPermission('logistics.inventory.manage'))
                                <a href="{{ route('logistics.ops.inventory.edit', $i->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            @else
                                <span class="text-white-50 small">View only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-white-50">No inventory items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
        <div class="p-4 border-top border-secondary d-flex justify-content-center" style="border-color: rgba(255,255,255,0.05) !important;">
            {{ $items->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection


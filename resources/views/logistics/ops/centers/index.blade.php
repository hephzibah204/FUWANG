@extends('layouts.nexus')

@section('title', 'Centers - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Pickup & Drop-off Centers', 'subtitle' => 'Manage center availability across Nigeria'])

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
        <form class="d-flex flex-wrap align-items-center" method="GET" action="{{ route('logistics.ops.centers.index') }}">
            <input type="text" name="state" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 220px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" placeholder="Filter state..." value="{{ request('state') }}">
            <select name="type" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 220px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;">
                <option value="">All types</option>
                <option value="pickup" @selected(request('type')==='pickup')>pickup</option>
                <option value="dropoff" @selected(request('type')==='dropoff')>dropoff</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary mb-2" type="submit">Filter</button>
            @if(request('state') || request('type'))
                <a class="btn btn-sm btn-outline-secondary ml-2 mb-2" href="{{ route('logistics.ops.centers.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="p-4 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.05) !important;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-white mb-0 font-weight-bold">Add Center</h6>
            <span class="text-white-50 small">Officers and Managers can create, update, or delete centers</span>
        </div>
        <form method="POST" action="{{ route('logistics.ops.centers.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="text-white-50 small">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" required>
                </div>
                <div class="form-group col-md-2">
                    <label class="text-white-50 small">Type</label>
                    <select name="type" class="form-control form-control-sm" required>
                        <option value="pickup">pickup</option>
                        <option value="dropoff">dropoff</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="text-white-50 small">State</label>
                    <input type="text" name="state" class="form-control form-control-sm" required placeholder="e.g., Lagos">
                </div>
                <div class="form-group col-md-3">
                    <label class="text-white-50 small">City</label>
                    <input type="text" name="city" class="form-control form-control-sm" placeholder="e.g., Ikeja">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-5">
                    <label class="text-white-50 small">Address</label>
                    <input type="text" name="address" class="form-control form-control-sm" placeholder="Street address">
                </div>
                <div class="form-group col-md-2">
                    <label class="text-white-50 small">Lat</label>
                    <input type="number" step="0.0000001" name="lat" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-2">
                    <label class="text-white-50 small">Lng</label>
                    <input type="number" step="0.0000001" name="lng" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-2">
                    <label class="text-white-50 small">Availability</label>
                    <select name="availability_status" class="form-control form-control-sm" required>
                        <option value="available">available</option>
                        <option value="limited">limited</option>
                        <option value="closed">closed</option>
                    </select>
                </div>
                <div class="form-group col-md-1">
                    <label class="text-white-50 small">Cap</label>
                    <input type="number" name="capacity_per_day" class="form-control form-control-sm" min="0">
                </div>
            </div>
            <button class="btn btn-sm btn-outline-light" type="submit" style="border-radius: 12px;">Create</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Center</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">State</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Type</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Availability</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($centers as $c)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4">
                            <div class="font-weight-bold">{{ $c->name }}</div>
                            <div class="text-white-50 small">{{ $c->city ?: '—' }}</div>
                        </td>
                        <td class="py-3 text-white-50">{{ $c->state }}</td>
                        <td class="py-3 text-white-50">{{ $c->type }}</td>
                        <td class="py-3">
                            <span class="badge badge-pill badge-dark px-3">{{ strtoupper($c->availability_status) }}</span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <form method="POST" action="{{ route('logistics.ops.centers.update', $c->id) }}" class="d-flex flex-column flex-md-row justify-content-end">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 230px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" value="{{ $c->name }}">
                                <input type="text" name="address" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 240px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" placeholder="Address" value="{{ $c->address }}">
                                <input type="number" step="0.0000001" name="lat" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 140px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" placeholder="Lat" value="{{ $c->lat }}">
                                <input type="number" step="0.0000001" name="lng" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 140px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" placeholder="Lng" value="{{ $c->lng }}">
                                <select name="availability_status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 190px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;">
                                    @foreach(['available','limited','closed'] as $s)
                                        <option value="{{ $s }}" @selected($c->availability_status===$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="capacity_per_day" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 150px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" placeholder="Capacity" value="{{ $c->capacity_per_day }}">
                                <input type="number" name="current_load" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 150px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" placeholder="Load" value="{{ $c->current_load }}">
                                <input type="hidden" name="is_active" value="1">
                                <button class="btn btn-sm btn-outline-light mb-2" type="submit" style="border-radius: 12px;">Save</button>
                            </form>
                            <form method="POST" action="{{ route('logistics.ops.centers.destroy', $c->id) }}" onsubmit="return confirm('Delete this center?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" style="border-radius: 12px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white-50">No centers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($centers->hasPages())
        <div class="p-4 border-top border-secondary d-flex justify-content-center" style="border-color: rgba(255,255,255,0.05) !important;">
            {{ $centers->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection

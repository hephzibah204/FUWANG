@extends('layouts.nexus')

@section('title', 'Delivery Agents - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Delivery Agents', 'subtitle' => 'Onboard agents, manage availability, and track performance signals'])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif

<div class="card glass-card border-0 rounded-lg p-0 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="p-4 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.05) !important;">
        <form class="d-flex flex-wrap align-items-center" method="GET" action="{{ route('logistics.ops.agents.index') }}">
            <select name="approval_status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 220px; border-radius: 20px; border-color: rgba(255,255,255,0.1) !important;">
                <option value="">All approvals</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" @selected(request('approval_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="availability_status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 220px; border-radius: 20px; border-color: rgba(255,255,255,0.1) !important;">
                <option value="">All availability</option>
                @foreach(['available','on_delivery','offline'] as $s)
                    <option value="{{ $s }}" @selected(request('availability_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary mb-2" type="submit">Filter</button>
            @if(request('approval_status') || request('availability_status'))
                <a class="btn btn-sm btn-outline-secondary ml-2 mb-2" href="{{ route('logistics.ops.agents.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Agent</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Location</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Approval</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Availability</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Rating</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agents as $a)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4">
                            <div class="font-weight-bold">{{ $a->user?->fullname ?: ('Agent #' . $a->id) }}</div>
                            <div class="text-white-50 small">{{ $a->user?->email }}</div>
                        </td>
                        <td class="py-3 text-white-50">{{ $a->city ? ($a->city . ', ' . $a->state) : ($a->state ?: '—') }}</td>
                        <td class="py-3"><span class="badge badge-pill badge-secondary px-3 py-1">{{ $a->approval_status }}</span></td>
                        <td class="py-3"><span class="badge badge-pill badge-secondary px-3 py-1">{{ $a->availability_status }}</span></td>
                        <td class="py-3 text-white-50">{{ $a->rating ?? '—' }}</td>
                        <td class="py-3 px-4 text-right">
                            @if(auth('logistics_staff')->user()?->hasPermission('logistics.agents.onboard'))
                                <form method="POST" action="{{ route('logistics.ops.agents.update', $a->id) }}" class="d-flex flex-column flex-md-row justify-content-end">
                                    @csrf
                                    @method('PUT')
                                    <select name="approval_status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 190px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;">
                                        @foreach(['pending','approved','rejected'] as $s)
                                            <option value="{{ $s }}" @selected($a->approval_status===$s)>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    <select name="availability_status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 190px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;">
                                        @foreach(['available','on_delivery','offline'] as $s)
                                            <option value="{{ $s }}" @selected($a->availability_status===$s)>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2 mb-2" style="max-width: 120px; border-radius: 12px; border-color: rgba(255,255,255,0.1) !important;" value="{{ $a->rating }}">
                                    <button class="btn btn-sm btn-outline-light mb-2" type="submit" style="border-radius: 12px;">Save</button>
                                </form>
                            @else
                                <span class="text-white-50 small">No permissions</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-white-50">No agents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($agents->hasPages())
        <div class="p-4 border-top border-secondary d-flex justify-content-center" style="border-color: rgba(255,255,255,0.05) !important;">
            {{ $agents->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection

@extends('layouts.nexus')

@section('title', 'Logistics | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Logistics / Post Office</h1>
            <p class="text-muted mb-0">Track shipments and update delivery status.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="admin-panel p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;">
        <form method="GET" class="row">
            <div class="col-lg-6 col-12 mb-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search tracking, sender, recipient">
            </div>
            <div class="col-lg-3 col-12 mb-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['processing','in_transit','delivered','completed','cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-12 mb-3">
                <button class="btn btn-primary w-100" type="submit"><i class="fa fa-search mr-2"></i>Filter</button>
            </div>
        </form>
    </div>

    <div class="admin-panel mt-4">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Tracking</th>
                        <th>User</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $sh)
                        <tr>
                            <td class="align-middle"><code class="text-primary">{{ $sh->tracking_id }}</code></td>
                            <td class="align-middle">
                                <div class="text-white">{{ $sh->user?->fullname ?? '—' }}</div>
                                <div class="small text-muted">{{ $sh->user?->email ?? '—' }}</div>
                            </td>
                            <td class="align-middle">
                                <div class="text-white">{{ $sh->sender_name }} → {{ $sh->recipient_name }}</div>
                                <div class="small text-muted">{{ $sh->delivery_type }} • {{ $sh->weight }}kg</div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-pill {{ in_array($sh->status, ['delivered','completed']) ? 'badge-success' : ($sh->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $sh->status }}
                                </span>
                            </td>
                            <td class="align-middle text-right text-white">₦{{ number_format((float) $sh->amount, 2) }}</td>
                            <td class="align-middle text-right pr-4">
                                <form method="POST" action="{{ route('admin.operations.logistics.status', $sh->id) }}" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-control d-inline-block" style="width: 160px; height: 34px;" onchange="this.form.submit()">
                                        @foreach(['processing','in_transit','delivered','completed','cancelled'] as $st)
                                            <option value="{{ $st }}" {{ $sh->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                @if($sh->waybill_path)
                                    <a class="btn btn-sm btn-success ml-2" href="{{ \Illuminate\Support\Facades\Storage::url($sh->waybill_path) }}" target="_blank"><i class="fa fa-file-pdf mr-1"></i>Waybill</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No shipments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $shipments->links() }}
    </div>
</div>
@endsection


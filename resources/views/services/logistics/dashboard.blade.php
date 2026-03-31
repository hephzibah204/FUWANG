@extends('layouts.postoffice')

@section('title', 'Logistics Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="font-weight-bold mb-1">My <span style="color:var(--po-primary)">Shipments</span></h3>
            <p class="text-white-50 small mb-0">Track and manage your logistics activity.</p>
        </div>
        <a href="{{ route('user.logistics.book') }}" class="btn btn-po-primary px-4 shadow-sm">
            <i class="fa fa-plus-circle mr-1"></i> New Shipment
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255,255,255,0.05); color: var(--po-primary);">
                <i class="fa fa-box-open fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Total Booked</small>
                <h4 class="font-weight-bold mb-0">{{ $stats['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); color: var(--po-accent);">
                <i class="fa fa-truck-fast fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Active Logistics</small>
                <h4 class="font-weight-bold mb-0 text-primary">{{ $stats['active'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(34, 197, 94, 0.1); color: #22c55e);">
                <i class="fa fa-check-circle fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Delivered</small>
                <h4 class="font-weight-bold mb-0 text-success">{{ $stats['delivered'] }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Shipments Table -->
<div class="glass-card overflow-hidden">
    <div class="p-4 border-bottom border-white-10 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 font-weight-bold">Recent History</h5>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-glass dropdown-toggle" data-toggle="dropdown">All Statuses</button>
            <div class="dropdown-menu dropdown-menu-right bg-dark border-glass mt-2">
                <a class="dropdown-item text-white" href="#">Processing</a>
                <a class="dropdown-item text-white" href="#">In Transit</a>
                <a class="dropdown-item text-white" href="#">Delivered</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="color: #eee;">
            <thead class="bg-black-10 small text-uppercase" style="letter-spacing: 1px;">
                <tr>
                    <th class="border-0 px-4">Tracking ID</th>
                    <th class="border-0">Recipient</th>
                    <th class="border-0">Service</th>
                    <th class="border-0">Status</th>
                    <th class="border-0">Date</th>
                    <th class="border-0 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myShipments as $s)
                    <tr class="border-white-05">
                        <td class="px-4"><code class="text-primary">{{ $s->tracking_id }}</code></td>
                        <td>
                            <div class="font-weight-bold">{{ $s->recipient_name }}</div>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($s->recipient_address, 30) }}</small>
                        </td>
                        <td>
                            <span class="badge badge-pill badge-dark px-3">{{ strtoupper($s->delivery_type) }}</span>
                        </td>
                        <td>
                            @php
                                $badge = match($s->status) {
                                    'processing' => 'badge-info',
                                    'in_transit' => 'badge-primary',
                                    'delivered' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    default => 'badge-light'
                                };
                            @endphp
                            <span class="badge badge-pill {{ $badge }} px-2 py-1" style="min-width: 80px;">{{ strtoupper(str_replace('_', ' ', $s->status)) }}</span>
                        </td>
                        <td class="small text-white-50">{{ $s->created_at->format('M d, Y') }}</td>
                        <td class="text-right px-4">
                            @if($s->waybill_path)
                                <a href="{{ Storage::url($s->waybill_path) }}" target="_blank" class="btn btn-sm btn-outline-glass" title="Download Waybill">
                                    <i class="fa fa-file-pdf"></i>
                                </a>
                            @endif
                            <button class="btn btn-sm btn-outline-glass ml-1 po-track-btn" data-id="{{ $s->tracking_id }}">
                                <i class="fa fa-location-dot"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-white-50 italic">
                            No shipments found. Start by booking your first package.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($myShipments->hasPages())
        <div class="p-4 border-top border-white-10">
            {{ $myShipments->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    $('.po-track-btn').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Please Wait',
            html: 'Fetching tracking data...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() }
        });

        $.post("{{ route('public.logistics.track') }}", {
            _token: "{{ csrf_token() }}",
            tracking_id: id
        }, function(res) {
            Swal.close();
            if (res.status) {
                let steps = '';
                res.tracking.timeline.forEach(s => {
                    steps += `<div style="text-align:left; margin-bottom:10px; display:flex; align-items:center;">
                        <i class="fa ${s.done ? 'fa-check-circle text-success' : 'fa-circle text-muted'} mr-2"></i>
                        <div><b>${s.event}</b><br><small>${s.time}</small></div>
                    </div>`;
                });

                Swal.fire({
                    title: 'Track Shipment: ' + id,
                    html: `<div class="p-3 text-left bg-dark text-white rounded mb-3">
                        <b>Current Status:</b> <span class="text-warning">${res.tracking.status}</span><br>
                        <b>Last Update:</b> ${res.tracking.updated}
                    </div>` + steps,
                    background: '#1e293b',
                    color: '#fff',
                    confirmButtonColor: '#f59e0b'
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });
</script>
@endpush
@endsection

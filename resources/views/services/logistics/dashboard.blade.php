@extends('layouts.postoffice')

@section('title', 'Logistics Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="font-weight-bold mb-1">My <span style="color:var(--po-primary)">Shipments</span></h3>
            <p class="text-white-50 small mb-0">Track and manage your logistics activity.</p>
        </div>
        <div class="d-flex flex-wrap justify-content-end">
            <a href="{{ \Illuminate\Support\Facades\Route::has('services.user.logistics.book') ? route('services.user.logistics.book') : route('logistics.book') }}" class="btn btn-po-primary px-4 shadow-sm mb-2">
                <i class="fa fa-plus-circle mr-1"></i> New Shipment
            </a>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255,255,255,0.05); color: var(--po-primary);">
                <i class="fa fa-box-open fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Sent Bookings</small>
                <h4 class="font-weight-bold mb-0">{{ $stats['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); color: var(--po-accent);">
                <i class="fa fa-truck-fast fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Active Sent</small>
                <h4 class="font-weight-bold mb-0 text-primary">{{ $stats['active'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="glass-card p-4 d-flex align-items-center border border-warning" style="background: rgba(245, 158, 11, 0.08);">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
                <i class="fa fa-boxes-packing fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Incoming Packages</small>
                <h4 class="font-weight-bold mb-0 text-warning">{{ $stats['incoming_ready'] }} <small class="text-muted" style="font-size:0.75rem;">to collect</small></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <i class="fa fa-check-circle fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Delivered Sent</small>
                <h4 class="font-weight-bold mb-0 text-success">{{ $stats['delivered'] }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Incoming Packages (Packages to Collect / Receive) Section -->
<div class="glass-card overflow-hidden mb-4 border border-warning">
    <div class="p-4 border-bottom border-white-10 d-flex justify-content-between align-items-center bg-dark" style="background: rgba(245, 158, 11, 0.05) !important;">
        <div>
            <h5 class="mb-0 font-weight-bold text-white">
                <i class="fa fa-boxes-packing text-warning mr-2"></i>Packages Arriving / Ready For Collection
            </h5>
            <small class="text-white-50">Fulfillment packages sent to you by shippers & merchants</small>
        </div>
        <span class="badge badge-warning px-3 py-2 font-weight-bold" style="font-size:0.85rem;">
            {{ $incomingPackages->total() }} Incoming Package(s)
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="color: #eee;">
            <thead class="bg-black-10 small text-uppercase" style="letter-spacing: 1px;">
                <tr>
                    <th class="border-0 px-4">Tracking ID</th>
                    <th class="border-0">Sender</th>
                    <th class="border-0">Fulfillment Method</th>
                    <th class="border-0">Status</th>
                    <th class="border-0">Collection Center / Address</th>
                    <th class="border-0 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomingPackages as $inc)
                    <tr class="border-white-05">
                        <td class="px-4"><code class="text-warning font-weight-bold">{{ $inc->tracking_id }}</code></td>
                        <td>
                            <div class="font-weight-bold">{{ $inc->sender_name }}</div>
                            <small class="text-white-50">{{ $inc->sender_state }}</small>
                        </td>
                        <td>
                            @if($inc->delivery_method === 'center_pickup')
                                <span class="badge badge-pill badge-warning px-3"><i class="fa fa-building-circle-check mr-1"></i> Center Pickup</span>
                            @else
                                <span class="badge badge-pill badge-info px-3"><i class="fa fa-house-chimney-user mr-1"></i> Home Delivery</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $incBadge = match($inc->status) {
                                    'processing' => 'badge-info',
                                    'in_transit' => 'badge-primary',
                                    'arrived_at_center', 'out_for_delivery' => 'badge-warning',
                                    'delivered' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    default => 'badge-light'
                                };
                                $statusLabel = match($inc->status) {
                                    'arrived_at_center' => 'READY FOR PICKUP',
                                    'out_for_delivery' => 'OUT FOR DELIVERY',
                                    default => strtoupper(str_replace('_', ' ', $inc->status))
                                };
                            @endphp
                            <span class="badge badge-pill {{ $incBadge }} px-2 py-1" style="min-width: 90px;">{{ $statusLabel }}</span>
                        </td>
                        <td class="small text-white-50">
                            @if($inc->delivery_method === 'center_pickup' && $inc->dropoffCenter)
                                <strong class="text-white d-block"><i class="fa fa-location-dot text-warning mr-1"></i> {{ $inc->dropoffCenter->name }}</strong>
                                {{ $inc->dropoffCenter->address }}
                            @else
                                {{ \Illuminate\Support\Str::limit($inc->recipient_address ?: $inc->recipient_state, 35) }}
                            @endif
                        </td>
                        <td class="text-right px-4">
                            <button class="btn btn-sm btn-warning po-track-btn" data-id="{{ $inc->tracking_id }}">
                                <i class="fa fa-location-dot mr-1"></i> Track & Collect
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-white-50 italic">
                            <i class="fa fa-box-open me-2"></i> No incoming fulfillment packages found addressed to you.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($incomingPackages->hasPages())
        <div class="p-3 border-top border-white-10">
            {{ $incomingPackages->links() }}
        </div>
    @endif
</div>

<!-- My Sent Shipments Table -->
<div class="glass-card overflow-hidden">
    <div class="p-4 border-bottom border-white-10 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 font-weight-bold">My Sent Shipments</h5>
            <small class="text-white-50">Packages you booked and dispatched</small>
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

        $.post("{{ route('logistics.track') }}", {
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

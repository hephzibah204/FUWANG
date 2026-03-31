@extends('layouts.auction')

@section('title', ($lot->title ?? 'Auction Lot') . ' | ' . config('app.name'))

@section('content')
@php
    $isAuthed = auth()->check();
    $imgs = $lot->images;
    $heroImg = $imgs->first()?->url;
    $status = $lot->status;
    $isLive = $status === 'live';
    $isEnded = $status === 'ended';
    $endIso = $lot->end_at ? $lot->end_at->toIso8601String() : null;
@endphp

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="rounded-xl overflow-hidden shadow-lg border-glass bg-glass">
            <div style="height: 400px; background: rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center;">
                @if($heroImg)
                    <img src="{{ $heroImg }}" alt="{{ $lot->title }}" id="mainHeroImage" style="width:100%; height:100%; object-fit:contain;">
                @else
                    <i class="fa-solid fa-image fa-4x text-white-10"></i>
                @endif
            </div>
            @if($imgs->count() > 1)
                <div class="p-3 d-flex flex-wrap" style="gap: 12px; background: rgba(0,0,0,0.2);">
                    @foreach($imgs as $img)
                        <button type="button" class="btn p-0 border-0 rounded overflow-hidden shadow-sm hover-scale" onclick="document.getElementById('mainHeroImage').src='{{ $img->url }}'" style="width: 60px; height: 45px; transition: transform 0.2s;">
                            <img src="{{ $img->url }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-4 p-4 rounded-xl border-glass bg-glass">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge px-3 py-2 text-white" style="background: {{ $isLive ? '#ef4444' : ($isEnded ? '#6b7280' : '#3b82f6') }};">
                    {{ strtoupper($status) }}
                </span>
                <span class="text-white-50 small">Lot {{ $lot->lot_code }}</span>
            </div>
            <h1 class="h2 font-weight-bold mb-3">{{ $lot->title }}</h1>
            <div class="d-flex flex-wrap mb-4" style="gap: 10px;">
                <span class="badge badge-pill badge-outline-glass py-2 px-3 text-white-50"><i class="fa-solid fa-tag mr-1"></i> {{ $lot->category }}</span>
                <span class="badge badge-pill badge-outline-glass py-2 px-3 text-white-50"><i class="fa-solid fa-location-dot mr-1"></i> {{ $lot->location ?? 'Global' }}</span>
            </div>
            <div class="text-white-50" style="line-height: 1.6; font-size: 1.05rem;">
                {!! nl2br(e($lot->description ?: 'No additional description provided for this item.')) !!}
            </div>
        </div>

        <!-- Bid History List -->
        <div class="mt-4 p-4 rounded-xl border-glass bg-glass">
            <h5 class="font-weight-bold mb-4">Bid History</h5>
            <div id="bidHistoryList">
                @forelse($bids as $b)
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-white-10">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 rounded-circle bg-dark d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-user text-white-50 small"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ $b['bidder'] }}</div>
                                <div class="small text-white-50">{{ $b['created_at']->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-weight-bold {{ $b['status'] === 'winning' ? 'text-success' : 'text-white-50' }}">
                                ₦{{ number_format((float) $b['amount'], 2) }}
                            </div>
                            @if($b['status'] === 'winning')
                                <span class="badge badge-success small">Winning</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-white-50 small">No bids yet. Be the first to bid!</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bidding Sidebar -->
    <div class="col-lg-5 mb-4">
        <div class="p-4 rounded-xl border-glass bg-glass sticky-top shadow-lg" style="top: 100px;">
            <div class="row mb-4">
                <div class="col-6">
                    <div class="text-white-50 small mb-1">Current Bid</div>
                    <div class="h3 font-weight-bold text-primary mb-0" id="currentPriceDisplay">₦{{ number_format((float) $lot->current_price, 2) }}</div>
                </div>
                <div class="col-6 text-right">
                    <div class="text-white-50 small mb-1">Ends In</div>
                    <div class="h4 font-weight-bold js-countdown mb-0" data-end="{{ $endIso }}">{{ $lot->end_at ? $lot->end_at->diffForHumans() : '—' }}</div>
                </div>
            </div>

            <div class="p-3 rounded mb-4" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);">
                <div class="d-flex justify-content-between small">
                    <span class="text-white-50">Min. Next Bid:</span>
                    <span class="font-weight-bold text-warning" id="minBidDisplay">₦{{ number_format((float) $lot->current_price + (float) $lot->bid_increment, 2) }}</span>
                </div>
            </div>

            @if($isAuthed)
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">Your Maximum Bid</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark border-0 text-white-50">₦</span>
                        </div>
                        <input type="number" step="0.01" class="form-control" id="bidAmountInput" placeholder="Enter amount" style="height: 50px;">
                    </div>
                </div>
                <button type="button" id="btnPlaceBid" class="btn btn-primary btn-lg w-100 py-3 mb-3 shadow-glow" style="background: var(--auction-primary); border:none; font-weight: 700; border-radius: 12px;">
                    PLACE BID
                </button>
                <div class="text-center small text-white-50 mb-0">No fees. Only pay if you win.</div>
            @else
                <div class="p-4 text-center rounded bg-dark border-glass">
                    <p class="mb-3">Sign in to participate in this auction.</p>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block mb-2">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-block btn-sm">Create Account</a>
                </div>
            @endif

            <hr class="border-white-10 my-4">

            <div class="mb-3 d-flex align-items-center">
                <div class="mr-3" style="width: 48px; height: 48px; border-radius: 12px; overflow: hidden; background: rgba(0,0,0,0.2);">
                    @if($lot->seller?->avatar_url)
                        <img src="{{ $lot->seller->avatar_url }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100"><i class="fa-solid fa-store text-white-50"></i></div>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="font-weight-bold">{{ $lot->seller?->name ?? 'System Auction' }}</div>
                    <div class="small text-white-50">Verified Seller · {{ $lot->seller?->location ?? 'Lagos' }}</div>
                </div>
            </div>

            <div class="d-flex flex-column" style="gap: 12px;">
                <div class="d-flex align-items-center text-white-50 small"><i class="fa-solid fa-shield-check mr-2 text-success"></i> Secure Escrow Protected</div>
                <div class="d-flex align-items-center text-white-50 small"><i class="fa-solid fa-truck-fast mr-2"></i> Shipping available nationwide</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); }
    .border-glass { border: 1px solid rgba(255,255,255,0.08); }
    .rounded-xl { border-radius: 20px; }
    .hover-scale:hover { transform: scale(1.05); }
    .btn-outline-glass { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const endIso = @json($endIso);
    function tick() {
        if (!endIso) return;
        const diff = new Date(endIso).getTime() - Date.now();
        const el = document.querySelector('.js-countdown');
        if (!el) return;
        if (diff <= 0) { el.textContent = 'Ended'; return; }
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.textContent = `${h}h ${m}m ${s}s`;
    }
    tick(); setInterval(tick, 1000);
})();

@if($isAuthed)
$(() => {
    const streamUrl = @json(route('realtime.auctions.stream'));
    const lotCode = @json($lot->lot_code);
    const es = new EventSource(streamUrl + '?lot=' + encodeURIComponent(lotCode) + '&ttl=300');

    es.addEventListener('snapshot', (e) => {
        try {
            const data = JSON.parse(e.data);
            if (data.scope !== 'lot' || !data.lot) return;
            const price = Number(data.lot.current_price);
            $('#currentPriceDisplay').text('₦' + price.toLocaleString(undefined, {minimumFractionDigits:2}));
            $('#minBidDisplay').text('₦' + (price + Number(@json($lot->bid_increment))).toLocaleString(undefined, {minimumFractionDigits:2}));
            
            if (data.bids) {
                const list = $('#bidHistoryList');
                list.html(data.bids.map(b => `
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-white-10">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 rounded-circle bg-dark d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-user text-white-50 small"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">${b.bidder}</div>
                                <div class="small text-white-50">Just now</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-weight-bold ${b.status === 'winning' ? 'text-success' : 'text-white-50'}">
                                ₦${Number(b.amount).toLocaleString(undefined, {minimumFractionDigits:2})}
                            </div>
                            ${b.status === 'winning' ? '<span class="badge badge-success small">Winning</span>' : ''}
                        </div>
                    </div>
                `).join(''));
            }
        } catch(err) {}
    });

    $('#btnPlaceBid').on('click', function() {
        const amt = $('#bidAmountInput').val();
        if(!amt) return alert('Please enter a bid amount.');
        
        const btn = $(this);
        btn.prop('disabled', true).text('PROCESSING...');

        $.ajax({
            url: "{{ \Illuminate\Support\Facades\Route::has('auctions.bid') ? route('auctions.bid') : url('/auctions/bid') }}",
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                lot_code: lotCode,
                amount: amt
            },
            success: function(res) {
                if(res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message,
                        background: '#1e293b',
                        color: '#fff',
                        confirmButtonColor: 'var(--auction-primary)'
                    });
                    $('#bidAmountInput').val('');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                Swal.fire({ icon: 'error', title: 'Bid Failed', text: msg });
            },
            complete: function() {
                btn.prop('disabled', false).text('PLACE BID');
            }
        });
    });
});
@endif
</script>
@endpush

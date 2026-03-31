@extends('layouts.auction')

@section('title', 'Browse Auctions')

@section('content')
<div class="mb-5">
    @if(!empty($dbError))
        <div class="mb-4 p-3 rounded-lg" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25);">
            <div class="d-flex align-items-start" style="gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b; margin-top: 3px;"></i>
                <div>
                    <div class="font-weight-bold" style="color: #fbbf24;">Auctions temporarily unavailable</div>
                    <div class="text-white-50 small">{{ $dbError }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h1 class="font-weight-bold mb-2">Live Auctions</h1>
            <p class="text-white-50 mb-0">Discover and bid on exclusive items. Real-time updates active.</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-glass dropdown-toggle" data-toggle="dropdown">
                    Sort: {{ $filters['sort'] === 'price_low' ? 'Price Low' : ($filters['sort'] === 'price_high' ? 'Price High' : 'Ending Soon') }}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('public.auctions.index', array_merge($filters, ['sort' => 'ending_soon'])) }}">Ending Soon</a>
                    <a class="dropdown-item" href="{{ route('public.auctions.index', array_merge($filters, ['sort' => 'newest'])) }}">Newest</a>
                    <a class="dropdown-item" href="{{ route('public.auctions.index', array_merge($filters, ['sort' => 'price_low'])) }}">Price: Low to High</a>
                    <a class="dropdown-item" href="{{ route('public.auctions.index', array_merge($filters, ['sort' => 'price_high'])) }}">Price: High to Low</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar (Summary) -->
    <div class="mb-4 d-flex flex-wrap" style="gap: 10px;">
        @if($filters['q'])
            <span class="badge badge-pill badge-outline-glass py-2 px-3">Search: {{ $filters['q'] }} <a href="{{ route('public.auctions.index', array_merge($filters, ['q' => ''])) }}" class="ml-2 text-white-50"><i class="fa-solid fa-xmark"></i></a></span>
        @endif
        @if($filters['category'])
            <span class="badge badge-pill badge-outline-glass py-2 px-3">Category: {{ $filters['category'] }} <a href="{{ route('public.auctions.index', array_merge($filters, ['category' => ''])) }}" class="ml-2 text-white-50"><i class="fa-solid fa-xmark"></i></a></span>
        @endif
    </div>

    <div class="row">
        @forelse($lots as $lot)
            @php
                $img = $lot->images->first()?->url;
                $status = $lot->status;
                $endAt = $lot->end_at ? $lot->end_at->toIso8601String() : null;
            @endphp
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="{{ route('public.auctions.show', ['lotCode' => $lot->lot_code]) }}" class="text-decoration-none d-block h-100 card-auction">
                    <div class="h-100 rounded-lg overflow-hidden" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03); transition: transform 0.2s;">
                        <div style="height: 200px; background: rgba(0,0,0,0.25); display:flex; align-items:center; justify-content:center; position: relative;">
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $lot->title }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="fa-solid fa-image fa-2x text-white-50"></i>
                            @endif
                            <div class="position-absolute" style="top: 15px; left: 15px;">
                                <span class="badge js-status px-2 py-1" data-code="{{ $lot->lot_code }}" style="background: {{ $status === 'live' ? '#ef4444' : ($status === 'scheduled' ? '#3b82f6' : '#6b7280') }};">
                                    {{ strtoupper($status) }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="h5 text-white font-weight-bold mb-1 truncate">{{ $lot->title }}</h3>
                            <div class="text-white-50 small mb-3">{{ $lot->category }} · Lot {{ $lot->lot_code }}</div>
                            
                            <div class="d-flex justify-content-between align-items-end pt-3 border-top border-white-10">
                                <div>
                                    <div class="text-white-50 small">Current Bid</div>
                                    <div class="h5 text-primary font-weight-bold mb-0 js-price" data-code="{{ $lot->lot_code }}">₦{{ number_format((float) $lot->current_price, 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-white-50 small">Ends In</div>
                                    <div class="text-white font-weight-bold mb-0 js-countdown" data-code="{{ $lot->lot_code }}" data-end="{{ $endAt }}">{{ $lot->end_at ? $lot->end_at->diffForHumans() : '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-box-open fa-3x text-white-10 mb-3"></i>
                <h3 class="h5">No items found</h3>
                <p class="text-white-50">Try adjusting your search or filters.</p>
                <a href="{{ route('public.auctions.index') }}" class="btn btn-outline-light">Clear All Filters</a>
            </div>
        @endforelse
    </div>

    <div class="mt-5">
        {{ $lots->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-auction:hover div { transform: translateY(-5px); border-color: var(--auction-primary) !important; }
    .badge-outline-glass { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
    .truncate { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush

@push('scripts')
<script>
(() => {
  function fmt(ms) {
    if (ms <= 0) return 'Ended';
    const s = Math.floor(ms / 1000);
    const d = Math.floor(s / 86400);
    const h = Math.floor((s % 86400) / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    if (d > 0) return d + 'd ' + h + 'h';
    if (h > 0) return h + 'h ' + m + 'm';
    return m + 'm ' + sec + 's';
  }
  function tick() {
    document.querySelectorAll('.js-countdown').forEach(el => {
      const end = el.getAttribute('data-end');
      if (!end) return;
      el.textContent = fmt(new Date(end).getTime() - Date.now());
    });
  }
  tick();
  setInterval(tick, 1000);
})();
</script>
<script>
(() => {
  const streamUrl = @json(route('realtime.auctions.stream'));
  const es = new EventSource(streamUrl + '?ttl=300&interval=5');

  function fmtPrice(v) {
    return '₦' + Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  es.addEventListener('snapshot', (e) => {
    try {
      const data = JSON.parse(e.data || '{}');
      if (!data.lots) return;
      data.lots.forEach(l => {
        const priceEl = document.querySelector('.js-price[data-code="' + l.lot_code + '"]');
        if (priceEl) priceEl.textContent = fmtPrice(l.current_price);
        const stEl = document.querySelector('.js-status[data-code="' + l.lot_code + '"]');
        if (stEl) {
          stEl.textContent = l.status.toUpperCase();
          stEl.style.background = l.status === 'live' ? '#ef4444' : (l.status === 'scheduled' ? '#3b82f6' : '#6b7280');
        }
      });
    } catch (err) {}
  });
})();
</script>
@endpush

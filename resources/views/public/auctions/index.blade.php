@extends('layouts.auction')

@section('title', 'Browse Auctions')

@section('content')
@php
    $totalLots = method_exists($lots, 'total') ? (int) $lots->total() : 0;
    $isAuthed = auth()->check();
@endphp

<div class="mb-5">
    @if(!empty($dbError))
        <div class="mb-4 p-3 rounded-lg" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25);">
            <div class="d-flex align-items-start" style="gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation" style="color: var(--auction-primary); margin-top: 3px;"></i>
                <div>
                    <div class="font-weight-bold" style="color: #fbbf24;">Auctions temporarily unavailable</div>
                    <div class="text-white-50 small">{{ $dbError }}</div>
                </div>
            </div>
        </div>
    @endif

    <section class="auction-hero glass-card p-4 p-md-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill auction-pill mb-3">
                    <i class="fa-solid fa-bolt mr-2"></i>
                    <span class="small">Real-time bidding • Verified sellers • Wallet-backed</span>
                </div>
                <h1 class="display-4 font-weight-bold mb-3">
                    Auction Hub
                    <span style="color: var(--auction-primary);">built for speed</span>
                </h1>
                <p class="lead text-white-50 mb-4" style="max-width: 640px;">
                    Browse live lots, place bids instantly, and track your watchlist from one clean dashboard.
                </p>

                <div class="d-flex flex-wrap align-items-center" style="gap: 12px;">
                    @if($isAuthed)
                        <a href="{{ route('auction.dashboard') }}" class="btn btn-auction-primary">
                            <i class="fa-solid fa-gauge-high mr-2"></i> Open dashboard
                        </a>
                    @else
                        <a href="{{ route('auction.login') }}" class="btn btn-auction-primary">
                            <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Sign in to bid
                        </a>
                        <a href="{{ route('auction.register') }}" class="btn btn-outline-light" style="border-radius: 12px;">
                            <i class="fa-solid fa-user-plus mr-2"></i> Create account
                        </a>
                    @endif
                    <a href="#liveLots" class="btn btn-outline-light" style="border-radius: 12px;">
                        <i class="fa-solid fa-gavel mr-2"></i> Browse lots
                    </a>
                </div>

                <div class="d-flex flex-wrap mt-4" style="gap: 10px;">
                    <div class="auction-stat">
                        <div class="auction-stat__k">Live & Upcoming</div>
                        <div class="auction-stat__v">{{ number_format($totalLots) }}</div>
                    </div>
                    <div class="auction-stat">
                        <div class="auction-stat__k">Categories</div>
                        <div class="auction-stat__v">{{ number_format(is_iterable($categories ?? null) ? count($categories) : 0) }}</div>
                    </div>
                    <div class="auction-stat">
                        <div class="auction-stat__k">Last refresh</div>
                        <div class="auction-stat__v">{{ ($updatedAt ?? now())->format('H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="glass-card p-4 auction-search-card">
                    <div class="font-weight-bold mb-2">Find a lot</div>
                    <div class="text-white-50 small mb-3">Search by title or lot code, then filter by category/location.</div>
                    <form action="{{ route('public.auctions.index') }}" method="GET">
                        <div class="form-group mb-3">
                            <div class="position-relative">
                                <i class="fa-solid fa-magnifying-glass position-absolute auction-search-ico"></i>
                                <input class="form-control tracking-input pl-5" type="search" name="q" placeholder="e.g. iPhone, A-1000, Land" value="{{ $filters['q'] }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <select name="category" class="form-control tracking-input">
                                    <option value="">All categories</option>
                                    @foreach(($categories ?? []) as $cat)
                                        <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <select name="location" class="form-control tracking-input">
                                    <option value="">All locations</option>
                                    @foreach(($locations ?? []) as $loc)
                                        <option value="{{ $loc }}" @selected(($filters['location'] ?? '') === $loc)>{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <select name="sort" class="form-control tracking-input">
                                    <option value="ending_soon" @selected(($filters['sort'] ?? '') === 'ending_soon')>Ending soon</option>
                                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                                    <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: Low to High</option>
                                    <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: High to Low</option>
                                </select>
                            </div>
                            <div class="form-group col-md-5">
                                <button type="submit" class="btn btn-auction-primary btn-block">Search</button>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap mt-2" style="gap: 10px;">
                        @if(($filters['q'] ?? '') !== '' || ($filters['category'] ?? '') !== '' || ($filters['location'] ?? '') !== '' || ($filters['status'] ?? '') !== '')
                            <a href="{{ route('public.auctions.index') }}" class="small text-white-50 text-decoration-none">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Clear filters
                            </a>
                        @endif
                        <a href="{{ route('auction.admin.dashboard') }}" class="small text-white-50 text-decoration-none">
                            <i class="fa-solid fa-shield-halved mr-1"></i> Auction admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center mb-2">
                        <div class="auction-icon mr-3"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <div class="font-weight-bold">Browse and shortlist</div>
                    </div>
                    <div class="text-white-50 small">Filter by category, location, and end time. Save items to your watchlist.</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center mb-2">
                        <div class="auction-icon mr-3"><i class="fa-solid fa-gavel"></i></div>
                        <div class="font-weight-bold">Bid in real time</div>
                    </div>
                    <div class="text-white-50 small">Live updates keep prices current. You always see the latest bid.</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center mb-2">
                        <div class="auction-icon mr-3"><i class="fa-solid fa-shield-check"></i></div>
                        <div class="font-weight-bold">Win with confidence</div>
                    </div>
                    <div class="text-white-50 small">Transparent bid history and seller details help you decide faster.</div>
                </div>
            </div>
        </div>
    </section>

    <section id="liveLots" class="mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3" style="gap: 12px;">
            <div>
                <h2 class="h3 font-weight-bold mb-1">Live Auctions</h2>
                <div class="text-white-50">Discover and bid on exclusive items. Real-time updates active.</div>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <span class="badge badge-pill auction-pill px-3 py-2">
                    <i class="fa-solid fa-clock mr-2"></i>
                    Sort: {{ $filters['sort'] === 'price_low' ? 'Price Low' : ($filters['sort'] === 'price_high' ? 'Price High' : ($filters['sort'] === 'newest' ? 'Newest' : 'Ending Soon')) }}
                </span>
            </div>
        </div>

        <div class="row">
            @forelse($lots as $lot)
                @php
                    $img = $lot->images->first()?->url;
                    $status = $lot->status;
                    $endAt = $lot->end_at ? $lot->end_at->toIso8601String() : null;
                @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <a href="{{ route('public.auctions.show', ['lotCode' => $lot->lot_code]) }}" class="text-decoration-none d-block h-100">
                        <div class="glass-card h-100 auction-card">
                            <div class="auction-card__img">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $lot->title }}">
                                @else
                                    <i class="fa-solid fa-image fa-2x text-white-50"></i>
                                @endif
                                <div class="auction-card__badge">
                                    <span class="badge js-status px-2 py-1" data-code="{{ $lot->lot_code }}" style="background: {{ $status === 'live' ? '#ef4444' : ($status === 'scheduled' ? '#3b82f6' : '#6b7280') }};">
                                        {{ strtoupper($status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="h5 text-white font-weight-bold mb-1 auction-truncate">{{ $lot->title }}</h3>
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

        <div class="mt-4">
            {{ $lots->links() }}
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .auction-hero { position: relative; overflow: hidden; }
    .auction-hero::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 20% 20%, rgba(245, 158, 11, 0.18) 0%, transparent 45%),
            radial-gradient(circle at 80% 60%, rgba(59, 130, 246, 0.14) 0%, transparent 45%);
        transform: rotate(8deg);
        pointer-events: none;
    }
    .auction-hero > * { position: relative; }
    .auction-pill { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
    .auction-stat { padding: 14px 16px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.18); min-width: 160px; }
    .auction-stat__k { font-size: 12px; color: rgba(255,255,255,0.6); }
    .auction-stat__v { font-weight: 800; font-size: 20px; color: #fff; }
    .auction-search-card { border: 1px solid rgba(255,255,255,0.08); }
    .auction-search-ico { left: 16px; top: 14px; color: rgba(255,255,255,0.45); }
    .auction-icon { width: 42px; height: 42px; border-radius: 12px; display:flex; align-items:center; justify-content:center; background: rgba(245,158,11,0.16); color: var(--auction-primary); }
    .auction-card { transition: transform 0.2s ease, border-color 0.2s ease; }
    .auction-card:hover { transform: translateY(-6px); border-color: rgba(245,158,11,0.45); }
    .auction-card__img { height: 210px; background: rgba(0,0,0,0.25); display:flex; align-items:center; justify-content:center; position: relative; overflow: hidden; border-top-left-radius: 20px; border-top-right-radius: 20px; }
    .auction-card__img img { width:100%; height:100%; object-fit:cover; }
    .auction-card__badge { position: absolute; top: 14px; left: 14px; }
    .auction-truncate { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
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

@extends('layouts.auction')

@section('title', 'Browse Auctions')

@section('content')
@php
    $totalLots = method_exists($lots, 'total') ? (int) $lots->total() : 0;
    $isAuthed = auth()->check();
@endphp

<div class="mb-5">
    @if(!empty($dbError))
        <div class="mb-4 p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25);">
            <div class="d-flex align-items-start" style="gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation" style="color: var(--auction-primary); margin-top: 3px;"></i>
                <div>
                    <div class="font-weight-bold" style="color: #d97706;">Auctions temporarily unavailable</div>
                    <div class="small" style="color: var(--auction-text-muted);">{{ $dbError }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Hero Section: Institutional Marketplace Header -->
    <section class="auction-hero-panel p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center px-3 py-1.5 rounded-pill mb-3" style="background: rgba(217, 119, 6, 0.12); border: 1px solid rgba(217, 119, 6, 0.25); color: var(--auction-primary);">
                    <i class="fa-solid fa-scale-balanced mr-2" style="font-size: 0.8125rem;"></i>
                    <span class="small font-weight-semibold">Institutional Asset Liquidation &amp; Bidding</span>
                </div>
                <h1 class="display-5 font-weight-bold mb-3" style="letter-spacing: -0.025em; color: var(--auction-text-main);">
                    Verified Public Auctions
                </h1>
                <p class="lead mb-4" style="max-width: 600px; color: var(--auction-text-muted); font-size: 1.0625rem; line-height: 1.6;">
                    Direct bidding on liquidated inventory, certified electronics, verified vehicles, and real estate. Real-time ledger updates and escrow-backed wallet settlement.
                </p>

                <div class="d-flex flex-wrap align-items-center mb-4" style="gap: 10px;">
                    @if($isAuthed)
                        <a href="{{ route('auction.dashboard') }}" class="btn btn-auction-primary">
                            <i class="fa-solid fa-gauge-high mr-2"></i> Open Dashboard
                        </a>
                    @else
                        <a href="{{ route('auction.login') }}" class="btn btn-auction-primary">
                            <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Sign In to Bid
                        </a>
                        <a href="{{ route('auction.register') }}" class="btn btn-outline-secondary" style="border-radius: 8px; border-color: var(--auction-border); color: var(--auction-text-main);">
                            <i class="fa-solid fa-user-plus mr-2"></i> Create Account
                        </a>
                    @endif
                    <a href="#liveLots" class="btn btn-link text-decoration-none font-weight-semibold" style="color: var(--auction-text-muted);">
                        Browse Catalog <i class="fa-solid fa-arrow-down ml-1"></i>
                    </a>
                </div>

                <!-- Stats Overview -->
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    <div class="auction-metric-box">
                        <div class="auction-metric-k">Live &amp; Scheduled Lots</div>
                        <div class="auction-metric-v">{{ number_format($totalLots) }}</div>
                    </div>
                    <div class="auction-metric-box">
                        <div class="auction-metric-k">Active Categories</div>
                        <div class="auction-metric-v">{{ number_format(is_iterable($categories ?? null) ? count($categories) : 0) }}</div>
                    </div>
                    <div class="auction-metric-box">
                        <div class="auction-metric-k">Bid Settlement</div>
                        <div class="auction-metric-v" style="font-size: 1rem; color: #10b981;">Escrow Verified</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Search Console -->
                <div class="auction-search-panel p-4">
                    <div class="font-weight-bold mb-1" style="color: var(--auction-text-main); font-size: 1.05rem;">Search Auction Catalog</div>
                    <div class="small mb-3" style="color: var(--auction-text-muted);">Filter by lot code, title, category, or location.</div>
                    
                    <form action="{{ route('public.auctions.index') }}" method="GET">
                        <div class="form-group mb-3">
                            <div class="position-relative">
                                <i class="fa-solid fa-magnifying-glass position-absolute" style="left: 14px; top: 13px; color: var(--auction-text-soft);"></i>
                                <input class="form-control tracking-input pl-5" type="search" name="q" placeholder="e.g. MacBook, Toyota, LOT-104" value="{{ $filters['q'] }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6 mb-3">
                                <select name="category" class="form-control tracking-input">
                                    <option value="">All categories</option>
                                    @foreach(($categories ?? []) as $cat)
                                        <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-6 mb-3">
                                <select name="location" class="form-control tracking-input">
                                    <option value="">All locations</option>
                                    @foreach(($locations ?? []) as $loc)
                                        <option value="{{ $loc }}" @selected(($filters['location'] ?? '') === $loc)>{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-7 mb-0">
                                <select name="sort" class="form-control tracking-input">
                                    <option value="ending_soon" @selected(($filters['sort'] ?? '') === 'ending_soon')>Ending soon</option>
                                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                                    <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: Low to High</option>
                                    <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: High to Low</option>
                                </select>
                            </div>
                            <div class="form-group col-5 mb-0">
                                <button type="submit" class="btn btn-auction-primary btn-block">Filter Lots</button>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 pt-2 border-top" style="border-color: var(--auction-border) !important;">
                        @if(($filters['q'] ?? '') !== '' || ($filters['category'] ?? '') !== '' || ($filters['location'] ?? '') !== '' || ($filters['status'] ?? '') !== '')
                            <a href="{{ route('public.auctions.index') }}" class="small text-decoration-none font-weight-medium" style="color: var(--auction-primary);">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Reset filters
                            </a>
                        @else
                            <span class="small" style="color: var(--auction-text-soft);">Live market updates active</span>
                        @endif
                        <a href="{{ route('auction.admin.dashboard') }}" class="small text-decoration-none" style="color: var(--auction-text-soft);">
                            <i class="fa-solid fa-shield-halved mr-1"></i> Admin Console
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Operational Standards Strip -->
    <section class="mb-4">
        <div class="row g-3">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="auction-standard-card p-3.5 h-100">
                    <div class="d-flex align-items-center mb-1.5">
                        <i class="fa-solid fa-certificate mr-2" style="color: var(--auction-primary);"></i>
                        <div class="font-weight-semibold" style="color: var(--auction-text-main); font-size: 0.9375rem;">Verified Consignors</div>
                    </div>
                    <div class="small" style="color: var(--auction-text-muted); line-height: 1.5;">Physical assets, title checks, and condition disclosures are verified before listing.</div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="auction-standard-card p-3.5 h-100">
                    <div class="d-flex align-items-center mb-1.5">
                        <i class="fa-solid fa-bolt mr-2" style="color: var(--auction-primary);"></i>
                        <div class="font-weight-semibold" style="color: var(--auction-text-main); font-size: 0.9375rem;">Real-Time Bid Stream</div>
                    </div>
                    <div class="small" style="color: var(--auction-text-muted); line-height: 1.5;">Bid increments update immediately across all participants with millisecond synchronization.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="auction-standard-card p-3.5 h-100">
                    <div class="d-flex align-items-center mb-1.5">
                        <i class="fa-solid fa-shield-halved mr-2" style="color: var(--auction-primary);"></i>
                        <div class="font-weight-semibold" style="color: var(--auction-text-main); font-size: 0.9375rem;">Escrow Settlement</div>
                    </div>
                    <div class="small" style="color: var(--auction-text-muted); line-height: 1.5;">Winner balances are reserved in verified escrow until physical item verification and release.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Catalog Lots Grid -->
    <section id="liveLots" class="mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3" style="gap: 12px;">
            <div>
                <h2 class="h4 font-weight-bold mb-1" style="color: var(--auction-text-main); letter-spacing: -0.015em;">Live Auctions</h2>
                <div class="small" style="color: var(--auction-text-muted);">Explore open bidding lots, inspect provenance, and place competitive offers.</div>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <span class="badge px-3 py-1.5" style="background: var(--auction-surface-subtle); border: 1px solid var(--auction-border); color: var(--auction-text-muted); font-size: 0.8125rem;">
                    <i class="fa-solid fa-arrow-down-wide-short mr-1.5"></i>
                    Sort: {{ $filters['sort'] === 'price_low' ? 'Price: Low to High' : ($filters['sort'] === 'price_high' ? 'Price: High to Low' : ($filters['sort'] === 'newest' ? 'Newest' : 'Ending Soon')) }}
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
                        <div class="auction-lot-card h-100">
                            <div class="auction-lot-media">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $lot->title }}" loading="lazy">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100" style="color: var(--auction-text-soft);">
                                        <i class="fa-solid fa-box-archive fa-2x"></i>
                                    </div>
                                @endif
                                <div class="auction-lot-tag">
                                    LOT {{ $lot->lot_code }}
                                </div>
                                <div class="auction-status-tag">
                                    <span class="badge js-status px-2 py-1 font-weight-semibold" data-code="{{ $lot->lot_code }}" style="background: {{ $status === 'live' ? 'rgba(239, 68, 68, 0.9)' : ($status === 'scheduled' ? 'rgba(37, 99, 235, 0.9)' : 'rgba(107, 114, 128, 0.9)') }}; color: #fff; font-size: 0.6875rem; letter-spacing: 0.04em;">
                                        {{ strtoupper($status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-3.5">
                                <h3 class="h6 font-weight-bold mb-1 auction-title-clamp" style="color: var(--auction-text-main);">
                                    {{ $lot->title }}
                                </h3>
                                <div class="small mb-3 d-flex align-items-center" style="color: var(--auction-text-soft); gap: 6px;">
                                    <span>{{ $lot->category }}</span>
                                    <span>•</span>
                                    <span><i class="fa-solid fa-location-dot mr-1"></i>{{ $lot->location ?? 'Nigeria' }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-end pt-2.5 border-top" style="border-color: var(--auction-border) !important;">
                                    <div>
                                        <div class="small" style="color: var(--auction-text-soft); font-size: 0.75rem;">Current Bid</div>
                                        <div class="font-weight-bold js-price" data-code="{{ $lot->lot_code }}" style="color: var(--auction-primary); font-size: 1.15rem; font-variant-numeric: tabular-nums;">
                                            ₦{{ number_format((float) $lot->current_price, 2) }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="small" style="color: var(--auction-text-soft); font-size: 0.75rem;">Time Remaining</div>
                                        <div class="font-weight-medium js-countdown" data-code="{{ $lot->lot_code }}" data-end="{{ $endAt }}" style="color: var(--auction-text-main); font-size: 0.875rem; font-variant-numeric: tabular-nums;">
                                            {{ $lot->end_at ? $lot->end_at->diffForHumans() : '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="p-4 mx-auto" style="max-width: 480px; background: var(--auction-surface); border: 1px solid var(--auction-border); border-radius: 12px;">
                        <i class="fa-solid fa-box-open fa-2x mb-3" style="color: var(--auction-text-soft);"></i>
                        <h3 class="h5 font-weight-bold mb-1" style="color: var(--auction-text-main);">No matching auction lots</h3>
                        <p class="small mb-3" style="color: var(--auction-text-muted);">There are currently no active lots matching your selected criteria.</p>
                        <a href="{{ route('public.auctions.index') }}" class="btn btn-sm btn-auction-primary">Clear Filters</a>
                    </div>
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
    /* =========================================================
       Unslopped Public Auction Marketplace Styles
       ========================================================= */
    .auction-hero-panel {
        background: var(--auction-surface);
        border: 1px solid var(--auction-border);
        border-radius: 12px;
        position: relative;
    }

    .auction-search-panel {
        background: var(--auction-surface-raised);
        border: 1px solid var(--auction-border);
        border-radius: 10px;
    }

    .auction-metric-box {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid var(--auction-border);
        background: var(--auction-surface-subtle);
        min-width: 140px;
    }

    .auction-metric-k {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--auction-text-soft);
        margin-bottom: 2px;
    }

    .auction-metric-v {
        font-weight: 700;
        font-size: 1.15rem;
        font-variant-numeric: tabular-nums;
        color: var(--auction-text-main);
    }

    .auction-standard-card {
        background: var(--auction-surface);
        border: 1px solid var(--auction-border);
        border-radius: 8px;
    }

    /* Lot Card */
    .auction-lot-card {
        background: var(--auction-surface);
        border: 1px solid var(--auction-border);
        border-radius: 10px;
        overflow: hidden;
        transition: border-color 0.15s ease;
    }

    .auction-lot-card:hover {
        border-color: var(--auction-primary);
    }

    .auction-lot-media {
        height: 190px;
        background: var(--auction-surface-raised);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid var(--auction-border-subtle);
    }

    .auction-lot-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .auction-lot-tag {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(15, 23, 42, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #f8fafc;
        font-size: 0.6875rem;
        font-weight: 700;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        padding: 3px 8px;
        border-radius: 4px;
        letter-spacing: 0.04em;
    }

    .auction-status-tag {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .auction-title-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.35;
    }
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
          stEl.style.background = l.status === 'live' ? 'rgba(239, 68, 68, 0.9)' : (l.status === 'scheduled' ? 'rgba(37, 99, 235, 0.9)' : 'rgba(107, 114, 128, 0.9)');
        }
      });
    } catch (err) {}
  });
})();
</script>
@endpush

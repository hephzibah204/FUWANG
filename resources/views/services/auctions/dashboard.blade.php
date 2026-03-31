@extends('layouts.auction')

@section('title', 'My Auction Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 font-weight-bold mb-0">My Auction Dashboard</h1>
                <p class="text-white-50">Track your bidding activity and watchlisted items.</p>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row mb-5" style="gap: 0; margin-left: -10px; margin-right: -10px;">
            <div class="col-md-4 mb-3">
                <div class="p-4 rounded-lg h-100" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="text-white-50 small mb-1">Winning Bids</div>
                    <div class="h3 font-weight-bold text-success">{{ $winningBids->count() }}</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4 rounded-lg h-100" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);">
                    <div class="text-white-50 small mb-1">Outbid</div>
                    <div class="h3 font-weight-bold text-danger">{{ $outbidBids->count() }}</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4 rounded-lg h-100" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="text-white-50 small mb-1">Watchlist</div>
                    <div class="h3 font-weight-bold text-white">{{ count($watchlisted) }}</div>
                </div>
            </div>
        </div>

        <!-- Main Tabs -->
        <ul class="nav nav-tabs border-0 mb-4" id="auctionTabs" role="tablist" style="gap: 20px;">
            <li class="nav-item m-0 p-0">
                <a class="nav-link border-0 p-0 pb-2 text-white {{ $tab === 'overview' || $tab === 'bids' ? 'active font-weight-bold' : 'opacity-50' }}" id="bids-tab" data-toggle="tab" href="#bids" role="tab" style="background: none; border-bottom: 2px solid {{ $tab === 'overview' || $tab === 'bids' ? 'var(--auction-primary)' : 'transparent' }} !important; border-radius: 0;">My Active Bids</a>
            </li>
            <li class="nav-item m-0 p-0">
                <a class="nav-link border-0 p-0 pb-2 text-white {{ $tab === 'watchlist' ? 'active font-weight-bold' : 'opacity-50' }}" id="watchlist-tab" data-toggle="tab" href="#watchlist" role="tab" style="background: none; border-bottom: 2px solid {{ $tab === 'watchlist' ? 'var(--auction-primary)' : 'transparent' }} !important; border-radius: 0;">Watchlist</a>
            </li>
            <li class="nav-item m-0 p-0">
                <a class="nav-link border-0 p-0 pb-2 text-white {{ $tab === 'history' ? 'active font-weight-bold' : 'opacity-50' }}" id="history-tab" data-toggle="tab" href="#history" role="tab" style="background: none; border-bottom: 2px solid {{ $tab === 'history' ? 'var(--auction-primary)' : 'transparent' }} !important; border-radius: 0;">Past Participation</a>
            </li>
        </ul>

        <div class="tab-content" id="auctionTabsContent">
            <!-- Active Bids Tab -->
            <div class="tab-pane fade show {{ $tab === 'overview' || $tab === 'bids' ? 'active' : '' }}" id="bids" role="tabpanel">
                @if($winningBids->count() || $outbidBids->count())
                    <div class="table-responsive">
                        <table class="table table-borderless text-white">
                            <thead>
                                <tr class="text-white-50 border-bottom border-white-10">
                                    <th>Item</th>
                                    <th>Status</th>
                                    <th class="text-right">Your Bid</th>
                                    <th class="text-right">Current Price</th>
                                    <th class="text-right">Ends In</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($winningBids->concat($outbidBids) as $bid)
                                    <tr class="border-bottom border-white-10 align-middle">
                                        <td class="py-4">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3 rounded overflow-hidden" style="width: 48px; height: 48px; background: rgba(0,0,0,0.25);">
                                                    @if($bid->lot->images->first())
                                                        <img src="{{ $bid->lot->images->first()->url }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center h-100"><i class="fa-solid fa-image text-white-50"></i></div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">{{ $bid->lot->title }}</div>
                                                    <div class="small text-white-50">Lot {{ $bid->lot->lot_code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4">
                                            <span class="badge {{ $bid->status === 'winning' ? 'badge-winning' : 'badge-outbid' }} px-3 py-2">
                                                {{ strtoupper($bid->status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-right">₦{{ number_format($bid->bid_amount, 2) }}</td>
                                        <td class="py-4 text-right font-weight-bold text-primary">₦{{ number_format($bid->lot->current_price, 2) }}</td>
                                        <td class="py-4 text-right">{{ $bid->lot->end_at->diffForHumans() }}</td>
                                        <td class="py-4 text-right">
                                            <a href="{{ route('public.auctions.show', $bid->lot->lot_code) }}" class="btn btn-sm btn-outline-glass">View / Bid</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 rounded-lg" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                        <i class="fa-solid fa-gavel fa-3x text-white-10 mb-3"></i>
                        <p class="text-white-50">You haven't placed any bids yet.</p>
                        <a href="{{ route('public.auctions.index') }}" class="btn btn-primary px-4" style="background: var(--auction-primary); border: none;">Start Browsing</a>
                    </div>
                @endif
            </div>

            <!-- Watchlist Tab -->
            <div class="tab-pane fade {{ $tab === 'watchlist' ? 'active show' : '' }}" id="watchlist" role="tabpanel">
                <div class="text-center py-5 rounded-lg" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                    <p class="text-white-50">Watchlist feature is coming soon.</p>
                </div>
            </div>

            <!-- History Tab -->
            <div class="tab-pane fade {{ $tab === 'history' ? 'active show' : '' }}" id="history" role="tabpanel">
                <div class="text-center py-5 rounded-lg" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                    <p class="text-white-50">Past auction participation will appear here.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

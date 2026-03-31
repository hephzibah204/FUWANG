@extends('layouts.nexus')

@section('title', 'Manage Auctions')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="text-white font-weight-bold mb-0">Auction Lots</h3>
            <p class="text-white-50">Manage auction items, statuses, and pricing.</p>
        </div>
        <div class="d-flex" style="gap: 10px;">
            <a href="{{ request()->fullUrlWithQuery(['trashed' => request()->boolean('trashed') ? null : 1]) }}" class="btn btn-outline-glass">
                {{ request()->boolean('trashed') ? 'Hide Deleted' : 'Show Deleted' }}
            </a>
            <a href="{{ route('admin.auctions.sellers') }}" class="btn btn-outline-glass">Manage Sellers</a>
            <a href="{{ route('admin.auctions.create') }}" class="btn btn-primary"><i class="fa fa-plus mr-1"></i> Create Lot</a>
        </div>
    </div>
</div>

<div class="card bg-glass border-glass rounded-lg overflow-hidden">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Item</th>
                    <th>Seller</th>
                    <th>Current Price</th>
                    <th>Status</th>
                    <th>Ends At</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                    <tr>
                        <td><code class="text-primary">{{ $lot->lot_code }}</code></td>
                        <td>
                            <div class="text-white font-weight-bold">{{ $lot->title }}</div>
                            <div class="small text-white-50">{{ $lot->category }}</div>
                        </td>
                        <td class="text-white-50">{{ $lot->seller->name ?? 'N/A' }}</td>
                        <td class="text-white font-weight-bold">₦{{ number_format($lot->current_price, 2) }}</td>
                        <td>
                            @php
                                $badge = match($lot->status) {
                                    'live' => 'badge-danger',
                                    'scheduled' => 'badge-info',
                                    'ended' => 'badge-secondary',
                                    'cancelled' => 'badge-dark',
                                    default => 'badge-light'
                                };
                            @endphp
                            <span class="badge badge-pill {{ $badge }} uppercase">{{ strtoupper($lot->status) }}</span>
                        </td>
                        <td class="text-white-50 small">
                            {{ $lot->end_at ? $lot->end_at->format('M d, H:i') : '—' }}
                        </td>
                        <td class="text-right">
                            @if(!$lot->trashed())
                                <a href="{{ route('admin.auctions.edit', $lot->id) }}" class="btn btn-sm btn-outline-glass"><i class="fa fa-edit"></i></a>
                                <a href="{{ route('admin.auctions.bids', $lot->id) }}" class="btn btn-sm btn-outline-glass ml-1"><i class="fa fa-gavel"></i></a>
                                <a href="{{ route('public.auctions.show', $lot->lot_code) }}" target="_blank" class="btn btn-sm btn-outline-glass ml-1"><i class="fa fa-eye"></i></a>
                                <form action="{{ route('admin.auctions.destroy', $lot->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-glass ml-1"><i class="fa fa-trash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('admin.auctions.restore', $lot->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-glass"><i class="fa fa-rotate-left"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-white-50">No auction lots found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lots->hasPages())
        <div class="p-3 border-top border-white-10">
            {{ $lots->links() }}
        </div>
    @endif
</div>
@endsection

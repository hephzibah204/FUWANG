@extends('layouts.nexus')

@section('title', 'Manage Bids')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="text-white font-weight-bold mb-0">Bids for {{ $lot->lot_code }}</h3>
            <p class="text-white-50 mb-0">{{ $lot->title }}</p>
        </div>
        <div class="d-flex" style="gap: 10px;">
            <a href="{{ route('admin.auctions.edit', $lot->id) }}" class="btn btn-outline-glass">Back to Lot</a>
            <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-glass">All Lots</a>
        </div>
    </div>
</div>

<div class="card bg-glass border-glass rounded-lg overflow-hidden">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bids as $bid)
                    <tr class="{{ $bid->trashed() ? 'opacity-50' : '' }}">
                        <td class="text-white-50"><code class="text-primary">{{ $bid->reference }}</code></td>
                        <td class="text-white-50">
                            <div class="text-white font-weight-bold">{{ $bid->user?->fullname ?? $bid->user?->username ?? $bid->user?->email ?? 'User' }}</div>
                            <div class="small text-white-50">#{{ $bid->user_id }}</div>
                        </td>
                        <td class="text-white font-weight-bold">₦{{ number_format((float) $bid->bid_amount, 2) }}</td>
                        <td>
                            @php
                                $badge = match($bid->status) {
                                    'winning' => 'badge-success',
                                    'outbid' => 'badge-secondary',
                                    'cancelled' => 'badge-dark',
                                    default => 'badge-light'
                                };
                            @endphp
                            <span class="badge badge-pill {{ $badge }}">{{ strtoupper($bid->status) }}</span>
                        </td>
                        <td class="text-white-50 small">{{ $bid->created_at?->format('M d, H:i') ?? '—' }}</td>
                        <td class="text-right">
                            @if(!$bid->trashed())
                                <button type="button" class="btn btn-sm btn-outline-glass" data-toggle="modal" data-target="#editBidModal{{ $bid->id }}"><i class="fa fa-edit"></i></button>
                                <form action="{{ route('admin.auctions.bids.destroy', $bid->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-glass ml-1"><i class="fa fa-trash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('admin.auctions.bids.restore', $bid->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-glass"><i class="fa fa-rotate-left"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    @if(!$bid->trashed())
                    <div class="modal fade" id="editBidModal{{ $bid->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content bg-dark border-glass text-white">
                                <form action="{{ route('admin.auctions.bids.update', $bid->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header border-white-10">
                                        <h5 class="modal-title font-weight-bold">Edit Bid</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label class="text-white-50 small mb-2">User ID</label>
                                            <input type="number" name="user_id" class="form-control" value="{{ $bid->user_id }}" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="text-white-50 small mb-2">Bid Amount (₦)</label>
                                            <input type="number" name="bid_amount" class="form-control" step="0.01" value="{{ $bid->bid_amount }}" required>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="text-white-50 small mb-2">Status</label>
                                            <select name="status" class="form-control" required>
                                                @foreach(['winning', 'outbid', 'cancelled'] as $st)
                                                    <option value="{{ $st }}" {{ $bid->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-white-10">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-white-50">No bids found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bids->hasPages())
        <div class="p-3 border-top border-white-10">
            {{ $bids->links() }}
        </div>
    @endif
</div>
@endsection


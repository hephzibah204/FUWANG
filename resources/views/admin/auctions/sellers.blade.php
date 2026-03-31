@extends('layouts.nexus')

@section('title', 'Manage Sellers')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="text-white font-weight-bold mb-0">Auction Sellers</h3>
            <p class="text-white-50">Manage verified sellers and organizations.</p>
        </div>
        <div class="d-flex" style="gap: 10px;">
            <a href="{{ request()->fullUrlWithQuery(['trashed' => request()->boolean('trashed') ? null : 1]) }}" class="btn btn-outline-glass">
                {{ request()->boolean('trashed') ? 'Hide Deleted' : 'Show Deleted' }}
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSellerModal">
                <i class="fa fa-plus mr-1"></i> Add Seller
            </button>
        </div>
    </div>
</div>

<div class="card bg-glass border-glass rounded-lg overflow-hidden">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Rating</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $s)
                    <tr class="{{ $s->trashed() ? 'opacity-50' : '' }}">
                        <td class="text-white-50">#{{ $s->id }}</td>
                        <td class="text-white font-weight-bold">{{ $s->name }}</td>
                        <td class="text-white-50">{{ $s->location ?? '—' }}</td>
                        <td>
                            <div class="text-warning">
                                @for($i=1; $i<=5; $i++)
                                    <i class="fa{{ $i <= ($s->rating ?? 5) ? 's' : 'r' }} fa-star small"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="text-right">
                            @if(!$s->trashed())
                                <button type="button" class="btn btn-sm btn-outline-glass" data-toggle="modal" data-target="#editSellerModal{{ $s->id }}">Edit</button>
                                <form action="{{ route('admin.auctions.sellers.destroy', $s->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-glass ml-1">Delete</button>
                                </form>
                            @else
                                <form action="{{ route('admin.auctions.sellers.restore', $s->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-glass">Restore</button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    @if(!$s->trashed())
                    <div class="modal fade" id="editSellerModal{{ $s->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content bg-dark border-glass text-white">
                                <form action="{{ route('admin.auctions.sellers.update', $s->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header border-white-10">
                                        <h5 class="modal-title font-weight-bold">Edit Seller</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label class="text-white-50 small mb-2">Seller Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $s->name }}" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="text-white-50 small mb-2">Location</label>
                                            <input type="text" name="location" class="form-control" value="{{ $s->location }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="text-white-50 small mb-2">Rating</label>
                                            <input type="number" name="rating" class="form-control" step="0.01" min="0" max="5" value="{{ $s->rating }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="text-white-50 small mb-2">Reviews Count</label>
                                            <input type="number" name="reviews_count" class="form-control" min="0" value="{{ $s->reviews_count }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="text-white-50 small mb-2">Avatar URL</label>
                                            <input type="url" name="avatar_url" class="form-control" value="{{ $s->avatar_url }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" name="verified" class="custom-control-input" id="verifiedSwitch{{ $s->id }}" {{ $s->verified ? 'checked' : '' }}>
                                                <label class="custom-control-label text-white" for="verifiedSwitch{{ $s->id }}">Verified</label>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="text-white-50 small mb-2">About</label>
                                            <textarea name="about" class="form-control" rows="3">{{ $s->about }}</textarea>
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
                    <tr><td colspan="5" class="text-center py-5 text-white-50">No sellers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Seller Modal -->
<div class="modal fade" id="addSellerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-glass text-white">
            <form action="{{ route('admin.auctions.sellers.store') }}" method="POST">
                @csrf
                <div class="modal-header border-white-10">
                    <h5 class="modal-title font-weight-bold">Add Verified Seller</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">Seller Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="City, Country">
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">Rating</label>
                        <input type="number" name="rating" class="form-control" step="0.01" min="0" max="5" value="4.8">
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">Avatar URL</label>
                        <input type="url" name="avatar_url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="verified" class="custom-control-input" id="verifiedSwitchNew">
                            <label class="custom-control-label text-white" for="verifiedSwitchNew">Verified</label>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-white-50 small mb-2">About (Optional)</label>
                        <textarea name="about" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-white-10">
                    <button type="submit" class="btn btn-primary">Save Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

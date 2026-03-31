@extends('layouts.nexus')

@section('title', 'Edit Auction Lot')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-white font-weight-bold mb-1">Edit Auction Lot: {{ $lot->lot_code }}</h3>
        <p class="text-white-50">Update item details or change status.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.auctions.update', $lot->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card bg-glass border-glass p-4 rounded-lg mb-4">
                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Item Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $lot->title }}" required>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Seller</label>
                        <select name="seller_id" class="form-control" required>
                            @foreach($sellers as $s)
                                <option value="{{ $s->id }}" {{ $lot->seller_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Category</label>
                        <select name="category" class="form-control" required>
                            @foreach(['Electronics', 'Vehicles', 'Real Estate', 'Collectibles', 'Fashion'] as $cat)
                                <option value="{{ $cat }}" {{ $lot->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ $lot->location }}" placeholder="City, State">
                </div>

                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Description</label>
                    <textarea name="description" class="form-control" rows="5">{{ $lot->description }}</textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="text-white-50 small mb-2">Current Price (₦)</label>
                        <input type="number" name="current_price" class="form-control" step="0.01" value="{{ $lot->current_price }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="text-white-50 small mb-2">Starting Price (₦)</label>
                        <input type="number" name="starting_price" class="form-control" step="0.01" value="{{ $lot->starting_price }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="text-white-50 small mb-2">Bid Increment (₦)</label>
                        <input type="number" name="bid_increment" class="form-control" step="0.01" value="{{ $lot->bid_increment }}" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Start At</label>
                        <input type="datetime-local" name="start_at" class="form-control" value="{{ $lot->start_at->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">End At</label>
                        <input type="datetime-local" name="end_at" class="form-control" value="{{ $lot->end_at->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="scheduled" {{ $lot->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="live" {{ $lot->status === 'live' ? 'selected' : '' }}>Live</option>
                            <option value="ended" {{ $lot->status === 'ended' ? 'selected' : '' }}>Ended</option>
                            <option value="cancelled" {{ $lot->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="custom-control custom-switch mt-4">
                            <input type="checkbox" name="featured" class="custom-control-input" id="featuredSwitch" {{ $lot->featured ? 'checked' : '' }}>
                            <label class="custom-control-label text-white" for="featuredSwitch">Featured Lot</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex" style="gap: 15px;">
                <button type="submit" class="btn btn-primary btn-lg px-5">Save Changes</button>
                <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-glass btn-lg px-4">Cancel</a>
                    <a href="{{ route('admin.auctions.bids', $lot->id) }}" class="btn btn-outline-glass btn-lg px-4">Manage Bids</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card bg-glass border-glass p-4 rounded-lg mb-4">
            <h5 class="text-white mb-3">Item Images</h5>
            <div class="row mx-n1">
                @foreach($lot->images as $img)
                    <div class="col-6 px-1 mb-2">
                        <div class="rounded overflow-hidden shadow-sm" style="height: 80px; background: rgba(0,0,0,0.2); position: relative;">
                            <img src="{{ $img->url }}" style="width: 100%; height: 100%; object-fit: cover;">
                            <div class="position-absolute" style="left: 8px; top: 8px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="remove_image_ids[]" value="{{ $img->id }}" class="custom-control-input" id="rmImg{{ $img->id }}">
                                    <label class="custom-control-label text-white small" for="rmImg{{ $img->id }}">Remove</label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                <label class="text-white-50 small mb-2">Add Images</label>
                <input type="file" name="images[]" class="form-control-file" multiple accept="image/*">
                <small class="text-white-50 mt-2 d-block">Upload one or more images. Max 2MB each.</small>
            </div>
        </div>
    </div>
</div>
@endsection

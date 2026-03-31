@extends('layouts.nexus')

@section('title', 'Create Auction Lot')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-white font-weight-bold mb-1">Create New Auction Lot</h3>
        <p class="text-white-50">Add a new item to the auction catalog.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.auctions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card bg-glass border-glass p-4 rounded-lg mb-4">
                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Item Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Rare Vintage Watch" required>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Seller</label>
                        <select name="seller_id" class="form-control" required>
                            <option value="">Select Seller</option>
                            @foreach($sellers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Category</label>
                        <select name="category" class="form-control" required>
                            <option value="Electronics">Electronics</option>
                            <option value="Vehicles">Vehicles</option>
                            <option value="Real Estate">Real Estate</option>
                            <option value="Collectibles">Collectibles</option>
                            <option value="Fashion">Fashion</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Location</label>
                    <input type="text" name="location" class="form-control" placeholder="City, State">
                </div>

                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Detailed item description..."></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Starting Price (₦)</label>
                        <input type="number" name="starting_price" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Bid Increment (₦)</label>
                        <input type="number" name="bid_increment" class="form-control" step="0.01" value="100" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Current Price (₦)</label>
                    <input type="number" name="current_price" class="form-control" step="0.01" placeholder="Leave empty to use Starting Price">
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Start At</label>
                        <input type="datetime-local" name="start_at" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">End At</label>
                        <input type="datetime-local" name="end_at" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-white-50 small mb-2">Initial Status</label>
                        <select name="status" class="form-control" required>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="live">Live</option>
                            <option value="ended">Ended</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="custom-control custom-switch mt-4">
                            <input type="checkbox" name="featured" class="custom-control-input" id="featuredSwitch">
                            <label class="custom-control-label text-white" for="featuredSwitch">Featured Lot</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-glass border-glass p-4 rounded-lg mb-4">
                <h5 class="text-white mb-3">Item Images</h5>
                <input type="file" name="images[]" class="form-control-file" multiple accept="image/*">
                <small class="text-white-50 mt-2 d-block">Upload one or more images. Max 2MB each.</small>
            </div>

            <div class="d-flex" style="gap: 15px;">
                <button type="submit" class="btn btn-primary btn-lg px-5">Publish Lot</button>
                <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-glass btn-lg px-4">Cancel</a>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <div class="card bg-glass border-glass p-4 rounded-lg">
            <h5 class="text-white mb-3">Guidelines</h5>
            <div class="text-white-50 small" style="line-height: 1.6;">
                <p><i class="fa fa-info-circle mr-2 text-info"></i> <strong>Starting Price</strong> is the opening bid.</p>
                <p><i class="fa fa-info-circle mr-2 text-info"></i> <strong>Bid Increment</strong> is the minimum amount required to outbid the previous bidder.</p>
                <p><i class="fa fa-clock mr-2 text-warning"></i> <strong>Scheduled</strong> lots will automatically go live when the current time reaches the Start At time.</p>
            </div>
        </div>
    </div>
</div>
@endsection

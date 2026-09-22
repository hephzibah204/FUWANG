@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Become a Parcel Agent</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Register your retail shop as an official Parcel Collection and Drop-off point.</p>
                    
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('parcels.register.submit') }}" method="POST">
                        @csrf
                        
                        <h5 class="mt-3 mb-3 border-bottom pb-2">Shop Details</h5>
                        <div class="mb-3">
                            <label for="shop_name" class="form-label">Shop Name</label>
                            <input type="text" class="form-control @error('shop_name') is-invalid @enderror" id="shop_name" name="shop_name" value="{{ old('shop_name') }}" required>
                            @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="shop_address" class="form-label">Shop Address</label>
                            <input type="text" class="form-control @error('shop_address') is-invalid @enderror" id="shop_address" name="shop_address" value="{{ old('shop_address') }}" required>
                            @error('shop_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" required>
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required>
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3 border-bottom pb-2">Agent Verification</h5>
                        <div class="mb-4">
                            <label for="nin_number" class="form-label">NIN Number</label>
                            <input type="text" class="form-control @error('nin_number') is-invalid @enderror" id="nin_number" name="nin_number" value="{{ old('nin_number') }}" required maxlength="11" minlength="11">
                            <small class="text-muted">Enter your 11-digit National Identity Number (NIN) for verification.</small>
                            @error('nin_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

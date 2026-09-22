@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Driver Drop-off</h3>
                <a href="{{ route('parcels.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    
                    <p class="text-muted mb-4">Accept a parcel from a courier driver to hold for customer collection.</p>

                    <form action="{{ route('parcels.dropoff.driver.process') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="tracking_number" class="form-label fw-bold">Tracking / Waybill Number</label>
                            <input type="text" class="form-control form-control-lg @error('tracking_number') is-invalid @enderror" 
                                id="tracking_number" name="tracking_number" value="{{ old('tracking_number') }}" 
                                placeholder="Scan or type here..." required autofocus>
                            <small class="form-text text-muted">Ready for USB Barcode Scanner input.</small>
                            @error('tracking_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="condition" class="form-label fw-bold">Parcel Condition</label>
                            <select class="form-select form-select-lg @error('condition') is-invalid @enderror" id="condition" name="condition" required>
                                <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good Condition</option>
                                <option value="damaged" {{ old('condition') == 'damaged' ? 'selected' : '' }}>Defective / Damaged</option>
                            </select>
                            @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-secondary btn-lg">Receive from Driver</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

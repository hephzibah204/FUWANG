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

                    <form action="{{ route('parcels.dropoff.driver.process') }}" method="POST" enctype="multipart/form-data">
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
                            <select class="form-select form-select-lg @error('condition') is-invalid @enderror" id="condition" name="condition" required onchange="document.getElementById('damagePhotoWrapper').style.display = (this.value === 'damaged' ? 'block' : 'none');">
                                <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good Condition</option>
                                <option value="damaged" {{ old('condition') == 'damaged' ? 'selected' : '' }}>Defective / Damaged</option>
                            </select>
                            @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4" id="damagePhotoWrapper" style="display: {{ old('condition') == 'damaged' ? 'block' : 'none' }};">
                            <label for="damage_photo" class="form-label fw-bold text-danger"><i class="fa fa-camera"></i> Upload Damage Photo (Required if damaged)</label>
                            <input type="file" class="form-control form-control-lg @error('damage_photo') is-invalid @enderror" id="damage_photo" name="damage_photo" accept="image/*">
                            @error('damage_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-secondary btn-lg" id="submitBtn">Receive from Driver</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Double submit prevention
    document.querySelector('form').addEventListener('submit', function() {
        let btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = 'Processing...';
    });

    // Global Keydown listener for barcode scanners
    document.addEventListener('keydown', function(e) {
        const trackingInput = document.getElementById('tracking_number');
        if (document.activeElement !== trackingInput && document.activeElement.tagName !== 'SELECT') {
            if (e.key.length === 1 && e.key.match(/[a-zA-Z0-9-]/)) {
                trackingInput.focus();
            }
        }
    });
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Customer Pickup</h3>
                <a href="{{ route('parcels.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    
                    <p class="text-muted mb-4">Process a parcel handover to the customer. Please verify their identity before release.</p>

                    <form action="{{ route('parcels.pickup.customer.process') }}" method="POST" id="pickupForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="tracking_number" class="form-label fw-bold">Tracking Number</label>
                            <input type="text" class="form-control form-control-lg @error('tracking_number') is-invalid @enderror" 
                                id="tracking_number" name="tracking_number" value="{{ old('tracking_number') }}" 
                                placeholder="Scan or type here..." required>
                            @error('tracking_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="id_type" class="form-label fw-bold">ID Presented by Customer</label>
                            <select class="form-select form-select-lg @error('id_type') is-invalid @enderror" id="id_type" name="id_type" required>
                                <option value="" disabled selected>Select ID Type...</option>
                                <option value="NIN" {{ old('id_type') == 'NIN' ? 'selected' : '' }}>National Identity Number (NIN)</option>
                                <option value="Passport" {{ old('id_type') == 'Passport' ? 'selected' : '' }}>International Passport</option>
                                <option value="DriverLicense" {{ old('id_type') == 'DriverLicense' ? 'selected' : '' }}>Driver's License</option>
                                <option value="VoterCard" {{ old('id_type') == 'VoterCard' ? 'selected' : '' }}>Voter's Card</option>
                            </select>
                            @error('id_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Customer Signature</label>
                            <div class="border rounded bg-light" style="touch-action: none;">
                                <canvas id="signatureCanvas" width="100%" height="200" style="width: 100%; height: 200px; cursor: crosshair;"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="clearSignature">Clear Signature</button>
                            <input type="hidden" name="signature_data" id="signature_data" required>
                            @error('signature_data')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-success btn-lg" onclick="submitPickupForm()">Release Parcel</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        // Resize canvas to fit container
        canvas.width = canvas.parentElement.clientWidth;

        function startDrawing(e) {
            isDrawing = true;
            draw(e);
        }

        function stopDrawing() {
            isDrawing = false;
            ctx.beginPath();
        }

        function draw(e) {
            if (!isDrawing) return;
            
            e.preventDefault(); // Prevent scrolling on touch
            
            const rect = canvas.getBoundingClientRect();
            let x, y;
            
            if (e.type.includes('touch')) {
                x = e.touches[0].clientX - rect.left;
                y = e.touches[0].clientY - rect.top;
            } else {
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }

            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch events
        canvas.addEventListener('touchstart', startDrawing, {passive: false});
        canvas.addEventListener('touchmove', draw, {passive: false});
        canvas.addEventListener('touchend', stopDrawing);

        // Clear button
        document.getElementById('clearSignature').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signature_data').value = '';
        });
    });

    window.addEventListener('resize', function() {
        const canvas = document.getElementById('signatureCanvas');
        const temp = canvas.toDataURL(); // Save existing drawing
        canvas.width = canvas.parentElement.clientWidth;
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.onload = function() {
            ctx.drawImage(img, 0, 0);
        };
        img.src = temp;
    });

    // Global Keydown listener for barcode scanners
    document.addEventListener('keydown', function(e) {
        const trackingInput = document.getElementById('tracking_number');
        if (document.activeElement !== trackingInput && document.activeElement.tagName !== 'SELECT' && document.activeElement.tagName !== 'CANVAS') {
            if (e.key.length === 1 && e.key.match(/[a-zA-Z0-9-]/)) {
                trackingInput.focus();
            }
        }
    });

    function submitPickupForm() {
        const form = document.getElementById('pickupForm');
        
        // Trigger native HTML5 validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const canvas = document.getElementById('signatureCanvas');
        const dataUrl = canvas.toDataURL('image/png');
        
        // Simple check to ensure canvas isn't mostly blank
        if(dataUrl.length < 3000) {
            alert('Please ask the customer to provide a signature.');
            return;
        }

        document.getElementById('signature_data').value = dataUrl;
        
        const btn = document.querySelector('button[onclick="submitPickupForm()"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Processing...';
        }
        
        form.submit();
    }
</script>
@endsection

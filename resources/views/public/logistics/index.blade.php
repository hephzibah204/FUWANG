@extends('layouts.postoffice')

@section('title', 'Logistics & Shipping Hub')

@section('content')
<div class="text-center mb-5 mt-4">
    <h1 class="display-4 font-weight-bold mb-3">Track & Manage Your <span style="color:var(--po-primary)">Shipments</span></h1>
    <p class="lead text-white-50 mx-auto" style="max-width: 700px;">
        Fast, secure, and reliable logistics solutions for your business and personal needs. 
        Track your package in real-time or book a new shipment in minutes.
    </p>
</div>

<!-- Tracking Search Section -->
<div class="row justify-content-center mb-5">
    <div class="col-lg-8">
        <div class="glass-card p-4 shadow-lg border-primary-fade">
            <div class="input-group">
                <input type="text" id="trackingId" class="form-control tracking-input mr-2" placeholder="Enter Tracking ID (e.g., NXS-XXXXXX)">
                <div class="input-group-append">
                    <button class="btn btn-po-primary px-5" id="trackBtn">
                        <i class="fa fa-magnifying-glass mr-2"></i> Track Now
                    </button>
                </div>
            </div>
            <div id="trackingResult" class="mt-4 d-none">
                <!-- Ajax Result -->
            </div>
        </div>
    </div>
</div>

<!-- Services Grid -->
<div id="services" class="row mt-5">
    <div class="col-12 text-center mb-4">
        <h2 class="font-weight-bold">Our Logistics <span style="color:var(--po-primary)">Services</span></h2>
    </div>
    <div class="col-md-4 mb-4">
        <div class="glass-card p-4 h-100 text-center">
            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(245, 158, 11, 0.1); color: var(--po-primary);">
                <i class="fa fa-truck-fast fa-2x"></i>
            </div>
            <h5 class="font-weight-bold">Express Delivery</h5>
            <p class="text-white-50 small">Get your packages delivered within 1-2 business days across major cities.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="glass-card p-4 h-100 text-center">
            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(59, 130, 246, 0.1); color: var(--po-accent);">
                <i class="fa fa-plane fa-2x"></i>
            </div>
            <h5 class="font-weight-bold">Inter-State Shipping</h5>
            <p class="text-white-50 small">Reliable nationwide coverage for all your shipping requirements.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="glass-card p-4 h-100 text-center">
            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(34, 197, 94, 0.1); color: #22c55e);">
                <i class="fa fa-warehouse fa-2x"></i>
            </div>
            <h5 class="font-weight-bold">Warehousing</h5>
            <p class="text-white-50 small">Secure storage and fulfillment services for e-commerce businesses.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#trackBtn').click(function() {
        const id = $('#trackingId').val();
        if (!id) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Tracking...');

        $.post("{{ route('public.logistics.track') }}", {
            _token: "{{ csrf_token() }}",
            tracking_id: id
        }, function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-magnifying-glass mr-2"></i> Track Now');
            const resultDiv = $('#trackingResult');
            resultDiv.removeClass('d-none');

            if (res.status) {
                let timelineHtml = '<div class="tracking-timeline mt-4">';
                res.tracking.timeline.forEach(step => {
                    timelineHtml += `
                        <div class="d-flex mb-3 ${step.done ? 'text-white' : 'text-white-50'}">
                            <div class="mr-3 text-center" style="width: 25px;">
                                <i class="fa ${step.done ? 'fa-check-circle text-success' : 'fa-circle-notch'}"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">${step.event}</div>
                                <small class="text-white-50">${step.time}</small>
                            </div>
                        </div>
                    `;
                });
                timelineHtml += '</div>';

                resultDiv.html(`
                    <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 font-weight-bold">Shipment Found: <span class="text-primary">${res.tracking.id}</span></h5>
                            <span class="badge badge-warning px-3 py-2 rounded-pill text-dark">${res.tracking.status}</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 border-right border-white-10">
                                <p class="text-white-50 small mb-1">Current Location</p>
                                <p class="font-weight-bold mb-0">${res.tracking.location}</p>
                            </div>
                            <div class="col-md-6 px-4">
                                <p class="text-white-50 small mb-1">Last Updated</p>
                                <p class="font-weight-bold mb-0">${res.tracking.updated}</p>
                            </div>
                        </div>
                        ${timelineHtml}
                    </div>
                `);
            } else {
                resultDiv.html(`
                    <div class="alert alert-danger border-0 bg-danger text-white rounded-lg">
                        <i class="fa fa-exclamation-triangle mr-2"></i> ${res.message}
                    </div>
                `);
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fa fa-magnifying-glass mr-2"></i> Track Now');
            alert('Something went wrong. Please try again.');
        });
    });
</script>
@endpush
@endsection

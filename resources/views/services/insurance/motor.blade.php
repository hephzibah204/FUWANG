@extends('layouts.nexus')

@section('title', 'Motor Insurance | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05)); border-color: rgba(139, 92, 246, 0.2);">
        <div class="sh-icon" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.3);">
            <i class="fa-solid fa-car-shield"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Third Party Motor Insurance</h1>
            <p class="text-muted small">Universal Insurance coverage for your vehicle. Fast & Easy.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-file-contract text-primary"></i> Instant Certificate</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-heart text-success"></i> Universal Insurance</span>
        </div>
    </div>

    <form id="insuranceForm" action="{{ route('services.insurance.motor.buy') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Left Column: Form Sections -->
            <div class="col-lg-8">
                <!-- Section 1: Personal Info -->
                <div class="panel-card p-4 mb-4">
                    <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">
                        <i class="fa-solid fa-user mr-2 text-primary"></i> 1. Policy Holder Information
                    </h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Insured Full Name</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-id-card"></i>
                                <input type="text" name="insured_name" class="form-control" placeholder="John Doe" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Email Address</label>
                            <div class="input-wrap">
                                <i class="fa-regular fa-envelope"></i>
                                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Phone Number</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-phone"></i>
                                <input type="text" name="phone" class="form-control" placeholder="08012345678" maxlength="11" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Vehicle Info -->
                <div class="panel-card p-4 mb-4">
                    <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">
                        <i class="fa-solid fa-car mr-2 text-primary"></i> 2. Vehicle Specification
                    </h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Plate Number</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-hashtag"></i>
                                <input type="text" name="plate_number" class="form-control" placeholder="LAG-123-ABC" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Chasis Number</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-fingerprint"></i>
                                <input type="text" name="chasis_number" class="form-control" placeholder="VIN Number" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Year of Make</label>
                            <div class="input-wrap">
                                <i class="fa-regular fa-calendar"></i>
                                <input type="number" name="year_of_make" class="form-control" placeholder="2015" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Vehicle Color</label>
                            <select id="vehicle_color" name="vehicle_color" class="form-control" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Engine Capacity</label>
                            <select id="engine_capacity" name="engine_capacity" class="form-control" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Vehicle Brand</label>
                            <select id="vehicle_make" name="vehicle_make" class="form-control" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">Vehicle Model</label>
                            <select id="vehicle_model" name="vehicle_model" class="form-control" required disabled>
                                <option value="">Select Brand First</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Location -->
                <div class="panel-card p-4">
                    <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">
                        <i class="fa-solid fa-location-dot mr-2 text-primary"></i> 3. Location Details
                    </h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">State</label>
                            <select id="state" name="state" class="form-control" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted font-weight-600 mb-2">LGA</label>
                            <select id="lga" name="lga" class="form-control" required disabled>
                                <option value="">Select State First</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary & Checkout -->
            <div class="col-lg-4">
                <div class="panel-card p-4 sticky-top" style="top: 100px; z-index: 10;">
                    <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Order Summary</h3>
                    
                    <div class="form-group mb-4">
                        <label class="small text-muted font-weight-600 mb-2">Insurance Category</label>
                        <select id="variation_code" name="variation_code" class="form-control" required>
                            <option value="">Select Plan...</option>
                        </select>
                    </div>

                    <div class="p-4 rounded-xl mb-4" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Plan Price</span>
                            <span class="small font-weight-bold" id="plan-price-label">₦0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom border-white-5">
                            <span class="text-muted small">Processing Fee</span>
                            <span class="small">₦0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="font-weight-bold">Total Amount</span>
                            <span class="font-weight-bold text-primary h5 m-0" id="total-price-label">₦0.00</span>
                        </div>
                        <input type="hidden" name="amount" id="amount-input" value="0">
                    </div>

                    @if(isset($insuranceProviders) && $insuranceProviders->count() > 1)
                    <div class="form-group mb-4">
                        <label class="small text-muted font-weight-600 mb-2">API Provider</label>
                        <select name="api_provider_id" class="form-control">
                            @foreach($insuranceProviders as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 60px;">
                        <i class="fa-solid fa-shield-check mr-2"></i> Purchase Insurance
                    </button>

                    <div class="mt-4 p-3 rounded bg-info-soft border-info-20">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-circle-info text-info mr-3"></i>
                            <p class="small text-muted m-0">Your certificate will be ready for download immediately after payment.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 24px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); font-size: 14px; }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .form-control:focus { background: rgba(255,255,255,0.05); border-color: var(--clr-primary); }
    select.form-control { height: 50px; cursor: pointer; }
    .bg-info-soft { background: rgba(59, 130, 246, 0.05); }
    .border-info-20 { border: 1px solid rgba(59, 130, 246, 0.2); }
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .animate__animated { --animate-duration: 0.5s; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Load initial options
        loadOptions('variation', '#variation_code');
        loadOptions('color', '#vehicle_color');
        loadOptions('engine-capacity', '#engine_capacity');
        loadOptions('brand', '#vehicle_make');
        loadOptions('state', '#state');

        // Dynamic Model Loading
        $('#vehicle_make').on('change', function() {
            let brandCode = $(this).val();
            if (brandCode) {
                $('#vehicle_model').prop('disabled', false).html('<option value="">Loading Models...</option>');
                loadOptions('model', '#vehicle_model', brandCode);
            } else {
                $('#vehicle_model').prop('disabled', true).html('<option value="">Select Brand First</option>');
            }
        });

        // Dynamic LGA Loading
        $('#state').on('change', function() {
            let stateCode = $(this).val();
            if (stateCode) {
                $('#lga').prop('disabled', false).html('<option value="">Loading LGAs...</option>');
                loadOptions('lga', '#lga', stateCode);
            } else {
                $('#lga').prop('disabled', true).html('<option value="">Select State First</option>');
            }
        });

        // Price Update
        $('#variation_code').on('change', function() {
            let option = $(this).find('option:selected');
            let price = option.data('price') || 0;
            let formattedPrice = '₦' + parseFloat(price).toLocaleString();
            $('#plan-price-label, #total-price-label').text(formattedPrice);
            $('#amount-input').val(price);
        });

        // Form Submit
        $('#insuranceForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Insurance',
                text: "Purchase Third Party Motor Insurance for ₦" + parseFloat($('#amount-input').val()).toLocaleString() + "?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8b5cf6',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Registering Policy...');
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: 'Policy Issued!',
                                    text: 'Your motor insurance policy has been registered.',
                                    icon: 'success',
                                    footer: `<a href="${response.certUrl}" target="_blank" class="btn btn-primary w-100">Download Certificate</a>`,
                                    background: '#0a0a0f',
                                    color: '#fff'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({ title: 'Payment Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Network Error', text: 'Error communicating with insurance server.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });

    function loadOptions(type, selector, id = null) {
        let url = "{{ route('services.insurance.motor.options') }}?type=" + type;
        if (id) url += "&id=" + id;

        $.get(url, function(res) {
            if (res.status) {
                let html = '<option value="">Select Option</option>';
                
                if (type === 'variation') {
                    res.data.variations.forEach(item => {
                        html += `<option value="${item.variation_code}" data-price="${item.variation_amount}">${item.name}</option>`;
                    });
                } else {
                    res.data.forEach(item => {
                        let value = item.ColourCode || item.VehicleMakeCode || item.VehicleModelCode || item.StateCode || item.LGACode || item.CapacityCode;
                        let name = item.ColourName || item.VehicleMakeName || item.VehicleModelName || item.StateName || item.LGAName || item.CapacityName;
                        html += `<option value="${value}">${name}</option>`;
                    });
                }
                $(selector).html(html);
            }
        });
    }
</script>
@endpush

@extends('layouts.nexus')

@section('title', 'Plate Number Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05)); border-color: rgba(16, 185, 129, 0.2);">
        <div class="sh-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
            <i class="fa-solid fa-car-rear"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Plate Number Verification</h1>
            <p class="text-muted small">Verify vehicle registration and ownership details instantly.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-shield-car text-success"></i> FRSC Validated</span>
            <span class="badge-accent"><i class="fa-solid fa-id-card text-info"></i> Official Data</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="panel-card p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Vehicle Lookup</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦200.00</span>
                </div>

                <form id="plateForm" action="{{ route('services.plate_number.verify') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label for="vehicle_number" class="font-weight-600 mb-2 small text-muted">Vehicle Plate Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-hashtag"></i>
                            <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" placeholder="AAA000000" required>
                        </div>
                        <p class="x-small text-muted mt-2"><i class="fa-solid fa-circle-info mr-1"></i> Enter the plate number without spaces or special characters.</p>
                    </div>

                    @if(isset($plateProviders) && $plateProviders->count() > 1)
                    <div class="form-group mb-4">
                        <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Verification Source</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-server"></i>
                            <select id="api_provider_id" name="api_provider_id" class="form-control">
                                @foreach($plateProviders as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @elseif(isset($plateProviders) && $plateProviders->count() == 1)
                        <input type="hidden" name="api_provider_id" value="{{ $plateProviders->first()->id }}">
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 55px;">
                        <i class="fa-solid fa-bolt mr-2"></i> Verify Plate Number
                    </button>
                </form>

                <!-- Results display area -->
                <div id="resultArea" class="mt-4" style="display: none;">
                    <div class="p-4 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="text-center mb-4">
                            <div class="plate-badge mb-3">
                                <span id="res-plate-disp">-</span>
                            </div>
                            <h4 class="h6 font-weight-bold text-white mb-1" id="res-status">Verified Successfully</h4>
                            <p class="x-small text-success mb-0" id="res-message">Valid and assigned to the vehicle</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Vehicle Name/Model</label>
                                <strong id="res-name" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Vehicle Color</label>
                                <strong id="res-color" class="text-white small">-</strong>
                            </div>
                        </div>

                        <button class="btn btn-outline-light btn-block mt-4" onclick="window.location.reload()">New Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4">
                <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Verification Info</h3>
                <div class="info-item mb-4 d-flex align-items-start gap-3">
                    <div class="icon-circle bg-success-soft text-success">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h6 class="small font-weight-bold text-white mb-1">Instant Results</h6>
                        <p class="x-small text-muted m-0">Get real-time data from the official vehicle registration database.</p>
                    </div>
                </div>
                <div class="info-item mb-4 d-flex align-items-start gap-3">
                    <div class="icon-circle bg-info-soft text-info">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h6 class="small font-weight-bold text-white mb-1">Anti-Fraud</h6>
                        <p class="x-small text-muted m-0">Useful for law enforcement, insurance claims, and private transactions to confirm vehicle legitimacy.</p>
                    </div>
                </div>
                
                <div class="plate-sample mt-4">
                    <div class="p-3 rounded-xl text-center" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <p class="x-small text-muted mb-2">Sample Plate Format</p>
                        <div class="d-inline-block px-4 py-2 bg-white text-dark font-weight-bold rounded" style="border: 2px solid #000; letter-spacing: 2px; font-family: 'Courier New', Courier, monospace;">
                            AAA-000-XX
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 24px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .x-small { font-size: 0.7rem; }
    .icon-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-info-soft { background: rgba(59, 130, 246, 0.1); }
    .plate-badge { display: inline-block; padding: 10px 30px; background: #fff; color: #333; border: 3px solid #000; border-radius: 8px; font-weight: 900; font-size: 1.2rem; letter-spacing: 2px; font-family: 'Courier New', Courier, monospace; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#plateForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Lookup',
                text: "Verify this vehicle plate for ₦200.00?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Accessing Registry...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let data = response.data;
                                $('#plateForm').slideUp();
                                
                                $('#res-plate-disp').text(data.vehicle_number || $('#vehicle_number').val());
                                $('#res-name').text(data.vehicle_name || 'N/A');
                                $('#res-color').text(data.vehicle_color || 'N/A');
                                $('#res-message').text(response.message);

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', text: response.message, icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Search Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'IdentityPay server error.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

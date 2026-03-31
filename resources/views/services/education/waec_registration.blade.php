@extends('layouts.nexus')

@section('title', 'WAEC Registration PIN | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); border-color: rgba(59, 130, 246, 0.2);">
        <div class="sh-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3);">
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">WAEC Registration PIN</h1>
            <p class="text-muted small">Purchase WASSCE / GCE e-PINs for examination enrollment.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-bolt text-warning"></i> Instant Token</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved text-primary"></i> Secure Gateway</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="panel-card p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-cart-shopping mr-2 text-primary"></i> Order Registration PIN</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦18,500.00</span>
                </div>

                <form id="waecRegForm" action="{{ route('services.education.waec_registration.buy') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 small text-muted">Examination Category</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <select class="form-control" name="variation_code" readonly>
                                <option value="waec-registration">WAEC (WASSCE) Registration PIN</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="phone" class="font-weight-600 mb-2 small text-muted">Candidate Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="08012345678" maxlength="11" required>
                        </div>
                        <small class="text-muted mt-2 d-block"><i class="fa-solid fa-circle-info mr-1"></i> The e-PIN will be sent to this number for use on WAEC portal.</small>
                    </div>

                    @if(isset($waecProviders) && $waecProviders->count() > 1)
                    <div class="form-group mb-4">
                        <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Preferred Provider</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-server"></i>
                            <select id="api_provider_id" name="api_provider_id" class="form-control">
                                @foreach($waecProviders as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @elseif(isset($waecProviders) && $waecProviders->count() == 1)
                        <input type="hidden" name="api_provider_id" value="{{ $waecProviders->first()->id }}">
                    @endif

                    <div class="p-3 mb-4 rounded" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Platform Fee</span>
                            <span class="small">₦0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="font-weight-bold">Total Payable</span>
                            <span class="font-weight-bold text-primary">₦18,500.00</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 55px;">
                        <i class="fa-solid fa-bolt mr-2"></i> Purchase Registration PIN
                    </button>
                </form>

                <div id="resultArea" class="mt-4 animate__animated animate__fadeIn" style="display: none;">
                    <div class="p-4 rounded-lg text-center" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                        <div class="mb-3"><i class="fa-solid fa-circle-check text-primary fa-3x"></i></div>
                        <h4 class="h5 font-weight-bold text-white mb-1">Registration PIN Issued</h4>
                        <p class="text-muted small mb-4">Your exam registration credentials are below.</p>
                        
                        <div class="pin-display-card p-3 mb-4" style="background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <div class="row no-gutters align-items-center">
                                <div class="col-6 border-right border-white-10">
                                    <span class="text-muted d-block small mb-1">SERIAL</span>
                                    <strong id="res-serial" class="h6 text-primary tracking-wider">-</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block small mb-1">PIN / TOKEN</span>
                                    <strong id="res-pin" class="h6 text-white tracking-wider">-</strong>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-sm btn-outline-light w-100" onclick="copyPin()">
                            <i class="fa-solid fa-copy mr-2"></i> Copy Pin & Serial
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4 h-100">
                <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Important Notes</h3>
                <ul class="list-unstyled">
                    <li class="d-flex gap-3 mb-4">
                        <div class="rounded-circle bg-primary-soft d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; font-size: 12px; font-weight: 800; color: var(--clr-primary);">!</div>
                        <div class="small text-muted">This PIN is for **EXAM REGISTRATION** only, not for result checking.</div>
                    </li>
                    <li class="d-flex gap-3 mb-4">
                        <div class="rounded-circle bg-primary-soft d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; font-size: 12px; font-weight: 800; color: var(--clr-primary);">*</div>
                        <div class="small text-muted">A copy of the PIN and Serial will be sent to the phone number via SMS.</div>
                    </li>
                    <li class="d-flex gap-3 mb-4">
                        <div class="rounded-circle bg-primary-soft d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; font-size: 12px; font-weight: 800; color: var(--clr-primary);">V</div>
                        <div class="small text-muted">Visit [registration.waecdirect.org](https://registration.waecdirect.org) to use your PIN.</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 24px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 18px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 50px !important; height: 52px; }
    .bg-primary-soft { background: rgba(59, 130, 246, 0.1); }
    .tracking-wider { letter-spacing: 1px; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#waecRegForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalBtnHtml = btn.html();
            
            Swal.fire({
                title: 'Confirm Registration PIN',
                text: "You are about to purchase a WAEC Registration PIN for ₦18,500.00. Continue?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirm Purchase',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Generating e-PIN...');
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                $('#waecRegForm').slideUp();
                                $('#res-pin').text(response.pin);
                                $('#res-serial').text(response.serial);
                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Error', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalBtnHtml);
                            }
                        }
                    });
                }
            });
        });
    });

    function copyPin() {
        let text = "SERIAL: " + $('#res-serial').text() + "\nPIN: " + $('#res-pin').text();
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Copied!', showConfirmButton: false, timer: 1500 });
        });
    }
</script>
@endpush

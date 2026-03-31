@extends('layouts.nexus')

@section('title', 'Airtime Top-up | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Airtime"
        subtitle="Instant airtime top-up for all major Nigerian networks."
        icon="fa-solid fa-mobile-screen-button"
        icon-class="vtu-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant</span>
            <span class="badge-accent"><i class="fa-solid fa-gift"></i> 2% Cashback</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form id="airtimeForm" action="{{ route('services.vtu.airtime.buy') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Network Provider</label>
                        <div class="network-grid">
                            @foreach(['MTN', 'GLO', 'AIRTEL', '9MOBILE'] as $net)
                            <label class="nexus-net-option">
                                <input type="radio" name="network" value="{{ $net }}" required>
                                <div class="net-box">
                                    <img src="{{ asset('images/' . strtolower($net) . '.png') }}" alt="{{ $net }}">
                                    <span>{{ $net }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="phone" class="font-weight-600 mb-2">Recipient Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="081 2345 6789" maxlength="11" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="amount" class="font-weight-600 mb-2">Amount (₦)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-naira-sign"></i>
                            <input type="number" name="amount" id="amount" class="form-control" placeholder="100 - 50,000" min="50" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="buy-btn">
                        <i class="fa-solid fa-bolt mr-2"></i> Purchase Top-up
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Transmitting through secure channel...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3">Quick Actions</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-layer-group mr-2"></i> Back to VTU Hub
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-wifi mr-2"></i> Switch to Data
                    </a>
                    <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-tv mr-2"></i> Cable TV Hub
                    </a>
                    <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-lightbulb mr-2"></i> Electricity Bills
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon vtu-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">99.9%</div>
                <div class="stat-label">Uptime Reliability</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-header-card { background: rgba(255,255,255,0.03); border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .vtu-bg { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .sh-text h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
    .sh-text p { margin: 4px 0 0; color: var(--clr-text-muted); font-size: 0.95rem; }
    
    .network-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .nexus-net-option { cursor: pointer; }
    .nexus-net-option input { display: none; }
    .net-box { border: var(--border-glass); background: rgba(255,255,255,0.03); border-radius: 14px; padding: 12px; text-align: center; transition: all 0.2s; }
    .net-box img { max-height: 25px; margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto; }
    .net-box span { font-size: 0.7rem; font-weight: 700; color: var(--clr-text-muted); }
    .nexus-net-option input:checked + .net-box { border-color: var(--clr-primary); background: rgba(59, 130, 246, 0.1); }
    .nexus-net-option input:checked + .net-box span { color: #fff; }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#airtimeForm').on('submit', function(e) {
            e.preventDefault();
            $('#loaderOverlay').show();
            let btn = $('#buy-btn');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Processing...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#loaderOverlay').hide();
                    if (response.status) {
                        Swal.fire({ 
                            title: 'Success!', 
                            text: response.message, 
                            icon: 'success',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                        $('#airtimeForm')[0].reset();
                    } else {
                        Swal.fire({ 
                            title: 'Failed', 
                            text: response.message, 
                            icon: 'error',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    }
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Purchase Top-up');
                },
                error: function() {
                    $('#loaderOverlay').hide();
                    Swal.fire({ 
                        title: 'Error', 
                        text: 'An unexpected error occurred.', 
                        icon: 'error',
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Purchase Top-up');
                }
            });
        });
    });
</script>
@endpush

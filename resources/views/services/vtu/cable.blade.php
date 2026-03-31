@extends('layouts.nexus')

@section('title', 'Cable TV Subscription | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Cable TV"
        subtitle="Renew your DSTV, GOTV, and Startimes subscriptions instantly."
        icon="fa-solid fa-tv"
        icon-class="cable-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Activation</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form id="cableForm" action="{{ route('services.vtu.cable.buy') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Provider</label>
                        <div class="network-grid">
                            @foreach(['DSTV', 'GOTV', 'STARTIMES'] as $provider)
                            <label class="nexus-net-option">
                                <input type="radio" name="serviceID" value="{{ strtolower($provider) }}" required>
                                <div class="net-box">
                                    <img src="{{ asset('images/' . strtolower($provider) . '.png') }}" alt="{{ $provider }}" onerror="this.src='https://placehold.co/40x40?text={{ $provider }}'">
                                    <span>{{ $provider }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="smart_card_number" class="font-weight-600 mb-2">Smart Card / IUC Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-id-card"></i>
                            <input type="text" name="smart_card_number" id="smart_card_number" class="form-control" placeholder="Enter Card Number" required>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info mt-2" id="verify-iuc">Verify Card</button>
                    </div>

                    <div class="form-group mb-4">
                        <label for="variation_code" class="font-weight-600 mb-2">Select Package</label>
                        <select name="variation_code" id="variation_code" class="form-control" required>
                            <option value="">Select a package</option>
                            <!-- Packages will be loaded dynamically via AJAX or hardcoded for now -->
                            <option value="dstv-padi">DSTV Padi - ₦2,500</option>
                            <option value="gotv-smallie">GOTV Smallie - ₦1,100</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="amount" class="font-weight-600 mb-2">Amount (₦)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-naira-sign"></i>
                            <input type="number" name="amount" id="amount" class="form-control" placeholder="0.00" readonly required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="phone" class="font-weight-600 mb-2">Recipient Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="081 2345 6789" maxlength="11" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="buy-btn">
                        <i class="fa-solid fa-bolt mr-2"></i> Pay Subscription
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Processing your subscription...</p>
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
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-phone mr-2"></i> Buy Airtime
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-wifi mr-2"></i> Buy Data
                    </a>
                    <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-lightbulb mr-2"></i> Electricity Bills
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon cable-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">Verified</div>
                <div class="stat-label">Secure Transactions</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-header-card { background: rgba(255,255,255,0.03); border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .cable-bg { background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.2); }
    .sh-text h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
    .sh-text p { margin: 4px 0 0; color: var(--clr-text-muted); font-size: 0.95rem; }
    
    .network-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
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
        // Simple amount mapping for demo
        const prices = {
            'dstv-padi': 2500,
            'gotv-smallie': 1100
        };

        $('#variation_code').on('change', function() {
            let price = prices[$(this).val()] || 0;
            $('#amount').val(price);
        });

        $('#cableForm').on('submit', function(e) {
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
                        Swal.fire({ title: 'Success!', text: response.message, icon: 'success', background: '#0a0a0f', color: '#fff' });
                        $('#cableForm')[0].reset();
                    } else {
                        Swal.fire({ title: 'Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                    }
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Pay Subscription');
                },
                error: function() {
                    $('#loaderOverlay').hide();
                    Swal.fire({ title: 'Error', text: 'An unexpected error occurred.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Pay Subscription');
                }
            });
        });
    });
</script>
@endpush

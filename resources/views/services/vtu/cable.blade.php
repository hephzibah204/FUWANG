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
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> IUC Verified</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <x-nexus.flash-message />
                <form id="cableForm" action="{{ route('services.vtu.cable.buy') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Provider</label>
                        <div class="network-grid">
                            @foreach(['GOTV', 'DSTV', 'STARTIMES'] as $provider)
                            <label class="nexus-net-option">
                                <input type="radio" name="serviceID" value="{{ strtolower($provider) }}" {{ $loop->first ? 'checked' : '' }} required>
                                <div class="net-box">
                                    <img src="{{ asset('images/' . strtolower($provider) . '.png') }}" alt="{{ $provider }}" onerror="this.onerror=null; this.src='{{ asset('vtusite/images/' . strtolower($provider) . '.png') }}';">
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
                            <input type="text" name="smart_card_number" id="smart_card_number" class="form-control" placeholder="Enter Smart Card / IUC Number" required>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <button type="button" class="btn btn-sm btn-outline-info" id="verify-iuc">
                                <i class="fa-solid fa-magnifying-glass mr-1"></i> Verify Card Holder
                            </button>
                            <span id="verify-loading" class="text-muted small" style="display:none;">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i> Verifying with provider...
                            </span>
                        </div>
                        <div id="customer-info" class="mt-2 p-2 rounded" style="display:none; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25);">
                            <div class="small text-success font-weight-bold">
                                <i class="fa-solid fa-circle-check mr-1"></i> <span id="customer-name">Verified</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="variation_code" class="font-weight-600 mb-2">Select Package</label>
                        <select name="variation_code" id="variation_code" class="form-control" required>
                            <option value="">-- Choose a package --</option>
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
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="081 2345 6789" maxlength="11" value="{{ auth()->user()->phone ?? '' }}" required>
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
                    <a href="{{ route('services.vtu.bulksms') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-comments mr-2"></i> Bulk SMS
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon cable-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">Verified</div>
                <div class="stat-label">Fast IUC Verification & Activation</div>
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
    .net-box span { font-size: 0.75rem; font-weight: 700; color: var(--clr-text-muted); }
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
        // Packages catalog per cable provider
        const cablePackages = {
            'gotv': [
                { id: '1', name: 'GOTV Smallie - Monthly', amount: 1575 },
                { id: '2', name: 'GOTV Jinja - Monthly', amount: 3300 },
                { id: '3', name: 'GOTV Jolli - Monthly', amount: 4850 },
                { id: '4', name: 'GOTV Max - Monthly', amount: 7200 },
                { id: '5', name: 'GOTV Supa - Monthly', amount: 9600 },
                { id: '6', name: 'GOTV Supa Plus - Monthly', amount: 15700 }
            ],
            'dstv': [
                { id: '1', name: 'DSTV Padi - Monthly', amount: 4400 },
                { id: '2', name: 'DSTV Yanga - Monthly', amount: 5100 },
                { id: '3', name: 'DSTV Confam - Monthly', amount: 11000 },
                { id: '4', name: 'DSTV Compact - Monthly', amount: 19000 },
                { id: '5', name: 'DSTV Compact Plus - Monthly', amount: 30000 },
                { id: '6', name: 'DSTV Premium - Monthly', amount: 44000 }
            ],
            'startimes': [
                { id: '1', name: 'Startimes Nova - Monthly', amount: 1700 },
                { id: '2', name: 'Startimes Basic - Monthly', amount: 3300 },
                { id: '3', name: 'Startimes Smart - Monthly', amount: 4200 },
                { id: '4', name: 'Startimes Classic - Monthly', amount: 5000 },
                { id: '5', name: 'Startimes Super - Monthly', amount: 9000 }
            ]
        };

        function populatePackages() {
            const provider = $('input[name="serviceID"]:checked').val() || 'gotv';
            const packages = cablePackages[provider] || [];
            const select = $('#variation_code');
            select.empty();
            select.append('<option value="">-- Choose a package --</option>');

            packages.forEach(function(pkg) {
                select.append($('<option>', {
                    value: pkg.id,
                    text: pkg.name + ' - ₦' + Number(pkg.amount).toLocaleString(),
                    'data-amount': pkg.amount
                }));
            });

            $('#amount').val('');
        }

        // Populate initially
        populatePackages();

        // On provider switch
        $('input[name="serviceID"]').on('change', function() {
            populatePackages();
            $('#customer-info').hide();
            $('#customer-name').text('');
        });

        // On package select
        $('#variation_code').on('change', function() {
            const selected = $(this).find(':selected');
            const amount = selected.data('amount') || '';
            $('#amount').val(amount);
        });

        // Smart Card / IUC Verification
        $('#verify-iuc').on('click', function() {
            const provider = $('input[name="serviceID"]:checked').val();
            const iuc = $('#smart_card_number').val().trim();

            if (!iuc) {
                Swal.fire({
                    icon: 'warning',
                    title: 'IUC Required',
                    text: 'Please enter your Smart Card or IUC number first.',
                    background: '#0a0a0f',
                    color: '#fff'
                });
                return;
            }

            $('#verify-loading').show();
            $('#verify-iuc').prop('disabled', true);
            $('#customer-info').hide();

            $.ajax({
                url: "{{ route('services.vtu.cable.validate') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    serviceID: provider,
                    smart_card_number: iuc
                },
                success: function(res) {
                    $('#verify-loading').hide();
                    $('#verify-iuc').prop('disabled', false);

                    if (res.status) {
                        const name = (res.customer && res.customer.name) ? res.customer.name : (res.name || 'Verified Customer');
                        $('#customer-name').text('Customer: ' + name);
                        $('#customer-info').fadeIn();
                        toastr.success('Customer verified: ' + name);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Verification Failed',
                            text: res.message || 'Could not verify smart card number. Please check the number and provider.',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    }
                },
                error: function(xhr) {
                    $('#verify-loading').hide();
                    $('#verify-iuc').prop('disabled', false);
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to verify smart card.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: msg,
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                }
            });
        });

        // Form Submit
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
                        Swal.fire({
                            title: 'Subscription Successful!',
                            text: response.message || 'Your cable TV subscription has been activated.',
                            icon: 'success',
                            background: '#0a0a0f',
                            color: '#fff'
                        }).then(() => {
                            $('#cableForm')[0].reset();
                            populatePackages();
                            $('#customer-info').hide();
                        });
                    } else {
                        Swal.fire({
                            title: 'Subscription Failed',
                            text: response.message || 'Transaction could not be completed.',
                            icon: 'error',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    }
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Pay Subscription');
                },
                error: function(xhr) {
                    $('#loaderOverlay').hide();
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An unexpected error occurred.';
                    Swal.fire({
                        title: 'Error',
                        text: msg,
                        icon: 'error',
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Pay Subscription');
                }
            });
        });
    });
</script>
@endpush

@extends('layouts.nexus')

@section('title', 'Airtime Top-up | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Airtime Top-up"
        subtitle="Instant airtime top-up for all major Nigerian networks with automated delivery."
        icon="fa-solid fa-mobile-screen-button"
        icon-class="vtu-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant</span>
            <span class="badge-accent"><i class="fa-solid fa-gift"></i> {{ (float) \App\Models\SystemSetting::get('vtu_airtime_discount_percent', 2.0) }}% Cashback</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <x-nexus.flash-message />

                @php
                    $airtimeDiscount = (float) \App\Models\SystemSetting::get('vtu_airtime_discount_percent', 2.0);
                @endphp
                @if($airtimeDiscount > 0)
                    <div class="p-3 mb-4 rounded-3 d-flex align-items-center justify-content-between" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-tags text-success fa-lg mr-3"></i>
                            <div>
                                <span class="font-weight-bold text-white small d-block">Special Airtime Discount Active</span>
                                <span class="text-muted small">Enjoy {{ $airtimeDiscount }}% instant discount deducted automatically at checkout.</span>
                            </div>
                        </div>
                        <span class="badge badge-success px-2 py-1 font-weight-bold">{{ $airtimeDiscount }}% OFF</span>
                    </div>
                @endif

                <form id="airtimeForm" action="{{ route('services.vtu.airtime.buy') }}" method="POST" class="vtu-form">
                    @csrf
                    
                    <!-- 1. Select Network Provider -->
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-flex align-items-center justify-content-between">
                            <span class="text-white">1. Select Network Provider</span>
                            <span class="small text-muted" id="selectedNetLabel">Selected: <strong class="text-warning">MTN</strong></span>
                        </label>
                        <div class="network-grid">
                            <!-- MTN -->
                            <label class="nexus-net-option" for="net_airtime_MTN" data-net="MTN">
                                <input type="radio" name="network" id="net_airtime_MTN" value="MTN" checked required>
                                <div class="net-box net-mtn">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/mtn.png') }}" alt="MTN" class="net-logo-img">
                                    </div>
                                    <span class="net-title">MTN</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- GLO -->
                            <label class="nexus-net-option" for="net_airtime_GLO" data-net="GLO">
                                <input type="radio" name="network" id="net_airtime_GLO" value="GLO" required>
                                <div class="net-box net-glo">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/glo.png') }}" alt="GLO" class="net-logo-img">
                                    </div>
                                    <span class="net-title">GLO</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- AIRTEL -->
                            <label class="nexus-net-option" for="net_airtime_AIRTEL" data-net="AIRTEL">
                                <input type="radio" name="network" id="net_airtime_AIRTEL" value="AIRTEL" required>
                                <div class="net-box net-airtel">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/airtel.png') }}" alt="AIRTEL" class="net-logo-img">
                                    </div>
                                    <span class="net-title">AIRTEL</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- 9MOBILE -->
                            <label class="nexus-net-option" for="net_airtime_9MOBILE" data-net="9MOBILE">
                                <input type="radio" name="network" id="net_airtime_9MOBILE" value="9MOBILE" required>
                                <div class="net-box net-9mobile">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/9mobile.png') }}" alt="9MOBILE" class="net-logo-img">
                                    </div>
                                    <span class="net-title">9MOBILE</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Recipient Phone Number -->
                    <div class="form-group mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label for="phone" class="font-weight-600 text-white m-0">2. Recipient Phone Number</label>
                            @if(auth()->check() && auth()->user()->phone)
                                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" id="btnUseMyPhone" data-phone="{{ auth()->user()->phone }}">
                                    <i class="fa-solid fa-user mr-1"></i> Use My Phone
                                </button>
                            @endif
                        </div>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone text-primary"></i>
                            <input type="tel" name="phone" id="phone" class="form-control vtu-input" placeholder="0801 234 5678" maxlength="11" required value="{{ old('phone') }}">
                        </div>
                    </div>

                    <!-- 3. Amount & Quick Chips -->
                    <div class="form-group mb-4">
                        <label for="amount" class="font-weight-600 mb-2 text-white">3. Amount (₦)</label>
                        <div class="input-wrap mb-2">
                            <i class="fa-solid fa-naira-sign text-primary"></i>
                            <input type="number" name="amount" id="amount" class="form-control vtu-input" placeholder="100 - 50,000" min="50" max="50000" required value="{{ old('amount', 500) }}">
                        </div>
                        
                        <!-- Quick Amount Selection Chips -->
                        <div class="d-flex flex-wrap gap-2 mt-2" style="gap: 8px;">
                            @foreach([100, 200, 500, 1000, 2000, 5000] as $chip)
                                <button type="button" class="btn btn-sm btn-outline-secondary amount-chip py-1 px-3" data-amount="{{ $chip }}">
                                    ₦{{ number_format($chip) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- 4. Live Order Breakdown Card -->
                    <div class="p-3 mb-4 rounded-3 vtu-summary-card" id="summaryCard">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-white-10">
                            <span class="text-muted small">Airtime Summary</span>
                            <span class="badge badge-success px-2 py-1 font-weight-bold" id="sumNetworkBadge">MTN TOP-UP</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-white-50 small">Top-up Value:</span>
                            <span class="text-white font-weight-600 small" id="sumFaceAmount">₦500.00</span>
                        </div>
                        @if($airtimeDiscount > 0)
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-success small">Instant Discount ({{ $airtimeDiscount }}%):</span>
                            <span class="text-success font-weight-bold small" id="sumDiscount">-₦10.00</span>
                        </div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-white-50 small">Amount to Pay:</span>
                            <span class="text-warning font-weight-bold h5 mb-0" id="sumChargedDisplay">₦490.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-white-10">
                            <span class="text-muted small">Wallet Balance:</span>
                            <span class="text-white small font-weight-600">
                                ₦{{ number_format((float) ($walletBalance ?? 0), 2) }}
                                @if(($walletBalance ?? 0) < 100)
                                    <a href="{{ route('wallet.fund') }}" class="ml-2 text-primary font-weight-bold" style="text-decoration: underline;">Fund</a>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-bold" id="buy-btn">
                        <i class="fa-solid fa-bolt mr-2"></i> Purchase Top-up
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small">Transmitting through secure channel...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3 text-white">Quick Actions</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-layer-group mr-2 text-primary"></i> Back to VTU Hub
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-wifi mr-2 text-info"></i> Switch to Data
                    </a>
                    <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-tv mr-2 text-warning"></i> Cable TV Hub
                    </a>
                    <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-lightbulb mr-2 text-danger"></i> Electricity Bills
                    </a>
                    <a href="{{ route('services.vtu.bulksms') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-comments mr-2 text-success"></i> Bulk SMS
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon vtu-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">99.9%</div>
                <div class="stat-label">Uptime Reliability (Network Band)</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .network-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .nexus-net-option {
        cursor: pointer;
        margin: 0;
        position: relative;
        user-select: none;
        -webkit-user-select: none;
    }
    .nexus-net-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        border: 0;
        clip: rect(0 0 0 0);
        overflow: hidden;
        pointer-events: none;
    }
    .net-box {
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.03);
        border-radius: 14px;
        padding: 12px 8px;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 94px;
    }
    .net-logo-wrap {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 6px;
        padding: 5px;
        transition: transform 0.2s ease;
    }
    .net-logo-img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
    }
    .nexus-net-option input:checked + .net-box .net-logo-wrap {
        transform: scale(1.08);
        background: rgba(59, 130, 246, 0.15);
    }
    .net-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--clr-text-muted, #94a3b8);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .net-check {
        position: absolute;
        top: 6px;
        right: 6px;
        font-size: 0.8rem;
        color: #3b82f6;
        opacity: 0;
        transform: scale(0.6);
        transition: all 0.2s ease;
    }
    .net-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        position: absolute;
        bottom: 6px;
        opacity: 0.6;
    }
    .nexus-net-option input:checked + .net-box {
        border-color: #3b82f6 !important;
        background: rgba(59, 130, 246, 0.12) !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }
    .nexus-net-option input:checked + .net-box .net-title {
        color: #ffffff !important;
        font-weight: 800;
    }
    .nexus-net-option input:checked + .net-box .net-badge-icon {
        transform: scale(1.08);
    }
    .nexus-net-option input:checked + .net-box .net-check {
        opacity: 1;
        transform: scale(1);
    }

    /* Form Controls Dark Theme */
    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrap i {
        position: absolute;
        left: 16px;
        font-size: 1rem;
        z-index: 2;
        pointer-events: none;
    }
    .vtu-input,
    .vtu-form .form-control {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        padding-left: 48px !important;
        height: 52px !important;
        font-size: 0.95rem !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }
    .vtu-form .form-control:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        color: #ffffff !important;
    }
    .amount-chip {
        border-color: rgba(255, 255, 255, 0.15) !important;
        color: #94a3b8 !important;
        border-radius: 20px;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .amount-chip:hover,
    .amount-chip.active {
        background: rgba(59, 130, 246, 0.2) !important;
        border-color: #3b82f6 !important;
        color: #ffffff !important;
    }
    .vtu-summary-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .border-white-10 {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    function initAirtimePage() {
        const discountPercent = {{ (float) \App\Models\SystemSetting::get('vtu_airtime_discount_percent', 2.0) }};
        const networkRadios = document.querySelectorAll('input[name="network"]');
        const amountInput = document.getElementById('amount');
        const phoneInput = document.getElementById('phone');
        const selectedNetLabel = document.getElementById('selectedNetLabel');
        const sumNetworkBadge = document.getElementById('sumNetworkBadge');
        const sumFaceAmount = document.getElementById('sumFaceAmount');
        const sumDiscount = document.getElementById('sumDiscount');
        const sumChargedDisplay = document.getElementById('sumChargedDisplay');
        const btnUseMyPhone = document.getElementById('btnUseMyPhone');
        const amountChips = document.querySelectorAll('.amount-chip');
        const airtimeForm = document.getElementById('airtimeForm');
        const buyBtn = document.getElementById('buy-btn');
        const loaderOverlay = document.getElementById('loaderOverlay');

        // Shortcut: Use user's phone
        if (btnUseMyPhone && phoneInput) {
            btnUseMyPhone.addEventListener('click', function() {
                phoneInput.value = this.getAttribute('data-phone') || '';
                phoneInput.focus();
            });
        }

        function getActiveNetwork() {
            let active = 'MTN';
            networkRadios.forEach(function(radio) {
                if (radio.checked) active = radio.value;
            });
            return active;
        }

        function updateSummary() {
            const net = getActiveNetwork();
            const rawAmount = Number(amountInput ? amountInput.value : 0) || 0;
            const discount = discountPercent > 0 ? (rawAmount * discountPercent / 100) : 0;
            const charged = Math.max(1, rawAmount - discount);

            if (selectedNetLabel) {
                selectedNetLabel.innerHTML = 'Selected: <strong class="text-warning">' + net + '</strong>';
            }
            if (sumNetworkBadge) {
                sumNetworkBadge.textContent = net + ' TOP-UP';
            }
            if (sumFaceAmount) {
                sumFaceAmount.textContent = '₦' + rawAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (sumDiscount) {
                sumDiscount.textContent = '-₦' + discount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (sumChargedDisplay) {
                sumChargedDisplay.textContent = '₦' + charged.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Sync active chip
            amountChips.forEach(function(chip) {
                if (Number(chip.getAttribute('data-amount')) === rawAmount) {
                    chip.classList.add('active');
                } else {
                    chip.classList.remove('active');
                }
            });
        }

        // Direct option card click handling for instantaneous selection across all devices
        document.querySelectorAll('.nexus-net-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    updateSummary();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });

        networkRadios.forEach(function(radio) {
            radio.addEventListener('change', updateSummary);
        });

        if (amountInput) {
            amountInput.addEventListener('input', updateSummary);
        }

        amountChips.forEach(function(chip) {
            chip.addEventListener('click', function() {
                const val = this.getAttribute('data-amount');
                if (amountInput) {
                    amountInput.value = val;
                    updateSummary();
                }
            });
        });

        // Initialize display
        updateSummary();

        // Progressive Ajax Submit
        if (airtimeForm) {
            airtimeForm.addEventListener('submit', function(e) {
                if (!window.jQuery) {
                    return true;
                }

                e.preventDefault();
                if (loaderOverlay) loaderOverlay.style.display = 'block';
                if (buyBtn) {
                    buyBtn.disabled = true;
                    buyBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Transmitting Airtime...';
                }

                const formData = $(airtimeForm).serialize();
                $.ajax({
                    url: airtimeForm.action,
                    method: 'POST',
                    data: formData,
                    success: function(resp) {
                        if (loaderOverlay) loaderOverlay.style.display = 'none';
                        if (resp && resp.status) {
                            if (window.Swal) {
                                Swal.fire({
                                    title: 'Airtime Sent!',
                                    text: resp.message || 'Airtime top-up delivered successfully.',
                                    icon: 'success',
                                    background: '#0a0a0f',
                                    color: '#fff'
                                });
                            } else {
                                alert(resp.message || 'Airtime top-up delivered successfully.');
                            }
                            if (phoneInput) phoneInput.value = '';
                        } else {
                            const msg = (resp && resp.message) ? resp.message : 'Transaction could not be completed.';
                            if (window.Swal) {
                                Swal.fire({
                                    title: 'Top-up Failed',
                                    text: msg,
                                    icon: 'error',
                                    background: '#0a0a0f',
                                    color: '#fff'
                                });
                            } else {
                                alert(msg);
                            }
                        }
                        if (buyBtn) {
                            buyBtn.disabled = false;
                            buyBtn.innerHTML = '<i class="fa-solid fa-bolt mr-2"></i> Purchase Top-up';
                        }
                    },
                    error: function(xhr) {
                        if (loaderOverlay) loaderOverlay.style.display = 'none';
                        const errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Network error communicating with gateway.';
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Order Failed',
                                text: errMsg,
                                icon: 'error',
                                background: '#0a0a0f',
                                color: '#fff'
                            });
                        } else {
                            alert(errMsg);
                        }
                        if (buyBtn) {
                            buyBtn.disabled = false;
                            buyBtn.innerHTML = '<i class="fa-solid fa-bolt mr-2"></i> Purchase Top-up';
                        }
                    }
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAirtimePage);
    } else {
        initAirtimePage();
    }
})();
</script>
@endpush

@extends('layouts.nexus')

@section('title', 'Data Bundles | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Data Bundles"
        subtitle="Reliable high-speed data for all Nigerian networks with automated delivery."
        icon="fa-solid fa-wifi"
        icon-class="data-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Low Latency</span>
            <span class="badge-accent"><i class="fa-solid fa-cloud"></i> Automated</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <x-nexus.flash-message />
                
                <form id="dataForm" action="{{ route('services.vtu.data.buy') }}" method="POST" class="vtu-form">
                    @csrf
                    
                    <!-- 1. Select Network Provider -->
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-flex align-items-center justify-content-between">
                            <span class="text-white">1. Select Network Provider</span>
                            <span class="small text-muted" id="selectedNetLabel">Selected: <strong class="text-warning">MTN</strong></span>
                        </label>
                        <div class="network-grid">
                            <!-- MTN -->
                            <label class="nexus-net-option" for="net_MTN" data-net="MTN">
                                <input type="radio" name="network" id="net_MTN" value="MTN" checked required>
                                <div class="net-box net-mtn">
                                    <img src="{{ asset('images/mtn.png') }}" alt="MTN" class="net-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="net-badge-icon badge-mtn" style="display:none;">MTN</div>
                                    <span class="net-title">MTN</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- GLO -->
                            <label class="nexus-net-option" for="net_GLO" data-net="GLO">
                                <input type="radio" name="network" id="net_GLO" value="GLO" required>
                                <div class="net-box net-glo">
                                    <img src="{{ asset('images/glo.png') }}" alt="GLO" class="net-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="net-badge-icon badge-glo" style="display:none;">GLO</div>
                                    <span class="net-title">GLO</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- AIRTEL -->
                            <label class="nexus-net-option" for="net_AIRTEL" data-net="AIRTEL">
                                <input type="radio" name="network" id="net_AIRTEL" value="AIRTEL" required>
                                <div class="net-box net-airtel">
                                    <img src="{{ asset('images/airtel.png') }}" alt="AIRTEL" class="net-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="net-badge-icon badge-airtel" style="display:none;">AIRTEL</div>
                                    <span class="net-title">AIRTEL</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- 9MOBILE -->
                            <label class="nexus-net-option" for="net_9MOBILE" data-net="9MOBILE">
                                <input type="radio" name="network" id="net_9MOBILE" value="9MOBILE" required>
                                <div class="net-box net-9mobile">
                                    <img src="{{ asset('images/9mobile.png') }}" alt="9MOBILE" class="net-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="net-badge-icon badge-9mobile" style="display:none;">9MOB</div>
                                    <span class="net-title">9MOBILE</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Select Data Plan -->
                    <div class="form-group mb-4">
                        <label for="planSelect" class="font-weight-600 mb-2 text-white">2. Select Data Plan</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-layer-group text-primary"></i>
                            <select name="plan_id" id="planSelect" class="form-control vtu-input" required>
                                <option value="">-- Choose Data Bundle --</option>
                            </select>
                        </div>
                        <input type="hidden" name="amount" id="amountInput" value="0">
                        <small class="text-muted mt-1 d-block"><i class="fa-solid fa-circle-info mr-1"></i> Data bundles are delivered directly to the phone line within seconds.</small>
                    </div>

                    <!-- 3. Recipient Phone Number -->
                    <div class="form-group mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label for="phone" class="font-weight-600 text-white m-0">3. Recipient Phone Number</label>
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

                    <!-- 4. Live Order Summary Card -->
                    <div class="p-3 mb-4 rounded-3 vtu-summary-card" id="summaryCard">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-white-10">
                            <span class="text-muted small">Package Summary</span>
                            <span class="badge badge-primary px-2 py-1 font-weight-bold" id="sumNetworkBadge">MTN DATA</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-white-50 small">Selected Bundle:</span>
                            <span class="text-white font-weight-600 small" id="sumPlanName">Select a plan above</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-white-50 small">Amount to Pay:</span>
                            <span class="text-warning font-weight-bold h5 mb-0" id="sumAmountDisplay">₦0.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-white-10">
                            <span class="text-muted small">Wallet Balance:</span>
                            <span class="text-white small font-weight-600">
                                ₦{{ number_format((float) ($walletBalance ?? 0), 2) }}
                                @if(($walletBalance ?? 0) < 150)
                                    <a href="{{ route('wallet.fund') }}" class="ml-2 text-primary font-weight-bold" style="text-decoration: underline;">Fund</a>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-bold" id="buy-btn">
                        <i class="fa-solid fa-cloud-arrow-down mr-2"></i> Activate Bundle
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small">Connecting to Network Band gateway...</p>
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
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-mobile-screen-button mr-2 text-success"></i> Switch to Airtime
                    </a>
                    <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-lightbulb mr-2 text-warning"></i> Electricity Bills
                    </a>
                    <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-tv mr-2 text-info"></i> Cable TV Hub
                    </a>
                    <a href="{{ route('services.vtu.bulksms') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-comments mr-2 text-danger"></i> Bulk SMS
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon data-bg"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="stat-val">Automated</div>
                <div class="stat-label">Instant Network Band Fulfillment</div>
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
        padding: 14px 10px;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 90px;
    }
    .net-logo-img {
        height: 28px;
        width: auto;
        max-width: 44px;
        object-fit: contain;
        margin-bottom: 6px;
        transition: transform 0.2s ease;
    }
    .net-badge-icon {
        width: 44px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 0.8rem;
        letter-spacing: -0.5px;
        margin-bottom: 6px;
        transition: transform 0.2s ease;
    }
    .badge-mtn {
        background: #ffcc00;
        color: #000000;
        box-shadow: 0 2px 8px rgba(255, 204, 0, 0.3);
    }
    .badge-glo {
        background: #00a859;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 168, 89, 0.3);
    }
    .badge-airtel {
        background: #ed1c24;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(237, 28, 36, 0.3);
    }
    .badge-9mobile {
        background: #00573d;
        color: #00ea90;
        border: 1px solid #00ea90;
        box-shadow: 0 2px 8px rgba(0, 87, 61, 0.3);
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

    /* Form Controls & Inputs Dark Theme */
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
    .vtu-form select.form-control {
        background-color: #111827 !important;
        color: #ffffff !important;
    }
    .vtu-form select.form-control option {
        background: #0f172a !important;
        color: #ffffff !important;
        padding: 8px 12px;
    }
    .vtu-form .form-control:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
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
    // Pure, reliable execution independent of external script loaders
    function initDataBundlePage() {
        const serverPlans = @json($plans ?? []);
        const defaultPlans = {
            'MTN': [
                { id: '1', data_plan: '500MB SME Data', amount: 150, validate: '30 Days' },
                { id: '2', data_plan: '1.0GB SME Data', amount: 290, validate: '30 Days' },
                { id: '3', data_plan: '2.0GB SME Data', amount: 580, validate: '30 Days' },
                { id: '4', data_plan: '3.0GB SME Data', amount: 870, validate: '30 Days' },
                { id: '5', data_plan: '5.0GB SME Data', amount: 1450, validate: '30 Days' },
                { id: '6', data_plan: '10.0GB SME Data', amount: 2900, validate: '30 Days' }
            ],
            'GLO': [
                { id: '1', data_plan: '1.0GB Corporate Gifting', amount: 270, validate: '30 Days' },
                { id: '2', data_plan: '2.0GB Corporate Gifting', amount: 540, validate: '30 Days' },
                { id: '3', data_plan: '3.0GB Corporate Gifting', amount: 810, validate: '30 Days' },
                { id: '5', data_plan: '5.0GB Corporate Gifting', amount: 1350, validate: '30 Days' },
                { id: '10', data_plan: '10.0GB Corporate Gifting', amount: 2700, validate: '30 Days' }
            ],
            'AIRTEL': [
                { id: '1', data_plan: '1.0GB Corporate Gifting', amount: 280, validate: '30 Days' },
                { id: '2', data_plan: '2.0GB Corporate Gifting', amount: 560, validate: '30 Days' },
                { id: '5', data_plan: '5.0GB Corporate Gifting', amount: 1400, validate: '30 Days' },
                { id: '10', data_plan: '10.0GB Corporate Gifting', amount: 2800, validate: '30 Days' }
            ],
            '9MOBILE': [
                { id: '1', data_plan: '1.0GB Corporate Gifting', amount: 250, validate: '30 Days' },
                { id: '2', data_plan: '2.0GB Corporate Gifting', amount: 500, validate: '30 Days' },
                { id: '5', data_plan: '5.0GB Corporate Gifting', amount: 1250, validate: '30 Days' }
            ]
        };

        const planSelect = document.getElementById('planSelect');
        const amountInput = document.getElementById('amountInput');
        const selectedNetLabel = document.getElementById('selectedNetLabel');
        const sumNetworkBadge = document.getElementById('sumNetworkBadge');
        const sumPlanName = document.getElementById('sumPlanName');
        const sumAmountDisplay = document.getElementById('sumAmountDisplay');
        const networkRadios = document.querySelectorAll('input[name="network"]');
        const dataForm = document.getElementById('dataForm');
        const buyBtn = document.getElementById('buy-btn');
        const loaderOverlay = document.getElementById('loaderOverlay');
        const btnUseMyPhone = document.getElementById('btnUseMyPhone');
        const phoneInput = document.getElementById('phone');

        // Shortcut: Use user's own phone
        if (btnUseMyPhone && phoneInput) {
            btnUseMyPhone.addEventListener('click', function() {
                phoneInput.value = this.getAttribute('data-phone') || '';
                phoneInput.focus();
            });
        }

        function getActiveNetwork() {
            let active = 'MTN';
            networkRadios.forEach(function(radio) {
                if (radio.checked) {
                    active = radio.value;
                }
            });
            return active;
        }

        function populatePlans(network) {
            if (!planSelect) return;
            planSelect.innerHTML = '<option value="">-- Choose Data Bundle --</option>';

            let plans = [];
            if (serverPlans && serverPlans[network] && serverPlans[network].length > 0) {
                plans = serverPlans[network];
            } else if (defaultPlans[network]) {
                plans = defaultPlans[network];
            }

            plans.forEach(function(p) {
                const opt = document.createElement('option');
                opt.value = p.id;
                const price = Number(p.amount || 0);
                opt.setAttribute('data-price', price);
                const title = p.data_plan || p.name || 'Data Plan';
                const validity = p.validate ? ' (' + p.validate + ')' : '';
                opt.textContent = `${title}${validity} — ₦${price.toLocaleString()}`;
                planSelect.appendChild(opt);
            });

            // Update UI Labels
            if (selectedNetLabel) {
                selectedNetLabel.innerHTML = 'Selected: <strong class="text-warning">' + network + '</strong>';
            }
            if (sumNetworkBadge) {
                sumNetworkBadge.textContent = network + ' DATA';
            }

            // Auto-select first bundle if available
            if (plans.length > 0) {
                planSelect.selectedIndex = 1;
                updateSummary();
            } else {
                updateSummary();
            }
        }

        function updateSummary() {
            if (!planSelect) return;
            const selectedOpt = planSelect.options[planSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const price = Number(selectedOpt.getAttribute('data-price') || 0);
                if (amountInput) amountInput.value = price;
                if (sumPlanName) sumPlanName.textContent = selectedOpt.textContent.split('—')[0].trim();
                if (sumAmountDisplay) sumAmountDisplay.textContent = '₦' + price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                if (amountInput) amountInput.value = 0;
                if (sumPlanName) sumPlanName.textContent = 'Select a plan above';
                if (sumAmountDisplay) sumAmountDisplay.textContent = '₦0.00';
            }
        }

        // Direct option card click handling for instantaneous selection across all browsers
        document.querySelectorAll('.nexus-net-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    populatePlans(radio.value);
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });

        // Network radio change listener
        networkRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                populatePlans(this.value);
            });
        });

        // Plan change listener
        if (planSelect) {
            planSelect.addEventListener('change', updateSummary);
        }

        // Initial setup on page load
        const initialNet = getActiveNetwork();
        populatePlans(initialNet);

        // Form Submission with progressive enhancement
        if (dataForm) {
            dataForm.addEventListener('submit', function(e) {
                if (!window.jQuery) {
                    // Standard POST submission with formatted HTML response
                    return true;
                }

                e.preventDefault();
                if (loaderOverlay) loaderOverlay.style.display = 'block';
                if (buyBtn) {
                    buyBtn.disabled = true;
                    buyBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Activating Bundle...';
                }

                const formData = $(dataForm).serialize();
                $.ajax({
                    url: dataForm.action,
                    method: 'POST',
                    data: formData,
                    success: function(resp) {
                        if (loaderOverlay) loaderOverlay.style.display = 'none';
                        if (resp && resp.status) {
                            if (window.Swal) {
                                Swal.fire({
                                    title: 'Bundle Activated!',
                                    text: resp.message || 'Data purchase completed successfully.',
                                    icon: 'success',
                                    background: '#0a0a0f',
                                    color: '#fff'
                                });
                            } else {
                                alert(resp.message || 'Data purchase completed successfully.');
                            }
                            if (phoneInput) phoneInput.value = '';
                        } else {
                            const msg = (resp && resp.message) ? resp.message : 'Transaction could not be completed.';
                            if (window.Swal) {
                                Swal.fire({
                                    title: 'Transaction Failed',
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
                            buyBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down mr-2"></i> Activate Bundle';
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
                            buyBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down mr-2"></i> Activate Bundle';
                        }
                    }
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDataBundlePage);
    } else {
        initDataBundlePage();
    }
})();
</script>
@endpush

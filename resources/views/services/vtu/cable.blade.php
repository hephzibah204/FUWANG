@extends('layouts.nexus')

@section('title', 'Cable TV Subscription | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Cable TV Subscription"
        subtitle="Instant renewal and activation for GOtv, DStv, and StarTimes decoders."
        icon="fa-solid fa-tv"
        icon-class="cable-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Activation</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> IUC Pre-Verified</span>
            <span class="badge-accent"><i class="fa-solid fa-clock"></i> 24/7 Gateway</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8 mb-4">
            <div class="panel-card p-4">
                <x-nexus.flash-message />
                
                <form id="cableForm" action="{{ route('services.vtu.cable.buy') }}" method="POST">
                    @csrf

                    <!-- 1. Cable TV Provider Selector -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-600 m-0 text-white">Select TV Provider</label>
                            <span class="text-muted small">Choose your decoder provider</span>
                        </div>
                        <div class="vtu-provider-grid">
                            <!-- GOTV -->
                            <label class="vtu-provider-card" data-provider="gotv">
                                <input type="radio" name="serviceID" value="gotv" checked required>
                                <div class="provider-box provider-gotv">
                                    <div class="provider-logo-wrap">
                                        <img src="{{ asset('images/vtu/gotv.png') }}" alt="GOtv" class="provider-logo-img">
                                    </div>
                                    <span class="provider-name">GOtv</span>
                                    <span class="provider-tag">All Bouquets</span>
                                    <div class="provider-check"><i class="fa-solid fa-circle-check"></i></div>
                                </div>
                            </label>

                            <!-- DSTV -->
                            <label class="vtu-provider-card" data-provider="dstv">
                                <input type="radio" name="serviceID" value="dstv" required>
                                <div class="provider-box provider-dstv">
                                    <div class="provider-logo-wrap">
                                        <img src="{{ asset('images/vtu/dstv.png') }}" alt="DStv" class="provider-logo-img">
                                    </div>
                                    <span class="provider-name">DStv</span>
                                    <span class="provider-tag">Padi to Premium</span>
                                    <div class="provider-check"><i class="fa-solid fa-circle-check"></i></div>
                                </div>
                            </label>

                            <!-- STARTIMES -->
                            <label class="vtu-provider-card" data-provider="startimes">
                                <input type="radio" name="serviceID" value="startimes" required>
                                <div class="provider-box provider-startimes">
                                    <div class="provider-logo-wrap">
                                        <img src="{{ asset('images/vtu/startimes.png') }}" alt="StarTimes" class="provider-logo-img">
                                    </div>
                                    <span class="provider-name">StarTimes</span>
                                    <span class="provider-tag">Nova to Super</span>
                                    <div class="provider-check"><i class="fa-solid fa-circle-check"></i></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Smart Card / IUC Number with Verification -->
                    <div class="form-group mb-4">
                        <label for="smart_card_number" class="font-weight-600 mb-2 text-white">Smart Card / IUC Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-id-card input-icon"></i>
                            <input type="text" name="smart_card_number" id="smart_card_number" class="form-control vtu-input font-mono" placeholder="Enter 10 or 11 digit Smart Card / IUC number" required autocomplete="off">
                            <button type="button" class="btn btn-primary btn-sm verify-btn" id="verify-iuc">
                                <i class="fa-solid fa-magnifying-glass mr-1"></i> Verify Name
                            </button>
                        </div>
                        <div id="verify-loading" class="mt-2 text-muted small" style="display:none;">
                            <i class="fa-solid fa-spinner fa-spin mr-1 text-primary"></i> Querying card holder from provider...
                        </div>
                        <div id="customer-info" class="mt-2 p-3 rounded" style="display:none; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check text-success mr-2" style="font-size: 1.1rem;"></i>
                                <div>
                                    <div class="small text-success font-weight-bold" id="customer-name">Verified Card Holder</div>
                                    <div class="text-muted x-small" id="customer-meta">Details confirmed. Ready to subscribe.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Package Selection -->
                    <div class="form-group mb-4">
                        <label for="variation_code" class="font-weight-600 mb-2 text-white">Select TV Package / Bouquet</label>
                        <div class="select-wrap">
                            <select name="variation_code" id="variation_code" class="form-control vtu-input vtu-select" required>
                                <option value="">-- Choose a package --</option>
                            </select>
                        </div>
                    </div>

                    <!-- 4. Amount -->
                    <div class="form-group mb-4">
                        <label for="amount" class="font-weight-600 mb-2 text-white">Subscription Amount (₦)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-naira-sign input-icon"></i>
                            <input type="number" name="amount" id="amount" class="form-control vtu-input font-weight-bold" placeholder="0.00" readonly required>
                        </div>
                        <small class="text-muted mt-1 d-block">Price is standardized directly from the provider's active tariff.</small>
                    </div>

                    <!-- 5. Recipient Phone Number -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="phone" class="font-weight-600 m-0 text-white">Recipient Phone Number</label>
                            @if(auth()->check() && auth()->user()->phone)
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary text-decoration-none" id="useMyPhoneBtn" data-phone="{{ auth()->user()->phone }}">
                                    <i class="fa-solid fa-circle-user mr-1"></i> Use My Phone
                                </button>
                            @endif
                        </div>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone input-icon"></i>
                            <input type="tel" name="phone" id="phone" class="form-control vtu-input" placeholder="08012345678" maxlength="11" value="{{ auth()->user()->phone ?? '' }}" required autocomplete="tel">
                        </div>
                        <small class="text-muted mt-1 d-block">SMS confirmation and renewal receipt will be delivered here.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 vtu-submit-btn" id="buy-btn">
                        <i class="fa-solid fa-bolt mr-2"></i> Pay Cable TV Subscription
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Broadcasting subscription to satellite gateway...</p>
                </div>
            </div>
        </div>

        <!-- Sidebar / Order Summary Column -->
        <div class="col-lg-4 mb-4">
            <!-- Order Summary Card -->
            <div class="panel-card p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 font-weight-bold mb-0 text-white">Order Summary</h3>
                    <span class="badge badge-success px-2 py-1"><i class="fa-solid fa-shield mr-1"></i> Secure</span>
                </div>

                <div class="vtu-summary-list mb-3">
                    <div class="summary-row">
                        <span class="text-muted">TV Provider</span>
                        <span class="font-weight-600 text-white" id="summaryProvider">GOtv</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Package</span>
                        <span class="font-weight-600 text-white text-right" id="summaryPackage" style="max-width: 170px;">None</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Smart Card</span>
                        <span class="font-mono text-muted" id="summaryIuc">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Gateway Fee</span>
                        <span class="text-success font-weight-600">₦0.00 (Free)</span>
                    </div>
                    <div class="summary-divider my-2"></div>
                    <div class="summary-row summary-total">
                        <span class="font-weight-bold text-white">Total Amount</span>
                        <span class="font-weight-800 text-primary h5 mb-0" id="summaryTotal">₦0.00</span>
                    </div>
                </div>

                <!-- Wallet Check -->
                <div class="vtu-wallet-box p-3 rounded mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">Your Wallet Balance</span>
                        <span class="font-weight-bold text-white" id="walletBalanceVal">₦{{ number_format((float) ($walletBalance ?? 0), 2) }}</span>
                    </div>
                    <div id="walletAlert" class="small mt-2" style="display:none;"></div>
                </div>

                <a href="{{ route('wallet.fund') }}" class="btn btn-outline btn-sm w-100 mb-2">
                    <i class="fa-solid fa-wallet mr-1"></i> Fund Wallet
                </a>
            </div>

            <!-- Quick Navigation -->
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3 text-white">Quick Navigation</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-layer-group mr-2 text-primary"></i> Back to VTU Hub
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-wifi mr-2 text-success"></i> Buy Data Bundles
                    </a>
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-phone mr-2 text-info"></i> Buy Airtime
                    </a>
                    <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-lightbulb mr-2 text-warning"></i> Electricity Tokens
                    </a>
                </div>
            </div>

            <!-- Trust Badge -->
            <div class="panel-card p-3 text-center" style="background: rgba(255,255,255,0.02);">
                <i class="fa-solid fa-tv text-primary mb-2" style="font-size: 1.8rem;"></i>
                <div class="font-weight-bold text-white small">Direct Satellite Renewal</div>
                <div class="text-muted x-small mt-1">Subscriptions are credited directly to Multichoice & StarTimes systems.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .cable-bg { background: rgba(168, 85, 247, 0.12); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.25); }

    /* Provider Grid */
    .vtu-provider-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    @media (max-width: 576px) {
        .vtu-provider-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
    }
    .vtu-provider-card {
        cursor: pointer;
        margin: 0;
        position: relative;
    }
    .vtu-provider-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .provider-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 14px;
        padding: 14px 10px;
        text-align: center;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 110px;
    }
    .provider-box:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.18);
        transform: translateY(-2px);
    }
    .vtu-provider-card input:checked + .provider-box {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.12);
        box-shadow: 0 0 0 1px #3b82f6, 0 8px 20px -4px rgba(59, 130, 246, 0.3);
    }
    .provider-logo-wrap {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        padding: 6px;
        transition: transform 0.2s ease;
    }
    .provider-logo-img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
    }
    .vtu-provider-card input:checked + .provider-box .provider-logo-wrap {
        transform: scale(1.08);
        background: rgba(59, 130, 246, 0.15);
    }
    .provider-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
    }
    .provider-tag {
        font-size: 0.68rem;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 3px;
    }
    .provider-check {
        position: absolute;
        top: 8px;
        right: 8px;
        color: #3b82f6;
        font-size: 0.85rem;
        opacity: 0;
        transform: scale(0.6);
        transition: all 0.2s ease;
    }
    .vtu-provider-card input:checked + .provider-box .provider-check {
        opacity: 1;
        transform: scale(1);
    }

    /* Input & Select Styling (Eliminating stark white inputs) */
    .vtu-input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    .vtu-input:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        color: #ffffff !important;
    }
    .vtu-input::placeholder {
        color: rgba(255, 255, 255, 0.35) !important;
    }
    .vtu-select {
        height: 50px;
        cursor: pointer;
    }
    .vtu-select option {
        background-color: #111827;
        color: #ffffff;
        padding: 10px;
    }

    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrap .input-icon {
        position: absolute;
        left: 16px;
        color: rgba(255, 255, 255, 0.4);
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
    }
    .input-wrap .vtu-input {
        padding-left: 45px !important;
        height: 50px;
        width: 100%;
    }
    .verify-btn {
        position: absolute;
        right: 8px;
        height: 36px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 3;
    }

    /* Summary Styling */
    .vtu-summary-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.88rem;
    }
    .summary-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.08);
    }
    .summary-total {
        font-size: 1rem;
        padding-top: 4px;
    }
    .vtu-wallet-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .vtu-submit-btn {
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        padding: 14px;
        box-shadow: 0 8px 24px -4px rgba(59, 130, 246, 0.4);
    }
</style>
@endpush

@push('scripts')
<script>
    (function() {
        // High-Fidelity packages catalog
        const cablePackages = {
            'gotv': [
                { id: '1', name: 'GOtv Smallie - Monthly', amount: 1575 },
                { id: '2', name: 'GOtv Jinja - Monthly', amount: 3300 },
                { id: '3', name: 'GOtv Jolli - Monthly', amount: 4850 },
                { id: '4', name: 'GOtv Max - Monthly', amount: 7200 },
                { id: '5', name: 'GOtv Supa - Monthly', amount: 9600 },
                { id: '6', name: 'GOtv Supa Plus - Monthly', amount: 15700 }
            ],
            'dstv': [
                { id: '1', name: 'DStv Padi - Monthly', amount: 4400 },
                { id: '2', name: 'DStv Yanga - Monthly', amount: 5100 },
                { id: '3', name: 'DStv Confam - Monthly', amount: 11000 },
                { id: '4', name: 'DStv Compact - Monthly', amount: 19000 },
                { id: '5', name: 'DStv Compact Plus - Monthly', amount: 30000 },
                { id: '6', name: 'DStv Premium - Monthly', amount: 44000 }
            ],
            'startimes': [
                { id: '1', name: 'StarTimes Nova - Monthly', amount: 1700 },
                { id: '2', name: 'StarTimes Basic - Monthly', amount: 3300 },
                { id: '3', name: 'StarTimes Smart - Monthly', amount: 4200 },
                { id: '4', name: 'StarTimes Classic - Monthly', amount: 5000 },
                { id: '5', name: 'StarTimes Super - Monthly', amount: 9000 }
            ]
        };

        const walletBalance = {{ (float) ($walletBalance ?? 0) }};

        function initCableApp() {
            const providerRadios = document.querySelectorAll('input[name="serviceID"]');
            const selectPackage = document.getElementById('variation_code');
            const amountInput = document.getElementById('amount');
            const iucInput = document.getElementById('smart_card_number');
            const verifyBtn = document.getElementById('verify-iuc');
            const verifyLoading = document.getElementById('verify-loading');
            const customerInfo = document.getElementById('customer-info');
            const customerName = document.getElementById('customer-name');
            const customerMeta = document.getElementById('customer-meta');
            const useMyPhoneBtn = document.getElementById('useMyPhoneBtn');
            const phoneInput = document.getElementById('phone');

            const summaryProvider = document.getElementById('summaryProvider');
            const summaryPackage = document.getElementById('summaryPackage');
            const summaryIuc = document.getElementById('summaryIuc');
            const summaryTotal = document.getElementById('summaryTotal');
            const walletAlert = document.getElementById('walletAlert');
            const cableForm = document.getElementById('cableForm');
            const buyBtn = document.getElementById('buy-btn');
            const loaderOverlay = document.getElementById('loaderOverlay');

            function getSelectedProvider() {
                const checked = document.querySelector('input[name="serviceID"]:checked');
                return checked ? checked.value : 'gotv';
            }

            function getProviderLabel(key) {
                switch(key.toLowerCase()) {
                    case 'gotv': return 'GOtv';
                    case 'dstv': return 'DStv';
                    case 'startimes': return 'StarTimes';
                    default: return key.toUpperCase();
                }
            }

            function populatePackages() {
                const prov = getSelectedProvider();
                const packages = cablePackages[prov] || [];

                selectPackage.innerHTML = '<option value="">-- Choose a package --</option>';

                packages.forEach(function(pkg) {
                    const opt = document.createElement('option');
                    opt.value = pkg.id;
                    opt.textContent = pkg.name + ' - ₦' + Number(pkg.amount).toLocaleString();
                    opt.dataset.amount = pkg.amount;
                    opt.dataset.name = pkg.name;
                    selectPackage.appendChild(opt);
                });

                amountInput.value = '';
                updateOrderSummary();
            }

            function updateOrderSummary() {
                const prov = getSelectedProvider();
                if (summaryProvider) summaryProvider.textContent = getProviderLabel(prov);

                const iuc = (iucInput.value || '').trim();
                if (summaryIuc) summaryIuc.textContent = iuc ? iuc : '—';

                const selectedOption = selectPackage.options[selectPackage.selectedIndex];
                const amt = selectedOption && selectedOption.dataset.amount ? parseFloat(selectedOption.dataset.amount) : 0;
                const pkgName = selectedOption && selectedOption.dataset.name ? selectedOption.dataset.name : 'None';

                if (summaryPackage) summaryPackage.textContent = pkgName;
                if (summaryTotal) summaryTotal.textContent = '₦' + amt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                if (walletAlert) {
                    if (amt > 0 && walletBalance < amt) {
                        const diff = amt - walletBalance;
                        walletAlert.style.display = 'block';
                        walletAlert.className = 'small mt-2 text-warning';
                        walletAlert.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i> Balance insufficient. Need ₦' + diff.toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' more.';
                    } else if (amt > 0) {
                        walletAlert.style.display = 'block';
                        walletAlert.className = 'small mt-2 text-success';
                        walletAlert.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i> Balance sufficient. Ready to proceed.';
                    } else {
                        walletAlert.style.display = 'none';
                    }
                }
            }

            // Provider changes
            providerRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    populatePackages();
                    if (customerInfo) customerInfo.style.display = 'none';
                });
            });

            // Package change
            selectPackage.addEventListener('change', function() {
                const opt = selectPackage.options[selectPackage.selectedIndex];
                const amt = opt && opt.dataset.amount ? opt.dataset.amount : '';
                amountInput.value = amt;
                updateOrderSummary();
            });

            // IUC Input change
            iucInput.addEventListener('input', function() {
                updateOrderSummary();
            });

            // "Use My Phone" quick action
            if (useMyPhoneBtn && phoneInput) {
                useMyPhoneBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    phoneInput.value = useMyPhoneBtn.getAttribute('data-phone') || '';
                    phoneInput.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }

            // IUC Verification
            if (verifyBtn) {
                verifyBtn.addEventListener('click', function() {
                    const prov = getSelectedProvider();
                    const iuc = (iucInput.value || '').trim();

                    if (!iuc) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'IUC Number Required',
                                text: 'Please enter your Smart Card or IUC number before verifying.',
                                background: '#0a0a0f',
                                color: '#fff'
                            });
                        } else {
                            alert('Please enter your Smart Card or IUC number before verifying.');
                        }
                        return;
                    }

                    if (verifyLoading) verifyLoading.style.display = 'block';
                    verifyBtn.disabled = true;
                    if (customerInfo) customerInfo.style.display = 'none';

                    fetch("{{ route('services.vtu.cable.validate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            serviceID: prov,
                            smart_card_number: iuc
                        })
                    })
                    .then(response => response.json().then(data => ({ status: response.ok, body: data })))
                    .then(res => {
                        if (verifyLoading) verifyLoading.style.display = 'none';
                        verifyBtn.disabled = false;

                        if (res.status && res.body.status) {
                            const name = (res.body.customer && res.body.customer.name) ? res.body.customer.name : (res.body.name || 'Verified Customer');
                            if (customerName) customerName.textContent = name;
                            if (customerMeta) customerMeta.textContent = 'Account active. Bouquet renewal permitted.';
                            if (customerInfo) customerInfo.style.display = 'block';

                            if (typeof toastr !== 'undefined') {
                                toastr.success('Customer verified: ' + name);
                            }
                        } else {
                            const msg = (res.body && res.body.message) ? res.body.message : 'Could not verify smart card number. Please confirm your IUC and TV provider.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Verification Failed',
                                    text: msg,
                                    background: '#0a0a0f',
                                    color: '#fff'
                                });
                            } else {
                                alert('Verification Failed: ' + msg);
                            }
                        }
                    })
                    .catch(err => {
                        if (verifyLoading) verifyLoading.style.display = 'none';
                        verifyBtn.disabled = false;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Network Notice',
                                text: 'Unable to reach provider verification gateway at this moment. You may proceed with subscription if your card number is correct.',
                                background: '#0a0a0f',
                                color: '#fff'
                            });
                        } else {
                            alert('Verification notice: Gateway unreachable.');
                        }
                    });
                });
            }

            // Form Submit confirmation
            if (cableForm) {
                cableForm.addEventListener('submit', function(e) {
                    if (typeof Swal === 'undefined') {
                        // Native submission fallback
                        if (buyBtn) {
                            buyBtn.disabled = true;
                            buyBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Transmitting...';
                        }
                        if (loaderOverlay) loaderOverlay.style.display = 'block';
                        return true;
                    }

                    e.preventDefault();

                    const selectedOption = selectPackage.options[selectPackage.selectedIndex];
                    const amt = selectedOption && selectedOption.dataset.amount ? parseFloat(selectedOption.dataset.amount) : 0;
                    const pkgName = selectedOption && selectedOption.dataset.name ? selectedOption.dataset.name : 'Selected Bouquet';
                    const prov = getSelectedProvider();

                    Swal.fire({
                        title: 'Confirm Cable TV Payment',
                        html: `
                            <div class="text-left p-3 rounded" style="background: rgba(255,255,255,0.05); font-size: 0.95rem;">
                                <div class="mb-2"><strong>Provider:</strong> ${getProviderLabel(prov)}</div>
                                <div class="mb-2"><strong>Package:</strong> ${pkgName}</div>
                                <div class="mb-2"><strong>Smart Card:</strong> ${iucInput.value}</div>
                                <div class="mb-2"><strong>Amount:</strong> ₦${amt.toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Pay ₦' + amt.toLocaleString('en-US', { minimumFractionDigits: 2 }),
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#6b7280',
                        background: '#0a0a0f',
                        color: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (buyBtn) {
                                buyBtn.disabled = true;
                                buyBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Transmitting...';
                            }
                            if (loaderOverlay) loaderOverlay.style.display = 'block';
                            cableForm.submit();
                        }
                    });
                });
            }

            // Initial Population
            populatePackages();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCableApp);
        } else {
            initCableApp();
        }
    })();
</script>
@endpush
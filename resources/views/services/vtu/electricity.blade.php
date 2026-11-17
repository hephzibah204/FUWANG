@extends('layouts.nexus')

@section('title', 'Electricity Bills & Tokens | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Electricity Bills & Tokens"
        subtitle="Purchase prepaid meter recharge tokens and pay postpaid electricity bills across all Nigerian DISCOs."
        icon="fa-solid fa-lightbulb"
        icon-class="elect-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Token (Prepaid)</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> Meter Pre-Verified</span>
            <span class="badge-accent"><i class="fa-solid fa-clock"></i> 24/7 Processing</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8 mb-4">
            <div class="panel-card p-4">
                <x-nexus.flash-message />

                <form id="electricityForm" action="{{ route('services.vtu.electricity.buy') }}" method="POST">
                    @csrf
                    <input type="hidden" name="validation_token" id="validation_token" value="">
                    <input type="hidden" name="provider_id" id="provider_id" value="">

                    <!-- 1. Select DISCO Provider -->
                    <div class="form-group mb-4">
                        <label for="serviceID" class="font-weight-600 mb-2 text-white d-block">
                            Select Distribution Company (DISCO)
                        </label>
                        <div class="select-wrap">
                            <select name="serviceID" id="serviceID" class="form-control vtu-input vtu-select" required>
                                <option value="">-- Choose your electricity provider --</option>
                                @foreach((config('discos.electricity') ?? [
                                    ['key' => 'ikeja-electric', 'label' => 'Ikeja Electric (IKEDC)'],
                                    ['key' => 'eko-electric', 'label' => 'Eko Electric (EKEDC)'],
                                    ['key' => 'abuja-electric', 'label' => 'Abuja Electric (AEDC)'],
                                    ['key' => 'kano-electric', 'label' => 'Kano Electric (KEDCO)'],
                                    ['key' => 'portharcourt-electric', 'label' => 'Port Harcourt (PHED)'],
                                    ['key' => 'jos-electric', 'label' => 'Jos Electric (JED)'],
                                    ['key' => 'kaduna-electric', 'label' => 'Kaduna Electric (KAEDCO)'],
                                    ['key' => 'enugu-electric', 'label' => 'Enugu Electric (EEDC)'],
                                    ['key' => 'ibadan-electric', 'label' => 'Ibadan Electric (IBEDC)'],
                                    ['key' => 'benin-electric', 'label' => 'Benin Electric (BEDC)'],
                                    ['key' => 'yola-electric', 'label' => 'Yola Electric (YEDC)'],
                                ]) as $d)
                                    <option value="{{ $d['key'] }}">{{ $d['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- 2. Meter Type Selector (Prepaid vs Postpaid) -->
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 text-white d-block">Meter Account Type</label>
                        <div class="meter-type-toggle">
                            <label class="meter-type-option">
                                <input type="radio" name="variation_code" value="prepaid" checked required>
                                <div class="meter-type-box">
                                    <i class="fa-solid fa-receipt mr-2 text-warning"></i>
                                    <span class="font-weight-bold">Prepaid</span>
                                    <span class="meter-type-sub">(Generates 20-digit recharge token)</span>
                                </div>
                            </label>

                            <label class="meter-type-option">
                                <input type="radio" name="variation_code" value="postpaid" required>
                                <div class="meter-type-box">
                                    <i class="fa-solid fa-file-invoice-dollar mr-2 text-info"></i>
                                    <span class="font-weight-bold">Postpaid</span>
                                    <span class="meter-type-sub">(Clears monthly utility bill)</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 3. Meter Number Input with Verification -->
                    <div class="form-group mb-4">
                        <label for="meter_number" class="font-weight-600 mb-2 text-white">Meter / Account Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-bolt input-icon"></i>
                            <input type="text" name="meter_number" id="meter_number" class="form-control vtu-input font-mono" placeholder="Enter 11 or 13-digit meter number" required autocomplete="off">
                            <button type="button" class="btn btn-warning btn-sm verify-btn" id="verify-meter">
                                <i class="fa-solid fa-magnifying-glass mr-1"></i> Verify Meter
                            </button>
                        </div>
                        <div id="verify-loading" class="mt-2 text-muted small" style="display:none;">
                            <i class="fa-solid fa-spinner fa-spin mr-1 text-warning"></i> Validating meter with DISCO database...
                        </div>

                        <!-- Verified Customer Result Card -->
                        <div id="meterResult" class="mt-3 p-3 rounded" style="display:none; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <div class="d-flex align-items-start">
                                <i class="fa-solid fa-circle-check text-success mr-2 mt-1" style="font-size: 1.1rem;"></i>
                                <div class="flex-grow-1">
                                    <div class="text-white font-weight-bold" id="meterName">Verified Customer</div>
                                    <div class="text-muted small mt-1" id="meterAddress">Address confirmed</div>
                                    <span class="badge badge-success mt-2"><i class="fa-solid fa-check mr-1"></i> Meter Ready for Payment</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Amount Input & Quick Chips -->
                    <div class="form-group mb-4">
                        <label for="amount" class="font-weight-600 mb-2 text-white">Recharge Amount (₦)</label>
                        <div class="input-wrap mb-2">
                            <i class="fa-solid fa-naira-sign input-icon"></i>
                            <input type="number" name="amount" id="amount" class="form-control vtu-input font-weight-bold" placeholder="Min ₦500" min="500" step="100" required>
                        </div>
                        <div class="d-flex flex-wrap vtu-quick-chips" style="gap: 8px;">
                            <button type="button" class="vtu-chip" data-amount="1000">₦1,000</button>
                            <button type="button" class="vtu-chip" data-amount="2000">₦2,000</button>
                            <button type="button" class="vtu-chip" data-amount="3000">₦3,000</button>
                            <button type="button" class="vtu-chip" data-amount="5000">₦5,000</button>
                            <button type="button" class="vtu-chip" data-amount="10000">₦10,000</button>
                            <button type="button" class="vtu-chip" data-amount="20000">₦20,000</button>
                        </div>
                        <small class="text-muted mt-2 d-block">Prepaid meters have a minimum recharge value of ₦500.</small>
                    </div>

                    <!-- 5. Recipient Phone Number -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="phone" class="font-weight-600 m-0 text-white">Recipient Phone Number</label>
                            @if(auth()->check() && auth()->user()->phone)
                                <button type="button" class="btn btn-link btn-sm p-0 text-warning text-decoration-none" id="useMyPhoneBtn" data-phone="{{ auth()->user()->phone }}">
                                    <i class="fa-solid fa-circle-user mr-1"></i> Use My Phone
                                </button>
                            @endif
                        </div>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone input-icon"></i>
                            <input type="tel" name="phone" id="phone" class="form-control vtu-input" placeholder="08012345678" maxlength="11" value="{{ auth()->user()->phone ?? '' }}" required autocomplete="tel">
                        </div>
                        <small class="text-muted mt-1 d-block">The 20-digit prepaid token code and receipt will be delivered via SMS to this number.</small>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100 vtu-submit-btn text-dark font-weight-bold" id="buy-btn">
                        <i class="fa-solid fa-bolt mr-2"></i> Pay Electricity Bill
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2 text-muted">Generating electricity token through Network Band gateway...</p>
                </div>
            </div>
        </div>

        <!-- Sidebar / Order Summary Column -->
        <div class="col-lg-4 mb-4">
            <!-- Order Summary Card -->
            <div class="panel-card p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 font-weight-bold mb-0 text-white">Payment Summary</h3>
                    <span class="badge badge-warning px-2 py-1 text-dark"><i class="fa-solid fa-bolt mr-1"></i> Instant</span>
                </div>

                <div class="vtu-summary-list mb-3">
                    <div class="summary-row">
                        <span class="text-muted">DISCO</span>
                        <span class="font-weight-600 text-white text-right" id="summaryDisco" style="max-width: 170px;">None</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Type</span>
                        <span class="font-weight-600 text-white" id="summaryType">Prepaid</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Meter Number</span>
                        <span class="font-mono text-muted" id="summaryMeter">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Verified Customer</span>
                        <span class="font-weight-600 text-white text-right" id="summaryCustomer" style="max-width: 160px;">Unverified</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Convenience Fee</span>
                        <span class="text-success font-weight-600">₦0.00 (Free)</span>
                    </div>
                    <div class="summary-divider my-2"></div>
                    <div class="summary-row summary-total">
                        <span class="font-weight-bold text-white">Total Debit</span>
                        <span class="font-weight-800 text-warning h5 mb-0" id="summaryTotal">₦0.00</span>
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
                    <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-tv mr-2 text-purple" style="color: #c084fc;"></i> Cable TV Renewal
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-wifi mr-2 text-success"></i> Buy Data Bundles
                    </a>
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-phone mr-2 text-info"></i> Buy Airtime
                    </a>
                </div>
            </div>

            <!-- Token Delivery Notice -->
            <div class="panel-card p-3 text-center" style="background: rgba(255,255,255,0.02);">
                <i class="fa-solid fa-shield-halved text-warning mb-2" style="font-size: 1.8rem;"></i>
                <div class="font-weight-bold text-white small">Automated Token Dispense</div>
                <div class="text-muted x-small mt-1">Prepaid tokens generate in ~2.4 seconds and are permanently stored in your receipt history.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .elect-bg { background: rgba(234, 179, 8, 0.12); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.25); }

    /* Meter Type Toggle Cards */
    .meter-type-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .meter-type-option {
        cursor: pointer;
        margin: 0;
        position: relative;
    }
    .meter-type-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .meter-type-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 12px;
        padding: 12px 14px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }
    .meter-type-box:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.18);
    }
    .meter-type-option input:checked + .meter-type-box {
        border-color: #eab308;
        background: rgba(234, 179, 8, 0.10);
        box-shadow: 0 0 0 1px #eab308;
    }
    .meter-type-sub {
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 3px;
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
        border-color: #eab308 !important;
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.25) !important;
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
        font-weight: 700;
        z-index: 3;
    }

    /* Quick Chips */
    .vtu-chip {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.10);
        color: rgba(255, 255, 255, 0.85);
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .vtu-chip:hover {
        background: rgba(234, 179, 8, 0.15);
        border-color: #eab308;
        color: #facc15;
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
        font-size: 1rem;
        padding: 14px;
        box-shadow: 0 8px 24px -4px rgba(234, 179, 8, 0.3);
    }
</style>
@endpush

@push('scripts')
<script>
    (function() {
        const walletBalance = {{ (float) ($walletBalance ?? 0) }};

        function initElectricityApp() {
            const discoSelect = document.getElementById('serviceID');
            const meterRadios = document.querySelectorAll('input[name="variation_code"]');
            const meterInput = document.getElementById('meter_number');
            const amountInput = document.getElementById('amount');
            const phoneInput = document.getElementById('phone');
            const useMyPhoneBtn = document.getElementById('useMyPhoneBtn');
            const verifyBtn = document.getElementById('verify-meter');
            const verifyLoading = document.getElementById('verify-loading');
            const meterResult = document.getElementById('meterResult');
            const meterName = document.getElementById('meterName');
            const meterAddress = document.getElementById('meterAddress');
            const validationTokenInput = document.getElementById('validation_token');

            const summaryDisco = document.getElementById('summaryDisco');
            const summaryType = document.getElementById('summaryType');
            const summaryMeter = document.getElementById('summaryMeter');
            const summaryCustomer = document.getElementById('summaryCustomer');
            const summaryTotal = document.getElementById('summaryTotal');
            const walletAlert = document.getElementById('walletAlert');
            const chips = document.querySelectorAll('.vtu-chip');
            const electricityForm = document.getElementById('electricityForm');
            const buyBtn = document.getElementById('buy-btn');
            const loaderOverlay = document.getElementById('loaderOverlay');

            function getSelectedVariation() {
                const checked = document.querySelector('input[name="variation_code"]:checked');
                return checked ? checked.value : 'prepaid';
            }

            function invalidateValidation() {
                if (validationTokenInput) validationTokenInput.value = '';
                if (meterResult) meterResult.style.display = 'none';
                if (summaryCustomer) {
                    summaryCustomer.textContent = 'Unverified';
                    summaryCustomer.className = 'font-weight-600 text-muted';
                }
            }

            function updateSummary() {
                const discoText = discoSelect.options[discoSelect.selectedIndex]?.text || 'None';
                if (summaryDisco) summaryDisco.textContent = discoSelect.value ? discoText : 'None';

                const vType = getSelectedVariation();
                if (summaryType) summaryType.textContent = vType === 'prepaid' ? 'Prepaid Token' : 'Postpaid Bill';

                const mNum = (meterInput.value || '').trim();
                if (summaryMeter) summaryMeter.textContent = mNum ? mNum : '—';

                const amt = parseFloat(amountInput.value) || 0;
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

            // Quick Chips
            chips.forEach(function(chip) {
                chip.addEventListener('click', function(e) {
                    e.preventDefault();
                    const amt = this.getAttribute('data-amount');
                    amountInput.value = amt;
                    updateSummary();
                });
            });

            // Input Listeners
            discoSelect.addEventListener('change', function() {
                invalidateValidation();
                updateSummary();
            });

            meterRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    invalidateValidation();
                    updateSummary();
                });
            });

            meterInput.addEventListener('input', function() {
                invalidateValidation();
                updateSummary();
            });

            amountInput.addEventListener('input', updateSummary);

            // "Use My Phone" quick action
            if (useMyPhoneBtn && phoneInput) {
                useMyPhoneBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    phoneInput.value = useMyPhoneBtn.getAttribute('data-phone') || '';
                    phoneInput.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }

            // Meter Verification
            if (verifyBtn) {
                verifyBtn.addEventListener('click', function() {
                    const disco = discoSelect.value;
                    const variation = getSelectedVariation();
                    const meter = (meterInput.value || '').trim();

                    if (!disco) {
                        alertMessage('Please select your electricity distribution company (DISCO) first.', 'warning', 'DISCO Required');
                        return;
                    }
                    if (!meter) {
                        alertMessage('Please enter your meter or account number before verifying.', 'warning', 'Meter Number Required');
                        return;
                    }

                    if (verifyLoading) verifyLoading.style.display = 'block';
                    verifyBtn.disabled = true;
                    invalidateValidation();

                    fetch("{{ route('services.vtu.electricity.validate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            serviceID: disco,
                            variation_code: variation,
                            meter_number: meter
                        })
                    })
                    .then(response => response.json().then(data => ({ status: response.ok, body: data })))
                    .then(res => {
                        if (verifyLoading) verifyLoading.style.display = 'none';
                        verifyBtn.disabled = false;

                        if (res.status && res.body.status) {
                            if (validationTokenInput) validationTokenInput.value = res.body.validation_token || 'VALIDATED';
                            const name = (res.body.customer && res.body.customer.name) ? res.body.customer.name : (res.body.name || 'Verified Customer');
                            const addr = (res.body.customer && res.body.customer.address) ? res.body.customer.address : (res.body.address || '');

                            if (meterName) meterName.textContent = name;
                            if (meterAddress) meterAddress.textContent = addr ? ('Address: ' + addr) : 'Account verified with DISCO';
                            if (meterResult) meterResult.style.display = 'block';

                            if (summaryCustomer) {
                                summaryCustomer.textContent = name;
                                summaryCustomer.className = 'font-weight-600 text-success';
                            }

                            if (typeof toastr !== 'undefined') {
                                toastr.success('Meter verified: ' + name);
                            }
                        } else {
                            invalidateValidation();
                            const msg = (res.body && res.body.message) ? res.body.message : 'Unable to validate meter. Please check your meter number and selected DISCO.';
                            alertMessage(msg, 'error', 'Verification Failed');
                        }
                    })
                    .catch(err => {
                        if (verifyLoading) verifyLoading.style.display = 'none';
                        verifyBtn.disabled = false;
                        invalidateValidation();
                        alertMessage('Unable to reach DISCO verification gateway. Please check your internet connection and try again.', 'error', 'Gateway Notice');
                    });
                });
            }

            function alertMessage(msg, type, title) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type,
                        title: title || 'Notice',
                        text: msg,
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                } else {
                    alert((title ? title + ': ' : '') + msg);
                }
            }

            // Form Submit confirmation
            if (electricityForm) {
                electricityForm.addEventListener('submit', function(e) {
                    const token = validationTokenInput ? validationTokenInput.value : '';
                    if (!token) {
                        e.preventDefault();
                        alertMessage('Please verify your meter number before proceeding to payment.', 'warning', 'Verification Required');
                        return false;
                    }

                    if (typeof Swal === 'undefined') {
                        if (buyBtn) {
                            buyBtn.disabled = true;
                            buyBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Transmitting...';
                        }
                        if (loaderOverlay) loaderOverlay.style.display = 'block';
                        return true;
                    }

                    e.preventDefault();

                    const amt = parseFloat(amountInput.value) || 0;
                    const discoText = discoSelect.options[discoSelect.selectedIndex]?.text || '';
                    const vType = getSelectedVariation();

                    Swal.fire({
                        title: 'Confirm Electricity Payment',
                        html: `
                            <div class="text-left p-3 rounded" style="background: rgba(255,255,255,0.05); font-size: 0.95rem;">
                                <div class="mb-2"><strong>DISCO:</strong> ${discoText}</div>
                                <div class="mb-2"><strong>Type:</strong> ${vType.toUpperCase()}</div>
                                <div class="mb-2"><strong>Meter Number:</strong> ${meterInput.value}</div>
                                <div class="mb-2"><strong>Customer:</strong> ${meterName ? meterName.textContent : ''}</div>
                                <div class="mb-2"><strong>Amount:</strong> ₦${amt.toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Pay ₦' + amt.toLocaleString('en-US', { minimumFractionDigits: 2 }),
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#eab308',
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
                            electricityForm.submit();
                        }
                    });
                });
            }

            updateSummary();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initElectricityApp);
        } else {
            initElectricityApp();
        }
    })();
</script>
@endpush
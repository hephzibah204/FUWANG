@extends('layouts.nexus')

@section('title', 'NIN Verification Suite | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="NIN Verification Suite"
        subtitle="Direct NIMC access for real-time identity validation and record retrieval."
        icon="fa-regular fa-id-card"
        icon-class="nin-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> NIMC Certified</span>
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> < 3s Response</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-12">
            <!-- Main Tabs: Verify | Vault -->
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Verify NIN</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Verification Vault ({{ $myResults->count() }})</button>
            </div>

            <!-- ═══════════════ VERIFY PANEL ═══════════════ -->
            <div id="panel-verify" class="main-panel active">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="panel-card p-4 mb-4" id="searchPanel">
                            <!-- Lookup Mode Tabs (NIN / Phone / Tracking ID / Demographic) -->
                            <div class="nin-tabs mb-4">
                                <button class="nin-tab active" onclick="switchMode('nin', this)">Standard NIN Lookup</button>
                                <button class="nin-tab" onclick="switchMode('selfie', this)">Face (Selfie)</button>
                                <button class="nin-tab" onclick="switchMode('phone', this)">Phone Lookup</button>
                                <button class="nin-tab" onclick="switchMode('tracking', this)">Tracking ID</button>
                                <button class="nin-tab" onclick="switchMode('demographic', this)">Demographic Search</button>
                                <button class="nin-tab" onclick="switchMode('share_code', this)">Share Code</button>
                                <button class="nin-tab" onclick="switchMode('requery', this)">Requery</button>
                            </div>

                            <div class="consent-box mb-4">
                                <i class="fa-solid fa-user-shield"></i>
                                <span>By submitting, you confirm you have obtained the subject's explicit consent per NDPR guidelines. Queries are logged for compliance.</span>
                            </div>

                            <form id="verifyForm" action="{{ route('services.nin.verify') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="modeInput" name="mode" value="nin">

                                <!-- NIN / Phone / Tracking ID Input (hidden for Demographic) -->
                                <div class="form-group mb-4" id="numberGroup">
                                    <label for="number" class="font-weight-600 mb-2" id="number-label">National Identification Number (NIN)</label>
                                    <div class="input-wrap">
                                        <i class="fa-regular fa-id-card" id="number-icon"></i>
                                        <input type="text" id="number" name="number" class="form-control" placeholder="Enter 11-digit NIN" maxlength="25" required>
                                    </div>
                                    <p class="small text-muted mt-2">Verification Fee: <span class="text-white font-weight-bold" id="priceDisplay">₦{{ number_format($prices['nin'], 2) }}</span></p>
                                </div>

                                <div class="form-group mb-4" id="selfieGroup" style="display:none;">
                                    <label for="selfie" class="font-weight-600 mb-2 small text-muted">Selfie Image</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-camera"></i>
                                        <input type="file" id="selfie" name="selfie" class="form-control" accept="image/*" capture="user">
                                    </div>
                                    <div class="mt-3" id="selfiePreviewWrap" style="display:none;">
                                        <img id="selfiePreview" src="" alt="Selfie preview" style="max-width: 160px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.15);">
                                    </div>
                                </div>

                                <div class="form-group mb-4" id="shareCodeGroup" style="display:none;">
                                    <label for="share_code" class="font-weight-600 mb-2" id="share-code-label">Share Code</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-key"></i>
                                        <input type="text" id="share_code" name="share_code" class="form-control" placeholder="Enter 6-character share code" maxlength="64">
                                    </div>
                                </div>

                                <div class="form-group mb-4" id="requeryGroup" style="display:none;">
                                    <label for="reference_id" class="font-weight-600 mb-2">Reference ID</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-rotate"></i>
                                        <input type="text" id="reference_id" name="reference_id" class="form-control" placeholder="Enter reference_id from a previous request" maxlength="120">
                                    </div>
                                </div>

                                <!-- Demographic-only fields -->
                                <div id="identityFields" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-3 mb-4">
                                            <label for="firstname" class="font-weight-600 mb-2 small text-muted">First Name</label>
                                            <div class="input-wrap">
                                                <i class="fa-solid fa-user"></i>
                                                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="JOHN">
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-4">
                                            <label for="lastname" class="font-weight-600 mb-2 small text-muted">Last Name</label>
                                            <div class="input-wrap">
                                                <i class="fa-solid fa-user"></i>
                                                <input type="text" id="lastname" name="lastname" class="form-control" placeholder="DOE">
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-4">
                                            <label for="gender" class="font-weight-600 mb-2 small text-muted">Gender</label>
                                            <div class="input-wrap">
                                                <i class="fa-solid fa-venus-mars"></i>
                                                <select id="gender" name="gender" class="form-control">
                                                    <option value="">Select</option>
                                                    <option value="M">Male</option>
                                                    <option value="F">Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-4">
                                            <label for="dob" class="font-weight-600 mb-2 small text-muted">Date of Birth</label>
                                            <div class="input-wrap">
                                                <i class="fa-solid fa-calendar"></i>
                                                <input type="text" id="dob" name="dob" class="form-control" placeholder="DD-MM-YYYY">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Output Type -->
                                <div class="form-group mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Output Type</label>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="ot_info" name="output_type" value="info_page" class="custom-control-input" checked>
                                            <label class="custom-control-label" for="ot_info">Information Page</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="ot_standard" name="output_type" value="standard_slip" class="custom-control-input">
                                            <label class="custom-control-label" for="ot_standard">Standard Slip (PDF)</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="ot_regular" name="output_type" value="regular_slip" class="custom-control-input">
                                            <label class="custom-control-label" for="ot_regular">Regular Slip (PDF)</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="ot_premium" name="output_type" value="premium_slip" class="custom-control-input">
                                            <label class="custom-control-label" for="ot_premium">Premium Slip (PDF)</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="ot_vnin" name="output_type" value="vnin_slip" class="custom-control-input">
                                            <label class="custom-control-label" for="ot_vnin">vNIN Slip (PDF)</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Purpose -->
                                <div class="form-group mb-4">
                                    <label for="purpose" class="font-weight-600 mb-2 small text-muted">Purpose of Verification</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                        <select id="purpose" name="purpose" class="form-control">
                                            <option>KYC / Customer Onboarding</option>
                                            <option>Employment Background Check</option>
                                            <option>Financial Transaction Compliance</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Provider / Advanced Routing (shown when multiple providers available) -->
                                @if(isset($ninProviders) && $ninProviders->count() >= 1)
                                    <div class="form-group mb-4" id="providerSelectionGroup">
                                        <label for="api_provider_id" class="font-weight-600 mb-2" id="provider-label">Select API Provider</label>
                                        <div class="input-wrap">
                                            <i class="fa-solid fa-server"></i>
                                            <select id="api_provider_id" name="api_provider_id" class="form-control" required onchange="updateVerificationTypes()">
                                                <option value="">-- Choose a Provider --</option>
                                                @foreach($ninProviders as $provider)
                                                    <option value="{{ $provider->id }}" data-types="{{ json_encode($provider->verificationTypes->where('status', true)) }}">{{ $provider->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <p class="small text-muted mt-2 mb-0" id="provider-hint"><i class="fa-solid fa-circle-info mr-1"></i> Please select the API provider you want to use for this verification.</p>
                                    </div>
                                    
                                    <div class="alert alert-warning py-2 px-3 small" id="noProviderWarning" style="display: none;">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> No active provider supports the selected verification mode.
                                    </div>
                                    
                                    <div class="form-group mb-4" id="verificationTypeGroup" style="display: none;">
                                        <label for="verification_type" class="font-weight-600 mb-2 small text-muted">Verification Type</label>
                                        <select id="verification_type" name="verification_type" class="form-control">
                                            <!-- Populated dynamically via JS -->
                                        </select>
                                    </div>
                                @endif

                                <button type="submit" class="btn btn-primary btn-lg w-100" id="verify-btn">
                                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Verify Identity
                                </button>
                            </form>

                            <!-- Result Section with Skeleton Loader -->
                            <div id="resultContainer" class="mt-4" style="display: none;">
                                <div id="skeletonLoader" style="display: none;">
                                    <div class="p-4 text-center">
                                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                                        <p class="text-muted">Accessing NIMC Secure Gateway...</p>
                                    </div>
                                </div>

                                <div id="resultContent" class="fade-in">
                                    <!-- Result data injected here by JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="panel-card p-4 mb-4">
                            <h3 class="h6 font-weight-bold mb-3">Verification Tips</h3>
                            <ul class="list-unstyled small text-muted">
                                <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Ensure the 11 digits are typed correctly.</li>
                                <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Only verified records yield full data sets.</li>
                                <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Your account will be charged only on success.</li>
                                <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Failed lookups are automatically refunded.</li>
                            </ul>
                        </div>

                        <div class="panel-card p-4 mb-4">
                            <h3 class="h6 font-weight-bold mb-3">Lookup Modes</h3>
                            <div class="mode-info mb-3">
                                <strong class="text-white d-block mb-1"><i class="fa-solid fa-hashtag mr-1 text-primary"></i> NIN Lookup</strong>
                                <span class="small text-muted">Verify directly using the 11-digit NIN number.</span>
                            </div>
                            <div class="mode-info mb-3">
                                <strong class="text-white d-block mb-1"><i class="fa-solid fa-phone mr-1 text-success"></i> Phone Lookup</strong>
                                <span class="small text-muted">Look up NIN using the registered phone number — ₦{{ number_format($prices['phone'], 2) }}</span>
                            </div>
                            <div class="mode-info mb-3">
                                <strong class="text-white d-block mb-1"><i class="fa-solid fa-fingerprint mr-1 text-warning"></i> Tracking ID</strong>
                                <span class="small text-muted">Retrieve NIN details via NIMC tracking ID — ₦{{ number_format($prices['tracking'], 2) }}</span>
                            </div>
                        </div>

                        <div class="kyc-banner">
                            <i class="fa-solid fa-bolt"></i>
                            <div class="kyc-text">
                                <strong>Developer APIs</strong>
                                <p>Integrate NIN verification into your own app.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════ VAULT PANEL ═══════════════ -->
            <div id="panel-vault" class="main-panel" style="display: none;">
                <div class="panel-card p-4">
                    <h3 class="h6 font-weight-bold mb-4">NIN Verification History</h3>
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Name</th>
                                    <th>Identifier</th>
                                    <th>Provider</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myResults as $res)
                                    <tr>
                                        <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                        <td>{{ $res->response_data['firstname'] ?? 'N/A' }} {{ $res->response_data['lastname'] ?? $res->response_data['surname'] ?? '' }}</td>
                                        <td>{{ $res->identifier }}</td>
                                        <td><span class="badge badge-outline-primary">{{ $res->provider_name }}</span></td>
                                        <td>{{ $res->created_at->format('M d, Y') }}</td>
                                        <td class="text-right">
                                            <button class="btn btn-xs btn-outline-primary" onclick='viewVaultResult(@json($res->response_data))'>
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            @if($res->id)
                                                <a href="{{ route('services.verification.report', $res->id) }}" class="btn btn-xs btn-outline-light ml-1">
                                                    <i class="fa fa-file-pdf"></i> PDF
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">No records found in vault. Your completed verifications will appear here.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ── Service Header ── */
    .service-header-card { background: rgba(255,255,255,0.03); border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .nin-bg { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .sh-text h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
    .sh-text p { margin: 4px 0 0; color: var(--clr-text-muted); font-size: 0.95rem; }
    .sh-badges { gap: 10px; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); }

    /* ── Main Tabs (Verify / Vault) ── */
    .tab-strip { display: flex; gap: 0; border-bottom: 2px solid rgba(255,255,255,0.05); margin-bottom: 20px; }
    .s-tab { padding: 12px 25px; background: none; border: none; color: var(--clr-text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.3s; }
    .s-tab.active { color: #3b82f6; border-bottom-color: #3b82f6; }
    .main-panel { display: none; }
    .main-panel.active { display: block; }

    /* ── Lookup Mode Tabs (NIN / Phone / Tracking ID) ── */
    .nin-tabs { display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; overflow-x: auto; }
    .nin-tab { background: none; border: none; color: var(--clr-text-muted); font-size: 0.85rem; font-weight: 600; padding: 8px 0; border-bottom: 2px solid transparent; white-space: nowrap; transition: all 0.2s; cursor: pointer; }
    .nin-tab.active { color: var(--clr-primary); border-bottom-color: var(--clr-primary); }
    .nin-tab:disabled { opacity: 0.4; cursor: not-allowed; }

    /* ── Consent / Form ── */
    .consent-box { background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; padding: 15px; display: flex; gap: 12px; font-size: 0.85rem; color: var(--clr-text-muted); }
    .consent-box i { color: #3b82f6; font-size: 1.2rem; }
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 20px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); z-index: 1; }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }

    /* ── Result Card ── */
    .result-card-nexus { background: rgba(255,255,255,0.02); border: var(--border-glass); border-radius: 18px; padding: 25px; }
    .result-avatar { width: 100px; height: 100px; border-radius: 12px; object-fit: cover; border: 2px solid var(--clr-primary); }
    .result-grid-nexus { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-top: 20px; }
    .rg-cell { background: rgba(255,255,255,0.03); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); }
    .rg-cell span { display: block; font-size: 0.7rem; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .rg-cell strong { font-size: 0.9rem; color: #fff; }
    .badge-outline-primary { border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6; background: transparent; font-size: 0.7rem; padding: 3px 8px; border-radius: 6px; }
    .mode-info { padding: 10px 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; }
</style>
@endpush

@push('scripts')
<script>
    const prices = @json($prices);
    const providerModes = @json($providerModes ?? []);
    let currentMode = 'nin';
    let currentPrice = prices.nin;

    function filterProvidersByMode(mode) {
        const providerSelect = document.getElementById('api_provider_id');
        const providerGroup = document.getElementById('providerSelectionGroup');
        const noProviderWarning = document.getElementById('noProviderWarning');
        const submitBtn = document.getElementById('submitBtn');
        
        if (!providerSelect) return;

        let availableCount = 0;
        
        // Reset selection
        providerSelect.value = '';
        
        Array.from(providerSelect.options).forEach(option => {
            if (option.value === '') return; // Skip placeholder
            
            const providerId = option.value;
            const supportedModes = providerModes[providerId] || [];
            
            if (supportedModes.includes(mode)) {
                option.style.display = '';
                availableCount++;
            } else {
                option.style.display = 'none';
            }
        });

        if (availableCount === 0) {
            providerGroup.style.display = 'none';
            noProviderWarning.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-lock mr-2"></i> Unavailable';
        } else {
            providerGroup.style.display = 'block';
            noProviderWarning.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa fa-search mr-2"></i> Verify Now';
            
            // Auto-select if only one provider is available
            if (availableCount === 1) {
                const availableOption = Array.from(providerSelect.options).find(opt => opt.style.display !== 'none' && opt.value !== '');
                if (availableOption) {
                    providerSelect.value = availableOption.value;
                }
            }
        }
        
        // Trigger verification types update based on new selection (or reset)
        updateVerificationTypes();
    }

    function switchMode(mode, btn) {
        currentMode = mode;
        currentPrice = prices[mode] || prices.nin;
        document.getElementById('modeInput').value = mode;
        document.querySelectorAll('.nin-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        const label = document.getElementById('number-label');
        const input = document.getElementById('number');
        const icon = document.getElementById('number-icon');
        const identityFields = document.getElementById('identityFields');
        const numberGroup = document.getElementById('numberGroup');
        const selfieGroup = document.getElementById('selfieGroup');
        const selfieInput = document.getElementById('selfie');
        const shareCodeGroup = document.getElementById('shareCodeGroup');
        const shareCodeInput = document.getElementById('share_code');
        const requeryGroup = document.getElementById('requeryGroup');
        const requeryInput = document.getElementById('reference_id');
        if (mode === 'phone') {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'Phone Number';
            input.placeholder = 'Enter registered phone number';
            input.maxLength = 11;
            icon.className = 'fa-solid fa-phone';
            identityFields.style.display = 'none';
        } else if (mode === 'tracking') {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'Tracking ID';
            input.placeholder = 'Enter NIMC tracking ID';
            input.maxLength = 25;
            icon.className = 'fa-solid fa-fingerprint';
            identityFields.style.display = 'none';
        } else if (mode === 'selfie') {
            numberGroup.style.display = '';
            selfieGroup.style.display = '';
            selfieInput.required = true;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'National Identification Number (NIN)';
            input.placeholder = 'Enter 11-digit NIN';
            input.maxLength = 11;
            icon.className = 'fa-regular fa-id-card';
            identityFields.style.display = 'none';
        } else if (mode === 'demographic') {
            numberGroup.style.display = 'none';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            identityFields.style.display = '';
        } else if (mode === 'share_code') {
            numberGroup.style.display = 'none';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = '';
            shareCodeInput.required = true;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            identityFields.style.display = 'none';
        } else if (mode === 'requery') {
            numberGroup.style.display = 'none';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            requeryGroup.style.display = '';
            requeryInput.required = true;
            identityFields.style.display = 'none';
        } else {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'National Identification Number (NIN)';
            input.placeholder = 'Enter 11-digit NIN';
            input.maxLength = 11;
            icon.className = 'fa-regular fa-id-card';
            identityFields.style.display = 'none';
        }
        input.required = !['demographic', 'share_code', 'requery'].includes(mode);
        document.getElementById('priceDisplay').textContent = '₦' + currentPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Filter providers based on newly selected mode
        filterProvidersByMode(mode);
    }

    function switchMainPanel(panel, btn) {
        document.querySelectorAll('.main-panel').forEach(p => { p.style.display = 'none'; p.classList.remove('active'); });
        document.getElementById('panel-' + panel).style.display = 'block';
        document.getElementById('panel-' + panel).classList.add('active');
        document.querySelectorAll('.s-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
    }

    function updateVerificationTypes() {
        const providerSelect = document.getElementById('api_provider_id');
        const typeGroup = document.getElementById('verificationTypeGroup');
        const typeSelect = document.getElementById('verification_type');
        
        if (!providerSelect || !typeSelect || !typeGroup) return;

        const selectedOption = providerSelect.options[providerSelect.selectedIndex];
        const typesData = selectedOption.getAttribute('data-types');
        
        typeSelect.innerHTML = '';
        
        if (!typesData || typesData === '[]' || typesData === '') {
            typeGroup.style.display = 'none';
            typeSelect.innerHTML = '<option value="">Standard</option>';
            return;
        }

        try {
            const types = JSON.parse(typesData);
            const typesArray = Object.values(types);
            
            if (typesArray.length > 0) {
                typeGroup.style.display = 'block';
                typesArray.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.type_key;
                    // Format price if available
                    const price = type.price ? ` - ₦${Number(type.price).toLocaleString()}` : '';
                    option.textContent = type.name + price;
                    typeSelect.appendChild(option);
                });
            } else {
                typeGroup.style.display = 'none';
                typeSelect.innerHTML = '<option value="">Standard</option>';
            }
        } catch (e) {
            console.error('Error parsing provider types', e);
            typeGroup.style.display = 'none';
        }
    }

    function viewVaultResult(data) {
        switchMainPanel('verify', document.querySelector('.s-tab'));
        displayResult(data);
    }

    function displayResultWrapper(res) {
        const data = res.data || {};
        const photo = data.photo || data.image;
        const photoSrc = photo
            ? (photo.startsWith('http') || photo.startsWith('data:') ? photo : 'data:image/jpeg;base64,' + photo)
            : 'https://ui-avatars.com/api/?name=' + (data.firstname || 'N') + '+' + (data.lastname || data.surname || 'A') + '&background=3b82f6&color=fff';
        const name = [data.firstname, data.middlename, data.lastname || data.surname].filter(Boolean).join(' ') || 'N/A';
        const nin = data.nin || data.number || 'N/A';
        let cells = '';
        const addCell = (label, val) => {
            if (val && val !== 'N/A' && val !== '' && val !== 'null') {
                cells += `<div class="rg-cell"><span>${label}</span><strong>${val}</strong></div>`;
            }
        };
        addCell('Gender', data.gender);
        addCell('Birth Date', data.birthdate || data.dob);
        addCell('Phone', data.telephoneno || data.phone);
        addCell('Middle Name', data.middlename);
        addCell('State of Origin', data.self_origin_state || data.state);
        addCell('LGA of Origin', data.self_origin_lga || data.lga);
        addCell('Residence State', data.residence_state);
        addCell('Residence LGA', data.residence_lga);
        addCell('Marital Status', data.maritalstatus);
        addCell('Employment Status', data.emplymentstatus || data.employment_status);
        addCell('Religion', data.religion);
        addCell('Nationality', data.nationality);
        let actionButtons = `
            <button class="btn btn-outline flex-grow-1" onclick="window.print()">
                <i class="fa-solid fa-print mr-2"></i> Print
            </button>
            <button class="btn btn-primary flex-grow-1" onclick="window.location.reload()">
                <i class="fa-solid fa-magnifying-glass mr-2"></i> New Search
            </button>`;
        if (res.slip_url) {
            actionButtons = `<a class="btn btn-outline-light flex-grow-1" href="${res.slip_url}"><i class="fa fa-file-pdf mr-2"></i> Download Slip</a>` + actionButtons;
        }
        if (res.report_url) {
            actionButtons = `<a class="btn btn-outline-light flex-grow-1" href="${res.report_url}"><i class="fa fa-file-pdf mr-2"></i> Download Report</a>` + actionButtons;
        }
        const html = `
            <div class="result-card-nexus animate__animated animate__fadeIn">
                <div class="d-flex align-items-center gap-4 mb-4">
                    <img src="${photoSrc}" class="result-avatar" onerror="this.src='https://ui-avatars.com/api/?name=${data.firstname || 'N'}+${data.lastname || 'A'}&background=3b82f6&color=fff'">
                    <div>
                        <div class="badge-accent bg-success-light text-success mb-2 border-0"><i class="fa-solid fa-circle-check"></i> Identity Authenticated</div>
                        <h4 class="h5 font-weight-bold mb-1">${name}</h4>
                        <p class="text-muted small m-0">NIN: ${nin}</p>
                    </div>
                </div>
                <div class="result-grid-nexus">${cells}</div>
                <div class="mt-4 pt-3 border-top border-white-5 d-flex gap-3">
                    ${actionButtons}
                </div>
            </div>
        `;
        document.getElementById('searchPanel').querySelector('form').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('skeletonLoader').style.display = 'none';
        document.getElementById('resultContent').innerHTML = html;
        document.getElementById('resultContent').style.display = 'block';
    }

    $(document).ready(function() {
        // Initialize verification types and provider filtering based on default mode
        filterProvidersByMode(currentMode);
        
        $('#selfie').on('change', function() {
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) {
                $('#selfiePreviewWrap').hide();
                $('#selfiePreview').attr('src', '');
                return;
            }
            const url = URL.createObjectURL(file);
            $('#selfiePreview').attr('src', url);
            $('#selfiePreviewWrap').show();
        });

        $('#verifyForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#verify-btn');
            let originalText = btn.html();
            const title = currentMode === 'selfie' ? 'Confirm In-Person Verification' : 'Confirm NIN Lookup';
            Swal.fire({
                title,
                text: `A fee of ₦${currentPrice.toLocaleString()} will be charged from your wallet. Continue?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#resultContainer').show();
                    $('#skeletonLoader').show();
                    $('#resultContent').hide().empty();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Securing Connection...');
                    const formData = new FormData(this);
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#skeletonLoader').hide();
                            if (response.status) {
                                displayResultWrapper(response);
                                Swal.fire({ title: 'Record Found!', icon: 'success', background: '#0a0a0f', color: '#fff', timer: 2000, showConfirmButton: false });
                            } else {
                                Swal.fire({ title: 'Verification Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                $('#resultContainer').hide();
                                btn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function(xhr) {
                            $('#skeletonLoader').hide();
                            let msg = 'NIMC Gateway is currently busy. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            Swal.fire({ title: 'Connection Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                            $('#resultContainer').hide();
                            btn.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

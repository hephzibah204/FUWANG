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
                            @if(session('status'))
                                <div class="alert alert-success mb-4">
                                    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('status') }}
                                </div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger mb-4">
                                    <i class="fa-solid fa-circle-xmark mr-2"></i>{{ $errors->first() }}
                                </div>
                            @endif

                            <!-- Lookup Mode Tabs (NIN / Phone / Tracking ID / Demographic) -->
                            <div class="nin-tabs mb-4">
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'nin' ? 'active' : '' }}" data-mode="nin" onclick="switchMode('nin', this)">Standard NIN Lookup</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'selfie' ? 'active' : '' }}" data-mode="selfie" onclick="switchMode('selfie', this)">Face (Selfie)</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'phone' ? 'active' : '' }}" data-mode="phone" onclick="switchMode('phone', this)">Phone Lookup</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'tracking' ? 'active' : '' }}" data-mode="tracking" onclick="switchMode('tracking', this)">Tracking ID</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'validation' ? 'active' : '' }}" data-mode="validation" onclick="switchMode('validation', this)">NIN Validation</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'validation_status' ? 'active' : '' }}" data-mode="validation_status" onclick="switchMode('validation_status', this)">Validation Status</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'demographic' ? 'active' : '' }}" data-mode="demographic" onclick="switchMode('demographic', this)">Demographic Search</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'share_code' ? 'active' : '' }}" data-mode="share_code" onclick="switchMode('share_code', this)">Share Code</button>
                                <button class="nin-tab {{ ($initialMode ?? 'nin') === 'requery' ? 'active' : '' }}" data-mode="requery" onclick="switchMode('requery', this)">Requery</button>
                            </div>

                            <div class="consent-box mb-4">
                                <i class="fa-solid fa-user-shield"></i>
                                <span>By submitting, you confirm you have obtained the subject's explicit consent per NDPR guidelines. Queries are logged for compliance.</span>
                            </div>

                            <form id="verifyForm" action="{{ route('services.nin.verify') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="_nin_verify_json" value="1">
                                <input type="hidden" id="modeInput" name="mode" value="{{ $initialMode ?? 'nin' }}">

                                <!-- NIN / Phone / Tracking ID Input (hidden for Demographic) -->
                                <div class="form-group mb-4" id="numberGroup">
                                    <label for="number" class="font-weight-600 mb-2" id="number-label">
                                        @if(($initialMode ?? 'nin') === 'validation')
                                            NIN to Validate
                                        @elseif(($initialMode ?? 'nin') === 'validation_status')
                                            NIN to Check Status
                                        @else
                                            National Identification Number (NIN)
                                        @endif
                                    </label>
                                    <div class="input-wrap">
                                        <i class="fa-regular fa-id-card" id="number-icon"></i>
                                        <input type="text" id="number" name="number" class="form-control" placeholder="{{ (($initialMode ?? 'nin') === 'validation') ? 'Enter 11-digit NIN (record not found case)' : ((($initialMode ?? 'nin') === 'validation_status') ? 'Enter the NIN used for validation' : 'Enter 11-digit NIN') }}" maxlength="25" required>
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

                                <div class="form-group mb-4" id="shareReasonGroup" style="display:none;">
                                    <label for="share_reason" class="font-weight-600 mb-2 small text-muted">Reason for Share Code Verification</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-clipboard-check"></i>
                                        <select id="share_reason" name="share_reason" class="form-control">
                                            <option value="">Select a reason</option>
                                            <option value="nyscCheck">NYSC Check</option>
                                            <option value="bank_kyc">Bank / KYC</option>
                                            <option value="financialProducts">Financial Products</option>
                                            <option value="employmentRecruitment">Employment Recruitment</option>
                                            <option value="educationAdmission">Education Admission</option>
                                            <option value="passportImmigration">Passport / Immigration</option>
                                            <option value="telecommunicationSimReg">SIM Registration</option>
                                            <option value="physicalAccess">Physical Access</option>
                                            <option value="logicalVirtualAccess">Logical Virtual Access</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-4" id="shareReasonOtherGroup" style="display:none;">
                                    <label for="share_reason_other" class="font-weight-600 mb-2 small text-muted">Other Reason</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-pen"></i>
                                        <input type="text" id="share_reason_other" name="share_reason_other" class="form-control" placeholder="Enter reason" maxlength="120">
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
                                <div class="form-group mb-4" id="outputTypeGroup" style="{{ in_array(($initialMode ?? 'nin'), ['validation', 'validation_status'], true) ? 'display:none;' : '' }}">
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

                                <div class="form-group mb-4" id="validationReasonGroup" style="{{ (($initialMode ?? 'nin') === 'validation') ? '' : 'display:none;' }}">
                                    <label for="validation_reason" class="font-weight-600 mb-2 small text-muted">Reason for Validation</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <select id="validation_reason" name="validation_reason" class="form-control">
                                            <option value="">Select a reason</option>
                                            <option value="sim_registration_record_not_found">SIM Registration (Record Not Found)</option>
                                            <option value="bank_kyc_record_not_found">Bank / KYC (Record Not Found)</option>
                                            <option value="government_portal_record_not_found">Government Portal (Record Not Found)</option>
                                            <option value="nin_mismatch_correction">NIN Mismatch / Correction</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <p class="small text-muted mt-2 mb-0"><i class="fa-solid fa-circle-info mr-1"></i> NIN Validation is not instant. It submits your NIN for processing and may take 1–3 days.</p>
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
                                                    <option value="{{ $provider->id }}" data-provider="{{ $provider->provider_identifier }}" data-types="{{ json_encode($provider->verificationTypes->where('status', true)) }}">{{ $provider->name }}</option>
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
                            <div id="ninResponseNotice" class="mt-3" style="display:none;"></div>

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
                                                @php
                                                    $ot = $res->response_data['_requested_output_type'] ?? 'premium_slip';
                                                    $allowed = ['standard_slip', 'regular_slip', 'premium_slip', 'vnin_slip'];
                                                    if (!in_array($ot, $allowed, true)) $ot = 'premium_slip';
                                                @endphp
                                                @if(in_array($res->service_type, ['nin_verification', 'nin_face_verification'], true))
                                                    <a href="{{ route('services.nin.slip', ['id' => $res->id, 'type' => $ot]) }}" class="btn btn-xs btn-outline-light ml-1">
                                                        <i class="fa fa-id-card"></i> Slip
                                                    </a>
                                                @endif
                                                <a href="{{ route('services.verification.report', $res->id) }}" class="btn btn-xs btn-outline-light ml-1">
                                                    <i class="fa fa-file-pdf"></i> Report PDF
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
    .nin-slip-success-banner { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.35); border-radius: 14px; padding: 16px 18px; }
    .badge-outline-primary { border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6; background: transparent; font-size: 0.7rem; padding: 3px 8px; border-radius: 6px; }
    .mode-info { padding: 10px 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; }
</style>
@endpush

@push('scripts')
<script>
    const prices = @json($prices);
    const providerModes = @json($providerModes ?? []);
    let currentMode = @json($initialMode ?? 'nin');
    let currentPrice = prices.nin;

    function ninSubmitLabel(mode) {
        if (mode === 'validation') return '<i class="fa-solid fa-upload mr-2"></i> Submit Validation';
        if (mode === 'validation_status') return '<i class="fa-solid fa-rotate mr-2"></i> Check Status';
        return '<i class="fa fa-search mr-2"></i> Verify Now';
    }

    function filterProvidersByMode(mode) {
        const providerSelect = document.getElementById('api_provider_id');
        const providerGroup = document.getElementById('providerSelectionGroup');
        const noProviderWarning = document.getElementById('noProviderWarning');
        const submitBtn = document.getElementById('verify-btn');
        
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

        if (availableCount === 0 && submitBtn) {
            providerGroup.style.display = 'none';
            noProviderWarning.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-lock mr-2"></i> Unavailable';
        } else {
            providerGroup.style.display = 'block';
            noProviderWarning.style.display = 'none';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = ninSubmitLabel(mode);
            }
            
            // Auto-select if only one provider is available
            if (availableCount === 1) {
                const availableOption = Array.from(providerSelect.options).find(opt => opt.style.display !== 'none' && opt.value !== '');
                if (availableOption) {
                    providerSelect.value = availableOption.value;
                }
            }

            if (mode === 'validation' || mode === 'validation_status') {
                const robost = Array.from(providerSelect.options).find(opt => {
                    if (opt.value === '' || opt.style.display === 'none') return false;
                    return (opt.getAttribute('data-provider') || '').toLowerCase() === 'robosttech';
                });
                if (robost) {
                    providerSelect.value = robost.value;
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
        const shareReasonGroup = document.getElementById('shareReasonGroup');
        const shareReasonSelect = document.getElementById('share_reason');
        const shareReasonOtherGroup = document.getElementById('shareReasonOtherGroup');
        const shareReasonOtherInput = document.getElementById('share_reason_other');
        const requeryGroup = document.getElementById('requeryGroup');
        const requeryInput = document.getElementById('reference_id');
        const outputTypeGroup = document.getElementById('outputTypeGroup');
        const validationReasonGroup = document.getElementById('validationReasonGroup');
        const validationReason = document.getElementById('validation_reason');
        const updateShareReasonOtherVisibility = () => {
            if (!shareReasonSelect || !shareReasonOtherGroup || !shareReasonOtherInput) return;
            const isOther = shareReasonSelect.value === 'other';
            shareReasonOtherGroup.style.display = isOther ? '' : 'none';
            shareReasonOtherInput.required = isOther;
        };
        if (shareReasonSelect) {
            shareReasonSelect.onchange = updateShareReasonOtherVisibility;
        }
        if (mode === 'phone') {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'Phone Number';
            input.placeholder = 'Enter registered phone number';
            input.maxLength = 11;
            icon.className = 'fa-solid fa-phone';
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = '';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
        } else if (mode === 'tracking') {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'Tracking ID';
            input.placeholder = 'Enter NIMC tracking ID';
            input.maxLength = 25;
            icon.className = 'fa-solid fa-fingerprint';
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = '';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
        } else if (mode === 'validation') {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'NIN to Validate';
            input.placeholder = 'Enter 11-digit NIN (record not found case)';
            input.maxLength = 25;
            icon.className = 'fa-solid fa-wrench';
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = 'none';
            if (validationReasonGroup) validationReasonGroup.style.display = '';
            if (validationReason) validationReason.required = true;
        } else if (mode === 'validation_status') {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'NIN to Check Status';
            input.placeholder = 'Enter the NIN used for validation';
            input.maxLength = 25;
            icon.className = 'fa-solid fa-rotate';
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = 'none';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
        } else if (mode === 'selfie') {
            numberGroup.style.display = '';
            selfieGroup.style.display = '';
            selfieInput.required = true;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'National Identification Number (NIN)';
            input.placeholder = 'Enter 11-digit NIN';
            input.maxLength = 11;
            icon.className = 'fa-regular fa-id-card';
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = '';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
        } else if (mode === 'demographic') {
            numberGroup.style.display = 'none';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            identityFields.style.display = '';
            if (outputTypeGroup) outputTypeGroup.style.display = '';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
        } else if (mode === 'share_code') {
            numberGroup.style.display = 'none';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = '';
            shareCodeInput.required = true;
            if (shareReasonGroup) shareReasonGroup.style.display = '';
            if (shareReasonSelect) shareReasonSelect.required = true;
            updateShareReasonOtherVisibility();
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = '';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
        } else if (mode === 'requery') {
            numberGroup.style.display = 'none';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = '';
            requeryInput.required = true;
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = '';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
        } else {
            numberGroup.style.display = '';
            selfieGroup.style.display = 'none';
            selfieInput.required = false;
            shareCodeGroup.style.display = 'none';
            shareCodeInput.required = false;
            if (shareReasonGroup) shareReasonGroup.style.display = 'none';
            if (shareReasonSelect) shareReasonSelect.required = false;
            if (shareReasonOtherGroup) shareReasonOtherGroup.style.display = 'none';
            if (shareReasonOtherInput) shareReasonOtherInput.required = false;
            requeryGroup.style.display = 'none';
            requeryInput.required = false;
            label.textContent = 'National Identification Number (NIN)';
            input.placeholder = 'Enter 11-digit NIN';
            input.maxLength = 11;
            icon.className = 'fa-regular fa-id-card';
            identityFields.style.display = 'none';
            if (outputTypeGroup) outputTypeGroup.style.display = '';
            if (validationReasonGroup) validationReasonGroup.style.display = 'none';
            if (validationReason) validationReason.required = false;
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
        displayResultWrapper({ status: true, data: data });
    }

    function ninExtractAjaxErrorMessage(xhr) {
        const j = xhr.responseJSON;
        if (j) {
            if (j.message) {
                return j.message;
            }
            if (j.errors && typeof j.errors === 'object') {
                const parts = [];
                Object.keys(j.errors).forEach(function (k) {
                    const v = j.errors[k];
                    if (Array.isArray(v)) {
                        parts.push(v.join(' '));
                    } else if (v) {
                        parts.push(String(v));
                    }
                });
                if (parts.length) {
                    return parts.join(' ');
                }
            }
        }
        if (xhr.status === 0) {
            return 'Network error — check your connection and try again.';
        }
        return 'The request could not be completed. Please try again.';
    }

    function ninSetResponseNotice(type, message) {
        const notice = document.getElementById('ninResponseNotice');
        if (!notice) return;
        const kind = (type === 'success') ? 'success' : (type === 'warning' ? 'warning' : 'danger');
        const icon = kind === 'success'
            ? 'fa-circle-check'
            : (kind === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-xmark');
        notice.className = `alert alert-${kind} mt-3`;
        notice.innerHTML = `<i class="fa-solid ${icon} mr-2"></i>${message || ''}`;
        notice.style.display = '';
    }

    function ninClearResponseNotice() {
        const notice = document.getElementById('ninResponseNotice');
        if (!notice) return;
        notice.style.display = 'none';
        notice.innerHTML = '';
        notice.className = 'mt-3';
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
        const slipBanner = res.slip_url
            ? `<div class="nin-slip-success-banner mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div>
                            <strong class="text-white d-block mb-1">Verification successful</strong>
                            <p class="small mb-0 text-muted">Your record was verified. Use <strong class="text-white">Download Slip</strong> below to open the PDF. If nothing opens, allow pop-ups for this site or tap the button again.</p>
                        </div>
                    </div>
                </div>`
            : '';
        const html = `
            ${slipBanner}
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

    function ninShowSlipReadyModal(slipUrl, thenCallback) {
        const finish = function (r) {
            if (typeof thenCallback === 'function') {
                thenCallback(r || {});
            }
        };
        const openSlip = function () {
            if (slipUrl) {
                window.open(slipUrl, '_blank');
            }
        };
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            Swal.fire({
                icon: 'success',
                title: 'Verification successful',
                html: '<p class="text-start small mb-0">Your NIN lookup completed successfully. Download the slip PDF when you are ready, or choose <strong>View details on page</strong> to see the summary first.</p>',
                confirmButtonText: 'Download slip (PDF)',
                showCancelButton: true,
                cancelButtonText: 'View details on page',
                allowOutsideClick: false,
                heightAuto: false,
                scrollbarPadding: true,
                background: '#0a0a0f',
                color: '#fff',
                confirmButtonColor: '#3b82f6'
            }).then(function (r) {
                if (r.isConfirmed && slipUrl) {
                    openSlip();
                }
                finish(r);
            });
            return;
        }
        if (window.confirm('Verification successful. Open slip PDF in a new tab?')) {
            openSlip();
        }
        finish({ isConfirmed: false });
    }

    function ninRenderValidationSubmission(res) {
        const nin = (res.data && (res.data.nin || res.data.NIN)) ? (res.data.nin || res.data.NIN) : '';
        const resultId = res.result_id || '';
        const statusUrl = res.status_url || '';
        const html = `
            <div class="result-card-nexus animate__animated animate__fadeIn">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                    <div>
                        <div class="badge-accent bg-warning-light text-warning mb-2 border-0"><i class="fa-solid fa-hourglass-half"></i> Submitted</div>
                        <h4 class="h5 font-weight-bold mb-1">NIN Validation Submitted</h4>
                        <p class="text-muted small m-0">${res.message || 'Your validation request has been submitted. Please check status later.'}</p>
                    </div>
                </div>
                <div class="result-grid-nexus">
                    <div class="result-cell"><span class="cell-label">NIN</span><span class="cell-value">${nin || '—'}</span></div>
                    <div class="result-cell"><span class="cell-label">Reference</span><span class="cell-value">${res.reference_id || '—'}</span></div>
                </div>
                <div class="mt-4 pt-3 border-top border-white-5 d-flex gap-3">
                    <button class="btn btn-outline-light flex-grow-1" onclick="ninCheckValidationStatus('${statusUrl}', '${nin}', '${resultId}')">
                        <i class="fa-solid fa-rotate mr-2"></i> Check Status
                    </button>
                    <button class="btn btn-outline-light flex-grow-1" onclick="switchMainPanel('vault', document.querySelectorAll('.s-tab')[1])">
                        <i class="fa-solid fa-box-archive mr-2"></i> Open Vault
                    </button>
                </div>
            </div>
        `;
        ninSetResponseNotice('success', res.message || 'Validation submitted successfully.');
        document.getElementById('searchPanel').querySelector('form').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('skeletonLoader').style.display = 'none';
        document.getElementById('resultContent').innerHTML = html;
        document.getElementById('resultContent').style.display = 'block';
    }

    function ninCheckValidationStatus(statusUrl, nin, resultId) {
        if (!statusUrl || !nin) return;
        $('#resultContainer').show();
        $('#skeletonLoader').show();
        $('#resultContent').hide().empty();
        $.ajax({
            url: statusUrl,
            method: 'POST',
            dataType: 'json',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name=\"_token\"]').value
            },
            data: {
                nin,
                result_id: resultId || null
            },
            success: function (r) {
                $('#skeletonLoader').hide();
                if (!r.status) {
                    ninSetResponseNotice('error', r.message || 'Unable to fetch validation status.');
                    Swal.fire({
                        title: 'Status check failed',
                        text: r.message || 'Unable to fetch status.',
                        icon: 'error',
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                    $('#resultContainer').hide();
                    return;
                }
                ninSetResponseNotice('success', r.message || 'Validation status fetched.');
                const data = r.data || {};
                const statusText = data.status || (data['in-progress'] ? 'in-progress' : 'pending');
                const html = `
                    <div class="result-card-nexus animate__animated animate__fadeIn">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                            <div>
                                <div class="badge-accent bg-warning-light text-warning mb-2 border-0"><i class="fa-solid fa-hourglass-half"></i> ${statusText}</div>
                                <h4 class="h5 font-weight-bold mb-1">Validation Status</h4>
                                <p class="text-muted small m-0">${r.message || 'Status fetched.'}</p>
                            </div>
                        </div>
                        <div class="result-grid-nexus">
                            <div class="result-cell"><span class="cell-label">NIN</span><span class="cell-value">${nin}</span></div>
                            <div class="result-cell"><span class="cell-label">Status</span><span class="cell-value">${statusText}</span></div>
                        </div>
                        <div class="mt-4 pt-3 border-top border-white-5 d-flex gap-3">
                            <button class="btn btn-outline-light flex-grow-1" onclick="ninCheckValidationStatus('${statusUrl}', '${nin}', '${resultId}')">
                                <i class="fa-solid fa-rotate mr-2"></i> Refresh
                            </button>
                            <button class="btn btn-outline-light flex-grow-1" onclick="switchMainPanel('vault', document.querySelectorAll('.s-tab')[1])">
                                <i class="fa-solid fa-box-archive mr-2"></i> Open Vault
                            </button>
                        </div>
                    </div>
                `;
                document.getElementById('resultContainer').style.display = 'block';
                document.getElementById('resultContent').innerHTML = html;
                document.getElementById('resultContent').style.display = 'block';
            },
            error: function (xhr) {
                $('#skeletonLoader').hide();
                const msg = ninExtractAjaxErrorMessage(xhr);
                ninSetResponseNotice('error', msg);
                Swal.fire({
                    title: 'Status check failed',
                    text: msg,
                    icon: 'error',
                    background: '#0a0a0f',
                    color: '#fff'
                });
                $('#resultContainer').hide();
            }
        });
    }

    $(document).ready(function() {
        @if(session('nin_slip_download_url'))
        window.setTimeout(function () {
            ninShowSlipReadyModal(@json(session('nin_slip_download_url')), function () {});
        }, 200);
        @endif

        const initialBtn = document.querySelector('.nin-tab[data-mode=\"' + currentMode + '\"]') || document.querySelector('.nin-tab[data-mode=\"nin\"]');
        if (initialBtn) {
            switchMode(currentMode, initialBtn);
        } else {
            filterProvidersByMode(currentMode);
        }
        
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
            ninClearResponseNotice();
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
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            $('#skeletonLoader').hide();
                            btn.prop('disabled', false).html(originalText);
                            if (response.status) {
                                ninSetResponseNotice('success', response.message || 'Request completed successfully.');
                                if (response.ui_state === 'validation_submitted') {
                                    if (typeof Swal !== 'undefined' && typeof Swal.close === 'function') {
                                        Swal.close();
                                    }
                                    ninRenderValidationSubmission(response);
                                    return;
                                }
                                const slipUrl = response.slip_url;

                                if (slipUrl) {
                                    $('#resultContainer').hide();
                                    if (typeof Swal !== 'undefined' && typeof Swal.close === 'function') {
                                        Swal.close();
                                    }
                                    window.setTimeout(function () {
                                        ninShowSlipReadyModal(slipUrl, function () {
                                            $('#resultContainer').show();
                                            displayResultWrapper(response);
                                        });
                                    }, 150);
                                } else {
                                    displayResultWrapper(response);
                                    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Verification successful',
                                            text: response.message || 'Identity details are shown below.',
                                            confirmButtonColor: '#3b82f6',
                                            background: '#0a0a0f',
                                            color: '#fff'
                                        });
                                    }
                                }
                            } else {
                                ninSetResponseNotice('error', response.message || 'Verification failed.');
                                $('#resultContainer').hide();
                                Swal.fire({
                                    title: 'Verification failed',
                                    text: response.message || 'The provider could not complete this lookup. If your wallet was charged, a refund is processed automatically for failed verifications.',
                                    icon: 'error',
                                    background: '#0a0a0f',
                                    color: '#fff'
                                });
                            }
                        },
                        error: function(xhr) {
                            $('#skeletonLoader').hide();
                            btn.prop('disabled', false).html(originalText);
                            const msg = ninExtractAjaxErrorMessage(xhr);
                            ninSetResponseNotice('error', msg);
                            Swal.fire({
                                title: xhr.status === 422 ? 'Invalid request' : 'Request failed',
                                text: msg,
                                icon: 'error',
                                background: '#0a0a0f',
                                color: '#fff'
                            });
                            $('#resultContainer').hide();
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

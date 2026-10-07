@extends('layouts.nexus')

@section('title', 'Agency KYC Onboarding Portal | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 fade-in">
            <!-- Onboarding Header -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.6), rgba(15, 23, 42, 0.8)); border: 1px solid rgba(59, 130, 246, 0.3) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <span class="badge mb-2 font-monospace" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">Phase 2: Agency Onboarding</span>
                        <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-clipboard-check text-primary me-2"></i>Complete Your Agency KYC & Legal Compliance</h3>
                        <p class="text-white-50 mb-0">Upload required verification documents and accept NIMC operational guidelines to submit your application for Admin approval.</p>
                    </div>
                    <div>
                        @if($agent->isOnboardingSubmitted() && $agent->isPending())
                            <span class="badge px-3 py-2 rounded-pill fs-6" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);"><i class="fa-solid fa-clock me-1"></i>Submitted — Awaiting Admin Review</span>
                        @endif
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                </div>
            @endif

            <!-- Progress Tracker -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 rounded-3 p-3 text-center" style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3) !important;">
                        <small class="text-success fw-bold text-uppercase d-block mb-1">Step 1</small>
                        <strong class="text-white"><i class="fa-solid fa-circle-check text-success me-1"></i>Basic Info & NIN</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-3 p-3 text-center" style="{{ $agent->utility_bill_path && $agent->picture_path ? 'background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3) !important;' : 'background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.3) !important;' }}">
                        <small class="{{ $agent->utility_bill_path && $agent->picture_path ? 'text-success' : 'text-info' }} fw-bold text-uppercase d-block mb-1">Step 2</small>
                        <strong class="text-white">KYC Documents</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-3 p-3 text-center" style="{{ $agent->accepted_terms ? 'background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3) !important;' : 'background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08) !important;' }}">
                        <small class="{{ $agent->accepted_terms ? 'text-success' : 'text-white-50' }} fw-bold text-uppercase d-block mb-1">Step 3</small>
                        <strong class="text-white">NIMC Conduct</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-3 p-3 text-center" style="{{ $agent->isLicensePaid() ? 'background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3) !important;' : ($agent->isLicensePendingReview() ? 'background: rgba(234, 179, 8, 0.12); border: 1px solid rgba(234, 179, 8, 0.3) !important;' : 'background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08) !important;') }}">
                        <small class="{{ $agent->isLicensePaid() ? 'text-success' : ($agent->isLicensePendingReview() ? 'text-warning' : 'text-white-50') }} fw-bold text-uppercase d-block mb-1">Step 4</small>
                        <strong class="text-white">Station License</strong>
                    </div>
                </div>
            </div>

            <!-- Basic Identification Summary Badge -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-user-check text-primary me-2"></i>Verified Basic Details</h5>
                <div class="row g-3 text-white-50 small">
                    <div class="col-md-4">Full Name: <strong class="text-white d-block">{{ $agent->full_name }}</strong></div>
                    <div class="col-md-4">NIN Number: <strong class="text-info d-block">{{ $agent->nin }} (@if($agent->nin_verified) Verified @else Pending Background Verification @endif)</strong></div>
                    <div class="col-md-4">BVN Number: <strong class="text-white d-block">{{ $agent->bvn }}</strong></div>
                    <div class="col-md-4">State: <strong class="text-white d-block">{{ $agent->state }}</strong></div>
                    <div class="col-md-4">Terminal IMEI: <code class="text-warning d-block">{{ $agent->machine_imei }}</code></div>
                    <div class="col-md-4">Station Address: <strong class="text-white d-block">{{ \Illuminate\Support\Str::limit($agent->office_address, 30) }}</strong></div>
                </div>
            </div>

            <!-- Upload Agency KYC Documents Form -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-file-arrow-up text-warning me-2"></i>Upload Verification Documents</h5>
                <p class="text-white-50 small mb-4">Please upload clear copies of your Utility Bill, Passport Photo, and Business Registration Certificate.</p>

                <form action="{{ route('agent.onboarding.upload_docs') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">1. Utility Bill Upload (Electricity, Water, or Waste Bill)</label>
                            <input type="file" name="utility_bill" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control mb-2">
                            @if($agent->utility_bill_path)
                                <div class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Utility Bill Uploaded</div>
                            @else
                                <small class="text-danger">* Required. PNG, JPG, WEBP, or PDF (max 5MB).</small>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">2. Agent Passport Picture / Live Photo</label>
                            <input type="file" name="picture" accept="image/png,image/jpeg,image/webp" class="form-control mb-2">
                            @if($agent->picture_path)
                                <div class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Passport Picture Uploaded</div>
                            @else
                                <small class="text-danger">* Required. PNG, JPG, or WEBP (max 5MB).</small>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">3. Business Registration (CAC Number) <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">Optional</span></label>
                            <input type="text" name="business_registration_number" value="{{ old('business_registration_number', $agent->business_registration_number) }}" class="form-control mb-2" placeholder="e.g. RC1234567 or BN9876543">
                            <small class="text-warning" style="font-size: 0.75rem;"><i class="fa-solid fa-circle-info me-1"></i>Optional for individual agents. Uploading CAC boosts your Agency Trust Score & KYC Tier level.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">4. CAC / Business Registration Certificate <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">Optional</span></label>
                            <input type="file" name="business_doc" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control mb-2">
                            @if($agent->business_registration_doc_path)
                                <div class="badge bg-info"><i class="fa-solid fa-check me-1"></i>Business Document Uploaded</div>
                            @else
                                <small class="text-white-50" style="font-size: 0.75rem;">PNG, JPG, WEBP, or PDF (max 5MB). Increases profile trust rating.</small>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-outline-warning rounded-pill px-4">
                            <i class="fa-solid fa-upload me-2"></i>Upload Documents
                        </button>
                    </div>
                </form>
            </div>

            <!-- Station License Accreditation Card -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <span class="badge mb-2 font-monospace" style="background: rgba(234, 179, 8, 0.15); color: #fbbf24; border: 1px solid rgba(234, 179, 8, 0.3);">Phase 3: Station License Accreditation</span>
                        <h5 class="text-white fw-bold mb-1"><i class="fa-solid fa-certificate text-warning me-2"></i>NIN Enrollment Station License Fee</h5>
                        <p class="text-white-50 small mb-0">Accredit your station hardware and operational enrollment license.</p>
                    </div>
                    <div>
                        @if($agent->isLicensePaid())
                            <span class="badge bg-success px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-check-circle me-1"></i>Station License Paid & Verified</span>
                        @elseif($agent->isLicensePendingReview())
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-clock me-1"></i>Payment Proof Under Review</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-circle-info me-1"></i>Unpaid (Optional Before Approval)</span>
                        @endif
                    </div>
                </div>

                <!-- Promo Alert Banner -->
                <div class="alert border-0 rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(180, 83, 9, 0.25)); border: 1px solid rgba(234, 179, 8, 0.4) !important;">
                    <div>
                        <span class="badge bg-warning text-dark font-monospace fw-bold mb-1">PROMO RATE: ₦{{ number_format($effectiveFee, 2) }}</span>
                        <strong class="text-white d-block">Special Enrollment License Promo Ending October 10th, 2026</strong>
                        <small class="text-white-50">Notice: Prior payment is not strictly mandatory for admin approval (approval is at admin discretion). You can pay online, via wallet, or upload offline proof now or post-approval.</small>
                    </div>
                </div>

                @if($agent->isLicensePaid())
                    <div class="alert alert-success border-0 rounded-3 p-4 mb-0" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-check me-2"></i>Official Accredited Station License Active</h6>
                        <p class="mb-2 small">Your station has been officially accredited. License fee of <strong>₦{{ number_format((float)$agent->license_fee_paid, 2) }}</strong> was verified via <strong>{{ strtoupper(str_replace('_', ' ', $agent->license_payment_method ?? 'VERIFIED')) }}</strong>.</p>
                        @if($agent->license_payment_reference)
                            <div class="small">Reference ID: <code class="text-white font-monospace">{{ $agent->license_payment_reference }}</code></div>
                        @endif
                        @if($agent->license_paid_at)
                            <div class="small text-white-50 mt-1">Accredited On: {{ $agent->license_paid_at->format('d M Y, h:i A') }}</div>
                        @endif
                    </div>
                @elseif($agent->isLicensePendingReview())
                    <div class="alert alert-warning border-0 rounded-3 p-4 mb-0" style="background: rgba(234, 179, 8, 0.15); color: #fef08a;">
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-clock me-2"></i>Payment Verification In Progress</h6>
                        <p class="mb-2 small">Your payment proof / pre-launch claim has been submitted to the operations desk. An administrator will verify your transaction within 24 hours.</p>
                        @if($agent->license_payment_reference)
                            <div class="small">Submitted Reference: <code class="text-white font-monospace">{{ $agent->license_payment_reference }}</code></div>
                        @endif
                    </div>
                @else
                    <!-- Tabs for 4 Payment Methods -->
                    <ul class="nav nav-pills mb-3 gap-2" id="licensePaymentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill px-3 py-2 fw-bold text-xs" id="tab-paystack-btn" data-bs-toggle="pill" data-bs-target="#tab-paystack" data-toggle="pill" data-target="#tab-paystack" type="button" role="tab">
                                <i class="fa-solid fa-credit-card me-1 text-primary"></i>1. Pay Online (Paystack)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-3 py-2 fw-bold text-xs" id="tab-wallet-btn" data-bs-toggle="pill" data-bs-target="#tab-wallet" data-toggle="pill" data-target="#tab-wallet" type="button" role="tab">
                                <i class="fa-solid fa-wallet me-1 text-info"></i>2. Pay via Wallet Balance
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-3 py-2 fw-bold text-xs" id="tab-offline-btn" data-bs-toggle="pill" data-bs-target="#tab-offline" data-toggle="pill" data-target="#tab-offline" type="button" role="tab">
                                <i class="fa-solid fa-receipt me-1 text-warning"></i>3. Paid Offline (Upload Proof)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-3 py-2 fw-bold text-xs" id="tab-legacy-btn" data-bs-toggle="pill" data-bs-target="#tab-legacy" data-toggle="pill" data-target="#tab-legacy" type="button" role="tab">
                                <i class="fa-solid fa-clock-rotate-left me-1 text-success"></i>4. Paid Before Website Launch
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border border-secondary rounded-4 p-4 bg-dark" id="licensePaymentTabsContent">
                        <!-- Tab 1: Paystack Online -->
                        <div class="tab-pane fade show active" id="tab-paystack" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <h6 class="text-white fw-bold mb-1"><i class="fa-solid fa-bolt text-warning me-2"></i>Instant Online Activation via Paystack</h6>
                                    <p class="text-white-50 small mb-0">Pay using ATM Debit Card, Bank Transfer, USSD, or Apple Pay. Your station license is accredited automatically immediately upon payment confirmation.</p>
                                </div>
                                <div>
                                    <button type="button" id="paystackPayBtn" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                                        <i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee, 2) }} Now
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Wallet Balance -->
                        <div class="tab-pane fade" id="tab-wallet" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <h6 class="text-white fw-bold mb-1"><i class="fa-solid fa-wallet text-info me-2"></i>Pay from Your Account Balance</h6>
                                    <p class="text-white-50 small mb-1">Deduct the license fee directly from your Fuwa wallet. Your current balance is: <strong class="text-warning">₦{{ number_format($walletBalance, 2) }}</strong>.</p>
                                    @if($walletBalance < $effectiveFee)
                                        <small class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Insufficient balance (Needs ₦{{ number_format($effectiveFee - $walletBalance, 2) }} more). Fund your wallet first or pay via Paystack.</small>
                                    @endif
                                </div>
                                <div>
                                    <form action="{{ route('agent.license.pay_wallet') }}" method="POST" onsubmit="return confirm('Confirm payment of ₦{{ number_format($effectiveFee, 2) }} from your wallet balance?');">
                                        @csrf
                                        <button type="submit" class="btn btn-info text-dark rounded-pill px-4 py-2 fw-bold" {{ $walletBalance < $effectiveFee ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-check me-2"></i>Pay ₦{{ number_format($effectiveFee, 2) }} via Wallet
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Offline Proof Upload -->
                        <div class="tab-pane fade" id="tab-offline" role="tabpanel">
                            <div class="mb-3 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                <h6 class="text-warning fw-bold mb-2"><i class="fa-solid fa-building-columns me-2"></i>Official Bank Details for Offline Transfer:</h6>
                                <div class="row g-2 text-white small">
                                    <div class="col-md-4">Bank Name: <strong class="text-white">{{ $manualFunding->bank_name ?? 'Zenith Bank' }}</strong></div>
                                    <div class="col-md-4">Account Number: <strong class="text-warning font-monospace fs-6">{{ $manualFunding->account_number ?? '1234567890' }}</strong></div>
                                    <div class="col-md-4">Account Name: <strong class="text-white">{{ $manualFunding->account_name ?? 'Fuwa Logistics Services' }}</strong></div>
                                </div>
                            </div>

                            <form action="{{ route('agent.license.upload_proof') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Amount Paid (₦)</label>
                                        <input type="number" step="0.01" name="amount_paid" value="{{ $effectiveFee }}" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Bank Name Paid From</label>
                                        <input type="text" name="bank_name" placeholder="e.g. GTBank / Access / Zenith" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Payment Date</label>
                                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Transaction Reference / Teller Number</label>
                                        <input type="text" name="transaction_reference" placeholder="e.g. TRF-123456789" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Depositor / Payer Name (Optional)</label>
                                        <input type="text" name="depositor_name" value="{{ $agent->full_name }}" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Upload Payment Proof (JPG, PNG, PDF max 5MB)</label>
                                        <input type="file" name="payment_proof" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-warning rounded-pill px-4 btn-sm fw-bold">
                                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Submit Offline Payment Proof
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 4: Pre-Website Launch Legacy Claim -->
                        <div class="tab-pane fade" id="tab-legacy" role="tabpanel">
                            <p class="text-white-50 small mb-3">If you paid for your Enrollment Agent License before the launch of this portal, provide your payment information and verification note below. The administration will check company records and accredit your account.</p>

                            <form action="{{ route('agent.license.claim_legacy') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Approximate Payment Date (Optional)</label>
                                        <input type="date" name="legacy_payment_date" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Original Receipt / Reference (Optional)</label>
                                        <input type="text" name="legacy_reference" placeholder="e.g. Old Station Code / Bank Teller Ref" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-white small fw-bold">Details / Explanation for Admin Verification</label>
                                        <textarea name="legacy_notes" rows="2" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2" required placeholder="e.g. Paid license fee in cash at head office / Transferred to coordinator before website rollout..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-white small fw-bold">Attach Legacy Document or Receipt (Optional)</label>
                                        <input type="file" name="legacy_proof_file" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 btn-sm fw-bold">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Pre-Launch Claim for Verification
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- NIMC Code of Conduct & Operational Agreement -->
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-gavel text-danger me-2"></i>NIMC Code of Conduct & Legal Compliance Agreement</h5>
                <p class="text-white-50 small mb-4">You must read and explicitly accept all strict operational rules set by NIMC and Fuwa.ng before submitting your application for approval.</p>

                <form action="{{ route('agent.onboarding.accept_compliance') }}" method="POST">
                    @csrf
                    <div class="space-y-3 text-white small">
                        <div class="form-check p-3 rounded-3 mb-3" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);">
                            <input type="checkbox" name="agree_no_non_appearance" value="1" id="c1" class="form-check-input" required>
                            <label for="c1" class="form-check-label text-white fw-bold ms-2">
                                🚫 Strict Physical Presence — NO NON-APPEARANCE ENROLLMENT
                            </label>
                            <p class="text-white-50 mb-0 ms-4 mt-1">I solemnly agree that every applicant undergoing NIN enrollment must be physically present at the station. Proxy, remote, or non-appearance enrollment is strictly prohibited and constitutes a criminal offense.</p>
                        </div>

                        <div class="form-check p-3 rounded-3 mb-3" style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.2);">
                            <input type="checkbox" name="agree_no_illegal_enrollment" value="1" id="c2" class="form-check-input" required>
                            <label for="c2" class="form-check-label text-white fw-bold ms-2">
                                🚫 Anti-Fraud — NO ILLEGAL OR UNAUTHORIZED ENROLLMENT
                            </label>
                            <p class="text-white-50 mb-0 ms-4 mt-1">I agree not to capture fake biometric data, manipulate demographic information, or extort unauthorized charges beyond official NIMC fee structures.</p>
                        </div>

                        <div class="form-check p-3 rounded-3 mb-3" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                            <input type="checkbox" name="agree_data_privacy" value="1" id="c3" class="form-check-input" required>
                            <label for="c3" class="form-check-label text-white fw-bold ms-2">
                                🔒 NDPR & Data Privacy Protection Compliance
                            </label>
                            <p class="text-white-50 mb-0 ms-4 mt-1">I agree to handle all biometric and personal data in accordance with the Nigeria Data Protection Act (NDPA). No enrollee data shall be retained, copied, shared, or disclosed to unauthorized parties.</p>
                        </div>

                        <div class="form-check p-3 rounded-3 mb-3" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2);">
                            <input type="checkbox" name="agree_no_terminal_tampering" value="1" id="c4" class="form-check-input" required>
                            <label for="c4" class="form-check-label text-white fw-bold ms-2">
                                📱 Terminal & Machine IMEI Security Lock
                            </label>
                            <p class="text-white-50 mb-0 ms-4 mt-1">I agree that my registered Machine IMEI and hardware terminals belong to my authorized station. I will not tamper with device firmware, clone IMEIs, or share terminal access with unauthorized third parties.</p>
                        </div>

                        <div class="form-check p-3 rounded-3 mb-3" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);">
                            <input type="checkbox" name="agree_legal_liability" value="1" id="c5" class="form-check-input" required>
                            <label for="c5" class="form-check-label text-white fw-bold ms-2">
                                ⚖️ Criminal Liability, Wallet Forfeiture & Termination
                            </label>
                            <p class="text-white-50 mb-0 ms-4 mt-1">I understand that any breach of these rules will result in immediate account deactivation, forfeiture of agency wallet funds, permanent blacklisting, and reporting to NIMC and law enforcement agencies.</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-success rounded-pill px-5 py-3 text-white fw-bold fs-6 w-100">
                            <i class="fa-solid fa-paper-plane me-2"></i>Accept Compliance & Submit Application for Admin Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const payBtn = document.getElementById('paystackPayBtn');
    if (!payBtn) return;

    payBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Initializing Paystack...';

        try {
            const initRes = await fetch("{{ route('agent.license.paystack_init') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await initRes.json();
            if (!data.status) {
                alert(data.message || 'Unable to initialize Paystack checkout.');
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee, 2) }} Now';
                return;
            }

            const handler = PaystackPop.setup({
                key: data.public_key,
                email: data.email,
                amount: data.amount_kobo,
                currency: 'NGN',
                ref: data.reference,
                metadata: data.metadata,
                callback: function(response) {
                    payBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Verifying Accreditation...';
                    fetch("{{ route('agent.license.paystack_verify') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reference: response.reference })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status) {
                            alert(res.message);
                            window.location.reload();
                        } else {
                            alert(res.message || 'Payment verification failed.');
                            payBtn.disabled = false;
                            payBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee, 2) }} Now';
                        }
                    })
                    .catch(err => {
                        alert('Network error while verifying payment.');
                        payBtn.disabled = false;
                        payBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee, 2) }} Now';
                    });
                },
                onClose: function() {
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee, 2) }} Now';
                }
            });

            handler.openIframe();
        } catch (err) {
            alert('Error connecting to payment gateway: ' + err.message);
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee, 2) }} Now';
        }
    });
});
</script>
@endsection

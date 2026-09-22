@extends('layouts.app')

@section('title', 'Agency KYC Onboarding Portal | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 fade-in">
            <!-- Onboarding Header -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.6), rgba(15, 23, 42, 0.8)); border: 1px solid rgba(59, 130, 246, 0.3) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <span class="badge bg-warning text-dark font-monospace mb-2">Phase 2: Agency Onboarding</span>
                        <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-clipboard-check text-primary me-2"></i>Complete Your Agency KYC & Legal Compliance</h3>
                        <p class="text-white-50 mb-0">Upload required verification documents and accept NIMC operational guidelines to submit your application for Admin approval.</p>
                    </div>
                    <div>
                        @if($agent->isOnboardingSubmitted() && $agent->isPending())
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-clock me-1"></i>Submitted — Awaiting Admin Review</span>
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
                <div class="col-md-4">
                    <div class="card border-0 rounded-3 p-3 bg-success bg-opacity-25 border-success text-center">
                        <small class="text-success fw-bold text-uppercase d-block mb-1">Step 1</small>
                        <strong class="text-white"><i class="fa-solid fa-circle-check text-success me-1"></i>Basic Info & NIN</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 rounded-3 p-3 {{ $agent->utility_bill_path && $agent->picture_path ? 'bg-success bg-opacity-25 border-success' : 'bg-primary bg-opacity-25 border-primary' }} text-center">
                        <small class="{{ $agent->utility_bill_path && $agent->picture_path ? 'text-success' : 'text-info' }} fw-bold text-uppercase d-block mb-1">Step 2</small>
                        <strong class="text-white">KYC Documents & CAC</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 rounded-3 p-3 {{ $agent->accepted_terms ? 'bg-success bg-opacity-25 border-success' : 'bg-dark' }} text-center">
                        <small class="{{ $agent->accepted_terms ? 'text-success' : 'text-white-50' }} fw-bold text-uppercase d-block mb-1">Step 3</small>
                        <strong class="text-white">NIMC Code of Conduct</strong>
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
                            <label class="form-label text-white small fw-bold">3. Business Registration (CAC Number)</label>
                            <input type="text" name="business_registration_number" value="{{ old('business_registration_number', $agent->business_registration_number) }}" class="form-control mb-2" placeholder="e.g. RC1234567 or BN9876543">
                            <small class="text-white-50">Optional for individual agents, required for registered businesses.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">4. CAC / Business Registration Document</label>
                            <input type="file" name="business_doc" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control mb-2">
                            @if($agent->business_registration_doc_path)
                                <div class="badge bg-info"><i class="fa-solid fa-check me-1"></i>Business Document Uploaded</div>
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
@endsection

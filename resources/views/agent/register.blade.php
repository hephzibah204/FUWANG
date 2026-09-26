@extends('layouts.nexus')

@section('title', 'NIN Enrollment Agent Registration | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="agent-register-wrapper py-4 px-2 px-md-4">
    <!-- Ambient Background Blobs -->
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    <div class="container-fluid" style="max-width: 980px;">
        <!-- Header Section -->
        <div class="text-center mb-4">
            <div class="status-pill mb-3">
                <span class="status-dot"></span> NIMC Accredited Enrollment Ecosystem Partner
            </div>
            <h1 class="brand-headline mb-2">NIN Enrollment Agent Registration</h1>
            <p class="text-muted lead mb-4" style="max-width: 680px; margin: 0 auto; font-size: 0.95rem;">
                Accredited National Identification Number (NIN) agent registration. Fast-tracked verification, bank-grade encryption, and real-time synchronization.
            </p>
            
            <div class="d-inline-flex gap-2 mb-4">
                <a href="{{ route('agent.landing') }}" class="btn btn-glass btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> Program Overview
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-glass btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-house me-1"></i> User Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-glass btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Log In
                    </a>
                @endauth
            </div>
        </div>

        <!-- Progress Stepper -->
        <x-nexus.stepper 
            :steps="[
                ['title' => 'Agent Identification', 'subtitle' => 'Basic info & roster link'],
                ['title' => 'Agency KYC', 'subtitle' => 'Upload CAC & Terminal info'],
                ['title' => 'Certification', 'subtitle' => 'NIMC compliance & launch']
            ]" 
            :currentStep="1" 
        />

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert-banner alert-banner-success mb-4">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert-banner alert-banner-info mb-4">
                <i class="fa-solid fa-circle-info me-2"></i> {{ session('info') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-banner alert-banner-danger mb-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-banner alert-banner-danger mb-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ $errors->first() }}
            </div>
        @endif

        <!-- Main Form Card -->
        <div class="auth-card p-4 p-md-5 rounded-4 position-relative mx-auto" style="max-width: 850px; width: 100%;">
            <form action="{{ route('agent.register.submit') }}" method="POST" id="agentRegisterForm">
                @csrf

                <!-- Agent Category Selector -->
                @php $currentType = old('agent_type', $initialType ?? 'new'); @endphp
                <div class="mb-4">
                    <label class="form-label text-white fw-bold mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-layer-group text-primary me-2"></i>Select Agent Category</span>
                        <span class="badge-accent">Step 1 of 2</span>
                    </label>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label id="cardTypeNew" class="category-card cursor-pointer w-100 {{ $currentType === 'new' ? 'active-new' : '' }}">
                                <input type="radio" name="agent_type" value="new" class="d-none" {{ $currentType === 'new' ? 'checked' : '' }} onchange="toggleAgentType('new')">
                                <div class="category-icon icon-new">
                                    <i class="fa-solid fa-user-plus"></i>
                                </div>
                                <div class="category-content">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <strong class="category-title text-white">New Agent Registration</strong>
                                    </div>
                                    <p class="category-desc text-muted mb-0">Applying to join Fuwa.NG company enrollment ecosystem as a fresh agent.</p>
                                </div>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label id="cardTypeExisting" class="category-card cursor-pointer w-100 {{ $currentType === 'existing' ? 'active-existing' : '' }}">
                                <input type="radio" name="agent_type" value="existing" class="d-none" {{ $currentType === 'existing' ? 'checked' : '' }} onchange="toggleAgentType('existing')">
                                <div class="category-icon icon-existing">
                                    <i class="fa-solid fa-building-user"></i>
                                </div>
                                <div class="category-content">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <strong class="category-title text-white">Existing Agent Profile</strong>
                                        <span class="badge-gold">Fast-Track</span>
                                    </div>
                                    <p class="category-desc text-muted mb-0">Already enrolled in company roster. Link your station license.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Instant Autocomplete & Search for Existing Agents -->
                <div id="existingAgentSearchCard" class="mb-4 {{ $currentType === 'existing' ? '' : 'd-none' }}">
                    <div class="agent-lookup-box p-4 rounded-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label text-white fw-bold mb-0">
                                <i class="fa-solid fa-magnifying-glass text-gold me-2"></i>Find & Link Your Pre-Approved Profile
                            </label>
                            <span class="badge-gold">Search by Name or Code</span>
                        </div>
                        <p class="text-muted small mb-3">
                            Type your <strong>Full Name</strong> (first or last), <strong>Station Code</strong>, <strong>Email</strong>, or <strong>Phone Number</strong>. Selecting your profile will instantly link and validate your record:
                        </p>

                        <div class="position-relative" id="autocompleteWrapper">
                            <div class="input-wrap">
                                <i class="fa-solid fa-search input-icon text-gold"></i>
                                <input type="text" id="preApprovedSearch" class="form-input text-white pe-5" placeholder="Type your name (e.g. John Doe) or agent code (e.g. FUWA-LO001)..." autocomplete="off">
                                <button type="button" id="clearSearchBtn" class="btn-clear-search d-none" aria-label="Clear Search">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <span id="searchSpinner" class="search-spinner d-none">
                                    <i class="fa-solid fa-spinner fa-spin text-gold"></i>
                                </span>
                            </div>

                            <!-- Autocomplete Dropdown -->
                            <div id="searchResults" class="autocomplete-dropdown d-none" role="listbox" aria-live="polite" aria-atomic="true"></div>
                        </div>

                        <!-- Instant Real-Time Validation Box: Verified -->
                        <div id="verifiedAgentBanner" class="verified-agent-banner mt-3 d-none">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="verified-check-icon">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong id="verifiedAgentName" class="text-white"></strong>
                                            <span id="verifiedAgentCode" class="badge-gold font-monospace"></span>
                                            <span class="badge-emerald">✓ Pre-Approved</span>
                                        </div>
                                        <div id="verifiedAgentMeta" class="text-muted small mt-1"></div>
                                    </div>
                                </div>
                                <button type="button" id="changeAgentBtn" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Change
                                </button>
                            </div>
                        </div>

                        <!-- Instant Real-Time Validation Box: Unmatched Notice -->
                        <div id="unmatchedAgentNotice" class="unmatched-agent-notice mt-3 d-none">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-info text-gold mt-1"></i>
                                <div>
                                    <strong class="text-white d-block">Name not found in pre-approved master roster</strong>
                                    <span class="text-muted small">
                                        You can still submit your registration! Your application will undergo standard administrative verification instead of instant fast-track approval.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Company Agent Code & Email OTP Claim Section -->
                        <div class="mt-3 pt-3 border-top border-secondary border-opacity-25">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="companyAgentCodeInput" class="text-white-50 small fw-bold mb-0">Company Agent Code / Station License ID</label>
                                        <span class="text-muted small" style="font-size: 0.75rem;">(Auto-filled)</span>
                                    </div>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-id-badge input-icon text-muted"></i>
                                        <input type="text" id="companyAgentCodeInput" name="company_agent_code" class="form-input text-white" value="{{ old('company_agent_code') }}" placeholder="e.g. FUWA-LO001">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="claimOtpInput" class="text-white-50 small fw-bold mb-0">Profile Claim Verification OTP <span class="text-danger">*</span></label>
                                        <button type="button" id="sendOtpBtn" class="btn btn-link text-gold p-0 text-decoration-none small" style="font-size: 0.75rem;" disabled>
                                            <i class="fa-solid fa-paper-plane me-1"></i> Send Email OTP
                                        </button>
                                    </div>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-key input-icon text-gold"></i>
                                        <input type="text" id="claimOtpInput" name="claim_otp" maxlength="6" class="form-input text-white @error('claim_otp') is-invalid @enderror" value="{{ old('claim_otp') }}" placeholder="6-digit Email OTP">
                                    </div>
                                    <span id="otpStatusMsg" class="d-block small mt-1 text-muted" style="font-size: 0.75rem;">Select your profile above to send OTP.</span>
                                    @error('claim_otp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Basic Identification Details -->
                <div class="form-section-header mb-3 pb-2 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                    <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-id-card text-primary me-2"></i>Identification & Contact Information</h5>
                    <span class="text-muted small">All fields required</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="fullNameInput" class="form-label text-white small fw-bold">Full Name (Primary Name)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" id="fullNameInput" name="full_name" class="form-input @error('full_name') is-invalid @enderror" value="{{ old('full_name', Auth::user()?->fullname) }}" required placeholder="Enter full name">
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">Validated against NIMC database upon verification.</small>
                        @error('full_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="emailInput" class="form-label text-white small fw-bold">Email Address</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-envelope input-icon"></i>
                            <input type="email" id="emailInput" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email', Auth::user()?->email) }}" required placeholder="your.email@example.com">
                        </div>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phoneInput" class="form-label text-white small fw-bold">Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone input-icon"></i>
                            <input type="text" id="phoneInput" name="phone_number" class="form-input @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', Auth::user()?->number) }}" required placeholder="e.g. 08012345678">
                        </div>
                        @error('phone_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="ninInput" class="form-label text-white small fw-bold">NIN (National Identification Number - 11 Digits)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-shield-halved input-icon text-primary"></i>
                            <input type="text" id="ninInput" name="nin" maxlength="11" class="form-input @error('nin') is-invalid @enderror" value="{{ old('nin') }}" required placeholder="11-digit NIN">
                        </div>
                        <small class="text-info" style="font-size: 0.75rem;"><i class="fa-solid fa-shield-check me-1"></i>Non-blocking: registration proceeds even if NIMC server is temporarily offline.</small>
                        @error('nin') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="bvnInput" class="form-label text-white small fw-bold">BVN (Bank Verification Number - 11 Digits)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-building-columns input-icon"></i>
                            <input type="text" id="bvnInput" name="bvn" maxlength="11" class="form-input @error('bvn') is-invalid @enderror" value="{{ old('bvn') }}" required placeholder="11-digit BVN">
                        </div>
                        @error('bvn') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="stateInput" class="form-label text-white small fw-bold">State of Residence / Station</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-location-dot input-icon"></i>
                            <input type="text" id="stateInput" name="state" class="form-input @error('state') is-invalid @enderror" value="{{ old('state') }}" required placeholder="e.g. Lagos">
                        </div>
                        @error('state') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="residentialAddressInput" class="form-label text-white small fw-bold">Residential Address</label>
                        <div class="input-wrap align-items-start"><i class="fa-solid fa-house input-icon mt-1"></i></div>
                        @error('residential_address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="officeAddressInput" class="form-label text-white small fw-bold">Office / Station Center Address</label>
                        <div class="input-wrap align-items-start"><i class="fa-solid fa-building input-icon mt-1"></i></div>
                        @error('office_address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Hardware & Terminal Equipment -->
                <div class="form-section-header mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                    <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-microchip text-primary me-2"></i>Hardware & Machine Terminal</h5>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6" id="hasMachineSelectBox">
                        <label class="form-label text-white small fw-bold">Do you have physical machine(s) to carry out enrollments?</label>
                        <div class="input-wrap"><i class="fa-solid fa-laptop input-icon"></i></div>
                    </div>

                    <div class="col-md-6" id="imeiBox">
                        <label for="machineImeiInput" class="form-label text-white small fw-bold">Machine IMEI / Device Terminal ID <span class="text-danger">*</span></label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-barcode input-icon"></i>
                            <input type="text" id="machineImeiInput" name="machine_imei" class="form-input @error('machine_imei') is-invalid @enderror" value="{{ old('machine_imei') }}" required placeholder="Enter machine IMEI (e.g. 864201041234567)">
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">Existing agents are required to enter their assigned enrollment terminal IMEI.</small>
                        @error('machine_imei') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Info Box -->
                <div class="info-callout p-3 rounded-3 mb-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                        <div>
                            <strong class="text-white">Next Step: Agency KYC Documents</strong>
                            <p class="text-muted small mb-0">After submitting your identification details, you will be guided to upload your Utility Bill, Passport Photo, Business CAC document, and accept NIMC Code of Conduct compliance.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-4 pt-2">
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-full py-3">
                        <span>Continue to Agency KYC Portal</span>
                        <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .agent-register-wrapper {
        position: relative;
        min-height: calc(100vh - 120px);
    }

    /* Category Cards - Clean Glassmorphic, No Solid Yellow */
    .category-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 18px 20px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: all 0.3s ease;
        position: relative;
    }

    .category-card:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .category-card.active-new {
        background: rgba(59, 130, 246, 0.08) !important;
        border-color: var(--clr-primary, #3b82f6) !important;
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
    }

    .category-card.active-existing {
        background: rgba(245, 158, 11, 0.06) !important;
        border-color: rgba(245, 158, 11, 0.5) !important;
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.12);
    }

    .category-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .icon-new {
        background: rgba(59, 130, 246, 0.12);
        color: var(--clr-primary, #3b82f6);
    }

    .icon-existing {
        background: rgba(245, 158, 11, 0.12);
        color: #fbbf24;
    }

    .category-title {
        font-size: 0.98rem;
        font-weight: 700;
    }

    .category-desc {
        font-size: 0.82rem;
        line-height: 1.4;
    }

    /* Badges - Subtle translucent pills, NO solid neon yellow */
    .badge-gold {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.3);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
    }

    .badge-emerald {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .text-gold {
        color: #fbbf24 !important;
    }

    /* Agent Lookup Box - Elegant dark card with subtle gold accent border */
    .agent-lookup-box {
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(245, 158, 11, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .btn-clear-search {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px 8px;
        font-size: 1rem;
        z-index: 5;
    }

    .btn-clear-search:hover {
        color: #fff;
    }

    .search-spinner {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
    }

    /* Autocomplete Dropdown */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 6px;
        background: #0b1120;
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: 14px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7);
        max-height: 280px;
        overflow-y: auto;
        z-index: 1060;
    }

    .autocomplete-item {
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover,
    .autocomplete-item.selected {
        background: rgba(255, 255, 255, 0.06);
    }

    .agent-avatar-chip {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .badge-gold {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: 9999px;
        padding: 2px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .badge-select-pill {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 9999px;
        padding: 3px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .autocomplete-item:hover .badge-select-pill {
        background: #3b82f6;
        color: #ffffff;
    }

    /* Verified Banner - Soft dark emerald card */
    .verified-agent-banner {
        background: rgba(16, 185, 129, 0.08);
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 12px;
        padding: 14px 18px;
    }

    .verified-check-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    /* Unmatched Notice */
    .unmatched-agent-notice {
        background: rgba(245, 158, 11, 0.06);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: 12px;
        padding: 12px 16px;
    }

    /* Info Callout */
    .info-callout {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #cbd5e1;
        transition: all 0.2s ease;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border-color: rgba(255, 255, 255, 0.2);
    }
</style>
@endpush

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
function toggleAgentType(type) {
    const searchCard = document.getElementById('existingAgentSearchCard');
    const cardNew = document.getElementById('cardTypeNew');
    const cardExisting = document.getElementById('cardTypeExisting');

    if (type === 'existing') {
        searchCard.classList.remove('d-none');
        if (cardExisting) cardExisting.className = 'category-card cursor-pointer w-100 active-existing';
        if (cardNew) cardNew.className = 'category-card cursor-pointer w-100';
    } else {
        searchCard.classList.add('d-none');
        if (cardNew) cardNew.className = 'category-card cursor-pointer w-100 active-new';
        if (cardExisting) cardExisting.className = 'category-card cursor-pointer w-100';
    }
}

function toggleMachineIMEI(val) {
    const box = document.getElementById('imeiBox');
    if (val === '1') {
        box.classList.remove('d-none');
    } else {
        box.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('preApprovedSearch');
    const resultsContainer = document.getElementById('searchResults');
    const clearBtn = document.getElementById('clearSearchBtn');
    const spinner = document.getElementById('searchSpinner');
    const codeInput = document.getElementById('companyAgentCodeInput');
    const nameInput = document.getElementById('fullNameInput');
    const emailInput = document.getElementById('emailInput');
    const phoneInput = document.getElementById('phoneInput');
    const verifiedBanner = document.getElementById('verifiedAgentBanner');
    const verifiedName = document.getElementById('verifiedAgentName');
    const verifiedCode = document.getElementById('verifiedAgentCode');
    const verifiedMeta = document.getElementById('verifiedAgentMeta');
    const changeBtn = document.getElementById('changeAgentBtn');
    const unmatchedNotice = document.getElementById('unmatchedAgentNotice');
    const form = document.getElementById('agentRegisterForm');
    const submitBtn = document.getElementById('submitBtn');

    if (!searchInput || !resultsContainer) return;

    let debounceTimer = null;
    let selectedIndex = -1;
    let currentResults = [];

    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const otpStatusMsg = document.getElementById('otpStatusMsg');

    function selectAgent(agent) {
        if (codeInput) {
            codeInput.value = agent.agent_code;
            codeInput.readOnly = true;
        }
        if (nameInput) {
            nameInput.value = agent.full_name;
            nameInput.readOnly = true;
        }
        if (emailInput && !agent.email.includes('***')) {
            emailInput.value = agent.email;
            emailInput.readOnly = true;
        }
        if (phoneInput && !agent.phone_number.includes('****')) {
            phoneInput.value = agent.phone_number;
            phoneInput.readOnly = true;
        }

        if (sendOtpBtn) sendOtpBtn.disabled = false;
        if (otpStatusMsg) {
            otpStatusMsg.className = 'd-block small mt-1 text-gold';
            otpStatusMsg.textContent = 'Click "Send Email OTP" to verify account ownership.';
        }

        // Display Verified Banner
        if (verifiedName) verifiedName.textContent = agent.full_name;
        if (verifiedCode) verifiedCode.textContent = agent.agent_code;
        if (verifiedMeta) verifiedMeta.textContent = `Phone: ${agent.phone_number} | Email: ${agent.email}`;
        if (verifiedBanner) verifiedBanner.classList.remove('d-none');
        if (unmatchedNotice) unmatchedNotice.classList.add('d-none');

        searchInput.value = `${agent.full_name} (${agent.agent_code})`;
        clearBtn.classList.remove('d-none');
        resultsContainer.innerHTML = '';
        resultsContainer.classList.add('d-none');
    }

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function() {
            const code = codeInput ? codeInput.value : '';
            if (!code) return;

            sendOtpBtn.disabled = true;
            sendOtpBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...';

            fetch(`{{ route('agent.send_claim_otp') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ company_agent_code: code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    if (otpStatusMsg) {
                        otpStatusMsg.className = 'd-block small mt-1 text-emerald';
                        otpStatusMsg.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + data.message;
                    }
                    sendOtpBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Sent';
                } else {
                    if (otpStatusMsg) {
                        otpStatusMsg.className = 'd-block small mt-1 text-danger';
                        otpStatusMsg.textContent = data.message || 'Failed to send OTP.';
                    }
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Resend OTP';
                }
            })
            .catch(err => {
                console.error('OTP send error:', err);
                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Email OTP';
            });
        });
    }

    function clearSelection() {
        searchInput.value = '';
        clearBtn.classList.add('d-none');
        if (codeInput) codeInput.readOnly = false;
        if (nameInput) nameInput.readOnly = false;
        if (emailInput) emailInput.readOnly = false;
        if (phoneInput) phoneInput.readOnly = false;
        if (sendOtpBtn) sendOtpBtn.disabled = true;
        if (otpStatusMsg) {
            otpStatusMsg.className = 'd-block small mt-1 text-muted';
            otpStatusMsg.textContent = 'Select your profile above to send OTP.';
        }
        if (verifiedBanner) verifiedBanner.classList.add('d-none');
        if (unmatchedNotice) unmatchedNotice.classList.add('d-none');
        resultsContainer.innerHTML = '';
        resultsContainer.classList.add('d-none');
        selectedIndex = -1;
    }

    if (changeBtn) {
        changeBtn.addEventListener('click', function() {
            clearSelection();
            searchInput.focus();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            clearSelection();
            searchInput.focus();
        });
    }

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        clearTimeout(debounceTimer);

        if (query.length > 0) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('d-none');
            return;
        }

        if (spinner) spinner.classList.remove('d-none');

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('agent.search_preapproved') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (spinner) spinner.classList.add('d-none');
                    resultsContainer.innerHTML = '';
                    currentResults = data.agents || [];
                    selectedIndex = -1;

                    if (currentResults.length > 0) {
                        if (unmatchedNotice) unmatchedNotice.classList.add('d-none');

                        currentResults.forEach((agent) => {
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.setAttribute('role', 'option');
                            item.setAttribute('tabindex', '0');

                            const initials = (agent.full_name || 'AG')
                                .split(' ')
                                .map(n => n[0])
                                .slice(0, 2)
                                .join('')
                                .toUpperCase();

                            item.innerHTML = `
                                <div class="d-flex align-items-center gap-3">
                                    <div class="agent-avatar-chip">${initials}</div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-white">${agent.full_name}</strong>
                                            <span class="badge-gold font-monospace">${agent.agent_code}</span>
                                        </div>
                                        <div class="text-muted small">${agent.phone_number} • ${agent.email}</div>
                                    </div>
                                </div>
                                <span class="badge-select-pill">Select</span>
                            `;

                            item.addEventListener('click', function () {
                                selectAgent(agent);
                            });

                            resultsContainer.appendChild(item);
                        });
                        resultsContainer.classList.remove('d-none');
                    } else {
                        if (unmatchedNotice) unmatchedNotice.classList.remove('d-none');
                        resultsContainer.innerHTML = `
                            <div class="p-3 text-center text-muted small">
                                <i class="fa-solid fa-circle-question me-1"></i> No matching agent found in pre-approved roster.
                            </div>
                        `;
                        resultsContainer.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    if (spinner) spinner.classList.add('d-none');
                    console.error('PreApproved lookup error:', err);
                });
        }, 150);
    });

    // Keyboard Navigation for Autocomplete
    searchInput.addEventListener('keydown', function (e) {
        const items = resultsContainer.querySelectorAll('.autocomplete-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            updateSelection(items);
        } else if (e.key === 'Enter') {
            if (selectedIndex >= 0 && selectedIndex < currentResults.length) {
                e.preventDefault();
                selectAgent(currentResults[selectedIndex]);
            }
        } else if (e.key === 'Escape') {
            resultsContainer.classList.add('d-none');
        }
    });

    function updateSelection(items) {
        items.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('selected');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('selected');
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });

    // Form submission spinner
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting Application...';
            submitBtn.disabled = true;
        });
    }
});
</script>
@endpush

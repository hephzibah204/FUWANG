@extends('layouts.app')

@section('title', 'NIN Enrollment Agent Registration | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9 fade-in">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div>
                    <h3 class="text-white mb-1 fw-bold"><i class="fa-solid fa-user-shield text-primary me-2"></i>NIN Enrollment Agent Registration</h3>
                    <p class="text-white-50 mb-0">Fill in your basic information to begin your onboarding. (NIN verification is non-blocking if gateway is offline).</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('agent.landing') }}" class="btn btn-outline-primary rounded-pill btn-sm">Program Overview</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light rounded-pill btn-sm">User Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light rounded-pill btn-sm">Already registered? Log In</a>
                    @endauth
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info border-0 rounded-3 mb-4 p-3" style="background: rgba(59, 130, 246, 0.15); color: #bfdbfe;">
                    <i class="fa-solid fa-circle-info me-2"></i>{{ session('info') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <form action="{{ route('agent.register.submit') }}" method="POST">
                    @csrf

                    <!-- Registration Type Selector Tabs -->
                    @php $currentType = old('agent_type', $initialType ?? 'new'); @endphp
                    <div class="mb-4">
                        <label class="form-label text-white fw-bold mb-2">Select Agent Category</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label id="cardTypeNew" class="card border-0 p-3 rounded-3 cursor-pointer text-start w-100 {{ $currentType === 'new' ? 'bg-primary bg-opacity-25 border-primary' : 'bg-dark' }}" style="border: 1px solid rgba(255,255,255,0.1) !important;">
                                    <input type="radio" name="agent_type" value="new" class="d-none" {{ $currentType === 'new' ? 'checked' : '' }} onchange="toggleAgentType('new')">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-user-plus text-primary fa-lg"></i>
                                        <div>
                                            <strong class="text-white d-block">Sign Up as a New Agent</strong>
                                            <small class="text-white-50">Applying to join Fuwa.ng company ecosystem</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label id="cardTypeExisting" class="card border-0 p-3 rounded-3 cursor-pointer text-start w-100 {{ $currentType === 'existing' ? 'bg-warning bg-opacity-25 border-warning' : 'bg-dark' }}" style="border: 1px solid rgba(255,255,255,0.1) !important;">
                                    <input type="radio" name="agent_type" value="existing" class="d-none" {{ $currentType === 'existing' ? 'checked' : '' }} onchange="toggleAgentType('existing')">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-building-user text-warning fa-lg"></i>
                                        <div>
                                            <strong class="text-white d-block">Sign Up as an Existing Agent</strong>
                                            <small class="text-white-50">Already part of company carrying out enrollments</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Company Code Field & Existing Agent Searchable Select -->
                    <div class="mb-4 {{ $currentType === 'existing' ? '' : 'd-none' }}" id="companyCodeBox">
                        <div class="card border-warning bg-warning bg-opacity-10 p-3 rounded-3 mb-3">
                            <label class="form-label text-warning fw-bold mb-1">
                                <i class="fa-solid fa-magnifying-glass me-1"></i>Search & Select Your Pre-Approved Existing Agent Profile
                            </label>
                            <p class="text-white-50 small mb-2">Type your name, email, phone number, or agent code to select your record from our master directory:</p>
                            
                            <div class="position-relative">
                                <input type="text" id="preApprovedSearch" class="form-control bg-dark text-white border-warning" placeholder="Type to search your name, email, or agent code..." autocomplete="off">
                                <div id="searchResults" class="list-group position-absolute w-100 shadow-lg d-none mt-1" style="z-index: 1050; max-height: 240px; overflow-y: auto; background: #0f172a; border: 1px solid rgba(245, 158, 11, 0.3);"></div>
                            </div>
                        </div>

                        <label class="form-label text-warning small fw-bold">Company Agent Code / Station License ID</label>
                        <input type="text" id="companyAgentCodeInput" name="company_agent_code" class="form-control border-warning" value="{{ old('company_agent_code') }}" placeholder="e.g. FUWA-LO001">
                        <small class="text-white-50">Selecting your name above automatically fills your Agent Code, Name, Email, and Phone Number below.</small>
                    </div>

                    <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2"><i class="fa-solid fa-address-card text-primary me-2"></i>Basic Identification Details</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Full Name (Primary Name)</label>
                            <input type="text" id="fullNameInput" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', Auth::user()?->fullname) }}" required placeholder="Enter full name">
                            <small class="text-white-50">Will be updated automatically from NIMC record upon NIN verification.</small>
                            @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Email Address</label>
                            <input type="email" id="emailInput" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', Auth::user()?->email) }}" required placeholder="your.email@example.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Phone Number</label>
                            <input type="text" id="phoneInput" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', Auth::user()?->number) }}" required placeholder="e.g. 08012345678">
                            @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">NIN Number (11 Digits)</label>
                            <input type="text" name="nin" maxlength="11" class="form-control @error('nin') is-invalid @enderror" value="{{ old('nin') }}" required placeholder="11-digit NIN">
                            <small class="text-info"><i class="fa-solid fa-shield-halved me-1"></i>Non-blocking: if NIN server is down, registration will proceed.</small>
                            @error('nin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">BVN (Bank Verification Number - 11 Digits)</label>
                            <input type="text" name="bvn" maxlength="11" class="form-control @error('bvn') is-invalid @enderror" value="{{ old('bvn') }}" required placeholder="11-digit BVN">
                            @error('bvn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">State of Residence / Station</label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}" required placeholder="e.g. Lagos">
                            @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Residential Address</label>
                            <textarea name="residential_address" rows="2" class="form-control @error('residential_address') is-invalid @enderror" required placeholder="Full residential home address">{{ old('residential_address') }}</textarea>
                            @error('residential_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Office / Station Address</label>
                            <textarea name="office_address" rows="2" class="form-control @error('office_address') is-invalid @enderror" required placeholder="Full station / office location address">{{ old('office_address') }}</textarea>
                            @error('office_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2"><i class="fa-solid fa-microchip text-warning me-2"></i>Hardware & Machine Equipment</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Do you have physical machine(s) to carry out enrollments?</label>
                            <select name="has_machine" class="form-select" onchange="toggleMachineIMEI(this.value)">
                                <option value="1" {{ old('has_machine', '1') === '1' ? 'selected' : '' }}>Yes — I have physical enrollment hardware/terminal</option>
                                <option value="0" {{ old('has_machine') === '0' ? 'selected' : '' }}>No — I need hardware provisioned by company</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="imeiBox">
                            <label class="form-label text-white small fw-bold">Machine IMEI / Device Terminal ID</label>
                            <input type="text" name="machine_imei" class="form-control @error('machine_imei') is-invalid @enderror" value="{{ old('machine_imei') }}" placeholder="e.g. 864201041234567">
                            @error('machine_imei') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="alert alert-dark border-secondary text-white-50 small p-3 rounded-3 mb-4">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>
                        <strong>Next Step:</strong> After submitting basic info, you will log in to complete your Agency KYC documents (Utility Bill, Passport Photo, Business Registration CAC doc, and NIMC Code of Conduct agreement).
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 text-white fw-bold">
                            <i class="fa-solid fa-arrow-right me-2"></i>Continue to Agency KYC Portal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
function toggleAgentType(type) {
    const box = document.getElementById('companyCodeBox');
    const cardNew = document.getElementById('cardTypeNew');
    const cardExisting = document.getElementById('cardTypeExisting');

    if (type === 'existing') {
        box.classList.remove('d-none');
        if (cardExisting) {
            cardExisting.className = 'card border-0 p-3 rounded-3 cursor-pointer text-start w-100 bg-warning bg-opacity-25 border-warning';
        }
        if (cardNew) {
            cardNew.className = 'card border-0 p-3 rounded-3 cursor-pointer text-start w-100 bg-dark';
        }
    } else {
        box.classList.add('d-none');
        if (cardNew) {
            cardNew.className = 'card border-0 p-3 rounded-3 cursor-pointer text-start w-100 bg-primary bg-opacity-25 border-primary';
        }
        if (cardExisting) {
            cardExisting.className = 'card border-0 p-3 rounded-3 cursor-pointer text-start w-100 bg-dark';
        }
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
    const codeInput = document.getElementById('companyAgentCodeInput');
    const nameInput = document.getElementById('fullNameInput');
    const emailInput = document.getElementById('emailInput');
    const phoneInput = document.getElementById('phoneInput');

    if (!searchInput || !resultsContainer) return;

    let debounceTimer = null;

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('d-none');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('agent.search_preapproved') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.agents && data.agents.length > 0) {
                        data.agents.forEach(agent => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action bg-dark text-white border-secondary p-3 text-start';
                            btn.innerHTML = `
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <strong class="text-warning">${agent.full_name || (agent.first_name + ' ' + agent.last_name)}</strong>
                                        <div class="small text-white-50">${agent.email} | ${agent.phone_number}</div>
                                    </div>
                                    <span class="badge bg-warning text-dark font-monospace">${agent.agent_code}</span>
                                </div>
                            `;

                            btn.addEventListener('click', function () {
                                if (codeInput) codeInput.value = agent.agent_code;
                                if (nameInput) nameInput.value = agent.full_name || (agent.first_name + ' ' + agent.last_name);
                                if (emailInput) emailInput.value = agent.email;
                                if (phoneInput) phoneInput.value = agent.phone_number;

                                searchInput.value = `${agent.full_name || (agent.first_name + ' ' + agent.last_name)} (${agent.agent_code})`;
                                resultsContainer.classList.add('d-none');
                            });

                            resultsContainer.appendChild(btn);
                        });
                        resultsContainer.classList.remove('d-none');
                    } else {
                        resultsContainer.innerHTML = '<div class="list-group-item bg-dark text-white-50 p-3 small">No matching agent record found in directory.</div>';
                        resultsContainer.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    console.error('PreApproved lookup error:', err);
                });
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });
});
</script>
@endsection

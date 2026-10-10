@extends('layouts.nexus')

@section('title', 'Agency Dashboard | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <!-- Top Welcome & Mode Switcher Bar -->
    <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.6), rgba(15, 23, 42, 0.8)); border: 1px solid rgba(59, 130, 246, 0.3) !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    @if($agent->picture_path)
                        <img src="{{ $agent->picture_url }}" alt="{{ $agent->full_name }}" class="rounded-circle border border-primary border-3" style="width: 70px; height: 70px; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-1" style="width: 26px; height: 26px; line-height: 1;" data-toggle="modal" data-bs-toggle="modal" data-target="#uploadPhotoModal" data-bs-target="#uploadPhotoModal" title="Update Profile Picture">
                            <i class="fa-solid fa-camera fa-xs"></i>
                        </button>
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 border border-warning border-3" style="width: 70px; height: 70px; background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute bottom-0 end-0 p-1" style="width: 26px; height: 26px; line-height: 1;" data-toggle="modal" data-bs-toggle="modal" data-target="#uploadPhotoModal" data-bs-target="#uploadPhotoModal" title="Upload Profile Picture Required">
                            <i class="fa-solid fa-upload fa-xs"></i>
                        </button>
                    @endif
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-primary px-3 py-1 rounded-pill text-uppercase font-monospace">Agency Mode Active</span>
                        @if($agent->isLicensePaid())
                            <span class="badge bg-success px-3 py-1 rounded-pill"><i class="fa-solid fa-certificate me-1"></i>Licensed Station</span>
                        @elseif($agent->isLicensePendingReview())
                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill"><i class="fa-solid fa-clock me-1"></i>License Under Review</span>
                        @else
                            <span class="badge bg-secondary px-3 py-1 rounded-pill"><i class="fa-solid fa-xmark me-1"></i>License Unpaid</span>
                        @endif
                        @if(!$agent->picture_path)
                            <span class="badge bg-danger px-3 py-1 rounded-pill"><i class="fa-solid fa-exclamation-triangle me-1"></i>Profile Photo Required for Full Activation</span>
                        @endif
                    </div>
                    <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-id-card-clip text-primary me-2"></i>Welcome, Agent {{ $agent->full_name }}</h3>
                    <p class="text-white-50 mb-0">Station: {{ $agent->meta['station_name'] ?? 'Primary Terminal' }} | IMEI: <code class="text-info">{{ $agent->machine_imei }}</code></p>
                </div>
            </div>
            <div>
                <a href="{{ route('agent.switch_mode', ['mode' => 'user']) }}" class="btn btn-outline-light rounded-pill px-4 py-2 font-weight-bold">
                    <i class="fa-solid fa-user me-2"></i>Switch to Ordinary User View
                </a>
            </div>
        </div>
    </div>

    <!-- Station License Status Banner -->
    @if(!$agent->isLicensePaid())
        <div class="alert border-0 rounded-4 p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.2), rgba(180, 83, 9, 0.3)); border: 1px solid rgba(234, 179, 8, 0.4) !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 text-center" style="background: rgba(234, 179, 8, 0.3); width: 54px; height: 54px;">
                    <i class="fa-solid fa-certificate text-warning fa-xl"></i>
                </div>
                <div>
                    @if($agent->isLicensePendingReview())
                        <span class="badge bg-warning text-dark font-monospace fw-bold mb-1">PAYMENT PROOF SUBMITTED</span>
                        <h5 class="text-white fw-bold mb-1">License Accreditation Verification In Progress</h5>
                        <p class="text-white-50 mb-0">Your offline transfer / pre-launch payment claim is being verified by the operations desk within 24 hours.</p>
                    @else
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark font-monospace fw-bold">PROMO FEE: ₦{{ number_format($effectiveFee, 2) }}</span>
                            <span class="badge bg-danger rounded-pill">Promo Ends Oct 10, 2026</span>
                        </div>
                        <h5 class="text-white fw-bold mb-1">Accredit Your Station License</h5>
                        <p class="text-white-50 mb-0">Pay your station accreditation fee online via Paystack, wallet balance, or submit offline transfer receipt / pre-launch payment proof.</p>
                    @endif
                </div>
            </div>
            <div>
                @if(!$agent->isLicensePendingReview())
                    <button type="button" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold" data-toggle="modal" data-bs-toggle="modal" data-target="#accreditLicenseModal" data-bs-target="#accreditLicenseModal">
                        <i class="fa-solid fa-shield-check me-2"></i>Accredit License Now
                    </button>
                @else
                    <button type="button" class="btn btn-outline-warning rounded-pill px-4 py-2" data-toggle="modal" data-bs-toggle="modal" data-target="#accreditLicenseModal" data-bs-target="#accreditLicenseModal">
                        <i class="fa-solid fa-eye me-2"></i>View / Update Claim
                    </button>
                @endif
            </div>
        </div>
    @endif

    @if(!$agent->picture_path)
        <div class="alert alert-danger border-0 rounded-4 p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4) !important;">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-camera-retro fa-2x text-danger"></i>
                <div>
                    <h5 class="text-white fw-bold mb-1">Upload Your Profile Picture to Fully Activate Account</h5>
                    <p class="text-white-50 mb-0">NIMC regulations require all active enrollment agents to maintain a clear profile picture on their agency account.</p>
                </div>
            </div>
            <button type="button" class="btn btn-danger rounded-pill px-4 py-2 fw-bold" data-toggle="modal" data-bs-toggle="modal" data-target="#uploadPhotoModal" data-bs-target="#uploadPhotoModal">
                <i class="fa-solid fa-upload me-2"></i>Upload Profile Picture Now
            </button>
        </div>
    @endif

    <!-- Profile Photo Upload Modal -->
    <div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4 border border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-header-title fw-bold text-white mb-0" id="uploadPhotoModalLabel"><i class="fa-solid fa-id-badge text-primary me-2"></i>Upload Agent Profile Photo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: transparent; border: 0; font-size: 1.5rem; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('agent.onboarding.upload_docs') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-white-50 small mb-3">Upload a clear, recent passport-style portrait photo of yourself for your official enrollment agent identification.</p>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Select Photo (PNG, JPG, WEBP - Max 5MB)</label>
                            <input type="file" name="picture" accept="image/png,image/jpeg,image/webp" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Save & Activate Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MVA Showcase Banner if set -->
    @if($mvaAgent)
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.2), rgba(180, 83, 9, 0.3)); border: 1px solid rgba(234, 179, 8, 0.4) !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 text-center" style="background: rgba(234, 179, 8, 0.3); width: 60px; height: 60px;">
                    <i class="fa-solid fa-trophy text-warning fa-2x"></i>
                </div>
                <div>
                    <span class="badge bg-warning text-dark font-monospace fw-bold mb-1">MOST VALUABLE AGENT (MVP)</span>
                    <h5 class="text-white fw-bold mb-0">{{ $mvaAgent->full_name }} @if($mvaAgent->id === $agent->id) <span class="badge bg-success ms-2">YOU!</span> @endif</h5>
                    <p class="text-white-50 small mb-0">Recognized for top enrollment volume & outstanding agency service delivery with {{ number_format($mvaAgent->total_enrollments) }} total citizen enrollments recorded.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-white-50 small fw-bold text-uppercase">Total Enrollments</span>
                    <i class="fa-solid fa-users-viewfinder text-primary fa-lg"></i>
                </div>
                <h2 class="text-white fw-bold mb-0">{{ number_format($agent->total_enrollments) }}</h2>
                <small class="text-white-50">All time processed</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-white-50 small fw-bold text-uppercase">Monthly Volume</span>
                    <i class="fa-solid fa-chart-line text-success fa-lg"></i>
                </div>
                <h2 class="text-white fw-bold mb-0">{{ number_format($agent->monthly_enrollments) }}</h2>
                <small class="text-white-50">This month's count</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-white-50 small fw-bold text-uppercase">Terminal Status</span>
                    <i class="fa-solid fa-microchip text-info fa-lg"></i>
                </div>
                <h5 class="text-success fw-bold mb-0"><i class="fa-solid fa-circle me-1 small"></i>Active & Online</h5>
                <small class="text-white-50">IMEI: {{ $agent->machine_imei }}</small>
            </div>
        </div>
    </div>

    <!-- Performance Target & Account Health Status -->
    <div class="row g-3 mb-4">
        <!-- Monthly Target & Quota Progress -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.25), rgba(15, 23, 42, 0.4)); border: 1px solid rgba(59, 130, 246, 0.25) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="badge {{ $tierInfo['badge'] }} px-3 py-1 font-monospace mb-1">{{ $tierInfo['name'] }}</span>
                        <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-bullseye text-primary me-2"></i>Monthly Target Progress</h5>
                    </div>
                    <div class="text-end">
                        <span class="fs-4 fw-bold text-white">{{ number_format($agent->monthly_enrollments ?? 0) }}</span>
                        <span class="text-white-50">/ {{ number_format($monthlyTarget) }} Target</span>
                    </div>
                </div>

                <div class="progress rounded-pill mb-2" style="height: 12px; background: rgba(255,255,255,0.1);">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary rounded-pill" role="progressbar" style="width: {{ $targetProgress }}%;" aria-valuenow="{{ $targetProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="d-flex align-items-center justify-content-between text-white-50 small">
                    <span><i class="fa-solid fa-award text-warning me-1"></i>Active Perk: <strong>{{ $tierInfo['bonus'] }}</strong></span>
                    @if($tierInfo['needed'] > 0)
                        <span><strong>{{ $tierInfo['needed'] }}</strong> more to reach {{ $tierInfo['next'] }}</span>
                    @else
                        <span class="text-success"><i class="fa-solid fa-check-circle me-1"></i>Peak Tier Achieved!</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Account Health & Compliance Status -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-shield-heart text-danger me-2"></i>Account Compliance Health</h5>
                    <span class="badge {{ $healthScore >= 90 ? 'bg-success' : ($healthScore >= 70 ? 'bg-warning text-dark' : 'bg-danger') }} px-3 py-1 font-monospace">
                        {{ $healthScore }}% Health
                    </span>
                </div>

                <div class="row g-2 small">
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-start" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <span class="text-white-50 d-block" style="font-size: 11px;">Profile Portrait</span>
                            @if($agent->picture_path)
                                <span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Uploaded</span>
                            @else
                                <span class="text-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i>Missing</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-start" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <span class="text-white-50 d-block" style="font-size: 11px;">NIN Identity</span>
                            @if($agent->nin_verified)
                                <span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Verified</span>
                            @else
                                <span class="text-warning fw-bold"><i class="fa-solid fa-clock me-1"></i>Pending</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-start" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <span class="text-white-50 d-block" style="font-size: 11px;">Terminal Linked</span>
                            @if($agent->machine_imei)
                                <span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Online</span>
                            @else
                                <span class="text-white-50"><i class="fa-solid fa-minus me-1"></i>None</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-start" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <span class="text-white-50 d-block" style="font-size: 11px;">NIMC Accredit</span>
                            <span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Authorized</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Tools & NIN Suite -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-bolt text-warning me-2"></i>Agency Enrollment Actions</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('services.nin.suite') }}" class="btn btn-outline-primary rounded-3 w-100 p-3 text-start d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-white fw-bold mb-1">NIN Verification</h6>
                                <span class="text-white-50 small">Verify NIN / IPE / Phone</span>
                            </div>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('services.nin') }}?mode=demographic" class="btn btn-outline-info rounded-3 w-100 p-3 text-start d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-white fw-bold mb-1">Demographic Lookup</h6>
                                <span class="text-white-50 small">Verify by Name & DOB</span>
                            </div>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('services.nin.modification') }}" class="btn btn-outline-success rounded-3 w-100 p-3 text-start d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-white fw-bold mb-1">NIN Modification</h6>
                                <span class="text-white-50 small">Name/DOB updates</span>
                            </div>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('agent.issues.index') }}" class="btn btn-outline-warning rounded-3 w-100 p-3 text-start d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-white fw-bold mb-1">Issue Helpdesk</h6>
                                <span class="text-white-50 small">Report fault & upload proof</span>
                            </div>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    {{-- Digital Agent ID Card (Backend & UI ready; kept hidden from display for future rollout as requested)
                    <div class="col-md-3">
                        <a href="{{ route('agent.id_card') }}" class="btn btn-outline-light rounded-3 w-100 p-3 text-start d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-white fw-bold mb-1">Official ID Card</h6>
                                <span class="text-white-50 small">Printable Digital Credential</span>
                            </div>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Regional Coordinator & Field Operations Desk (Getting in touch) -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card border-0 rounded-4 p-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(15, 23, 42, 0.6)); border: 1px solid rgba(16, 185, 129, 0.3) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-3 text-center" style="background: rgba(16, 185, 129, 0.2); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-headset text-success fa-xl"></i>
                        </div>
                        <div>
                            <span class="badge bg-success text-white mb-1 small text-uppercase font-monospace"><i class="fa-solid fa-satellite-dish me-1"></i>Field Operations Desk</span>
                            <h5 class="text-white fw-bold mb-1">{{ $coordinatorName }}</h5>
                            <p class="text-white-50 small mb-0">Direct line for hardware issues, biometric sync exceptions, and LGA/State coordinator assistance.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success rounded-pill px-4 py-2 fw-bold">
                            <i class="fa-brands fa-whatsapp me-2"></i>WhatsApp Desk
                        </a>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}" class="btn btn-outline-light rounded-pill px-3 py-2">
                            <i class="fa-solid fa-phone me-1"></i>Call Support
                        </a>
                        <a href="{{ route('agent.issues.create') }}" class="btn btn-outline-warning rounded-pill px-3 py-2">
                            <i class="fa-solid fa-ticket me-1"></i>Log Issue Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Agent Leaderboard -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-ranking-star text-warning me-2"></i>Top Agents Leaderboard</h5>
                    <span class="text-white-50 small">Ranked by Total Enrollments</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                        <thead>
                            <tr class="text-white-50 border-bottom border-secondary">
                                <th>#</th>
                                <th>Agent Name</th>
                                <th>Office Location</th>
                                <th class="text-end">Total Enrollments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaderboard as $index => $leadAgent)
                                <tr>
                                    <td>
                                        @if($index === 0)
                                            <span class="badge bg-warning text-dark rounded-circle px-2 py-1"><i class="fa-solid fa-crown"></i> 1</span>
                                        @elseif($index === 1)
                                            <span class="badge bg-light text-dark rounded-circle px-2 py-1">2</span>
                                        @elseif($index === 2)
                                            <span class="badge bg-secondary rounded-circle px-2 py-1">3</span>
                                        @else
                                            <span class="text-white-50 ms-1">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-white">{{ $leadAgent->full_name }}</strong>
                                        @if($leadAgent->id === $agent->id)
                                            <span class="badge bg-success ms-1">You</span>
                                        @endif
                                        @if($leadAgent->is_mva_of_month || $index === 0)
                                            <span class="badge bg-warning text-dark ms-1"><i class="fa-solid fa-star me-1"></i>MVP</span>
                                        @endif
                                    </td>
                                    <td class="text-white-50 small">{{ \Illuminate\Support\Str::limit($leadAgent->office_address, 25) }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($leadAgent->total_enrollments) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-white-50 py-3">No active leaderboard data yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Agent Broadcasts & Announcements -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-bullhorn text-danger me-2"></i>Admin Broadcasts</h5>

                @forelse($broadcasts as $broadcast)
                    <div class="border-bottom border-secondary pb-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <strong class="text-white">{{ $broadcast->subject }}</strong>
                            <small class="text-white-50">{{ $broadcast->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="text-white-50 small mb-0">{{ \Illuminate\Support\Str::limit($broadcast->message, 120) }}</p>
                    </div>
                @empty
                    <p class="text-white-50 small mb-0">No active announcements from administration at this time.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal: Accredit Station License -->
<div class="modal fade" id="accreditLicenseModal" tabindex="-1" aria-labelledby="accreditLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4" style="background: #111827; border: 1px solid rgba(255,255,255,0.15) !important;">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-white fw-bold" id="accreditLicenseModalLabel">
                    <i class="fa-solid fa-certificate text-warning me-2"></i>Accredit NIN Station License
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Promo rate header -->
                <div class="alert border-0 rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(180, 83, 9, 0.25)); border: 1px solid rgba(234, 179, 8, 0.4) !important;">
                    <div>
                        <span class="badge bg-warning text-dark font-monospace fw-bold mb-1">PROMO RATE: ₦{{ number_format($effectiveFee ?? 100000, 2) }}</span>
                        <strong class="text-white d-block">Special Enrollment License Promo Ending October 10th, 2026</strong>
                        <small class="text-white-50">Select your preferred payment channel to complete accreditation.</small>
                    </div>
                </div>

                <!-- 4 Tabs -->
                <ul class="nav nav-pills mb-3 gap-2" id="dashLicensePaymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-3 py-2 fw-bold text-xs" id="dtab-paystack-btn" data-bs-toggle="pill" data-bs-target="#dtab-paystack" data-toggle="pill" data-target="#dtab-paystack" type="button" role="tab">
                            <i class="fa-solid fa-credit-card me-1 text-primary"></i>1. Pay Online (Paystack)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-2 fw-bold text-xs" id="dtab-wallet-btn" data-bs-toggle="pill" data-bs-target="#dtab-wallet" data-toggle="pill" data-target="#dtab-wallet" type="button" role="tab">
                            <i class="fa-solid fa-wallet me-1 text-info"></i>2. Pay via Wallet
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-2 fw-bold text-xs" id="dtab-offline-btn" data-bs-toggle="pill" data-bs-target="#dtab-offline" data-toggle="pill" data-target="#dtab-offline" type="button" role="tab">
                            <i class="fa-solid fa-receipt me-1 text-warning"></i>3. Paid Offline (Proof)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-2 fw-bold text-xs" id="dtab-legacy-btn" data-bs-toggle="pill" data-bs-target="#dtab-legacy" data-toggle="pill" data-target="#dtab-legacy" type="button" role="tab">
                            <i class="fa-solid fa-clock-rotate-left me-1 text-success"></i>4. Pre-Launch Paid
                        </button>
                    </li>
                </ul>

                <div class="tab-content border border-secondary rounded-4 p-4 bg-dark" id="dashLicensePaymentTabsContent">
                    <!-- Tab 1: Paystack Online -->
                    <div class="tab-pane fade show active" id="dtab-paystack" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h6 class="text-white fw-bold mb-1"><i class="fa-solid fa-bolt text-warning me-2"></i>Instant Accreditation via Paystack</h6>
                                <p class="text-white-50 small mb-0">Pay with Debit Card, Transfer, USSD or Apple Pay. Accreditation activates immediately on confirmation.</p>
                            </div>
                            <div>
                                <button type="button" id="dashPaystackPayBtn" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                                    <i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee ?? 100000, 2) }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Wallet Balance -->
                    <div class="tab-pane fade" id="dtab-wallet" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h6 class="text-white fw-bold mb-1"><i class="fa-solid fa-wallet text-info me-2"></i>Pay from Fuwa Wallet</h6>
                                <p class="text-white-50 small mb-1">Current Wallet Balance: <strong class="text-warning">₦{{ number_format($walletBalance ?? 0, 2) }}</strong>.</p>
                                @if(($walletBalance ?? 0) < ($effectiveFee ?? 100000))
                                    <small class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Insufficient balance. Fund your wallet or pay via Paystack.</small>
                                @endif
                            </div>
                            <div>
                                <form action="{{ route('agent.license.pay_wallet') }}" method="POST" onsubmit="return confirm('Confirm payment of ₦{{ number_format($effectiveFee ?? 100000, 2) }} from your wallet balance?');">
                                    @csrf
                                    <button type="submit" class="btn btn-info text-dark rounded-pill px-4 py-2 fw-bold" {{ ($walletBalance ?? 0) < ($effectiveFee ?? 100000) ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-check me-2"></i>Pay ₦{{ number_format($effectiveFee ?? 100000, 2) }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Offline Proof Upload -->
                    <div class="tab-pane fade" id="dtab-offline" role="tabpanel">
                        <div class="mb-3 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                            <h6 class="text-warning fw-bold mb-2"><i class="fa-solid fa-building-columns me-2"></i>Official Bank Details:</h6>
                            <div class="row g-2 text-white small">
                                <div class="col-md-4">Bank: <strong class="text-white">{{ $manualFunding->bank_name ?? 'Zenith Bank' }}</strong></div>
                                <div class="col-md-4">Account No: <strong class="text-warning font-monospace fs-6">{{ $manualFunding->account_number ?? '1234567890' }}</strong></div>
                                <div class="col-md-4">Account Name: <strong class="text-white">{{ $manualFunding->account_name ?? 'Fuwa Logistics Services' }}</strong></div>
                            </div>
                        </div>

                        <form action="{{ route('agent.license.upload_proof') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Amount Paid (₦)</label>
                                    <input type="number" step="0.01" name="amount_paid" class="form-control bg-transparent text-white border-secondary" value="{{ $effectiveFee ?? 100000 }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Bank Reference / Session ID</label>
                                    <input type="text" name="payment_reference" class="form-control bg-transparent text-white border-secondary" placeholder="e.g. 000013241007..." required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-white-50 small">Upload Payment Receipt / Proof (PDF, JPG, PNG)</label>
                                    <input type="file" name="payment_proof" class="form-control bg-transparent text-white border-secondary" accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark">
                                        <i class="fa-solid fa-upload me-2"></i>Submit Offline Proof for Verification
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 4: Pre-Website Legacy Claim -->
                    <div class="tab-pane fade" id="dtab-legacy" role="tabpanel">
                        <div class="alert alert-info border-0 rounded-3 p-3 mb-3" style="background: rgba(14, 165, 233, 0.15); color: #bae6fd;">
                            <h6 class="fw-bold mb-1"><i class="fa-solid fa-info-circle me-2"></i>Paid Prior to Website Launch?</h6>
                            <p class="mb-0 small">If you paid for your license before this portal launched, submit your payment records below. An admin will verify with the legacy accounting records.</p>
                        </div>

                        <form action="{{ route('agent.license.claim_legacy') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Approximate Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control bg-transparent text-white border-secondary" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Amount Paid (₦)</label>
                                    <input type="number" step="0.01" name="amount_paid" class="form-control bg-transparent text-white border-secondary" placeholder="100000" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Original Bank / Transaction Reference</label>
                                    <input type="text" name="legacy_reference" class="form-control bg-transparent text-white border-secondary" placeholder="e.g. Bank slip reference, teller number...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Supporting Receipt / Teller (Optional)</label>
                                    <input type="file" name="legacy_proof" class="form-control bg-transparent text-white border-secondary" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-white-50 small">Remarks / Details for Admin Verification</label>
                                    <textarea name="remarks" rows="2" class="form-control bg-transparent text-white border-secondary" placeholder="Provide any details about the admin staff or coordinator you paid to..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Pre-Launch Verification Claim
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dashPayBtn = document.getElementById('dashPaystackPayBtn');
    if (!dashPayBtn) return;

    dashPayBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        dashPayBtn.disabled = true;
        dashPayBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Initializing Paystack...';

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
                dashPayBtn.disabled = false;
                dashPayBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee ?? 100000, 2) }}';
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
                    dashPayBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Verifying Accreditation...';
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
                            dashPayBtn.disabled = false;
                            dashPayBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee ?? 100000, 2) }}';
                        }
                    })
                    .catch(err => {
                        alert('Network error while verifying payment.');
                        dashPayBtn.disabled = false;
                        dashPayBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee ?? 100000, 2) }}';
                    });
                },
                onClose: function() {
                    dashPayBtn.disabled = false;
                    dashPayBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee ?? 100000, 2) }}';
                }
            });

            handler.openIframe();
        } catch (err) {
            alert('Error connecting to payment gateway: ' + err.message);
            dashPayBtn.disabled = false;
            dashPayBtn.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Pay ₦{{ number_format($effectiveFee ?? 100000, 2) }}';
        }
    });
});
</script>
@endsection

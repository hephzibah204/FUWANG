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
                        <img src="{{ asset('storage/' . $agent->picture_path) }}" alt="{{ $agent->full_name }}" class="rounded-circle border border-primary border-3" style="width: 70px; height: 70px; object-fit: cover;">
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
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary px-3 py-1 rounded-pill text-uppercase font-monospace">Agency Mode Active</span>
                        @if(!$agent->picture_path)
                            <span class="badge bg-danger px-3 py-1 rounded-pill"><i class="fa-solid fa-exclamation-triangle me-1"></i>Profile Photo Required for Full Activation</span>
                        @endif
                    </div>
                    <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-id-card-clip text-primary me-2"></i>Welcome, Agent {{ $agent->full_name }}</h3>
                    <p class="text-white-50 mb-0">Station: {{ $agent->meta['station_name'] ?? 'Primary Terminal' }} | IMEI: <code class="text-info">{{ $agent->machine_imei }}</code></p>
                </div>
            </div>
            <div>
                <form action="{{ route('agent.switch_mode') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="mode" value="user">
                    <button type="submit" class="btn btn-outline-light rounded-pill px-4 py-2">
                        <i class="fa-solid fa-user me-2"></i>Switch to Ordinary User View
                    </button>
                </form>
            </div>
        </div>
    </div>

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
                    <span class="badge bg-warning text-dark font-monospace fw-bold mb-1">MOST VALUABLE AGENT OF THE MONTH</span>
                    <h5 class="text-white fw-bold mb-0">{{ $mvaAgent->full_name }} @if($mvaAgent->id === $agent->id) <span class="badge bg-success ms-2">YOU!</span> @endif</h5>
                    <p class="text-white-50 small mb-0">Recognized for top enrollment volume & outstanding agency service delivery with {{ number_format($mvaAgent->monthly_enrollments) }} enrollments this month.</p>
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
                    <span class="text-white-50 small">Monthly Standings</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                        <thead>
                            <tr class="text-white-50 border-bottom border-secondary">
                                <th>#</th>
                                <th>Agent Name</th>
                                <th>Office Location</th>
                                <th class="text-end">Enrollments</th>
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
                                        @if($leadAgent->is_mva_of_month)
                                            <span class="badge bg-warning text-dark ms-1"><i class="fa-solid fa-star me-1"></i>MVA</span>
                                        @endif
                                    </td>
                                    <td class="text-white-50 small">{{ \Illuminate\Support\Str::limit($leadAgent->office_address, 25) }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($leadAgent->monthly_enrollments) }}</td>
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
@endsection

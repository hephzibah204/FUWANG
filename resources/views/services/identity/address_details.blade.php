@extends('layouts.nexus')

@section('title', 'Address Verification Details | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.1), rgba(202, 138, 4, 0.05)); border-color: rgba(234, 179, 8, 0.2);">
        <div class="sh-icon" style="background: rgba(234, 179, 8, 0.15); color: #eab308;">
            <i class="fa-solid fa-house-circle-check"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Observation Report</h1>
            <p class="text-muted small">Reference: {{ $data['reference'] }} | ID: {{ $data['id'] }}</p>
        </div>
        <div class="sh-badges ml-auto">
            <span class="badge badge-{{ strtolower($data['status']['status'] ?? '') === 'completed' ? 'success' : 'warning' }}-soft px-3 py-2">
                {{ strtoupper($data['status']['status'] ?? 'PENDING') }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Report -->
        <div class="col-lg-8">
            <div class="panel-card p-4 mb-4">
                <h6 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Verification Summary</h6>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="x-small text-muted uppercase d-block mb-1">Applicant Name</label>
                        <strong class="text-white">{{ $data['applicant']['firstname'] }} {{ $data['applicant']['lastname'] }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="x-small text-muted uppercase d-block mb-1">Subject Identity</label>
                        <strong class="text-white">{{ strtoupper($data['applicant']['idType'] ?? 'ID') }}: {{ $data['applicant']['idNumber'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="x-small text-muted uppercase d-block mb-1">Verified Address</label>
                        <p class="text-white mb-0">{{ $data['street'] }}, {{ $data['lga'] }}, {{ $data['state'] }}</p>
                    </div>
                </div>

                @if(isset($data['neighbor']))
                <div class="p-3 rounded mb-4" style="background: rgba(255,255,255,0.02); border-left: 4px solid #4f46e5;">
                    <h6 class="x-small font-weight-bold text-primary mb-2 uppercase">Neighbor Confirmation</h6>
                    <p class="small text-white mb-1">"{{ $data['neighbor']['comment'] ?? 'Identity confirmed.' }}"</p>
                    <span class="x-small text-muted">- {{ $data['neighbor']['name'] }} ({{ $data['neighbor']['phone'] }})</span>
                </div>
                @endif

                <h6 class="h6 font-weight-bold mb-3 mt-5">Site Photography</h6>
                <div class="row g-3">
                    @forelse($data['photos'] ?? [] as $photo)
                    <div class="col-md-4">
                        <div class="rounded overflow-hidden" style="height: 180px; border: 1px solid rgba(255,255,255,0.08);">
                            <img src="{{ $photo }}" class="w-100 h-100" style="object-fit: cover; cursor: pointer;" onclick="viewImage('{{ $photo }}')">
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4 border rounded border-dashed opacity-50">
                        <i class="fa-solid fa-camera fa-2x mb-2 text-muted"></i>
                        <p class="small text-muted mb-0">No photos uploaded by field agent yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4 text-center">
                <div class="mx-auto rounded overflow-hidden mb-3" style="width: 120px; height: 140px; border: 2px solid #eab308;">
                    <img src="{{ $data['applicant']['photo'] ?? 'https://ui-avatars.com/api/?name='.$data['applicant']['firstname'] }}" class="w-100 h-100" style="object-fit: cover;">
                </div>
                <h5 class="h6 font-weight-bold mb-1">{{ $data['applicant']['firstname'] }}</h5>
                <span class="badge badge-dark-soft x-small text-muted mb-3">{{ $data['applicant']['gender'] ?? '' }} | {{ $data['applicant']['birthdate'] ?? '' }}</span>
            </div>

            <div class="panel-card p-4">
                <h6 class="x-small font-weight-bold uppercase mb-3 text-muted">Field Metadata</h6>
                <div class="mb-3">
                    <label class="x-small text-muted d-block mb-1">Coordinates</label>
                    <code class="text-primary">{{ $data['lattitude'] ?? '0.00' }}, {{ $data['longitude'] ?? '0.00' }}</code>
                </div>
                <div class="mb-3">
                    <label class="x-small text-muted d-block mb-1">Submission Date</label>
                    <span class="small text-white">{{ $data['createdAt'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <label class="x-small text-muted d-block mb-1">Completion Date</label>
                    <span class="small text-white">{{ $data['completedAt'] ?? 'In Progress' }}</span>
                </div>
            </div>

            <button class="btn btn-outline-light btn-block mt-4" onclick="window.print()">
                <i class="fa-solid fa-print mr-2"></i> Print Official Report
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 20px; }
    .badge-success-soft { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge-warning-soft { background: rgba(234, 179, 8, 0.1); color: #eab308; }
    .uppercase { text-transform: uppercase; }
    .x-small { font-size: 0.75rem; }
    .border-white-5 { border-color: rgba(255,255,255,0.05) !important; }
    .opacity-50 { opacity: 0.5; }
    .tracking-wider { letter-spacing: 1px; }

    @media print {
        .sidebar, .navbar, .btn, .sh-badges { display: none !important; }
        .main-content { padding: 0 !important; margin: 0 !important; }
        .panel-card { border: 1px solid #eee !important; background: #fff !important; color: #000 !important; box-shadow: none !important; }
        .text-white, h1, h2, h3, h4, h5, h6, strong { color: #000 !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    function viewImage(url) {
        Swal.fire({
            imageUrl: url,
            imageAlt: 'Field Agent Capture',
            showCloseButton: true,
            showConfirmButton: false,
            background: '#0a0a0f',
            imageWidth: 'auto',
            imageHeight: 'auto',
            padding: '10px'
        });
    }
</script>
@endpush

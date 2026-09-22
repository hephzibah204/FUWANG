@extends('layouts.nexus')

@section('title', 'Upload Pre-Approved Enrollment Agents | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-file-excel text-warning me-2"></i>Batch Upload Pre-Approved Existing Agents</h3>
            <p class="text-white-50 mb-0">Upload Excel/CSV roster of accredited agents to enable instant autocomplete during onboarding registration.</p>
        </div>
        <div>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Agents List
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 text-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <span class="text-white-50 small text-uppercase">Total Master Directory Roster</span>
                <h2 class="display-6 text-warning fw-bold mb-0 mt-1">{{ number_format($preApprovedCount) }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 text-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <span class="text-white-50 small text-uppercase">Claimed / Registered Agents</span>
                <h2 class="display-6 text-success fw-bold mb-0 mt-1">{{ number_format($claimedCount) }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-4 text-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <span class="text-white-50 small text-uppercase">Available / Unclaimed Slots</span>
                <h2 class="display-6 text-primary fw-bold mb-0 mt-1">{{ number_format($unclaimedCount) }}</h2>
            </div>
        </div>
    </div>

    <!-- Upload Box -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Upload Agent Spreadsheet (.xlsx, .csv)</h5>

                <form action="{{ route('admin.agents.upload_preapproved.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label text-white small fw-bold">Select File</label>
                        <input type="file" name="agent_file" class="form-control border-secondary text-white" accept=".xlsx,.xls,.csv" required>
                        <small class="text-white-50 d-block mt-2">
                            Supported columns in Excel: <code>First Name</code>, <code>Last Name</code>, <code>Email</code>, <code>Phone Number</code>, <code>Agent Code</code>.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark">
                        <i class="fa-solid fa-upload me-2"></i>Upload & Process Spreadsheet
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-circle-info text-info me-2"></i>File Structure Instructions</h5>
                <p class="text-white-50 small mb-3">Ensure your Excel or CSV file contains the following column layout (header row within first 5 lines):</p>

                <div class="table-responsive">
                    <table class="table table-dark table-sm table-bordered small">
                        <thead>
                            <tr class="text-warning">
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Agent Code</th>
                            </tr>
                        </thead>
                        <tbody class="text-white-50">
                            <tr>
                                <td>Loveth</td>
                                <td>Okafor</td>
                                <td>Chisazan@gmail.com</td>
                                <td>07036857727</td>
                                <td>FUWA-LO001</td>
                            </tr>
                            <tr>
                                <td>Abdullahi</td>
                                <td>Shuaibu</td>
                                <td>Abdullahishuaibu1005@gmail.com</td>
                                <td>07041191878</td>
                                <td>FUWA-AS002</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Added Roster Preview -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-list text-primary me-2"></i>Recent Master Roster Preview (Top 20)</h5>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th>#</th>
                        <th>Agent Code</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Status</th>
                        <th>Claimed At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-warning text-dark font-monospace">{{ $item->agent_code }}</span></td>
                            <td class="fw-bold text-white">{{ $item->full_name }}</td>
                            <td class="text-white-50">{{ $item->email }}</td>
                            <td class="text-white-50">{{ $item->formatted_phone }}</td>
                            <td>
                                @if($item->is_claimed)
                                    <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3">Claimed</span>
                                @else
                                    <span class="badge bg-info bg-opacity-25 text-info rounded-pill px-3">Available</span>
                                @endif
                            </td>
                            <td class="text-white-50 small">{{ $item->claimed_at ? $item->claimed_at->format('M d, Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-white-50 py-4">No pre-approved records imported yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('layouts.nexus')

@section('title', 'IPE Clearance | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(217, 119, 6, 0.06)); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 16px; padding: 24px;">
        <div class="d-flex align-items-center gap-3">
            <div class="sh-icon" style="width: 56px; height: 56px; border-radius: 14px; background: rgba(245, 158, 11, 0.18); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.35); display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0;">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="sh-text flex-grow-1">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                    <h1 class="h4 font-weight-bold mb-0 text-white">IPE Clearance (Internal Processing Error)</h1>
                    <span class="badge py-2 px-3 font-weight-bold text-dark" style="background: #f59e0b; font-size: 0.85rem; border-radius: 999px;">
                        <i class="fa-solid fa-bolt mr-1"></i> Result: {{ in_array(($ipeMode ?? 'manual'), ['robosttech', 'dataverify', 'auto', 'api'], true) ? 'Automated in a few minutes' : 'In less than 24 hours' }}
                    </span>
                </div>
                <p class="text-white-50 small mb-0">
                    Fix NIMC enrollment issues easily using <strong>only your NIMC Tracking ID</strong>. The NIN number is <strong>not required</strong> for IPE clearance.
                </p>
            </div>
        </div>
    </div>

    @if(session('status') || session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3 text-white" style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4);">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <div>{{ session('status') ?? session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 rounded-3 text-white" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4);">
            <i class="fa-solid fa-circle-xmark fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Main Panel Card with Tabs -->
    <div class="panel-card p-4 mb-4" id="mainCard">
        <!-- Tab Navigation -->
        <ul class="nav nav-pills mb-4 gap-2 border-bottom border-white-5 pb-3" id="clearanceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 py-2 font-weight-bold rounded-3" id="submit-tab" data-bs-toggle="pill" data-bs-target="#submitPane" type="button" role="tab" onclick="switchTab('submit')">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Submit New Clearance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-2 font-weight-bold rounded-3" id="track-tab" data-bs-toggle="pill" data-bs-target="#trackPane" type="button" role="tab" onclick="switchTab('track')">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Track Clearance Status
                </button>
            </li>
        </ul>

        <!-- Submit Tab Pane -->
        <div id="submitPane">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 font-weight-bold m-0 text-white">
                    <i class="fa-solid fa-id-badge mr-2 text-warning"></i> Select IPE Category &amp; Tracking ID
                </h2>
                <span class="badge badge-warning py-2 px-3 text-dark font-weight-bold" id="priceBadge" style="font-size: 0.95rem;">
                    Fee: ₦{{ number_format($price ?? 700, 2) }}
                </span>
            </div>

            <!-- Helpful Notice -->
            <div class="alert alert-info py-2.5 px-3 mb-4 rounded-3 d-flex align-items-center gap-2" style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.25); color: #7dd3fc;">
                <i class="fa-solid fa-circle-info fa-lg flex-shrink-0"></i>
                <div class="small">
                    <strong>NIN Number is NOT required:</strong> Only provide your NIMC enrollment Tracking ID (e.g. <code>BTX947E60001020</code>). Once cleared, you will receive a new Tracking ID to retrieve your identity record.
                </div>
            </div>

            <form id="clearanceForm" action="{{ route('services.clearance.verify') }}" method="POST">
                @csrf
                <input type="hidden" name="mode" id="formMode" value="submit">

                <div class="row">
                    <!-- Select IPE Category -->
                    <div class="col-md-6 mb-3">
                        <label for="category" class="font-weight-600 mb-2 small text-muted">Select IPE Category <span class="text-danger">*</span></label>
                        <select id="category" name="category" class="form-control form-control-lg custom-select-styled" required>
                            <option value="">-- Select IPE Category --</option>
                            <option value="New Enrollment for ID Retrieval">New Enrollment for ID Retrieval</option>
                            <option value="Improcessing Error" selected>Improcessing Error</option>
                            <option value="Enrollment Is still Being Processed">Enrollment Is still Being Processed</option>
                        </select>
                    </div>

                    <!-- Enter Tracking ID -->
                    <div class="col-md-6 mb-3">
                        <label for="number" class="font-weight-600 mb-2 small text-muted">NIMC Enrollment Tracking ID <span class="text-danger">*</span></label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-hashtag"></i>
                            <input type="text" id="number" name="number" class="form-control form-control-lg" placeholder="e.g. BTX947E60001020" required autocomplete="off">
                        </div>
                    </div>

                    <!-- Optional Remarks -->
                    <div class="col-12 mb-3">
                        <label for="remarks" class="font-weight-600 mb-2 small text-muted">Additional Remarks / Notes (Optional)</label>
                        <input type="text" id="remarks" name="remarks" class="form-control" placeholder="Any specific note regarding this clearance" style="background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.12); color: #fff;">
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12 d-flex flex-wrap align-items-center gap-2 mt-2">
                        <a href="{{ route('services.nin.suite') }}" class="btn btn-warning text-white px-4 font-weight-bold mr-2" style="background-color: #f97316; border-color: #f97316; border-radius: 8px;">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Go Back
                        </a>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="submitBtn" style="background-color: #2563eb; border-color: #2563eb; border-radius: 8px;">
                            <i class="fa-solid fa-paper-plane mr-1"></i> <span id="btnText">Submit Tracking</span>
                        </button>
                        <button type="button" class="btn btn-outline-info ml-auto" onclick="quickCheckFromInput()">
                            <i class="fa-solid fa-magnifying-glass mr-1"></i> Track Inputted ID
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Track Status Tab Pane -->
        <div id="trackPane" style="display: none;">
            <div class="mb-3">
                <h2 class="h5 font-weight-bold m-0 text-white">
                    <i class="fa-solid fa-magnifying-glass mr-2 text-info"></i> Instant IPE Clearance Status Tracker
                </h2>
                <p class="text-muted small mb-0 mt-1">
                    Enter your NIMC Tracking ID or System Reference ID below to fetch the latest clearance progress and your newly assigned Tracking ID.
                </p>
            </div>

            <div class="row align-items-end g-3">
                <div class="col-md-9 mb-2">
                    <label for="trackLookupInput" class="font-weight-600 mb-2 small text-muted">Enter Tracking ID or Reference ID</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-barcode"></i>
                        <input type="text" id="trackLookupInput" class="form-control form-control-lg" placeholder="Enter Tracking ID e.g. BTX947E60001020 or Reference ID">
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="button" class="btn btn-info w-100 font-weight-bold" style="height: 52px; border-radius: 10px;" onclick="trackStatusFromSearch()">
                        <i class="fa-solid fa-search mr-1"></i> Check Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Status Preview Card (Shown after submit or lookup) -->
    <div id="statusResultCard" class="panel-card p-4 mb-4" style="display: none; border-left: 4px solid #f59e0b;">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-2 border-bottom border-white-5 gap-2">
            <h3 class="h6 font-weight-bold text-white m-0">
                <i class="fa-solid fa-clipboard-check text-warning mr-2"></i> Current Application Status &amp; Result
            </h3>
            <div class="d-flex align-items-center gap-2">
                <span id="cardStatusBadge" class="badge py-2 px-3"></span>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="refreshCurrentCardStatus()" title="Refresh live status">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
            </div>
        </div>
        <div class="row g-3 text-white">
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Original Tracking ID</small>
                <div class="d-flex align-items-center gap-1">
                    <strong id="cardTrackingId" class="text-warning font-monospace fs-6"></strong>
                    <button class="btn btn-xs btn-link text-white-50 p-0" onclick="copyText($('#cardTrackingId').text())" title="Copy">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">IPE Category</small>
                <span id="cardCategory" class="text-white font-weight-600"></span>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">System Reference</small>
                <code id="cardRefId" class="text-primary font-weight-bold"></code>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Submitted Date</small>
                <span id="cardSubmittedAt" class="text-white-50"></span>
            </div>

            <!-- New Tracking ID Spotlight Banner -->
            <div class="col-12 mt-2" id="newTrackingCardWrap" style="display: none;">
                <div class="p-3.5 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1)); border: 1px solid rgba(16, 185, 129, 0.4);">
                    <div>
                        <span class="badge bg-success text-white text-uppercase font-weight-bold px-2 py-1 mb-1" id="cardSuccessBadge">
                            <i class="fa-solid fa-circle-check mr-1"></i> Clearance Completed
                        </span>
                        <small class="text-success-emphasis d-block font-weight-bold" id="cardNewTrackingLabel">Your Newly Assigned Tracking ID:</small>
                        <span id="cardNewTrackingId" class="font-monospace fs-3 text-white font-weight-bold tracking-wider"></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-success font-weight-bold px-3 py-2" onclick="copyText($('#cardNewTrackingId').text(), 'Tracking ID copied!')">
                            <i class="fa-solid fa-copy mr-1"></i> <span id="cardCopyBtnText">Copy Tracking ID</span>
                        </button>
                        <a id="cardPersonalizeBtn" href="{{ route('services.personalization') }}" class="btn font-weight-bold px-3 py-2 text-white" style="background-color: #8b5cf6;">
                            <i class="fa-solid fa-user-gear mr-1"></i> Proceed to Personalization
                        </a>
                        <a href="{{ route('services.nin.suite') }}" class="btn btn-outline-light px-3 py-2 font-weight-bold">
                            <i class="fa-solid fa-arrow-right mr-1"></i> Retrieve Slip
                        </a>
                    </div>
                </div>
            </div>

            <!-- Remarks Output Box -->
            <div class="col-12 mt-2">
                <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <small class="text-warning d-block mb-1 font-weight-600"><i class="fa-solid fa-circle-info mr-1"></i> Clearance Desk Notes:</small>
                    <p id="cardAdminNote" class="mb-0 text-white font-weight-500"></p>
                </div>
            </div>

            <!-- Action Buttons for Result Card -->
            <div class="col-12 mt-2 d-flex align-items-center justify-content-end gap-2">
                <a id="cardReportBtn" href="#" target="_blank" class="btn btn-info font-weight-bold px-3 py-2" style="display: none;">
                    <i class="fa-solid fa-file-invoice mr-1"></i> Print / View Official Report
                </a>
            </div>
        </div>
    </div>

    <!-- IPE Results Section (Table & Filters) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" style="background: var(--clr-bg-card); border: 1px solid rgba(255,255,255,0.08);">
        <!-- Blue Header Banner -->
        <div class="py-3 px-4 text-white font-weight-bold d-flex flex-wrap align-items-center justify-content-between gap-2" style="background-color: #2563eb;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check fs-5"></i>
                <span style="font-size: 1.05rem;" class="font-weight-bold">IPE Results &amp; Tracking History</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-light text-primary font-weight-bold px-3 py-1.5" id="totalRecordsBadge">{{ $history->count() }} Submissions</span>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="window.location.reload()" title="Reload list">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Search & Filter Controls -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 480px;">
                    <div class="input-wrap flex-grow-1">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="tableSearchInput" class="form-control" placeholder="Search by Tracking ID, New Tracking ID, or Reference..." onkeyup="filterResultsTable()">
                    </div>
                </div>

                <!-- Status Filter Badges -->
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-light status-filter-btn active" onclick="filterByStatus('all', this)">All</button>
                    <button type="button" class="btn btn-sm btn-outline-success status-filter-btn" onclick="filterByStatus('successful', this)">Cleared</button>
                    <button type="button" class="btn btn-sm btn-outline-warning status-filter-btn" onclick="filterByStatus('waiting_for_review', this)">Processing</button>
                    <button type="button" class="btn btn-sm btn-outline-danger status-filter-btn" onclick="filterByStatus('failed', this)">Failed</button>
                </div>
            </div>

            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white" id="ipeResultsTable">
                    <thead style="background: rgba(255,255,255,0.04);">
                        <tr>
                            <th>Reference</th>
                            <th>Original Tracking ID</th>
                            <th>New Tracking ID</th>
                            <th>IPE Category</th>
                            <th>Status</th>
                            <th>Admin Remarks / Note</th>
                            <th>Submitted At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $res)
                            @php 
                                $d = $res->response_data ?? []; 
                                $rowStatus = $res->status ?? 'waiting_for_review';
                                $newTid = $res->resolved_new_tracking_id ?? ($d['new_tracking_id'] ?? null);
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);" class="result-row" data-status="{{ $rowStatus }}" id="row-{{ $res->id }}">
                                <td><code>{{ $res->reference_id }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-warning font-monospace font-weight-bold tracking-cell">{{ $res->identifier }}</span>
                                        <button class="btn btn-xs btn-link text-white-50 p-0" onclick="copyText('{{ $res->identifier }}')" title="Copy ID">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="new-tracking-cell">
                                    @if(!empty($newTid))
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-success font-monospace px-2.5 py-1 text-white font-weight-bold">
                                                <i class="fa-solid fa-key me-1"></i> {{ $newTid }}
                                            </span>
                                            <button class="btn btn-xs btn-link text-success p-0" onclick="copyText('{{ $newTid }}', 'New Tracking ID copied!')" title="Copy New ID">
                                                <i class="fa-solid fa-copy"></i>
                                            </button>
                                        </div>
                                    @else
                                        @if($rowStatus === 'successful')
                                            <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-0.5 small" title="Original Tracking ID was cleared directly by NIMC without re-indexing">
                                                <i class="fa-solid fa-circle-check me-1"></i> Original Cleared
                                            </span>
                                        @elseif($rowStatus === 'failed')
                                            <span class="text-danger small">—</span>
                                        @else
                                            <span class="badge badge-warning text-dark px-2 py-0.5 small">
                                                <i class="fa-solid fa-clock-rotate-left mr-1"></i> In Process
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary px-2 py-1">
                                        {{ $d['category'] ?? 'Improcessing Error' }}
                                    </span>
                                </td>
                                <td class="status-cell">
                                    @if($rowStatus === 'successful')
                                        <span class="badge badge-success px-2.5 py-1"><i class="fa-solid fa-check me-1"></i> Cleared / Ready</span>
                                    @elseif($rowStatus === 'failed')
                                        <span class="badge badge-danger px-2.5 py-1"><i class="fa-solid fa-xmark me-1"></i> Failed / Refunded</span>
                                    @else
                                        <span class="badge badge-warning text-dark px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i> Processing</span>
                                    @endif
                                </td>
                                <td class="admin-note-cell">
                                    <small class="text-white-50">
                                        {{ $res->admin_note ?: ($rowStatus === 'successful' ? 'Cleared successfully.' : 'Under review.') }}
                                    </small>
                                </td>
                                <td><small class="text-muted">{{ $res->created_at->format('M d, Y H:i') }}</small></td>
                                <td class="text-right">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='displayCardDetails(@json($res))' title="View details">
                                            <i class="fa-solid fa-eye mr-1"></i> View
                                        </button>
                                        <a href="{{ route('services.clearance.report', $res->id) }}" class="btn btn-outline-info" target="_blank" title="View official printable report">
                                            <i class="fa-solid fa-file-invoice mr-1"></i> Report
                                        </a>
                                        @if($rowStatus !== 'successful' && $rowStatus !== 'failed')
                                            <button class="btn btn-outline-warning" onclick="checkRowStatus('{{ $res->identifier }}', {{ $res->id }})" title="Check live status">
                                                <i class="fa-solid fa-arrows-rotate mr-1"></i> Live Check
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                    No IPE records found. Submit your NIMC Tracking ID above to initiate clearance.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Status Result Modal -->
<div class="modal fade" id="ipeDetailsModal" tabindex="-1" aria-labelledby="ipeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: 16px; color: #fff;">
            <div class="modal-header border-bottom border-white-10 py-3 px-4">
                <h5 class="modal-title font-weight-bold d-flex align-items-center gap-2" id="ipeDetailsModalLabel">
                    <i class="fa-solid fa-clipboard-check text-warning"></i> IPE Clearance Result Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Status Banner -->
                <div id="modalStatusBanner" class="p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-uppercase font-weight-bold d-block text-white-50" id="modalStatusCaption">Clearance Status</small>
                        <h4 class="font-weight-bold mb-0 text-white" id="modalStatusTitle">Processing</h4>
                    </div>
                    <span id="modalStatusBadge" class="badge py-2 px-3 fs-6"></span>
                </div>

                <!-- Spotlight New Tracking ID (if available) -->
                <div id="modalNewTrackingWrap" class="p-3.5 rounded-3 mb-3" style="display: none; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4);">
                    <small class="text-success-emphasis text-uppercase font-weight-bold d-block mb-1" id="modalNewTrackingLabel">
                        <i class="fa-solid fa-key mr-1"></i> Your New Tracking ID:
                    </small>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span id="modalNewTrackingId" class="font-monospace fs-3 text-white font-weight-bold tracking-wider"></span>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold" onclick="copyText($('#modalNewTrackingId').text(), 'Tracking ID copied!')">
                            <i class="fa-solid fa-copy mr-1"></i> <span id="modalCopyBtnText">Copy</span>
                        </button>
                    </div>
                    <small class="text-white-50 mt-1 d-block" id="modalNewTrackingHelp">
                        Use this new Tracking ID to print your standard or premium slip from the NIN Suite.
                    </small>
                </div>

                <!-- Application Metadata Table -->
                <div class="table-responsive rounded-3 mb-3" style="border: 1px solid rgba(255,255,255,0.08);">
                    <table class="table table-sm table-borderless text-white mb-0" style="background: rgba(255,255,255,0.02);">
                        <tbody>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="text-muted py-2 px-3" style="width: 35%;">Submitted Tracking ID</td>
                                <td class="py-2 px-3 font-weight-bold">
                                    <span id="modalTrackingId" class="text-warning font-monospace"></span>
                                    <button class="btn btn-xs btn-link text-white-50 p-0 ml-1" onclick="copyText($('#modalTrackingId').text())" title="Copy">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="text-muted py-2 px-3">IPE Category</td>
                                <td class="py-2 px-3" id="modalCategory"></td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="text-muted py-2 px-3">System Reference</td>
                                <td class="py-2 px-3"><code id="modalRefId" class="text-primary font-weight-bold"></code></td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="text-muted py-2 px-3">Processing Mode</td>
                                <td class="py-2 px-3" id="modalServiceMode">Automated Processing</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="text-muted py-2 px-3">Submitted Timestamp</td>
                                <td class="py-2 px-3 text-white-50" id="modalSubmittedAt"></td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2 px-3">Clearance Desk Notes</td>
                                <td class="py-2 px-3 font-weight-500 text-info" id="modalAdminNote"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top border-white-10 py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-warning btn-sm" id="modalLiveCheckBtn" onclick="modalLiveCheck()">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i> Refresh Live Status
                    </button>
                    <a href="#" id="modalReportBtn" target="_blank" class="btn btn-info btn-sm font-weight-bold" style="display: none;">
                        <i class="fa-solid fa-file-invoice mr-1"></i> Print / View Report
                    </a>
                </div>
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .panel-card { 
        background: var(--clr-bg-card); 
        backdrop-filter: blur(25px); 
        border: 1px solid rgba(255,255,255,0.08); 
        border-radius: 16px; 
    }
    .custom-select-styled {
        height: 52px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.12);
        color: #fff;
        border-radius: 10px;
    }
    .custom-select-styled option {
        background: #111827;
        color: #fff;
    }
    .input-wrap { 
        position: relative; 
        display: flex; 
        align-items: center; 
    }
    .input-wrap i { 
        position: absolute; 
        left: 16px; 
        color: var(--clr-text-muted); 
    }
    .input-wrap .form-control { 
        padding-left: 45px !important; 
        height: 52px; 
        background: rgba(255,255,255,0.04); 
        border: 1px solid rgba(255,255,255,0.12); 
        border-radius: 10px;
        color: #fff;
    }
    .input-wrap .form-control:focus {
        border-color: #2563eb;
        background: rgba(255,255,255,0.07);
        color: #fff;
    }
    .nav-pills .nav-link {
        color: rgba(255,255,255,0.7);
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link:hover {
        color: #fff;
        background: rgba(255,255,255,0.08);
    }
    .nav-pills .nav-link.active {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }
    .status-filter-btn.active {
        background: rgba(255,255,255,0.2) !important;
        font-weight: 700;
    }
</style>
@endpush

@push('scripts')
<script>
    let currentActiveTrackingId = '';

    function switchTab(tab) {
        if (tab === 'submit') {
            $('#submit-tab').addClass('active');
            $('#track-tab').removeClass('active');
            $('#submitPane').show();
            $('#trackPane').hide();
        } else {
            $('#track-tab').addClass('active');
            $('#submit-tab').removeClass('active');
            $('#trackPane').show();
            $('#submitPane').hide();
            $('#trackLookupInput').focus();
        }
    }

    function copyText(text, msg = 'Copied to clipboard!') {
        if (!text) return;
        navigator.clipboard.writeText(text.trim()).then(function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: msg,
                showConfirmButton: false,
                timer: 2000,
                background: '#0f172a',
                color: '#fff'
            });
        });
    }

    function displayCardDetails(data) {
        let trackingId = data.identifier || data.tracking_id || 'N/A';
        let refId = data.reference_id || 'N/A';
        let resp = data.response_data || data.details || {};
        let category = resp.category || data.category || 'Improcessing Error';
        let newTrackingId = data.resolved_new_tracking_id || resp.new_tracking_id || data.new_tracking_id || null;
        let status = data.status || 'waiting_for_review';
        let adminNote = data.admin_note || (status === 'successful' ? 'Cleared successfully.' : 'Under review by verification desk.');
        let submittedAt = data.created_at || data.submitted_at || 'Just now';
        let serviceMode = resp.service_mode || 'Automated Processing';
        let resultId = data.result_id || data.id;
        let reportUrl = data.report_url || (resultId ? `{{ url('/services/clearance/report') }}/${resultId}` : null);

        currentActiveTrackingId = trackingId;

        // In-page summary card
        $('#cardTrackingId').text(trackingId);
        $('#cardRefId').text(refId);
        $('#cardCategory').text(category);
        $('#cardSubmittedAt').text(submittedAt);
        $('#cardAdminNote').text(adminNote);

        if (reportUrl) {
            $('#cardReportBtn').attr('href', reportUrl).show();
            $('#modalReportBtn').attr('href', reportUrl).show();
        } else {
            $('#cardReportBtn').hide();
            $('#modalReportBtn').hide();
        }

        if (status === 'successful') {
            if (newTrackingId) {
                $('#cardNewTrackingLabel').html('<i class="fa-solid fa-key me-1"></i> Newly Assigned Tracking ID:');
                $('#cardNewTrackingId').text(newTrackingId);
                $('#cardCopyBtnText').text('Copy New ID');
                $('#cardPersonalizeBtn').attr('href', `{{ route('services.personalization') }}?tracking_id=${encodeURIComponent(newTrackingId)}`);
            } else {
                $('#cardNewTrackingLabel').html('<i class="fa-solid fa-circle-check me-1"></i> Original Tracking ID Cleared & Unlocked (Direct Clearance):');
                $('#cardNewTrackingId').text(trackingId);
                $('#cardCopyBtnText').text('Copy Cleared ID');
                $('#cardPersonalizeBtn').attr('href', `{{ route('services.personalization') }}?tracking_id=${encodeURIComponent(trackingId)}`);
            }
            $('#newTrackingCardWrap').show();
        } else {
            $('#newTrackingCardWrap').hide();
        }

        let cardBadge = $('#cardStatusBadge');
        if (status === 'successful') {
            cardBadge.removeClass('badge-warning badge-danger text-dark').addClass('badge-success text-white').html('<i class="fa-solid fa-check me-1"></i> Cleared / Successful');
        } else if (status === 'failed') {
            cardBadge.removeClass('badge-warning badge-success text-dark').addClass('badge-danger text-white').html('<i class="fa-solid fa-xmark me-1"></i> Failed / Refunded');
        } else {
            cardBadge.removeClass('badge-success badge-danger text-white').addClass('badge-warning text-dark').html('<i class="fa-solid fa-clock me-1"></i> Under Review (&lt; 24h)');
        }

        $('#statusResultCard').fadeIn();

        // Populate Modal
        $('#modalTrackingId').text(trackingId);
        $('#modalRefId').text(refId);
        $('#modalCategory').text(category);
        $('#modalSubmittedAt').text(submittedAt);
        $('#modalAdminNote').text(adminNote);
        $('#modalServiceMode').text(serviceMode);

        let banner = $('#modalStatusBanner');
        let modalBadge = $('#modalStatusBadge');

        if (status === 'successful') {
            banner.css('background', 'linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.15))').css('border', '1px solid rgba(16, 185, 129, 0.4)');
            $('#modalStatusTitle').text('Cleared / Ready');
            modalBadge.removeClass('badge-warning badge-danger text-dark').addClass('badge-success text-white').html('<i class="fa-solid fa-circle-check me-1"></i> Success');
            if (newTrackingId) {
                $('#modalNewTrackingLabel').html('<i class="fa-solid fa-key me-1"></i> Newly Assigned Tracking ID:');
                $('#modalNewTrackingId').text(newTrackingId);
                $('#modalNewTrackingHelp').text('Use this newly issued Tracking ID to personalize or retrieve your standard/premium slip.');
                $('#modalCopyBtnText').text('Copy New ID');
            } else {
                $('#modalNewTrackingLabel').html('<i class="fa-solid fa-circle-check me-1"></i> Original Tracking ID Cleared (Direct Clearance):');
                $('#modalNewTrackingId').text(trackingId);
                $('#modalNewTrackingHelp').text('NIMC cleared the error directly on your record. No new tracking ID is required — your original ID is now unlocked and active for personalization and slip retrieval.');
                $('#modalCopyBtnText').text('Copy Cleared ID');
            }
            $('#modalNewTrackingWrap').show();
            $('#modalLiveCheckBtn').hide();
        } else if (status === 'failed') {
            banner.css('background', 'linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(220, 38, 38, 0.15))').css('border', '1px solid rgba(239, 68, 68, 0.4)');
            $('#modalStatusTitle').text('Failed / Rejected');
            modalBadge.removeClass('badge-warning badge-success text-dark').addClass('badge-danger text-white').html('<i class="fa-solid fa-circle-xmark me-1"></i> Refunded');
            $('#modalNewTrackingWrap').hide();
            $('#modalLiveCheckBtn').hide();
        } else {
            banner.css('background', 'linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.15))').css('border', '1px solid rgba(245, 158, 11, 0.4)');
            $('#modalStatusTitle').text('In Progress / Under Review');
            modalBadge.removeClass('badge-success badge-danger text-white').addClass('badge-warning text-dark').html('<i class="fa-solid fa-clock me-1"></i> Processing');
            $('#modalNewTrackingWrap').hide();
            $('#modalLiveCheckBtn').show();
        }

        let modalEl = document.getElementById('ipeDetailsModal');
        let modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.show();
    }

    function quickCheckFromInput() {
        let tid = $('#number').val().trim();
        if (!tid) {
            Swal.fire({
                title: 'Enter Tracking ID',
                text: 'Please input your NIMC Tracking ID in the box first.',
                icon: 'info',
                background: '#0f172a',
                color: '#fff'
            });
            $('#number').focus();
            return;
        }
        executeLiveStatusQuery(tid);
    }

    function trackStatusFromSearch() {
        let tid = $('#trackLookupInput').val().trim();
        if (!tid) {
            Swal.fire({
                title: 'Enter Tracking ID',
                text: 'Please enter a Tracking ID or Reference ID to query.',
                icon: 'info',
                background: '#0f172a',
                color: '#fff'
            });
            $('#trackLookupInput').focus();
            return;
        }
        executeLiveStatusQuery(tid);
    }

    function refreshCurrentCardStatus() {
        if (currentActiveTrackingId) {
            executeLiveStatusQuery(currentActiveTrackingId);
        }
    }

    function modalLiveCheck() {
        if (currentActiveTrackingId) {
            executeLiveStatusQuery(currentActiveTrackingId);
        }
    }

    function checkRowStatus(trackingId, rowId) {
        executeLiveStatusQuery(trackingId, rowId);
    }

    function executeLiveStatusQuery(trackingId, rowId = null) {
        Swal.fire({
            title: 'Querying Status...',
            html: `Fetching live clearance record for <strong>${trackingId}</strong>`,
            didOpen: () => { Swal.showLoading(); },
            background: '#0f172a',
            color: '#fff',
            allowOutsideClick: false
        });

        $.ajax({
            url: "{{ route('services.clearance.verify') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                mode: 'status',
                number: trackingId
            },
            success: function(res) {
                Swal.close();
                if (res.status && res.data) {
                    displayCardDetails(res.data);

                    // Update corresponding row in table if present
                    let targetRow = rowId ? $('#row-' + rowId) : $('.result-row').filter(function() {
                        return $(this).find('.tracking-cell').text().trim() === trackingId;
                    });

                    if (targetRow.length > 0) {
                        let st = res.data.status;
                        let newTid = res.data.new_tracking_id;
                        targetRow.attr('data-status', st);

                        if (st === 'successful') {
                            targetRow.find('.status-cell').html('<span class="badge badge-success px-2.5 py-1"><i class="fa-solid fa-check me-1"></i> Cleared / Ready</span>');
                            if (newTid) {
                                targetRow.find('.new-tracking-cell').html(`
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-success font-monospace px-2.5 py-1 text-white font-weight-bold">
                                            <i class="fa-solid fa-key me-1"></i> ${newTid}
                                        </span>
                                        <button class="btn btn-xs btn-link text-success p-0" onclick="copyText('${newTid}', 'New Tracking ID copied!')">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>
                                `);
                            } else {
                                targetRow.find('.new-tracking-cell').html(`
                                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-0.5 small" title="Original Tracking ID was cleared directly by NIMC without re-indexing">
                                        <i class="fa-solid fa-circle-check me-1"></i> Original Cleared
                                    </span>
                                `);
                            }
                        } else if (st === 'failed') {
                            targetRow.find('.status-cell').html('<span class="badge badge-danger px-2.5 py-1"><i class="fa-solid fa-xmark me-1"></i> Failed / Refunded</span>');
                        }

                        if (res.data.admin_note) {
                            targetRow.find('.admin-note-cell small').text(res.data.admin_note);
                        }
                    }
                } else {
                    Swal.fire({
                        title: 'Record Not Found',
                        text: res.message || 'No clearance record found with that Tracking ID.',
                        icon: 'warning',
                        background: '#0f172a',
                        color: '#fff'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error retrieving status.';
                Swal.fire({ title: 'Status Check Error', text: msg, icon: 'error', background: '#0f172a', color: '#fff' });
            }
        });
    }

    function filterResultsTable() {
        let query = $('#tableSearchInput').val().toLowerCase().trim();
        let rows = $('.result-row');
        let matched = 0;
        rows.each(function() {
            let text = $(this).text().toLowerCase();
            if (text.indexOf(query) !== -1) {
                $(this).show();
                matched++;
            } else {
                $(this).hide();
            }
        });

        if (matched === 0 && rows.length > 0) {
            if ($('#noMatchRow').length === 0) {
                $('#ipeResultsTable tbody').append('<tr id="noMatchRow"><td colspan="8" class="text-center py-4 text-muted">No matching records found.</td></tr>');
            }
        } else {
            $('#noMatchRow').remove();
        }
    }

    function filterByStatus(status, btn) {
        $('.status-filter-btn').removeClass('active');
        $(btn).addClass('active');

        let rows = $('.result-row');
        let matched = 0;
        rows.each(function() {
            let rowStatus = $(this).attr('data-status');
            if (status === 'all' || rowStatus === status) {
                $(this).show();
                matched++;
            } else {
                $(this).hide();
            }
        });

        if (matched === 0 && rows.length > 0) {
            if ($('#noMatchRow').length === 0) {
                $('#ipeResultsTable tbody').append('<tr id="noMatchRow"><td colspan="8" class="text-center py-4 text-muted">No records matching the selected status filter.</td></tr>');
            }
        } else {
            $('#noMatchRow').remove();
        }
    }

    $(document).ready(function() {
        @if(session('ipe_result'))
            displayCardDetails(@json(session('ipe_result')));
        @endif

        $('#clearanceForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submitBtn');
            let originalHtml = btn.html();
            let category = $('#category').val();
            let trackingId = $('#number').val().trim();

            if (!category) {
                Swal.fire({ title: 'Select Category', text: 'Please select an IPE category before submitting.', icon: 'warning', background: '#0f172a', color: '#fff' });
                return;
            }

            if (!trackingId) {
                Swal.fire({ title: 'Enter Tracking ID', text: 'Please enter your NIMC Tracking ID.', icon: 'warning', background: '#0f172a', color: '#fff' });
                return;
            }

            Swal.fire({
                title: 'Confirm IPE Clearance',
                html: `
                    <div class="text-start p-2">
                        <p class="mb-2">A fee of <strong>₦{{ number_format($price ?? 700, 2) }}</strong> will be deducted from your wallet balance.</p>
                        <div class="p-2.5 rounded bg-black bg-opacity-25 border border-white-10 small">
                            <div><strong>Category:</strong> ${category}</div>
                            <div><strong>Tracking ID:</strong> <span class="font-monospace text-warning">${trackingId}</span></div>
                            <div><strong>NIN Requirement:</strong> <span class="text-success">Not required</span></div>
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Submit Tracking ID',
                background: '#0f172a',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Submitting...');

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            btn.prop('disabled', false).html(originalHtml);
                            if (response.status) {
                                let reportUrl = response.report_url || (response.data && response.data.report_url) || (response.result_id ? `{{ url('/services/clearance/report') }}/${response.result_id}` : null);

                                Swal.fire({
                                    title: 'Submitted Successfully!',
                                    text: response.message,
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#059669',
                                    cancelButtonColor: '#2563eb',
                                    confirmButtonText: '<i class="fa-solid fa-file-invoice mr-1"></i> View Official Report',
                                    cancelButtonText: 'View Status on Portal',
                                    background: '#0f172a',
                                    color: '#fff'
                                }).then((choice) => {
                                    if (choice.isConfirmed && reportUrl) {
                                        window.open(reportUrl, '_blank');
                                    }
                                    if (response.data) {
                                        displayCardDetails(response.data);
                                    } else {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({ title: 'Notice', text: response.message, icon: 'warning', background: '#0f172a', color: '#fff' });
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(originalHtml);
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unable to complete clearance submission.';
                            Swal.fire({ title: 'Submission Error', text: msg, icon: 'error', background: '#0f172a', color: '#fff' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

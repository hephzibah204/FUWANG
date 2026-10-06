@extends('layouts.nexus')

@section('title', 'IPE Clearance | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05)); border-color: rgba(245, 158, 11, 0.2);">
        <div class="sh-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">IPE Clearance</h1>
            <p class="text-muted small mb-0">
                Select IPEs Category and Enter the Tracking ID in the form below. The IPE Result are shown under the form below. You will get the Result in <strong class="text-success font-weight-bold">{{ ($ipeMode ?? 'manual') === 'robosttech' ? 'a few minutes' : 'less than 24 hours' }}</strong>.
            </p>
        </div>
    </div>

    <!-- Main Clearance Submission Card -->
    <div class="panel-card p-4 mb-4" id="submissionCard">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-white-5">
            <h2 class="h5 font-weight-bold m-0 text-white">
                <i class="fa-solid fa-id-badge mr-2 text-warning"></i> Select IPEs Category
            </h2>
            <span class="badge badge-warning py-2 px-3 text-dark font-weight-bold" id="priceBadge" style="font-size: 0.95rem;">
                Fee: ₦{{ number_format($price ?? 700, 2) }}
            </span>
        </div>

        <form id="clearanceForm" action="{{ route('services.clearance.verify') }}" method="POST">
            @csrf
            <input type="hidden" name="mode" id="formMode" value="submit">

            <div class="row">
                <!-- Select IPEs Category -->
                <div class="col-12 mb-3">
                    <label for="category" class="font-weight-600 mb-2 small text-muted">Select IPEs Category</label>
                    <select id="category" name="category" class="form-control form-control-lg custom-select-styled" required>
                        <option value="">--Select IPE Category--</option>
                        <option value="New Enrollment for ID Retrieval">New Enrollment for ID Retrieval</option>
                        <option value="Improcessing Error" selected>Improcessing Error</option>
                        <option value="Enrollment Is still Being Processed">Enrollment Is still Being Processed</option>
                    </select>
                </div>

                <!-- Enter Tracking ID -->
                <div class="col-md-7 mb-3">
                    <label for="number" class="font-weight-600 mb-2 small text-muted">Enter Tracking ID</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-hashtag"></i>
                        <input type="text" id="number" name="number" class="form-control form-control-lg" placeholder="Enter Tracking ID e.g BTX947E60001020" required>
                    </div>
                </div>

                <!-- Optional NIN Number -->
                <div class="col-md-5 mb-3">
                    <label for="nin" class="font-weight-600 mb-2 small text-muted">NIN Number (Optional if known)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-id-card"></i>
                        <input type="text" id="nin" name="nin" class="form-control form-control-lg" placeholder="11-digit NIN (Optional)" maxlength="15">
                    </div>
                </div>

                <!-- Action Buttons: Go Back & Submit Tracking -->
                <div class="col-12 d-flex align-items-center gap-2 mt-2">
                    <a href="{{ route('services.nin.suite') }}" class="btn btn-warning text-white px-4 font-weight-bold mr-2" style="background-color: #f97316; border-color: #f97316; border-radius: 8px;">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Go Back
                    </a>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="submitBtn" style="background-color: #2563eb; border-color: #2563eb; border-radius: 8px;">
                        <i class="fa-solid fa-paper-plane mr-1"></i> <span id="btnText">Submit Tracking</span>
                    </button>
                    <button type="button" class="btn btn-outline-light ml-auto" onclick="quickCheckStatus()" title="Quick check status by entered Tracking ID">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Track Status
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Live Status Preview Card (Shown after submit or lookup) -->
    <div id="statusResultCard" class="panel-card p-4 mb-4" style="display: none;">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-white-5">
            <h3 class="h6 font-weight-bold text-white m-0">
                <i class="fa-solid fa-clipboard-check text-warning mr-2"></i> Current Application Status &amp; Result
            </h3>
            <span id="cardStatusBadge" class="badge py-2 px-3"></span>
        </div>
        <div class="row g-3 text-white">
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Original Tracking ID</small>
                <strong id="cardTrackingId" class="text-warning font-monospace fs-6"></strong>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">IPE Category</small>
                <span id="cardCategory" class="text-white font-weight-600"></span>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">System Reference</small>
                <code id="cardRefId" class="text-primary"></code>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Submitted Date</small>
                <span id="cardSubmittedAt"></span>
            </div>

            <!-- New Tracking ID Awarded Banner -->
            <div class="col-12 mt-2" id="newTrackingCardWrap" style="display: none;">
                <div class="p-3 bg-success bg-opacity-25 rounded-3 border border-success border-opacity-50 text-white">
                    <small class="text-success-emphasis d-block font-weight-bold mb-1">
                        <i class="fa-solid fa-key me-1"></i> Your New Tracking ID:
                    </small>
                    <span id="cardNewTrackingId" class="font-monospace fs-4 text-white font-weight-bold"></span>
                </div>
            </div>

            <div class="col-12 mt-2">
                <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <small class="text-warning d-block mb-1 font-weight-600"><i class="fa-solid fa-circle-info mr-1"></i> Admin Remarks / Vetting Output:</small>
                    <p id="cardAdminNote" class="mb-0 text-white font-weight-500"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- IPE Results Section (Competitor style blue bar & table) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" style="background: var(--clr-bg-card); border: 1px solid rgba(255,255,255,0.08);">
        <!-- Blue Header Banner matching competitor screenshot -->
        <div class="py-2.5 px-4 text-white font-weight-bold d-flex align-items-center justify-content-between" style="background-color: #2563eb;">
            <span style="font-size: 1rem;"><i class="fa-solid fa-list-check mr-2"></i> IPE Results</span>
            <span class="badge badge-light text-primary font-weight-bold">{{ $history->count() }} Total</span>
        </div>

        <div class="card-body p-4">
            <!-- Search & Filter Controls -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div class="d-flex align-items-center gap-2" style="max-width: 450px; width: 100%;">
                    <div class="input-wrap flex-grow-1">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="tableSearchInput" class="form-control" placeholder="Search by Tracking ID, New Tracking ID, or NIN" onkeyup="filterResultsTable()">
                    </div>
                    <button type="button" class="btn btn-primary px-3" onclick="filterResultsTable()">
                        Search
                    </button>
                </div>
                <div class="text-white-50 small">
                    <strong>Total Record({{ $history->count() }})</strong> — page 1 of 1
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
                            <th>NIN</th>
                            <th>IPE Category</th>
                            <th>Status</th>
                            <th>Admin Remarks / Note</th>
                            <th>Submitted At</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $res)
                            @php $d = $res->response_data ?? []; @endphp
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);" class="result-row">
                                <td><code>{{ $res->reference_id }}</code></td>
                                <td><span class="text-warning font-monospace font-weight-bold tracking-cell">{{ $res->identifier }}</span></td>
                                <td>
                                    @if(!empty($d['new_tracking_id']))
                                        <span class="badge bg-success font-monospace px-2.5 py-1">
                                            <i class="fa-solid fa-key me-1"></i> {{ $d['new_tracking_id'] }}
                                        </span>
                                    @else
                                        <span class="text-white-50">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($d['nin']))
                                        <span class="font-monospace text-info">{{ $d['nin'] }}</span>
                                    @else
                                        <span class="text-white-50">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary px-2 py-1">
                                        {{ $d['category'] ?? 'Improcessing Error' }}
                                    </span>
                                </td>
                                <td>
                                    @if($res->status === 'successful')
                                        <span class="badge badge-success px-2.5 py-1"><i class="fa-solid fa-check me-1"></i> Cleared / Ready</span>
                                    @elseif($res->status === 'failed')
                                        <span class="badge badge-danger px-2.5 py-1"><i class="fa-solid fa-xmark me-1"></i> Failed / Refunded</span>
                                    @else
                                        <span class="badge badge-warning text-dark px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i> Processing (&lt; 24h)</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-white-50">
                                        {{ $res->admin_note ?: ($res->status === 'successful' ? 'Cleared successfully.' : 'Under review.') }}
                                    </small>
                                </td>
                                <td><small class="text-muted">{{ $res->created_at->format('M d, Y H:i') }}</small></td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-outline-primary" onclick='displayCardDetails(@json($res))'>
                                        <i class="fa-solid fa-eye mr-1"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                    No IPE records found. Submit a tracking ID above to initiate clearance.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
</style>
@endpush

@push('scripts')
<script>
    function displayCardDetails(data) {
        let trackingId = data.identifier || data.tracking_id || 'N/A';
        let refId = data.reference_id || 'N/A';
        let resp = data.response_data || data.details || {};
        let category = resp.category || data.category || 'Improcessing Error';
        let newTrackingId = resp.new_tracking_id || data.new_tracking_id || null;
        let status = data.status || 'waiting_for_review';
        let adminNote = data.admin_note || (status === 'successful' ? 'Cleared successfully by administrator.' : 'Under review by administrator (in less than 24 hours).');
        let submittedAt = data.created_at || data.submitted_at || 'Just now';

        $('#cardTrackingId').text(trackingId);
        $('#cardRefId').text(refId);
        $('#cardCategory').text(category);
        $('#cardSubmittedAt').text(submittedAt);
        $('#cardAdminNote').text(adminNote);

        if (newTrackingId) {
            $('#cardNewTrackingId').text(newTrackingId);
            $('#newTrackingCardWrap').show();
        } else {
            $('#newTrackingCardWrap').hide();
        }

        let badge = $('#cardStatusBadge');
        if (status === 'successful') {
            badge.removeClass('badge-warning badge-danger text-dark').addClass('badge-success text-white').html('<i class="fa-solid fa-check me-1"></i> Cleared / Successful');
        } else if (status === 'failed') {
            badge.removeClass('badge-warning badge-success text-dark').addClass('badge-danger text-white').html('<i class="fa-solid fa-xmark me-1"></i> Failed / Refunded');
        } else {
            badge.removeClass('badge-success badge-danger text-white').addClass('badge-warning text-dark').html('<i class="fa-solid fa-clock me-1"></i> Under Review (&lt; 24 hours)');
        }

        $('#statusResultCard').fadeIn();
        $('html, body').animate({ scrollTop: $('#statusResultCard').offset().top - 80 }, 400);
    }

    function quickCheckStatus() {
        let trackingId = $('#number').val().trim();
        let nin = $('#nin').val().trim();

        if (!trackingId) {
            Swal.fire({
                title: 'Enter Tracking ID',
                text: 'Please input your Tracking ID in the box to track its clearance status.',
                icon: 'info',
                background: '#0a0a0f',
                color: '#fff'
            });
            $('#number').focus();
            return;
        }

        Swal.fire({
            title: 'Checking Status...',
            text: 'Querying administrative vetting records for ' + trackingId,
            didOpen: () => { Swal.showLoading(); },
            background: '#0a0a0f',
            color: '#fff',
            allowOutsideClick: false
        });

        $.ajax({
            url: "{{ route('services.clearance.verify') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                mode: 'status',
                number: trackingId,
                nin: nin
            },
            success: function(res) {
                if (res.status && res.data) {
                    Swal.close();
                    displayCardDetails(res.data);
                } else {
                    Swal.fire({
                        title: 'Record Not Found',
                        text: res.message || 'No clearance record found with that Tracking ID.',
                        icon: 'warning',
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                }
            },
            error: function(xhr) {
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error retrieving status.';
                Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
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
                $('#ipeResultsTable tbody').append('<tr id="noMatchRow"><td colspan="9" class="text-center py-4 text-muted">No matching tracking IDs found.</td></tr>');
            }
        } else {
            $('#noMatchRow').remove();
        }
    }

    $(document).ready(function() {
        $('#clearanceForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submitBtn');
            let originalHtml = btn.html();
            let category = $('#category').val();
            let trackingId = $('#number').val().trim();

            if (!category) {
                Swal.fire({ title: 'Select Category', text: 'Please select an IPE category before submitting.', icon: 'warning', background: '#0a0a0f', color: '#fff' });
                return;
            }

            Swal.fire({
                title: 'Confirm IPE Clearance',
                html: `<p class="mb-2">A fee of <strong>₦{{ number_format($price ?? 700, 2) }}</strong> will be deducted from your wallet.</p><p class="small text-muted mb-0">Category: <b>${category}</b><br>Tracking ID: <b>${trackingId}</b><br>Result turnaround: <b>In less than 24 hours</b></p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Submit Tracking',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Submitting...');

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: 'Submitted Successfully!',
                                    text: response.message,
                                    icon: 'success',
                                    background: '#0a0a0f',
                                    color: '#fff'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({ title: 'Notice', text: response.message, icon: 'warning', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function(xhr) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unable to complete clearance submission.';
                            Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

@extends('layouts.nexus')

@section('title', 'Credit Bureau Advance Report | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(79, 70, 229, 0.05)); border-color: rgba(99, 102, 241, 0.2);">
        <div class="sh-icon" style="background: rgba(99, 102, 241, 0.15); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.3);">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Credit Bureau Advance</h1>
            <p class="text-muted small">Comprehensive financial insight and credit worthiness report.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-ranking-star text-warning"></i> Scoring</span>
            <span class="badge-accent"><i class="fa-solid fa-file-invoice-dollar text-success"></i> Account History</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel-card p-4 mb-4" id="formPanel">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Credit Lookup</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦1,000.00</span>
                </div>

                <form id="creditForm" action="{{ route('services.credit_bureau.verify') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <label for="mode" class="font-weight-600 mb-2 small text-muted">Lookup Mode</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-fingerprint"></i>
                                <select id="mode" name="mode" class="form-control" required>
                                    <option value="ID">BVN / Identity (ID)</option>
                                    <option value="BIO">Date of Birth (BIO)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label for="customer_name" class="font-weight-600 mb-2 small text-muted">Customer Full Name</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="e.g. Test Name" required>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4" id="number-input-wrap">
                            <label for="number" class="font-weight-600 mb-2 small text-muted">BVN Number</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-id-card"></i>
                                <input type="text" id="number" name="number" class="form-control" placeholder="22222222222">
                            </div>
                        </div>
                        <div class="col-md-3 mb-4 d-none" id="dob-input-wrap">
                            <label for="dob" class="font-weight-600 mb-2 small text-muted">Date of Birth</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-calendar-day"></i>
                                <input type="date" id="dob" name="dob" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 50px;">
                                <i class="fa-solid fa-search mr-2"></i> Generate Report
                            </button>
                        </div>
                    </div>

                    @if(isset($bureauProviders) && $bureauProviders->count() > 1)
                    <div class="form-group mb-0">
                        <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Bureau Source</label>
                        <select id="api_provider_id" name="api_provider_id" class="form-control form-control-sm" style="width: auto;">
                            @foreach($bureauProviders as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Result Area -->
        <div class="col-lg-12" id="resultArea" style="display: none;">
            <div class="row">
                <!-- Left Column: Summary & Scoring -->
                <div class="col-lg-4">
                    <!-- Scoring Card -->
                    <div class="panel-card p-4 mb-4 text-center">
                        <h4 class="h6 font-weight-bold mb-3 uppercase tracking-wider text-muted">Credit Score</h4>
                        <div class="mx-auto my-4 position-relative d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                            <svg viewBox="0 0 36 36" class="circular-chart indigo">
                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path id="score-circle" class="circle" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <div class="position-absolute text-center">
                                <h2 class="h3 font-weight-bold text-white m-0" id="res-score">0</h2>
                                <p class="x-small text-muted m-0">Rating</p>
                            </div>
                        </div>
                        <p class="small text-white" id="res-score-desc">Loading...</p>
                        <div class="row mt-4">
                            <div class="col-6 border-right border-white-5">
                                <p class="x-small text-muted mb-1">Repayment</p>
                                <strong class="small text-white" id="res-score-repay">-</strong>
                            </div>
                            <div class="col-6">
                                <p class="x-small text-muted mb-1">Owed Score</p>
                                <strong class="small text-white" id="res-score-owed">-</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Summary -->
                    <div class="panel-card p-4 mb-4">
                        <h4 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Consumer Profile</h4>
                        <div class="row g-3">
                            <div class="col-12 mb-2">
                                <label class="x-small text-muted d-block mb-0">Full Name</label>
                                <strong id="res-name" class="text-white small">-</strong>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="x-small text-muted d-block mb-0">Gender</label>
                                <strong id="res-gender" class="text-white small">-</strong>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="x-small text-muted d-block mb-0">Nationality</label>
                                <strong id="res-nat" class="text-white small">-</strong>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="x-small text-muted d-block mb-0">BVN</label>
                                <strong id="res-bvn" class="text-white small">-</strong>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="x-small text-muted d-block mb-0">NIN</label>
                                <strong id="res-nin" class="text-white small">-</strong>
                            </div>
                            <div class="col-12">
                                <label class="x-small text-muted d-block mb-0">Email</label>
                                <strong id="res-email" class="text-white small">-</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Account Stats & History -->
                <div class="col-lg-8">
                    <!-- Stats Grid -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="panel-card p-3 text-center border-l-primary">
                                <p class="x-small text-muted mb-1">Total Accounts</p>
                                <h3 class="h5 font-weight-bold text-white mb-0" id="res-total-acct">0</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="panel-card p-3 text-center border-l-success">
                                <p class="x-small text-muted mb-1">Good Condition</p>
                                <h3 class="h5 font-weight-bold text-success mb-0" id="res-good-acct">0</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="panel-card p-3 text-center border-l-danger">
                                <p class="x-small text-muted mb-1">Arrears</p>
                                <h3 class="h5 font-weight-bold text-danger mb-0" id="res-arrears">₦0.00</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Agreements -->
                    <div class="panel-card p-4">
                        <h4 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Credit Agreements</h4>
                        <div id="agreements-list" style="max-height: 500px; overflow-y: auto;">
                            <!-- Agreements dynamic here -->
                        </div>
                        <div id="no-agreements" class="text-center py-5 text-muted small" style="display: none;">
                            <i class="fa-solid fa-folder-open fa-2x mb-2 opacity-20"></i>
                            <p>No credit agreements found for this consumer.</p>
                        </div>
                    </div>
                    
                    <button class="btn btn-outline-light btn-block mt-4" onclick="window.location.reload()">New Search</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 20px; }
    .border-l-primary { border-left: 4px solid var(--clr-primary) !important; }
    .border-l-success { border-left: 4px solid #10b981 !important; }
    .border-l-danger { border-left: 4px solid #ef4444 !important; }
    
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .x-small { font-size: 0.7rem; }
    
    /* Circular Chart */
    .circular-chart { display: block; margin: 0 auto; max-width: 100%; max-height: 100%; shadow: 0 0 15px rgba(0,0,0,0.5); }
    .circle-bg { fill: none; stroke: rgba(255,255,255,0.05); stroke-width: 3.8; }
    .circle { fill: none; stroke-width: 2.8; stroke-linecap: round; transition: stroke-dasharray 1.5s ease; }
    .indigo .circle { stroke: #6366f1; filter: drop-shadow(0 0 5px #6366f1); }
    
    /* Agreement List */
    .agreement-item { padding: 15px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 15px; margin-bottom: 12px; }
    .status-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
    .status-performing { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .status-non-performing { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#mode').on('change', function() {
            if ($(this).val() === 'ID') {
                $('#number-input-wrap').removeClass('d-none');
                $('#dob-input-wrap').addClass('d-none');
                $('#number').prop('required', true);
                $('#dob').prop('required', false);
            } else {
                $('#number-input-wrap').addClass('d-none');
                $('#dob-input-wrap').removeClass('d-none');
                $('#number').prop('required', false);
                $('#dob').prop('required', true);
            }
        });

        $('#creditForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Advanced Credit Search',
                text: "A premium fee of ₦1,000.00 will be charged for this detailed report. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Fetching Credit Data...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let dataArray = response.data;
                                
                                // Flatten Data Array for easier access
                                let data = {};
                                dataArray.forEach(item => {
                                    Object.assign(data, item);
                                });

                                $('#formPanel').hide();
                                
                                // 1. Scoring
                                if (data.Scoring && data.Scoring.length > 0) {
                                    let s = data.Scoring[0];
                                    let score = parseInt(s.TotalConsumerScore || 0);
                                    let percentage = (score / 1000) * 100; // Assuming 1000 is max
                                    $('#res-score').text(score);
                                    $('#res-score-desc').text(s.Description || 'N/A');
                                    $('#res-score-repay').text(s.RepaymentHistoryScore || '0');
                                    $('#res-score-owed').text(s.TotalAmountOwedScore || '0');
                                    $('#score-circle').css('stroke-dasharray', `${percentage}, 100`);
                                }

                                // 2. Personal Details
                                if (data.PersonalDetailsSummary && data.PersonalDetailsSummary.length > 0) {
                                    let p = data.PersonalDetailsSummary[0];
                                    $('#res-name').text(`${p.FirstName} ${p.Surname} ${p.OtherNames || ''}`);
                                    $('#res-gender').text(p.Gender || 'N/A');
                                    $('#res-nat').text(p.Nationality || 'N/A');
                                    $('#res-bvn').text(p.BankVerificationNo || 'N/A');
                                    $('#res-nin').text(p.NationalIDNo || 'N/A');
                                    $('#res-email').text(p.EmailAddress || 'N/A');
                                }

                                // 3. Account Summary
                                if (data.CreditAccountSummary && data.CreditAccountSummary.length > 0) {
                                    let cs = data.CreditAccountSummary[0];
                                    $('#res-total-acct').text(cs.TotalAccounts || '0');
                                    $('#res-good-acct').text(cs.TotalaccountinGoodcondition || '0');
                                    $('#res-arrears').text('₦' + (cs.Amountarrear || '0.00'));
                                }

                                // 4. Agreements
                                let list = $('#agreements-list');
                                list.empty();
                                let agreements = data.CreditAgreementSummary || [];
                                if (agreements.length > 0) {
                                    $('#no-agreements').hide();
                                    agreements.forEach(a => {
                                        let statusClass = a.PerformanceStatus?.toLowerCase().includes('performing') && !a.PerformanceStatus?.toLowerCase().includes('non') ? 'status-performing' : 'status-non-performing';
                                        list.append(`
                                            <div class="agreement-item animate-in">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="small font-weight-bold text-white mb-0">${a.SubscriberName}</h6>
                                                        <span class="x-small text-muted">${a.IndicatorDescription}</span>
                                                    </div>
                                                    <span class="status-badge ${statusClass}">${a.PerformanceStatus}</span>
                                                </div>
                                                <div class="row g-2 mt-2">
                                                    <div class="col-4">
                                                        <p class="x-small text-muted mb-0">Balance</p>
                                                        <strong class="x-small text-white">NGN ${a.CurrentBalanceAmt}</strong>
                                                    </div>
                                                    <div class="col-4">
                                                        <p class="x-small text-muted mb-0">Overdue</p>
                                                        <strong class="x-small text-danger">NGN ${a.AmountOverdue}</strong>
                                                    </div>
                                                    <div class="col-4 p-0">
                                                        <p class="x-small text-muted mb-0">Opened</p>
                                                        <strong class="x-small text-white">${a.DateAccountOpened || '-'}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        `);
                                    });
                                } else {
                                    $('#no-agreements').show();
                                }

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Report Generated', text: 'Financial credit profile retrieved.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Search Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'Bureau servers are currently unreachable.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

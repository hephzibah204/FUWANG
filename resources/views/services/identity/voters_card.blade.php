@extends('layouts.nexus')

@section('title', 'Voters Card Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); border-color: rgba(59, 130, 246, 0.2);">
        <div class="sh-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3);">
            <i class="fa-solid fa-id-card-clip"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Voters Card (PVC)</h1>
            <p class="text-muted small">Official INEC registry lookup for Permanent Voters Card verification.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-check-to-slot text-success"></i> INEC Data</span>
            <span class="badge-accent"><i class="fa-solid fa-location-dot text-primary"></i> Polling Unit</span>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4 custom-tabs" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-search-tab" data-toggle="pill" data-target="#pills-search" type="button" role="tab">
                <i class="fa-solid fa-search mr-2"></i> New Search
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-vault-tab" data-toggle="pill" data-target="#pills-vault" type="button" role="tab">
                <i class="fa-solid fa-vault mr-2"></i> Verification Vault
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <!-- Search Tab -->
        <div class="tab-pane fade show active" id="pills-search" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel-card p-4 mb-4" id="searchPanel">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                            <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> PVC Lookup</h2>
                            <span class="ml-auto badge badge-primary py-2 px-3">₦200.00</span>
                        </div>

                        <form id="voterForm" action="{{ route('services.voters_card.verify') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-3 mb-4">
                                    <label for="number" class="font-weight-600 mb-2 small text-muted">VIN Number</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-hashtag"></i>
                                        <input type="text" id="number" name="number" class="form-control" placeholder="90A5AB..." required>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <label for="firstname" class="font-weight-600 mb-2 small text-muted">First Name</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-user"></i>
                                        <input type="text" id="firstname" name="firstname" class="form-control" placeholder="JOHN" required>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <label for="lastname" class="font-weight-600 mb-2 small text-muted">Last Name</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-user"></i>
                                        <input type="text" id="lastname" name="lastname" class="form-control" placeholder="DOE" required>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <label for="dob" class="font-weight-600 mb-2 small text-muted">Date of Birth</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-calendar-day"></i>
                                        <input type="date" id="dob" name="dob" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-12 text-right mt-2">
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="submit-btn" style="height: 50px;">
                                        <i class="fa-solid fa-search mr-2"></i> Verify Voter
                                    </button>
                                </div>
                            </div>

                            @if(isset($voterProviders) && $voterProviders->count() > 1)
                            <div class="form-group mb-0 mt-3">
                                <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Source</label>
                                <select id="api_provider_id" name="api_provider_id" class="form-control form-control-sm" style="width: auto;">
                                    @foreach($voterProviders as $provider)
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
                    <div class="pvc-card-container mx-auto">
                        <div class="pvc-card panel-card p-0 overflow-hidden">
                            <div class="pvc-header p-3 d-flex align-items-center">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/79/Coat_of_arms_of_Nigeria.svg/1200px-Coat_of_arms_of_Nigeria.svg.png" alt="Nigeria Coat of Arms" style="height: 40px;" class="mr-3">
                                <div>
                                    <h6 class="m-0 font-weight-bold text-success small">FEDERAL REPUBLIC OF NIGERIA</h6>
                                    <p class="m-0 x-small text-muted font-weight-bold">INDEPENDENT NATIONAL ELECTORAL COMMISSION</p>
                                </div>
                                <div class="ml-auto text-right">
                                    <span class="badge badge-success x-small py-1 px-2">PVC VERIFIED</span>
                                </div>
                            </div>
                            
                            <div class="pvc-body p-4 row g-0">
                                <div class="col-4 text-center">
                                    <div class="pvc-photo rounded mb-2 mx-auto" style="width: 100px; height: 120px; background: rgba(0,0,0,0.1); border: 2px solid #ddd; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-user fa-3x opacity-20"></i>
                                    </div>
                                    <strong class="x-small text-muted d-block uppercase tracking-widest mt-2" id="res-vin">90A5AB...</strong>
                                </div>
                                <div class="col-8 pl-4 border-left border-white-5">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="x-small text-muted mb-0 d-block uppercase">Full Name</label>
                                            <strong id="res-fullname" class="text-white h6 font-weight-bold">-</strong>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="x-small text-muted mb-0 d-block uppercase">Gender</label>
                                            <strong id="res-gender" class="text-white small">-</strong>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="x-small text-muted mb-0 d-block uppercase">Occupation</label>
                                            <strong id="res-occupation" class="text-white small">-</strong>
                                        </div>
                                        <div class="col-12">
                                            <label class="x-small text-muted mb-0 d-block uppercase">Polling Unit Code</label>
                                            <strong id="res-polling" class="text-success small">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pvc-footer p-2 text-center" style="background: rgba(16, 185, 129, 0.1);">
                                <span class="x-small font-weight-bold text-success tracking-widest">CONTINUOUS VOTER REGISTRATION</span>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button class="btn btn-outline-light btn-wide" onclick="window.location.reload()">Verify Another PVC</button>
                            <a href="#" id="downloadReport" class="btn btn-primary ml-2 py-2 d-none">
                                <i class="fa-solid fa-file-pdf mr-1"></i> Download Certificate
                            </a>
                            <button class="btn btn-outline-primary ml-2 py-2" onclick="window.print()"><i class="fa-solid fa-print mr-1"></i> Print</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vault Tab -->
        <div class="tab-pane fade" id="pills-vault" role="tabpanel">
            <div class="panel-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Reference</th>
                                <th>Identifier</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $record)
                                <tr>
                                    <td class="pl-4">
                                        <span class="font-weight-bold text-white small">{{ $record->reference_id }}</span>
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ $record->identifier }}</div>
                                    </td>
                                    <td>
                                        @if($record->status == 'success')
                                            <span class="badge badge-success-soft text-success"><i class="fa-solid fa-check-circle mr-1"></i> Verified</span>
                                        @else
                                            <span class="badge badge-danger-soft text-danger"><i class="fa-solid fa-times-circle mr-1"></i> Failed</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $record->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="text-right pr-4">
                                        <a href="{{ route('services.verification.report', $record->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">
                                        <i class="fa-solid fa-vault fa-2x mb-3 d-block opacity-20"></i>
                                        No verification history found in your vault.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 20px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .x-small { font-size: 0.7rem; }
    
    .pvc-card-container { max-width: 500px; }
    .pvc-card { border-radius: 15px; border: 2px solid rgba(16, 185, 129, 0.3) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .pvc-header { background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05); }
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.1em; }
    .btn-wide { padding-left: 40px; padding-right: 40px; border-radius: 12px; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#voterForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm PVC Lookup',
                text: "A fee of ₦200.00 will be charged for this INEC registry lookup. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Fetching Voter Data...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let d = response.data;
                                $('#searchPanel').hide();
                                
                                $('#res-fullname').text(d.fullname || `${d.firstName} ${d.lastName}`);
                                $('#res-vin').text(d.vin || $('#number').val());
                                $('#res-gender').text(d.gender || 'N/A');
                                $('#res-occupation').text(d.occupation || 'N/A');
                                $('#res-polling').text(d.pollingUnitCode || 'N/A');

                                if (response.result_id) {
                                    $('#downloadReport').attr('href', `/services/verification/report/${response.result_id}`).removeClass('d-none');
                                }

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', text: 'Voter record retrieved.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Search Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'Voter Information System is currently unreachable.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

@extends('layouts.nexus')

@section('title', 'Address Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.1), rgba(202, 138, 4, 0.05)); border-color: rgba(234, 179, 8, 0.2);">
        <div class="sh-icon" style="background: rgba(234, 179, 8, 0.15); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.3);">
            <i class="fa-solid fa-house-chimney-user"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Address Verification</h1>
            <p class="text-muted small">Field agent physical address verification for trust and compliance.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-map-location text-warning"></i> Georeferenced</span>
            <span class="badge-accent"><i class="fa-solid fa-clock text-info"></i> Async Status</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <ul class="nav nav-pills" id="addressTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="verify-tab" data-toggle="pill" href="#verify-panel" role="tab"><i class="fa-solid fa-file-signature mr-2"></i> New Request</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="marketplace-tab" data-toggle="pill" href="#marketplace-panel" role="tab"><i class="fa-solid fa-shop mr-2"></i> Marketplace Search</a>
                        </li>
                    </ul>
                    <span class="ml-auto badge badge-warning-soft text-warning py-2 px-3">₦1,000.00</span>
                </div>

                <div class="tab-content" id="addressTabsContent">
                    <!-- Verify Panel -->
                    <div class="tab-pane fade show active" id="verify-panel" role="tabpanel">
                        <form id="addressForm" action="{{ route('services.address_verify.submit') }}" method="POST">
                            @csrf
                            
                            <h6 class="x-small text-muted uppercase font-weight-bold mb-3 tracking-wider">Property Details</h6>
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Street Address</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <input type="text" name="street" class="form-control" placeholder="270 Murtala Muhammed Way, Alagomeji" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">City</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-city"></i>
                                        <input type="text" name="city" class="form-control" placeholder="Yaba" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Local Govt (LGA)</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-map"></i>
                                        <input type="text" name="lga" class="form-control" placeholder="Surulere" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">State</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-earth-africa"></i>
                                        <input type="text" name="state" class="form-control" placeholder="Lagos" required>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Nearest Landmark</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-monument"></i>
                                        <input type="text" name="landmark" class="form-control" placeholder="Beside GTbank / Near the big mango tree" required>
                                    </div>
                                </div>
                            </div>

                            <h6 class="x-small text-muted uppercase font-weight-bold mt-2 mb-3 tracking-wider">Applicant Info</h6>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">First Name</label>
                                    <input type="text" name="firstname" class="form-control" placeholder="JOHN" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Last Name</label>
                                    <input type="text" name="lastname" class="form-control" placeholder="DOE" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">ID Type</label>
                                    <select name="idType" class="form-control">
                                        <option value="BVN">BVN</option>
                                        <option value="NIN">NIN</option>
                                        <option value="KYC">Phone Number (KYC)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">ID Number</label>
                                    <input type="text" name="idNumber" class="form-control" placeholder="10000000001" required>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Contact Phone</label>
                                    <input type="text" name="phone" class="form-control" placeholder="08012345678" required>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="submit-btn" style="height: 50px;">
                                    <i class="fa-solid fa-paper-plane mr-2"></i> Submit for Field Agent
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Marketplace Panel -->
                    <div class="tab-pane fade" id="marketplace-panel" role="tabpanel">
                        <form id="marketplaceForm" action="{{ route('services.address_verify.marketplace') }}" method="POST">
                            @csrf
                            <h6 class="x-small text-muted uppercase font-weight-bold mb-3 tracking-wider">Marketplace Address Search</h6>
                            <p class="small text-muted mb-4">Fetch already verified addresses from the VerifyMe marketplace.</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">First Name</label>
                                    <input type="text" name="firstname" class="form-control" placeholder="JOHN" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Last Name</label>
                                    <input type="text" name="lastname" class="form-control" placeholder="DOE" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">ID Type</label>
                                    <select name="idType" class="form-control">
                                        <option value="NIN">NIN</option>
                                        <option value="FRSC">Driver's License (FRSC)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">ID Number</label>
                                    <input type="text" name="idNumber" class="form-control" placeholder="10000000001" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted">Max Address Age</label>
                                    <select name="maxAddressAge" class="form-control">
                                        <option value="1M">1 Month</option>
                                        <option value="3M">3 Months</option>
                                        <option value="6M" selected>6 Months</option>
                                        <option value="12M">12 Months</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-warning btn-lg px-5" id="market-btn" style="height: 50px; color: #000;">
                                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Search Marketplace
                                </button>
                            </div>
                        </form>

                        <div id="marketplaceResult" class="mt-4 d-none">
                            <h6 class="h6 font-weight-bold mb-3 border-bottom border-white-5 pb-2">Search Result</h6>
                            <div class="p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <div id="resultContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4">
                <h6 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Recent Requests</h6>
                <div class="request-list">
                    @forelse($recentRequests as $req)
                    <div class="request-item d-block mb-3 p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease;">
                        <div class="d-flex justify-content-between mb-1">
                            <strong class="x-small text-white uppercase">{{ $req->transaction_id }}</strong>
                            <span class="badge badge-{{ ($req->status === 'success' || $req->status === 'completed') ? 'success' : ($req->status === 'cancelled' ? 'danger' : 'info') }}-soft x-small">{{ strtoupper($req->status) }}</span>
                        </div>
                        <p class="x-small text-muted mb-2">{{ $req->order_type }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-1 pt-2 border-top border-white-5">
                            <span class="x-small text-muted opacity-50">{{ $req->created_at->diffForHumans() }}</span>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                @if($req->status === 'pending')
                                <button onclick="cancelVerification('{{ $req->transaction_id }}')" class="btn btn-link p-0 x-small text-danger text-decoration-none">Cancel</button>
                                @endif
                                <a href="{{ route('services.address_verify.details', $req->transaction_id) }}" class="x-small text-primary text-decoration-none">View Report <i class="fa-solid fa-chevron-right ml-1"></i></a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fa-solid fa-inbox fa-3x mb-3 text-muted opacity-20"></i>
                        <p class="small text-muted">No pending verifications found.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <div class="alert alert-info mt-4" style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.2); color: #7dd3fc; border-radius: 12px;">
                <i class="fa-solid fa-info-circle mr-2"></i>
                <span class="small">Address verifications can take 24-72 hours to complete once a field agent is assigned.</span>
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
    .input-wrap .form-control { padding-left: 45px !important; }
    .form-control { height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: #fff !important; }
    .form-control::placeholder { color: rgba(255,255,255,0.2); }
    
    .nav-pills .nav-link { color: rgba(255,255,255,0.6); padding: 8px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease; }
    .nav-pills .nav-link.active { background: rgba(255,255,255,0.1) !important; color: #fff; }

    .badge-warning-soft { background: rgba(234, 179, 8, 0.1); color: #eab308; }
    .badge-success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge-info-soft { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
    .badge-danger-soft { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .uppercase { text-transform: uppercase; }
    .tracking-wider { letter-spacing: 1px; }
    .x-small { font-size: 0.75rem; }
    .border-white-5 { border-color: rgba(255,255,255,0.05) !important; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Address Submission
        $('#addressForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            
            Swal.fire({
                title: 'Confirm Submission',
                text: "A fee of ₦1,000.00 will be charged for this address verification. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#eab308',
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
                                Swal.fire({ title: 'Submitted!', text: response.message, icon: 'success', background: '#0a0a0f', color: '#fff' })
                                .then(() => window.location.reload());
                            } else {
                                Swal.fire({ title: 'Error', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Submit for Field Agent');
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'Gateway timeout.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Submit for Field Agent');
                        }
                    });
                }
            });
        });

        // Marketplace Search
        $('#marketplaceForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#market-btn');
            let resultDiv = $('#marketplaceResult');
            let resultContent = $('#resultContent');
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Searching...');
            resultDiv.addClass('d-none');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-magnifying-glass mr-2"></i> Search Marketplace');
                    if (response.status === 'success') {
                        let d = response.data;
                        let html = `
                            <div class="row">
                                <div class="col-8">
                                    <p class="mb-1 text-white"><strong>${d.street}</strong></p>
                                    <p class="x-small text-muted mb-2">${d.lga}, ${d.state}</p>
                                    <span class="badge badge-success-soft x-small">Verified</span>
                                </div>
                                <div class="col-4 text-right">
                                    <div class="rounded overflow-hidden ml-auto" style="width: 50px; height: 50px; border: 1px solid #eab308;">
                                        <img src="${d.applicant.photo}" class="w-100 h-100" style="object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        `;
                        resultContent.html(html);
                        resultDiv.removeClass('d-none');
                    } else {
                        Swal.fire({ title: 'Not Found', text: response.message || 'No verified address found.', icon: 'info', background: '#0a0a0f', color: '#fff' });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-magnifying-glass mr-2"></i> Search Marketplace');
                    Swal.fire({ title: 'Error', text: xhr.responseJSON?.message || 'Search failed.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                }
            });
        });
    });

    function cancelVerification(id) {
        Swal.fire({
            title: 'Cancel Verification?',
            text: "Are you sure you want to cancel this address verification request?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            background: '#0a0a0f',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/services/address-verify/cancel/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({ title: 'Cancelled', text: response.message, icon: 'success', background: '#0a0a0f', color: '#fff' })
                            .then(() => window.location.reload());
                        } else {
                            Swal.fire({ title: 'Error', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                        }
                    },
                    error: function() {
                        Swal.fire({ title: 'Error', text: 'Gateway timeout.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                    }
                });
            }
        });
    }
</script>
@endpush

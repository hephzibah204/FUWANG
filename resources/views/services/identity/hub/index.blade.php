@extends('layouts.nexus')
@section('title', 'Verification Hub')

@section('content')
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <!-- Main Content -->
    <div class="col-xl-9 fade-in">
        <div class="card card-flush shadow-sm">
            <div class="card-header pt-7 pb-3">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800 fs-2">Verification Hub</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Access all auxiliary identity services</span>
                </h3>
            </div>
            
            <div class="card-body">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6 overflow-auto flex-nowrap border-0 pb-3" style="text-wrap: nowrap;">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary fw-bold {{ $activeTab == 'drivers_license' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab_dl"><i class="fa-solid fa-id-card me-2"></i> Driver's License</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary fw-bold {{ $activeTab == 'voters_card_verification' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab_voter"><i class="fa-solid fa-person-booth me-2"></i> Voter's Card</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary fw-bold {{ $activeTab == 'passport_verification' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab_passport"><i class="fa-solid fa-passport me-2"></i> Passport</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary fw-bold {{ $activeTab == 'cac_verification' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab_cac"><i class="fa-solid fa-building me-2"></i> CAC Verify</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary fw-bold {{ $activeTab == 'tin_verification' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab_tin"><i class="fa-solid fa-calculator me-2"></i> TIN Verify</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary fw-bold {{ $activeTab == 'plate_number_verification' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab_plate"><i class="fa-solid fa-car me-2"></i> Plate Number</a>
                    </li>
                    <li class="nav-item ms-auto">
                        <a class="nav-link text-active-success fw-bold {{ str_contains($activeTab, 'history') ? 'active' : '' }}" data-bs-toggle="tab" href="#tab_vault"><i class="fa-solid fa-vault me-2"></i> Service Vault</a>
                    </li>
                </ul>

                <div class="tab-content" id="hubTabContent">
                    
                    <!-- 1. Driver's License Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'drivers_license' ? 'show active' : '' }}" id="tab_dl" role="tabpanel">
                        <form class="verify-form" action="{{ route('services.drivers_license.verify') }}" method="POST">
                            @csrf
                            <input type="hidden" name="service_type" value="drivers_license">
                            <div class="mb-5">
                                <label class="form-label required">License Number</label>
                                <input type="text" class="form-control form-control-solid" name="license_no" placeholder="Enter DL Number" required />
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label required">Date of Birth</label>
                                    <input type="date" class="form-control form-control-solid" name="dob" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">First Name</label>
                                    <input type="text" class="form-control form-control-solid" name="firstname" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Last Name</label>
                                    <input type="text" class="form-control form-control-solid" name="lastname" required />
                                </div>
                            </div>
                            <!-- Dynamic Pricer display -->
                            <div class="d-flex align-items-center justify-content-between mb-5 px-4 py-3 bg-light-primary rounded border border-primary border-dashed">
                                <div class="fs-6 fw-semibold text-gray-700">Verification Cost</div>
                                <div class="fs-2 fw-bold text-primary">₦{{ number_format((float)($prices['drivers_license'] ?? 300), 2) }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-verify">
                                <span class="indicator-label"><i class="fa-solid fa-magnifying-glass me-2"></i> Verify Driver's License</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    <!-- 2. Voter's Card Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'voters_card_verification' ? 'show active' : '' }}" id="tab_voter" role="tabpanel">
                        <form class="verify-form" action="{{ route('services.voters_card.verify') }}" method="POST">
                            @csrf
                            <div class="row mb-5">
                                <div class="col-md-6 mb-5">
                                    <label class="form-label required">VIN</label>
                                    <input type="text" class="form-control form-control-solid" name="number" placeholder="Enter VIN" required />
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label required">Date of Birth</label>
                                    <input type="date" class="form-control form-control-solid" name="dob" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">First Name</label>
                                    <input type="text" class="form-control form-control-solid" name="firstname" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Last Name</label>
                                    <input type="text" class="form-control form-control-solid" name="lastname" required />
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-5 px-4 py-3 bg-light-primary rounded border border-primary border-dashed">
                                <div class="fs-6 fw-semibold text-gray-700">Verification Cost</div>
                                <div class="fs-2 fw-bold text-primary">₦{{ number_format((float)($prices['voters_card_verification'] ?? 200), 2) }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-verify">
                                <span class="indicator-label"><i class="fa-solid fa-magnifying-glass me-2"></i> Verify Voter's Card</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    <!-- 3. Passport Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'passport_verification' ? 'show active' : '' }}" id="tab_passport" role="tabpanel">
                        <form class="verify-form" action="{{ route('services.passport.verify') }}" method="POST">
                            @csrf
                            <input type="hidden" name="mode" value="sync">
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <label class="form-label required">Passport Number</label>
                                    <input type="text" class="form-control form-control-solid" name="number" placeholder="Eg. A0000000" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Last Name</label>
                                    <input type="text" class="form-control form-control-solid" name="last_name" required />
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-5 px-4 py-3 bg-light-primary rounded border border-primary border-dashed">
                                <div class="fs-6 fw-semibold text-gray-700">Verification Cost</div>
                                <div class="fs-2 fw-bold text-primary">₦{{ number_format((float)($prices['passport_verification'] ?? 500), 2) }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-verify">
                                <span class="indicator-label"><i class="fa-solid fa-magnifying-glass me-2"></i> Verify Passport</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    <!-- 4. CAC Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'cac_verification' ? 'show active' : '' }}" id="tab_cac" role="tabpanel">
                        <form class="verify-form" action="{{ route('services.cac_verify.verify') }}" method="POST">
                            @csrf
                            <div class="row mb-5">
                                <div class="col-md-8">
                                    <label class="form-label required">RC/BN Number</label>
                                    <input type="text" class="form-control form-control-solid" name="rc_number" placeholder="Enter Registration Number" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Company Type</label>
                                    <select class="form-select form-select-solid" name="company_type" required>
                                        <option value="RC">RC</option>
                                        <option value="BN">BN</option>
                                        <option value="IT">IT</option>
                                        <option value="LL">LL</option>
                                        <option value="LLP">LLP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-5 px-4 py-3 bg-light-primary rounded border border-primary border-dashed">
                                <div class="fs-6 fw-semibold text-gray-700">Verification Cost</div>
                                <div class="fs-2 fw-bold text-primary">₦{{ number_format((float)($prices['cac_verification'] ?? 500), 2) }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-verify">
                                <span class="indicator-label"><i class="fa-solid fa-magnifying-glass me-2"></i> Verify CAC Entity</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    <!-- 5. TIN Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'tin_verification' ? 'show active' : '' }}" id="tab_tin" role="tabpanel">
                        <form class="verify-form" action="{{ route('services.tin_verify.verify') }}" method="POST">
                            @csrf
                            <div class="row mb-5">
                                <div class="col-md-8">
                                    <label class="form-label required">Identifier Number</label>
                                    <input type="text" class="form-control form-control-solid" name="number" placeholder="TIN, Phone, or CAC Number" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Lookup Channel</label>
                                    <select class="form-select form-select-solid" name="channel" required>
                                        <option value="TIN">TIN Number</option>
                                        <option value="CAC">CAC Number</option>
                                        <option value="Phone">Phone Number</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-5 px-4 py-3 bg-light-primary rounded border border-primary border-dashed">
                                <div class="fs-6 fw-semibold text-gray-700">Verification Cost</div>
                                <div class="fs-2 fw-bold text-primary">₦{{ number_format((float)($prices['tin_verification'] ?? 200), 2) }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-verify">
                                <span class="indicator-label"><i class="fa-solid fa-magnifying-glass me-2"></i> Verify TIN Record</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    <!-- 6. Plate Number Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'plate_number_verification' ? 'show active' : '' }}" id="tab_plate" role="tabpanel">
                        <form class="verify-form" action="{{ route('services.plate_number.verify') }}" method="POST">
                            @csrf
                            <div class="mb-5">
                                <label class="form-label required">Plate Number</label>
                                <input type="text" class="form-control form-control-solid" name="vehicle_number" placeholder="Eg. ABJ-123XY" required />
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-5 px-4 py-3 bg-light-primary rounded border border-primary border-dashed">
                                <div class="fs-6 fw-semibold text-gray-700">Verification Cost</div>
                                <div class="fs-2 fw-bold text-primary">₦{{ number_format((float)($prices['plate_number_verification'] ?? 200), 2) }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-verify">
                                <span class="indicator-label"><i class="fa-solid fa-magnifying-glass me-2"></i> Verify Plate Number</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    <!-- 7. Vault Tab -->
                    <div class="tab-pane fade" id="tab_vault" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th>Date</th>
                                        <th>Service</th>
                                        <th>Identifier</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    @forelse($history as $item)
                                    <tr>
                                        <td>{{ $item->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge badge-light-primary text-uppercase">{{ str_replace('_', ' ', $item->service_type) }}</span>
                                        </td>
                                        <td>{{ $item->identifier }}</td>
                                        <td>
                                            @if($item->status == 'success')
                                                <span class="badge badge-light-success">Success</span>
                                            @else
                                                <span class="badge badge-light-danger">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->status == 'success')
                                            <a href="{{ route('services.verification.report', $item->id) }}" class="btn btn-sm btn-light-info btn-active-info" target="_blank">
                                                <i class="fa-solid fa-print"></i> Print PDF
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-10">No verification history available in the hub yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Shared Result Container -->
                <div id="result-container" class="mt-8 d-none">
                    <h3 class="card-title fw-bold text-gray-800 mb-4">Verification Result</h3>
                    <div class="bg-light-success rounded p-5 border border-success border-dashed">
                        <json-viewer id="json-renderer" class="fs-6"></json-viewer>
                        <div class="mt-4 pt-4 border-top border-success border-dashed text-end" id="action-buttons">
                            <!-- Download PDF Button will be injected here -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-xl-3 fade-in" style="animation-delay: 0.1s;">
        <div class="card card-flush shadow-sm mb-5">
            <div class="card-header pt-5">
                <h3 class="card-title text-gray-800 fw-bold">Hub Information</h3>
            </div>
            <div class="card-body pt-0 fs-6 text-gray-600">
                <p>The Verification Hub provides instant access to all auxiliary identity and corporate background checks.</p>
                <div class="d-flex align-items-center mb-3">
                    <i class="fa-solid fa-circle-check text-success me-3"></i> Unified Deductions
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="fa-solid fa-circle-check text-success me-3"></i> Instant Refunds
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="fa-solid fa-circle-check text-success me-3"></i> Consolidated Vault
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.verify-form').on('submit', function(e) {
        e.preventDefault();
        
        let $form = $(this);
        let $btn = $form.find('.btn-verify');
        let $indicatorLabel = $btn.find('.indicator-label');
        let $indicatorProgress = $btn.find('.indicator-progress');
        
        // Hide result container
        $('#result-container').addClass('d-none');
        
        // Show loading state
        $indicatorLabel.hide();
        $indicatorProgress.show();
        $btn.prop('disabled', true);
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                // Restore button state
                $indicatorLabel.show();
                $indicatorProgress.hide();
                $btn.prop('disabled', false);
                
                if (response.status) {
                    toastr.success(response.message);
                    
                    // Render simple JSON recursively or custom HTML if desired
                    let prettyData = JSON.stringify(response.data, null, 2);
                    $('#json-renderer').html('<pre class="mb-0 text-gray-800">' + prettyData + '</pre>');
                    
                    let actionsHtml = '';
                    if (response.result_id) {
                        actionsHtml = '<a href="/services/verification/report/' + response.result_id + '" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-file-pdf me-2"></i> Download PDF Report</a>';
                    }
                    $('#action-buttons').html(actionsHtml);
                    
                    $('#result-container').removeClass('d-none');
                    
                    // Optional scroll to result
                    $('html, body').animate({
                        scrollTop: $("#result-container").offset().top - 100
                    }, 500);
                } else {
                    toastr.error(response.message || 'Verification failed');
                }
            },
            error: function(xhr) {
                // Restore button state
                $indicatorLabel.show();
                $indicatorProgress.hide();
                $btn.prop('disabled', false);
                
                let errorMsg = 'An error occurred during verification';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
            }
        });
    });
});
</script>
@endpush

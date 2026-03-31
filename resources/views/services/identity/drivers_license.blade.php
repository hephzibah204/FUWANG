@extends('layouts.nexus')

@section('title', 'Drivers License Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(52, 211, 153, 0.1), rgba(16, 185, 129, 0.05)); border-color: rgba(52, 211, 153, 0.2);">
        <div class="sh-icon" style="background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3);">
            <i class="fa-solid fa-address-card"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Drivers License Verification</h1>
            <p class="text-muted small">Verify identity via the FRSC Drivers License database.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-shield-check text-success"></i> Official FRSC Data</span>
            <span class="badge-accent"><i class="fa-solid fa-bolt text-warning"></i> Instant Result</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Verify License</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Verification Vault ({{ $myResults->count() }})</button>
            </div>

            <div id="panel-verify" class="main-panel active">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="panel-card p-4">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                                <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Verification Details</h2>
                                <span class="ml-auto badge badge-primary py-2 px-3" id="price-tag" data-price="{{ (float) ($price ?? 300) }}">₦{{ number_format((float) ($price ?? 300), 2) }}</span>
                            </div>

                <form id="dlForm" action="{{ route('services.drivers_license.verify') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label for="license_no" class="font-weight-600 mb-2 small text-muted">License Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-hashtag"></i>
                            <input type="text" id="license_no" name="license_no" class="form-control" placeholder="ABC123456789" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="firstname" class="font-weight-600 mb-2 small text-muted">First Name</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="John" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="lastname" class="font-weight-600 mb-2 small text-muted">Last Name</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Doe" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="dob" class="font-weight-600 mb-2 small text-muted">Date of Birth</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-calendar"></i>
                            <input type="date" id="dob" name="dob" class="form-control" required>
                        </div>
                        <small class="text-muted mt-2 d-block"><i class="fa-solid fa-circle-info mr-1"></i> Format: YYYY-MM-DD</small>
                    </div>

                    @if(isset($dlProviders) && $dlProviders->count() > 1)
                    <div class="form-group mb-4">
                        <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Preferred Provider</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-server"></i>
                            <select id="api_provider_id" name="api_provider_id" class="form-control">
                                @foreach($dlProviders as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @elseif(isset($dlProviders) && $dlProviders->count() == 1)
                        <input type="hidden" id="api_provider_id" name="api_provider_id" value="{{ $dlProviders->first()->id }}">
                    @endif

                    <div class="form-group mb-4">
                        <label for="verification_type" class="font-weight-600 mb-2 small text-muted">Verification Type</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-layer-group"></i>
                            <select id="verification_type" name="verification_type" class="form-control"></select>
                        </div>
                        <small class="text-muted mt-2 d-block" id="typeHint" style="display:none;"></small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 55px;">
                        <i class="fa-solid fa-bolt mr-2"></i> Verify Identity
                    </button>
                </form>

                <!-- Results Display Area -->
                <div id="resultArea" class="mt-4" style="display: none;">
                    <div class="p-4 rounded-lg" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="text-center mb-4">
                            <div id="res-photo" class="mx-auto mb-3" style="width: 120px; height: 120px; border-radius: 15px; background: rgba(0,0,0,0.2); border: 2px solid var(--clr-primary); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-user-tie fa-4x opacity-20"></i>
                            </div>
                            <h4 id="res-name" class="h5 font-weight-bold text-white mb-1">-</h4>
                            <div class="d-flex flex-column gap-2 align-items-center">
                                <span class="badge badge-success py-2 px-3 mb-2"><i class="fa-solid fa-check-circle mr-1"></i> Verified Policy Holder</span>
                                <a href="#" id="downloadReport" class="btn btn-primary btn-sm d-none">
                                    <i class="fa-solid fa-file-pdf mr-1"></i> Download Certificate
                                </a>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6 mb-3">
                                <label class="small text-muted d-block mb-1">License No.</label>
                                <strong id="res-license" class="text-white">-</strong>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small text-muted d-block mb-1">State of Issue</label>
                                <strong id="res-state" class="text-white">-</strong>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small text-muted d-block mb-1">Expiry Date</label>
                                <strong id="res-expiry" class="text-white">-</strong>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small text-muted d-block mb-1">Gender</label>
                                <strong id="res-gender" class="text-white">-</strong>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-success">Field Match Status</span>
                                <div id="res-matches" class="d-flex gap-2">
                                    <span class="badge badge-pill badge-success">Firstname <i class="fa-solid fa-check ml-1"></i></span>
                                    <span class="badge badge-pill badge-success">Lastname <i class="fa-solid fa-check ml-1"></i></span>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-outline-light w-100 mt-4" onclick="window.location.reload()">
                            <i class="fa-solid fa-rotate-left mr-2"></i> New Verification
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4">
                <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Verification Guide</h3>
                <ul class="list-unstyled p-0">
                    <li class="d-flex gap-3 mb-4">
                        <div class="badge-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="fa-solid fa-1"></i></div>
                        <div>
                            <h6 class="mb-1 small font-weight-bold text-white">Input Details</h6>
                            <p class="small text-muted m-0">Ensure the License Number, Name, and Date of Birth match the official document.</p>
                        </div>
                    </li>
                    <li class="d-flex gap-3 mb-4">
                        <div class="badge-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-2"></i></div>
                        <div>
                            <h6 class="mb-1 small font-weight-bold text-white">Instant Search</h6>
                            <p class="small text-muted m-0">Our system connects directly to FRSC servers via VerifyMe to retrieve verified data.</p>
                        </div>
                    </li>
                    <li class="d-flex gap-3 mb-4">
                        <div class="badge-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="fa-solid fa-3"></i></div>
                        <div>
                            <h6 class="mb-1 small font-weight-bold text-white">Balance Check</h6>
                            <p class="small text-muted m-0">A fee of <span id="fee-text">₦{{ number_format((float) ($price ?? 300), 2) }}</span> will be deducted per search attempt.</p>
                        </div>
                    </li>
                </ul>

                <div class="mt-4 p-4 rounded-xl text-center" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);">
                    <i class="fa-solid fa-circle-user fa-3x mb-3 text-muted"></i>
                    <h5 class="h6 mb-2">Identify Policy Holders</h5>
                    <p class="small text-muted mb-0">Identity verification helps reduce fraud and ensures the integrity of your platform users.</p>
                </div>
            </div>
        </div>
            </div>
        </div>

        <!-- Vault Panel -->
        <div id="panel-vault" class="main-panel col-lg-12" style="display: none;">
            <div class="panel-card p-4">
                <h3 class="h6 font-weight-bold mb-4">FRSC Verification History</h3>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Name</th>
                                <th>License No</th>
                                <th>Date</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myResults as $res)
                                <tr>
                                    <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                    <td>{{ $res->response_data['firstname'] ?? 'N/A' }} {{ $res->response_data['lastname'] ?? '' }}</td>
                                    <td>{{ $res->identifier }}</td>
                                    <td>{{ $res->created_at->format('M d, Y') }}</td>
                                    <td class="text-right">
                                        <button class="btn btn-xs btn-outline-primary" onclick='viewResult(@json($res->response_data))'>
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">No records found in vault.</td>
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
    .tab-strip { display: flex; gap: 0; border-bottom: 2px solid rgba(255,255,255,0.05); margin-bottom: 20px; }
    .s-tab { padding: 12px 25px; background: none; border: none; color: var(--clr-text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.3s; }
    .s-tab.active { color: #34d399; border-bottom-color: #34d399; }
    .main-panel { display: none; }
    .main-panel.active { display: block; }
    
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 24px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .badge-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; }
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
</style>
@endpush

@push('scripts')
<script>
    function switchMainPanel(panel, btn) {
        $('.main-panel').hide().removeClass('active');
        $('#panel-' + panel).show().addClass('active');
        $('.s-tab').removeClass('active');
        $(btn).addClass('active');
    }

    function viewResult(data) {
        $('#dlForm').hide();
        $('#panel-vault').hide();
        
        let d = data;
        $('#res-name').text(d.firstname + ' ' + (d.lastname || ''));
        $('#res-license').text(d.licenseNo || d.number || 'N/A');
        $('#res-state').text(d.stateOfIssue || 'N/A');
        $('#res-expiry').text(d.expiryDate || 'N/A');
        $('#res-gender').text(d.gender || 'N/A');
        
        if (d.photo) {
            $('#res-photo').html(`<img src="data:image/jpeg;base64,${d.photo}" style="width: 100%; height: 100%; object-fit: cover;">`);
        }
        
        $('#resultArea').fadeIn();
    }

    $(document).ready(function() {
        const serviceType = 'drivers_license';
        let currentPrice = Number($('#price-tag').data('price') || {{ (float) ($price ?? 300) }});

        function setPriceTag(price) {
            currentPrice = Number(price || 0);
            $('#price-tag').data('price', currentPrice);
            $('#price-tag').text('₦' + currentPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#fee-text').text('₦' + currentPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        function loadTypes() {
            const providerId = $('#api_provider_id').val();
            if (!providerId) return;
            $.get(`{{ url('/services/providers') }}/${providerId}/types`, { service_type: serviceType })
                .done((res) => {
                    const select = $('#verification_type');
                    select.empty();
                    if (res.types && res.types.length > 0) {
                        $('#typeHint').text('Types and pricing are provider-specific.').show();
                        res.types.forEach(t => {
                            select.append(`<option value="${t.key}" data-price="${t.price}">${t.label} (₦${Number(t.price).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})})</option>`);
                        });
                        const first = select.find('option').first();
                        setPriceTag(first.data('price'));
                    } else {
                        $('#typeHint').hide();
                        select.append('<option value="">Standard</option>');
                        setPriceTag(res.provider?.price ?? {{ (float) ($price ?? 300) }});
                    }
                })
                .fail(() => {
                    $('#typeHint').text('Unable to load verification types.').show();
                });
        }

        $('#api_provider_id').on('change', function() {
            loadTypes();
        });

        $('#verification_type').on('change', function() {
            const p = $(this).find(':selected').data('price');
            if (p !== undefined) setPriceTag(p);
        });

        loadTypes();

        $('#dlForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();
            let price = Number(currentPrice || 0);

            Swal.fire({
                title: 'Confirm Verification',
                text: `A fee of ₦${parseFloat(price).toLocaleString()} will be charged. Continue?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Processing FRSC Data...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let data = response.data;
                                $('#dlForm').slideUp();
                                
                                // Update Results
                                $('#res-name').text(data.firstname + ' ' + data.lastname);
                                $('#res-license').text(data.licenseNo);
                                $('#res-state').text(data.stateOfIssue);
                                $('#res-expiry').text(data.expiryDate);
                                $('#res-gender').text(data.gender);
                                
                                if (data.photo) {
                                    $('#res-photo').html(`<img src="data:image/jpeg;base64,${data.photo}" style="width: 100%; height: 100%; object-fit: cover;">`);
                                }
                                
                                // Field Matches
                                let matchHtml = '';
                                if(data.fieldMatches) {
                                    Object.keys(data.fieldMatches).forEach(field => {
                                        let match = data.fieldMatches[field];
                                        matchHtml += `<span class="badge badge-pill badge-${match ? 'success' : 'danger'}">${field} <i class="fa-solid fa-${match ? 'check' : 'times'} ml-1"></i></span> `;
                                    });
                                }
                                $('#res-matches').html(matchHtml);
                                
                                if (response.result_id) {
                                    $('#downloadReport').attr('href', `/services/verification/report/${response.result_id}`).removeClass('d-none');
                                }

                                $('#resultArea').fadeIn();
                                
                                Swal.fire({ title: 'Verified!', text: 'Identity retrieved successfully.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Verification Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'System Error', text: 'Identity server is currently unreachable.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

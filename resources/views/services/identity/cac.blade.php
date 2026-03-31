@extends('layouts.nexus')

@section('title', 'CAC Business Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); border-color: rgba(59, 130, 246, 0.2);">
        <div class="sh-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3);">
            <i class="fa-solid fa-building-shield"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">CAC Business Verification</h1>
            <p class="text-muted small">Verify Corporate Affairs Commission (CAC) registration details for businesses.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-briefcase text-primary"></i> Entity Search</span>
            <span class="badge-accent"><i class="fa-solid fa-users-gear text-info"></i> Director Mapping</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Verify Business</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Verification Vault ({{ $myResults->count() }})</button>
            </div>

            <div id="panel-verify" class="main-panel active">
                <div class="panel-card p-4 mb-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Corporate Lookup</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3" id="price-tag" data-price="{{ (float) ($price ?? 500) }}">₦{{ number_format((float) ($price ?? 500), 2) }}</span>
                </div>

                <form id="cacForm" action="{{ route('services.cac_verify.verify') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-5 mb-4">
                            <label for="rc_number" class="font-weight-600 mb-2 small text-muted">Registration Number (RC/BN No.)</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-hashtag"></i>
                                <input type="text" id="rc_number" name="rc_number" class="form-control" placeholder="e.g. 092932" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="company_type" class="font-weight-600 mb-2 small text-muted">Company Type</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-landmark"></i>
                                <select id="company_type" name="company_type" class="form-control" required>
                                    <option value="RC">Limited Liability (RC)</option>
                                    <option value="BN">Business Name (BN)</option>
                                    <option value="IT">Incorporated Trustee (IT)</option>
                                    <option value="LL">Limited (LL)</option>
                                    <option value="LLP">Limited Liability Partnership (LLP)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 50px;">
                                <i class="fa-solid fa-search mr-2"></i> Verify Business
                            </button>
                        </div>
                    </div>

                    @if(isset($cacProviders) && $cacProviders->count() >= 1)
                        <div class="row mt-3">
                            <div class="col-md-5 mb-3">
                                <label class="font-weight-600 mb-2 small text-muted">Provider</label>
                                @if($cacProviders->count() > 1)
                                    <select id="api_provider_id" name="api_provider_id" class="form-control form-control-sm">
                                        @foreach($cacProviders as $provider)
                                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" id="api_provider_id" name="api_provider_id" value="{{ $cacProviders->first()->id }}">
                                    <div class="text-white font-weight-bold">{{ $cacProviders->first()->name }}</div>
                                    <div class="text-muted small">Only one provider is enabled.</div>
                                @endif
                            </div>
                            <div class="col-md-7 mb-3">
                                <label class="font-weight-600 mb-2 small text-muted">Verification Type</label>
                                <select id="verification_type" name="verification_type" class="form-control form-control-sm"></select>
                                <div class="text-muted small mt-1" id="typeHint" style="display:none;"></div>
                            </div>
                        </div>
                    @else
                        <input type="hidden" id="verification_type" name="verification_type" value="">
                    @endif
                </form>
            </div>
        </div>

        <div id="panel-vault" class="main-panel col-lg-12" style="display: none;">
            <div class="panel-card p-4">
                <h3 class="h6 font-weight-bold mb-4">CAC Verification History</h3>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Business Name</th>
                                <th>RC Number</th>
                                <th>Date</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myResults as $res)
                                <tr>
                                    <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                    <td>{{ $res->response_data['company_name'] ?? 'N/A' }}</td>
                                    <td>{{ $res->identifier }}</td>
                                    <td>{{ $res->created_at->format('M d, Y') }}</td>
                                    <td class="text-right">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewResult('{{ $res->id }}')">View</button>
                                        <a href="#" class="btn btn-sm btn-success"><i class="fa-solid fa-file-pdf"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Result Area -->
        <div class="col-lg-12" id="resultArea" style="display: none;">
            <div class="row">
                <div class="col-lg-7">
                    <div class="panel-card p-4 mb-4 h-100">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-sq bg-primary-soft text-primary mr-3">
                                <i class="fa-solid fa-building fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="h5 font-weight-bold text-white mb-0" id="res-company-name">Business Name</h3>
                                <p class="x-small text-muted mb-0" id="res-rc">RC Number: -</p>
                            </div>
                            <span id="res-status" class="ml-auto badge badge-success py-2 px-3">ACTIVE</span>
                        </div>

                        <div class="row mt-4 g-4">
                            <div class="col-md-6 mb-3">
                                <label class="x-small text-muted d-block mb-1">Email Address</label>
                                <strong id="res-email" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="x-small text-muted d-block mb-1">Registration Date</label>
                                <strong id="res-date" class="text-white small">-</strong>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="x-small text-muted d-block mb-1">Full Registered Address</label>
                                <strong id="res-address" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="x-small text-muted d-block mb-1">City / LGA</label>
                                <strong id="res-lga" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="x-small text-muted d-block mb-1">State</label>
                                <strong id="res-state" class="text-white small">-</strong>
                            </div>
                        </div>
                        
                        <div id="res-actions"></div>
                        <button class="btn btn-outline-light btn-block mt-4" onclick="window.location.reload()">New Search</button>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="panel-card p-4 h-100">
                        <h4 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2"><i class="fa-solid fa-users mr-2 text-primary"></i> Directors & Personnel</h4>
                        <div id="directors-list" style="max-height: 400px; overflow-y: auto;">
                            <!-- Directors will be injected here -->
                        </div>
                        <div id="no-directors" class="text-center py-5 text-muted small" style="display: none;">
                            <i class="fa-solid fa-user-slash fa-2x mb-2 opacity-20"></i>
                            <p>No director data publicly disclosed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 24px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .x-small { font-size: 0.7rem; }
    .icon-sq { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .bg-primary-soft { background: rgba(59, 130, 246, 0.1); }
    
    .director-item { display: flex; align-items: center; padding: 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 15px; margin-bottom: 10px; }
    .dir-avatar { width: 35px; height: 35px; border-radius: 50%; background: var(--clr-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem; flex-shrink: 0; }
</style>
@endpush

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; border-bottom: 2px solid rgba(255, 255, 255, 0.05); }
    .s-tab { padding: 12px 25px; background: none; border: none; color: var(--clr-text-muted); font-weight: 600; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.2s; }
    .s-tab.active { color: #3b82f6; border-bottom-color: #3b82f6; }
    .main-panel { display: none; }
    .main-panel.active { display: block; }
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

    $(document).ready(function() {
        const serviceType = 'cac_verification';
        let currentPrice = Number($('#price-tag').data('price') || {{ (float) ($price ?? 500) }});

        function setPriceTag(price) {
            currentPrice = Number(price || 0);
            $('#price-tag').data('price', currentPrice);
            $('#price-tag').text('₦' + currentPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
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
                        setPriceTag(res.provider?.price ?? {{ (float) ($price ?? 500) }});
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

        $('#cacForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Lookup',
                text: `A fee of ₦${Number(currentPrice || 0).toLocaleString()} will be charged for this CAC search. Continue?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking CAC Registry...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let data = response.data;
                                $('#cacForm').parent().hide();
                                
                                $('#res-company-name').text(data.company_name || 'N/A');
                                $('#res-rc').text('RC Number: ' + (data.rc_number || $('#rc_number').val()));
                                $('#res-email').text(data.email_address || 'N/A');
                                $('#res-date').text(data.date_of_registration || 'N/A');
                                $('#res-address').text(data.address || 'N/A');
                                $('#res-lga').text((data.lga || '') + ' ' + (data.city || ''));
                                $('#res-state').text(data.state || 'N/A');
                                $('#res-status').text(data.company_status || 'ACTIVE').removeClass('badge-success badge-warning').addClass(data.company_status == 'ACTIVE' ? 'badge-success' : 'badge-warning');

                                // Directors Logic
                                let dirList = $('#directors-list');
                                dirList.empty();
                                if (data.directors && data.directors.length > 0) {
                                    $('#no-directors').hide();
                                    data.directors.forEach(dir => {
                                        let initials = dir.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                                        dirList.append(`
                                            <div class="director-item animate-in">
                                                <div class="dir-avatar">${initials}</div>
                                                <div class="ml-3">
                                                    <h6 class="small font-weight-bold text-white mb-0">${dir.name}</h6>
                                                    <p class="x-small text-muted mb-0">${dir.designation}</p>
                                                </div>
                                            </div>
                                        `);
                                    });
                                } else {
                                    $('#no-directors').show();
                                }

                                $('#resultArea').fadeIn();

                                // Add PDF report button if result_id is present
                                if (response.result_id) {
                                    $('#res-actions').html('<a href="/services/identity/report/' + response.result_id + '" class="btn btn-success btn-block mt-4"><i class="fa-solid fa-file-pdf mr-2"></i> Download Verification Report</a>');
                                }

                                Swal.fire({ title: 'Success!', text: 'Business details retrieved.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Search Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'IdentityPay CAC services are currently busy.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

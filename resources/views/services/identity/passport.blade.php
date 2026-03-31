@extends('layouts.nexus')

@section('title', 'Passport Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(67, 56, 202, 0.05)); border-color: rgba(79, 70, 229, 0.2);">
        <div class="sh-icon" style="background: rgba(79, 70, 229, 0.15); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.3);">
            <i class="fa-solid fa-passport"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">International Passport</h1>
            <p class="text-muted small">Official passport data extraction and verification for international travel docs.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-earth-africa text-info"></i> Immigration Data</span>
            <span class="badge-accent"><i class="fa-solid fa-scan text-success"></i> OCR Matching</span>
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
                            <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Verification Query</h2>
                            <span class="ml-auto badge badge-primary py-2 px-3">₦500.00</span>
                        </div>

                        <form id="passportForm" action="{{ route('services.passport.verify') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-3 mb-4">
                                    <label for="mode" class="font-weight-600 mb-2 small text-muted">Verification Mode</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-sliders"></i>
                                        <select id="mode" name="mode" class="form-control" required>
                                            <option value="sync">Sync (Passport No + Name)</option>
                                            <option value="image">Image (OCR Extraction)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Sync Fields -->
                                <div class="col-md-3 mb-4 sync-field">
                                    <label for="number" class="font-weight-600 mb-2 small text-muted">Passport Number</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-hashtag"></i>
                                        <input type="text" id="number" name="number" class="form-control" placeholder="A00XXXXXX" required>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4 sync-field">
                                    <label for="last_name" class="font-weight-600 mb-2 small text-muted">Last Name</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-user"></i>
                                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Surname" required>
                                    </div>
                                </div>

                                <!-- Image Field -->
                                <div class="col-md-6 mb-4 image-field d-none">
                                    <label class="font-weight-600 mb-2 small text-muted d-block">Upload Passport Image</label>
                                    <div class="upload-box d-flex align-items-center" id="upload-trigger" style="height: 50px; background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1); border-radius: 10px; cursor: pointer; padding: 0 15px;">
                                        <i class="fa-solid fa-file-image mr-3 text-muted"></i>
                                        <span class="small text-muted" id="file-name">Click to select image (JPG/PNG)</span>
                                        <input type="file" id="passport_file" accept="image/*" class="d-none">
                                        <input type="hidden" name="image" id="image_data">
                                    </div>
                                </div>

                                <div class="col-md-3 mb-4">
                                    <label class="d-none d-md-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 50px;">
                                        <i class="fa-solid fa-bolt mr-2"></i> Verify Record
                                    </button>
                                </div>
                            </div>

                            @if(isset($passportProviders) && $passportProviders->count() > 1)
                            <div class="form-group mb-0">
                                <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Verification Source</label>
                                <select id="api_provider_id" name="api_provider_id" class="form-control form-control-sm" style="width: auto;">
                                    @foreach($passportProviders as $provider)
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
                        <!-- Passport Card Mockup -->
                        <div class="col-lg-5 mx-auto">
                            <div class="passport-card panel-card p-0 overflow-hidden" style="background: linear-gradient(135deg, #1e293b, #0f172a); border: 2px solid rgba(255,255,255,0.05);">
                                <div class="p-3 text-white border-bottom border-white-5 d-flex align-items-center" style="background: rgba(0,0,0,0.2);">
                                    <i class="fa-solid fa-passport mr-2 text-indigo"></i>
                                    <span class="x-small font-weight-bold uppercase tracking-widest">Passport Verification Record</span>
                                    <span class="ml-auto badge badge-success x-small" id="res-status">VALID</span>
                                </div>
                                
                                <div class="p-4">
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="rounded-lg overflow-hidden border border-white-10 mb-2" style="aspect-ratio: 3/4; background: rgba(255,255,255,0.02);">
                                                <img id="res-photo" src="" alt="Holder Photo" class="w-100 h-100" style="object-fit: cover;">
                                            </div>
                                            <div class="signature-box p-1 text-center border border-white-5 rounded" style="background: #fff; filter: grayscale(1) contrast(1.5);">
                                                <img id="res-signature" src="" alt="Signature" class="mw-100" style="max-height: 40px;">
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="mb-3">
                                                <label class="x-small text-muted mb-0 d-block">Surname</label>
                                                <strong id="res-lastname" class="text-white small">-</strong>
                                            </div>
                                            <div class="mb-3">
                                                <label class="x-small text-muted mb-0 d-block">Given Names</label>
                                                <strong id="res-firstname" class="text-white small">-</strong>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <label class="x-small text-muted mb-0 d-block">Gender</label>
                                                    <strong id="res-gender" class="text-white small">-</strong>
                                                </div>
                                                <div class="col-6">
                                                    <label class="x-small text-muted mb-0 d-block">Nationality</label>
                                                    <strong class="text-white small">NIGERIAN</strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-6 mt-3">
                                            <label class="x-small text-muted mb-0 d-block">Passport No.</label>
                                            <strong id="res-number" class="text-white small">-</strong>
                                        </div>
                                        <div class="col-6 mt-3">
                                            <label class="x-small text-muted mb-0 d-block">Date of Birth</label>
                                            <strong id="res-dob" class="text-white small">-</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="x-small text-muted mb-0 d-block">Date of Issue</label>
                                            <strong id="res-issued" class="text-white small">-</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="x-small text-muted mb-0 d-block">Date of Expiry</label>
                                            <strong id="res-expiry" class="text-white small text-danger">-</strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-3 border-top border-white-5" style="background: rgba(0,0,0,0.1);">
                                    <div class="machine-readable-zone font-monospace text-muted" style="letter-spacing: 2px; font-size: 0.6rem;">
                                        P&lt;NGA<span id="mrz-name"></span>&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;<br>
                                        <span id="mrz-no"></span>&lt;&lt;NGA<span id="mrz-dob"></span>&lt;<span id="mrz-gender"></span>&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mt-4 text-center">
                            <button class="btn btn-outline-light btn-wide" onclick="window.location.reload()">New Verification</button>
                            <a href="#" id="downloadReport" class="btn btn-primary ml-2 py-2 d-none">
                                <i class="fa-solid fa-file-pdf mr-1"></i> Download Certificate
                            </a>
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
    .font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; }
    .btn-wide { padding-left: 50px; padding-right: 50px; border-radius: 12px; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#mode').on('change', function() {
            if ($(this).val() === 'sync') {
                $('.sync-field').removeClass('d-none');
                $('.image-field').addClass('d-none');
                $('#number, #last_name').prop('required', true);
            } else {
                $('.sync-field').addClass('d-none');
                $('.image-field').removeClass('d-none');
                $('#number, #last_name').prop('required', false);
            }
        });

        $('#upload-trigger').on('click', function() {
            $('#passport_file').trigger('click');
        });

        $('#passport_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                $('#file-name').text(file.name);
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image_data').val(e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $('#passportForm').on('submit', function(e) {
            e.preventDefault();
            if ($('#mode').val() === 'image' && !$('#image_data').val()) {
                Swal.fire({ title: 'Image Required', text: 'Please upload a passport photo.', icon: 'warning', background: '#0a0a0f', color: '#fff' });
                return;
            }

            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Verification',
                text: "A fee of ₦500.00 will be charged for this passport lookup. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Investigating Record...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let d = response.data;
                                $('#searchPanel').hide();
                                
                                $('#res-lastname').text(d.last_name || 'N/A');
                                $('#res-firstname').text((d.first_name + ' ' + (d.middle_name || '')).trim());
                                $('#res-gender').text(d.gender || 'N/A');
                                $('#res-number').text(d.number || 'N/A');
                                $('#res-dob').text(d.dob || 'N/A');
                                $('#res-issued').text(d.issued_date || 'N/A');
                                $('#res-expiry').text(d.expiry_date || 'N/A');
                                $('#res-photo').attr('src', d.photo);
                                $('#res-signature').attr('src', d.signature);

                                // MRZ Generation
                                let mrzName = (d.last_name || '').padEnd(30, '<').toUpperCase();
                                let mrzNo = (d.number || '').padEnd(9, '<').toUpperCase();
                                $('#mrz-name').text(mrzName);
                                $('#mrz-no').text(mrzNo);
                                $('#mrz-dob').text((d.dob || '').replace(/-/g, '').substring(2));
                                $('#mrz-gender').text(d.gender ? d.gender.substring(0,1).toUpperCase() : '<');

                                if (response.result_id) {
                                    $('#downloadReport').attr('href', `/services/verification/report/${response.result_id}`).removeClass('d-none');
                                }

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', text: 'Passport record retrieved.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Search Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'Immigration service bridge is currently busy.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

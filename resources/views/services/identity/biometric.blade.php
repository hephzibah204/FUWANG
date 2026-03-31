@extends('layouts.nexus')

@section('title', 'Biometric Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05)); border-color: rgba(139, 92, 246, 0.2);">
        <div class="sh-icon" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.3);">
            <i class="fa-solid fa-face-viewfinder"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Biometric Verification</h1>
            <p class="text-muted small">Verify identity matches via AI facial recognition technology.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-microchip text-primary"></i> Neural Matching</span>
            <span class="badge-accent"><i class="fa-solid fa-eye text-info"></i> Anti-Spoofing</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="panel-card p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-camera mr-2 text-primary"></i> Biometric Auth</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦1,000.00</span>
                </div>

                <form id="bioForm" action="{{ route('services.biometric.verify') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="id_type" class="font-weight-600 mb-2 small text-muted">Identity Type</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-list-check"></i>
                                <select id="id_type" name="id_type" class="form-control" required>
                                    <option value="nin">NIN (National Identity)</option>
                                    <option value="bvn">BVN (Bank Verification)</option>
                                    <option value="frsc">FRSC (Drivers License)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="id_number" class="font-weight-600 mb-2 small text-muted">ID Number</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-hashtag"></i>
                                <input type="text" id="id_number" name="id_number" class="form-control" placeholder="Identity No." required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="photo" class="font-weight-600 mb-2 small text-muted">Selfie / Profile Image</label>
                        <div class="upload-area p-5 text-center" id="drop-area" style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 20px; background: rgba(255,255,255,0.02); cursor: pointer; transition: 0.3s;">
                            <input type="file" id="photo" name="photo" class="d-none" accept="image/*" required>
                            <div id="preview-placeholder">
                                <i class="fa-solid fa-cloud-arrow-up fa-3x mb-3 text-primary"></i>
                                <h5 class="h6 mb-1">Upload Photo</h5>
                                <p class="small text-muted m-0">Drag and drop or click to browse (Max 1MB)</p>
                            </div>
                            <div id="image-preview" class="d-none text-center">
                                <img id="preview-img" src="" style="max-height: 200px; border-radius: 15px; border: 2px solid var(--clr-primary);">
                                <p class="small text-primary mt-2 mb-0" id="filename-label"></p>
                            </div>
                        </div>
                    </div>

                    @if(isset($bioProviders) && $bioProviders->count() > 1)
                    <div class="form-group mb-4">
                        <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Provider</label>
                        <select id="api_provider_id" name="api_provider_id" class="form-control">
                            @foreach($bioProviders as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 55px;">
                        <i class="fa-solid fa-bolt mr-2"></i> Run Biometric Check
                    </button>
                </form>

                <!-- Results display -->
                <div id="resultArea" class="mt-4" style="display: none;">
                    <div class="p-4 rounded-xl" style="background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.15);">
                        <div class="d-flex align-items-center mb-4">
                           <div id="res-photo" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--clr-primary); overflow: hidden;">
                                <img src="" style="width: 100%; height: 100%; object-fit: cover;">
                           </div>
                           <div class="ml-3">
                                <h4 id="res-name" class="h6 font-weight-bold text-white mb-0">-</h4>
                                <div id="res-score-badge" class="badge badge-success mt-2">Match: 100%</div>
                           </div>
                        </div>

                        <div class="row small text-muted">
                            <div class="col-6 mb-2">Phone: <span id="res-phone" class="text-white">-</span></div>
                            <div class="col-6 mb-2">DOB: <span id="res-dob" class="text-white">-</span></div>
                            <div class="col-6">Nationality: <span id="res-nation" class="text-white">-</span></div>
                            <div class="col-6">Gender: <span id="res-gender" class="text-white">-</span></div>
                        </div>

                        <div class="mt-4 p-3 rounded" style="background: rgba(255,255,255,0.03);">
                             <h6 class="small font-weight-bold text-primary mb-2">Bio-Match Score</h6>
                             <div class="progress" style="height: 8px; background: rgba(255,255,255,0.05);">
                                <div id="res-progress" class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                             </div>
                             <div class="d-flex justify-content-between mt-2 x-small opacity-50">
                                <span>Thresh: 80%</span>
                                <span id="res-score-text">Current: 0%</span>
                             </div>
                        </div>

                        <button class="btn btn-outline-light w-100 mt-4" onclick="window.location.reload()">New Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4">
                <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Why Biometrics?</h3>
                <div class="mb-4 d-flex align-items-start gap-3">
                    <div class="badge-icon-bg" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <h6 class="small font-weight-bold">Zero Fraud Tolerance</h6>
                        <p class="x-small text-muted mb-0">Biometric matching ensures that the person performing the action is the actual owner of the ID.</p>
                    </div>
                </div>
                <div class="mb-4 d-flex align-items-start gap-3">
                    <div class="badge-icon-bg" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="fa-solid fa-robot"></i></div>
                    <div>
                        <h6 class="small font-weight-bold">AI Driven Logic</h6>
                        <p class="x-small text-muted mb-0">VerifyMe Uses advanced neural networks to compare your snapshot with government records.</p>
                    </div>
                </div>
                
                <div class="alert alert-warning p-3 rounded-xl mt-4" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fa-solid fa-lightbulb text-warning"></i>
                        <p class="x-small text-muted m-0">Ensure your photo has good lighting and a clear view of your face for the best match score.</p>
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
    .upload-area:hover { background: rgba(255,255,255,0.05) !important; border-color: var(--clr-primary) !important; }
    .badge-icon-bg { width: 35px; height: 35px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .x-small { font-size: 0.7rem; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Photo preview logic
        $('#drop-area').on('click', function() { $('#photo').click(); });
        $('#photo').on('change', function(e) {
            let file = e.target.files[0];
            if (file) {
                if (file.size > 1024 * 1024) {
                    Swal.fire({ title: 'Image too large', text: 'Max size is 1MB', icon: 'error', background: '#0a0a0f', color: '#fff' });
                    $(this).val('');
                    return;
                }
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('#preview-img').attr('src', event.target.result);
                    $('#preview-placeholder').addClass('d-none');
                    $('#image-preview').removeClass('d-none');
                    $('#filename-label').text(file.name);
                }
                reader.readAsDataURL(file);
            }
        });

        $('#bioForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let formData = new FormData(this);

            Swal.fire({
                title: 'Confirm Verification',
                text: "A fee of ₦1,000.00 will be charged. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8b5cf6',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Analyzing Biometrics...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status) {
                                let data = response.data;
                                $('#bioForm').slideUp();
                                
                                $('#res-name').text(data.firstname + ' ' + (data.middlename || '') + ' ' + data.lastname);
                                $('#res-phone').text(data.phone || 'N/A');
                                $('#res-dob').text(data.birthdate);
                                $('#res-nation').text(data.nationality || 'N/A');
                                $('#res-gender').text(data.gender);
                                
                                if (data.photo) {
                                    $('#res-photo img').attr('src', 'data:image/jpeg;base64,' + data.photo);
                                }

                                if (data.photoMatching) {
                                    let score = data.photoMatching.matchScore.toFixed(2);
                                    $('#res-score-badge').text('Match: ' + score + '%');
                                    $('#res-progress').css('width', score + '%');
                                    $('#res-score-text').text('Current: ' + score + '%');
                                }

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Matched!', text: 'Identity verified with biometrics.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Verification Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Run Biometric Check');
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'System Error', text: 'Cloud AI services are currently busy.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Run Biometric Check');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

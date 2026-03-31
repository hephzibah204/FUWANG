@extends('layouts.nexus')

@section('title', 'NIN with Face Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(219, 39, 119, 0.05)); border-color: rgba(236, 72, 153, 0.2);">
        <div class="sh-icon" style="background: rgba(236, 72, 153, 0.15); color: #ec4899; border: 1px solid rgba(236, 72, 153, 0.3);">
            <i class="fa-solid fa-user-gear"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">NIN with Face Verification</h1>
            <p class="text-muted small">Verify identity by matching live face against NIMC biometric database.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-face-smile text-pink"></i> Biometric Match</span>
            <span class="badge-accent"><i class="fa-solid fa-id-card text-info"></i> Official NIMC</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel-card p-4 mb-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-camera mr-2 text-primary"></i> Biometric Auth</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦500.00</span>
                </div>

                <form id="ninFaceForm" action="{{ route('services.nin_face.verify') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-5 mb-4 border-right border-white-5">
                            <label for="number" class="font-weight-600 mb-2 small text-muted">NIN Number</label>
                            <div class="input-wrap mb-4">
                                <i class="fa-solid fa-id-card-clip"></i>
                                <input type="text" id="number" name="number" class="form-control" placeholder="Enter 11-digit NIN" required maxlength="11">
                            </div>

                            <div class="upload-area" id="upload-box">
                                <input type="file" id="photo_file" accept="image/*" class="d-none">
                                <input type="hidden" name="image" id="image_data">
                                <div class="text-center py-4" id="upload-trigger" style="cursor: pointer;">
                                    <div class="icon-circle mx-auto bg-pink-soft text-pink mb-3" style="width: 50px; height: 50px;">
                                        <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                                    </div>
                                    <h6 class="small font-weight-bold text-white mb-1">Upload Face Image</h6>
                                    <p class="x-small text-muted px-3">JPG, PNG or Base64 supported. Max 1MB.</p>
                                </div>
                                <div id="preview-area" class="text-center p-2" style="display: none;">
                                    <img id="image-preview" src="#" alt="Preview" class="img-fluid rounded-lg border border-white-10 mb-2" style="max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" id="remove-img">Change Image</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 d-flex flex-column justify-content-between pb-4">
                            <div>
                                <h6 class="small font-weight-bold text-white mb-3">Verification Instructions</h6>
                                <ul class="text-muted x-small p-0 pl-3 m-0" style="list-style-type: decimal;">
                                    <li class="mb-2">Ensure your face is well-lit and clearly visible.</li>
                                    <li class="mb-2">Do not wear glasses, hats, or masks.</li>
                                    <li class="mb-2">The image should strictly belong to the NIN holder.</li>
                                    <li class="mb-2">Artificial intelligence will compare the uploaded photo with the NIMC registry.</li>
                                </ul>
                            </div>

                            @if(isset($faceProviders) && $faceProviders->count() > 1)
                            <div class="form-group mb-3">
                                <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Registry Source</label>
                                <select id="api_provider_id" name="api_provider_id" class="form-control form-control-sm">
                                    @foreach($faceProviders as $provider)
                                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-4" id="submit-btn" style="height: 55px;">
                                <i class="fa-solid fa-face-viewfinder mr-2"></i> Match Face & Verify
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Result Area -->
        <div class="col-lg-12" id="resultArea" style="display: none;">
            <div class="row">
                <div class="col-lg-4">
                    <div class="panel-card p-4 text-center h-100">
                        <div id="face-match-indicator" class="mb-4">
                            <div class="mx-auto rounded-circle overflow-hidden mb-3" style="width: 120px; height: 120px; border: 4px solid #ec4899; box-shadow: 0 0 20px rgba(236, 72, 153, 0.3);">
                                <img id="res-photo" src="" alt="Registry Photo" class="w-100 h-100" style="object-fit: cover; filter: brightness(1.1);">
                            </div>
                            <h4 id="res-face-status" class="h6 font-weight-bold text-white mb-1">Face Match</h4>
                            <p id="res-face-msg" class="x-small text-success m-0">Biometric Identity Confirmed</p>
                        </div>
                        <div class="border-top border-white-5 mt-4 pt-4 text-center">
                            <i class="fa-solid fa-certificate text-primary mb-2"></i>
                            <p class="x-small text-muted font-italic px-4">"This identity has been digitally verified against the NIMC central repository."</p>
                        </div>
                        <button class="btn btn-outline-light btn-block mt-4" onclick="window.location.reload()">New Search</button>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="panel-card p-4 h-100">
                        <h4 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Full Identity Profile</h4>
                        <div class="row g-4">
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">Full Name</label>
                                <strong id="res-fullname" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">NIN</label>
                                <strong id="res-nin" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">Birth Date</label>
                                <strong id="res-dob" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">Gender</label>
                                <strong id="res-gender" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">Religion</label>
                                <strong id="res-religion" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">Profession</label>
                                <strong id="res-profession" class="text-white small">-</strong>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="x-small text-muted d-block mb-1">Residential Address</label>
                                <strong id="res-address" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">LGA of Origin</label>
                                <strong id="res-origin-lga" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">State of Origin</label>
                                <strong id="res-origin-state" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="x-small text-muted d-block mb-1">Telephone</label>
                                <strong id="res-phone" class="text-white small">-</strong>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-lg border border-white-10" style="background: rgba(255,255,255,0.02);">
                            <h6 class="x-small font-weight-bold text-muted mb-2 uppercase tracking-wider">Next of Kin</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="x-small text-muted d-block mb-1">Name</label>
                                    <strong id="res-nok-name" class="text-white small">-</strong>
                                </div>
                                <div class="col-md-6">
                                    <label class="x-small text-muted d-block mb-1">Relation Address</label>
                                    <strong id="res-nok-address" class="text-white small">-</strong>
                                </div>
                            </div>
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
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 1.5rem; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 1.25rem; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 3.5rem !important; height: 3.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 1rem; }
    .upload-area { border: 2px dashed rgba(236, 72, 153, 0.2); border-radius: 1.25rem; background: rgba(236, 72, 153, 0.05); transition: all 0.3s ease; }
    .upload-area:hover { border-color: #ec4899; background: rgba(236, 72, 153, 0.1); }
    .service-header-card { border: var(--border-glass); border-radius: 1.5rem; padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; }
    .sh-icon { width: 4.5rem; height: 4.5rem; border-radius: 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 0.5rem 1rem; border-radius: 5rem; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 0.5rem; }
    .x-small { font-size: 0.75rem; }
    .text-pink { color: #ec4899; }
    .bg-pink-soft { background: rgba(236, 72, 153, 0.1); }
    .icon-circle { border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .mx-auto { margin-left: auto; margin-right: auto; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#upload-trigger').on('click', function() {
            $('#photo_file').trigger('click');
        });

        $('#photo_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 1024 * 1024) {
                    Swal.fire({ title: 'File Too Large', text: 'Please select an image smaller than 1MB.', icon: 'warning', background: '#0a0a0f', color: '#fff' });
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result);
                    $('#image_data').val(e.target.result);
                    $('#upload-trigger').hide();
                    $('#preview-area').show();
                }
                reader.readAsDataURL(file);
            }
        });

        $('#remove-img').on('click', function() {
            $('#photo_file').val('');
            $('#image_data').val('');
            $('#preview-area').hide();
            $('#upload-trigger').show();
        });

        $('#ninFaceForm').on('submit', function(e) {
            e.preventDefault();
            if (!$('#image_data').val()) {
                Swal.fire({ title: 'Photo Required', text: 'Please upload or capture a face photo.', icon: 'warning', background: '#0a0a0f', color: '#fff' });
                return;
            }

            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Verification',
                text: "A fee of ₦500.00 will be charged for this biometric search. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ec4899',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Matching Biometrics...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let nin = response.nin_data;
                                let face = response.face_data;
                                
                                $('#ninFaceForm').parent().parent().parent().hide();
                                
                                // Face Status
                                if (face && face.status === true) {
                                    $('#res-face-status').text('Face Match').addClass('text-success');
                                    $('#res-face-msg').text(face.message || 'Biometric Identity Confirmed').removeClass('text-danger').addClass('text-success');
                                } else {
                                    $('#res-face-status').text('Face Mismatch').addClass('text-danger');
                                    $('#res-face-msg').text(face ? face.message : 'Identity mismatch detected.').removeClass('text-success').addClass('text-danger');
                                }

                                // NIN Data
                                $('#res-photo').attr('src', nin.photo || $('#image_data').val());
                                $('#res-fullname').text(`${nin.title || ''} ${nin.firstname || ''} ${nin.pmiddlename || ''} ${nin.psurname || ''}`.trim() || 'N/A');
                                $('#res-nin').text(nin.nin || $('#number').val());
                                $('#res-dob').text(nin.birthdate || 'N/A');
                                $('#res-gender').text(nin.gender === 'f' ? 'Female' : 'Male');
                                $('#res-religion').text(nin.religion || 'N/A');
                                $('#res-profession').text(nin.profession || 'N/A');
                                $('#res-address').text(nin.residence_address || 'N/A');
                                $('#res-origin-lga').text(nin.self_origin_lga || 'N/A');
                                $('#res-origin-state').text(nin.self_origin_state || 'N/A');
                                $('#res-phone').text(nin.telephoneno || 'N/A');
                                $('#res-nok-name').text(`${nin.nok_firstname || ''} ${nin.nok_surname || ''}`.trim() || 'N/A');
                                $('#res-nok-address').text(nin.nok_address1 || 'N/A');

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', text: 'Identity Profile Retrieved.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Verification Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({ title: 'Error', text: 'Biometric server is currently unreachable.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

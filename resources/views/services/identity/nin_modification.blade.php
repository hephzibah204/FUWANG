@extends('layouts.nexus')

@section('title', 'NIN Modification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="NIN Modification"
        title-class="h4 font-weight-bold mb-1"
        subtitle="Update your name, DOB, phone number, or other details on your NIN record."
        subtitle-class="text-muted small"
        icon="fa-solid fa-file-pen"
        icon-style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff;"
        style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); border: 1px solid rgba(59, 130, 246, 0.2);"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-user-shield text-primary"></i> Authorized Agent</span>
            <span class="badge-accent"><i class="fa-solid fa-bolt text-warning"></i> Fast Sync</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <div class="alert alert-danger border-0 small mb-4" style="background: rgba(239, 68, 68, 0.1); color: #f87171;">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> 
                    <strong>Important:</strong> You cannot submit the same modification request to another platform while we process it. Violating this policy will result in no refund, as we incur costs for the modifications.
                </div>

                <form id="ninModForm" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Already have Self Service Account (Delinked Account)?</label>
                        <div class="d-flex gap-3">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="selfServiceYes" name="self_service" value="yes" class="custom-control-input">
                                <label class="custom-control-label text-white-50" for="selfServiceYes">Yes</label>
                            </div>
                            <div class="custom-control custom-radio ml-4">
                                <input type="radio" id="selfServiceNo" name="self_service" value="no" class="custom-control-input" checked>
                                <label class="custom-control-label text-white-50" for="selfServiceNo">No</label>
                            </div>
                        </div>
                        <div id="selfServiceWarning" class="alert alert-danger mt-3 small" style="display: none;">
                            <i class="fa-solid fa-circle-xmark mr-2"></i> 
                            Users with existing Delinked Self-Service accounts cannot continue with this registration.
                        </div>
                    </div>

                    <div id="formContent">
                        <div class="form-group mb-4">
                            <label class="font-weight-600 mb-2">National Identification Number (NIN)</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-id-card"></i>
                                <input type="text" name="nin" class="form-control" placeholder="Enter 11-digit NIN" maxlength="11" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-600 mb-2">Full Name (As on NIN)</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-600 mb-2">Select Modification Type</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-list-check"></i>
                                <select name="modification_type" id="modType" class="form-control" required>
                                    <option value="">--Select Modification Type--</option>
                                    <option value="name">Change of Name</option>
                                    <option value="dob">Date of Birth Modification</option>
                                    <option value="address">Change of Address</option>
                                    <option value="phone">Change of Phone Number</option>
                                    <option value="email">Change of Email</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dynamic Fields Container -->
                        <div id="dynamicFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2" id="oldValLabel">Old Value</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        <input type="text" name="old_value" id="oldValue" class="form-control" placeholder="Current detail on record">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2" id="newValLabel">New Value</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <input type="text" name="new_value" id="newValue" class="form-control" placeholder="New detail to be updated">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-600 mb-2">Supporting Photo/Document</label>
                                <div class="custom-file-upload p-4 text-center" style="border: 2px dashed rgba(255,255,255,0.1); background: rgba(255,255,255,0.02); border-radius: 15px;">
                                    <i class="fa-solid fa-cloud-arrow-up text-primary mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-white-50 small mb-2">Upload a clear photo or scanned document for verification</p>
                                    <input type="file" name="photo" class="form-control-file text-white-50 small" accept="image/*,.pdf" required>
                                </div>
                            </div>

                            <div class="alert alert-warning border-0 small mb-4" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24;">
                                <i class="fa-solid fa-circle-info mr-2"></i> 
                                Service Fee: <strong>₦{{ number_format($price, 2) }}</strong>. Ensure you have sufficient balance before submitting.
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Submit Modification Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3">How it works</h3>
                <ul class="process-list p-0 m-0">
                    <li>
                        <div class="p-icon">1</div>
                        <div class="p-text"><strong>Submit Request</strong> Provide your current NIN and the specific changes you need.</div>
                    </li>
                    <li>
                        <div class="p-icon">2</div>
                        <div class="p-text"><strong>Agent Review</strong> Our certified agents will review and process your request with NIMC.</div>
                    </li>
                    <li>
                        <div class="p-icon">3</div>
                        <div class="p-text"><strong>Instant Update</strong> Changes reflect on the NIMC portal immediately after processing.</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Consent & Authorization Modal -->
<div class="modal fade" id="consentModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0" style="background: #141826; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center">
                    <i class="fa-solid fa-shield-check text-primary mr-3" style="font-size: 1.5rem;"></i>
                    NIN Modification Consent & Authorization
                </h5>
            </div>
            <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                <div class="terms-content text-white-50 small line-height-relaxed">
                    <p class="mb-3 text-white">Welcome to the <strong>Fuwa.NG NIN Modification Suite</strong>. By proceeding, you acknowledge and agree to the following terms:</p>
                    
                    <div class="term-item mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <strong class="text-white d-block mb-1">1. Authorization of Agency</strong>
                        You hereby authorize Fuwa.NG and its certified processing partners to access and utilize your personal data, including your current NIN, to execute the requested record modifications.
                    </div>

                    <div class="term-item mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <strong class="text-white d-block mb-1">2. Independent Service Provider</strong>
                        You acknowledge that Fuwa.NG is an independent platform and is not officially affiliated with NIMC. You provide full, irrevocable consent for Fuwa.NG to act as your authorized agent in interfacing with relevant identity databases.
                    </div>

                    <div class="term-item mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <strong class="text-white d-block mb-1">3. Third-Party Representation</strong>
                        If you are an agent submitting on behalf of a client, you solemnly declare that you have obtained explicit, verifiable authorization from the NIN owner to modify their records.
                    </div>

                    <div class="term-item mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <strong class="text-white d-block mb-1">4. Alias Credentials Policy</strong>
                        To ensure processing efficiency, Fuwa.NG may utilize secure alias email addresses for system authentication. These aliases are for internal routing and may not provide external inbox access.
                    </div>

                    <div class="term-item mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <strong class="text-white d-block mb-1">5. Propagation & Syncing Delays</strong>
                        While modifications are reflected instantly on NIMC portals, third-party institutions (Banks, Telcos, etc.) may take significantly longer to sync. Fuwa.NG is not responsible for these external delays.
                    </div>

                    <div class="term-item mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <strong class="text-white d-block mb-1">6. Wallet & Refund Policy</strong>
                        All service fees are final. In the event of a technical processing failure, funds will be refunded to your Fuwa.NG wallet. Wallet balances are non-withdrawable.
                    </div>

                    <div class="term-item mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <strong class="text-white d-block mb-1">7. Exclusivity of Submission</strong>
                        You agree not to submit the same modification request on any other platform while it is active on Fuwa.NG. Duplicate submissions result in a forfeiture of the service fee.
                    </div>

                    <p class="mt-4 mb-0 text-center font-italic">By clicking "I Agree & Proceed," you confirm that you have read, understood, and voluntarily accepted these terms in their entirety.</p>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex gap-3 flex-column flex-sm-row">
                <button type="button" class="btn btn-outline-light rounded-pill px-4 flex-grow-1" onclick="window.location.href='{{ route('dashboard') }}'">Not Agreed</button>
                <button type="button" id="agreeBtn" class="btn btn-primary rounded-pill px-4 flex-grow-1">I Agree & Proceed</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .line-height-relaxed { line-height: 1.7; }
    .process-list { list-style: none; }
    .process-list li { display: flex; gap: 15px; margin-bottom: 20px; }
    .p-icon { width: 28px; height: 28px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; flex-shrink: 0; }
    .p-text { font-size: 0.85rem; color: var(--clr-text-muted); }
    .p-text strong { display: block; color: white; margin-bottom: 2px; }

    @media (max-width: 767.98px) {
        .service-page .panel-card {
            padding: 1rem !important;
        }
        .form-control, .btn {
            font-size: 0.95rem;
            padding: 0.85rem 1rem;
        }
        .custom-file-upload p {
            font-size: 0.8rem;
        }
        .modal-dialog {
            margin: 0.5rem;
        }
        .modal-content {
            border-radius: 15px !important;
        }
        .modal-body {
            max-height: 70vh;
        }
        .term-item {
            margin-bottom: 1rem !important;
        }
        .service-header .display-4 {
            font-size: 2.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        @if(!$user->nin_mod_consent)
            $('#consentModal').modal('show');
        @endif

        // Self Service Logic
        $('input[name="self_service"]').on('change', function() {
            if ($(this).val() === 'yes') {
                $('#selfServiceWarning').fadeIn();
                $('#formContent').fadeOut();
            } else {
                $('#selfServiceWarning').fadeOut();
                $('#formContent').fadeIn();
            }
        });

        // Dynamic Fields Logic
        $('#modType').on('change', function() {
            const type = $(this).val();
            if (!type) {
                $('#dynamicFields').fadeOut();
                return;
            }

            let oldLabel = 'Old Value';
            let newLabel = 'New Value';
            let oldPlaceholder = 'Current detail on record';
            let newPlaceholder = 'New detail to be updated';

            switch(type) {
                case 'name':
                    oldLabel = 'Current Full Name';
                    newLabel = 'Correct Full Name';
                    oldPlaceholder = 'e.g. Jamiu kowalski';
                    newPlaceholder = 'e.g. John Doe';
                    break;
                case 'dob':
                    oldLabel = 'Current Date of Birth';
                    newLabel = 'Correct Date of Birth';
                    oldPlaceholder = 'e.g. 1989-01-31';
                    newPlaceholder = 'e.g. 1990-01-01';
                    break;
                case 'address':
                    oldLabel = 'Current Address';
                    newLabel = 'Correct Address';
                    oldPlaceholder = 'Enter full old address';
                    newPlaceholder = 'Enter full new address';
                    break;
                case 'phone':
                    oldLabel = 'Current Phone Number';
                    newLabel = 'Correct Phone Number';
                    oldPlaceholder = 'e.g. 08012345679';
                    newPlaceholder = 'e.g. 08012345678';
                    break;
                case 'email':
                    oldLabel = 'Current Email';
                    newLabel = 'Correct Email';
                    oldPlaceholder = 'e.g. old@example.com';
                    newPlaceholder = 'e.g. user@example.com';
                    break;
            }

            $('#oldValLabel').text(oldLabel);
            $('#newValLabel').text(newLabel);
            $('#oldValue').attr('placeholder', oldPlaceholder);
            $('#newValue').attr('placeholder', newPlaceholder);
            
            $('#dynamicFields').fadeIn();
        });

        $('#agreeBtn').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...');

            $.ajax({
                url: "{{ route('services.nin.modification.consent') }}",
                method: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if (res.status) {
                        $('#consentModal').modal('hide');
                        Swal.fire({ title: 'Authorized!', text: 'You can now proceed with NIN modifications.', icon: 'success', background: '#141826', color: '#fff', timer: 2000, showConfirmButton: false });
                    }
                },
                error: function() {
                    btn.prop('disabled', false).text('I Agree & Proceed');
                    Swal.fire({ title: 'Error', text: 'Failed to save consent. Please try again.', icon: 'error', background: '#141826', color: '#fff' });
                }
            });
        });

        $('#ninModForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#submitBtn');
            const oldHtml = btn.html();
            
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Submitting Request...');

            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('services.nin.modification.submit') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status) {
                        Swal.fire({ title: 'Success!', text: res.message, icon: 'success', background: '#141826', color: '#fff' })
                        .then(() => {
                            window.location.href = "{{ route('history') }}";
                        });
                    } else {
                        Swal.fire({ title: 'Error', text: res.message, icon: 'error', background: '#141826', color: '#fff' });
                    }
                },
                error: function(xhr) {
                    let msg = 'Something went wrong. Please try again.';
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({ title: 'Failed', text: msg, icon: 'error', background: '#141826', color: '#fff' });
                },
                complete: function() {
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });
    });
</script>
@endpush

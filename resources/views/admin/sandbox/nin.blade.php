@extends('layouts.nexus')

@section('title', 'NIN Sandbox | Admin ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <div class="service-header-card mb-4">
        <div class="sh-icon nin-bg"><i class="fa-regular fa-id-card"></i></div>
        <div class="sh-text">
            <h1>NIN Suite (Admin Sandbox)</h1>
            <p>Admin-only entrypoint for testing. No wallet deductions.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4 mb-4">
                <form id="verifyForm" action="{{ route('admin.sandbox.nin.verify') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label for="nin" class="font-weight-600 mb-2">National Identification Number (NIN)</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-id-card"></i>
                            <input type="text" id="nin" name="nin" class="form-control" placeholder="Enter 11-digit NIN" maxlength="11" required>
                        </div>
                    </div>

                    @if(isset($providers) && $providers->count() > 0)
                        <div class="form-group mb-4">
                            <label for="api_provider_id" class="font-weight-600 mb-2">Provider</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-server text-primary"></i>
                                <select id="api_provider_id" name="api_provider_id" class="form-control bg-dark text-white border-primary" style="background: rgba(59, 130, 246, 0.05) !important;">
                                    <option value="">Auto</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="verify-btn">
                        <i class="fa-solid fa-flask mr-2"></i> Run Sandbox Check
                    </button>
                </form>

                <div id="resultContainer" class="mt-4" style="display:none;">
                    <div class="result-card-nexus">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-white font-weight-bold">Response</div>
                            <button type="button" class="btn btn-sm btn-outline" id="copyBtn"><i class="fa-regular fa-copy mr-2"></i>Copy</button>
                        </div>
                        <pre id="resultJson" style="white-space: pre-wrap; word-break: break-word; margin: 0; color: #e5e7eb;"></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3">Sandbox Notes</h3>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> No wallet deductions</li>
                    <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Uses configured APIs</li>
                    <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Suitable for admin testing</li>
                </ul>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline w-100 mt-2">Open Settings</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const $form = $('#verifyForm');
    const $btn = $('#verify-btn');
    const $resultContainer = $('#resultContainer');
    const $resultJson = $('#resultJson');
    const $copyBtn = $('#copyBtn');

    $copyBtn.on('click', function() {
        const text = $resultJson.text();
        if (!text) return;
        navigator.clipboard.writeText(text);
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Running...');
        $resultContainer.hide();
        $resultJson.text('');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function(res) {
                if (!res.status) {
                    Swal.fire({ title: 'Sandbox Error', text: res.message || 'Request failed', icon: 'error', background: '#0a0a0f', color: '#fff' });
                    return;
                }
                $resultJson.text(JSON.stringify(res.data, null, 2));
                $resultContainer.show();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Request failed';
                Swal.fire({ title: 'Sandbox Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush


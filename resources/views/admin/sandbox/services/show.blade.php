@extends('layouts.nexus')

@section('title', $serviceConfig['title'] . ' | Admin ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <div class="service-header-card mb-4">
        <div class="sh-icon" style="background: rgba(59,130,246,0.12); color: var(--clr-primary); border: 1px solid rgba(59,130,246,0.22);">
            <i class="fa-solid fa-flask"></i>
        </div>
        <div class="sh-text">
            <h1>{{ $serviceConfig['title'] }}</h1>
            <p>Admin-only entrypoint for testing. No wallet deductions.</p>
        </div>
        <div class="ml-auto d-none d-md-flex" style="gap: 10px;">
            <a href="{{ route('admin.sandbox.services.index') }}" class="btn btn-outline">All Sandboxes</a>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline">Settings</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4 mb-4">
                <form id="sandboxForm" action="{{ route('admin.sandbox.services.run', $service) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    @if($providers->count() > 0)
                        <div class="form-group mb-4">
                            <label class="font-weight-600 mb-2">Provider</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-server text-primary"></i>
                                <select name="api_provider_id" class="form-control bg-dark text-white border-primary" style="background: rgba(59, 130, 246, 0.05) !important;">
                                    <option value="">Auto</option>
                                    @foreach($providers as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        @foreach($serviceConfig['fields'] as $field)
                            @php
                                $name = $field['name'];
                                $label = $field['label'] ?? $name;
                                $type = $field['type'] ?? 'text';
                                $required = (bool) ($field['required'] ?? false);
                                $placeholder = $field['placeholder'] ?? '';
                                $options = $field['options'] ?? [];
                                $col = $type === 'textarea' ? 'col-12' : 'col-md-6';
                            @endphp

                            <div class="{{ $col }} mb-4">
                                <label class="font-weight-600 mb-2">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>

                                @if($type === 'select')
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-list"></i>
                                        <select name="{{ $name }}" class="form-control" {{ $required ? 'required' : '' }}>
                                            @foreach($options as $opt)
                                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($type === 'textarea')
                                    <textarea name="{{ $name }}" class="form-control" rows="4" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}></textarea>
                                @elseif($type === 'file')
                                    <input type="file" name="{{ $name }}" class="form-control" {{ $required ? 'required' : '' }}>
                                @else
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-pen"></i>
                                        <input type="{{ $type }}" name="{{ $name }}" class="form-control" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="runBtn">
                        <i class="fa-solid fa-flask mr-2"></i> Run Sandbox
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
                <h3 class="h6 font-weight-bold mb-3">Notes</h3>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Uses configured providers</li>
                    <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Does not debit users</li>
                    <li class="mb-2"><i class="fa-solid fa-circle-check text-success mr-2"></i> Returns raw JSON response</li>
                </ul>
                <a href="{{ route('admin.sandbox.services.index') }}" class="btn btn-outline w-100 mt-2">Back to Sandbox List</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-header-card { background: rgba(255,255,255,0.03); border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .sh-text h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
    .sh-text p { margin: 4px 0 0; color: var(--clr-text-muted); font-size: 0.95rem; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
    .result-card-nexus { background: rgba(255,255,255,0.02); border: var(--border-glass); border-radius: 18px; padding: 20px; }
    .btn-outline { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.14); color: #fff; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const $form = $('#sandboxForm');
    const $btn = $('#runBtn');
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

        const hasFile = $form.find('input[type="file"]').length > 0;
        const ajaxOptions = {
            url: $form.attr('action'),
            method: 'POST',
            success: function(res) {
                if (!res.status) {
                    Swal.fire({ title: 'Sandbox Error', text: res.message || 'Request failed', icon: 'error', background: '#0a0a0f', color: '#fff' });
                    return;
                }
                $resultJson.text(JSON.stringify(res, null, 2));
                $resultContainer.show();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Request failed';
                Swal.fire({ title: 'Sandbox Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        };

        if (hasFile) {
            const data = new FormData($form[0]);
            $.ajax({ ...ajaxOptions, data, processData: false, contentType: false });
            return;
        }

        $.ajax({ ...ajaxOptions, data: $form.serialize() });
    });
});
</script>
@endpush


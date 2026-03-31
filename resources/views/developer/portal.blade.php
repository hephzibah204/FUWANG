@extends('layouts.nexus')

@section('title', 'Developer Portal')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-white mb-1 font-weight-bold"><i class="fa-solid fa-code text-primary mr-2"></i> Developer Portal</h3>
        <p class="text-white-50 mb-0">Create API tokens and copy integration snippets for your app.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 col-12 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="text-white mb-0 fw-bold">API Tokens</h5>
                <button class="btn btn-primary rounded-pill px-4" onclick="openCreateTokenModal()"><i class="fa fa-plus mr-2"></i>Create</button>
            </div>

            <div class="text-white-50 small mb-3">
                Use your token on your server (recommended). Avoid exposing tokens directly in public frontend code.
            </div>

            <div class="table-responsive">
                <table class="table table-borderless text-white small mb-0">
                    <thead>
                        <tr class="text-white-50 border-bottom border-white-10">
                            <th>Name</th>
                            <th>Token</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tokens as $t)
                            <tr class="border-bottom border-white-10">
                                <td class="py-3">{{ $t->name }}</td>
                                <td class="py-3 font-monospace text-white-50">••••{{ $t->last_four ?? '----' }}</td>
                                <td class="py-3 text-white-50">{{ $t->rate_limit_per_minute ?? 60 }}/min</td>
                                <td class="py-3">
                                    @if($t->revoked_at)
                                        <span class="badge badge-danger">Revoked</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    @if(!$t->revoked_at)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="revokeToken({{ $t->id }})">Revoke</button>
                                    @else
                                        <span class="text-white-50">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-white-50">No API tokens yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7 col-12 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="text-white mb-0 fw-bold">Integration</h5>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-light rounded-pill px-4" href="{{ route('developer.docs') }}">
                        <i class="fa fa-book mr-2"></i>Tutorials
                    </a>
                    <a class="btn btn-outline-light rounded-pill px-4" href="{{ route('developer.openapi.v1') }}" target="_blank">
                        <i class="fa fa-file-code mr-2"></i>OpenAPI
                    </a>
                </div>
            </div>

            <div class="mb-3">
                <div class="text-white-50 small mb-2">Base URL</div>
                <div class="d-flex" style="gap: 10px;">
                    <input id="baseUrl" class="form-control text-white rounded-3 font-monospace" value="{{ $baseUrl }}" readonly style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    <button class="btn btn-outline-primary rounded-pill px-4" onclick="copyText('baseUrl')">Copy</button>
                </div>
            </div>

            <div class="mb-3">
                <div class="text-white-50 small mb-2">Auth header</div>
                <div class="d-flex" style="gap: 10px;">
                    <input id="authHeader" class="form-control text-white rounded-3 font-monospace" value="Authorization: Bearer nx_&lt;YOUR_TOKEN&gt;" readonly style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    <button class="btn btn-outline-primary rounded-pill px-4" onclick="copyText('authHeader')">Copy</button>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="text-white-50 small mb-2">cURL example (NIN verification)</div>
                    <div class="position-relative">
                        <button class="btn btn-sm btn-outline-light rounded-pill px-3 position-absolute" style="right: 10px; top: 10px;" onclick="copyBlock('curlExample')">Copy</button>
                        <pre id="curlExample" class="p-3 rounded-3 text-white small" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">curl -X POST "{{ $baseUrl }}/verifications/nin" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer nx_&lt;YOUR_TOKEN&gt;" \
  -d '{"number":"12345678901","firstname":"John","lastname":"Doe","dob":"1990-01-01","mode":"nin"}'</pre>
                    </div>
                </div>

                <div class="col-12">
                    <div class="text-white-50 small mb-2">fetch() example (server-side)</div>
                    <div class="position-relative">
                        <button class="btn btn-sm btn-outline-light rounded-pill px-3 position-absolute" style="right: 10px; top: 10px;" onclick="copyBlock('fetchExample')">Copy</button>
                        <pre id="fetchExample" class="p-3 rounded-3 text-white small" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">const res = await fetch("{{ $baseUrl }}/verifications/nin", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "Authorization": "Bearer nx_&lt;YOUR_TOKEN&gt;",
  },
  body: JSON.stringify({
    number: "12345678901",
    firstname: "John",
    lastname: "Doe",
    dob: "1990-01-01",
    mode: "nin"
  }),
});

const data = await res.json();</pre>
                    </div>
                </div>
            </div>

            <div class="text-white-50 small mt-3">
                Tip: If you must call from a browser app, route requests through your backend to keep your token secret.
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createTokenModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="background: #1a1f2e; border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-white fw-bold">Create API Token</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="createTokenForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">Token Name</label>
                        <input type="text" name="name" class="form-control text-white rounded-3" placeholder="e.g. My Web App" required style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div id="createdTokenWrap" style="display:none;">
                        <label class="text-white-50 small mb-2">Your new token (copy now)</label>
                        <div class="d-flex" style="gap: 10px;">
                            <input id="createdToken" class="form-control text-white rounded-3 font-monospace" readonly style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="copyText('createdToken')">Copy</button>
                        </div>
                        <div class="text-warning small mt-2">This token will not be shown again.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button id="createTokenBtn" type="submit" class="btn btn-primary rounded-pill px-4">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCreateTokenModal() {
    document.getElementById('createdTokenWrap').style.display = 'none';
    document.getElementById('createdToken').value = '';
    $('#createTokenModal').modal('show');
}

async function copyText(inputId) {
    const el = document.getElementById(inputId);
    const value = el.value || el.getAttribute('value') || '';
    try {
        await navigator.clipboard.writeText(value);
        Swal.fire({ icon: 'success', title: 'Copied', text: 'Copied to clipboard.', background: '#141826', color: '#fff', timer: 1200, showConfirmButton: false });
    } catch (e) {
        el.select();
        document.execCommand('copy');
        Swal.fire({ icon: 'success', title: 'Copied', text: 'Copied to clipboard.', background: '#141826', color: '#fff', timer: 1200, showConfirmButton: false });
    }
}

async function copyBlock(id) {
    const el = document.getElementById(id);
    const value = el.innerText || el.textContent || '';
    try {
        await navigator.clipboard.writeText(value);
        Swal.fire({ icon: 'success', title: 'Copied', text: 'Copied to clipboard.', background: '#141826', color: '#fff', timer: 1200, showConfirmButton: false });
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Copy failed.', background: '#141826', color: '#fff' });
    }
}

$('#createTokenForm').on('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('createTokenBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>Creating…';

    $.ajax({
        url: '{{ route("developer.tokens.create") }}',
        method: 'POST',
        data: new FormData(form),
        processData: false,
        contentType: false,
        success(res) {
            document.getElementById('createdToken').value = res.token;
            document.getElementById('createdTokenWrap').style.display = 'block';
            Swal.fire({ icon: 'success', title: 'Created', text: res.message, background: '#141826', color: '#fff' });
            setTimeout(() => location.reload(), 1400);
        },
        error(xhr) {
            const errs = xhr.responseJSON?.errors;
            const msg  = errs ? Object.values(errs).flat().join('\n') : (xhr.responseJSON?.message || 'Create failed.');
            Swal.fire({ icon: 'error', title: 'Error', text: msg, background: '#141826', color: '#fff' });
        },
        complete() {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
});

function revokeToken(id) {
    Swal.fire({
        icon: 'warning',
        title: 'Revoke token?',
        text: 'This will immediately stop the token from working.',
        showCancelButton: true,
        confirmButtonText: 'Revoke',
        confirmButtonColor: '#ef4444',
        background: '#141826',
        color: '#fff',
    }).then((r) => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: '{{ url("/developer/tokens") }}/' + id + '/revoke',
            method: 'POST',
            data: {_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            success(res) {
                Swal.fire({ icon: 'success', title: 'Done', text: res.message, background: '#141826', color: '#fff' });
                setTimeout(() => location.reload(), 900);
            },
            error(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Revoke failed.', background: '#141826', color: '#fff' });
            }
        });
    });
}
</script>
@endpush


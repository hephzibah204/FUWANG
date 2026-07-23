@extends('layouts.nexus')

@section('title', $docs['title'])

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="text-white mb-1 font-weight-bold"><i class="fa-solid fa-book text-primary mr-2"></i> {{ $docs['title'] }}</h3>
                <p class="text-white-50 mb-0">Live documentation for the currently enabled developer API endpoints.</p>
            </div>
            <a href="{{ route('developer.portal') }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="fa fa-arrow-left mr-2"></i>Back to Portal
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 d-none d-lg-block">
        <div class="card border-0 rounded-4 p-4 sticky-top" style="top: 100px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h6 class="text-white fw-bold mb-3">Contents</h6>
            <nav class="nav flex-column doc-nav">
                <a class="nav-link text-white-50 small px-0 py-1" href="#auth">1. Authentication</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#pricing">2. Developer Pricing</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#endpoints">3. Enabled Endpoints</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#practices">4. Best Practices</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#support">5. Support</a>
            </nav>
        </div>
    </div>

    <div class="col-lg-9 col-12">
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="prose text-white">
                <p class="text-white-50 lead mb-5">{{ $docs['intro'] }}</p>

                <section id="auth" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">1. Authentication</h4>
                    <p class="text-white-50">{{ $docs['auth'] }}</p>
                    <div class="code-block position-relative mb-4">
                        <button class="btn btn-sm btn-outline-light rounded-pill px-3 position-absolute" style="right: 10px; top: 10px;" onclick="copyBlock('curlAuth')">Copy</button>
                        <pre id="curlAuth" class="p-3 rounded-3 small text-white font-monospace" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">curl -X POST "{{ $baseUrl }}/verifications/nin" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer nx_&lt;YOUR_TOKEN&gt;" \
  -d '{"number":"12345678901","firstname":"John","lastname":"Doe","dob":"1990-01-01","mode":"nin","api_provider_id":1}'</pre>
                    </div>
                </section>

                <section id="pricing" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">2. Developer Pricing</h4>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm small text-white-50 mb-0">
                            <tbody>
                                <tr><td width="220" class="text-white">NIN Verification</td><td>₦{{ number_format($developerPricing['developer_api_nin_price'], 2) }}</td></tr>
                                <tr><td class="text-white">BVN Basic</td><td>₦{{ number_format($developerPricing['developer_api_bvn_basic_price'], 2) }}</td></tr>
                                <tr><td class="text-white">BVN Premium</td><td>₦{{ number_format($developerPricing['developer_api_bvn_premium_price'], 2) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="endpoints" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">3. Enabled Endpoints</h4>
                    @forelse($endpoints as $groupName => $groupEndpoints)
                        <h5 class="fw-bold mb-3 h6 text-info">{{ $groupName }}</h5>
                        @foreach($groupEndpoints as $endpoint)
                            <div class="mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="text-white fw-bold">{{ $endpoint->name }}</div>
                                    <span class="badge badge-primary">{{ $endpoint->method }}</span>
                                </div>
                                <div class="text-white-50 small mb-2"><code>/{{ ltrim($endpoint->path_pattern, '/') }}</code></div>
                                <p class="text-white-50 small mb-3">{{ $endpoint->docs_summary ?: 'No summary has been added yet.' }}</p>
                                @if($endpoint->docs_request_example)
                                    <div class="text-white-50 small mb-1">Request Example</div>
                                    <pre class="p-3 rounded-3 small text-white font-monospace mb-3" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">{{ $endpoint->docs_request_example }}</pre>
                                @endif
                                @if($endpoint->docs_response_example)
                                    <div class="text-white-50 small mb-1">Response Example</div>
                                    <pre class="p-3 rounded-3 small text-white font-monospace mb-0" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">{{ $endpoint->docs_response_example }}</pre>
                                @endif
                            </div>
                        @endforeach
                    @empty
                        <p class="text-white-50">No developer endpoints are currently enabled.</p>
                    @endforelse

                    <div class="alert mt-4" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                            <div>
                                <i class="fa fa-info-circle text-primary mr-2"></i> Access raw specification formats for testing.
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <a href="{{ route('developer.openapi.v1') }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 mr-2">
                                    <i class="fa-solid fa-file-code mr-1"></i> OpenAPI Spec
                                </a>
                                <a href="{{ route('developer.postman.v1') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-rocket mr-1"></i> Postman Collection
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="practices" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">4. Best Practices</h4>
                    <p class="text-white-50">{{ $docs['best_practices'] }}</p>
                </section>

                <section id="support">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">5. Support</h4>
                    <p class="text-white-50 mb-0">{{ $docs['support'] }}</p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .doc-nav .nav-link { transition: all 0.2s ease; border-left: 2px solid transparent; }
    .doc-nav .nav-link:hover, .doc-nav .nav-link.active { color: var(--clr-primary) !important; border-left-color: var(--clr-primary); background: rgba(59, 130, 246, 0.05); }
    .prose code { color: #fca5a5; background: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 4px; font-size: 0.85em; }
</style>
@endpush

@push('scripts')
<script>
async function copyBlock(id) {
    const el = document.getElementById(id);
    const value = el.innerText || el.textContent || '';
    try {
        await navigator.clipboard.writeText(value);
        Swal.fire({ icon: 'success', title: 'Copied', text: 'Code copied to clipboard.', background: '#141826', color: '#fff', timer: 1200, showConfirmButton: false });
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Copy failed.', background: '#141826', color: '#fff' });
    }
}
</script>
@endpush

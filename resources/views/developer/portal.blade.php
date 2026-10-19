@extends('layouts.nexus')

@section('title', 'Developer Portal')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa-solid fa-code text-primary mr-2"></i> Developer Portal</h3>
            <p class="text-white-50 mb-0">Create API tokens and copy integration snippets for your app.</p>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            @if($user->api_access_status === 'approved')
                <span class="badge badge-success px-3 py-2 rounded-pill"><i class="fa fa-check-circle mr-1"></i> API Access Approved</span>
            @elseif($user->api_access_status === 'pending')
                <span class="badge badge-warning text-dark px-3 py-2 rounded-pill"><i class="fa fa-clock mr-1"></i> Application Pending Review</span>
            @elseif($user->api_access_status === 'rejected')
                <span class="badge badge-danger px-3 py-2 rounded-pill"><i class="fa fa-times-circle mr-1"></i> Application Rejected</span>
            @else
                <span class="badge badge-secondary px-3 py-2 rounded-pill"><i class="fa fa-info-circle mr-1"></i> Application Required</span>
            @endif

            @if($user->hasCustomApiPricing())
                <span class="badge badge-info px-3 py-2 rounded-pill" title="Custom volume rates configured for your account"><i class="fa fa-tag mr-1"></i> Custom Pricing Active</span>
            @endif
        </div>
    </div>
</div>

@if($user->api_access_status !== 'approved')
<div class="row mb-4">
    <div class="col-12">
        @if($user->api_access_status === 'pending')
            <div class="card border-0 rounded-4 p-4" style="background: rgba(234,179,8,0.06); border: 1px solid rgba(234,179,8,0.2) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 15px;">
                    <div>
                        <h5 class="text-warning mb-1 fw-bold"><i class="fa-solid fa-hourglass-half mr-2"></i> API Application Under Review</h5>
                        <p class="text-white-50 mb-1">Your request for API access is being reviewed by the operations and compliance team. Once approved, API key generation will unlock immediately.</p>
                        <div class="text-white-50 small mt-2">
                            <span class="mr-3"><strong class="text-white">Website Domain:</strong> <a href="{{ data_get($user->api_application_details, 'website') }}" target="_blank" class="text-info">{{ data_get($user->api_application_details, 'website', '—') }}</a></span>
                            <span class="mr-3"><strong class="text-white">Purpose:</strong> {{ data_get($user->api_application_details, 'intended_use', '—') }}</span>
                            @if(data_get($user->api_application_details, 'company_name'))
                                <span><strong class="text-white">Company:</strong> {{ data_get($user->api_application_details, 'company_name') }}</span>
                            @endif
                        </div>
                    </div>
                    <button class="btn btn-outline-warning rounded-pill px-4" onclick="openApplyModal()"><i class="fa fa-edit mr-1"></i>Update Details</button>
                </div>
            </div>
        @elseif($user->api_access_status === 'rejected')
            <div class="card border-0 rounded-4 p-4" style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 15px;">
                    <div>
                        <h5 class="text-danger mb-1 fw-bold"><i class="fa-solid fa-circle-xmark mr-2"></i> API Application Rejected</h5>
                        <p class="text-white-50 mb-0">{{ data_get($user->api_application_details, 'review_reason') ?: 'Your API access application was not approved. Please review your website domain or intended use details and submit an updated application.' }}</p>
                    </div>
                    <button class="btn btn-danger rounded-pill px-4" onclick="openApplyModal()"><i class="fa fa-redo mr-1"></i>Re-apply for API Access</button>
                </div>
            </div>
        @elseif(in_array($user->api_access_status, ['suspended', 'revoked']))
            <div class="card border-0 rounded-4 p-4" style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2) !important;">
                <h5 class="text-danger mb-1 fw-bold"><i class="fa-solid fa-triangle-exclamation mr-2"></i> API Access {{ ucfirst($user->api_access_status) }}</h5>
                <p class="text-white-50 mb-0">Your developer API access is currently {{ $user->api_access_status }}. Please reach out to support if you require assistance.</p>
            </div>
        @else
            <div class="card border-0 rounded-4 p-4" style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.25) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 15px;">
                    <div>
                        <h5 class="text-primary mb-1 fw-bold"><i class="fa-solid fa-shield-halved mr-2"></i> Submit Developer Application to Unlock API Access</h5>
                        <p class="text-white-50 mb-0">Before generating an API key, please submit your website domain and intended purpose for security, compliance, and custom volume pricing evaluation.</p>
                    </div>
                    <button class="btn btn-primary rounded-pill px-4 font-weight-bold" onclick="openApplyModal()"><i class="fa fa-paper-plane mr-2"></i>Apply for API Key</button>
                </div>
            </div>
        @endif
    </div>
</div>
@endif

<div class="row">
    <div class="col-lg-5 col-12 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="text-white mb-0 fw-bold">API Tokens</h5>
                <div class="d-flex" style="gap: 8px;">
                    @if($user->api_access_status === 'approved')
                        <button class="btn btn-primary rounded-pill px-3" onclick="instantGenerateToken()" id="instantGenBtn"><i class="fa fa-bolt mr-1"></i>Generate API Key</button>
                        <button class="btn btn-outline-light rounded-pill px-3" onclick="openCreateTokenModal()"><i class="fa fa-plus mr-1"></i>Custom Name</button>
                    @else
                        <button class="btn btn-primary rounded-pill px-3" onclick="openApplyModal()"><i class="fa fa-paper-plane mr-1"></i>Apply for Key</button>
                    @endif
                </div>
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
                                        <a href="{{ route('developer.sandbox', ['token_id' => $t->id]) }}" class="btn btn-sm btn-outline-info rounded-pill px-2.5 mr-1" title="Test in API Sandbox">
                                            <i class="fa-solid fa-vial"></i> Test
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="revokeToken({{ $t->id }})">Revoke</button>
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
                <div class="d-flex" style="gap: 8px;">
                    <a class="btn btn-warning text-dark font-weight-bold rounded-pill px-3" href="{{ route('developer.sandbox') }}">
                        <i class="fa-solid fa-flask-vial mr-1"></i>API Sandbox
                    </a>
                    <a class="btn btn-outline-light rounded-pill px-3" href="{{ route('developer.docs') }}">
                        <i class="fa fa-book mr-1"></i>Tutorials
                    </a>
                    <a class="btn btn-outline-light rounded-pill px-3" href="{{ route('developer.openapi.v1') }}" target="_blank">
                        <i class="fa fa-file-code mr-1"></i>OpenAPI
                    </a>
                    <a class="btn btn-outline-light rounded-pill px-3" href="{{ route('developer.postman.v1') }}" target="_blank">
                        <i class="fa-solid fa-rocket mr-1"></i>Postman
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

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 12px;">
                <div>
                    <h5 class="text-white mb-1 fw-bold"><i class="fa fa-tags text-primary mr-2"></i>API Pricing & Service Rates</h5>
                    <p class="text-white-50 small mb-0">Transparent billing per successful endpoint call. Charges and discounts are debited directly from your account wallet balance.</p>
                </div>
                <div>
                    @if($user->hasCustomApiPricing())
                        <span class="badge badge-warning text-dark font-weight-bold px-3 py-1.5 rounded-pill"><i class="fa fa-gem mr-1"></i>Custom VIP Pricing Applied</span>
                    @else
                        <span class="badge badge-outline-light px-3 py-1.5 rounded-pill">Standard Developer Pricing</span>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-primary px-2.5 py-1 text-uppercase mr-2" style="font-size: 10px;">Identity & Verification</span>
                        </div>
                        <table class="table table-borderless table-sm small text-white-50 mb-0">
                            <tbody>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">NIN Verification</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_nin_price'], 2) }}</td></tr>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">NIN Validation</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_nin_validation_price'], 2) }}</td></tr>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">IPE Clearance</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_ipe_clearance_price'], 2) }}</td></tr>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">BVN Basic</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_bvn_basic_price'], 2) }}</td></tr>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">BVN Premium</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_bvn_premium_price'], 2) }}</td></tr>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">CAC Verification</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_cac_price'], 2) }}</td></tr>
                                <tr><td class="text-white py-2">Driver's License</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_drivers_license_price'], 2) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-warning text-dark font-weight-bold px-2.5 py-1 text-uppercase mr-2" style="font-size: 10px;">VTU Telecom & Bills</span>
                        </div>
                        <table class="table table-borderless table-sm small text-white-50 mb-0">
                            <tbody>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">Airtime Top-up</td><td class="text-right py-2 font-weight-bold text-success">{{ number_format($developerPricing['developer_api_vtu_airtime_discount'], 1) }}% Discount</td></tr>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">Data Bundles</td><td class="text-right py-2 font-weight-bold text-success">{{ number_format($developerPricing['developer_api_vtu_data_discount'], 1) }}% Discount</td></tr>
                                <tr class="border-bottom border-white-10"><td class="text-white py-2">Cable TV</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_vtu_cable_fee'], 2) }} fee</td></tr>
                                <tr><td class="text-white py-2">Electricity Bills</td><td class="text-right py-2 font-weight-bold text-white">₦{{ number_format($developerPricing['developer_api_vtu_electricity_fee'], 2) }} fee</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="text-white mb-1 fw-bold">Available Endpoints</h5>
                    <p class="text-white-50 small mb-0">Only endpoints enabled by the platform are listed here.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless text-white small mb-0">
                    <thead>
                        <tr class="text-white-50 border-bottom border-white-10">
                            <th>Name</th>
                            <th>Method</th>
                            <th>Path</th>
                            <th>Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($endpoints as $endpoint)
                            <tr class="border-bottom border-white-10">
                                <td class="py-3">{{ $endpoint->name }}</td>
                                <td class="py-3"><span class="badge badge-primary">{{ $endpoint->method }}</span></td>
                                <td class="py-3 font-monospace text-white-50">{{ '/' . ltrim($endpoint->path_pattern, '/') }}</td>
                                <td class="py-3 text-white-50">{{ $endpoint->docs_summary ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-white-50">No endpoints are enabled right now.</td></tr>
                        @endforelse
                    </tbody>
                </table>
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
                        <label class="text-white-50 small mb-2">Token Name (Optional)</label>
                        <input type="text" name="name" class="form-control text-white rounded-3" placeholder="e.g. My Web App (Optional)" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div id="createdTokenWrap" style="display:none;">
                        <label class="text-white-50 small mb-2">Your new token (copy now)</label>
                        <div class="d-flex" style="gap: 10px;">
                            <input id="createdToken" class="form-control text-white rounded-3 font-monospace" readonly style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="copyText('createdToken')">Copy</button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-warning small">This token will not be shown again.</span>
                            <a id="modalTestSandboxBtn" href="{{ route('developer.sandbox') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                <i class="fa-solid fa-flask-vial me-1"></i> Test in Sandbox &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button id="createTokenBtn" type="submit" class="btn btn-primary rounded-pill px-4">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="applyApiModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 p-3" style="background: #141826; border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title text-white fw-bold"><i class="fa fa-id-card text-primary mr-2"></i>Apply for Developer API Access</h5>
                    <p class="text-white-50 small mb-0">Submit your website domain and purpose to unlock key generation and custom pricing.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.7;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="applyApiForm" method="POST" action="{{ route('developer.apply') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2"><i class="fa fa-globe mr-1"></i> Website Domain / App URL <span class="text-danger">*</span></label>
                        <input type="text" name="website" class="form-control text-white rounded-3 font-monospace" placeholder="e.g. https://mycompany.com or app.fuwa.ng" required value="{{ data_get($user->api_application_details, 'website', '') }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                        <small class="text-white-50 d-block mt-1">Your website or application where API requests will originate.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2"><i class="fa fa-bullseye mr-1"></i> Purpose of the API Key / Use Case <span class="text-danger">*</span></label>
                        <textarea name="intended_use" class="form-control text-white rounded-3" rows="3" placeholder="Describe your service, why you need NIN/BVN verification or VTU access, and how requests will be used..." required style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ data_get($user->api_application_details, 'intended_use', '') }}</textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2"><i class="fa fa-building mr-1"></i> Company or App Name (Optional)</label>
                        <input type="text" name="company_name" class="form-control text-white rounded-3" placeholder="e.g. Acme Tech Solutions Ltd" value="{{ data_get($user->api_application_details, 'company_name', '') }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2"><i class="fa fa-chart-line mr-1"></i> Estimated Monthly Volume (Optional)</label>
                        <input type="text" name="estimated_monthly_volume" class="form-control text-white rounded-3" placeholder="e.g. 5,000 - 20,000 verifications/month" value="{{ data_get($user->api_application_details, 'estimated_monthly_volume', '') }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-dismiss="modal">Cancel</button>
                    <button id="applyApiBtn" type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold"><i class="fa fa-paper-plane mr-1"></i>Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openApplyModal() {
    $('#applyApiModal').modal('show');
}
function openCreateTokenModal() {
    document.getElementById('createdTokenWrap').style.display = 'none';
    document.getElementById('createdToken').value = '';
    $('#createTokenModal').modal('show');
}

async function instantGenerateToken() {
    const btn = document.getElementById('instantGenBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i>Generating…';
    
    try {
        const res = await $.ajax({
            url: '{{ route("developer.tokens.create") }}',
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        Swal.fire({
            icon: 'success',
            title: 'API Key Generated!',
            html: `
                <p class="text-white-50 small mb-3">Copy your key now. For security, it won't be shown again:</p>
                <div class="d-flex mb-3" style="gap: 10px;">
                    <input id="swalTokenVal" class="form-control text-white rounded-3 font-monospace text-center" value="${res.token}" readonly style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick="copyText('swalTokenVal')">Copy</button>
                </div>
                <div class="text-center mt-2">
                    <a href="{{ route('developer.sandbox') }}?api_key=${encodeURIComponent(res.token)}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        <i class="fa-solid fa-flask-vial me-1"></i> Test Key in Sandbox &rarr;
                    </a>
                </div>
            `,
            confirmButtonText: 'Done',
            confirmButtonColor: '#3b82f6',
            background: '#141826',
            color: '#fff'
        }).then(() => {
            location.reload();
        });
    } catch (xhr) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: xhr.responseJSON?.message || 'Generation failed.',
            background: '#141826',
            color: '#fff'
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
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
        data: $(form).serialize(),
        success(res) {
            document.getElementById('createdToken').value = res.token;
            const sandboxLink = document.getElementById('modalTestSandboxBtn');
            if (sandboxLink) {
                sandboxLink.href = '{{ route("developer.sandbox") }}?api_key=' + encodeURIComponent(res.token);
            }
            document.getElementById('createdTokenWrap').style.display = 'block';
            Swal.fire({ icon: 'success', title: 'Created', text: res.message, background: '#141826', color: '#fff' });
            setTimeout(() => location.reload(), 2000);
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

$('#applyApiForm').on('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('applyApiBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i>Submitting…';

    $.ajax({
        url: '{{ route("developer.apply") }}',
        method: 'POST',
        data: $(form).serialize(),
        headers: {
            'Accept': 'application/json'
        },
        success(res) {
            $('#applyApiModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Application Submitted',
                text: res.message || 'Your application has been received.',
                background: '#141826',
                color: '#fff'
            }).then(() => location.reload());
        },
        error(xhr) {
            const errs = xhr.responseJSON?.errors;
            const msg  = errs ? Object.values(errs).flat().join('\n') : (xhr.responseJSON?.message || 'Submission failed.');
            Swal.fire({ icon: 'error', title: 'Submission Error', text: msg, background: '#141826', color: '#fff' });
        },
        complete() {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
});
</script>
@endpush

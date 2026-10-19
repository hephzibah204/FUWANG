@extends('layouts.nexus')

@section('title', 'Developer API Console - Admin')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex align-items-center">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-dark rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1);">
            <i class="fa fa-arrow-left text-white"></i>
        </a>
        <div>
            <h3 class="text-white mb-0 fw-bold"><i class="fa-solid fa-code text-primary mr-2"></i>Developer API Console</h3>
            <p class="text-white-50 mb-0">Set developer pricing, control available endpoints, manage docs, and monitor API adoption.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 rounded-4" style="background: rgba(34,197,94,0.12); color:#bbf7d0;">
        {{ session('success') }}
    </div>
@endif

<div class="row mb-4">
    <div class="col-md-2 col-6 mb-3">
        <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
            <div class="text-white-50 small">Approved Developers</div>
            <div class="text-white fw-bold h3 mb-0">{{ number_format($metrics['approvedDevelopers']) }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
            <div class="text-white-50 small">Pending Apps</div>
            <div class="text-white fw-bold h3 mb-0">{{ number_format($metrics['pendingApplications']) }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
            <div class="text-white-50 small">Active Tokens</div>
            <div class="text-white fw-bold h3 mb-0">{{ number_format($metrics['activeTokens']) }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
            <div class="text-white-50 small">Requests / {{ $days }}d</div>
            <div class="text-white fw-bold h3 mb-0">{{ number_format($metrics['requests30d']) }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
            <div class="text-white-50 small">API Users / {{ $days }}d</div>
            <div class="text-white fw-bold h3 mb-0">{{ number_format($metrics['uniqueUsers30d']) }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
            <div class="text-white-50 small">Websites / {{ $days }}d</div>
            <div class="text-white fw-bold h3 mb-0">{{ number_format($metrics['websiteCount30d']) }}</div>
        </div>
    </div>
</div>

    <div class="col-lg-5 mb-4">
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white fw-bold mb-1">Developer API Settings</h5>
            <p class="text-white-50 small mb-4">Manage global rules for developer onboarding.</p>
            <form method="POST" action="{{ route('admin.developer_api.settings') }}">
                @csrf
                <div class="form-group mb-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="api_approve_by_default" name="api_approve_by_default" value="1" @checked($approveByDefault)>
                        <label class="custom-control-label text-white" for="api_approve_by_default">Approve Developers by Default</label>
                    </div>
                    <span class="text-white-50 small d-block mt-1">If enabled, new accounts can generate tokens and verify instantly without pending review.</span>
                </div>
                <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-4">Save Settings</button>
            </form>
        </div>

        <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white fw-bold mb-1">Developer Pricing</h5>
            <p class="text-white-50 small mb-4">Separate API pricing from general web pricing for billable verification endpoints.</p>
            <form method="POST" action="{{ route('admin.developer_api.pricing') }}">
                @csrf
                <div class="d-flex align-items-center mb-3">
                    <span class="badge badge-primary px-2.5 py-1 text-uppercase mr-2" style="font-size: 11px;">Identity & Verification</span>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">NIN Verification (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_nin_price" value="{{ $pricing['developer_api_nin_price'] }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">NIN Validation (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_nin_validation_price" value="{{ $pricing['developer_api_nin_validation_price'] ?? 500 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">IPE Clearance (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_ipe_clearance_price" value="{{ $pricing['developer_api_ipe_clearance_price'] ?? 700 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">BVN Basic (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_bvn_basic_price" value="{{ $pricing['developer_api_bvn_basic_price'] }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">BVN Premium (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_bvn_premium_price" value="{{ $pricing['developer_api_bvn_premium_price'] }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">CAC Verification (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_cac_price" value="{{ $pricing['developer_api_cac_price'] ?? 300 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label class="text-white-50 small mb-1">Driver's License (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_drivers_license_price" value="{{ $pricing['developer_api_drivers_license_price'] ?? 200 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label class="text-white-50 small mb-1">TIN (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_tin_price" value="{{ $pricing['developer_api_tin_price'] ?? 200 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label class="text-white-50 small mb-1">Voter's Card (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_voters_card_price" value="{{ $pricing['developer_api_voters_card_price'] ?? 150 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>

                <hr style="border-color: rgba(255,255,255,0.08);" class="my-3">

                <div class="d-flex align-items-center mb-3">
                    <span class="badge badge-warning text-dark font-weight-bold px-2.5 py-1 text-uppercase mr-2" style="font-size: 11px;">VTU Services & Discounts</span>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">Airtime Discount (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="developer_api_vtu_airtime_discount" value="{{ $pricing['developer_api_vtu_airtime_discount'] ?? 2.0 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-white-50 small mb-1">Data Discount (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="developer_api_vtu_data_discount" value="{{ $pricing['developer_api_vtu_data_discount'] ?? 3.0 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small mb-1">Cable TV Fee (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_vtu_cable_fee" value="{{ $pricing['developer_api_vtu_cable_fee'] ?? 0.0 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small mb-1">Electricity Fee (₦)</label>
                        <input type="number" step="0.01" min="0" name="developer_api_vtu_electricity_fee" value="{{ $pricing['developer_api_vtu_electricity_fee'] ?? 0.0 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Developer Pricing</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white fw-bold mb-1">Documentation Management</h5>
            <p class="text-white-50 small mb-4">Control the live content shown in the developer documentation page.</p>
            <form method="POST" action="{{ route('admin.developer_api.docs') }}">
                @csrf
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">Documentation Title</label>
                    <input type="text" name="title" value="{{ $docs['title'] }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">Intro</label>
                    <textarea name="intro" rows="3" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ $docs['intro'] }}</textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">Authentication Notes</label>
                    <textarea name="auth" rows="3" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ $docs['auth'] }}</textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">Best Practices</label>
                    <textarea name="best_practices" rows="3" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ $docs['best_practices'] }}</textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Support Notes</label>
                    <textarea name="support" rows="3" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ $docs['support'] }}</textarea>
                </div>
                <button type="submit" class="btn btn-outline-light rounded-pill px-4">Save Documentation</button>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="text-white fw-bold mb-1">Available Endpoints</h5>
            <p class="text-white-50 small mb-0">Enable or disable endpoints globally and tune the documentation examples developers see.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.developer_api.endpoints') }}">
        @csrf
        @foreach($endpoints as $groupName => $groupEndpoints)
            <div class="mb-4">
                <h6 class="text-info text-uppercase small fw-bold mb-3">{{ $groupName }}</h6>
                @foreach($groupEndpoints as $endpoint)
                    <div class="border rounded-4 p-3 mb-3" style="border-color: rgba(255,255,255,0.08) !important; background: rgba(255,255,255,0.02);">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="text-white fw-bold">{{ $endpoint->name }}</div>
                                <div class="text-white-50 small"><span class="badge badge-primary mr-2">{{ $endpoint->method }}</span><code>{{ $endpoint->path_pattern }}</code></div>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="endpoint_{{ $endpoint->id }}" name="enabled_{{ $endpoint->id }}" value="1" @checked($endpoint->is_enabled)>
                                <label class="custom-control-label text-white" for="endpoint_{{ $endpoint->id }}">Enabled</label>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-white-50 small mb-2">Summary</label>
                            <textarea name="summary_{{ $endpoint->id }}" rows="2" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ $endpoint->docs_summary }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-white-50 small mb-2">Request Example</label>
                                <textarea name="request_example_{{ $endpoint->id }}" rows="5" class="form-control text-white rounded-3 font-monospace" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ $endpoint->docs_request_example }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-white-50 small mb-2">Response Example</label>
                                <textarea name="response_example_{{ $endpoint->id }}" rows="5" class="form-control text-white rounded-3 font-monospace" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">{{ $endpoint->docs_response_example }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Endpoint Controls</button>
    </form>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white fw-bold mb-1">API Applications & Developer Accounts</h5>
            <p class="text-white-50 small mb-4">Review applicant website domains, intended use case, manage access status, and set custom volume pricing per user.</p>
            <div class="table-responsive">
                <table class="table table-borderless text-white small mb-0">
                    <thead>
                        <tr class="text-white-50 border-bottom border-white-10">
                            <th>Developer</th>
                            <th>Website & Use Case</th>
                            <th>Status</th>
                            <th>Custom Pricing</th>
                            <th>Tokens</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($applications as $app)
                        <tr class="border-bottom border-white-10">
                            <td class="py-3" style="min-width: 180px;">
                                <div class="fw-bold text-white">{{ $app->fullname ?: $app->email }}</div>
                                <div class="text-white-50">{{ $app->email }}</div>
                                <div class="text-white-50 small mt-1">Bal: ₦{{ number_format($app->balance->user_balance ?? 0, 2) }}</div>
                            </td>
                            <td class="py-3" style="max-width: 280px;">
                                @php
                                    $site = data_get($app->api_application_details, 'website');
                                    $purpose = data_get($app->api_application_details, 'intended_use');
                                    $company = data_get($app->api_application_details, 'company_name');
                                    $volume = data_get($app->api_application_details, 'estimated_monthly_volume');
                                    $appliedAt = data_get($app->api_application_details, 'applied_at');
                                @endphp
                                @if($site)
                                    <div><i class="fa fa-globe text-primary mr-1"></i> <a href="{{ str_starts_with($site, 'http') ? $site : 'https://' . $site }}" target="_blank" class="text-info font-weight-bold">{{ $site }}</a></div>
                                @else
                                    <span class="text-white-50">—</span>
                                @endif

                                @if($purpose)
                                    <div class="text-white-50 small text-truncate mt-1" style="max-width: 260px;" title="{{ $purpose }}">
                                        <i class="fa fa-bullseye text-warning mr-1"></i> {{ $purpose }}
                                    </div>
                                @endif

                                @if($company || $volume)
                                    <div class="text-white-50 small mt-1" style="font-size: 0.75rem;">
                                        @if($company) <span class="badge badge-dark border border-white-10 mr-1">{{ $company }}</span> @endif
                                        @if($volume) <span class="text-white-50">Vol: {{ $volume }}</span> @endif
                                    </div>
                                @endif
                                @if($appliedAt)
                                    <div class="text-white-50 small mt-1" style="font-size: 0.75rem;">
                                        Applied: {{ \Carbon\Carbon::parse($appliedAt)->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="badge badge-{{ $app->api_access_status === 'approved' ? 'success' : (in_array($app->api_access_status, ['rejected', 'revoked', 'suspended']) ? 'danger' : 'warning') }} px-2 py-1">{{ ucfirst($app->api_access_status ?: 'none') }}</span>
                            </td>
                            <td class="py-3" style="min-width: 170px;">
                                @if($app->hasCustomApiPricing())
                                    <div class="badge badge-info px-2 py-1 mb-1"><i class="fa fa-tag mr-1"></i>Custom Rates</div>
                                    <div class="text-white-50 small">NIN: ₦{{ number_format($app->getDeveloperApiPrice('developer_api_nin_price', 100), 2) }}</div>
                                    <div class="text-white-50 small">BVN: ₦{{ number_format($app->getDeveloperApiPrice('developer_api_bvn_basic_price', 100), 2) }}</div>
                                @else
                                    <div class="text-white-50 small">Default (NIN: ₦{{ number_format($pricing['developer_api_nin_price'], 2) }})</div>
                                @endif
                                <button type="button" class="btn btn-outline-info btn-xs rounded-pill px-2.5 mt-1" data-toggle="modal" data-target="#userPricingModal{{ $app->id }}">
                                    <i class="fa fa-sliders mr-1"></i>Edit Rates
                                </button>
                            </td>
                            <td class="py-3">{{ $app->active_api_tokens_count }}</td>
                            <td class="py-3" style="min-width: 150px;">
                                <form method="POST" action="{{ route('admin.api_applications.update_status', $app->id) }}" class="form-inline d-inline-block">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <select name="status" class="custom-select custom-select-sm text-white rounded-left-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); width: auto;">
                                            <option value="approved" @selected($app->api_access_status === 'approved')>Approve</option>
                                            <option value="rejected" @selected($app->api_access_status === 'rejected')>Reject</option>
                                            <option value="revoked" @selected($app->api_access_status === 'revoked')>Revoke</option>
                                            <option value="suspended" @selected($app->api_access_status === 'suspended')>Suspend</option>
                                            <option value="none" @selected($app->api_access_status === 'none')>Reset</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary btn-sm px-2 rounded-right-3">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>

                        <!-- Custom Pricing Modal for this user -->
                        <div class="modal fade" id="userPricingModal{{ $app->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content rounded-4 border-0 p-3" style="background: #141826; border: 1px solid rgba(255,255,255,0.1) !important;">
                                    <div class="modal-header border-0 pb-0">
                                        <div>
                                            <h5 class="modal-title text-white fw-bold"><i class="fa fa-sliders text-info mr-2"></i>Custom API Pricing</h5>
                                            <p class="text-white-50 small mb-0">{{ $app->email }}</p>
                                        </div>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.7;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.developer_api.user_pricing', $app->id) }}">
                                        @csrf
                                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                            <p class="text-white-50 small mb-3">Set individual rates for this developer. Empty fields inherit the global system defaults.</p>
                                            
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge badge-primary px-2 py-0.5 text-uppercase" style="font-size: 10px;">Identity & Verification</span>
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">NIN Verification (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_nin_price'], 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_nin_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_nin_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_nin_price'] }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">NIN Validation (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_nin_validation_price'] ?? 500, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_nin_validation_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_nin_validation_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_nin_validation_price'] ?? 500 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">IPE Clearance (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_ipe_clearance_price'] ?? 700, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_ipe_clearance_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_ipe_clearance_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_ipe_clearance_price'] ?? 700 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">BVN Basic (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_bvn_basic_price'], 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_bvn_basic_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_bvn_basic_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_bvn_basic_price'] }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">BVN Premium (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_bvn_premium_price'], 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_bvn_premium_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_bvn_premium_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_bvn_premium_price'] }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">CAC Verification (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_cac_price'] ?? 300, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_cac_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_cac_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_cac_price'] ?? 300 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">Driver's License (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_drivers_license_price'] ?? 200, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_drivers_license_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_drivers_license_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_drivers_license_price'] ?? 200 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">TIN Verification (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_tin_price'] ?? 200, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_tin_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_tin_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_tin_price'] ?? 200 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="text-white-50 small mb-1">Voter's Card (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_voters_card_price'] ?? 150, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_voters_card_price" value="{{ data_get($app->api_custom_pricing, 'developer_api_voters_card_price') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_voters_card_price'] ?? 150 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <hr style="border-color: rgba(255,255,255,0.08);" class="my-3">

                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-0.5 text-uppercase" style="font-size: 10px;">VTU Services & Discounts</span>
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">Airtime Discount (%) <span class="text-muted">(Default: {{ $pricing['developer_api_vtu_airtime_discount'] ?? 2.0 }}%)</span></label>
                                                <input type="number" step="0.01" min="0" max="100" name="developer_api_vtu_airtime_discount" value="{{ data_get($app->api_custom_pricing, 'developer_api_vtu_airtime_discount') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_vtu_airtime_discount'] ?? 2.0 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">Data Discount (%) <span class="text-muted">(Default: {{ $pricing['developer_api_vtu_data_discount'] ?? 3.0 }}%)</span></label>
                                                <input type="number" step="0.01" min="0" max="100" name="developer_api_vtu_data_discount" value="{{ data_get($app->api_custom_pricing, 'developer_api_vtu_data_discount') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_vtu_data_discount'] ?? 3.0 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-2.5">
                                                <label class="text-white-50 small mb-1">Cable TV Fee (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_vtu_cable_fee'] ?? 0.0, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_vtu_cable_fee" value="{{ data_get($app->api_custom_pricing, 'developer_api_vtu_cable_fee') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_vtu_cable_fee'] ?? 0.0 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="text-white-50 small mb-1">Electricity Fee (₦) <span class="text-muted">(Default: ₦{{ number_format($pricing['developer_api_vtu_electricity_fee'] ?? 0.0, 2) }})</span></label>
                                                <input type="number" step="0.01" min="0" name="developer_api_vtu_electricity_fee" value="{{ data_get($app->api_custom_pricing, 'developer_api_vtu_electricity_fee') }}" class="form-control text-white rounded-3" placeholder="Default: {{ $pricing['developer_api_vtu_electricity_fee'] ?? 0.0 }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                                            @if($app->hasCustomApiPricing())
                                                <button type="submit" name="reset_to_default" value="1" class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="fa fa-undo mr-1"></i>Reset to Defaults</button>
                                            @else
                                                <span></span>
                                            @endif
                                            <div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold">Save Rates</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-white-50">No API applications yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $applications->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

    <div class="col-lg-6 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white fw-bold mb-1">Website Usage</h5>
            <p class="text-white-50 small mb-4">Distinct websites detected from declared application URLs, Origin, or Referer headers.</p>
            <div class="table-responsive">
                <table class="table table-borderless text-white small mb-0">
                    <thead>
                        <tr class="text-white-50 border-bottom border-white-10">
                            <th>Website / Host</th>
                            <th>Users</th>
                            <th>Requests</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($siteUsage as $site)
                        <tr class="border-bottom border-white-10">
                            <td class="py-3 text-break">{{ $site->site ?: 'Unknown' }}</td>
                            <td class="py-3">{{ $site->total_users }}</td>
                            <td class="py-3">{{ number_format($site->total_requests) }}</td>
                            <td class="py-3 text-white-50">{{ $site->last_seen ? \Illuminate\Support\Carbon::parse($site->last_seen)->diffForHumans() : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-white-50">No API traffic logged yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
    <h5 class="text-white fw-bold mb-1">Recent API Usage</h5>
    <p class="text-white-50 small mb-4">Latest authenticated developer API requests for visibility and support.</p>
    <div class="table-responsive">
        <table class="table table-borderless text-white small mb-0">
            <thead>
                <tr class="text-white-50 border-bottom border-white-10">
                    <th>Time</th>
                    <th>User</th>
                    <th>Endpoint</th>
                    <th>Token</th>
                    <th>Status</th>
                    <th>Site</th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentUsage as $log)
                <tr class="border-bottom border-white-10">
                    <td class="py-3 text-white-50">{{ optional($log->requested_at)->diffForHumans() ?: '—' }}</td>
                    <td class="py-3">
                        <div>{{ optional($log->user)->email ?: 'Unknown' }}</div>
                        <div class="text-white-50">{{ optional($log->user)->fullname ?: '' }}</div>
                    </td>
                    <td class="py-3">
                        <div class="fw-bold">{{ $log->endpoint_slug ?: 'unmatched' }}</div>
                        <div class="text-white-50">{{ $log->method }} {{ $log->path }}</div>
                    </td>
                    <td class="py-3 text-white-50">{{ optional($log->token)->name ?: '—' }} @if(optional($log->token)->last_four) ••••{{ $log->token->last_four }} @endif</td>
                    <td class="py-3"><span class="badge badge-{{ (int) $log->status_code < 400 ? 'success' : 'danger' }}">{{ $log->status_code ?: '—' }}</span></td>
                    <td class="py-3 text-break text-white-50">{{ $log->origin_host ?: $log->referer_host ?: $log->declared_website ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4 text-white-50">No recent usage yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


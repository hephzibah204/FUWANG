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
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h5 class="text-white fw-bold mb-1"><i class="fa fa-id-card text-primary mr-2"></i>API Applications & Developer Profiles</h5>
                    <p class="text-white-50 small mb-0">Inspect full profiles of everyone using or applied for API keys, review integration use cases, manage access permissions, and configure custom pricing.</p>
                </div>
            </div>

            <!-- Filter tabs & Search form -->
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4 pb-3 border-bottom border-white-10" style="gap: 15px;">
                <div class="d-flex flex-wrap gap-2" style="gap: 8px;">
                    <a href="{{ route('admin.developer_api.index', array_merge(request()->query(), ['developer_filter' => 'all'])) }}" class="btn btn-sm rounded-pill px-3 {{ ($activeFilter ?? 'all') === 'all' ? 'btn-primary' : 'btn-dark text-white-50' }}" style="{{ ($activeFilter ?? 'all') !== 'all' ? 'background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);' : '' }}">
                        All Developers <span class="badge badge-light text-dark ml-1">{{ $developerCounts['all'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.developer_api.index', array_merge(request()->query(), ['developer_filter' => 'pending'])) }}" class="btn btn-sm rounded-pill px-3 {{ ($activeFilter ?? '') === 'pending' ? 'btn-warning text-dark font-weight-bold' : 'btn-dark text-white-50' }}" style="{{ ($activeFilter ?? '') !== 'pending' ? 'background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);' : '' }}">
                        Pending <span class="badge badge-warning text-dark ml-1">{{ $developerCounts['pending'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.developer_api.index', array_merge(request()->query(), ['developer_filter' => 'approved'])) }}" class="btn btn-sm rounded-pill px-3 {{ ($activeFilter ?? '') === 'approved' ? 'btn-success' : 'btn-dark text-white-50' }}" style="{{ ($activeFilter ?? '') !== 'approved' ? 'background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);' : '' }}">
                        Approved <span class="badge badge-success ml-1">{{ $developerCounts['approved'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.developer_api.index', array_merge(request()->query(), ['developer_filter' => 'with_tokens'])) }}" class="btn btn-sm rounded-pill px-3 {{ ($activeFilter ?? '') === 'with_tokens' ? 'btn-info' : 'btn-dark text-white-50' }}" style="{{ ($activeFilter ?? '') !== 'with_tokens' ? 'background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);' : '' }}">
                        With Tokens <span class="badge badge-info ml-1">{{ $developerCounts['with_tokens'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.developer_api.index', array_merge(request()->query(), ['developer_filter' => 'custom_pricing'])) }}" class="btn btn-sm rounded-pill px-3 {{ ($activeFilter ?? '') === 'custom_pricing' ? 'btn-secondary text-white font-weight-bold' : 'btn-dark text-white-50' }}" style="{{ ($activeFilter ?? '') !== 'custom_pricing' ? 'background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);' : '' }}">
                        Custom Rates <span class="badge badge-secondary ml-1">{{ $developerCounts['custom_pricing'] ?? 0 }}</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.developer_api.index') }}" class="d-flex align-items-center" style="gap: 8px;">
                    @if(request('developer_filter'))
                        <input type="hidden" name="developer_filter" value="{{ request('developer_filter') }}">
                    @endif
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <input type="text" name="developer_search" value="{{ $search ?? '' }}" class="form-control text-white rounded-left-pill border-0 px-3" placeholder="Search developer, website, email..." style="background: rgba(255,255,255,0.06);">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary rounded-right-pill px-3"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    @if(!empty($search))
                        <a href="{{ route('admin.developer_api.index', ['developer_filter' => request('developer_filter', 'all')]) }}" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Clear Search"><i class="fa fa-times"></i></a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless text-white small mb-0">
                    <thead>
                        <tr class="text-white-50 border-bottom border-white-10">
                            <th>Developer Profile</th>
                            <th>Website & Use Case</th>
                            <th>Status</th>
                            <th>Pricing Tier</th>
                            <th>Tokens & Calls</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($applications as $app)
                        @php
                            $site = data_get($app->api_application_details, 'website');
                            $purpose = data_get($app->api_application_details, 'intended_use');
                            $company = data_get($app->api_application_details, 'company_name');
                            $volume = data_get($app->api_application_details, 'estimated_monthly_volume');
                            $appliedAt = data_get($app->api_application_details, 'applied_at');
                        @endphp
                        <tr class="border-bottom border-white-10">
                            <td class="py-3" style="min-width: 220px;">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#developerProfileModal{{ $app->id }}" class="text-decoration-none mr-2.5">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 38px; height: 38px; background: linear-gradient(135deg, rgba(99,102,241,0.5), rgba(79,70,229,0.3)); border: 1px solid rgba(255,255,255,0.15); font-size: 0.95rem;">
                                            {{ strtoupper(substr($app->fullname ?: ($app->email ?: 'U'), 0, 1)) }}
                                        </div>
                                    </a>
                                    <div>
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#developerProfileModal{{ $app->id }}" class="text-white fw-bold text-decoration-none d-block">
                                            {{ $app->fullname ?: ($app->username ?: 'Developer #' . $app->id) }}
                                        </a>
                                        <div class="text-white-50" style="font-size: 0.78rem;">{{ $app->email }}</div>
                                        @if($app->number)
                                            <div class="text-white-50" style="font-size: 0.72rem;"><i class="fa fa-phone mr-1"></i>{{ $app->number }}</div>
                                        @endif
                                        <div class="text-white-50 small mt-0.5" style="font-size: 0.72rem;">Bal: <span class="text-success font-weight-bold">₦{{ number_format($app->balance->user_balance ?? 0, 2) }}</span></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3" style="max-width: 280px;">
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
                            <td class="py-3">
                                <div><span class="badge badge-primary px-2 py-0.5">{{ $app->active_api_tokens_count }} active</span></div>
                                <div class="text-white-50 small mt-1">{{ number_format($app->total_api_requests_count ?? 0) }} calls</div>
                            </td>
                            <td class="py-3 text-right" style="min-width: 220px;">
                                <button type="button" class="btn btn-outline-light btn-xs rounded-pill px-2.5 py-1 mr-1.5" data-toggle="modal" data-target="#developerProfileModal{{ $app->id }}" title="View Complete Developer Profile">
                                    <i class="fa fa-user-circle mr-1"></i>View Profile
                                </button>
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

                        <!-- Developer Profile Modal -->
                        <div class="modal fade" id="developerProfileModal{{ $app->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content rounded-4 border-0" style="background: #141826; border: 1px solid rgba(255,255,255,0.12) !important; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mr-3 shadow" style="width: 52px; height: 52px; background: linear-gradient(135deg, rgba(99,102,241,0.6), rgba(79,70,229,0.4)); border: 2px solid rgba(255,255,255,0.2); font-size: 1.4rem;">
                                                {{ strtoupper(substr($app->fullname ?: ($app->email ?: 'U'), 0, 1)) }}
                                            </div>
                                            <div>
                                                <h5 class="modal-title text-white fw-bold mb-0">{{ $app->fullname ?: 'Developer #' . $app->id }}</h5>
                                                <div class="text-white-50 small">
                                                    {{ $app->email }}
                                                    @if($app->username) <span class="badge badge-dark border border-white-10 ml-1">@ {{ $app->username }}</span> @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.7;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                                        <!-- Top Summary Metrics -->
                                        <div class="row mb-4">
                                            <div class="col-sm-3 col-6 mb-2">
                                                <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                                    <div class="text-white-50 small text-uppercase" style="font-size: 0.7rem;">Wallet Balance</div>
                                                    <div class="h5 mb-0 font-weight-bold text-success mt-1">₦{{ number_format($app->balance->user_balance ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 col-6 mb-2">
                                                <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                                    <div class="text-white-50 small text-uppercase" style="font-size: 0.7rem;">API Access</div>
                                                    <div class="mt-1">
                                                        <span class="badge badge-{{ $app->api_access_status === 'approved' ? 'success' : (in_array($app->api_access_status, ['rejected', 'revoked', 'suspended']) ? 'danger' : 'warning') }} px-2 py-1">
                                                            {{ ucfirst($app->api_access_status ?: 'none') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 col-6 mb-2">
                                                <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                                    <div class="text-white-50 small text-uppercase" style="font-size: 0.7rem;">API Tokens</div>
                                                    <div class="h5 mb-0 font-weight-bold text-info mt-1">{{ $app->active_api_tokens_count }} <span class="small text-white-50" style="font-size: 0.75rem;">/ {{ $app->total_api_tokens_count ?? $app->apiTokens->count() }}</span></div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 col-6 mb-2">
                                                <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                                    <div class="text-white-50 small text-uppercase" style="font-size: 0.7rem;">Recorded Calls</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white mt-1">{{ number_format($app->total_api_requests_count ?? 0) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Account & Contact Details -->
                                        <div class="card border-0 rounded-3 mb-3 p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06) !important;">
                                            <h6 class="text-white font-weight-bold mb-3"><i class="fa fa-user-circle text-primary mr-2"></i>Account & Contact Details</h6>
                                            <div class="row small">
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Email Address:</span>
                                                    <span class="text-white font-weight-bold ml-1"><a href="mailto:{{ $app->email }}" class="text-info">{{ $app->email }}</a></span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Phone Number:</span>
                                                    <span class="text-white font-weight-bold ml-1">{{ $app->number ?: 'Not provided' }}</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Account Status:</span>
                                                    <span class="badge badge-{{ ($app->user_status ?? 'active') === 'active' ? 'success' : 'danger' }} ml-1">{{ ucfirst($app->user_status ?? 'active') }}</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">KYC Tier:</span>
                                                    <span class="badge badge-info ml-1">Tier {{ $app->kyc_tier ?? 0 }}</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Member Since:</span>
                                                    <span class="text-white ml-1">{{ $app->created_at?->format('M d, Y h:i A') }} ({{ $app->created_at?->diffForHumans() }})</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Last Active:</span>
                                                    <span class="text-white ml-1">{{ $app->updated_at?->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-2 pt-2 border-top border-white-10 d-flex gap-2" style="gap: 10px;">
                                                <a href="{{ route('admin.users.show', $app->id) }}" target="_blank" class="btn btn-outline-primary btn-xs rounded-pill px-3">
                                                    <i class="fa fa-external-link mr-1"></i>Open Full Admin User Profile
                                                </a>
                                                <a href="{{ route('admin.users.history', urlencode($app->email)) }}" target="_blank" class="btn btn-outline-light btn-xs rounded-pill px-3">
                                                    <i class="fa fa-history mr-1"></i>View User Wallet History
                                                </a>
                                            </div>
                                        </div>

                                        <!-- API Application Information -->
                                        <div class="card border-0 rounded-3 mb-3 p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06) !important;">
                                            <h6 class="text-white font-weight-bold mb-3"><i class="fa fa-file-text-o text-warning mr-2"></i>API Application & Business Info</h6>
                                            <div class="row small mb-3">
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Registered Website / App:</span>
                                                    @if($site)
                                                        <div class="mt-1">
                                                            <a href="{{ str_starts_with($site, 'http') ? $site : 'https://' . $site }}" target="_blank" class="text-info font-weight-bold">
                                                                <i class="fa fa-globe mr-1"></i>{{ $site }} <i class="fa fa-external-link small ml-1"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        <span class="text-white-50 ml-1">Not provided</span>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Company / Organization:</span>
                                                    <div class="text-white font-weight-bold mt-1">{{ $company ?: 'Individual / Not specified' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Estimated Monthly Calls:</span>
                                                    <div class="text-white font-weight-bold mt-1">{{ $volume ?: 'Standard' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <span class="text-white-50">Application Submitted:</span>
                                                    <div class="text-white mt-1">{{ $appliedAt ? \Carbon\Carbon::parse($appliedAt)->format('M d, Y h:i A') . ' (' . \Carbon\Carbon::parse($appliedAt)->diffForHumans() . ')' : 'Direct Access' }}</div>
                                                </div>
                                            </div>

                                            @if($purpose)
                                                <div class="p-3 rounded-3 mb-3" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
                                                    <div class="text-warning small font-weight-bold mb-1"><i class="fa fa-bullseye mr-1"></i>Intended Integration Purpose & Use Case:</div>
                                                    <div class="text-white small" style="white-space: pre-line; line-height: 1.5;">{{ $purpose }}</div>
                                                </div>
                                            @endif

                                            @if(!empty(data_get($app->api_application_details, 'notes')))
                                                <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                                                    <div class="text-white-50 small font-weight-bold mb-1">Additional Developer Notes:</div>
                                                    <div class="text-white small">{{ data_get($app->api_application_details, 'notes') }}</div>
                                                </div>
                                            @endif

                                            <!-- Status Update Form inside Modal -->
                                            <div class="pt-2 border-top border-white-10">
                                                <form method="POST" action="{{ route('admin.api_applications.update_status', $app->id) }}" class="d-flex align-items-center gap-2" style="gap: 10px;">
                                                    @csrf
                                                    <span class="text-white-50 small">Change Status:</span>
                                                    <select name="status" class="custom-select custom-select-sm text-white" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.15); width: auto;">
                                                        <option value="approved" @selected($app->api_access_status === 'approved')>Approve Access</option>
                                                        <option value="rejected" @selected($app->api_access_status === 'rejected')>Reject Application</option>
                                                        <option value="revoked" @selected($app->api_access_status === 'revoked')>Revoke Access</option>
                                                        <option value="suspended" @selected($app->api_access_status === 'suspended')>Suspend Access</option>
                                                        <option value="none" @selected($app->api_access_status === 'none')>Reset to Default</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Update Status</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- API Tokens Section -->
                                        <div class="card border-0 rounded-3 mb-3 p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06) !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="text-white font-weight-bold mb-0"><i class="fa fa-key text-info mr-2"></i>Issued API Keys & Tokens ({{ $app->apiTokens->count() }})</h6>
                                            </div>
                                            @if($app->apiTokens->isNotEmpty())
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-borderless text-white small mb-0">
                                                        <thead>
                                                            <tr class="text-white-50 border-bottom border-white-10">
                                                                <th>Token Name</th>
                                                                <th>Key Identifier</th>
                                                                <th>Rate Limit</th>
                                                                <th>Created</th>
                                                                <th>Last Used</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($app->apiTokens as $token)
                                                                <tr class="border-bottom border-white-5">
                                                                    <td class="py-2 text-white font-weight-bold">{{ $token->name }}</td>
                                                                    <td class="py-2"><code class="text-info">nx_••••••••{{ $token->last_four }}</code></td>
                                                                    <td class="py-2">{{ $token->rate_limit_per_minute }} req/min</td>
                                                                    <td class="py-2 text-white-50">{{ $token->created_at?->format('M d, Y') }}</td>
                                                                    <td class="py-2 text-white-50">{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}</td>
                                                                    <td class="py-2">
                                                                        @if($token->revoked_at)
                                                                            <span class="badge badge-danger">Revoked</span>
                                                                        @else
                                                                            <span class="badge badge-success">Active</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-white-50 small py-2">No API tokens issued yet.</div>
                                            @endif
                                        </div>

                                        <!-- Current Custom Pricing Rates -->
                                        <div class="card border-0 rounded-3 p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06) !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h6 class="text-white font-weight-bold mb-0"><i class="fa fa-tags text-success mr-2"></i>Active Developer API Rates</h6>
                                                    <small class="text-white-50">{{ $app->hasCustomApiPricing() ? 'VIP Custom Price Overrides Configured' : 'Inheriting Global Default System Rates' }}</small>
                                                </div>
                                                <button type="button" class="btn btn-outline-info btn-xs rounded-pill px-3" data-toggle="modal" data-target="#userPricingModal{{ $app->id }}" data-dismiss="modal">
                                                    <i class="fa fa-sliders mr-1"></i>Configure Custom Rates
                                                </button>
                                            </div>
                                            <div class="row small">
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">NIN Verify:</span>
                                                    <div class="text-white font-weight-bold">₦{{ number_format($app->getDeveloperApiPrice('developer_api_nin_price', 100), 2) }}</div>
                                                </div>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">NIN Validation:</span>
                                                    <div class="text-white font-weight-bold">₦{{ number_format($app->getDeveloperApiPrice('developer_api_nin_validation_price', 500), 2) }}</div>
                                                </div>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">IPE Clearance:</span>
                                                    <div class="text-white font-weight-bold">₦{{ number_format($app->getDeveloperApiPrice('developer_api_ipe_clearance_price', 700), 2) }}</div>
                                                </div>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">BVN Basic:</span>
                                                    <div class="text-white font-weight-bold">₦{{ number_format($app->getDeveloperApiPrice('developer_api_bvn_basic_price', 100), 2) }}</div>
                                                </div>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">BVN Premium:</span>
                                                    <div class="text-white font-weight-bold">₦{{ number_format($app->getDeveloperApiPrice('developer_api_bvn_premium_price', 500), 2) }}</div>
                                                </div>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">CAC Registry:</span>
                                                    <div class="text-white font-weight-bold">₦{{ number_format($app->getDeveloperApiPrice('developer_api_cac_price', 300), 2) }}</div>
                                                </div>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">Airtime Discount:</span>
                                                    <div class="text-success font-weight-bold">{{ $app->getDeveloperApiPrice('developer_api_vtu_airtime_discount', 2.0) }}% off</div>
                                                </div>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <span class="text-white-50">Data Discount:</span>
                                                    <div class="text-success font-weight-bold">{{ $app->getDeveloperApiPrice('developer_api_vtu_data_discount', 3.0) }}% off</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Close Profile</button>
                                    </div>
                                </div>
                            </div>
                        </div>

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


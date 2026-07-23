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

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white fw-bold mb-1">Developer Pricing</h5>
            <p class="text-white-50 small mb-4">Separate API pricing from general web pricing for billable verification endpoints.</p>
            <form method="POST" action="{{ route('admin.developer_api.pricing') }}">
                @csrf
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">NIN Verification Price (₦)</label>
                    <input type="number" step="0.01" min="0" name="developer_api_nin_price" value="{{ $pricing['developer_api_nin_price'] }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">BVN Basic Price (₦)</label>
                    <input type="number" step="0.01" min="0" name="developer_api_bvn_basic_price" value="{{ $pricing['developer_api_bvn_basic_price'] }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">BVN Premium Price (₦)</label>
                    <input type="number" step="0.01" min="0" name="developer_api_bvn_premium_price" value="{{ $pricing['developer_api_bvn_premium_price'] }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-2">CAC Verification Price (₦)</label>
                    <input type="number" step="0.01" min="0" name="developer_api_cac_price" value="{{ $pricing['developer_api_cac_price'] ?? 300 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="form-group mb-4">
                    <label class="text-white-50 small mb-2">Driver's License Price (₦)</label>
                    <input type="number" step="0.01" min="0" name="developer_api_drivers_license_price" value="{{ $pricing['developer_api_drivers_license_price'] ?? 200 }}" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
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
    <div class="col-lg-6 mb-4">
        <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white fw-bold mb-1">API Applications</h5>
            <p class="text-white-50 small mb-4">Approve, reject, and monitor developer accounts and declared websites.</p>
            <div class="table-responsive">
                <table class="table table-borderless text-white small mb-0">
                    <thead>
                        <tr class="text-white-50 border-bottom border-white-10">
                            <th>User</th>
                            <th>Website</th>
                            <th>Status</th>
                            <th>Tokens</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($applications as $app)
                        <tr class="border-bottom border-white-10">
                            <td class="py-3">
                                <div class="fw-bold">{{ $app->fullname ?: $app->email }}</div>
                                <div class="text-white-50">{{ $app->email }}</div>
                            </td>
                            <td class="py-3 text-white-50">{{ data_get($app->api_application_details, 'website', '—') }}</td>
                            <td class="py-3">
                                <span class="badge badge-{{ $app->api_access_status === 'approved' ? 'success' : ($app->api_access_status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($app->api_access_status) }}</span>
                            </td>
                            <td class="py-3">{{ $app->active_api_tokens_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-white-50">No API applications yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $applications->links('pagination::bootstrap-4') }}
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


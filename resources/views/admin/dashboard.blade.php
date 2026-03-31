@extends('layouts.nexus')

@section('title', 'Admin Dashboard - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-shield-halved text-primary mr-2"></i> System Control</h3>
            <p class="text-white-50 mb-0">Powering {{ config('app.name', 'Fuwa.NG') }}</p>
        </div>
        <div class="dropdown mt-3 mt-md-0 d-flex align-items-center">
            <button id="theme-toggle" class="btn btn-dark border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 45px; height: 45px; background: rgba(255,255,255,0.05) !important;" title="Toggle High Contrast">
                <i class="fa fa-circle-half-stroke text-white"></i>
            </button>
            <button class="btn btn-dark border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" type="button" data-toggle="dropdown" style="width: 45px; height: 45px; background: rgba(255,255,255,0.05) !important;">
                <i class="fa fa-th-large text-white"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right border-0 shadow-lg p-2" style="background: rgba(25, 30, 45, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 15px;">
                <a class="dropdown-item text-white rounded py-2 d-flex align-items-center" href="{{ route('admin.services.index') }}" style="transition: 0.2s;"><i class="fa fa-layer-group text-info mr-3"></i> Services</a>
                <a class="dropdown-item text-white rounded py-2 d-flex align-items-center" href="{{ route('admin.sandbox.index') }}" style="transition: 0.2s;"><i class="fa fa-flask text-success mr-3"></i> Sandbox</a>
                <a class="dropdown-item text-white rounded py-2 d-flex align-items-center" href="{{ route('admin.settings.index', ['tab' => 'tab-features']) }}" style="transition: 0.2s;"><i class="fa fa-toggle-on text-warning mr-3"></i> Service Toggles</a>
                <a class="dropdown-item text-white rounded py-2 d-flex align-items-center" href="{{ route('admin.settings.index') }}" style="transition: 0.2s;"><i class="fa fa-gear text-primary mr-3"></i> Settings</a>
                <div class="dropdown-divider border-secondary" style="opacity: 0.3;"></div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger rounded py-2 d-flex align-items-center" style="transition: 0.2s;"><i class="fa fa-power-off text-danger mr-3"></i> Shutdown</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4 animate__animated animate__zoomIn" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">Total Users</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                    <i class="fa fa-users"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-white mb-0">{{ $totalUsers ?? 0 }}</h3>
            <small class="text-success mt-2 d-block"><i class="fa fa-arrow-up mr-1"></i> {{ $newUsersToday ?? 0 }} today</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4 animate__animated animate__zoomIn" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important; animation-delay: 0.1s;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">Successful Tx (24h)</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                    <i class="fa fa-receipt"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-success mb-0">{{ $tx24h ?? 0 }}</h3>
            <small class="text-white-50 mt-2 d-block">{{ $failedTx24h ?? 0 }} failed</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4 animate__animated animate__zoomIn" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important; animation-delay: 0.2s;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">Revenue (Today)</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(234, 179, 8, 0.1); color: #eab308;">
                    <i class="fa fa-money-bill-trend-up"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-warning mb-0">₦{{ number_format($revenueToday ?? 0, 2) }}</h3>
            <small class="text-white-50 mt-2 d-block">Overall: ₦{{ number_format($totalRevenue ?? 0, 2) }}</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4 animate__animated animate__zoomIn" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important; animation-delay: 0.3s;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">Verifications (24h)</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                    <i class="fa fa-shield-check"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-white mb-0">{{ $verifications24h ?? 0 }}</h3>
            <small class="text-info mt-2 d-block">{{ $pendingVerifications ?? 0 }} pending review</small>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mb-5">
    <div class="col-lg-8">
        <div class="card glass-card border-0 rounded-lg p-4 h-100" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="text-white font-weight-bold mb-0">Platform Multi-Metrics</h5>
                <div class="d-flex gap-2" style="gap: 8px;">
                    <span class="badge badge-pill py-1 px-3 d-flex align-items-center" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); font-size: 0.7rem;"><i class="fa fa-circle mr-1" style="font-size: 6px;"></i> Revenue</span>
                    <span class="badge badge-pill py-1 px-3 d-flex align-items-center" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); font-size: 0.7rem;"><i class="fa fa-circle mr-1" style="font-size: 6px;"></i> Verifications</span>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="platformMetricsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card glass-card border-0 rounded-lg p-4 h-100" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <h5 class="text-white font-weight-bold mb-4">User Acquisition</h5>
            <div style="height: 300px; position: relative;">
                <canvas id="userAcquisitionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">Online Users</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.12); color: #22c55e;">
                    <i class="fa fa-circle-dot"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-white mb-0">{{ $onlineUsers ?? 0 }}</h3>
            <div class="text-white-50 small mt-2">of {{ $totalUsers ?? 0 }} total</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">Open Tickets</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.12); color: #60a5fa;">
                    <i class="fa fa-headset"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline justify-content-between">
                <h3 class="font-weight-bold text-white mb-0">{{ $openTickets ?? 0 }}</h3>
                <a href="{{ route('admin.tickets') }}" class="small text-primary text-decoration-none">View</a>
            </div>
            <div class="text-white-50 small mt-2">Answered: {{ $answeredTickets ?? 0 }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">Pending Verifications</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                    <i class="fa fa-hourglass-half"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-white mb-0">{{ $pendingVerifications ?? 0 }}</h3>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card glass-card border-0 rounded-lg h-100 p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-white-50">API Providers</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                    <i class="fa fa-server"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline justify-content-between">
                <h3 class="font-weight-bold text-white mb-0">{{ $providersDown ?? 0 }}</h3>
                <span class="text-white-50 small">down</span>
            </div>
            <div class="text-white-50 small mt-2">Total providers: {{ $providersTotal ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="row mb-4 mt-2">
    <div class="col-md-6 col-12">
        <a href="{{ route('admin.settings.index') }}" class="text-decoration-none d-block">
            <div class="p-4 rounded-lg d-flex align-items-center" style="background: rgba(99,102,241,0.07); border: 1px solid rgba(99,102,241,0.2);">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; min-width:45px; background: rgba(99,102,241,0.15); color: #818cf8;"><i class="fa fa-sliders"></i></div>
                <div>
                    <small class="text-white-50">Global Settings</small>
                    <h5 class="text-white font-weight-bold mb-0">Pricing, Theme, API Keys <i class="fa fa-arrow-right small"></i></h5>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-12">
        <a href="{{ route('admin.services.index') }}" class="text-decoration-none d-block">
            <div class="p-4 rounded-lg d-flex align-items-center" style="background: rgba(34,197,94,0.07); border: 1px solid rgba(34,197,94,0.2);">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; min-width:45px; background: rgba(34,197,94,0.15); color: #22c55e;"><i class="fa fa-layer-group"></i></div>
                <div>
                    <small class="text-white-50">Service Control</small>
                    <h5 class="text-white font-weight-bold mb-0">Uptime & Integrations <i class="fa fa-arrow-right small"></i></h5>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mb-5">
    <div class="col-lg-4 col-12 mb-4 mb-lg-0">
        <div class="card glass-card border-0 rounded-lg p-4 h-100" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-weight-bold mb-0">Traffic (Verifications)</h5>
                <span class="text-white-50 small">Last 7 days</span>
            </div>
            @php
                $dv = $dailyVerifications ?? collect();
                $max = max(1, (int) ($dv->max('total') ?? 1));
            @endphp
            <div class="d-flex align-items-end" style="gap: 10px; height: 120px;">
                @foreach($dv as $item)
                    @php $h = (int) round((($item['total'] ?? 0) / $max) * 100); @endphp
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="height: 100px; display:flex; align-items:flex-end;">
                            <div style="width: 100%; height: {{ max(8, $h) }}px; border-radius: 10px; background: rgba(59,130,246,0.25); border: 1px solid rgba(59,130,246,0.35);"></div>
                        </div>
                        <div class="text-center text-white-50" style="font-size: 11px; margin-top: 6px;">
                            {{ \Illuminate\Support\Str::of($item['day'])->substr(5) }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-white-50 small">
                Total verifications: <span class="text-white font-weight-bold">{{ $verificationsTotal ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12 mb-4 mb-lg-0">
        <div class="card glass-card border-0 rounded-lg p-4 h-100" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-weight-bold mb-0">Traffic (Signups)</h5>
                <span class="text-white-50 small">Last 7 days</span>
            </div>
            @php
                $ds = $dailySignups ?? collect();
                $smax = max(1, (int) ($ds->max('total') ?? 1));
            @endphp
            <div class="d-flex align-items-end" style="gap: 10px; height: 120px;">
                @foreach($ds as $item)
                    @php $h = (int) round((($item['total'] ?? 0) / $smax) * 100); @endphp
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="height: 100px; display:flex; align-items:flex-end;">
                            <div style="width: 100%; height: {{ max(8, $h) }}px; border-radius: 10px; background: rgba(6,182,212,0.22); border: 1px solid rgba(6,182,212,0.3);"></div>
                        </div>
                        <div class="text-center text-white-50" style="font-size: 11px; margin-top: 6px;">
                            {{ \Illuminate\Support\Str::of($item['day'])->substr(5) }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-white-50 small">
                Today revenue: <span class="text-white font-weight-bold">₦{{ number_format($revenueToday ?? 0, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="card glass-card border-0 rounded-lg p-4 h-100" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-weight-bold mb-0">Operations</h5>
                <span class="text-white-50 small">Live status</span>
            </div>
            <div class="d-flex justify-content-between mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span class="text-white-50">Transactions (24h)</span>
                <span class="text-white font-weight-bold">{{ $tx24h ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span class="text-white-50">Failed (24h)</span>
                <span class="text-white font-weight-bold">{{ $failedTx24h ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span class="text-white-50">Invoices (Open)</span>
                <span class="text-white font-weight-bold">{{ $openInvoices ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span class="text-white-50">Notary (Pending)</span>
                <span class="text-white font-weight-bold">{{ $pendingNotary ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-white-50">Shipments (Pending)</span>
                <span class="text-white font-weight-bold">{{ $pendingShipments ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-lg-7 col-12 mb-4 mb-lg-0">
        <div class="card glass-card border-0 rounded-lg p-4 h-100" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-weight-bold mb-0">Recent Activity</h5>
                <span class="text-white-50 small">Latest records</span>
            </div>

            <ul class="nav nav-pills mb-3" style="gap: 10px;">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="pill" href="#tab-tx" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="pill" href="#tab-vr" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">Verifications</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="pill" href="#tab-tk" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">Tickets</a>
                </li>
                @if(Auth::guard('admin')->user()?->is_super_admin)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="pill" href="#tab-au" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">Admin</a>
                </li>
                @endif
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-tx">
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th class="text-right">Delta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions ?? [] as $tx)
                                    @php $delta = (float) ($tx->balance_before - $tx->balance_after); @endphp
                                    <tr>
                                        <td><code class="text-primary">{{ $tx->transaction_id }}</code></td>
                                        <td class="text-white-50">{{ $tx->user_email }}</td>
                                        <td class="text-white">{{ \Illuminate\Support\Str::limit($tx->order_type, 32) }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $tx->status === 'success' ? 'badge-success' : ($tx->status === 'failed' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $tx->status ?? 'n/a' }}
                                            </span>
                                        </td>
                                        <td class="text-right text-white">₦{{ number_format($delta, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-white-50">No transactions yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-vr">
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>User</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th class="text-right">When</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVerifications ?? [] as $vr)
                                    <tr>
                                        <td><code class="text-primary">{{ $vr->reference_id }}</code></td>
                                        <td class="text-white-50">{{ $vr->user?->email ?? '—' }}</td>
                                        <td class="text-white">{{ strtoupper($vr->service_type) }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $vr->status === 'success' ? 'badge-success' : ($vr->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                                {{ $vr->status }}
                                            </span>
                                        </td>
                                        <td class="text-right text-white-50">{{ $vr->created_at->format('M d, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-white-50">No verification activity yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-tk">
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTickets ?? [] as $tk)
                                    <tr>
                                        <td><code class="text-primary">#{{ $tk->id }}</code></td>
                                        <td class="text-white-50">{{ $tk->user?->email ?? $tk->user_email ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $tk->status === 'open' ? 'badge-danger' : ($tk->status === 'answered' ? 'badge-warning' : 'badge-secondary') }}">
                                                {{ $tk->status }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.tickets.show', $tk->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-white-50">No tickets yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(Auth::guard('admin')->user()?->is_super_admin)
                <div class="tab-pane fade" id="tab-au">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small">Latest admin actions</span>
                        <a href="{{ route('admin.audit_logs.index') }}" class="small text-primary text-decoration-none">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>IP</th>
                                    <th class="text-right">When</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAdminAuditLogs ?? [] as $al)
                                    <tr>
                                        <td class="text-white">{{ $al->admin?->username ?? '—' }}</td>
                                        <td class="text-white-50">{{ $al->action }}</td>
                                        <td class="text-white-50">{{ $al->ip }}</td>
                                        <td class="text-right text-white-50">{{ optional($al->created_at)->format('M d, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-white-50">No admin activity yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5 col-12">
        <div class="card glass-card border-0 rounded-lg p-4 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-weight-bold mb-0">Top Services</h5>
                <span class="text-white-50 small">By usage</span>
            </div>
            <div class="list-group list-group-flush">
                @forelse($topServices as $row)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0" style="background: transparent; border-color: rgba(255,255,255,0.06);">
                        <span class="text-white">{{ strtoupper($row->service_type) }}</span>
                        <span class="badge badge-pill" style="background: rgba(255,255,255,0.06); color: #ddd;">{{ $row->total }}</span>
                    </div>
                @empty
                    <div class="text-center py-4 text-white-50">No verification data yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card glass-card border-0 rounded-lg p-4 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-weight-bold mb-0">System Health</h5>
                <a href="{{ route('admin.settings.index') }}#tab-api-keys" class="small text-primary text-decoration-none">Configure</a>
            </div>
            @php
                $items = $systemHealthItems ?? [];
                $okCount = collect($items)->where('ok', true)->count();
                $totalCount = count($items);
            @endphp
            <div class="d-flex justify-content-between mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span class="text-white-50">Checks passing</span>
                <span class="text-white font-weight-bold">{{ $okCount }} / {{ $totalCount }}</span>
            </div>
            <div class="list-group list-group-flush">
                @foreach($items as $it)
                    <div class="list-group-item px-0" style="background: transparent; border-color: rgba(255,255,255,0.06);">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white">{{ $it['label'] }}</span>
                            @if($it['ok'])
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-danger">Fix</span>
                            @endif
                        </div>
                        @if(!$it['ok'] && !empty($it['hint']))
                            <div class="text-white-50 small mt-1">{{ $it['hint'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card glass-card border-0 rounded-lg p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-weight-bold mb-0">Feature Flags</h5>
                <a href="{{ route('admin.settings.index', ['tab' => 'tab-features']) }}" class="small text-primary text-decoration-none">Manage</a>
            </div>
            <div class="d-flex justify-content-between mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span class="text-white-50">Disabled</span>
                <span class="text-white font-weight-bold">{{ $disabledFeatures ?? 0 }}</span>
            </div>
            <div class="list-group list-group-flush">
                @forelse(($disabledFeatureList ?? []) as $f)
                    <div class="list-group-item px-0" style="background: transparent; border-color: rgba(255,255,255,0.06);">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white">{{ $f->feature_name }}</span>
                            <span class="badge badge-danger">OFF</span>
                        </div>
                        @if($f->offline_message)
                            <div class="text-white-50 small mt-1">{{ \Illuminate\Support\Str::limit($f->offline_message, 70) }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-3 text-white-50">No disabled features.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="text-white font-weight-bold mb-0">Management Hub</h5>
    <span class="badge badge-primary px-3 py-2 rounded-pill" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa;">Manual Overrides</span>
</div>

<div class="row mb-5">
    <div class="col-6 col-md-3 text-center mb-4">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none d-block overlay-card" style="transition: 0.3s transform;">
            <div class="mx-auto shadow-sm d-flex align-items-center justify-content-center mb-3 text-white" style="width: 60px; height: 60px; border-radius: 18px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.1)); border: 1px solid rgba(59, 130, 246, 0.2);">
                <i class="fa fa-users-gear fa-lg"></i>
            </div>
            <small class="d-block font-weight-bold text-white-50 text-uppercase tracking-wider" style="letter-spacing: 1px;">Users</small>
        </a>
    </div>
    <div class="col-6 col-md-3 text-center mb-4">
        <a href="{{ route('admin.settings.index') }}?tab=orders" class="text-decoration-none d-block overlay-card" style="transition: 0.3s transform;">
            <div class="mx-auto shadow-sm d-flex align-items-center justify-content-center mb-3 text-white" style="width: 60px; height: 60px; border-radius: 18px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(22, 163, 74, 0.1)); border: 1px solid rgba(34, 197, 94, 0.2);">
                <i class="fa fa-chart-line fa-lg text-success"></i>
            </div>
            <small class="d-block font-weight-bold text-white-50 text-uppercase tracking-wider" style="letter-spacing: 1px;">Pricing</small>
        </a>
    </div>
    <div class="col-6 col-md-3 text-center mb-4">
        <a href="{{ route('admin.settings.index') }}" class="text-decoration-none d-block overlay-card" style="transition: 0.3s transform;">
            <div class="mx-auto shadow-sm d-flex align-items-center justify-content-center mb-3 text-white" style="width: 60px; height: 60px; border-radius: 18px; background: linear-gradient(135deg, rgba(234, 179, 8, 0.2), rgba(202, 138, 4, 0.1)); border: 1px solid rgba(234, 179, 8, 0.2);">
                <i class="fa fa-key fa-lg text-warning"></i>
            </div>
            <small class="d-block font-weight-bold text-white-50 text-uppercase tracking-wider" style="letter-spacing: 1px;">APIs</small>
        </a>
    </div>
    <div class="col-6 col-md-3 text-center mb-4">
        <a href="{{ route('admin.settings.index') }}" class="text-decoration-none d-block overlay-card" style="transition: 0.3s transform;">
            <div class="mx-auto shadow-sm d-flex align-items-center justify-content-center mb-3 text-white" style="width: 60px; height: 60px; border-radius: 18px; background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.1)); border: 1px solid rgba(239, 68, 68, 0.2);">
                <i class="fa fa-bell fa-lg text-danger"></i>
            </div>
            <small class="d-block font-weight-bold text-white-50 text-uppercase tracking-wider" style="letter-spacing: 1px;">Alerts</small>
        </a>
    </div>
</div>

<!-- System Alerts -->
<div class="row">
    <div class="col-12">
        <h5 class="font-weight-bold text-white mb-4">Recent Onboarding</h5>
        <div class="card glass-card border-0 rounded-lg overflow-hidden shadow-sm" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="list-group list-group-flush">
                @forelse($recentUsers as $user)
                <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center py-4 px-4" style="background: transparent; border-color: rgba(255,255,255,0.05);">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="d-flex align-items-center mb-3 mb-md-0 text-decoration-none" aria-label="Open full profile for {{ $user->fullname }}">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 font-weight-bold text-white shadow-sm" style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-hover)); border: 2px solid rgba(255,255,255,0.1);">
                            {{ strtoupper(substr($user->fullname ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-1 font-weight-bold text-white">{{ $user->fullname }}</h6>
                            <small class="text-white-50"><i class="fa fa-envelope mr-1"></i> {{ $user->email }}</small>
                        </div>
                    </a>
                    <span class="badge badge-pill font-weight-normal py-1 px-3" style="background: rgba(255,255,255,0.05); color: #ccc;">{{ $user->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <div class="text-center py-5">
                    <p class="text-white-50">No recent onboarding.</p>
                </div>
                @endforelse
            </div>
            <div class="p-3 text-center" style="background: rgba(0,0,0,0.1);">
                <a href="{{ route('admin.users.index') }}" class="text-primary font-weight-bold text-decoration-none small text-uppercase tracking-wider">View System Directory <i class="fa fa-arrow-right ml-1"></i></a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    .overlay-card:hover { transform: translateY(-5px); }
    .dropdown-item:hover { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
    .nav-pills .nav-link { color: rgba(255,255,255,0.6); border-radius: 10px; padding: 8px 14px; }
    .nav-pills .nav-link.active { background: rgba(59,130,246,0.2); color: #93c5fd; border: 1px solid rgba(59,130,246,0.25); }
    
    /* High Contrast Mode Styles */
    body.high-contrast {
        background-color: #000 !important;
        color: #fff !important;
    }
    body.high-contrast .glass-card {
        background: #111 !important;
        border: 1px solid #444 !important;
        backdrop-filter: none !important;
    }
    body.high-contrast .text-white-50 {
        color: #ccc !important;
    }
    body.high-contrast .badge {
        border: 1px solid currentColor;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('theme-toggle');
        
        // Check saved preference
        if (localStorage.getItem('admin-high-contrast') === 'true') {
            document.body.classList.add('high-contrast');
        }
        
        toggleBtn.addEventListener('click', function() {
            document.body.classList.toggle('high-contrast');
            const isHighContrast = document.body.classList.contains('high-contrast');
            localStorage.setItem('admin-high-contrast', isHighContrast);
        });

        // Chart.js Configuration
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#ccc',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                    ticks: { color: 'rgba(255, 255, 255, 0.4)', font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: 'rgba(255, 255, 255, 0.4)', font: { size: 10 } }
                }
            }
        };

        // Multi-Metrics Chart
        const ctxMetrics = document.getElementById('platformMetricsChart').getContext('2d');
        const labels = {!! json_encode($dailyRevenue->pluck('day')->map(fn($d) => date('D', strtotime($d)))) !!};
        
        new Chart(ctxMetrics, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: {!! json_encode($dailyRevenue->pluck('total')) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6'
                    },
                    {
                        label: 'Verifications',
                        data: {!! json_encode($dailyVerifications->pluck('total')) !!},
                        borderColor: '#818cf8',
                        borderDash: [5, 5],
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0
                    }
                ]
            },
            options: chartOptions
        });

        // User Acquisition Chart
        const ctxUsers = document.getElementById('userAcquisitionChart').getContext('2d');
        new Chart(ctxUsers, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'New Signups',
                    data: {!! json_encode($dailySignups->pluck('total')) !!},
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderRadius: 5,
                    barThickness: 15
                }]
            },
            options: chartOptions
        });
    });
</script>
@endpush
@endsection

@extends('layouts.nexus')

@section('title', 'VTU & Bill Payments Hub | ' . config('app.name'))

@section('content')
@php
    $user = auth()->user();
@endphp

<div class="service-page fade-in">
    <x-nexus.service-header
        title="VTU & Bill Payments Hub"
        subtitle="Airtime, data, cable TV, and electricity—fast checkout, clean references, and a single wallet."
        icon="fa-solid fa-layer-group"
        icon-class="vtu-hub-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant delivery</span>
            <span class="badge-accent"><i class="fa-solid fa-receipt"></i> Trackable history</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> Secure billing</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="panel-card p-4 mb-4">
        <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap: 12px;">
            <div>
                <div class="text-muted small">VTU financial dashboard</div>
                <div class="h5 font-weight-bold mb-0">Wallet, billing, and transaction history</div>
            </div>
            <div class="d-flex flex-wrap vtu-finance-nav" style="gap: 10px;">
                <a href="#vtuOverview" class="btn btn-outline btn-sm">Overview</a>
                <a href="#vtuWallet" class="btn btn-outline btn-sm">Fund wallet</a>
                <a href="#vtuBilling" class="btn btn-outline btn-sm">Billing</a>
                <a href="#vtuHistory" class="btn btn-outline btn-sm">History</a>
                <a href="#vtuActions" class="btn btn-primary btn-sm">Pay bills</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4 mb-4" id="vtuOverview">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="h6 font-weight-bold mb-0">Overview</div>
                        <div class="text-muted small">Key metrics for this month.</div>
                    </div>
                    <span class="badge badge-secondary">This month</span>
                </div>

                <div class="vtu-metrics-grid">
                    <div class="vtu-metric">
                        <div class="vtu-metric-label">VTU spend</div>
                        <div class="vtu-metric-val">₦{{ number_format((float) ($monthSpend ?? 0), 2) }}</div>
                    </div>
                    <div class="vtu-metric">
                        <div class="vtu-metric-label">Successful</div>
                        <div class="vtu-metric-val">{{ number_format((int) ($monthSuccessCount ?? 0)) }}</div>
                    </div>
                    <div class="vtu-metric">
                        <div class="vtu-metric-label">Pending</div>
                        <div class="vtu-metric-val">{{ number_format((int) ($pendingCount ?? 0)) }}</div>
                    </div>
                    <div class="vtu-metric">
                        <div class="vtu-metric-label">Failed</div>
                        <div class="vtu-metric-val">{{ number_format((int) ($failedCount ?? 0)) }}</div>
                    </div>
                </div>
            </div>

            <div class="panel-card p-4 mb-4" id="vtuHistory">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="h6 font-weight-bold mb-0">Transaction history</div>
                        <div class="text-muted small">Your latest VTU transactions.</div>
                    </div>
                    <a href="{{ route('history') }}" class="small text-muted">View all</a>
                </div>

                @php
                    $recent = isset($recentVtu) ? $recentVtu : collect();
                @endphp

                @if($recent->isEmpty())
                    <div class="text-muted small">No VTU transactions yet. Use the Pay bills section to start.</div>
                @else
                    <div class="table-container">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th class="text-right">Total</th>
                                    <th>Status</th>
                                    <th class="text-right">When</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent->take(6) as $t)
                                    <tr>
                                        <td class="font-weight-600">{{ str_replace('vtu_', '', (string) ($t->service_type ?? '')) ?: 'VTU' }}</td>
                                        <td class="text-right">₦{{ number_format((float) ($t->total ?? 0), 2) }}</td>
                                        <td>
                                            @php $st = (string) ($t->status ?? 'pending'); @endphp
                                            <span class="badge {{ $st === 'success' ? 'badge-success' : ($st === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ ucfirst($st) }}
                                            </span>
                                        </td>
                                        <td class="text-right text-muted small">{{ optional($t->created_at)->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4" id="vtuWallet">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="h6 font-weight-bold mb-0">Fund wallet</div>
                    <span class="badge badge-secondary">Wallet</span>
                </div>
                <div class="text-muted small mb-2">Your VTU payments use this wallet balance.</div>
                <div class="h3 font-weight-bold mb-3">₦{{ number_format((float) ($walletBalance ?? 0), 2) }}</div>
                <div class="d-grid gap-2">
                    <a href="{{ route('wallet.fund') }}" class="btn btn-primary w-100" data-cta="vtu_hub_fund"><i class="fa-solid fa-plus mr-2"></i>Fund wallet</a>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline btn-sm flex-grow-1" data-cta="vtu_hub_airtime"><i class="fa-solid fa-mobile-screen-button mr-2"></i>Airtime</a>
                        <a href="{{ route('services.vtu.data') }}" class="btn btn-outline btn-sm flex-grow-1" data-cta="vtu_hub_data"><i class="fa-solid fa-wifi mr-2"></i>Data</a>
                        <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline btn-sm flex-grow-1" data-cta="vtu_hub_cable"><i class="fa-solid fa-tv mr-2"></i>Cable</a>
                        <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline btn-sm flex-grow-1" data-cta="vtu_hub_electricity"><i class="fa-solid fa-lightbulb mr-2"></i>Electricity</a>
                    </div>
                </div>
            </div>

            <div class="panel-card p-4 mb-4" id="vtuBilling">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="h6 font-weight-bold mb-0">Billing summary</div>
                    <span class="badge badge-secondary">Status</span>
                </div>
                <div class="text-muted small mb-3">Payment health and current processing state.</div>

                <div class="vtu-billing-grid">
                    <div class="vtu-billing-item">
                        <div class="vtu-billing-label">Successful (month)</div>
                        <div class="vtu-billing-val">{{ number_format((int) ($monthSuccessCount ?? 0)) }}</div>
                    </div>
                    <div class="vtu-billing-item">
                        <div class="vtu-billing-label">Pending</div>
                        <div class="vtu-billing-val">{{ number_format((int) ($pendingCount ?? 0)) }}</div>
                    </div>
                </div>

                @php
                    $last = $lastVtu ?? null;
                @endphp
                <div class="mt-3 p-3 rounded-lg vtu-last-tx">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="font-weight-600">Last transaction</div>
                        @if($last)
                            @php $lst = (string) ($last->status ?? 'pending'); @endphp
                            <span class="badge {{ $lst === 'success' ? 'badge-success' : ($lst === 'failed' ? 'badge-danger' : 'badge-warning') }}">{{ ucfirst($lst) }}</span>
                        @else
                            <span class="badge badge-secondary">None</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">
                        @if($last)
                            {{ (string) ($last->transaction_id ?? '') }}
                        @else
                            No billing activity yet.
                        @endif
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon vtu-hub-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">Fast</div>
                <div class="stat-label">Designed like a VTU app</div>
            </div>
        </div>
    </div>

    <div class="panel-card p-4 mb-4" id="vtuActions">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
            <div>
                <div class="text-muted small">Quick actions</div>
                <div class="h5 font-weight-bold mb-0">Choose what you want to pay</div>
            </div>
            <div class="d-flex flex-wrap" style="gap: 10px;">
                <a href="{{ route('services.vtu.airtime') }}" class="btn btn-primary" data-cta="vtu_hub_airtime"><i class="fa-solid fa-mobile-screen-button mr-2"></i>Airtime</a>
                <a href="{{ route('services.vtu.data') }}" class="btn btn-outline" data-cta="vtu_hub_data"><i class="fa-solid fa-wifi mr-2"></i>Data</a>
                <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline" data-cta="vtu_hub_cable"><i class="fa-solid fa-tv mr-2"></i>Cable</a>
                <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline" data-cta="vtu_hub_electricity"><i class="fa-solid fa-lightbulb mr-2"></i>Electricity</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <a href="{{ route('services.vtu.airtime') }}" class="text-decoration-none d-block h-100">
                <div class="panel-card p-4 h-100 vtu-tile">
                    <div class="d-flex align-items-center mb-2">
                        <div class="tile-icon vtu-airtime"><i class="fa-solid fa-mobile-screen-button"></i></div>
                        <div class="ml-3">
                            <div class="font-weight-bold">Airtime Top-up</div>
                            <div class="text-muted small">MTN, Glo, Airtel, 9mobile</div>
                        </div>
                    </div>
                    <div class="text-muted small">Top up in seconds and keep clean references for reconciliation.</div>
                    <div class="mt-3 font-weight-bold">Buy airtime <i class="fa-solid fa-arrow-right ml-1"></i></div>
                </div>
            </a>
        </div>
        <div class="col-md-6 mb-4">
            <a href="{{ route('services.vtu.data') }}" class="text-decoration-none d-block h-100">
                <div class="panel-card p-4 h-100 vtu-tile">
                    <div class="d-flex align-items-center mb-2">
                        <div class="tile-icon vtu-data"><i class="fa-solid fa-wifi"></i></div>
                        <div class="ml-3">
                            <div class="font-weight-bold">Data Bundles</div>
                            <div class="text-muted small">Multi-network plans</div>
                        </div>
                    </div>
                    <div class="text-muted small">Stay online—fast checkout with history you can track.</div>
                    <div class="mt-3 font-weight-bold">Buy data <i class="fa-solid fa-arrow-right ml-1"></i></div>
                </div>
            </a>
        </div>
        <div class="col-md-6 mb-4">
            <a href="{{ route('services.vtu.cable') }}" class="text-decoration-none d-block h-100">
                <div class="panel-card p-4 h-100 vtu-tile">
                    <div class="d-flex align-items-center mb-2">
                        <div class="tile-icon vtu-cable"><i class="fa-solid fa-tv"></i></div>
                        <div class="ml-3">
                            <div class="font-weight-bold">Cable TV Subscription</div>
                            <div class="text-muted small">DSTV, GOTV, Startimes</div>
                        </div>
                    </div>
                    <div class="text-muted small">Renew instantly so you don’t miss what you watch.</div>
                    <div class="mt-3 font-weight-bold">Subscribe cable <i class="fa-solid fa-arrow-right ml-1"></i></div>
                </div>
            </a>
        </div>
        <div class="col-md-6 mb-4">
            <a href="{{ route('services.vtu.electricity') }}" class="text-decoration-none d-block h-100">
                <div class="panel-card p-4 h-100 vtu-tile">
                    <div class="d-flex align-items-center mb-2">
                        <div class="tile-icon vtu-electricity"><i class="fa-solid fa-lightbulb"></i></div>
                        <div class="ml-3">
                            <div class="font-weight-bold">Electricity Bills</div>
                            <div class="text-muted small">Prepaid/Postpaid meters</div>
                        </div>
                    </div>
                    <div class="text-muted small">Pay DISCO bills and keep a reference you can search later.</div>
                    <div class="mt-3 font-weight-bold">Pay electricity <i class="fa-solid fa-arrow-right ml-1"></i></div>
                </div>
            </a>
        </div>
    </div>

    <div class="panel-card p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="h6 font-weight-bold mb-1">More bill payments</div>
                <div class="text-muted small">Extend your VTU business with conversions, subscriptions, and digital products.</div>
            </div>
            <span class="badge badge-secondary">New</span>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <a href="{{ route('services.vtu.airtime_to_cash') }}" class="text-decoration-none d-block">
                    <div class="p-3 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="font-weight-bold mb-1"><i class="fa-solid fa-money-bill-transfer mr-2 text-white-50"></i>Airtime to Cash</div>
                        <div class="text-muted small">Convert airtime value with references and tracked status.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="{{ route('services.vtu.betting') }}" class="text-decoration-none d-block">
                    <div class="p-3 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="font-weight-bold mb-1"><i class="fa-solid fa-ticket mr-2 text-white-50"></i>Betting Funding</div>
                        <div class="text-muted small">Fund betting accounts with receipts and history.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="{{ route('services.vtu.epin') }}" class="text-decoration-none d-block">
                    <div class="p-3 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="font-weight-bold mb-1"><i class="fa-solid fa-key mr-2 text-white-50"></i>ePINs</div>
                        <div class="text-muted small">Purchase digital pins with trackable references.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="{{ route('services.vtu.internet') }}" class="text-decoration-none d-block">
                    <div class="p-3 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="font-weight-bold mb-1"><i class="fa-solid fa-globe mr-2 text-white-50"></i>Internet Subscription</div>
                        <div class="text-muted small">Pay for ISP bundles and keep clean records.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="{{ route('services.vtu.recharge_printing') }}" class="text-decoration-none d-block">
                    <div class="p-3 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="font-weight-bold mb-1"><i class="fa-solid fa-print mr-2 text-white-50"></i>Recharge Card Printing</div>
                        <div class="text-muted small">Generate and print data/airtime PINs for resale.</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="panel-card p-4 mb-4" id="educationHub">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="h6 font-weight-bold mb-1">Education Hub</div>
                <div class="text-muted small">Purchase examination PINs and registration tokens instantly.</div>
            </div>
            <span class="badge badge-info"><i class="fa-solid fa-graduation-cap mr-1"></i> Education</span>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="{{ route('services.education.waec') }}" class="text-decoration-none d-block h-100">
                    <div class="p-3 rounded-lg text-center h-100 vtu-tile" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="mx-auto mb-2" style="width: 46px; height: 46px;">
                            <img src="{{ asset('vtusite/images/waec.png') }}" alt="WAEC" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div class="font-weight-bold small mb-1">WAEC Checker</div>
                        <div class="text-muted x-small">Result checker PINs</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="{{ route('services.education.waec_registration') }}" class="text-decoration-none d-block h-100">
                    <div class="p-3 rounded-lg text-center h-100 vtu-tile" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="mx-auto mb-2" style="width: 46px; height: 46px;">
                            <img src="{{ asset('vtusite/images/waec.png') }}" alt="WAEC Reg" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div class="font-weight-bold small mb-1">WAEC Registration</div>
                        <div class="text-muted x-small">Exam registration tokens</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="{{ route('services.education.neco') }}" class="text-decoration-none d-block h-100">
                    <div class="p-3 rounded-lg text-center h-100 vtu-tile" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="mx-auto mb-2" style="width: 46px; height: 46px;">
                            <img src="{{ asset('vtusite/images/neco.png') }}" alt="NECO" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.src='https://via.placeholder.com/46x46/0f172a/94a3b8?text=NECO'">
                        </div>
                        <div class="font-weight-bold small mb-1">NECO Checker</div>
                        <div class="text-muted x-small">Result checker tokens</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="{{ route('services.education.nabteb') }}" class="text-decoration-none d-block h-100">
                    <div class="p-3 rounded-lg text-center h-100 vtu-tile" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="mx-auto mb-2" style="width: 46px; height: 46px;">
                            <img src="{{ asset('vtusite/images/nabteb.png') }}" alt="NABTEB" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div class="font-weight-bold small mb-1">NABTEB Checker</div>
                        <div class="text-muted x-small">Result checker PINs</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="{{ route('services.education.jamb') }}" class="text-decoration-none d-block h-100">
                    <div class="p-3 rounded-lg text-center h-100 vtu-tile" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                        <div class="mx-auto mb-2" style="width: 46px; height: 46px;">
                            <img src="{{ asset('vtusite/images/jamb.png') }}" alt="JAMB" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.src='https://via.placeholder.com/46x46/0f172a/94a3b8?text=JAMB'">
                        </div>
                        <div class="font-weight-bold small mb-1">JAMB PINs</div>
                        <div class="text-muted x-small">UTME/DE registration</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .vtu-hub-bg { background: rgba(59, 130, 246, 0.12); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.22); }
    .vtu-finance-nav .btn { border-radius: 9999px; }
    .vtu-metrics-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .vtu-metric { border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03); border-radius: 16px; padding: 14px; }
    .vtu-metric-label { font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.75); }
    .vtu-metric-val { font-size: 1.2rem; font-weight: 800; margin-top: 6px; color: #fff; }
    .vtu-billing-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .vtu-billing-item { border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03); border-radius: 16px; padding: 14px; }
    .vtu-billing-label { font-size: 0.8rem; color: rgba(255,255,255,0.75); }
    .vtu-billing-val { margin-top: 6px; font-weight: 800; color: #fff; font-size: 1.1rem; }
    .vtu-last-tx { border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02); }

    @media (max-width: 991px) {
        .vtu-metrics-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px) {
        .vtu-metrics-grid { grid-template-columns: 1fr; }
        .vtu-billing-grid { grid-template-columns: 1fr; }
    }
    .vtu-tile { transition: transform 0.15s ease, border-color 0.15s ease; }
    .vtu-tile:hover { transform: translateY(-2px); border-color: rgba(59,130,246,0.35) !important; }
    .tile-icon { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.04); color: #fff; }
    .vtu-airtime { background: rgba(239, 68, 68, 0.10); border-color: rgba(239, 68, 68, 0.20); color: #fca5a5; }
    .vtu-data { background: rgba(245, 158, 11, 0.10); border-color: rgba(245, 158, 11, 0.20); color: #fcd34d; }
    .vtu-cable { background: rgba(139, 92, 246, 0.10); border-color: rgba(139, 92, 246, 0.20); color: #c4b5fd; }
    .vtu-electricity { background: rgba(16, 185, 129, 0.10); border-color: rgba(16, 185, 129, 0.20); color: #6ee7b7; }
    .vtu-education { background: rgba(59, 130, 246, 0.10); border-color: rgba(59, 130, 246, 0.20); color: #93c5fd; }
</style>
@endpush

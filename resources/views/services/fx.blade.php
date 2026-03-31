@extends('layouts.nexus')

@section('title', 'FX & Currency Exchange | ' . config('app.name'))

@section('content')
<div class="service-page fade-in-up">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(124, 58, 237, 0.05)); border: 1px solid rgba(168, 85, 247, 0.2);">
        <div class="sh-icon" style="background: linear-gradient(135deg, #7c3aed, #a855f7); color: #fff;"><i class="fa-solid fa-arrows-rotate"></i></div>
        <div class="sh-text">
            <h1 class="h3 font-weight-bold mb-1">Currency Exchange</h1>
            <p class="text-white-50 small mb-0">Live mid-market rates. Convert between global currencies instantly with 0.5% flat fee.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-bolt mr-1"></i> Real-time Rates</span>
            <span class="badge-accent"><i class="fa-solid fa-lock mr-1"></i> Rate Lock</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <div class="tab-strip mb-4">
                    <button class="s-tab active" onclick="switchS('fx-convert', this)"><i class="fa-solid fa-rotate mr-2"></i>Convert</button>
                    <button class="s-tab" onclick="switchS('fx-rates', this)"><i class="fa-solid fa-chart-line mr-2"></i>Live Rates</button>
                    <button class="s-tab" onclick="switchS('fx-history', this)"><i class="fa-solid fa-clock-rotate-left mr-2"></i>History</button>
                    <button class="s-tab" onclick="switchS('fx-send', this)"><i class="fa-solid fa-paper-plane mr-2"></i>Send Abroad</button>
                </div>

                <div id="panel-container">
                    <!-- CONVERSION -->
                    <div class="s-panel active" id="fx-convert">
                        <div class="conversion-ui p-4 rounded-xl mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="mb-3 small text-muted d-flex justify-content-between">
                                <span class="uppercase tracking-widest font-weight-bold" style="font-size: 0.65rem;">You Send</span>
                                <span class="text-white">Balance: ₦{{ number_format(Auth::user()->balance?->user_balance ?? 0, 2) }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-4 bg-black-10 p-3 rounded-12">
                                <input type="number" id="sendAmt" class="form-control form-control-lg border-0 bg-transparent flex-grow-1 h-auto py-0 font-weight-bold" style="font-size: 2.2rem; color: #fff; box-shadow: none;" value="1000" oninput="calculateExchange()">
                                <select id="sendCurr" class="curr-selector-nexus" onchange="calculateExchange()">
                                    <option value="NGN">🇳🇬 NGN</option>
                                    <option value="USD">🇺🇸 USD</option>
                                </select>
                            </div>

                            <div class="text-center my-n2 position-relative" style="z-index: 2;">
                                <button class="btn btn-dark btn-sm rounded-circle p-2 border-primary" style="width: 44px; height: 44px; background: #0a0a0f;" onclick="swapCurrencies()">
                                    <i class="fa-solid fa-arrow-down-up-across-line text-primary"></i>
                                </button>
                            </div>

                            <div class="mb-3 mt-4 small text-muted uppercase tracking-widest font-weight-bold" style="font-size: 0.65rem;">Recipient Gets</div>
                            <div class="d-flex align-items-center gap-3 mb-2 bg-black-10 p-3 rounded-12">
                                <input type="number" id="receiveAmt" class="form-control form-control-lg border-0 bg-transparent flex-grow-1 h-auto py-0 font-weight-bold" style="font-size: 2.2rem; color: #a855f7; box-shadow: none;" readonly>
                                <select id="receiveCurr" class="curr-selector-nexus" onchange="calculateExchange()">
                                    <option value="USD">🇺🇸 USD</option>
                                    <option value="EUR">🇪🇺 EUR</option>
                                    <option value="GBP">🇬🇧 GBP</option>
                                </select>
                            </div>
                            
                            <div class="exchange-info border-top border-white-5 mt-4 pt-3 small text-muted d-flex justify-content-between">
                                <span>Exchange Rate: <strong class="text-white" id="rateText">1 USD = 1,710 NGN</strong></span>
                                <span>Platform Fee: <strong class="text-white">{{ number_format($fxFeePercent ?? 1.5, 2) }}%</strong></span>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg w-100 py-3 font-weight-bold" style="background: linear-gradient(135deg, #7c3aed, #a855f7); border: none; border-radius: 12px;" onclick="executeFX()">
                            <i class="fa-solid fa-bolt mr-2"></i> Execute Instant Conversion
                        </button>
                    </div>

                    <!-- LIVE RATES -->
                    <div class="s-panel" id="fx-rates">
                        <div class="rate-list">
                            @php
                                $flags = ['USD' => '🇺🇸', 'GBP' => '🇬🇧', 'EUR' => '🇪🇺', 'CAD' => '🇨🇦', 'AUD' => '🇦🇺', 'CNY' => '🇨🇳'];
                                $names = ['USD' => 'US Dollar', 'GBP' => 'British Pound', 'EUR' => 'Euro', 'CAD' => 'Canadian Dollar', 'AUD' => 'Australian Dollar', 'CNY' => 'Chinese Yuan'];
                            @endphp
                            @foreach(($rates ?? []) as $code => $rate)
                            <div class="rate-item p-3 mb-2 border rounded-12 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05) !important;">
                                <div class="d-flex align-items-center">
                                    <span class="h4 m-0 mr-3">{{ $flags[$code] ?? '💱' }}</span>
                                    <div>
                                        <div class="font-weight-bold text-white">{{ $code }} / NGN</div>
                                        <div class="small text-muted">{{ $names[$code] ?? $code }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="h6 m-0 font-weight-bold text-white">{{ number_format((float) $rate, 2) }}</div>
                                    <div class="small text-muted">updated now</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="s-panel" id="fx-history">
                        @if(($fxHistory ?? collect())->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No FX transactions yet.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table admin-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th class="text-right">Net Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fxHistory as $tx)
                                            <tr>
                                                <td><code class="text-primary">{{ $tx->transaction_id }}</code></td>
                                                <td>{{ $tx->order_type }}</td>
                                                <td>{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                                <td class="text-right">₦{{ number_format(($tx->balance_after ?? 0) - ($tx->balance_before ?? 0), 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- SEND ABROAD -->
                    <div class="s-panel" id="fx-send">
                        <div class="text-center py-5">
                            <div class="sh-icon mx-auto mb-4" style="background: rgba(168, 85, 247, 0.1); color: #a855f7; width: 80px; height: 80px; font-size: 2rem;"><i class="fa-solid fa-globe-africa"></i></div>
                            <h4 class="font-weight-bold text-white">International Wire Transfer</h4>
                            <p class="text-white-50 mx-auto" style="max-width: 400px;">Send money to 50+ countries via SWIFT, SEPA, and local networks. Lowest fees in the market.</p>
                            <button class="btn btn-primary btn-lg px-5 mt-3" style="background: #a855f7; border: none; border-radius: 12px;">Start International Transfer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-4 uppercase tracking-widest text-primary">Price Alert</h3>
                <p class="small text-muted">Get notified via email/SMS when USD hits your target price.</p>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-black-10 border-white-5 text-muted">₦</span>
                    </div>
                    <input type="number" class="form-control bg-black-10 border-white-5 text-white" placeholder="Target Rate">
                    <div class="input-group-append">
                        <button class="btn btn-primary px-3" style="background: #a855f7; border: none;">Set</button>
                    </div>
                </div>
            </div>

            <div class="stat-card" style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.2);">
                <div class="stat-icon" style="color: #a855f7;"><i class="fa-solid fa-chart-line"></i></div>
                <div class="stat-val">Low</div>
                <div class="stat-label">Market Volatility Index</div>
            </div>

            <div class="panel-card p-4 mt-4">
                <h3 class="h6 font-weight-bold mb-3 uppercase tracking-widest text-white">Why Exchange with us?</h3>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><i class="fa-solid fa-check text-success mr-2"></i> Instant wallet funding</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-success mr-2"></i> No hidden spread/margins</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-success mr-2"></i> Institutional-grade security</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rounded-xl { border-radius: 20px; }
    .rounded-12 { border-radius: 12px; }
    .bg-black-10 { background: rgba(0,0,0,0.2); }
    
    .tab-strip { display: flex; gap: 0; overflow-x: auto; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .s-tab { padding: 15px 25px; background: none; border: none; color: rgba(255,255,255,0.4); cursor: pointer; font-size: 0.9rem; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.3s; white-space: nowrap; }
    .s-tab.active { color: #a855f7; border-bottom-color: #a855f7; background: rgba(168, 85, 247, 0.05); }

    .s-panel { display: none; }
    .s-panel.active { display: block; animation: fadeInUp 0.5s ease; }

    .curr-selector-nexus { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px 18px; border-radius: 12px; font-weight: 700; outline: none; transition: 0.3s; }
    .curr-selector-nexus:focus { border-color: #a855f7; background: rgba(255,255,255,0.12); }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    const RATES = @json($rates ?? ['USD' => 1710, 'GBP' => 2145, 'EUR' => 1820, 'CAD' => 1240, 'AUD' => 980, 'CNY' => 215]);
    const FX_FEE = {{ (float) ($fxFeePercent ?? 1.5) }};

    function switchS(id, btn) {
        $('.s-panel').removeClass('active');
        $('.s-tab').removeClass('active');
        $('#' + id).addClass('active');
        $(btn).addClass('active');
    }

    function calculateExchange() {
        let amt = parseFloat($('#sendAmt').val()) || 0;
        let from = $('#sendCurr').val();
        let to   = $('#receiveCurr').val();
        let rate = RATES[to] || RATES[from];

        if (from === 'NGN') {
            $('#receiveAmt').val((amt / rate).toFixed(2));
            $('#rateText').text('1 ' + to + ' = ' + rate.toLocaleString() + ' NGN');
        } else {
            $('#receiveAmt').val((amt * rate).toFixed(2));
            $('#rateText').text('1 ' + from + ' = ' + rate.toLocaleString() + ' NGN');
        }
    }

    function swapCurrencies() {
        let send = $('#sendCurr').val();
        let receive = $('#receiveCurr').val();
        if (send === 'NGN') {
            $('#sendCurr').val(receive);
            $('#receiveCurr').val('NGN');
        } else {
            $('#sendCurr').val('NGN');
            $('#receiveCurr').val(send);
        }
        calculateExchange();
    }

    function executeFX() {
        const amt  = parseFloat($('#sendAmt').val());
        const from = $('#sendCurr').val();
        const to   = $('#receiveCurr').val();

        if (!amt || amt <= 0) { Swal.fire('Error', 'Please enter a valid amount.', 'error'); return; }

        Swal.fire({
            title: 'Authorize Exchange',
            html: `Exchange <strong>${amt} ${from}</strong> to NGN?<br><small class="text-muted">Rate: ₦${(RATES[from]||RATES[to]).toLocaleString()} | Fee: ${FX_FEE}%</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#a855f7',
            confirmButtonText: 'Confirm & Exchange',
            background: '#0a0a0f',
            color: '#fff'
        }).then((r) => {
            if (!r.isConfirmed) return;
            Swal.fire({ title: 'Processing Exchange...', didOpen: () => Swal.showLoading(), background: '#0a0a0f', color: '#fff' });
            
            $.post('{{ route("services.fx.exchange") }}', {
                _token: '{{ csrf_token() }}',
                from_currency: from,
                to_currency: 'NGN',
                amount: amt
            }).done((res) => {
                if (!res.status) {
                    Swal.fire('Error', res.message || 'Exchange failed', 'error');
                    return;
                }
                Swal.fire({
                    title: '✅ Exchange Successful!',
                    html: `Received ₦${res.received_ngn}<br><small>Ref: ${res.ref}</small>`,
                    icon: 'success',
                    background: '#0a0a0f',
                    color: '#fff'
                }).then(() => window.location.reload());
            }).fail((xhr) => {
                Swal.fire('Error', xhr.responseJSON?.message || 'Exchange failed', 'error');
            });
        });
    }

    $(document).ready(() => { calculateExchange(); });
</script>
@endpush



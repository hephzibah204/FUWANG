@extends('layouts.nexus')

@section('title', 'Virtual Cards | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(79, 70, 229, 0.05)); border: 1px solid rgba(99, 102, 241, 0.2);">
        <div class="sh-icon" style="background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff;"><i class="fa-solid fa-credit-card"></i></div>
        <div class="sh-text">
            <h1>Virtual Cards</h1>
            <p>Instant USD and NGN cards for global payments. Shop anywhere, securely.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> 3D Secure</span>
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Issuance</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <div class="tab-strip mb-4">
                    <button class="s-tab active" onclick="switchS('vc-mine', this)">My Cards</button>
                    <button class="s-tab" onclick="switchS('vc-create', this)">Create New</button>
                    <button class="s-tab" onclick="switchS('vc-txns', this)">Transactions</button>
                </div>

                <div id="panel-container">
                    <!-- MY CARDS -->
                    <div class="s-panel active" id="vc-mine">
                        @if($myCards->isEmpty())
                        <div class="text-center py-5">
                            <i class="fa-solid fa-credit-card fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">You don't have any virtual cards yet.</p>
                            <button class="btn btn-outline btn-sm" onclick="switchS('vc-create', $('.s-tab').eq(1))">Create Your First Card</button>
                        </div>
                        @else
                        @php $mainCard = $myCards->first(); @endphp
                        <div class="vc-display-wrapper mb-4 animate__animated animate__fadeIn">
                            <div class="vc-card-visual {{ strtolower($mainCard->currency) }}" id="mainCardVisual">
                                <div class="vc-brand">VISA</div>
                                <div class="vc-chip"></div>
                                <div class="vc-number" id="cardNum" data-full="{{ $mainCard->card_number }}">
                                    {{ substr($mainCard->card_number, 0, 4) }} •••• •••• {{ substr($mainCard->card_number, -4) }}
                                </div>
                                <div class="vc-meta d-flex justify-content-between">
                                    <span>
                                        <small class="d-block opacity-50">VALID THRU</small>
                                        <strong>{{ $mainCard->expiry_date }}</strong>
                                    </span>
                                    <span>
                                        <small class="d-block opacity-50">CVV</small>
                                        <strong id="cardCvv" data-full="{{ $mainCard->cvv }}">•••</strong>
                                    </span>
                                </div>
                                <div class="vc-status-badge">{{ strtoupper($mainCard->status) }}</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-center mb-4">
                            <button class="btn btn-secondary btn-sm px-3" onclick="toggleDetails()"><i class="fa-solid fa-eye mr-1"></i> Reveal</button>
                            <button class="btn btn-secondary btn-sm px-3" onclick="copyCard()"><i class="fa-solid fa-copy mr-1"></i> Copy</button>
                            <button class="btn btn-danger btn-sm px-3" onclick="freezeCard()"><i class="fa-solid fa-snowflake mr-1"></i> Freeze</button>
                        </div>

                        <div class="card-list mt-3">
                            <h6 class="text-white-50 mb-3 small uppercase font-weight-bold">Your Wallet ({{ $myCards->count() }})</h6>
                            @foreach($myCards as $card)
                            <div class="card-item-mini p-3 mb-2 d-flex align-items-center" onclick="updateMainCardVisual('{{ $card->card_number }}', '{{ $card->expiry_date }}', '{{ $card->cvv }}', '{{ strtolower($card->currency) }}', '{{ $card->status }}')">
                                <div class="mini-icon mr-3" style="color: {{ $card->currency == 'USD' ? '#6366f1' : '#10b981' }};">
                                    <i class="fa-solid fa-credit-card fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $card->card_name }} (•••• {{ substr($card->card_number, -4) }})</div>
                                    <div class="small text-muted">Balance: {{ $card->currency }} {{ number_format($card->balance, 2) }}</div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-muted small"></i>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- CREATE CARD -->
                    <div class="s-panel" id="vc-create">
                        <form onsubmit="initiateCard(event)">
                            <div class="form-group mb-4">
                                <label class="small text-muted mb-2">Select Currency</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="currency-option active p-3 text-center border rounded cursor-pointer" onclick="selCurr(this, 'USD')">
                                            <div class="h4 mb-1">🇺🇸</div>
                                            <div class="font-weight-bold">Dollar (USD)</div>
                                            <div class="small text-muted">$1 = ₦1,710</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="currency-option p-3 text-center border rounded cursor-pointer" onclick="selCurr(this, 'NGN')">
                                            <div class="h4 mb-1">🇳🇬</div>
                                            <div class="font-weight-bold">Naira (NGN)</div>
                                            <div class="small text-muted">Local Spend</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small text-muted mb-2">Initial Balance</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                    <input type="number" class="form-control" placeholder="Min 10" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                                <i class="fa-solid fa-plus-circle mr-2"></i> Create Card (Fee: ₦500)
                            </button>
                        </form>
                    </div>

                    <!-- TRANSACTIONS -->
                    <div class="s-panel" id="vc-txns">
                        <div class="text-center py-4">
                            <i class="fa-solid fa-receipt fa-3x text-muted mb-3 opacity-20"></i>
                            <p class="text-muted small">No recent transactions for this card.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3 d-flex align-items-center">
                    <i class="fa-solid fa-shield-heart text-primary mr-2"></i> card Security
                </h3>
                <p class="small text-muted">Block cards instantly if lost. Use dynamic CVV for added protection on untrusted sites.</p>
                <hr class="border-secondary opacity-10">
                <div class="d-flex justify-content-between align-items-center small mb-3">
                    <span>Active Fraud Guard</span>
                    <span class="text-success"><i class="fa-solid fa-circle-check"></i> Enabled</span>
                </div>
            </div>

            <div class="stat-card" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                <div class="stat-icon" style="color: #10b981;"><i class="fa-solid fa-globe"></i></div>
                <div class="stat-val">Global</div>
                <div class="stat-label">Merchant Coverage</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; overflow-x: auto; border-bottom: 2px solid rgba(255, 255, 255, 0.05); }
    .s-tab { padding: 12px 20px; background: none; border: none; color: var(--clr-text-muted); cursor: pointer; font-size: 0.85rem; font-weight: 600; border-bottom: 2px solid transparent; transition: all 0.2s; white-space: nowrap; }
    .s-tab.active { color: #6366f1; border-bottom-color: #6366f1; }

    .s-panel { display: none; }
    .s-panel.active { display: block; animation: fadeIn 0.4s ease; }

    /* VC Design */
    .vc-card-visual { width: 100%; max-width: 380px; height: 210px; border-radius: 22px; padding: 25px; position: relative; margin: 0 auto; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
    .vc-card-visual.usd { background: linear-gradient(135deg, #312e81, #4f46e5); }
    .vc-card-visual.ngn { background: linear-gradient(135deg, #064e3b, #059669); }
    
    .vc-brand { position: absolute; top: 25px; right: 25px; font-weight: 800; font-style: italic; font-size: 1.2rem; opacity: 0.8; }
    .vc-chip { width: 45px; height: 32px; background: linear-gradient(135deg, #d4d4d8, #a1a1aa); border-radius: 6px; margin-top: 5px; }
    .vc-number { font-family: 'Courier New', monospace; font-size: 1.2rem; letter-spacing: 3px; margin: 35px 0 20px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
    .vc-status-badge { position: absolute; bottom: 25px; right: 25px; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 50px; }

    .card-item-mini { border: var(--border-glass); background: rgba(255,255,255,0.02); border-radius: 14px; cursor: pointer; transition: 0.2s; }
    .card-item-mini:hover { background: rgba(255,255,255,0.05); border-color: #6366f1; }

    .currency-option { border: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
    .currency-option.active { border-color: #6366f1; background: rgba(99, 102, 241, 0.05); }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
    let selectedCurrency = 'usd';

    function switchS(id, btn) {
        $('.s-panel').removeClass('active');
        $('.s-tab').removeClass('active');
        $('#' + id).addClass('active');
        $(btn).addClass('active');
    }

    function toggleDetails() {
        let numEl = $('#cardNum');
        let cvvEl = $('#cardCvv');
        let isHidden = numEl.text().includes('••••');
        
        if(isHidden) {
            numEl.text(numEl.attr('data-full'));
            cvvEl.text(cvvEl.attr('data-full'));
        } else {
            let full = numEl.attr('data-full');
            numEl.text(full.substring(0,4) + ' •••• •••• ' + full.substring(full.length-4));
            cvvEl.text('•••');
        }
    }

    function updateMainCardVisual(num, exp, cvv, curr, status) {
        $('#cardNum').attr('data-full', num).text(num.substring(0,4) + ' •••• •••• ' + num.substring(num.length-4));
        $('#cardCvv').attr('data-full', cvv).text('•••');
        $('#mainCardVisual').removeClass('usd ngn').addClass(curr).find('strong').first().text(exp);
        $('#mainCardVisual .vc-status-badge').text(status.toUpperCase());
    }

    function selCurr(el, curr) {
        selectedCurrency = curr.toLowerCase();
        $('.currency-option').removeClass('active');
        $(el).addClass('active');
    }

    function initiateCard(e) {
        e.preventDefault();
        const amount = $(e.target).find('input[type=number]').val();
        if (!amount || amount < 10) { Swal.fire('Error', 'Enter initial balance (min 10).', 'error'); return; }
        const btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Creating…');

        $.post('{{ route("services.virtual_card.create") }}', {
            _token: '{{ csrf_token() }}',
            card_type: selectedCurrency,
            initial_load: amount
        })
        .done(res => {
            if (res.status) {
                Swal.fire({
                    title: '🃏 Card Created!',
                    html: `
                        <div style="font-family:monospace;background:#1e293b;padding:1.2rem;border-radius:12px;margin-bottom:1rem">
                            <div style="font-size:1.15rem;letter-spacing:3px;margin-bottom:.5rem">${res.card.number}</div>
                            <div style="display:flex;justify-content:space-between;font-size:.85rem;opacity:.7">
                                <span>Exp: ${res.card.expiry}</span><span>CVV: ${res.card.cvv}</span><span>${res.card.currency}</span>
                            </div>
                        </div>
                        <p style="color:#4ade80">Balance: ${res.card.balance} ${res.card.currency}</p>
                        <p style="font-size:.8rem;opacity:.6">Ref: ${res.ref} | Wallet: ₦${res.wallet_balance}</p>
                    `,
                    confirmButtonColor: '#6366f1',
                    icon: 'success',
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
            btn.prop('disabled', false).html('<i class="fa-solid fa-plus-circle mr-2"></i> Create Card (Fee: ₦500)');
        })
        .fail(xhr => {
            Swal.fire('Error', xhr.responseJSON?.message || 'Network error', 'error');
            btn.prop('disabled', false).html('<i class="fa-solid fa-plus-circle mr-2"></i> Create Card (Fee: ₦500)');
        });
    }
</script>
@endpush

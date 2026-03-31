@extends('layouts.nexus')

@section('title', 'Fund Wallet | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row" style="gap: 12px;">
        <div>
            <h1 class="h3 mb-1 text-white">Fund Wallet</h1>
            <p class="mb-0 text-white-50">Choose a funding method and complete your deposit securely.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-light">Back to Dashboard</a>
    </div>

    <div class="row mt-4">
        <div class="col-lg-5 mb-4">
            <div class="panel-card p-4 h-100">
                <div class="d-flex align-items-center mb-3" style="gap: 12px;">
                    <div class="section-icon verification"><i class="fa-solid fa-credit-card"></i></div>
                    <div>
                        <div class="h5 mb-0 text-white">Card Funding</div>
                        <div class="small text-white-50">Paystack, Flutterwave (and any active gateway)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-white-50" for="fundAmount">Amount (₦)</label>
                    <input id="fundAmount" type="number" min="100" step="50" class="form-control" placeholder="1000" value="1000">
                </div>

                <button type="button" class="btn btn-primary btn-block" id="fundWithCardBtn">
                    Continue to Payment Methods
                </button>

                <div class="small text-white-50 mt-3">
                    Uses secure hosted checkout. We do not store card details.
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="panel-card p-4 h-100">
                <div class="d-flex align-items-center mb-3" style="gap: 12px;">
                    <div class="section-icon lifestyle"><i class="fa-solid fa-building-columns"></i></div>
                    <div>
                        <div class="h5 mb-0 text-white">Bank Transfer (Auto Funding)</div>
                        <div class="small text-white-50">PayVessel / Monnify / PaymentPoint (if configured)</div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <div class="small text-white-50">Generate or fetch your dedicated accounts below.</div>
                    <button type="button" class="btn btn-outline-light btn-sm" id="loadAccountsBtn">Load Accounts</button>
                </div>

                <div id="accountsStatus" class="small text-white-50 mt-3"></div>
                <div id="accountsWrap" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        const amountEl = document.getElementById('fundAmount');
        const cardBtn = document.getElementById('fundWithCardBtn');
        const loadBtn = document.getElementById('loadAccountsBtn');
        const statusEl = document.getElementById('accountsStatus');
        const wrapEl = document.getElementById('accountsWrap');

        function sanitizeAmount(raw) {
            const n = Number(raw);
            if (!Number.isFinite(n)) return null;
            const v = Math.round(n * 100) / 100;
            if (v < 100) return null;
            return v;
        }

        async function csrfPost(url, data) {
            if (window.csrfFetch) {
                return await window.csrfFetch(url, { data: data || {} });
            }
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify(data || {}),
            });
            return await res.json();
        }

        function renderAccounts(accounts) {
            if (!Array.isArray(accounts) || accounts.length === 0) {
                wrapEl.innerHTML = '';
                statusEl.textContent = 'No auto-funding accounts available.';
                return;
            }

            statusEl.textContent = 'Use any of the accounts below. Transfer confirms automatically when supported by the provider.';
            const groups = {};
            accounts.forEach((a) => {
                const key = a.provider_group || 'other';
                const label = a.provider_group_label || 'Other';
                if (!groups[key]) groups[key] = { label, items: [] };
                groups[key].items.push(a);
            });

            const order = ['payvessel', 'monnify', 'palmpay', 'paystack', 'flutterwave', 'paymentpoint', 'other'];
            let html = '';
            order.forEach((k) => {
                if (!groups[k] || groups[k].items.length === 0) return;
                html += `<div class="small text-uppercase text-white-50 mt-2 mb-2" style="letter-spacing:1px;">${groups[k].label}</div>`;
                groups[k].items.forEach((a) => {
                    const acct = (a.accountNumber || '').toString();
                    const bank = (a.bank || 'Bank').toString();
                    const name = (a.accountName || '').toString();
                    const st = (a.status || '').toString();
                    const badge = st ? `<span class="badge badge-pill ml-2" style="background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25);">${st}</span>` : ``;
                    html += `
                        <div class="alert alert-info mb-2" style="background: rgba(59, 130, 246, 0.06); border: 1px solid rgba(59, 130, 246, 0.18); color: #cbd5e1;">
                            <div class="small text-uppercase mb-1">${bank}${badge}</div>
                            <div class="d-flex align-items-center justify-content-between" style="gap:10px;">
                                <div>
                                    <div class="h5 mb-0 text-white">${acct}</div>
                                    ${name ? `<div class="small text-white-50">${name}</div>` : ``}
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-light" data-copy="${acct.replace(/"/g, '&quot;')}">Copy</button>
                            </div>
                        </div>
                    `;
                });
            });

            wrapEl.innerHTML = html;
            wrapEl.querySelectorAll('[data-copy]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const text = btn.getAttribute('data-copy') || '';
                    try {
                        await navigator.clipboard.writeText(text);
                        btn.textContent = 'Copied';
                        setTimeout(() => (btn.textContent = 'Copy'), 1200);
                    } catch {
                    }
                });
            });
        }

        let pollTimer = null;

        async function pollAccountsUntilActive() {
            if (pollTimer) return;
            pollTimer = setInterval(async () => {
                try {
                    const res = await fetch('/payment/virtual-accounts', { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();
                    if (json && json.status && json.accounts) {
                        renderAccounts(json.accounts);
                        const pending = json.accounts.some((a) => (a.status || '') === 'pending');
                        if (!pending) {
                            clearInterval(pollTimer);
                            pollTimer = null;
                        }
                    }
                } catch {
                }
            }, 5000);
        }

        cardBtn?.addEventListener('click', () => {
            const amt = sanitizeAmount(amountEl?.value);
            if (!amt) {
                if (window.Swal) {
                    window.Swal.fire('Invalid Amount', 'Minimum funding amount is ₦100.', 'warning');
                }
                return;
            }
            if (typeof window.openPayModal === 'function') {
                window.openPayModal('Wallet Funding', amt, 'Credit your Fuwa.NG wallet');
            }
        });

        loadBtn?.addEventListener('click', async () => {
            statusEl.textContent = 'Loading accounts...';
            wrapEl.innerHTML = '';
            try {
                const res = await csrfPost('/payment/auto-funding/ensure', {});
                if (res && res.status && res.accounts) {
                    renderAccounts(res.accounts);
                    const pending = res.accounts.some((a) => (a.status || '') === 'pending');
                    if (pending) {
                        statusEl.textContent = 'Some accounts are activating. This page will update automatically.';
                        pollAccountsUntilActive();
                    }
                } else {
                    statusEl.textContent = (res && res.message) ? res.message : 'Unable to load accounts.';
                }
            } catch {
                statusEl.textContent = 'Unable to load accounts. Please try again.';
            }
        });
    })();
</script>
@endpush

@extends('layouts.nexus')

@section('title', 'Agency Banking & Financial Suite')

@section('content')
<div class="service-page fade-in-up">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(5, 150, 105, 0.1), rgba(16, 185, 129, 0.05)); border: 1px solid rgba(16, 185, 129, 0.2);">
        <div class="sh-icon" style="background: linear-gradient(135deg, #059669, #10b981); color: #fff;"><i class="fa-solid fa-building-columns"></i></div>
        <div class="sh-text">
            <h1 class="h3 font-weight-bold mb-1">Agency Banking Suite</h1>
            <p class="text-white-50 small mb-0">Direct banking access: Deposits, Withdrawals, Transfers, and Micro-loans.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-shield-halved mr-1"></i> NDIC Insured</span>
            <span class="badge-accent"><i class="fa-solid fa-location-dot mr-1"></i> 10k+ Agents</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <!-- Account Summary -->
                <div class="bank-card mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bal-label">Fuwa.NG Wallet Balance</div>
                        <div class="nexus-chip">FUWA.NG</div>
                    </div>
                    <div class="bal-amount">₦{{ number_format(Auth::user()->balance?->user_balance ?? 0, 2) }}</div>
                    <div class="d-flex justify-content-between mt-4 align-items-center">
                        <div class="acct-info">
                            <div class="small text-white-50 uppercase tracking-widest" style="font-size: 0.6rem;">Virtual Account Number</div>
                            <div class="font-weight-bold text-white h6 mb-0">{{ Auth::user()->account_number ?? '99' . str_pad(Auth::id(), 8, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div class="bank-name text-right">
                            <div class="small text-white-50 uppercase tracking-widest" style="font-size: 0.6rem;">Partner Bank</div>
                            <div class="font-weight-bold text-white small mb-0">WEMA BANK / VFD</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="tab-strip mb-4">
                    <button class="s-tab active" onclick="switchS('ab-transfer', this)"><i class="fa-solid fa-paper-plane mr-2"></i>Transfer</button>
                    <button class="s-tab" onclick="switchS('ab-withdraw', this)"><i class="fa-solid fa-money-bill-transfer mr-2"></i>Withdrawal</button>
                    <button class="s-tab" onclick="switchS('ab-deposit', this)"><i class="fa-solid fa-vault mr-2"></i>Cash Deposit</button>
                    <button class="s-tab" onclick="switchS('ab-loan', this)"><i class="fa-solid fa-hand-holding-dollar mr-2"></i>Micro Loan</button>
                    <button class="s-tab" onclick="switchS('ab-history', this)"><i class="fa-solid fa-clock-rotate-left mr-2"></i>History</button>
                </div>

                <!-- Panels -->
                <div id="panel-container">
                    <!-- TRANSFER -->
                    <div class="s-panel active" id="ab-transfer">
                        <form class="service-form" id="transferForm">
                            <div class="form-group mb-4">
                                <label class="font-weight-600 mb-2 small text-muted uppercase tracking-widest">Select Destination Bank</label>
                                <div class="bank-grid">
                                    <div class="bank-opt sel" data-code="058">
                                        <div class="bank-logo">GT</div>
                                        <span>GTBank</span>
                                    </div>
                                    <div class="bank-opt" data-code="011">
                                        <div class="bank-logo">FB</div>
                                        <span>First Bank</span>
                                    </div>
                                    <div class="bank-opt" data-code="057">
                                        <div class="bank-logo">ZN</div>
                                        <span>Zenith</span>
                                    </div>
                                    <div class="bank-opt" data-code="033">
                                        <div class="bank-logo">UB</div>
                                        <span>UBA</span>
                                    </div>
                                    <div class="bank-opt" data-code="044">
                                        <div class="bank-logo">AC</div>
                                        <span>Access</span>
                                    </div>
                                    <div class="bank-opt" data-code="other">
                                        <div class="bank-logo"><i class="fa-solid fa-ellipsis"></i></div>
                                        <span>Other</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted uppercase tracking-widest">Account Number</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-hashtag"></i>
                                        <input type="text" class="form-control" placeholder="10-digit account number" maxlength="10" required id="account_number">
                                    </div>
                                    <div id="account-name-resolved" class="mt-2 text-success small font-weight-bold" style="display:none;">
                                        <i class="fa-solid fa-circle-check mr-1"></i> <span id="resolved-name">...</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-600 mb-2 small text-muted uppercase tracking-widest">Amount (₦)</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-naira-sign"></i>
                                        <input type="number" id="tr-amount" class="form-control" placeholder="0.00" min="10" required>
                                    </div>
                                    <div class="quick-amounts mt-2">
                                        <span class="q-amt" onclick="setAmt('tr-amount', 1000)">₦1k</span>
                                        <span class="q-amt" onclick="setAmt('tr-amount', 5000)">₦5k</span>
                                        <span class="q-amt" onclick="setAmt('tr-amount', 10000)">₦10k</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-600 mb-2 small text-muted uppercase tracking-widest">Transaction Remark</label>
                                <input type="text" class="form-control" placeholder="e.g. Payment for services">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 font-weight-bold" style="background: linear-gradient(135deg, #059669, #10b981); border: none; border-radius: 12px;">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Authorize Transfer
                            </button>
                        </form>
                    </div>

                    <!-- WITHDRAWAL -->
                    <div class="s-panel" id="ab-withdraw">
                        <div class="alert alert-info border-0" style="background: rgba(59, 130, 246, 0.1); border-radius: 12px;">
                            <i class="fa-solid fa-circle-info mr-2"></i> Generate a secure OTP to withdraw cash at any Fuwa.NG Agent Point or ATM.
                        </div>
                        <form class="service-form">
                            <div class="form-group mb-4">
                                <label class="font-weight-600 mb-2 small text-muted uppercase tracking-widest">Withdrawal Amount (₦)</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-naira-sign"></i>
                                    <input type="number" class="form-control" placeholder="Min ₦500" min="500" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 font-weight-bold" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; border-radius: 12px;">
                                <i class="fa-solid fa-key mr-2"></i> Generate Cash-Out Token
                            </button>
                        </form>
                    </div>

                    <!-- DEPOSIT -->
                    <div class="s-panel" id="ab-deposit">
                        <div class="text-center py-5">
                            <div class="sh-icon mx-auto mb-4" style="background: rgba(16, 185, 129, 0.1); color: #10b981; width: 80px; height: 80px; font-size: 2rem;"><i class="fa-solid fa-map-location-dot"></i></div>
                            <h4 class="font-weight-bold text-white">Find Cash Deposit Point</h4>
                            <p class="text-white-50 mx-auto" style="max-width: 400px;">Locate authorized Fuwa.NG Agents near you to deposit physical cash directly into your wallet.</p>
                            <button class="btn btn-outline-primary btn-lg px-5 mt-3">Open Agent Map</button>
                        </div>
                    </div>

                    <!-- LOAN -->
                    <div class="s-panel" id="ab-loan">
                        <div class="text-center py-5">
                            <div class="sh-icon mx-auto mb-4" style="background: rgba(168, 85, 247, 0.1); color: #a855f7; width: 80px; height: 80px; font-size: 2rem;"><i class="fa-solid fa-bolt-lightning"></i></div>
                            <h4 class="font-weight-bold text-white">Instant Micro-Loans</h4>
                            <p class="text-white-50 mx-auto" style="max-width: 400px;">Get up to ₦50,000 instant credit based on your transaction history. No collateral required.</p>
                            <button class="btn btn-primary btn-lg px-5 mt-3" style="background: #a855f7; border: none;">Check Eligibility</button>
                        </div>
                    </div>

                    <div class="s-panel" id="ab-history">
                        @if(($agencyHistory ?? collect())->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No agency transactions yet.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table admin-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($agencyHistory as $tx)
                                            <tr>
                                                <td><code class="text-primary">{{ $tx->transaction_id }}</code></td>
                                                <td>{{ $tx->order_type }}</td>
                                                <td>{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                                <td class="text-right">₦{{ number_format(($tx->balance_before ?? 0) - ($tx->balance_after ?? 0), 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Limits Card -->
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-4 uppercase tracking-widest text-primary">Account Limits</h3>
                <div class="limit-item mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Daily Transfer</span>
                        <span class="small font-weight-bold text-white">₦200,000 / ₦500,000</span>
                    </div>
                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                        <div class="progress-bar bg-primary" style="width: 40%;"></div>
                    </div>
                </div>
                <div class="limit-item">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Daily Withdrawal</span>
                        <span class="small font-weight-bold text-white">₦50,000 / ₦100,000</span>
                    </div>
                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                        <div class="progress-bar bg-success" style="width: 50%;"></div>
                    </div>
                </div>
            </div>

            <!-- Charges Card -->
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-4 uppercase tracking-widest text-warning">Pricing Schedule</h3>
                <div class="small">
                    <div class="d-flex justify-content-between mb-3 pb-2 border-bottom border-white-5">
                        <span class="text-white-50">Agency Service Fee</span>
                        <strong class="text-white">₦{{ number_format($agencyFee ?? 50, 2) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Support Card -->
            <div class="stat-card" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                <div class="stat-icon" style="color: #10b981;"><i class="fa-solid fa-headset"></i></div>
                <div class="stat-val">24/7</div>
                <div class="stat-label">Priority Banking Support</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bank-card { 
        background: linear-gradient(135deg, #10b981, #059669); 
        border-radius: 24px; 
        padding: 30px; 
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(5, 150, 105, 0.2);
    }
    .bank-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .nexus-chip {
        background: rgba(255,255,255,0.2);
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: 2px;
    }
    .bal-label { font-size: 0.75rem; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
    .bal-amount { font-size: 2.8rem; font-weight: 800; color: #fff; margin: 10px 0; }
    
    .tab-strip { display: flex; gap: 0; overflow-x: auto; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .s-tab { padding: 15px 25px; background: none; border: none; color: rgba(255,255,255,0.4); cursor: pointer; font-size: 0.9rem; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.3s; white-space: nowrap; }
    .s-tab.active { color: #10b981; border-bottom-color: #10b981; background: rgba(16, 185, 129, 0.05); }

    .s-panel { display: none; }
    .s-panel.active { display: block; animation: fadeInUp 0.5s ease; }

    .bank-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; }
    @media (max-width: 768px) { .bank-grid { grid-template-columns: repeat(3, 1fr); } }
    
    .bank-opt { 
        background: rgba(255,255,255,0.03); 
        border: 1px solid rgba(255,255,255,0.07); 
        border-radius: 16px; 
        padding: 15px 10px; 
        text-align: center; 
        cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
    }
    .bank-logo {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-weight: 800;
        font-size: 0.8rem;
        color: #fff;
    }
    .bank-opt span { font-size: 0.7rem; color: rgba(255,255,255,0.5); font-weight: 600; }
    .bank-opt:hover { background: rgba(255,255,255,0.08); transform: translateY(-3px); }
    .bank-opt.sel { border-color: #10b981; background: rgba(16, 185, 129, 0.15); }
    .bank-opt.sel .bank-logo { background: #10b981; color: #fff; }
    .bank-opt.sel span { color: #fff; }

    .input-wrap { position: relative; }
    .input-wrap i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.3); }
    .input-wrap .form-control { padding-left: 50px !important; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; height: 55px; }
    .input-wrap .form-control:focus { border-color: #10b981; background: rgba(255,255,255,0.08); box-shadow: none; }
    
    .q-amt { display: inline-block; padding: 8px 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; font-size: 0.8rem; cursor: pointer; margin-right: 8px; transition: 0.3s; color: rgba(255,255,255,0.6); }
    .q-amt:hover { border-color: #10b981; color: #10b981; background: rgba(16, 185, 129, 0.05); }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    function switchS(id, btn) {
        $('.s-panel').removeClass('active');
        $('.s-tab').removeClass('active');
        $('#' + id).addClass('active');
        $(btn).addClass('active');
    }
    
    $('.bank-opt').on('click', function() {
        $('.bank-opt').removeClass('sel');
        $(this).addClass('sel');
    });

    function setAmt(id, val) { 
        $('#' + id).val(val).addClass('animate__animated animate__pulse');
        setTimeout(() => $('#' + id).removeClass('animate__animated animate__pulse'), 500);
    }

    $('#account_number').on('input', function() {
        if (this.value.length === 10) {
            $('#account-name-resolved').fadeIn().html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Resolving Account...');
            // Mock resolution
            setTimeout(() => {
                $('#account-name-resolved').html('<i class="fa-solid fa-circle-check mr-1"></i> <span id="resolved-name">ADENIYI OLUWAFEMI JAMES</span>');
            }, 1500);
        } else {
            $('#account-name-resolved').fadeOut();
        }
    });

    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        const amount = $('#tr-amount').val();
        const customerName = $('#resolved-name').text() || 'Account Holder';
        Swal.fire({
            title: 'Authorize Transfer',
            text: `Send ₦${amount} to ${customerName}?`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send Now',
            background: '#0a0a0f',
            color: '#fff',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#transferForm').find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i>Processing...');
                Swal.fire({
                    title: 'Processing...',
                    didOpen: () => Swal.showLoading(),
                    background: '#0a0a0f',
                    color: '#fff'
                });

                $.post('{{ route("services.agency.request") }}', {
                    _token: '{{ csrf_token() }}',
                    service_type: 'transfer',
                    customer_name: customerName,
                    customer_phone: '08000000000',
                    amount: amount
                }).done((res) => {
                    if (!res.status) {
                        Swal.fire('Error', res.message || 'Unable to process transfer.', 'error');
                        btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Authorize Transfer');
                        return;
                    }

                    Swal.fire({
                        title: 'Success!',
                        text: 'Transfer submitted. Transaction Ref: ' + res.ref,
                        icon: 'success',
                        background: '#0a0a0f',
                        color: '#fff'
                    }).then(() => window.location.reload());
                }).fail((xhr) => {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Network error', 'error');
                    btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Authorize Transfer');
                });
            }
        });
    });
</script>
@endpush



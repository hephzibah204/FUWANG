@extends('layouts.nexus')

@section('title', 'Bulk SMS Service | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Bulk SMS Gateway"
        subtitle="Deliver branded SMS campaigns to thousands of Nigerian phone numbers with instant delivery."
        icon="fa-solid fa-comments"
        icon-class="sms-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Route</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> 100% Delivery Report</span>
            <span class="badge-accent"><i class="fa-solid fa-naira-sign"></i> ₦{{ number_format((float) \App\Models\SystemSetting::get('vtu_bulk_sms_rate', 4.00), 2) }} / SMS</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8 mb-4">
            <div class="panel-card p-4">
                <x-nexus.flash-message />

                <form id="bulkSmsForm" action="{{ route('services.vtu.bulksms.send') }}" method="POST">
                    @csrf

                    <!-- 1. Sender ID -->
                    <div class="form-group mb-4">
                        <label for="sender" class="font-weight-600 mb-2 text-white">Sender ID (Max 11 alphanumeric characters)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-signature input-icon"></i>
                            <input type="text" name="sender" id="sender" class="form-control vtu-input font-weight-bold" placeholder="e.g. FUWA, MYSHOP" maxlength="11" required value="{{ strtoupper(substr(config('app.name', 'FUWA'), 0, 11)) }}">
                        </div>
                        <small class="text-muted mt-1 d-block">Your registered business name or brand identifier (1 to 11 characters, letters and numbers only).</small>
                    </div>

                    <!-- 2. Recipients -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="recipients" class="font-weight-600 m-0 text-white">Recipients Phone Numbers</label>
                            <span class="badge badge-info px-2 py-1" id="recipientCountBadge">0 Recipients</span>
                        </div>
                        <textarea name="recipients" id="recipients" rows="5" class="form-control vtu-input font-mono" placeholder="Enter recipient phone numbers separated by commas or new lines&#10;e.g.&#10;08012345678&#10;08123456789, 09098765432" required style="resize: vertical;"></textarea>
                        <small class="text-muted mt-1 d-block">Format: 080XXXXXXXX, 23480XXXXXXXX. Numbers are automatically cleaned and deduplicated.</small>
                    </div>

                    <!-- 3. Message Content -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="message" class="font-weight-600 m-0 text-white">Message Content</label>
                            <span class="text-muted small">
                                <span id="charCount" class="text-white font-weight-bold">0</span> characters | 
                                <span id="pageCount" class="text-primary font-weight-bold">1</span> page(s)
                            </span>
                        </div>
                        <textarea name="message" id="message" rows="5" class="form-control vtu-input" placeholder="Type your SMS message here..." required style="resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 vtu-submit-btn" id="sendBtn">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Send Bulk SMS Campaign
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Broadcasting SMS through Network Band gateway...</p>
                </div>
            </div>
        </div>

        <!-- Sidebar / Order Summary Column -->
        <div class="col-lg-4 mb-4">
            <!-- Order Summary Card -->
            <div class="panel-card p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 font-weight-bold mb-0 text-white">Campaign Summary</h3>
                    <span class="badge badge-success px-2 py-1"><i class="fa-solid fa-shield mr-1"></i> Live</span>
                </div>

                <div class="vtu-summary-list mb-3">
                    <div class="summary-row">
                        <span class="text-muted">Sender ID</span>
                        <span class="font-weight-600 text-white font-mono" id="summarySender">{{ strtoupper(substr(config('app.name', 'FUWA'), 0, 11)) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Unique Recipients</span>
                        <span class="font-weight-600 text-white" id="summaryRecipients">0</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">SMS Pages</span>
                        <span class="font-weight-600 text-white" id="summaryPages">1</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Rate / SMS Page</span>
                        <span class="font-weight-600 text-white">₦{{ number_format((float) \App\Models\SystemSetting::get('vtu_bulk_sms_rate', 4.00), 2) }}</span>
                    </div>
                    <div class="summary-divider my-2"></div>
                    <div class="summary-row summary-total">
                        <span class="font-weight-bold text-white">Estimated Cost</span>
                        <span class="font-weight-800 text-primary h5 mb-0" id="summaryTotal">₦0.00</span>
                    </div>
                </div>

                <!-- Wallet Check -->
                <div class="vtu-wallet-box p-3 rounded mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">Your Wallet Balance</span>
                        <span class="font-weight-bold text-white" id="walletBalanceVal">₦{{ number_format((float) ($walletBalance ?? 0), 2) }}</span>
                    </div>
                    <div id="walletAlert" class="small mt-2" style="display:none;"></div>
                </div>

                <a href="{{ route('wallet.fund') }}" class="btn btn-outline btn-sm w-100 mb-2">
                    <i class="fa-solid fa-wallet mr-1"></i> Fund Wallet
                </a>
            </div>

            <!-- Guidelines Card -->
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-2 text-white">Bulk SMS Guidelines</h3>
                <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                    <li>1 Page = up to 160 characters.</li>
                    <li>Subsequent pages = 153 characters each.</li>
                    <li>Recipients are charged per page per valid phone number.</li>
                    <li>Delivery is guaranteed across MTN, Glo, Airtel, and 9mobile.</li>
                </ul>
            </div>

            <!-- Quick Navigation -->
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3 text-white">Quick Navigation</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-layer-group mr-2 text-primary"></i> Back to VTU Hub
                    </a>
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-phone mr-2 text-info"></i> Buy Airtime
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-2 mb-2">
                        <i class="fa-solid fa-wifi mr-2 text-success"></i> Buy Data Bundles
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sms-bg { background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); }

    /* Dark Input Theme */
    .vtu-input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    .vtu-input:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        color: #ffffff !important;
    }
    .vtu-input::placeholder {
        color: rgba(255, 255, 255, 0.35) !important;
    }

    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrap .input-icon {
        position: absolute;
        left: 16px;
        color: rgba(255, 255, 255, 0.4);
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
    }
    .input-wrap .vtu-input {
        padding-left: 45px !important;
        height: 50px;
        width: 100%;
    }

    /* Summary Styling */
    .vtu-summary-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.88rem;
    }
    .summary-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.08);
    }
    .summary-total {
        font-size: 1rem;
        padding-top: 4px;
    }
    .vtu-wallet-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .vtu-submit-btn {
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        padding: 14px;
        box-shadow: 0 8px 24px -4px rgba(59, 130, 246, 0.4);
    }
</style>
@endpush

@push('scripts')
<script>
    (function() {
        const ratePerSms = {{ (float) \App\Models\SystemSetting::get('vtu_bulk_sms_rate', 4.00) }};
        const walletBalance = {{ (float) ($walletBalance ?? 0) }};

        function initBulkSmsApp() {
            const senderInput = document.getElementById('sender');
            const recipientsInput = document.getElementById('recipients');
            const messageInput = document.getElementById('message');
            const recipientCountBadge = document.getElementById('recipientCountBadge');
            const charCount = document.getElementById('charCount');
            const pageCount = document.getElementById('pageCount');

            const summarySender = document.getElementById('summarySender');
            const summaryRecipients = document.getElementById('summaryRecipients');
            const summaryPages = document.getElementById('summaryPages');
            const summaryTotal = document.getElementById('summaryTotal');
            const walletAlert = document.getElementById('walletAlert');
            const bulkSmsForm = document.getElementById('bulkSmsForm');
            const sendBtn = document.getElementById('sendBtn');
            const loaderOverlay = document.getElementById('loaderOverlay');

            function updateEstimates() {
                // Sender
                if (summarySender && senderInput) {
                    summarySender.textContent = (senderInput.value || '').trim() || '—';
                }

                // Recipients count
                let raw = recipientsInput ? recipientsInput.value.trim() : '';
                let count = 0;
                if (raw.length > 0) {
                    let parts = raw.split(/[\r\n,]+/);
                    let valid = [];
                    parts.forEach(function(item) {
                        let cleaned = item.replace(/[^0-9]/g, '');
                        if (cleaned.length >= 10 && !valid.includes(cleaned)) {
                            valid.push(cleaned);
                        }
                    });
                    count = valid.length;
                }

                if (recipientCountBadge) {
                    recipientCountBadge.textContent = count + ' Recipient' + (count === 1 ? '' : 's');
                }
                if (summaryRecipients) {
                    summaryRecipients.textContent = count;
                }

                // Characters and Pages
                let msg = messageInput ? messageInput.value : '';
                let chars = msg.length;
                let pages = 1;
                if (chars > 160) {
                    pages = Math.ceil(chars / 153);
                } else if (chars === 0) {
                    pages = 1;
                }

                if (charCount) charCount.textContent = chars;
                if (pageCount) pageCount.textContent = pages;
                if (summaryPages) summaryPages.textContent = pages;

                let total = count * pages * ratePerSms;
                if (summaryTotal) {
                    summaryTotal.textContent = '₦' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                // Wallet check
                if (walletAlert) {
                    if (total > 0 && walletBalance < total) {
                        const diff = total - walletBalance;
                        walletAlert.style.display = 'block';
                        walletAlert.className = 'small mt-2 text-warning';
                        walletAlert.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i> Balance insufficient. Need ₦' + diff.toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' more.';
                    } else if (total > 0) {
                        walletAlert.style.display = 'block';
                        walletAlert.className = 'small mt-2 text-success';
                        walletAlert.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i> Balance sufficient. Ready to send.';
                    } else {
                        walletAlert.style.display = 'none';
                    }
                }
            }

            if (recipientsInput) recipientsInput.addEventListener('input', updateEstimates);
            if (messageInput) messageInput.addEventListener('input', updateEstimates);
            if (senderInput) senderInput.addEventListener('input', updateEstimates);

            if (bulkSmsForm) {
                bulkSmsForm.addEventListener('submit', function(e) {
                    let raw = recipientsInput ? recipientsInput.value.trim() : '';
                    let parts = raw.split(/[\r\n,]+/);
                    let valid = [];
                    parts.forEach(function(item) {
                        let cleaned = item.replace(/[^0-9]/g, '');
                        if (cleaned.length >= 10 && !valid.includes(cleaned)) valid.push(cleaned);
                    });

                    if (valid.length === 0) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Recipients Required',
                                text: 'Please enter at least one valid recipient phone number.',
                                background: '#0a0a0f',
                                color: '#fff'
                            });
                        } else {
                            alert('Please enter at least one valid recipient phone number.');
                        }
                        return false;
                    }

                    if (typeof Swal === 'undefined') {
                        if (sendBtn) {
                            sendBtn.disabled = true;
                            sendBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Transmitting SMS...';
                        }
                        if (loaderOverlay) loaderOverlay.style.display = 'block';
                        return true;
                    }

                    e.preventDefault();
                    let msg = messageInput ? messageInput.value : '';
                    let pages = msg.length > 160 ? Math.ceil(msg.length / 153) : 1;
                    let total = valid.length * pages * ratePerSms;

                    Swal.fire({
                        title: 'Confirm Bulk SMS Broadcast',
                        html: `
                            <div class="text-left p-3 rounded" style="background: rgba(255,255,255,0.05); font-size: 0.95rem;">
                                <div class="mb-2"><strong>Sender ID:</strong> ${senderInput.value}</div>
                                <div class="mb-2"><strong>Recipients:</strong> ${valid.length} unique numbers</div>
                                <div class="mb-2"><strong>Pages:</strong> ${pages} page(s) (${msg.length} chars)</div>
                                <div class="mb-2"><strong>Total Cost:</strong> ₦${total.toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Send Campaign',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#6b7280',
                        background: '#0a0a0f',
                        color: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (sendBtn) {
                                sendBtn.disabled = true;
                                sendBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Transmitting SMS...';
                            }
                            if (loaderOverlay) loaderOverlay.style.display = 'block';
                            bulkSmsForm.submit();
                        }
                    });
                });
            }

            updateEstimates();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBulkSmsApp);
        } else {
            initBulkSmsApp();
        }
    })();
</script>
@endpush
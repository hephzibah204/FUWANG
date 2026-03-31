@extends('layouts.nexus')

@section('title', 'Invoicing & Subscriptions | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05)); border: 1px solid rgba(245, 158, 11, 0.2);">
        <div class="sh-icon" style="background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <div class="sh-text">
            <h1>Invoicing & Billing</h1>
            <p>Professional billing solutions for businesses. Automate subscriptions and payments.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-rotate"></i> Auto-billing</span>
            <span class="badge-accent"><i class="fa-solid fa-link"></i> Pay Links</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <div class="tab-strip mb-4">
                    <button class="s-tab active" onclick="switchS('inv-list', this)">All Invoices</button>
                    <button class="s-tab" onclick="switchS('inv-create', this)">New Invoice</button>
                    <button class="s-tab" onclick="switchS('inv-subs', this)">Subscriptions</button>
                </div>

                <div id="panel-container">
                    <!-- INVOICES LIST -->
                    <div class="s-panel active" id="inv-list">
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <h6 class="m-0 font-weight-bold opacity-50 small uppercase">Transaction Vault</h6>
                            <div class="badge-pill bg-dark border border-secondary py-1 px-3">
                                <span class="small">Total Sent: ₦{{ number_format($invoices->sum('total_amount'), 2) }}</span>
                            </div>
                        </div>

                        <div class="invoice-items">
                            @if($invoices->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa-solid fa-file-invoice fa-3x text-muted mb-3 d-block opacity-20"></i>
                                <p class="text-muted">No invoices generated yet.</p>
                                <button class="btn btn-outline btn-sm" onclick="switchS('inv-create', $('.s-tab').eq(1))">Create Invoice</button>
                            </div>
                            @else
                            @foreach($invoices as $inv)
                            <div class="inv-item-row p-3 mb-2 d-flex align-items-center rounded-xl border border-transparent hover-border-primary" style="background: rgba(255,255,255,0.02); transition: 0.2s;">
                                <div class="inv-type-icon mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $inv->client_name }}</div>
                                    <div class="small text-muted">{{ $inv->invoice_number }} • ₦{{ number_format($inv->total_amount, 2) }} • {{ ucfirst($inv->status) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="small text-muted mb-1">{{ $inv->created_at->format('M d, Y') }}</div>
                                    @if($inv->pdf_path)
                                    <a href="{{ Storage::url($inv->pdf_path) }}" target="_blank" class="badge badge-primary px-2 py-1"><i class="fa-solid fa-file-pdf mr-1"></i> PDF</a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- CREATE INVOICE -->
                    <div class="s-panel" id="inv-create">
                        <form onsubmit="generateInvoice(event)">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small text-muted">Client Name</label>
                                        <div class="input-wrap">
                                            <i class="fa-solid fa-user"></i>
                                            <input type="text" name="client_name" class="form-control" placeholder="Business or Name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small text-muted">Client Email</label>
                                        <div class="input-wrap">
                                            <i class="fa-solid fa-envelope"></i>
                                            <input type="email" name="client_email" class="form-control" placeholder="email@example.com" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small text-muted">Service Description</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-pen-nib"></i>
                                    <input type="text" name="items[0][desc]" class="form-control" placeholder="What is this invoice for?" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small text-muted">Amount (₦)</label>
                                        <div class="input-wrap">
                                            <i class="fa-solid fa-naira-sign"></i>
                                            <input type="number" name="items[0][price]" class="form-control" placeholder="0.00" required>
                                            <input type="hidden" name="items[0][qty]" value="1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small text-muted">Due Date</label>
                                        <div class="input-wrap">
                                            <i class="fa-solid fa-calendar-day"></i>
                                            <input type="date" name="due_date" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Create & Send Invoice
                            </button>
                        </form>
                    </div>

                    <!-- SUBSCRIPTIONS -->
                    <div class="s-panel" id="inv-subs">
                        <div class="text-center py-5">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: rgba(245, 158, 11, 0.05); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);">
                                <i class="fa-solid fa-rotate fa-2x"></i>
                            </div>
                            <h3>Automate your Revenue</h3>
                            <p class="text-muted small px-md-5">Set up recurring billing plans for your fixed-rate clients. Fuwa.NG will handle the charges automatically.</p>
                            <button class="btn btn-outline-primary mt-3 border-secondary text-white btn-sm px-4">Create Billing Plan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4" style="border-right: 4px solid #f59e0b;">
                <h3 class="h6 font-weight-bold mb-3">Quick Link</h3>
                <p class="small text-muted">Accept payments anywhere with a simple URL. No invoice needed.</p>
                <button class="btn btn-secondary btn-block btn-sm" onclick="showPayLink()"><i class="fa-solid fa-link mr-2 text-warning"></i> Get Payment Link</button>
            </div>

            <div class="stat-card" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
                <div class="stat-icon" style="color: #f59e0b;"><i class="fa-solid fa-money-bill-trend-up"></i></div>
                <div class="stat-val">₦2.4M</div>
                <div class="stat-label">Projected Revenue</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; overflow-x: auto; border-bottom: 2px solid rgba(255, 255, 255, 0.05); }
    .s-tab { padding: 12px 20px; background: none; border: none; color: var(--clr-text-muted); cursor: pointer; font-size: 0.85rem; font-weight: 600; border-bottom: 2px solid transparent; transition: all 0.2s; white-space: nowrap; }
    .s-tab.active { color: #f59e0b; border-bottom-color: #f59e0b; }

    .s-panel { display: none; }
    .s-panel.active { display: block; animation: fadeIn 0.4s ease; }

    .hover-border-primary:hover { border-color: #f59e0b !important; background: rgba(245, 158, 11, 0.05) !important; }
    .rounded-xl { border-radius: 18px; }
    
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
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

    function generateInvoice(e) {
        e.preventDefault();
        const btn = $(e.target).find('button[type=submit]');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Generating...');

        const formData = new FormData(e.target);
        
        $.ajax({
            url: '{{ route("services.invoicing.create") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if(res.status) {
                    Swal.fire({
                        title: 'Invoice Sent!',
                        html: `
                            <p class="text-muted">Invoice <strong>${res.invoice_no}</strong> for <strong>${res.client}</strong> has been generated.</p>
                            <div class="mt-3">
                                <a href="${res.pdf_url}" target="_blank" class="btn btn-primary btn-block mb-2"><i class="fa-solid fa-file-pdf mr-2"></i> Download Invoice PDF</a>
                                <button class="btn btn-outline-secondary btn-block" onclick="location.reload()">Back to List</button>
                            </div>
                        `,
                        icon: 'success',
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                    btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Create & Send Invoice');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Network error', 'error');
                btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Create & Send Invoice');
            }
        });
    }

    function showPayLink() {
        Swal.fire({
            title: 'Your Payment Link',
            text: 'pay.nexus.ng/u/{{ Auth::user()->username ?? "user" }}',
            input: 'text',
            inputValue: 'pay.nexus.ng/u/{{ Auth::user()->username ?? "user" }}',
            confirmButtonText: 'Copy Link',
            confirmButtonColor: '#f59e0b'
        }).then((result) => {
            if (result.isConfirmed) {
                // Clipboard copy logic
                Swal.fire('Copied!', 'Link copied to clipboard.', 'success');
            }
        });
    }
</script>
@endpush

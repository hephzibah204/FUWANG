@extends('layouts.nexus')

@section('title', 'Ticketing & Events | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(8, 145, 178, 0.1), rgba(6, 182, 212, 0.05)); border: 1px solid rgba(6, 182, 212, 0.2);">
        <div class="sh-icon" style="background: linear-gradient(135deg, #0891b2, #06b6d4); color: #fff;"><i class="fa-solid fa-ticket"></i></div>
        <div class="sh-text">
            <h1>Fuwa.NG Tickets</h1>
            <p>Concerts, movies, and intercity transport. Global access, local pricing.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-qrcode"></i> Instant QR</span>
            <span class="badge-accent"><i class="fa-solid fa-plane"></i> Transport API</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <div class="tab-strip mb-4">
                    <button class="s-tab active" onclick="switchS('t-events', this)">Events</button>
                    <button class="s-tab" onclick="switchS('t-transport', this)">Transport</button>
                    <button class="s-tab" onclick="switchS('t-cinema', this)">Cinema</button>
                    <button class="s-tab" onclick="switchS('t-history', this)">My Tickets</button>
                </div>

                <div id="panel-container">
                    <!-- EVENTS -->
                    <div class="s-panel active" id="t-events">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="event-card-nexus">
                                    <div class="ec-banner" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">
                                        <i class="fa-solid fa-music"></i>
                                        <span class="badge badge-warning position-absolute" style="top: 15px; right: 15px;">Trending</span>
                                    </div>
                                    <div class="ec-content p-3">
                                        <h5 class="mb-2">Afrobeats Night Live 2026</h5>
                                        <div class="d-flex gap-3 small text-muted mb-3">
                                            <span><i class="fa-solid fa-calendar mr-1"></i> March 15</span>
                                            <span><i class="fa-solid fa-location-dot mr-1"></i> Eko Hotel, Lagos</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h6 m-0 font-weight-bold" style="color: #06b6d4;">From ₦{{ number_format($ticketPrices['regular'] ?? 2500, 0) }}</span>
                                            <button class="btn btn-sm btn-primary px-4" onclick="buyTicket('Afrobeats Night')">Get Tickets</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TRANSPORT -->
                    <div class="s-panel" id="t-transport">
                        <form onsubmit="searchTransport(event)">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted">From</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-plane-departure"></i>
                                        <input type="text" class="form-control" placeholder="Origin City">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted">To</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-plane-arrival"></i>
                                        <input type="text" class="form-control" placeholder="Destination City">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-lg w-100 mt-2" style="background: #06b6d4;">Search Carriers</button>
                        </form>
                    </div>

                    <!-- MY TICKETS -->
                    <div class="s-panel" id="t-history">
                        @if($myTickets->isEmpty())
                        <div class="text-center py-5">
                            <i class="fa-solid fa-ticket-simple fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No active tickets.</p>
                            <button class="btn btn-outline btn-sm" onclick="switchS('t-events', $('.s-tab').first())">Browse Events</button>
                        </div>
                        @else
                        <div class="ticket-vault">
                            @foreach($myTickets as $ticket)
                            <div class="inv-item-row p-3 mb-2 d-flex align-items-center rounded-xl border border-transparent hover-border-primary" style="background: rgba(255,255,255,0.02); transition: 0.2s;">
                                <div class="mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $ticket->event_name }}</div>
                                    <div class="small text-muted">{{ $ticket->reference }} • {{ ucfirst($ticket->ticket_type) }} ×{{ $ticket->quantity }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="small text-muted mb-1">{{ \Carbon\Carbon::parse($ticket->event_date)->format('M d, Y') }}</div>
                                    @if($ticket->ticket_pdf_path)
                                    <a href="{{ Storage::url($ticket->ticket_pdf_path) }}" target="_blank" class="badge badge-primary px-2 py-1"><i class="fa-solid fa-file-pdf mr-1"></i> Download</a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div id="ticketResult" class="mt-4" style="display: none;">
                    <div class="result-card-nexus animate__animated animate__fadeIn">
                        <div class="p-4 text-center">
                            <div class="sh-icon mx-auto mb-3" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;"><i class="fa-solid fa-qrcode"></i></div>
                            <h4>Payment Confirmed</h4>
                            <p class="text-muted small">Your E-Ticket for <strong id="evtName" class="text-white">...</strong> has been generated.</p>
                            <div class="alert alert-dark border-0 py-2 small mb-3">Ref: #NXS-TK-{{ date('His') }}</div>
                            <button class="btn btn-outline w-100" onclick="location.reload()">Download PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3">Quick Links</h3>
                <ul class="list-unstyled small">
                    <li class="mb-2"><span class="text-white-50"><i class="fa-solid fa-headset mr-2"></i> 24/7 Support Available</span></li>
                    <li class="mb-2"><span class="text-white-50"><i class="fa-solid fa-user-shield mr-2"></i> Fuwa.NG Buyer Protection</span></li>
                </ul>
            </div>

            <div class="stat-card" style="background: rgba(6, 182, 212, 0.05); border: 1px solid rgba(6, 182, 212, 0.2);">
                <div class="stat-icon" style="color: #06b6d4;"><i class="fa-solid fa-check-double"></i></div>
                <div class="stat-val">Verified</div>
                <div class="stat-label">Official Partner</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; overflow-x: auto; border-bottom: 2px solid rgba(255, 255, 255, 0.05); }
    .s-tab { padding: 12px 20px; background: none; border: none; color: var(--clr-text-muted); cursor: pointer; font-size: 0.85rem; font-weight: 600; border-bottom: 2px solid transparent; transition: all 0.2s; white-space: nowrap; }
    .s-tab.active { color: #06b6d4; border-bottom-color: #06b6d4; }

    .s-panel { display: none; }
    .s-panel.active { display: block; animation: fadeIn 0.4s ease; }

    .event-card-nexus { border: var(--border-glass); background: rgba(255,255,255,0.02); border-radius: 18px; overflow: hidden; transition: 0.3s; }
    .event-card-nexus:hover { transform: scale(1.02); border-color: #06b6d4; }
    .ec-banner { height: 100px; position: relative; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #fff; }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
    const TICKET_PRICES = @json($ticketPrices ?? ['regular' => 2500, 'vip' => 10000, 'vvip' => 25000]);

    function switchS(id, btn) {
        $('.s-panel').removeClass('active');
        $('.s-tab').removeClass('active');
        $('#' + id).addClass('active');
        $(btn).addClass('active');
    }

    function buyTicket(name) {
        Swal.fire({
            title: 'Select Ticket Type',
            html: `
                <p class="text-muted mb-3">Event: <strong>${name}</strong></p>
                <select id="swal-type" class="swal2-input">
                    <option value="regular">Regular – ₦${Number(TICKET_PRICES.regular || 2500).toLocaleString()}</option>
                    <option value="vip">VIP – ₦${Number(TICKET_PRICES.vip || 10000).toLocaleString()}</option>
                    <option value="vvip">VVIP – ₦${Number(TICKET_PRICES.vvip || 25000).toLocaleString()}</option>
                </select>
                <input id="swal-qty" class="swal2-input" type="number" min="1" max="10" value="1" placeholder="Quantity">
                <input id="swal-name" class="swal2-input" placeholder="Attendee Full Name">
                <input id="swal-email" class="swal2-input" type="email" placeholder="Attendee Email">
            `,
            showCancelButton: true,
            confirmButtonColor: '#06b6d4',
            confirmButtonText: 'Buy Ticket',
            preConfirm: () => {
                return {
                    event_name: name,
                    event_date: new Date(Date.now() + 86400000 * 10).toISOString().split('T')[0],
                    ticket_type: document.getElementById('swal-type').value,
                    quantity: document.getElementById('swal-qty').value,
                    attendee_name: document.getElementById('swal-name').value,
                    attendee_email: document.getElementById('swal-email').value,
                };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            const payload = { ...result.value, _token: '{{ csrf_token() }}' };
            Swal.fire({ title: 'Processing…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.post('{{ route("services.ticketing.buy") }}', payload)
                .done(res => {
                    if (res.status) {
                        $('#evtName').text(name);
                        $('#panel-container').hide();
                        $('#ticketResult').html(`
                            <div class="result-card-nexus animate__animated animate__fadeIn">
                                <div class="p-4 text-center">
                                    <div class="sh-icon mx-auto mb-3" style="background:rgba(6,182,212,.1);color:#06b6d4"><i class="fa-solid fa-qrcode"></i></div>
                                    <h4>Tickets Confirmed!</h4>
                                    <p class="text-muted small">Event: <strong class="text-white">${name}</strong></p>
                                    <p class="text-muted small">Ref: <strong class="text-white">${res.ticket_ref}</strong> · Total: ₦${res.total}</p>
                                    <p class="small text-success mb-3">Wallet Balance: ₦${res.balance}</p>
                                    <div class="d-grid gap-2">
                                        <a href="${res.pdf_url}" target="_blank" class="btn btn-primary w-100 mb-2"><i class="fa-solid fa-file-pdf mr-2"></i> Download E-Tickets</a>
                                        <button class="btn btn-outline w-100" onclick="location.reload()">Back to Vault</button>
                                    </div>
                                </div>
                            </div>`).show();
                        Swal.close();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                })
                .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Network error', 'error'));
        });
    }

    function searchTransport(e) {
        e.preventDefault();
        Swal.fire('Coming Soon', 'Transport booking integration is in progress.', 'info');
    }
</script>
@endpush

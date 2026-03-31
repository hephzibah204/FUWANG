@extends('layouts.postoffice')

@section('title', 'Book a Shipment')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="font-weight-bold mb-1">Book New <span style="color:var(--po-primary)">Shipment</span></h3>
        <p class="text-white-50 small">Enter package details and recipient information to get started.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form id="bookForm">
            @csrf
            <!-- Sender & Recipient -->
            <div class="glass-card p-4 mb-4">
                <h5 class="font-weight-bold mb-4 d-flex align-items-center">
                    <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2" style="width:24px; height:24px; font-size:12px;">1</span>
                    Contact Information
                </h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Sender Name</label>
                        <input type="text" name="sender_name" class="form-control tracking-input py-2" value="{{ auth()->user()->fullname }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Sender Address</label>
                        <input type="text" name="sender_address" class="form-control tracking-input py-2" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Recipient Name</label>
                        <input type="text" name="recipient_name" class="form-control tracking-input py-2" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Recipient Address</label>
                        <input type="text" name="recipient_address" class="form-control tracking-input py-2" required>
                    </div>
                </div>
            </div>

            <!-- Package Details -->
            <div class="glass-card p-4 mb-4">
                <h5 class="font-weight-bold mb-4 d-flex align-items-center">
                    <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2" style="width:24px; height:24px; font-size:12px;">2</span>
                    Package & Delivery
                </h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">Weight (kg)</label>
                        <input type="number" name="weight" id="weight" step="0.1" class="form-control tracking-input py-2" value="1.0" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="text-white-50 small mb-2">Item Description</label>
                        <input type="text" name="description" class="form-control tracking-input py-2" placeholder="e.g., Electronics, Documents..." required>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <label class="text-white-50 small mb-2">Delivery Speed</label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="delivery-option p-3 rounded-lg border border-glass cursor-pointer text-center speed-card active" data-speed="standard">
                                    <div class="font-weight-bold">Standard</div>
                                    <small class="d-block text-white-50">3-5 Days</small>
                                    <input type="radio" name="delivery_type" value="standard" class="d-none" checked>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="delivery-option p-3 rounded-lg border border-glass cursor-pointer text-center speed-card" data-speed="express">
                                    <div class="font-weight-bold text-info">Express</div>
                                    <small class="d-block text-white-50">1-2 Days</small>
                                    <input type="radio" name="delivery_type" value="express" class="d-none">
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="delivery-option p-3 rounded-lg border border-glass cursor-pointer text-center speed-card" data-speed="overnight">
                                    <div class="font-weight-bold text-warning">Overnight</div>
                                    <small class="d-block text-white-50">Next Day</small>
                                    <input type="radio" name="delivery_type" value="overnight" class="d-none">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary & Submit -->
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <span class="text-white-50 mr-2">Total Cost:</span>
                    <h3 class="d-inline font-weight-bold mb-0" style="color:var(--po-primary)">₦<span id="displayTotal">0.00</span></h3>
                </div>
                <button type="submit" class="btn btn-po-primary btn-lg px-5 shadow-lg" id="submitBtn">
                    Confirm & Book <i class="fa fa-arrow-right ml-2"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="glass-card p-4 sticky-top" style="top: 100px;">
            <h5 class="font-weight-bold mb-3">Cost Breakdown</h5>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-white-50">Base Fare</span>
                <span>₦{{ number_format($logisticsPricing['base'], 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-white-50">Weight Surcharge</span>
                <span id="weightCost">₦0.00</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-white-50">Delivery Priority</span>
                <span id="speedCost">x1.00</span>
            </div>
            <hr class="border-white-10">
            <div class="d-flex justify-content-between">
                <span class="font-weight-bold">Grand Total</span>
                <h5 class="font-weight-bold mb-0 text-primary">₦<span id="breakdownTotal">0.00</span></h5>
            </div>
            
            <div class="mt-4 p-3 rounded bg-black-20 small text-white-50">
                <i class="fa fa-info-circle mr-2"></i> Payment will be deducted from your wallet balance instantly.
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .speed-card { transition: 0.2s; }
    .speed-card:hover { border-color: rgba(255,255,255,0.3) !important; }
    .speed-card.active { 
        background: rgba(245, 158, 11, 0.1) !important; 
        border-color: var(--po-primary) !important;
        color: var(--po-primary) !important;
    }
</style>

@push('scripts')
<script>
    const pricing = @json($logisticsPricing);
    
    function calculate() {
        const weight = parseFloat($('#weight').val()) || 0;
        const speed = $('.speed-card.active').data('speed');
        
        const weightSurcharge = Math.ceil(weight) * pricing.weight_multiplier;
        const baseAndWeight = pricing.base + weightSurcharge;
        
        let multiplier = 1;
        if (speed === 'express') multiplier = pricing.express_multiplier;
        if (speed === 'overnight') multiplier = pricing.overnight_multiplier;
        
        const total = baseAndWeight * multiplier;
        
        $('#weightCost').text('₦' + weightSurcharge.toLocaleString());
        $('#speedCost').text('x' + multiplier.toFixed(2));
        $('#displayTotal, #breakdownTotal').text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $('.speed-card').click(function() {
        $('.speed-card').removeClass('active');
        $(this).addClass('active');
        $(this).find('input').prop('checked', true);
        calculate();
    });

    $('#weight').on('input', calculate);
    calculate();

    $('#bookForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Confirm Booking',
            text: '₦' + $('#displayTotal').text() + ' will be deducted from your wallet. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Yes, Book Now'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#submitBtn');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                
                $.post("{{ route('user.logistics.store') }}", $(this).serialize(), function(res) {
                    btn.prop('disabled', false).html('Confirm & Book <i class="fa fa-arrow-right ml-2"></i>');
                    if (res.status) {
                        Swal.fire({
                            title: 'Success!',
                            html: `Shipment booked. Tracking ID: <b class="text-primary">${res.tracking_id}</b>`,
                            icon: 'success',
                            confirmButtonColor: '#f59e0b'
                        }).then(() => {
                            window.location.href = "{{ route('user.logistics.dashboard') }}";
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }).fail(() => {
                    btn.prop('disabled', false).html('Confirm & Book <i class="fa fa-arrow-right ml-2"></i>');
                    Swal.fire('Error', 'Something went wrong', 'error');
                });
            }
        });
    });
</script>
@endpush
@endsection

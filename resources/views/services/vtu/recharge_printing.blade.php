@extends('layouts.nexus')

@section('title', 'Recharge Card Printing | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white font-weight-bold">Recharge Card Printing</h1>
            <p class="text-muted">Generate airtime PINs for physical resale.</p>
        </div>
        <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Hub
        </a>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm" style="background: #1e293b;">
                <div class="card-body p-4">
                    <form id="rechargePrintForm">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label class="text-white-50 small mb-2">PIN Type</label>
                            <div class="btn-group btn-block" role="group">
                                <button type="button" class="btn btn-outline-primary active" id="btnTypeAirtime">Airtime PIN</button>
                                <button type="button" class="btn btn-outline-primary" id="btnTypeData">Data PIN</button>
                            </div>
                            <input type="hidden" name="pin_type" id="pinType" value="airtime">
                        </div>

                        <div class="form-group mb-4">
                            <label class="text-white-50 small mb-2">Select Network</label>
                            <div class="row px-2">
                                <label class="col-3 p-1 m-0 network-label">
                                    <input type="radio" name="network" value="01" class="d-none" required checked>
                                    <div class="network-card text-center p-2 rounded" style="border: 1px solid rgba(255,255,255,0.1); cursor: pointer; transition: 0.3s;">
                                        <img src="{{ asset('vtusite/images/mtn.png') }}" class="img-fluid rounded mb-1" style="height: 30px;">
                                        <div class="x-small text-white">MTN</div>
                                    </div>
                                </label>
                                <label class="col-3 p-1 m-0 network-label">
                                    <input type="radio" name="network" value="04" class="d-none">
                                    <div class="network-card text-center p-2 rounded" style="border: 1px solid rgba(255,255,255,0.1); cursor: pointer; transition: 0.3s;">
                                        <img src="{{ asset('vtusite/images/airtel.png') }}" class="img-fluid rounded mb-1" style="height: 30px;">
                                        <div class="x-small text-white">Airtel</div>
                                    </div>
                                </label>
                                <label class="col-3 p-1 m-0 network-label">
                                    <input type="radio" name="network" value="02" class="d-none">
                                    <div class="network-card text-center p-2 rounded" style="border: 1px solid rgba(255,255,255,0.1); cursor: pointer; transition: 0.3s;">
                                        <img src="{{ asset('vtusite/images/glo.png') }}" class="img-fluid rounded mb-1" style="height: 30px;">
                                        <div class="x-small text-white">Glo</div>
                                    </div>
                                </label>
                                <label class="col-3 p-1 m-0 network-label">
                                    <input type="radio" name="network" value="03" class="d-none">
                                    <div class="network-card text-center p-2 rounded" style="border: 1px solid rgba(255,255,255,0.1); cursor: pointer; transition: 0.3s;">
                                        <img src="{{ asset('vtusite/images/9mobile.png') }}" class="img-fluid rounded mb-1" style="height: 30px;">
                                        <div class="x-small text-white">9mobile</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-4" id="airtimeGroup">
                            <label class="text-white-50 small mb-2">Denomination (₦)</label>
                            <select name="amount" class="form-control text-white" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);" required>
                                <option value="" disabled selected>Select Value</option>
                                <option value="100">₦100</option>
                                <option value="200">₦200</option>
                                <option value="500">₦500</option>
                            </select>
                        </div>

                        <div class="form-group mb-4 d-none" id="dataGroup">
                            <label class="text-white-50 small mb-2">Data Plan</label>
                            <select name="data_plan" id="dataPlanSelect" class="form-control text-white" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                                <option value="" disabled selected>Loading plans...</option>
                            </select>
                            <input type="hidden" name="data_plan_price" id="dataPlanPrice">
                        </div>

                        <div class="form-group mb-4">
                            <label class="text-white-50 small mb-2">Quantity (1 - 100)</label>
                            <input type="number" name="quantity" class="form-control text-white font-weight-bold" min="1" max="100" value="1" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                            <span class="text-white-50 small">Total Cost</span>
                            <span class="text-white font-weight-bold h5 m-0" id="totalCostDisplay">₦0.00</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold" id="generateBtn">
                            <i class="fa-solid fa-print mr-2"></i> Generate PINs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100" style="background: #1e293b;">
                <div class="card-header border-0 bg-transparent pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white m-0"><i class="fa-solid fa-ticket mr-2 text-primary"></i> Generated PINs</h5>
                    <button class="btn btn-sm btn-outline-success d-none" id="printBtn" onclick="printPins()">
                        <i class="fa-solid fa-print mr-1"></i> Print
                    </button>
                </div>
                <div class="card-body">
                    <div id="pinsContainer" class="row">
                        <div class="col-12 text-center py-5 text-muted opacity-50">
                            <i class="fa-solid fa-receipt mb-3 d-block" style="font-size: 3rem;"></i>
                            <p>Your generated recharge PINs will appear here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .network-label input:checked + .network-card {
        border-color: #3b82f6 !important;
        background: rgba(59, 130, 246, 0.2);
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        #pinsContainer, #pinsContainer * {
            visibility: visible;
        }
        #pinsContainer {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .pin-card {
            border: 1px solid #000 !important;
            break-inside: avoid;
            page-break-inside: avoid;
            color: #000 !important;
            background: #fff !important;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let databundlePlans = null;

        // Fetch Data Plans initially
        $.get('{{ route("services.vtu.recharge_printing.data_plans") }}', function(res) {
            if(res.status && res.data && res.data.MOBILE_NETWORK) {
                databundlePlans = res.data.MOBILE_NETWORK;
                updateDataPlans();
            } else {
                $('#dataPlanSelect').html('<option disabled>Failed to load plans</option>');
            }
        });

        // Toggle Type
        $('#btnTypeAirtime').click(function() {
            $(this).addClass('active');
            $('#btnTypeData').removeClass('active');
            $('#pinType').val('airtime');
            $('#airtimeGroup').removeClass('d-none');
            $('#dataGroup').addClass('d-none');
            $('select[name="amount"]').prop('required', true);
            $('#dataPlanSelect').prop('required', false);
            calculateTotal();
        });

        $('#btnTypeData').click(function() {
            $(this).addClass('active');
            $('#btnTypeAirtime').removeClass('active');
            $('#pinType').val('data');
            $('#airtimeGroup').addClass('d-none');
            $('#dataGroup').removeClass('d-none');
            $('select[name="amount"]').prop('required', false);
            $('#dataPlanSelect').prop('required', true);
            updateDataPlans();
            calculateTotal();
        });

        // Network Change
        $('input[name="network"]').change(function() {
            if($('#pinType').val() === 'data') {
                updateDataPlans();
            }
        });

        function updateDataPlans() {
            if(!databundlePlans) return;
            
            let netCode = $('input[name="network"]:checked').val();
            let netName = '';
            if(netCode === '01') netName = 'MTN';
            if(netCode === '02') netName = 'Glo';
            if(netCode === '03') netName = '9mobile';
            if(netCode === '04') netName = 'Airtel';

            let options = '<option value="" disabled selected>Select Data Plan</option>';
            
            if(databundlePlans[netName] && databundlePlans[netName][0] && databundlePlans[netName][0].PRODUCT) {
                let products = databundlePlans[netName][0].PRODUCT;
                products.forEach(p => {
                    options += `<option value="${p.PRODUCT_ID}" data-price="${p.PRODUCT_AMOUNT}">${p.PRODUCT_NAME} - ₦${p.PRODUCT_AMOUNT}</option>`;
                });
            } else {
                options = '<option value="" disabled selected>No plans available for this network</option>';
            }
            
            $('#dataPlanSelect').html(options);
        }

        $('#dataPlanSelect').change(function() {
            let price = $(this).find(':selected').data('price');
            $('#dataPlanPrice').val(price);
            calculateTotal();
        });

        function calculateTotal() {
            let type = $('#pinType').val();
            let amount = 0;
            if(type === 'airtime') {
                amount = $('select[name="amount"]').val() || 0;
            } else {
                amount = $('#dataPlanPrice').val() || 0;
            }
            let qty = $('input[name="quantity"]').val() || 0;
            $('#totalCostDisplay').text('₦' + (amount * qty).toLocaleString('en-US', {minimumFractionDigits: 2}));
        }

        $('select[name="amount"], input[name="quantity"]').on('change keyup', calculateTotal);

        $('#rechargePrintForm').submit(function(e) {
            e.preventDefault();
            let btn = $('#generateBtn');
            let originalHtml = btn.html();
            
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Generating...');

            $.ajax({
                url: '{{ route("services.vtu.recharge_printing.generate") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if(res.status) {
                        toastr.success(res.message);
                        if(res.async) {
                            btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing Order...');
                            pollOrder(res.order_id, btn, originalHtml);
                        } else {
                            btn.prop('disabled', false).html(originalHtml);
                            displayPins(res.pins, 'airtime');
                            $('#printBtn').removeClass('d-none');
                        }
                    } else {
                        btn.prop('disabled', false).html(originalHtml);
                        toastr.error(res.message);
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false).html(originalHtml);
                    let msg = err.responseJSON?.message || 'An error occurred during generation.';
                    toastr.error(msg);
                }
            });
        });

        function pollOrder(orderId, btn, originalHtml, attempts = 0) {
            if(attempts > 12) { // Poll for 1 minute (5s * 12)
                btn.prop('disabled', false).html(originalHtml);
                toastr.warning('Order is taking too long. Check transaction history later.');
                return;
            }

            setTimeout(function() {
                $.get('{{ route("services.vtu.recharge_printing.query") }}', { order_id: orderId }, function(res) {
                    if(res.status && res.pins) {
                        btn.prop('disabled', false).html(originalHtml);
                        toastr.success('Data PINs are ready!');
                        displayPins(res.pins, 'data');
                        $('#printBtn').removeClass('d-none');
                    } else {
                        pollOrder(orderId, btn, originalHtml, attempts + 1);
                    }
                }).fail(function() {
                    pollOrder(orderId, btn, originalHtml, attempts + 1);
                });
            }, 5000);
        }

        function displayPins(pins, type) {
            let html = '';
            pins.forEach(function(pin) {
                let displayAmount = type === 'data' ? pin.productname : `₦${pin.amount}`;
                html += `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="pin-card p-3 rounded" style="background: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-uppercase">${pin.mobilenetwork} ${displayAmount}</span>
                                <span class="x-small text-muted">S/N: ${pin.sno}</span>
                            </div>
                            <div class="text-center py-2 mb-2" style="background: rgba(0,0,0,0.3); border-radius: 4px;">
                                <h5 class="m-0 font-monospace font-weight-bold text-primary" style="letter-spacing: 2px;">${pin.pin}</h5>
                            </div>
                            <div class="text-center x-small text-muted">
                                *131*PIN# OR *311*PIN#
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#pinsContainer').html(html);
        }
    });

    function printPins() {
        window.print();
    }
</script>
@endpush

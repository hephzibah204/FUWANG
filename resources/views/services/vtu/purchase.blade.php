@extends('layouts.nexus')

@section('title', $title . ' | ' . config('app.name'))

@section('content')
@php
    $networkService = in_array($type, ['vtu_airtime', 'vtu_data', 'vtu_airtime_to_cash', 'vtu_data_card', 'vtu_recharge_card']);
    $productService = in_array($type, ['vtu_data', 'vtu_cable_tv', 'vtu_internet', 'vtu_epin', 'vtu_data_card', 'vtu_recharge_card']);
    $verificationService = in_array($type, ['vtu_cable_tv', 'vtu_electricity']);
    $printing = in_array($type, ['vtu_data_card', 'vtu_recharge_card']);
@endphp
<div class="service-page fade-in vtu-purchase" data-vtu-purchase
     data-type="{{ $type }}" data-action="{{ route($purchaseRoute) }}"
     data-providers="{{ route('services.vtu.providers', ['serviceType' => $type]) }}"
     data-catalog="{{ route('services.vtu.catalog', ['serviceType' => $type]) }}"
     data-quote="{{ route('services.vtu.quote', ['serviceType' => $type]) }}"
     data-verify="{{ $verificationService ? route($type === 'vtu_electricity' ? 'services.vtu.electricity.validate' : 'services.vtu.cable.validate') : '' }}"
     data-status="{{ route('services.vtu.recharge_printing.query') }}"
     data-history="{{ url('/history') }}">
    <x-nexus.service-header :title="$title" subtitle="Choose your service, review the total, and pay securely from your wallet." icon="fa-solid fa-bolt" icon-class="vtu-bg" />
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="panel-card p-4">
                <x-nexus.flash-message />
                <div class="alert alert-info" data-state role="status" aria-live="polite">Loading available bands…</div>
                <form data-purchase-form action="{{ route($purchaseRoute) }}" method="POST">
                    @csrf
                    <input type="hidden" name="request_id" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                    <input type="hidden" name="validation_token" value="">
                    @if($printing)
                        <input type="hidden" name="pin_type" value="{{ $type === 'vtu_data_card' ? 'data' : 'airtime' }}">
                        <input type="hidden" name="card_name" value="{{ auth()->user()->username ?: config('app.name') }}">
                    @endif
                    <fieldset data-fields disabled>
                        <div class="form-group mb-4">
                            <input type="hidden" id="vtu-band" name="provider_id" value="">
                            <p class="small text-muted">Secure payment from your wallet.</p>
                        </div>
                        @if($networkService)
                            <div class="form-group mb-4">
                                <label for="vtu-network">Mobile network</label>
                                <select id="vtu-network" name="network" class="form-control" required>
                                    <option value="">Choose a network</option>
                                    @foreach(['MTN', 'GLO', 'AIRTEL', '9MOBILE'] as $network)
                                        <option value="{{ $network }}">{{ $network }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if(in_array($type, ['vtu_cable_tv', 'vtu_electricity', 'vtu_internet', 'vtu_epin', 'vtu_betting']))
                            <div class="form-group mb-4">
                                <label for="vtu-service">{{ $type === 'vtu_epin' ? 'Exam' : 'Service' }}</label>
                                <select id="vtu-service" name="serviceID" class="form-control" required>
                                    <option value="">Choose a service</option>
                                    @if($type === 'vtu_cable_tv')
                                        @foreach(['gotv' => 'GOtv', 'dstv' => 'DStv', 'startimes' => 'StarTimes'] as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    @elseif($type === 'vtu_electricity')
                                        @foreach(\App\Services\Datasub247Service::ELECTRICITY_PROVIDERS as $code => $disco)
                                            <option value="{{ $code }}">{{ $disco['label'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif
                        @if($type === 'vtu_electricity')
                            <div class="form-group mb-4">
                                <label for="vtu-meter-type">Meter type</label>
                                <select id="vtu-meter-type" name="variation_code" class="form-control" required>
                                    <option value="prepaid">Prepaid</option><option value="postpaid">Postpaid</option>
                                </select>
                            </div>
                        @endif
                        @if($productService)
                            @if($type === 'vtu_data')
                                <div class="row">
                                    <div class="col-sm-7 form-group mb-3"><label for="vtu-search">Find a bundle</label><input id="vtu-search" type="search" class="form-control" placeholder="Search size, plan or validity"></div>
                                    <div class="col-sm-5 form-group mb-3"><label for="vtu-budget">Maximum price (₦)</label><input id="vtu-budget" type="number" class="form-control" min="0" placeholder="Any price"></div>
                                </div>
                            @endif
                            <div class="form-group mb-4">
                                <label for="vtu-plan">{{ $printing ? 'PIN denomination / data plan' : 'Package / data bundle' }}</label>
                                <select id="vtu-plan" name="{{ $type === 'vtu_data' ? 'plan_id' : ($printing ? 'plan_type' : 'variation_code') }}" class="form-control" required disabled><option value="">Choose a network or service first</option></select>
                                <small class="text-muted" data-plan-help>Prices are set by the service catalogue.</small>
                            </div>
                        @endif
                        @if($verificationService)
                            <div class="form-group mb-4">
                                <label for="vtu-customer">{{ $type === 'vtu_electricity' ? 'Meter number' : 'Smartcard / IUC number' }}</label>
                                <input id="vtu-customer" name="{{ $type === 'vtu_electricity' ? 'meter_number' : 'smart_card_number' }}" type="text" inputmode="numeric" class="form-control" required maxlength="30">
                                <button type="button" class="btn btn-outline-primary mt-2" data-verify-customer>Verify customer</button>
                                <div data-customer-result class="mt-2" aria-live="polite"></div>
                            </div>
                        @elseif(in_array($type, ['vtu_internet', 'vtu_betting']))
                            <div class="form-group mb-4"><label for="vtu-customer">Customer / account ID</label><input id="vtu-customer" name="customer_id" class="form-control" required maxlength="100"></div>
                        @endif
                        @if($type === 'vtu_epin' || $printing)
                            <div class="form-group mb-4"><label for="vtu-quantity">Quantity</label><input id="vtu-quantity" name="quantity" type="number" min="1" max="{{ $printing ? 100 : 50 }}" value="1" class="form-control" required></div>
                        @endif
                        @if($type === 'vtu_bulksms')
                            <div class="form-group mb-4"><label for="vtu-sender">Sender name</label><input id="vtu-sender" name="sender" maxlength="11" class="form-control" required></div>
                            <div class="form-group mb-4"><label for="vtu-recipients">Recipients</label><textarea id="vtu-recipients" name="recipients" rows="3" class="form-control" placeholder="Separate Nigerian phone numbers with commas or new lines" required></textarea></div>
                            <div class="form-group mb-4"><label for="vtu-message">Message</label><textarea id="vtu-message" name="message" rows="4" maxlength="5000" class="form-control" required></textarea><small class="text-muted">Unicode text and special characters can use additional SMS pages. Review the quoted total before sending.</small></div>
                            <input name="amount" type="hidden" value="4">
                        @else
                            @if(!$printing)
                                <div class="form-group mb-4"><label for="vtu-phone">Recipient phone number</label><input id="vtu-phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="0[0-9]{10}" maxlength="11" placeholder="08123456789" class="form-control" required><small class="text-muted">Enter 11 digits, starting with 0.</small></div>
                            @endif
                            <div class="form-group mb-4">
                                <label for="vtu-amount">{{ $productService ? 'Package price' : 'Amount' }} (₦)</label>
                                <input id="vtu-amount" name="amount" type="number" step="0.01" min="{{ $productService ? 1 : ($type === 'vtu_electricity' ? 500 : ($type === 'vtu_airtime_to_cash' ? 200 : 50)) }}" class="form-control" {{ $productService ? 'readonly' : '' }} required placeholder="{{ $productService ? 'Select a package to see its price' : 'Enter amount' }}">
                                @if($productService)<small class="text-muted">The total below includes quantity, discounts and fees.</small>@endif
                                @if($type === 'vtu_airtime')
                                    <div class="d-flex flex-wrap mt-2" style="gap:8px">@foreach([100, 200, 500, 1000, 2000] as $value)<button type="button" class="btn btn-sm btn-outline-primary" data-amount="{{ $value }}">₦{{ number_format($value) }}</button>@endforeach</div>
                                @endif
                            </div>
                        @endif
                        @if($type === 'vtu_airtime_to_cash')
                            <div class="form-group mb-3"><label for="vtu-bank">Bank code</label><input id="vtu-bank" name="bank_code" class="form-control" required maxlength="20"></div>
                            <div class="form-group mb-3"><label for="vtu-account">Account number</label><input id="vtu-account" name="account_number" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" class="form-control" required></div>
                            <div class="form-group mb-4"><label for="vtu-account-name">Account name</label><input id="vtu-account-name" name="account_name" maxlength="120" class="form-control"></div>
                        @endif
                        <button class="btn btn-primary btn-lg w-100" type="submit" data-submit>Review purchase</button>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4" aria-live="polite">
                <h2 class="h5">Purchase summary</h2>
                <p data-selection class="text-muted">Choose a service to get started.</p>
                <dl class="row mb-0">
                    <dt class="col-7">Amount</dt><dd class="col-5 text-right" data-summary-amount>—</dd>
                    <dt class="col-7">Discount</dt><dd class="col-5 text-right" data-summary-discount>—</dd>
                    <dt class="col-7">Service fee</dt><dd class="col-5 text-right" data-summary-fee>—</dd>
                    <dt class="col-7">{{ $type === 'vtu_airtime_to_cash' ? 'Expected credit' : 'Total debit' }}</dt><dd class="col-5 text-right font-weight-bold" data-summary-total>—</dd>
                </dl>
                <small class="text-muted" data-quote-note>Complete the form to receive an exact quote.</small>
            </div>
            <div class="panel-card p-4 mb-4" data-result hidden role="status" aria-live="polite"></div>
            <div class="panel-card p-4 mb-4">
                <a class="btn btn-outline-primary w-100 mb-2" href="{{ route('services.vtu.hub') }}">Back to VTU hub</a>
                @if($printing)
                    <a class="btn btn-outline-primary w-100" href="{{ route('services.vtu.recharge_printing') }}?type={{ $type === 'vtu_data_card' ? 'airtime' : 'data' }}">Switch to {{ $type === 'vtu_data_card' ? 'airtime' : 'data' }} PINs</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/vtu-purchase.js') }}" defer></script>
@endpush

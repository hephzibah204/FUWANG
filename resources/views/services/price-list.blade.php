@extends('layouts.nexus')

@section('title', 'Service Price List | Fuwa.NG - Transparent & Competitive Pricing')
@section('meta_description', 'View the complete price list for all Fuwa.NG services, including NIN verification, BVN validation, VTU, and more. Transparent, pay-as-you-go pricing for your business.')
@section('meta_keywords', 'Fuwa.NG pricing, NIN verification price, BVN validation price, VTU prices Nigeria, identity verification costs')
@section('canonical', route('services.price-list'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1 text-white">Service Price List</h1>
            <p class="text-muted">Dynamic pricing for all our identity and utility services.</p>
        </div>
    </div>

    <div class="dash-two-col">
        <!-- Main Price Tables -->
        <div class="dash-left-col">
            @php
                $serviceNames = [
                    'nin' => 'NIN Verification',
                    'nin_verification' => 'NIN Verification',
                    'bvn' => 'BVN Verification',
                    'bvn_verification' => 'BVN Verification',
                    'address_verification' => 'Address Verification',
                    'drivers_license' => 'Drivers License Verify',
                    'biometric_verification' => 'Biometric Verification',
                    'cac_verification' => 'CAC Business Verify',
                    'tin_verification' => 'TIN Verification',
                    'passport_verification' => 'Passport Verification',
                    'voters_card_verification' => 'Voters Card Verify',
                    'vtu_airtime' => 'VTU Airtime',
                    'vtu_data' => 'VTU Data Bundle',
                    'education_waec' => 'WAEC Result Checker',
                    'education_waec_registration' => 'WAEC Registration',
                    'insurance_motor' => 'Motor Insurance',
                    'payment' => 'Payment Processing'
                ];
            @endphp

            @foreach($categories as $categoryName => $types)
                @php 
                    $hasContent = false;
                    foreach($types as $t) { if($customPrices->has($t)) $hasContent = true; }
                @endphp

                @if($hasContent)
                <div class="panel-card mb-4">
                    <div class="panel-hdr">
                        <h3>{{ $categoryName }}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Service Name</th>
                                    <th>Provider</th>
                                    <th>Verification Type</th>
                                    <th class="text-right pr-4">Cost (₦)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                    @if($customPrices->has($type))
                                        @foreach($customPrices[$type] as $provider)
                                            @php
                                                $providerTypes = $provider->verificationTypes ?? collect();
                                            @endphp
                                            @if($providerTypes->count() > 0)
                                                @foreach($providerTypes as $t)
                                                    <tr>
                                                        <td class="align-middle font-weight-bold text-white">{{ $serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type)) }}</td>
                                                        <td class="align-middle text-muted small">{{ $provider->name }}</td>
                                                        <td class="align-middle text-muted small">{{ $t->label }}</td>
                                                        <td class="align-middle text-right pr-4 font-weight-bold text-primary">
                                                            ₦{{ number_format((float) $t->price, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td class="align-middle font-weight-bold text-white">{{ $serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type)) }}</td>
                                                    <td class="align-middle text-muted small">{{ $provider->name }}</td>
                                                    <td class="align-middle text-muted small">Standard</td>
                                                    <td class="align-middle text-right pr-4 font-weight-bold text-primary">
                                                        ₦{{ number_format((float) $provider->price, 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach

            {{-- Legacy Fallbacks Section --}}
            @if((!$customPrices->has('nin') && isset($legacyPrices->nin_by_nin_price)) || (!$customPrices->has('bvn') && isset($legacyPrices->bvn_by_bvn)))
            <div class="panel-card mt-4">
                <div class="panel-hdr text-muted">
                    <h3>Legacy Service Fallbacks</h3>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <tbody>
                            @if(!$customPrices->has('nin') && isset($legacyPrices->nin_by_nin_price))
                                <tr>
                                    <td class="align-middle font-weight-bold text-white">NIN Verification (Legacy)</td>
                                    <td class="align-middle text-muted small">Global System</td>
                                    <td class="align-middle text-right pr-4 font-weight-bold">₦{{ number_format($legacyPrices->nin_by_nin_price, 2) }}</td>
                                </tr>
                            @endif
                            @if(!$customPrices->has('bvn') && isset($legacyPrices->bvn_by_bvn))
                                <tr>
                                    <td class="align-middle font-weight-bold text-white">BVN Verification (Legacy)</td>
                                    <td class="align-middle text-muted small">Global System</td>
                                    <td class="align-middle text-right pr-4 font-weight-bold">₦{{ number_format($legacyPrices->bvn_by_bvn, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Side: Service Info -->
        <div class="panel-card">
            <div class="panel-hdr">
                <h3>Pricing Notes</h3>
            </div>
            <div class="p-4">
                <div class="kyc-banner mb-4">
                    <i class="fa-solid fa-circle-info"></i>
                    <div class="kyc-text">
                        <strong>Real-time Updates</strong>
                        <p>Prices are automatically updated whenever the system administrator changes provider configurations.</p>
                    </div>
                </div>

                <div class="ref-card">
                    <p class="small text-muted m-0">Need higher limits?</p>
                    <strong>Contact our Sales team</strong>
                    <div class="mt-2">
                        <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-primary w-100">Open Ticket</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Fuwa.NG Service Price List",
    "description": "Transparent, pay-as-you-go pricing for all Fuwa.NG services.",
    "numberOfItems": {{ $customPrices->flatten()->count() + ($legacyPrices ? 2 : 0) }},
    "itemListElement": [
        @php $counter = 0; @endphp
        @foreach($categories as $categoryName => $types)
            @foreach($types as $type)
                @if($customPrices->has($type))
                    @foreach($customPrices[$type] as $provider)
                        @php
                            $providerTypes = $provider->verificationTypes ?? collect();
                        @endphp
                        @if($providerTypes->count() > 0)
                            @foreach($providerTypes as $t)
                                @if($counter > 0),@endif
                                {
                                    "@type": "Product",
                                    "name": "{{ $serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type)) }} - {{ $t->label }}",
                                    "description": "{{ $serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type)) }} by {{ $provider->name }} ({{ $t->label }})",
                                    "brand": {
                                        "@type": "Brand",
                                        "name": "{{ $provider->name }}"
                                    },
                                    "offers": {
                                        "@type": "Offer",
                                        "price": "{{ number_format((float) $t->price, 2, '.', '') }}",
                                        "priceCurrency": "NGN"
                                    }
                                }
                                @php $counter++; @endphp
                            @endforeach
                        @else
                            @if($counter > 0),@endif
                            {
                                "@type": "Product",
                                "name": "{{ $serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type)) }}",
                                "description": "{{ $serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type)) }} by {{ $provider->name }}",
                                "brand": {
                                    "@type": "Brand",
                                    "name": "{{ $provider->name }}"
                                },
                                "offers": {
                                    "@type": "Offer",
                                    "price": "{{ number_format((float) $provider->price, 2, '.', '') }}",
                                    "priceCurrency": "NGN"
                                }
                            }
                            @php $counter++; @endphp
                        @endif
                    @endforeach
                @endif
            @endforeach
        @if((!$customPrices->has('nin') && isset($legacyPrices->nin_by_nin_price)))
        @if($counter > 0),@endif
        {
            "@type": "Product",
            "name": "NIN Verification (Legacy)",
            "offers": {
                "@type": "Offer",
                "price": "{{ number_format($legacyPrices->nin_by_nin_price, 2, '.', '') }}",
                "priceCurrency": "NGN"
            }
        }
        @php $counter++; @endphp
        @endif
        @if((!$customPrices->has('bvn') && isset($legacyPrices->bvn_by_bvn)))
        @if($counter > 0),@endif
        {
            "@type": "Product",
            "name": "BVN Verification (Legacy)",
            "offers": {
                "@type": "Offer",
                "price": "{{ number_format($legacyPrices->bvn_by_bvn, 2, '.', '') }}",
                "priceCurrency": "NGN"
            }
        }
        @endif
    ]
}
</script>
@endpush

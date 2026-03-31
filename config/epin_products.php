<?php

return [
    'products' => [
        'waec_result_checker' => [
            'label' => 'WAEC Result Checker',
            'service_id' => 'waec',
            'variation_code' => 'waecdirect',
            'amount' => 950,
            'quantity' => 1,
            'order_type' => 'WAEC Result Checker',
            'tx_prefix' => 'WAEC',
            'provider_service_types' => ['vtu_epin', 'education_waec'],
        ],
        'waec_registration_pin' => [
            'label' => 'WAEC Registration PIN',
            'service_id' => 'waec-registration',
            'variation_code' => 'waec-registration',
            'amount' => 18500,
            'quantity' => 1,
            'order_type' => 'WAEC Registration PIN',
            'tx_prefix' => 'WAECREG',
            'provider_service_types' => ['vtu_epin', 'education_waec_registration'],
        ],
        'neco_result_checker' => [
            'label' => 'NECO Result Checker',
            'service_id' => 'neco',
            'variation_code' => 'neco-direct',
            'amount' => 950,
            'quantity' => 1,
            'order_type' => 'NECO Result Checker',
            'tx_prefix' => 'NECO',
            'provider_service_types' => ['vtu_epin', 'education_neco'],
        ],
        'nabteb_result_checker' => [
            'label' => 'NABTEB Result Checker',
            'service_id' => 'nabteb',
            'variation_code' => 'nabteb-direct',
            'amount' => 950,
            'quantity' => 1,
            'order_type' => 'NABTEB Result Checker',
            'tx_prefix' => 'NABTEB',
            'provider_service_types' => ['vtu_epin', 'education_nabteb'],
        ],
        'jamb_profile_pin' => [
            'label' => 'JAMB Profile/PIN',
            'service_id' => 'jamb',
            'variation_code' => 'utme',
            'amount' => 4700,
            'quantity' => 1,
            'order_type' => 'JAMB Profile/PIN',
            'tx_prefix' => 'JAMB',
            'provider_service_types' => ['vtu_epin', 'education_jamb'],
        ],
    ],
];


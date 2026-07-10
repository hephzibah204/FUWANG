<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'robosttech' => [
        'api_key' => env('ROBOSTTECH_API_KEY', ''),
        'base_url' => env('ROBOSTTECH_BASE_URL', 'https://robosttech.com/api'),
    ],

    'flutterwave' => [
        'public' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secret' => env('FLUTTERWAVE_SECRET_KEY'),
        'encryption' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'webhook_hash' => env('FLUTTERWAVE_SECRET_HASH'),
    ],

    'monnify' => [
        'api_key' => env('MONNIFY_API_KEY'),
        'secret_key' => env('MONNIFY_SECRET_KEY'),
        'contract_code' => env('MONNIFY_CONTRACT_CODE'),
        // Leave null to auto-pick live vs sandbox from monnify.sandbox
        'endpoint_auth' => env('MONNIFY_ENDPOINT_AUTH'),
        'endpoint_reserve' => env('MONNIFY_ENDPOINT_RESERVE'),
        'sandbox' => filter_var(env('MONNIFY_SANDBOX', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'paystack' => [
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
    ],

    'payvessel' => [
        'api_key' => env('PAYVESSEL_API_KEY'),
        'endpoint' => env('PAYVESSEL_ENDPOINT'),
        'business_id' => env('PAYVESSEL_BUSINESS_ID'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'legal_model' => env('OPENAI_LEGAL_MODEL', 'gpt-4o-mini'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'email_webhooks' => [
        'secret' => env('EMAIL_WEBHOOK_SECRET'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'logistics' => [
        'jwt_secret' => env('LOGISTICS_JWT_SECRET'),
        'jwt_ttl_seconds' => (int) env('LOGISTICS_JWT_TTL_SECONDS', 3600),
        'agent_fee_standard' => (float) env('LOGISTICS_AGENT_FEE_STANDARD', 1500),
        'agent_fee_express' => (float) env('LOGISTICS_AGENT_FEE_EXPRESS', 2500),
        'agent_fee_overnight' => (float) env('LOGISTICS_AGENT_FEE_OVERNIGHT', 3500),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

];

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
        'secret' => env('FLW_SECRET_KEY'),
        'webhook_hash' => env('FLW_SECRET_HASH'),
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

];

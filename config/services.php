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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'google' => [
        'gemini_api_key' => env('GEMINI_API_KEY'),
    ],
    'whatsapp' => [
        'key' => env('WA_GATEWAY_APIKEY'),
        'host' => env('WA_HOST'),
        'token' => env('WHATSAPP_TOKEN'),
    ],
    'moota' => [
        'api_key' => env('MOOTA_API_KEY'),
        'base_url' => 'https://api.moota.co',
        'webhook_secret' => env('MOOTA_WEBHOOK_SECRET'),
    ],
];

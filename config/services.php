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

    'print_service' => [
        'base_url'        => env('PRINT_SERVICE_URL', 'http://host.docker.internal:8080'),
        'timeout'         => env('PRINT_SERVICE_TIMEOUT', 3),
        'print_path'      => env('PRINT_SERVICE_PRINT_PATH', '/print'),
        'printers_path'   => env('PRINT_SERVICE_PRINTERS_PATH', '/printers'),
        'default_printer' => env('PRINT_SERVICE_DEFAULT_PRINTER'),
        'printers'        => [
            'customer' => env('PRINT_SERVICE_CUSTOMER_PRINTER'),
            'receipt'  => env('PRINT_SERVICE_RECEIPT_PRINTER'),
            'kitchen'  => env('PRINT_SERVICE_KITCHEN_PRINTER'),
        ],
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];

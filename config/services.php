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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'giaohangnhanh' => [
        'key' => env('GIAOHANGNHANH_API_KEY'),
    ],

    'payos_order' => [
        'client_id' => env('PAYOS_ORDER_CLIENT_ID'),
        'client_secret' => env('PAYOS_ORDER_CLIENT_SECRET'),
        'partner_code' => env('PAYOS_ORDER_PARTNER_CODE'),
        'checksum' => env('PAYOS_ORDER_CHECK_SUM'),
    ],

    'payos_order_motorcycle' => [
        'client_id' => env('PAYOS_ORDER_MOTORCYCLE_CLIENT_ID'),
        'client_secret' => env('PAYOS_ORDER_MOTORCYCLE_CLIENT_SECRET'),
        'partner_code' => env('PAYOS_ORDER_MOTORCYCLE_PARTNER_CODE'),
        'checksum' => env('PAYOS_ORDER_MOTORCYCLE_CHECK_SUM'),
    ],

    'youtube' => [
        'key' => env('YOUTUBE_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
    ],

];

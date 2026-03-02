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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'rehive' => [
        'base_url' => env('REHIVE_BASE_URL'),
        'token' => env('REHIVE_API_TOKEN'),
        'operational_accounts' => [
            'NGN' => env('REHIVE_OPERATIONAL_ACCOUNT_NGN'),
            'KES' => env('REHIVE_OPERATIONAL_ACCOUNT_KES'),
            'GHS' => env('REHIVE_OPERATIONAL_ACCOUNT_GHS'),
            'ZAR' => env('REHIVE_OPERATIONAL_ACCOUNT_ZAR'),
            'UGX' => env('REHIVE_OPERATIONAL_ACCOUNT_UGX'),
            'RWF' => env('REHIVE_OPERATIONAL_ACCOUNT_RWF'),
            'XOF' => env('REHIVE_OPERATIONAL_ACCOUNT_XOF'),
            'XAF' => env('REHIVE_OPERATIONAL_ACCOUNT_XAF'),
            'TZS' => env('REHIVE_OPERATIONAL_ACCOUNT_TZS'),
        ],
    ],

    'blockradar' => [
        'base_url' => env('BLOCKRADAR_BASE_URL'),
        'master_api_key' => env('BLOCKRADAR_MASTER_API_KEY'),
        'webhook_secret' => env('BLOCKRADAR_WEBHOOK_SECRET'),
        'env' => env('BLOCKRADAR_ENV', 'sandbox'),
        'base_wallet_id' => env('BLOCKRADAR_BASE_WALLET_ID'),
        'solana_wallet_id' => env('BLOCKRADAR_SOLANA_WALLET_ID'),
    ],

];

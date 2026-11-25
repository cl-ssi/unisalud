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

    'claveunica' => [
        'client_id' => env('CLAVEUNICA_CLIENT_ID'),
        'client_secret' => env('CLAVEUNICA_CLIENT_SECRET'),
        'redirect' => env('CLAVEUNICA_REDIRECT_URI') 
    ],

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

    // Cloud Run Job that runs waitlist uploads (php artisan waitlist:import).
    // Unset: uploads go to the regular queue (local development).
    'cloud_run_import' => [
        'project' => env('CLOUD_RUN_IMPORT_PROJECT', env('GOOGLE_PROJECT_ID')),
        'region' => env('CLOUD_RUN_IMPORT_REGION'),
        'job' => env('CLOUD_RUN_IMPORT_JOB'),
    ],

    // FONASA Certificador Previsional through the WSSSI proxy (as in ionline).
    'wsssi' => [
        'url' => env('WSSSI_URL'),
        'timeout' => env('WSSSI_TIMEOUT', 15),
    ],

];

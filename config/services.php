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

        'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'maps_api_url' => 'https://maps.googleapis.com/maps/api',
        'geocoding_enabled' => env('GOOGLE_GEOCODING_ENABLED', true),
    ],

    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'database_url' => env('FIREBASE_DATABASE_URL'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'africas_talking'),
        'api_key' => env('SMS_API_KEY'),
        'username' => env('SMS_USERNAME'),
        'sender_id' => env('SMS_SENDER_ID', 'SmartClean'),
    ],

    'ai' => [
        'url' => env('AI_MICROSERVICE_URL', 'http://ai-service:8001'),
        'timeout' => env('AI_SERVICE_TIMEOUT', 15),
        'retries' => env('AI_SERVICE_RETRIES', 3),
    ],

    'commission' => [
        'default_rate' => 15.00,
        'instant_booking_rate' => 18.00,
        'minimum_payout' => 5000.00,
    ],

    'booking' => [
        'instant_timeout_seconds' => 60,
        'scheduled_timeout_hours' => 24,
        'max_instant_retries' => 5,
        'reminder_hours_before' => [24, 1],
    ],

];

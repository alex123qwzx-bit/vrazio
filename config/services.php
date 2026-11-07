<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Razorpay, Fast2SMS, and other third-party service credentials.
    |
    */

    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY', env('RESEND_KEY')),
    ],

    'fast2sms' => [
        'key' => env('FAST2SMS_API_KEY'),
        'route' => env('FAST2SMS_ROUTE', 'otp'),
        'sender_id' => env('FAST2SMS_SENDER_ID', 'VRAZIO'),
    ],

    'otp' => [
        'dev_mode' => env('OTP_DEV_MODE', false),
        'expiry_minutes' => 5,
        'max_attempts' => 10,
        'cooldown_seconds' => 30,
        'rate_limit_max' => 5,
        'rate_limit_minutes' => 10,
    ],

    'gst_rate' => 0.03, // 3% GST on jewellery

    'shipping' => [
        'standard' => [
            'label' => 'Complimentary Armoured Transit',
            'cost' => 0,
            'days' => '5-7 business days',
        ],
        'express' => [
            'label' => 'Priority Insured Express',
            'cost' => 999,
            'days' => '2-3 business days',
        ],
    ],

];

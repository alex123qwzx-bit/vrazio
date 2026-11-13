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
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'vrazio.firebaseapp.com'),
        'project_id' => env('FIREBASE_PROJECT_ID', 'vrazio'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', 'vrazio.firebasestorage.app'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '765416189650'),
        'app_id' => env('FIREBASE_APP_ID', '1:765416189650:web:e8b0c346b70953c25f470b'),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID', 'G-0NL6RX2815'),
        'google_client_id' => env('GOOGLE_CLIENT_ID', '765416189650-fcsfoihb52uqcg7594bjocluph80t4q1.apps.googleusercontent.com'),
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

    'enterprise' => [
        'name' => env('ENTERPRISE_NAME', 'VRAZIO'),
        'udyam_registration' => env('UDYAM_REGISTRATION', 'UDYAM-UP-59-0089382'),
        'whatsapp_support' => env('WHATSAPP_SUPPORT', '+91 8791227210'),
        'support_phone' => env('SUPPORT_PHONE', '8791227210'),
        'support_email' => env('SUPPORT_EMAIL', 'vrazioofficail@gmail.com'),
    ],

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

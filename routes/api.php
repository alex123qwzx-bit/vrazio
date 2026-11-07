<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Fast2SMS OTP, Checkout & Razorpay Webhooks
|--------------------------------------------------------------------------
*/

Route::prefix('checkout/otp')->group(function () {
    Route::post('/send', [OtpController::class, 'send'])
        ->middleware('throttle:3,5');
    Route::post('/verify', [OtpController::class, 'verify'])
        ->middleware('throttle:5,5');
});

// Razorpay Server-to-Server Webhook (BUS-01)
Route::post('/razorpay/webhook', [CheckoutController::class, 'handleWebhook'])
    ->name('api.razorpay.webhook');

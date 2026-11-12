<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — VRAZIO Haute Joaillerie
|--------------------------------------------------------------------------
*/

// Home Showcase
Route::get('/', [HomeController::class, 'index'])->name('home');

// Customer Authentication & Account
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:3,1')->name('register.post');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
Route::get('/account', [CustomerAuthController::class, 'dashboard'])->name('account');

// Firebase Authentication (Phone SMS & Google Sign-In)
Route::post('/auth/firebase/session', [CustomerAuthController::class, 'handleFirebaseSession'])->middleware('throttle:10,5')->name('auth.firebase.session');
Route::post('/admin/auth/google', [\App\Http\Controllers\Auth\AdminAuthController::class, 'handleGoogleLogin'])->middleware(['web', 'throttle:10,5'])->name('admin.auth.google');

// Mobile SMS OTP Authentication & Verification
Route::post('/auth/otp/send', [CustomerAuthController::class, 'sendLoginOtp'])->middleware('throttle:3,5')->name('auth.otp.send');
Route::post('/auth/otp/verify', [CustomerAuthController::class, 'verifyLoginOtp'])->middleware('throttle:5,5')->name('auth.otp.verify');
Route::post('/account/phone/verify', [CustomerAuthController::class, 'verifyDashboardPhone'])->middleware(['auth', 'throttle:5,5'])->name('account.phone.verify');
Route::post('/account/profile', [CustomerAuthController::class, 'updateProfile'])->middleware(['auth', 'throttle:10,1'])->name('account.profile.update');

// Email Verification
Route::get('/email/verify', [CustomerAuthController::class, 'showVerifyEmail'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [CustomerAuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/verification-notification', [CustomerAuthController::class, 'resendVerification'])->middleware(['auth'])->name('verification.send');

// High Jewellery & Haute Couture Collections
Route::get('/collections', [ProductController::class, 'index'])->name('products.index');
Route::get('/couture', [ProductController::class, 'couture'])->name('products.couture');
Route::get('/clothes', [ProductController::class, 'couture'])->name('products.clothes');
Route::get('/collections/couture', [ProductController::class, 'couture']);
Route::get('/collections/clothes', [ProductController::class, 'couture']);
Route::get('/collections/{category}', [ProductController::class, 'category'])->name('products.category');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/search', [ProductController::class, 'search'])->name('products.search');

// Bespoke Bag / Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/voucher/apply', [CartController::class, 'applyVoucher'])->name('cart.voucher.apply');
Route::delete('/cart/voucher/remove', [CartController::class, 'removeVoucher'])->name('cart.voucher.remove');

// 3-Step Secure Escrow Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/verify-payment', [CheckoutController::class, 'verifyPayment'])->name('checkout.verify-payment');

// Order Confirmation, Customer Vault & Tracking
Route::get('/order-confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');
Route::get('/order/track/{orderNumber?}', [OrderController::class, 'track'])->name('order.track');
Route::get('/orders/track/{orderNumber?}', [OrderController::class, 'track'])->name('orders.track');
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/account/orders', [OrderController::class, 'index'])->name('account.orders');
Route::get('/orders/{orderNumber}/cancel-eligibility', [OrderController::class, 'checkCancellationEligibility'])->name('order.cancel.eligibility');
Route::post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');

// GST Credit Notes & Adjustments
Route::get('/order/{orderNumber}/credit-note', [\App\Http\Controllers\CreditNoteController::class, 'forOrder'])->name('order.credit_note');
Route::get('/credit-note/{creditNoteNumber}', [\App\Http\Controllers\CreditNoteController::class, 'show'])->name('credit_note.show');
Route::get('/credit-note/{creditNoteNumber}/pdf', [\App\Http\Controllers\CreditNoteController::class, 'download'])->name('credit_note.download');
Route::get('/credit-note/{creditNoteNumber}/export-pdf', [\App\Http\Controllers\CreditNoteController::class, 'download'])->name('credit_note.pdf');

// Luxury Order Invoices (Web View, PDF Download, Stream)
Route::get('/order/{orderNumber}/invoice', [InvoiceController::class, 'show'])->name('order.invoice');
Route::get('/order/{orderNumber}/invoice/pdf', [InvoiceController::class, 'download'])->name('order.invoice.download');
Route::get('/order/{orderNumber}/invoice/stream', [InvoiceController::class, 'stream'])->name('order.invoice.stream');

// Haute Joaillerie Warranty Claims
Route::get('/warranty/claim/{orderNumber}', [\App\Http\Controllers\WarrantyClaimController::class, 'create'])->name('warranty.create');
Route::post('/warranty/claim', [\App\Http\Controllers\WarrantyClaimController::class, 'store'])->name('warranty.store');
Route::get('/warranty/claim/view/{claimNumber}', [\App\Http\Controllers\WarrantyClaimController::class, 'show'])->name('warranty.show');
Route::get('/warranty/claim/image/{id}', [\App\Http\Controllers\WarrantyClaimController::class, 'streamImage'])->name('warranty.image');
Route::get('/api/warranty/check/{orderNumber}', [\App\Http\Controllers\WarrantyClaimController::class, 'checkStatus'])->name('api.warranty.check');

// VRAZIO PRIVÉ CLUB Membership System
Route::get('/prive-club', [\App\Http\Controllers\PriveClubController::class, 'index'])->name('prive-club.index');
Route::get('/prive-club/join/{slug}', [\App\Http\Controllers\PriveClubController::class, 'checkout'])->name('prive-club.checkout');
Route::post('/prive-club/enroll', [\App\Http\Controllers\PriveClubController::class, 'processEnrollment'])->name('prive-club.enroll');
Route::get('/prive-club/verify/{memberId}', [\App\Http\Controllers\PriveClubController::class, 'verify'])->name('prive-club.verify');

// Editorial & Legal Atelier Pages
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/faq-page', [PageController::class, 'faq'])->name('faq');
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/about-us', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/contact-us', [PageController::class, 'contact'])->name('contact');
Route::get('/shipping-policy', [PageController::class, 'shipping'])->name('pages.shipping');
Route::get('/shipping', [PageController::class, 'shipping'])->name('shipping-policy');
Route::get('/return-policy', [PageController::class, 'returns'])->name('pages.returns');
Route::get('/returns', [PageController::class, 'returns'])->name('returns-policy');
Route::get('/warranty', [PageController::class, 'warranty'])->name('pages.warranty');
Route::get('/warranty-policy', [PageController::class, 'warranty'])->name('warranty-policy');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy-policy');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');

// XML Sitemap for Search Engines
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

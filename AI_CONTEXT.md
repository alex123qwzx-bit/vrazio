# AI Context & Technical Blueprint: VRAZIO Haute Joaillerie

> **Purpose:** Drop-in context primer for AI coding assistants (Claude Projects, Cursor, Copilot, ChatGPT, Windsurf, Antigravity) to enable instant, zero-hallucination comprehension of the VRAZIO codebase.

---

## 1. System Overview & Tech Stack
- **Project Name:** VRAZIO (`vrazio/ecommerce`)
- **Domain:** Haute Joaillerie fine luxury jewellery e-commerce platform (India market).
- **Core Framework:** Laravel 11.x on PHP 8.2 / 8.3 / 8.4 (`declare(strict_types=1);` enforced).
- **Frontend Layer:** Blade templates + Livewire 3.4 + Alpine.js 3.14 + Tailwind CSS 3.4.
- **Admin Panel:** Filament v3.2 at `/admin` (Resources: `OrderResource`, `ProductResource`, `WarrantyClaimResource`, `UserResource`; Page: `ThemeCustomizer`).
- **PDF Engine:** `barryvdh/laravel-dompdf` for A4 portrait luxury tax invoices.
- **Payment & SMS:** Razorpay (escrow INR payments) & Fast2SMS / Firebase (OTP 2FA).

---

## 2. Directory Architecture & Key Files

| Role / Domain | Key Location | Purpose |
|---|---|---|
| **Theme & Sections** | `app/Services/ThemeService.php`<br/>`app/Filament/Pages/ThemeCustomizer.php` | Manages dynamic homepage sections (`hero`, `categories`, `heritage`, `showcase`, `testimonials`, `faq`), colors, and announcement bar. Cache-backed (`site_theme_settings`). |
| **Bespoke Bag / Cart** | `app/Services/CartService.php`<br/>`app/Http/Controllers/CartController.php` | Session-based cart storage, quantity updates, Indian Jewellery **3% GST** calculation (`config('services.gst_rate')`), shipping computation. |
| **Authentication** | `app/Http/Controllers/Auth/CustomerAuthController.php`<br/>`app/Services/OtpService.php` | Classical email/password with email verification, Mobile SMS OTP login/registration, and Firebase Token exchange with Google Identity Toolkit. |
| **2FA Checkout Shield** | `app/Http/Middleware/EnsureOtpVerified.php`<br/>`app/Http/Requests/CheckoutRequest.php`<br/>`app/Http/Controllers/CheckoutController.php` | Strictly prevents checkout processing unless `session('order_phone_verified') === $request->client_phone`. Validates 6-digit Indian PIN codes and 10-digit mobile numbers. |
| **Payments & Escrow** | `app/Services/RazorpayService.php` | Creates Razorpay INR orders (`receipt`, `amount` in paise). Verifies payment signature with HMAC-SHA256 (`hash_equals()`). |
| **Order Lifecycle** | `app/Models/Order.php`<br/>`app/Http/Controllers/OrderController.php`<br/>`app/Filament/Resources/OrderResource.php` | 5-stage fulfillment: `order_placed` &rarr; `packed` &rarr; `shipped` &rarr; `out_for_delivery` &rarr; `delivered`. Tracking via `/order/track/{orderNumber}`. |
| **Warranty Claims** | `app/Models/WarrantyClaim.php`<br/>`app/Http/Controllers/WarrantyClaimController.php`<br/>`app/Filament/Resources/WarrantyClaimResource.php` | Post-purchase claim registration strictly for `delivered` orders. Deep MIME file validation, multi-photo uploads to `public/uploads/warranty`, transaction rollback safety. |
| **Invoices** | `app/Http/Controllers/InvoiceController.php`<br/>`resources/views/invoices/show.blade.php`<br/>`resources/views/invoices/pdf.blade.php` | Web preview, PDF download, and browser streaming of luxury tax invoices. |

---

## 3. Critical Business Invariants (Never Break These)

1. **Jewellery GST Rate is 3%:** Never hardcode 18% or standard GST. Indian GST for precious metals and jewellery is legally 3% (`config('services.gst_rate', 0.03)`).
2. **Mandatory 2FA Before Order Processing:** An order cannot be initialized in `CheckoutController@process` unless the client's phone matches `session('order_phone_verified')`.
3. **Warranty Claim Eligibility:** An order MUST have `fulfillment_status === 'delivered'` to file a warranty claim. Each order can only have one warranty claim (`orders.id` &harr; `warranty_claims.order_id` 1:1).
4. **Theme Invalidation:** Always call `Cache::forget('site_theme_settings')` whenever updating storefront configuration or hero banners.
5. **Stock Deduction:** Inventory `stock_quantity` decrements strictly upon successful payment verification (`CheckoutController@verifyPayment`).

---

## 4. Database Schema Quick-Reference

- **`users`**: `name`, `email`, `phone`, `role` ('patron'|'admin'), `password`, `google_id`, `phone_verified_at`, `email_verified_at`.
- **`products`**: `sku`, `title`, `slug`, `category`, `metal_type`, `gross_weight_grams`, `net_weight_grams`, `price`, `sale_price`, `stock_quantity`, `is_featured`, `bis_hallmarked`, `certification`, `specs` (array), `images` (array), `tags` (array).
- **`orders`**: `order_number` (e.g. `VRZ-AB12CD34`), `user_id`, `client_name`, `client_email`, `client_phone`, `shipping_address` (array), `shipping_method`, `subtotal`, `tax_gst`, `shipping_cost`, `total_amount`, `payment_gateway`, `razorpay_order_id`, `razorpay_payment_id`, `payment_status` ('pending'|'paid'|'failed'), `fulfillment_status` ('inscribed'|'hallmarking'|'armoured_transit'|'delivered'), `tracking_number`, `otp_verified_at`.
- **`order_items`**: `order_id`, `product_id`, `product_title`, `product_sku`, `unit_price`, `quantity`, `line_total`.
- **`warranty_claims`**: `claim_number` (e.g. `WRC-AB12CD34`), `order_id`, `user_id`, `customer_name`, `phone`, `email`, `product_name`, `issue_type`, `description`, `status` ('submitted'|'under_review'|'inspection_approved'|'repaired'|'rejected'), `is_checked`, `admin_note`.
- **`warranty_claim_images`**: `claim_id`, `image_path`.
- **`phone_otps`**: `phone`, `otp_hash` (SHA-256), `expires_at`, `attempts`, `verified_at`.
- **`site_theme_settings`**: `key`, `value` (json), `updated_by`.

---

## 5. Development & Testing Commands

```bash
# Run migrations and product seeds (seeds 24 fine jewellery products + default theme)
php artisan migrate --seed

# Run dev server with hot reload
npm run dev
php artisan serve

# Code formatting & style compliance
vendor/bin/pint

# Default Admin credentials
# URL: http://localhost:8000/admin
# Email: admin@vrazio.com
# Password: password
```

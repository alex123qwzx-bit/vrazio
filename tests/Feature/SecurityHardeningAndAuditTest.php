<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\WarrantyClaim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningAndAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * VULN-01: Razorpay webhook rejects missing signature header with 400.
     */
    public function test_razorpay_webhook_rejects_missing_signature(): void
    {
        $response = $this->postJson(route('api.razorpay.webhook'), [
            'event' => 'order.paid',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing signature header']);
    }

    /**
     * VULN-01: Razorpay webhook rejects invalid cryptographic signature.
     */
    public function test_razorpay_webhook_rejects_invalid_signature_when_secret_configured(): void
    {
        config(['services.razorpay.webhook_secret' => 'super_secret_webhook_key_123']);

        $payload = json_encode(['event' => 'order.paid', 'payload' => []]);

        $response = $this->call(
            'POST',
            route('api.razorpay.webhook'),
            [],
            [],
            [],
            ['HTTP_X-Razorpay-Signature' => 'invalid_forged_signature', 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid signature']);
    }

    /**
     * VULN-02: Privé Club Membership enrollment rejects mismatched order ID session.
     */
    public function test_prive_membership_enrollment_rejects_mismatched_order_session(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Imperial Tier',
            'slug' => 'imperial',
            'price' => 50000.00,
            'validity_months' => 12,
            'next_order_discount_percent' => 10.0,
            'next_order_max_discount' => 5000.00,
            'next_order_min_cart' => 10000.00,
            'benefits' => ['Bespoke Concierge', 'Complimentary Insured Vault Transit'],
            'is_active' => true,
        ]);

        $user = User::factory()->create(['role' => 'patron']);

        // User simulates having started a checkout for another plan/order
        $response = $this->actingAs($user)->withSession([
            'pending_prive_enrollment' => [
                'plan_id' => $plan->id,
                'plan_slug' => 'imperial',
                'razorpay_order_id' => 'order_legit_12345',
                'amount' => 50000.00,
                'user_id' => $user->id,
            ],
        ])->postJson(route('prive-club.enroll'), [
            'plan_slug' => 'imperial',
            'razorpay_order_id' => 'order_swapped_cheap_999', // Swapped order ID
            'razorpay_payment_id' => 'pay_cheap_999',
            'razorpay_signature' => 'sig_sim_valid',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /**
     * VULN-03: Unauthenticated user cannot access warranty status API.
     */
    public function test_unauthenticated_user_cannot_access_warranty_status_api(): void
    {
        $response = $this->getJson(route('api.warranty.check', ['orderNumber' => 'VRZ-SAMPLE123']));

        $response->assertStatus(401);
    }

    /**
     * VULN-03: Authenticated user cannot access another patron's warranty status (IDOR prevention).
     */
    public function test_authenticated_user_cannot_access_another_patrons_warranty_status_idor(): void
    {
        $owner = User::factory()->create(['role' => 'patron']);
        $attacker = User::factory()->create(['role' => 'patron']);

        $order = Order::create([
            'order_number' => 'VRZ-OWNED01',
            'user_id' => $owner->id,
            'client_name' => $owner->name,
            'client_email' => $owner->email,
            'client_phone' => '9876543210',
            'shipping_address' => ['street' => 'Colaba', 'city' => 'Mumbai', 'state' => 'MH', 'pincode' => '400001'],
            'shipping_method' => 'standard',
            'subtotal' => 10000,
            'tax_gst' => 300,
            'total_amount' => 10300,
            'payment_status' => 'paid',
            'fulfillment_status' => 'delivered',
        ]);

        WarrantyClaim::create([
            'claim_number' => 'WC-VRZ-OWNED01-1234',
            'order_id' => $order->id,
            'user_id' => $owner->id,
            'member_id' => 'VPC-2026-00001',
            'customer_name' => $owner->name,
            'phone' => '9876543210',
            'email' => $owner->email,
            'product_name' => 'Emerald Ring',
            'issue_type' => 'prong_loosening',
            'description' => 'Detailed damage report',
            'status' => 'submitted',
            'admin_note' => 'Confidential internal note: VIP patron',
        ]);

        // Attacker attempts to inspect owner's warranty status and admin note
        $response = $this->actingAs($attacker)->getJson(route('api.warranty.check', ['orderNumber' => 'VRZ-OWNED01']));

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    /**
     * VULN-04: Super Admin can access all order dossiers and searches.
     */
    public function test_super_admin_can_access_orders_and_dossiers(): void
    {
        $patron = User::factory()->create(['role' => 'patron']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $order = Order::create([
            'order_number' => 'VRZ-DOSSIER01',
            'user_id' => $patron->id,
            'client_name' => $patron->name,
            'client_email' => $patron->email,
            'client_phone' => '9876543210',
            'shipping_address' => ['street' => 'MG Road', 'city' => 'Bangalore', 'state' => 'KA', 'pincode' => '560001'],
            'shipping_method' => 'standard',
            'subtotal' => 20000,
            'tax_gst' => 600,
            'total_amount' => 20600,
            'payment_status' => 'paid',
            'fulfillment_status' => 'inscribed',
        ]);

        $this->assertTrue($order->isAccessibleBy($superAdmin));

        $response = $this->actingAs($superAdmin)->get(route('order.confirmation', ['orderNumber' => 'VRZ-DOSSIER01']));
        $response->assertStatus(200);
    }

    /**
     * VULN-05: FAQ JSON-LD schema safely encodes special characters without unescaped script tags.
     */
    public function test_faq_json_ld_schema_safely_escapes_special_characters(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('application/ld+json', $content);
        $this->assertStringNotContainsString('</script><script>', $content);
    }

    /**
     * VULN-07: Security headers include Content-Security-Policy, X-XSS-Protection, and HSTS.
     */
    public function test_security_headers_include_csp_xss_and_hsts_when_secure(): void
    {
        $response = $this->get('https://localhost/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('checkout.razorpay.com', $csp);
    }

    /**
     * VULN-08: Cart update endpoint rejects invalid or negative quantities.
     */
    public function test_cart_update_rejects_negative_or_oversized_quantities(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->patchJson(route('cart.update', ['id' => $product->id]), [
            'quantity' => -5,
        ]);
        $response->assertStatus(422);

        $responseOver = $this->patchJson(route('cart.update', ['id' => $product->id]), [
            'quantity' => 999999,
        ]);
        $responseOver->assertStatus(422);
    }

    /**
     * VULN-06: Product review photo uploads sanitize client extensions.
     */
    public function test_product_review_photo_extension_is_sanitized(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'patron']);
        $product = Product::factory()->create();

        $file = UploadedFile::fake()->image('review_photo.jpg', 600, 600);

        $response = $this->actingAs($user)->post(route('products.reviews.store', ['slug' => $product->slug]), [
            'rating' => 5,
            'title' => 'Bespoke Perfection',
            'content' => 'The craftsmanship on this necklace is superlative and beyond compare.',
            'photos' => [$file],
        ]);

        $review = ProductReview::where('product_id', $product->id)->first();
        $this->assertNotNull($review);
        $this->assertNotEmpty($review->photos);
        $this->assertMatchesRegularExpression('/^reviews\/[a-f0-9\\-]+\\.(jpg|jpeg|png|webp)$/i', $review->photos[0]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriveClubMembershipTest extends TestCase
{
    use RefreshDatabase;

    private function createPlans(): void
    {
        MembershipPlan::firstOrCreate(
            ['slug' => 'member'],
            [
                'name' => 'Member',
                'tagline' => 'Essential Patronage & Year-Round Privilege',
                'price' => 299.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 5.00,
                'next_order_max_discount' => 100.00,
                'next_order_min_cart' => 999.00,
                'birthday_discount_percent' => 0.00,
                'birthday_max_discount' => 0.00,
                'birthday_min_cart' => 0.00,
                'benefits' => ['5% Next Order Discount', '12 Months Warranty Access'],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        MembershipPlan::firstOrCreate(
            ['slug' => 'gold'],
            [
                'name' => 'Gold Member',
                'tagline' => 'Refined Indulgence & Elevated Privileges',
                'price' => 499.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 10.00,
                'next_order_max_discount' => 250.00,
                'next_order_min_cart' => 1499.00,
                'birthday_discount_percent' => 10.00,
                'birthday_max_discount' => 150.00,
                'birthday_min_cart' => 1499.00,
                'benefits' => ['10% Next Order Discount', '24 Months Warranty Access', 'Birthday Surprise'],
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        MembershipPlan::firstOrCreate(
            ['slug' => 'gold_max'],
            [
                'name' => 'Gold Max Member',
                'tagline' => 'The Pinnacle of Haute Joaillerie Patronage',
                'price' => 999.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 15.00,
                'next_order_max_discount' => 500.00,
                'next_order_min_cart' => 1999.00,
                'birthday_discount_percent' => 15.00,
                'birthday_max_discount' => 300.00,
                'birthday_min_cart' => 1999.00,
                'benefits' => ['15% Next Order Discount', '36 Months Warranty Access', 'Priority Concierge'],
                'is_active' => true,
                'sort_order' => 3,
            ]
        );
    }

    /**
     * Test /prive-club page loads and displays 3 tiers and 100% digital guarantee.
     */
    public function test_prive_club_landing_page_renders_tiers(): void
    {
        $this->createPlans();

        $response = $this->get('/prive-club');
        $response->assertStatus(200);
        $response->assertSee('PRIVÉ CLUB');
        $response->assertSee('Member');
        $response->assertSee('Gold Member');
        $response->assertSee('Gold Max Member');
        $response->assertSee('100% Digital Privilege Pass');
    }

    /**
     * Test Membership unique member_id generation format VPC-YYYY-00001.
     */
    public function test_membership_generates_standard_vpc_member_id(): void
    {
        $this->createPlans();
        $user = User::factory()->create();
        $plan = MembershipPlan::where('slug', 'gold')->first();

        $membership = Membership::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'tier' => 'gold',
            'tier_name' => 'Gold Member',
            'fee_paid' => 499.00,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $year = now()->format('Y');
        $this->assertMatchesRegularExpression("/^VPC-{$year}-\\d{5}$/", $membership->member_id);
        $this->assertNotEmpty($membership->card_token);
        $this->assertTrue($membership->isValid());
        $this->assertTrue($user->isPriveMember());
    }

    /**
     * Test QR verification page /prive-club/verify/{memberId}.
     */
    public function test_qr_verification_page_verifies_active_membership(): void
    {
        $this->createPlans();
        $user = User::factory()->create();
        $plan = MembershipPlan::where('slug', 'member')->first();

        $membership = Membership::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'tier' => 'member',
            'tier_name' => 'Member',
            'fee_paid' => 299.00,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $response = $this->get("/prive-club/verify/{$membership->member_id}");
        $response->assertStatus(200);
        $response->assertSee('Authentic Privé Pass');
        $response->assertSee($membership->member_id);
        $response->assertSee('VRAZIO Ledger Verified');
    }

    /**
     * Test non-members are blocked from warranty claim with luxury notice.
     */
    public function test_non_members_are_redirected_to_prive_club_notice_on_warranty_page(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create([
            'category' => 'jewelry',
            'is_warranty_eligible' => true,
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-TESTWARR01',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => '9876543210',
            'shipping_address' => ['street' => '1 Main St', 'city' => 'Delhi', 'state' => 'Delhi', 'pincode' => '110001'],
            'shipping_method' => 'standard',
            'subtotal' => 2000,
            'tax_gst' => 60,
            'shipping_cost' => 0,
            'total_amount' => 2060,
            'payment_gateway' => 'cod',
            'payment_status' => 'completed',
            'fulfillment_status' => 'delivered',
            'delivered_at' => now()->subDays(5),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 2000,
            'line_total' => 2000,
        ]);

        // Unauthenticated visitor visiting claim form
        $guestResponse = $this->get("/warranty/claim/{$order->order_number}");
        $guestResponse->assertRedirect('/login');

        // Authenticated customer without Privé membership
        $authResponse = $this->actingAs($user)->get("/warranty/claim/{$order->order_number}");
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Members-Only Warranty Service');
        $authResponse->assertSee('prive-club');
    }

    /**
     * Test active Privé Club member can access warranty claim page.
     */
    public function test_active_prive_member_can_access_warranty_claim_page(): void
    {
        $this->createPlans();
        $user = User::factory()->create(['role' => 'customer']);
        $plan = MembershipPlan::where('slug', 'gold_max')->first();

        Membership::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'tier' => 'gold_max',
            'tier_name' => 'Gold Max Member',
            'fee_paid' => 999.00,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $product = Product::factory()->create([
            'category' => 'jewelry',
            'is_warranty_eligible' => true,
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-TESTWARR02',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => '9876543210',
            'shipping_address' => ['street' => '1 Main St', 'city' => 'Delhi', 'state' => 'Delhi', 'pincode' => '110001'],
            'shipping_method' => 'standard',
            'subtotal' => 2000,
            'tax_gst' => 60,
            'shipping_cost' => 0,
            'total_amount' => 2060,
            'payment_gateway' => 'cod',
            'payment_status' => 'completed',
            'fulfillment_status' => 'delivered',
            'delivered_at' => now()->subDays(5),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 2000,
            'line_total' => 2000,
        ]);

        $response = $this->actingAs($user)->get("/warranty/claim/{$order->order_number}");
        $response->assertStatus(200);
        $response->assertSee('Warranty Claim');
    }

    /**
     * Test CartService calculates one-time Privé next order discount accurately.
     */
    public function test_cart_service_calculates_prive_next_order_discount(): void
    {
        $this->createPlans();
        $user = User::factory()->create(['role' => 'customer']);
        $plan = MembershipPlan::where('slug', 'gold')->first();

        $membership = Membership::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'tier' => 'gold',
            'tier_name' => 'Gold Member',
            'fee_paid' => 499.00,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
            'next_order_discount_used' => false,
        ]);

        $product = Product::create([
            'sku' => 'VRZ-DISC-TEST',
            'title' => 'Gold Watch',
            'category' => 'watches',
            'price' => 3000.00,
            'discount_percent' => 0.00,
            'gst_percent' => 0.00,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($user);
        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->addItem($product, 1);

        // Subtotal = 3000. 10% of 3000 = 300, capped at 250!
        $this->assertEquals(250.00, $cartService->getPriveDiscount());
        $this->assertEquals(250.00, $cartService->getTotalDiscount());

        // Now mark as used
        $membership->update(['next_order_discount_used' => true]);
        $this->assertEquals(0.00, $cartService->getPriveDiscount());
    }
}

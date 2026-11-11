<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VoucherAndStaffDelegationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test voucher discounts, min-spend validation, and cart integration.
     */
    public function test_voucher_discount_math_and_cart_application(): void
    {
        $voucher = Voucher::create([
            'code' => 'ROYALTEST10',
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'min_spend' => 1000.0,
            'max_discount' => 500.0,
            'is_active' => true,
        ]);

        $this->assertEquals(100.0, $voucher->calculateDiscount(1000.0));
        $this->assertEquals(500.0, $voucher->calculateDiscount(10000.0)); // Capped at max_discount 500

        $cartService = app(CartService::class);
        $cartService->clear();

        // Should fail min spend when cart is empty
        $res = $cartService->applyVoucher('ROYALTEST10');
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('minimum spend', $res['message']);

        // Create product and add to cart
        $product = Product::factory()->create([
            'price' => 5000.0,
            'stock_quantity' => 10,
        ]);
        $cartService->addItem($product, 1);

        $resSuccess = $cartService->applyVoucher('ROYALTEST10');
        $this->assertTrue($resSuccess['success']);
        $this->assertEquals(500.0, $cartService->getDiscount());

        // Check GST on net amount: (5000 - 500) * 0.03 = 135
        $this->assertEquals(135.0, $cartService->getGst());

        // Check total: 4500 + 135 = 4635
        $this->assertEquals(4635.0, $cartService->getTotal('standard'));

        $cartService->removeVoucher();
        $this->assertEquals(0.0, $cartService->getDiscount());
    }

    /**
     * Test order placement persists voucher code and discount amount.
     */
    public function test_order_placement_records_voucher_and_increments_usage(): void
    {
        $user = User::factory()->create(['phone' => '9876543210', 'phone_verified_at' => now()]);
        $product = Product::factory()->create([
            'price' => 2000.0,
            'stock_quantity' => 5,
        ]);

        $voucher = Voucher::create([
            'code' => 'SAVE200',
            'discount_type' => 'fixed',
            'discount_value' => 200.0,
            'min_spend' => 1000.0,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->addItem($product, 1);
        $cartService->applyVoucher('SAVE200');

        session(['order_phone_verified' => $user->phone]);

        $response = $this->actingAs($user)->postJson(route('checkout.process'), [
            'client_name' => 'Lord Test Patron',
            'client_email' => 'lord@vrazio.com',
            'client_phone' => $user->phone,
            'shipping_address' => [
                'street' => '10 Atelier Way',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
            ],
            'shipping_method' => 'standard',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $orderNumber = $response->json('order_number');
        $order = Order::where('order_number', $orderNumber)->first();

        $this->assertNotNull($order);
        $this->assertEquals('SAVE200', $order->voucher_code);
        $this->assertEquals(200.0, (float) $order->discount_amount);

        // Voucher usage must be incremented
        $voucher->refresh();
        $this->assertEquals(1, $voucher->used_count);
    }

    /**
     * Test Super Admin and Sub-Manager approval restrictions.
     */
    public function test_sub_manager_access_strictly_requires_approval(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'is_approved' => true,
        ]);

        $subManager = User::factory()->create([
            'role' => 'sub_manager',
            'is_approved' => false,
        ]);

        $panel = app(\Filament\Panel::class);

        // Super Admin access must be true
        $this->assertTrue($superAdmin->canAccessPanel($panel));

        // Sub Manager without approval must be false
        $this->assertFalse($subManager->canAccessPanel($panel));

        // After approval by Super Admin, panel access is granted
        $subManager->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $superAdmin->id,
        ]);

        $this->assertTrue($subManager->canAccessPanel($panel));
    }

    /**
     * Test Admin Google authentication endpoint.
     */
    public function test_admin_google_login_endpoint_validation(): void
    {
        // 1. Missing token returns 422
        $resMissing = $this->postJson(route('admin.auth.google'), []);
        $resMissing->assertStatus(422);

        // 2. Invalid fake token returns 401 or 500 error safely without crashing
        $resInvalid = $this->postJson(route('admin.auth.google'), ['id_token' => 'invalid_token_sample']);
        $this->assertTrue(in_array($resInvalid->getStatusCode(), [401, 500], true));
    }
}

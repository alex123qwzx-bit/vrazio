<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_unverified_phone_number(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5, 'price' => 50000]);

        $payload = [
            'client_name' => 'Aditi Sharma',
            'client_email' => 'aditi@example.com',
            'client_phone' => '9876543210',
            'shipping_address' => [
                'street' => 'Colaba Causeway',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400005',
            ],
            'shipping_method' => 'standard',
        ];

        // Session does not have verified phone
        $response = $this->withSession([
            'cart' => [
                (string)$product->id => ['id' => $product->id, 'quantity' => 1],
            ],
        ])->postJson(route('checkout.process'), $payload);

        $response->assertStatus(403);
    }

    public function test_checkout_succeeds_with_verified_phone_and_locks_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 2, 'price' => 50000]);

        $payload = [
            'client_name' => 'Aditi Sharma',
            'client_email' => 'aditi@example.com',
            'client_phone' => '9876543210',
            'shipping_address' => [
                'street' => 'Colaba Causeway',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400005',
            ],
            'shipping_method' => 'standard',
        ];

        $response = $this->withSession([
            'order_phone_verified' => '9876543210',
            'cart' => [
                "{$product->id}_Size_14" => [
                    'id' => $product->id,
                    'quantity' => 1,
                    'size' => 'Size 14 (US 7)',
                ],
            ],
        ])->postJson(route('checkout.process'), $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'order_number', 'razorpay_order_id']);

        $order = Order::where('client_phone', '9876543210')->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->payment_status);

        // Verify size was saved in OrderItem (BUS-04)
        $orderItem = OrderItem::where('order_id', $order->id)->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals('Size 14 (US 7)', $orderItem->size);
    }

    public function test_razorpay_webhook_marks_order_paid_and_decrements_stock_idempotently_bus_01(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5, 'price' => 50000]);
        $order = Order::create([
            'order_number' => 'VRZ-WEBHOOK01',
            'client_name' => 'Vikram Patel',
            'client_email' => 'vikram@example.com',
            'client_phone' => '9876543210',
            'shipping_address' => ['street' => 'Marine Drive', 'city' => 'Mumbai', 'state' => 'MH', 'pincode' => '400020'],
            'shipping_method' => 'standard',
            'subtotal' => 50000,
            'tax_gst' => 1500,
            'shipping_cost' => 0,
            'total_amount' => 51500,
            'payment_status' => 'pending',
            'fulfillment_status' => 'inscribed',
            'razorpay_order_id' => 'order_rzp_mock_12345',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_sku' => $product->sku,
            'unit_price' => 50000,
            'quantity' => 2,
            'line_total' => 100000,
        ]);

        $webhookPayload = [
            'event' => 'order.paid',
            'payload' => [
                'order' => [
                    'entity' => [
                        'id' => 'order_rzp_mock_12345',
                        'amount' => 5150000,
                        'status' => 'paid',
                    ],
                ],
                'payment' => [
                    'entity' => [
                        'id' => 'pay_mock_998877',
                        'order_id' => 'order_rzp_mock_12345',
                    ],
                ],
            ],
        ];

        $jsonPayload = json_encode($webhookPayload);

        // Call Webhook endpoint
        $response = $this->call(
            'POST',
            route('api.razorpay.webhook'),
            [],
            [],
            [],
            ['HTTP_X-Razorpay-Signature' => 'test_sig', 'CONTENT_TYPE' => 'application/json'],
            $jsonPayload
        );

        $response->assertStatus(200);

        // Verify order status updated to paid
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('pay_mock_998877', $order->razorpay_payment_id);

        // Verify stock decremented by 2
        $product->refresh();
        $this->assertEquals(3, $product->stock_quantity);

        // Second call (idempotent): stock must NOT decrement again!
        $secondResponse = $this->call(
            'POST',
            route('api.razorpay.webhook'),
            [],
            [],
            [],
            ['HTTP_X-Razorpay-Signature' => 'test_sig', 'CONTENT_TYPE' => 'application/json'],
            $jsonPayload
        );
        $secondResponse->assertStatus(200);

        $product->refresh();
        $this->assertEquals(3, $product->stock_quantity); // Remains 3
    }
}

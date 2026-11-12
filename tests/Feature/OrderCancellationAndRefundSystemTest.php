<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\CancellationNotificationMail;
use App\Mail\RefundNotificationMail;
use App\Models\CreditNote;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\OrderCancellation;
use App\Models\OrderItem;
use App\Models\OrderRefund;
use App\Models\Product;
use App\Models\User;
use App\Services\CancellationRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderCancellationAndRefundSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key' => 'xxxx_test_key',
            'services.razorpay.secret' => 'xxxx_test_secret',
            'services.razorpay.webhook_secret' => 'test_webhook_secret',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'patron@vrazio.com',
            'phone' => '9876543210',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'concierge@vrazio.com',
            'phone' => '9999988888',
        ]);

        $this->productA = Product::factory()->create([
            'title' => 'Solitaire Diamond Ring 18KT',
            'sku' => 'VRZ-RNG-001',
            'price' => 50000,
            'stock_quantity' => 5,
        ]);

        $this->productB = Product::factory()->create([
            'title' => 'Royal Heritage Silk Sari',
            'sku' => 'VRZ-SAR-002',
            'price' => 20000,
            'stock_quantity' => 10,
        ]);
    }

    /**
     * Helper to create a test order.
     */
    protected function createOrder(
        string $paymentStatus = 'paid',
        string $fulfillmentStatus = 'order_placed',
        string $gateway = 'razorpay',
        float $subtotal = 50000,
        float $taxGst = 1500,
        float $discount = 0,
        float $priveDiscount = 0,
        ?string $memberId = null
    ): Order {
        $total = $subtotal - $discount - $priveDiscount + $taxGst;

        $order = Order::create([
            'order_number' => 'VRZ-TEST-' . strtoupper(bin2hex(random_bytes(4))),
            'user_id' => $this->customer->id,
            'client_name' => $this->customer->name,
            'client_email' => $this->customer->email,
            'client_phone' => $this->customer->phone,
            'shipping_address' => [
                'street' => '12 Atelier Boulevard',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
            ],
            'shipping_method' => 'armoured_transit',
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'prive_discount_amount' => $priveDiscount,
            'prive_member_id' => $memberId,
            'tax_gst' => $taxGst,
            'shipping_cost' => 0,
            'total_amount' => $total,
            'payment_gateway' => $gateway,
            'payment_status' => $paymentStatus,
            'fulfillment_status' => $fulfillmentStatus,
            'razorpay_payment_id' => $paymentStatus === 'paid' ? 'pay_test_' . bin2hex(random_bytes(6)) : null,
            'razorpay_order_id' => $paymentStatus === 'paid' ? 'order_test_' . bin2hex(random_bytes(6)) : null,
        ]);

        return $order;
    }

    /**
     * 1. Unpaid order cancellation cancels immediately, restores stock, no refund needed.
     */
    public function test_unpaid_order_cancellation_cancels_immediately_and_restores_stock(): void
    {
        Mail::fake();

        $order = $this->createOrder(
            paymentStatus: 'pending',
            fulfillmentStatus: 'order_placed',
            gateway: 'cod',
            subtotal: 50000,
            taxGst: 1500
        );

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $this->productA->update(['stock_quantity' => 4]);

        $response = $this->actingAs($this->customer)->post(route('order.cancel', $order->order_number), [
            'reason' => 'Ordered by mistake',
            'customer_notes' => 'Testing customer cancel note',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->productA->refresh();

        $this->assertEquals('cancelled', $order->fulfillment_status);
        $this->assertEquals('cancelled', $order->cancellation_status);
        $this->assertEquals('none', $order->refund_status);
        $this->assertEquals(5, $this->productA->stock_quantity);

        $cancellation = OrderCancellation::where('order_id', $order->id)->first();
        $this->assertNotNull($cancellation);
        $this->assertEquals(OrderCancellation::STATUS_COMPLETED, $cancellation->status);
        $this->assertEquals(0.0, (float) $cancellation->refund_amount);
        $this->assertEquals('cod_no_refund', $cancellation->refund_method);
        $this->assertTrue((bool) $cancellation->is_stock_restored);

        Mail::assertSent(CancellationNotificationMail::class);
    }

    /**
     * 2. Paid online order cancellation executes refund via Razorpay, restores stock, and creates credit note.
     */
    public function test_paid_order_cancellation_initiates_refund_and_generates_credit_note(): void
    {
        Mail::fake();

        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $this->productA->update(['stock_quantity' => 4]);

        $response = $this->actingAs($this->customer)->post(route('order.cancel', $order->order_number), [
            'reason' => 'Changed my mind',
            'customer_notes' => 'Decided on another collection',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->productA->refresh();

        $this->assertEquals('cancelled', $order->fulfillment_status);
        $this->assertEquals('cancelled', $order->cancellation_status);
        $this->assertEquals(5, $this->productA->stock_quantity);
        $this->assertEquals(51500.0, (float) $order->total_refunded);

        $cancellation = OrderCancellation::where('order_id', $order->id)->first();
        $this->assertNotNull($cancellation);
        $this->assertEquals(OrderCancellation::STATUS_COMPLETED, $cancellation->status);
        $this->assertEquals(51500.0, (float) $cancellation->refund_amount);
        $this->assertEquals('razorpay', $cancellation->refund_method);

        // Verify OrderRefund docket created
        $refund = OrderRefund::where('order_cancellation_id', $cancellation->id)->first();
        $this->assertNotNull($refund);
        $this->assertEquals(51500.0, (float) $refund->amount);
        $this->assertNotNull($refund->razorpay_refund_id);
        $this->assertEquals('refunded', $refund->status);

        // Verify Credit Note generated
        $creditNote = CreditNote::where('order_id', $order->id)->first();
        $this->assertNotNull($creditNote);
        $this->assertStringStartsWith('VRZ-CN-', $creditNote->credit_note_number);
        $this->assertEquals(50000.0, (float) $creditNote->taxable_amount);
        $this->assertEquals(1500.0, (float) $creditNote->gst_amount);
        $this->assertEquals(51500.0, (float) $creditNote->total_credit_amount);

        Mail::assertSent(CancellationNotificationMail::class);
        Mail::assertSent(RefundNotificationMail::class);
    }

    /**
     * 3. Packed order cancellation requires admin review (pending_review), stock NOT prematurely restored.
     */
    public function test_packed_order_cancellation_submits_for_admin_review_without_premature_stock_restoration(): void
    {
        Mail::fake();

        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'packed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $this->productA->update(['stock_quantity' => 4]);

        $response = $this->actingAs($this->customer)->post(route('order.cancel', $order->order_number), [
            'reason' => 'Delivery delay',
            'customer_notes' => 'Need it earlier for wedding',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->productA->refresh();

        // Fulfillment status remains packed while pending review
        $this->assertEquals('packed', $order->fulfillment_status);
        $this->assertEquals('pending_review', $order->cancellation_status);
        // Stock should NOT be incremented yet
        $this->assertEquals(4, $this->productA->stock_quantity);

        $cancellation = OrderCancellation::where('order_id', $order->id)->first();
        $this->assertNotNull($cancellation);
        $this->assertEquals(OrderCancellation::STATUS_PENDING_REVIEW, $cancellation->status);
        $this->assertFalse((bool) $cancellation->is_stock_restored);

        Mail::assertSent(CancellationNotificationMail::class);
    }

    /**
     * 4. Admin approves a pending review cancellation: restores stock, executes refund, issues credit note.
     */
    public function test_admin_approves_cancellation_restores_stock_executes_refund(): void
    {
        Mail::fake();

        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'packed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $this->productA->update(['stock_quantity' => 4]);

        $service = app(CancellationRefundService::class);
        $cancellation = $service->requestCancellation($order, 'Delivery delay', 'Need by tomorrow', null, $this->customer);

        $this->assertEquals(OrderCancellation::STATUS_PENDING_REVIEW, $cancellation->status);
        $this->assertEquals(4, $this->productA->fresh()->stock_quantity);

        // Admin approves
        $service->processApprovedCancellation($cancellation, $this->admin, 'Approved by head of packing bay');

        $order->refresh();
        $this->productA->refresh();
        $cancellation->refresh();

        $this->assertEquals(OrderCancellation::STATUS_COMPLETED, $cancellation->status);
        $this->assertEquals('cancelled', $order->fulfillment_status);
        $this->assertEquals(5, $this->productA->stock_quantity);
        $this->assertTrue((bool) $cancellation->is_stock_restored);
        $this->assertNotNull(CreditNote::where('order_id', $order->id)->first());

        Mail::assertSent(CancellationNotificationMail::class);
    }

    /**
     * 5. Admin rejects a pending cancellation: order remains packed, stock not touched, customer notified.
     */
    public function test_admin_rejects_cancellation_with_reason(): void
    {
        Mail::fake();

        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'packed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $this->productA->update(['stock_quantity' => 4]);

        $service = app(CancellationRefundService::class);
        $cancellation = $service->requestCancellation($order, 'Wrong product selected', null, null, $this->customer);

        // Admin rejects
        $service->rejectCancellation(
            $cancellation,
            'Consignment is already sealed in the armored vault vehicle for transit.',
            $this->admin,
            'Security lock active'
        );

        $order->refresh();
        $this->productA->refresh();
        $cancellation->refresh();

        $this->assertEquals(OrderCancellation::STATUS_REJECTED, $cancellation->status);
        $this->assertEquals('rejected', $order->cancellation_status);
        $this->assertEquals('packed', $order->fulfillment_status);
        $this->assertEquals(4, $this->productA->stock_quantity);
        $this->assertStringContainsString('armored vault', $cancellation->rejection_reason);

        Mail::assertSent(CancellationNotificationMail::class);
    }

    /**
     * 6. Shipped and delivered orders cannot be cancelled directly.
     */
    public function test_shipped_and_delivered_orders_cannot_be_cancelled(): void
    {
        $shippedOrder = $this->createOrder(fulfillmentStatus: 'shipped');
        $this->assertFalse($shippedOrder->canBeCancelled());

        $deliveredOrder = $this->createOrder(fulfillmentStatus: 'delivered');
        $this->assertFalse($deliveredOrder->canBeCancelled());

        $response = $this->actingAs($this->customer)->post(route('order.cancel', $shippedOrder->order_number), [
            'reason' => 'Changed my mind',
        ]);
        $response->assertSessionHas('error');
    }

    /**
     * 7. Partial order cancellation: cancel one item in a multi-item order with discounts, recalculate proportional refund.
     */
    public function test_partial_order_cancellation_recalculates_discounts_gst_and_updates_order(): void
    {
        Mail::fake();

        // Order with 2 products: ProductA (50,000) + ProductB (20,000) = 70,000 Subtotal
        // Voucher discount: 7,000 (10%)
        // Net taxable: 63,000
        // GST (3%): 1,890
        // Total: 64,890
        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 70000,
            taxGst: 1890,
            discount: 7000
        );

        $itemA = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $itemB = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productB->id,
            'product_title' => $this->productB->title,
            'product_sku' => $this->productB->sku,
            'unit_price' => 20000,
            'quantity' => 1,
            'line_total' => 20000,
            'status' => 'active',
        ]);

        $this->productA->update(['stock_quantity' => 4]);
        $this->productB->update(['stock_quantity' => 9]);

        // Cancel only ProductB (20,000)
        $response = $this->actingAs($this->customer)->post(route('order.cancel', $order->order_number), [
            'order_item_id' => $itemB->id,
            'reason' => 'Wrong product selected',
            'customer_notes' => 'Cancelling saree, keeping solitaire ring',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $itemA->refresh();
        $itemB->refresh();
        $this->productA->refresh();
        $this->productB->refresh();

        // ProductB cancelled and refunded
        $this->assertEquals('cancelled', $itemB->status);
        $this->assertNotNull($itemB->cancelled_at);
        $this->assertGreaterThan(0, (float) $itemB->refunded_amount);

        // ProductA remains active
        $this->assertEquals('active', $itemA->status);

        // Only ProductB stock is restored
        $this->assertEquals(4, $this->productA->stock_quantity);
        $this->assertEquals(10, $this->productB->stock_quantity);

        // Order is now partially cancelled
        $this->assertEquals('partially_cancelled', $order->cancellation_status);
        $this->assertEquals('order_placed', $order->fulfillment_status);

        // Credit Note generated specifically for ProductB
        $creditNote = CreditNote::where('order_id', $order->id)->first();
        $this->assertNotNull($creditNote);
        $this->assertEquals(1, count($creditNote->cancelled_items));
        $this->assertEquals($this->productB->sku, $creditNote->cancelled_items[0]['sku']);
    }

    /**
     * 8. Stock restoration is strictly idempotent and prevents double-restoration.
     */
    public function test_stock_restoration_is_strictly_idempotent(): void
    {
        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $this->productA->update(['stock_quantity' => 4]);

        $service = app(CancellationRefundService::class);
        $cancellation = $service->requestCancellation($order, 'Ordered by mistake', null, null, $this->customer);

        $this->assertEquals(5, $this->productA->fresh()->stock_quantity);
        $this->assertTrue((bool) $cancellation->fresh()->is_stock_restored);

        // Attempt second stock restoration
        $restoredSecondTime = $service->restoreStock($cancellation);
        $this->assertFalse($restoredSecondTime);
        $this->assertEquals(5, $this->productA->fresh()->stock_quantity);
    }

    /**
     * 9. VRAZIO PRIVÉ CLUB membership discount privilege is restored upon full order cancellation.
     */
    public function test_prive_club_privilege_restored_on_full_cancellation(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Gold Member',
            'slug' => 'gold-member',
            'tagline' => 'Signature Luxury',
            'validity_months' => 12,
            'price' => 499,
            'next_order_discount_percent' => 10,
            'next_order_max_discount' => 250,
            'next_order_min_cart' => 1499,
            'birthday_discount_percent' => 10,
            'birthday_max_discount' => 150,
            'birthday_min_cart' => 0,
            'benefits' => [
                'discount_percentage' => 10,
                'max_discount_cap' => 250,
                'min_cart_value' => 1499,
                'free_shipping' => true,
            ],
            'is_active' => true,
        ]);

        $membership = Membership::create([
            'user_id' => $this->customer->id,
            'membership_plan_id' => $plan->id,
            'membership_number' => 'VRZ-MBR-TEST999',
            'tier' => 'gold',
            'tier_name' => 'Gold Member',
            'fee_paid' => 499.00,
            'next_order_discount_percent' => 10,
            'next_order_max_discount' => 250,
            'next_order_min_cart' => 1499,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addYear(),
            'status' => 'active',
            'next_order_discount_used' => true, // patron used it on this order
            'used_discount_at' => now()->subHour(),
        ]);

        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500,
            priveDiscount: 250,
            memberId: $membership->membership_number
        );

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $service = app(CancellationRefundService::class);
        $cancellation = $service->requestCancellation($order, 'Changed my mind', null, null, $this->customer);

        $membership->refresh();
        $cancellation->refresh();

        $this->assertFalse((bool) $membership->next_order_discount_used);
        $this->assertTrue((bool) $cancellation->prive_discount_restored);
    }

    /**
     * 10. Razorpay webhook handles refund.processed and refund.failed idempotently.
     */
    public function test_razorpay_webhook_handles_refund_events(): void
    {
        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        $service = app(CancellationRefundService::class);
        $cancellation = $service->requestCancellation($order, 'Ordered by mistake', null, null, $this->customer);

        $refund = OrderRefund::where('order_cancellation_id', $cancellation->id)->first();
        $this->assertNotNull($refund);

        // Simulate Razorpay refund.processed webhook
        $webhookPayload = [
            'event' => 'refund.processed',
            'payload' => [
                'refund' => [
                    'entity' => [
                        'id' => $refund->razorpay_refund_id,
                        'payment_id' => $order->razorpay_payment_id,
                        'amount' => 5150000, // paise
                        'status' => 'processed',
                    ],
                ],
            ],
        ];

        $rawPayload = json_encode($webhookPayload, JSON_UNESCAPED_SLASHES);
        $secret = 'test_webhook_secret_key';
        config(['services.razorpay.webhook_secret' => $secret]);
        $sig = hash_hmac('sha256', $rawPayload, $secret);

        $response = $this->call('POST', route('api.razorpay.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $sig,
        ], $rawPayload);
        $response->assertOk();

        $refund->refresh();
        $this->assertEquals('refunded', $refund->status);
        $this->assertNotNull($refund->completed_at);
    }

    /**
     * 11. Live cancellation eligibility endpoint returns accurate breakdown for customer modal.
     */
    public function test_cancel_eligibility_endpoint_returns_live_breakdown(): void
    {
        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->customer)->getJson("/orders/{$order->order_number}/cancel-eligibility");
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'eligibility' => [
                'eligible' => true,
                'requires_review' => false,
                'refund_amount' => 51500.0,
                'refund_method' => 'razorpay',
            ],
        ]);
    }

    /**
     * 12. Credit Note web view and PDF generation render cleanly without errors.
     */
    public function test_credit_note_web_view_and_pdf_render(): void
    {
        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_title' => $this->productA->title,
            'product_sku' => $this->productA->sku,
            'unit_price' => 50000,
            'quantity' => 1,
            'line_total' => 50000,
            'status' => 'active',
        ]);

        $service = app(CancellationRefundService::class);
        $cancellation = $service->requestCancellation($order, 'Ordered by mistake', null, null, $this->customer);

        $creditNote = CreditNote::where('order_id', $order->id)->first();
        $this->assertNotNull($creditNote);

        // Web view
        $viewResponse = $this->actingAs($this->customer)->get(route('credit_note.show', $creditNote->credit_note_number));
        $viewResponse->assertOk();
        $viewResponse->assertSee($creditNote->credit_note_number);
        $viewResponse->assertSee('Official Credit Note');

        // PDF download
        $pdfResponse = $this->actingAs($this->customer)->get(route('credit_note.pdf', $creditNote->credit_note_number));
        $pdfResponse->assertOk();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    /**
     * 13. Admin can access Filament order cancellations resource table and view page without rendering error.
     */
    public function test_admin_can_access_filament_order_cancellations_page(): void
    {
        $order = $this->createOrder(
            paymentStatus: 'paid',
            fulfillmentStatus: 'order_placed',
            gateway: 'razorpay',
            subtotal: 50000,
            taxGst: 1500
        );

        $service = app(CancellationRefundService::class);
        $cancellation = $service->requestCancellation($order, 'Ordered by mistake', null, null, $this->customer);

        // Index table
        $indexResponse = $this->actingAs($this->admin)->get(route('filament.admin.resources.order-cancellations.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee($cancellation->cancellation_number);

        // View record
        $viewResponse = $this->actingAs($this->admin)->get(route('filament.admin.resources.order-cancellations.view', ['record' => $cancellation->id]));
        $viewResponse->assertOk();
        $viewResponse->assertSee($cancellation->cancellation_number);
    }
}
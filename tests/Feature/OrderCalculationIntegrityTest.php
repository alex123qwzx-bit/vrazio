<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CancellationRefundService;
use App\Services\CartService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCalculationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test cart and checkout calculations for product with voucher (matching user screenshot scenario).
     * Product: Sunglasses (Base: 1,600, GST: 18%)
     * Voucher: HANDY1000 (10% off)
     * Subtotal: 1,600
     * Discount: 160
     * Taxable: 1,440
     * GST (18% on 1,440): 259.20
     * Final Total: 1,699.20
     */
    public function test_cart_and_checkout_calculation_balances_perfectly(): void
    {
        $user = User::factory()->create([
            'phone' => '9876543210',
            'phone_verified_at' => now(),
        ]);

        $product = Product::factory()->create([
            'title' => 'Sunglasses',
            'price' => 2000.0,
            'discount_percent' => 20.0, // sale_price = 1600
            'gst_percent' => 18.0,
            'stock_quantity' => 10,
        ]);

        $voucher = Voucher::create([
            'code' => 'HANDY1000',
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'min_spend' => 1000.0,
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cartService->clear();
        $cartService->addItem($product, 1);
        $res = $cartService->applyVoucher('HANDY1000');
        $this->assertTrue($res['success']);

        $this->assertEquals(1600.0, $cartService->getSubtotal());
        $this->assertEquals(160.0, $cartService->getDiscount());
        $this->assertEquals(259.20, $cartService->getGst());
        $this->assertEquals(1699.20, $cartService->getTotal('standard'));

        // Checkout view renders properly without "(Included)" on GST and shows correct piece pluralization
        $response = $this->actingAs($user)->withSession([
            'order_phone_verified' => $user->phone,
        ])->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Order Dossier (1 Piece)');
        $response->assertDontSee('Order Dossier (1 Pieces)');
        $response->assertSee('Estimated GST:');
        $response->assertDontSee('Estimated GST (Included):');
        $response->assertSee('₹1,699.20');
    }

    /**
     * Test order placement persists exact financial figures and confirmation page displays them accurately.
     */
    public function test_order_confirmation_displays_discount_and_balances(): void
    {
        $user = User::factory()->create([
            'phone' => '9876543210',
            'phone_verified_at' => now(),
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-TESTCALC99',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => $user->phone,
            'shipping_address' => [
                'street' => 'Marine Lines',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400020',
            ],
            'shipping_method' => 'standard',
            'subtotal' => 1600.00,
            'voucher_code' => 'HANDY1000',
            'discount_amount' => 160.00,
            'tax_gst' => 259.20,
            'shipping_cost' => 0.00,
            'total_amount' => 1699.20,
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'fulfillment_status' => 'order_placed',
        ]);

        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 1600]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => 'Sunglasses',
            'product_sku' => 'VRZ-SUN',
            'unit_price' => 1600.00,
            'quantity' => 1,
            'line_total' => 1600.00,
        ]);

        $response = $this->actingAs($user)->get(route('order.confirmation', ['orderNumber' => $order->order_number]));
        $response->assertStatus(200);
        $response->assertSee('Subtotal:');
        $response->assertSee('₹1,600.00');
        $response->assertSee('Privilege Discount (HANDY1000):');
        $response->assertSee('-₹160.00');
        $response->assertSee('Tax / GST:');
        $response->assertSee('₹259.20');
        $response->assertDontSee('GST (3%):');
        $response->assertSee('Total Paid:');
        $response->assertSee('₹1,699.20');
    }

    /**
     * Test invoice displays actual discount amount and does not hardcode ₹0.00 or 3%.
     */
    public function test_invoice_displays_actual_discount_amount(): void
    {
        $user = User::factory()->create([
            'phone' => '9876543210',
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-TESTINV99',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => $user->phone,
            'shipping_address' => [
                'street' => 'Colaba',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400005',
            ],
            'shipping_method' => 'standard',
            'subtotal' => 1600.00,
            'voucher_code' => 'HANDY1000',
            'discount_amount' => 160.00,
            'tax_gst' => 259.20,
            'shipping_cost' => 0.00,
            'total_amount' => 1699.20,
            'payment_gateway' => 'razorpay',
            'payment_status' => 'paid',
            'fulfillment_status' => 'delivered',
        ]);

        $response = $this->actingAs($user)->get(route('order.invoice', $order->order_number));
        $response->assertStatus(200);
        $response->assertSee('IGST (18.0%)');
        $response->assertDontSee('Tax / GST (3%)');
        $response->assertSee('-₹160.00');
        $response->assertSee('₹1,699.20');
    }

    /**
     * Test single item cancellation proportional refund includes tax.
     */
    public function test_item_cancellation_refund_includes_tax(): void
    {
        $order = Order::create([
            'order_number' => 'VRZ-TESTREFUND99',
            'client_name' => 'Patron',
            'client_email' => 'patron@example.com',
            'client_phone' => '9876543210',
            'shipping_address' => ['state' => 'Maharashtra'],
            'shipping_method' => 'standard',
            'subtotal' => 1600.00,
            'discount_amount' => 160.00,
            'tax_gst' => 259.20,
            'shipping_cost' => 0.00,
            'total_amount' => 1699.20,
            'payment_gateway' => 'razorpay',
            'payment_status' => 'paid',
            'fulfillment_status' => 'order_placed',
        ]);

        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 1600]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => 'Sunglasses',
            'product_sku' => 'VRZ-SUN',
            'unit_price' => 1600.00,
            'quantity' => 1,
            'line_total' => 1600.00,
        ]);

        $service = app(CancellationRefundService::class);
        $refundAmount = $service->calculateItemRefundAmount($order, $item);

        // Refund must be 1440 (net) + 259.20 (gst) = 1699.20
        $this->assertEquals(1699.20, $refundAmount);
    }

    /**
     * Test amount in words and tax breakdown matching Image 3 specs.
     */
    public function test_amount_in_words_and_tax_breakdown_matches_image_3_specs(): void
    {
        $order = new Order([
            'order_number' => 'VRZ-TZ5SES1P',
            'client_name' => 'AMAR PANDAY',
            'client_phone' => '7505933907',
            'shipping_address' => [
                'street' => 'PEETAL BASTI SURAJ NAGAR',
                'city' => 'Moradabad',
                'state' => 'Uttar Pradesh',
                'pincode' => '244001',
            ],
            'subtotal' => 1600.00,
            'tax_gst' => 288.00,
            'shipping_cost' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => 1888.00,
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
        ]);

        $this->assertEquals('One Thousand Eight Hundred Eighty-Eight Only', $order->getAmountInWords());
        $this->assertFalse($order->isInterState());
        $this->assertEquals('09', $order->getCustomerStateCode());
        $this->assertEquals('Uttar Pradesh (09)', $order->getPlaceOfSupply());
        $this->assertEquals(9.0, $order->getCgstRate());
        $this->assertEquals(9.0, $order->getSgstRate());
        $this->assertEquals(144.00, $order->getCgstAmount());
        $this->assertEquals(144.00, $order->getSgstAmount());

        $item = new OrderItem([
            'product_title' => 'Sunglasses',
            'product_sku' => 'VRZ-SUN01',
            'unit_price' => 1600.00,
            'quantity' => 1,
            'line_total' => 1600.00,
        ]);
        $this->assertEquals('9004', $item->getHsnCode());

        // Test Inter-state order
        $interStateOrder = new Order([
            'shipping_address' => [
                'state' => 'Maharashtra',
            ],
            'subtotal' => 1600.00,
            'tax_gst' => 288.00,
            'total_amount' => 1888.00,
        ]);
        $this->assertTrue($interStateOrder->isInterState());
        $this->assertEquals('27', $interStateOrder->getCustomerStateCode());
        $this->assertEquals('Maharashtra (27)', $interStateOrder->getPlaceOfSupply());
        $this->assertEquals(18.0, $interStateOrder->getIgstRate());
        $this->assertEquals(288.00, $interStateOrder->getIgstAmount());
    }

    /**
     * Test invoice PDF compiles with DomPDF, renders Image 3 data, and fits on single A4 page.
     */
    public function test_invoice_pdf_matches_image_3_and_fits_single_page(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Sunglasses',
            'sku' => 'VRZ-SUN01',
            'price' => 1600.00,
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-TZ5SES1P',
            'user_id' => $user->id,
            'client_name' => 'AMAR PANDAY',
            'client_email' => 'amar@example.com',
            'client_phone' => '7505933907',
            'shipping_address' => [
                'street' => 'PEETAL BASTI SURAJ NAGAR',
                'city' => 'Moradabad',
                'state' => 'Uttar Pradesh',
                'pincode' => '244001',
            ],
            'shipping_method' => 'standard',
            'subtotal' => 1600.00,
            'tax_gst' => 288.00,
            'shipping_cost' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => 1888.00,
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'fulfillment_status' => 'order_placed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => 'Sunglasses',
            'product_sku' => 'VRZ-SUN01',
            'unit_price' => 1600.00,
            'quantity' => 1,
            'line_total' => 1600.00,
        ]);

        $response = $this->actingAs($user)->get(route('order.invoice.download', $order->order_number));
        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        // Verify PDF content generated
        $content = $response->getContent();
        $this->assertNotEmpty($content);

        // Check DomPDF canvas page count
        $pdf = Pdf::loadView('invoices.pdf', compact('order'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
                'dpi' => 96,
            ]);
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $pageCount = $canvas->get_page_count();
        $this->assertEquals(1, $pageCount, 'Invoice PDF should fit on a single A4 page with no cut-outs or overflow spills.');
    }
}

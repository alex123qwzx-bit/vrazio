<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteThemeSetting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderCancellationAndCodTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_cancel_active_order_and_restore_stock(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 1000,
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-TESTCANCEL1',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => '9876543210',
            'shipping_address' => [
                'street' => '123 Atelier Street',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
            ],
            'shipping_method' => 'standard',
            'subtotal' => 1000,
            'tax_gst' => 30,
            'shipping_cost' => 0,
            'total_amount' => 1030,
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'fulfillment_status' => 'order_placed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_sku' => $product->sku,
            'unit_price' => 1000,
            'quantity' => 2,
            'line_total' => 2000,
        ]);

        $product->update(['stock_quantity' => 8]);

        $this->assertTrue($order->canBeCancelled());

        $response = $this->actingAs($user)->post(route('order.cancel', $order->order_number));
        $response->assertSessionHas('success');

        $order->refresh();
        $product->refresh();

        $this->assertEquals('cancelled', $order->fulfillment_status);
        $this->assertEquals(10, $product->stock_quantity);
        $this->assertFalse($order->canBeCancelled());
    }

    public function test_cannot_cancel_shipped_or_delivered_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'VRZ-TESTSHIPPED',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => '9876543210',
            'shipping_address' => ['state' => 'Delhi'],
            'shipping_method' => 'standard',
            'subtotal' => 500,
            'tax_gst' => 15,
            'shipping_cost' => 0,
            'total_amount' => 515,
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'fulfillment_status' => 'shipped',
        ]);

        $this->assertFalse($order->canBeCancelled());

        $response = $this->actingAs($user)->post(route('order.cancel', $order->order_number));
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('shipped', $order->fulfillment_status);
    }

    public function test_cod_state_rates_calculation(): void
    {
        SiteThemeSetting::setSetting('cod_state_rates', [
            'default' => 50,
            'Maharashtra' => 0,
            'Uttar Pradesh' => 99,
            'Bihar' => 150,
        ]);

        $this->assertEquals(0.0, CartService::getCodFeeForState('Maharashtra'));
        $this->assertEquals(99.0, CartService::getCodFeeForState('Uttar Pradesh'));
        $this->assertEquals(99.0, CartService::getCodFeeForState('uttar pradesh'));
        $this->assertEquals(150.0, CartService::getCodFeeForState('Bihar'));
        $this->assertEquals(50.0, CartService::getCodFeeForState('Goa'));
        $this->assertEquals(50.0, CartService::getCodFeeForState(null));
    }

    public function test_order_confirmation_page_displays_whatsapp_mandatory_card(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'VRZ-TESTWA123',
            'user_id' => $user->id,
            'client_name' => 'Aditya Verma',
            'client_email' => 'aditya@example.com',
            'client_phone' => '9876543210',
            'shipping_address' => [
                'street' => '10 Marine Drive',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400020',
            ],
            'shipping_method' => 'standard',
            'subtotal' => 5000,
            'tax_gst' => 150,
            'shipping_cost' => 0,
            'total_amount' => 5150,
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'fulfillment_status' => 'order_placed',
        ]);

        $response = $this->actingAs($user)->get(route('order.confirmation', ['orderNumber' => $order->order_number]));
        $response->assertOk();
        $response->assertSee('Confirm Order on WhatsApp Support');
        $response->assertSee('87912 27210');
        $response->assertSee('wa.me/918791227210');
    }

    public function test_invoice_pdf_compiles_successfully(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create([
            'title' => 'Solitaire Diamond Inscribed Ring',
            'sku' => 'VRZ-SL-01',
            'price' => 45000,
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-PDFTEST01',
            'user_id' => $user->id,
            'client_name' => 'Kavita Singhal',
            'client_email' => 'kavita@example.com',
            'client_phone' => '9876543210',
            'shipping_address' => [
                'street' => '42 Heritage Enclave',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302001',
            ],
            'shipping_method' => 'standard',
            'subtotal' => 45000,
            'tax_gst' => 1350,
            'shipping_cost' => 0,
            'total_amount' => 46350,
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'fulfillment_status' => 'order_placed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_sku' => $product->sku,
            'unit_price' => 45000,
            'quantity' => 1,
            'line_total' => 45000,
        ]);

        $response = $this->actingAs($user)->get(route('order.invoice.download', $order->order_number));
        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertNotEmpty($response->getContent());
    }
}

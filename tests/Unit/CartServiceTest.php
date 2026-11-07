<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_calculates_jewellery_gst_at_three_percent(): void
    {
        $service = new CartService();
        $product = Product::factory()->create([
            'price' => 100000,
            'sale_price' => null,
            'stock_quantity' => 10,
        ]);

        $service->addItem($product, 1, 'Size 14 (US 7)');

        $subtotal = $service->getSubtotal();
        $gst = $service->getGst();

        $this->assertEquals(100000.0, $subtotal);
        $this->assertEquals(3000.0, $gst); // 3% of 100,000
        $this->assertEquals(103000.0, $service->getTotal('standard'));
    }

    public function test_cart_fetches_fresh_database_prices_preventing_stale_prices(): void
    {
        $service = new CartService();
        $product = Product::factory()->create([
            'price' => 50000,
            'sale_price' => null,
            'stock_quantity' => 5,
        ]);

        $service->addItem($product, 1);
        $this->assertEquals(50000.0, $service->getSubtotal());

        // Admin updates the gold/product price in the database
        $product->update(['price' => 65000]);

        // Cart items should dynamically reflect the fresh database price (BUS-03)
        $this->assertEquals(65000.0, $service->getSubtotal());
    }

    public function test_cart_preserves_ring_size_selection(): void
    {
        $service = new CartService();
        $product = Product::factory()->create([
            'price' => 25000,
            'stock_quantity' => 5,
        ]);

        $service->addItem($product, 1, 'Size 16 (US 7.75)');
        $items = $service->getItems();

        $firstItem = reset($items);
        $this->assertNotEmpty($firstItem);
        $this->assertEquals('Size 16 (US 7.75)', $firstItem['size']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryAndGstTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test price calculation formula:
     * Base Price = ₹10,000, Discount = 20%, GST = 18%
     * Discount Amount = ₹2,000
     * Discounted Price = ₹8,000
     * GST Amount = ₹1,440
     * Final Customer Price = ₹9,440
     */
    public function test_price_calculation_formula_matches_exact_specification(): void
    {
        $product = new Product([
            'sku' => 'VRZ-TEST-CALC',
            'title' => 'Imperial Royal Tourbillon',
            'category' => 'watches',
            'price' => 10000.00,
            'discount_percent' => 20.00,
            'gst_percent' => 18.00,
        ]);

        $this->assertEquals(2000.00, $product->calculateDiscountAmount());
        $this->assertEquals(8000.00, $product->calculateDiscountedPrice());
        $this->assertEquals(1440.00, $product->calculateGstAmount());
        $this->assertEquals(9440.00, $product->calculateFinalPrice());
        $this->assertEquals('20% OFF', $product->discount_badge);
        $this->assertEquals('₹9,440.00', $product->formatted_final_price);
    }

    /**
     * Test zero discount calculation:
     * Base Price = ₹5,000, Discount = 0%, GST = 12%
     * Final Price = 5,000 + (5,000 * 0.12) = ₹5,600
     * Discount badge is null.
     */
    public function test_zero_discount_product_calculation(): void
    {
        $product = new Product([
            'sku' => 'VRZ-TEST-NODISC',
            'title' => 'Pure Silk Royal Bandhgala',
            'category' => 'clothing',
            'price' => 5000.00,
            'discount_percent' => 0.00,
            'gst_percent' => 12.00,
        ]);

        $this->assertEquals(0.00, $product->calculateDiscountAmount());
        $this->assertEquals(5000.00, $product->calculateDiscountedPrice());
        $this->assertEquals(600.00, $product->calculateGstAmount());
        $this->assertEquals(5600.00, $product->calculateFinalPrice());
        $this->assertNull($product->discount_badge);
        $this->assertFalse($product->hasDiscount());
    }

    /**
     * Test saving model automatically calculates and stores final_price.
     */
    public function test_saving_product_automatically_computes_final_price(): void
    {
        $product = Product::create([
            'sku' => 'VRZ-TEST-' . rand(1000, 9999),
            'title' => 'Royal Heritage Solitaire Ring',
            'category' => 'jewelry',
            'price' => 20000.00,
            'discount_percent' => 10.00,
            'gst_percent' => 3.00,
            'stock_quantity' => 5,
        ]);

        // 20000 - 10% (2000) = 18000 (sale price) + 3% (540) = 18540 (final customer price)
        $this->assertEquals(18540.00, (float) $product->final_price);
        $this->assertEquals(18000.00, (float) $product->sale_price);

        // Clean up
        $product->delete();
    }

    /**
     * Test the 4 primary categories in collections filtering.
     */
    public function test_collections_filtering_by_four_authoritative_categories(): void
    {
        // 1. Jewelry
        $resJewelry = $this->get('/collections?category=jewelry');
        $resJewelry->assertStatus(200);

        // 2. Clothing
        $resClothing = $this->get('/collections?category=clothing');
        $resClothing->assertStatus(200);

        // 3. Watches
        $resWatches = $this->get('/collections?category=watches');
        $resWatches->assertStatus(200);

        // 4. Footwear
        $resFootwear = $this->get('/collections?category=footwear');
        $resFootwear->assertStatus(200);
    }

    /**
     * Test header and navigation contain the 4 categories and Privé Club link.
     */
    public function test_header_contains_four_categories_and_prive_club(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Jewelry');
        $response->assertSee('Clothing');
        $response->assertSee('Watches');
        $response->assertSee('Footwear');
        $response->assertSee('PRIVÉ CLUB');
    }
}

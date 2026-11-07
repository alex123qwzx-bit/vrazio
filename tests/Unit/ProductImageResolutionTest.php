<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;

class ProductImageResolutionTest extends TestCase
{
    public function test_resolves_filament_relative_storage_path(): void
    {
        $resolved = Product::resolveImageUrl('products/wakfoBYjzBeimmUffpfjoa2BFNmlbvwwATLlJVZn.jpg');
        $this->assertStringContainsString('/storage/products/wakfoBYjzBeimmUffpfjoa2BFNmlbvwwATLlJVZn.jpg', $resolved);
    }

    public function test_leaves_external_url_untouched(): void
    {
        $url = 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80';
        $resolved = Product::resolveImageUrl($url);
        $this->assertSame($url, $resolved);
    }

    public function test_falls_back_when_empty_or_null(): void
    {
        $resolved = Product::resolveImageUrl(null);
        $this->assertStringContainsString('images.unsplash.com', $resolved);

        $resolvedEmpty = Product::resolveImageUrl('');
        $this->assertStringContainsString('images.unsplash.com', $resolvedEmpty);
    }

    public function test_product_primary_image_and_image_urls_accessors(): void
    {
        $product = new Product([
            'title' => 'Solitaire Ring',
            'price' => 50000,
            'images' => [
                'products/custom_upload.jpg',
                'https://cdn.example.com/ring2.jpg',
            ],
        ]);

        $this->assertStringContainsString('/storage/products/custom_upload.jpg', $product->primary_image);

        $urls = $product->image_urls;
        $this->assertCount(2, $urls);
        $this->assertStringContainsString('/storage/products/custom_upload.jpg', $urls[0]);
        $this->assertSame('https://cdn.example.com/ring2.jpg', $urls[1]);
    }
}

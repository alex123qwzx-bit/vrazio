<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Session key for cart storage.
     */
    private const SESSION_KEY = 'cart';

    /**
     * Get all items currently in the cart with fresh live prices from the database.
     *
     * @return array<string, array{id: int, title: string, slug: string, price: float, image: ?string, quantity: int, size: ?string}>
     */
    public function getItems(): array
    {
        $rawCart = Session::get(self::SESSION_KEY, []);
        if (empty($rawCart)) {
            return [];
        }

        $productIds = array_unique(array_filter(array_column($rawCart, 'id')));
        if (empty($productIds)) {
            return [];
        }

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $freshCart = [];

        foreach ($rawCart as $key => $item) {
            $productId = (int) ($item['id'] ?? 0);
            $product = $products->get($productId);

            if (! $product) {
                continue; // Product has been removed from catalog
            }

            $price = (float) ($product->sale_price ?? $product->price);

            $freshCart[(string) $key] = [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price' => $price,
                'image' => $product->primary_image,
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'size' => $item['size'] ?? null,
            ];
        }

        return $freshCart;
    }

    /**
     * Add a product to the cart (or increment quantity if already present).
     */
    public function addItem(Product $product, int $quantity = 1, ?string $size = null): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $productId = $product->id;
        $cleanSize = $size ? trim($size) : null;
        $cartKey = $cleanSize ? "{$productId}_" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cleanSize) : (string) $productId;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
            if ($cleanSize) {
                $cart[$cartKey]['size'] = $cleanSize;
            }
        } else {
            $cart[$cartKey] = [
                'id' => $productId,
                'quantity' => $quantity,
                'size' => $cleanSize,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Update the quantity of a specific cart item.
     */
    public function updateQuantity(string|int $cartKey, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $key = (string) $cartKey;

        if (!isset($cart[$key])) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeItem($key);
            return;
        }

        $cart[$key]['quantity'] = $quantity;
        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Remove a specific item from the cart.
     */
    public function removeItem(string|int $cartKey): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $key = (string) $cartKey;

        unset($cart[$key]);
        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Calculate the subtotal (sum of live price × quantity for all items).
     */
    public function getSubtotal(): float
    {
        return array_reduce(
            $this->getItems(),
            fn (float $carry, array $item): float => $carry + ($item['price'] * $item['quantity']),
            0.0
        );
    }

    /**
     * Calculate GST amount (3% of subtotal for jewellery).
     */
    public function getGst(): float
    {
        $rate = (float) config('services.gst_rate', 0.03);

        return round($this->getSubtotal() * $rate, 2);
    }

    /**
     * Get shipping cost based on the selected shipping method.
     */
    public function getShippingCost(string $method): float
    {
        return (float) config("services.shipping.{$method}.cost", 0);
    }

    /**
     * Calculate the total (subtotal + GST + shipping).
     */
    public function getTotal(string $shippingMethod = 'standard'): float
    {
        return round(
            $this->getSubtotal() + $this->getGst() + $this->getShippingCost($shippingMethod),
            2
        );
    }

    /**
     * Get the total number of items in the cart (sum of quantities).
     */
    public function getItemCount(): int
    {
        return array_reduce(
            $this->getItems(),
            fn (int $carry, array $item): int => $carry + $item['quantity'],
            0
        );
    }

    /**
     * Check if the cart is empty.
     */
    public function isEmpty(): bool
    {
        return count($this->getItems()) === 0;
    }
}

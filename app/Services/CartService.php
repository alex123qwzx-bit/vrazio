<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\SiteThemeSetting;
use App\Models\Voucher;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Complete list of all 28 States and 8 Union Territories in India.
     */
    public const array INDIAN_STATES = [
        'Andaman and Nicobar Islands',
        'Andhra Pradesh',
        'Arunachal Pradesh',
        'Assam',
        'Bihar',
        'Chandigarh',
        'Chhattisgarh',
        'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi',
        'Goa',
        'Gujarat',
        'Haryana',
        'Himachal Pradesh',
        'Jammu and Kashmir',
        'Jharkhand',
        'Karnataka',
        'Kerala',
        'Ladakh',
        'Lakshadweep',
        'Madhya Pradesh',
        'Maharashtra',
        'Manipur',
        'Meghalaya',
        'Mizoram',
        'Nagaland',
        'Odisha',
        'Puducherry',
        'Punjab',
        'Rajasthan',
        'Sikkim',
        'Tamil Nadu',
        'Telangana',
        'Tripura',
        'Uttar Pradesh',
        'Uttarakhand',
        'West Bengal',
    ];

    /**
     * Get COD fee for a given state from admin settings.
     */
    public static function getCodFeeForState(?string $state): float
    {
        $rates = SiteThemeSetting::getSetting('cod_state_rates', []);
        if (! is_array($rates)) {
            $rates = json_decode((string)$rates, true) ?: [];
        }

        $defaultFee = (float) ($rates['default'] ?? 0);

        if (empty($state)) {
            return $defaultFee;
        }

        $cleanedState = trim($state);

        // Direct match
        if (isset($rates[$cleanedState])) {
            return (float) $rates[$cleanedState];
        }

        // Case-insensitive match
        foreach ($rates as $st => $fee) {
            if (strcasecmp((string)$st, $cleanedState) === 0) {
                return (float) $fee;
            }
        }

        return $defaultFee;
    }

    /**
     * Session key for cart storage.
     */
    private const SESSION_KEY = 'cart';
    private const VOUCHER_SESSION_KEY = 'cart_voucher';

    /**
     * Get all items currently in the cart with fresh live prices from the database.
     *
     * @return array<string, array{id: int, title: string, slug: string, price: float, base_price: float, gst_percent: float, discount_percent: float, image: ?string, quantity: int, size: ?string}>
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

            // Effective taxable price (Base price minus product-level discount if any)
            $price = (float) ($product->calculateDiscountedPrice() ?? $product->price);

            $freshCart[(string) $key] = [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price' => $price,
                'base_price' => (float) $product->price,
                'gst_percent' => (float) ($product->gst_percent ?? 3.0),
                'discount_percent' => (float) ($product->discount_percent ?? 0.0),
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
        $this->removeVoucher();
    }

    /**
     * Apply a voucher code to the current cart.
     *
     * @return array{success: bool, message: string, voucher?: Voucher, discount?: float, subtotal?: float, gst?: float, total?: float}
     */
    public function applyVoucher(string $code): array
    {
        $code = strtoupper(trim($code));
        if (empty($code)) {
            return [
                'success' => false,
                'message' => 'Please enter a valid voucher code.',
            ];
        }

        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher || ! $voucher->isActive()) {
            return [
                'success' => false,
                'message' => "Voucher code '{$code}' is invalid, inactive, or expired.",
            ];
        }

        $subtotal = $this->getSubtotal();

        // Check minimum spend
        if ($voucher->min_spend > 0 && $subtotal < (float) $voucher->min_spend) {
            return [
                'success' => false,
                'message' => "Voucher requires a minimum spend of ₹" . number_format((float) $voucher->min_spend, 2) . ". Current subtotal is ₹" . number_format($subtotal, 2) . ".",
            ];
        }

        // Check product exclusivity
        if ($voucher->product_id) {
            $hasProduct = false;
            foreach ($this->getItems() as $item) {
                if ((int) $item['id'] === (int) $voucher->product_id) {
                    $hasProduct = true;
                    break;
                }
            }

            if (! $hasProduct) {
                $productTitle = $voucher->product?->title ?? 'the specified atelier piece';
                return [
                    'success' => false,
                    'message' => "This voucher is exclusively valid for {$productTitle}.",
                ];
            }
        }

        // Check usage limit
        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            return [
                'success' => false,
                'message' => "This voucher code has reached its maximum redemption limit.",
            ];
        }

        Session::put(self::VOUCHER_SESSION_KEY, $voucher->code);

        return [
            'success' => true,
            'message' => "Voucher '{$voucher->code}' successfully applied! Discount: " . $voucher->discount_label . ".",
            'voucher' => $voucher,
            'discount' => $this->getDiscount(),
            'subtotal' => $subtotal,
            'gst' => $this->getGst(),
            'total' => $this->getTotal(),
        ];
    }

    /**
     * Remove applied voucher from cart.
     */
    public function removeVoucher(): void
    {
        Session::forget(self::VOUCHER_SESSION_KEY);
    }

    /**
     * Retrieve currently applied valid voucher or null.
     */
    public function getVoucher(): ?Voucher
    {
        $code = Session::get(self::VOUCHER_SESSION_KEY);
        if (empty($code)) {
            return null;
        }

        $voucher = Voucher::where('code', $code)->first();
        if (! $voucher || ! $voucher->isActive()) {
            $this->removeVoucher();
            return null;
        }

        $subtotal = $this->getSubtotal();
        if ($voucher->min_spend > 0 && $subtotal < (float) $voucher->min_spend) {
            $this->removeVoucher();
            return null;
        }

        if ($voucher->product_id) {
            $hasProduct = false;
            foreach ($this->getItems() as $item) {
                if ((int) $item['id'] === (int) $voucher->product_id) {
                    $hasProduct = true;
                    break;
                }
            }
            if (! $hasProduct) {
                $this->removeVoucher();
                return null;
            }
        }

        return $voucher;
    }

    /**
     * Calculate discount amount provided by the active voucher.
     */
    public function getDiscount(): float
    {
        $voucher = $this->getVoucher();
        if (! $voucher) {
            return 0.0;
        }

        if ($voucher->product_id) {
            $eligibleTotal = 0.0;
            foreach ($this->getItems() as $item) {
                if ((int) $item['id'] === (int) $voucher->product_id) {
                    $eligibleTotal += ($item['price'] * $item['quantity']);
                }
            }
            return $voucher->calculateDiscount($eligibleTotal);
        }

        return $voucher->calculateDiscount($this->getSubtotal());
    }

    /**
     * Calculate active VRAZIO PRIVÉ CLUB membership discount if applicable.
     */
    public function getPriveDiscount(): float
    {
        if (! auth()->check()) {
            return 0.0;
        }

        $user = auth()->user();
        $membership = $user->activeMembership();
        if (! $membership) {
            return 0.0;
        }

        $plan = $membership->plan;
        if (! $plan) {
            return 0.0;
        }

        $subtotal = $this->getSubtotal();

        // 1. One-time Next Order Privilege
        if (! $membership->next_order_discount_used) {
            if ($subtotal >= (float) $plan->next_order_min_cart) {
                $discount = $subtotal * ((float) $plan->next_order_discount_percent / 100.0);
                return round(min($discount, (float) $plan->next_order_max_discount), 2);
            }
        }

        // 2. Birthday month privilege
        if ($user->date_of_birth && (float) $plan->birthday_discount_percent > 0) {
            $birthMonth = (int) $user->date_of_birth->format('n');
            $currentMonth = (int) now()->format('n');
            $currentYear = (int) now()->format('Y');

            if ($birthMonth === $currentMonth && (int) $membership->birthday_coupon_used_year !== $currentYear) {
                if ($subtotal >= (float) $plan->birthday_min_cart) {
                    $bDiscount = $subtotal * ((float) $plan->birthday_discount_percent / 100.0);
                    return round(min($bDiscount, (float) $plan->birthday_max_discount), 2);
                }
            }
        }

        return 0.0;
    }

    /**
     * Get effective combined discount avoiding voucher stacking conflict.
     */
    public function getTotalDiscount(): float
    {
        $voucherDisc = $this->getDiscount();
        $priveDisc = $this->getPriveDiscount();

        if ($voucherDisc > 0 && $priveDisc > 0) {
            // Apply higher privilege benefit
            return max($voucherDisc, $priveDisc);
        }

        return round($voucherDisc + $priveDisc, 2);
    }

    /**
     * Calculate the subtotal (sum of customer GST-inclusive price × quantity for all items).
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
     * Calculate GST amount across items based on their product GST rates.
     */
    public function getGst(): float
    {
        $subtotal = $this->getSubtotal();
        if ($subtotal <= 0) {
            return 0.0;
        }

        $orderDiscount = $this->getTotalDiscount();
        $taxableRatio = max(0.0, ($subtotal - $orderDiscount) / $subtotal);

        $totalGst = 0.0;
        foreach ($this->getItems() as $item) {
            $gstRate = (float) ($item['gst_percent'] ?? 3.0);
            $lineNet = (float) ($item['price'] * $item['quantity']) * $taxableRatio;
            $totalGst += ($lineNet * ($gstRate / 100.0));
        }

        return round($totalGst, 2);
    }

    /**
     * Get shipping cost based on the selected shipping method.
     */
    public function getShippingCost(string $method): float
    {
        return (float) config("services.shipping.{$method}.cost", 0);
    }

    /**
     * Calculate the final customer total (Subtotal - Discount + GST + Shipping).
     */
    public function getTotal(string $shippingMethod = 'standard'): float
    {
        $taxable = max(0.0, $this->getSubtotal() - $this->getTotalDiscount());

        return round(
            $taxable + $this->getGst() + $this->getShippingCost($shippingMethod),
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

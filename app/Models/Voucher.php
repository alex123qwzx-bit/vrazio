<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_spend',
        'max_discount',
        'product_id',
        'usage_limit',
        'used_count',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_spend' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'is_active' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    /**
     * Relationship to Product (if this voucher is specific to one item).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope query to only active and valid vouchers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
            });
    }

    /**
     * Scope query for vouchers applicable to a specific product or storewide.
     */
    public function scopeForProduct(Builder $query, ?int $productId = null): Builder
    {
        return $query->where(function ($q) use ($productId) {
            $q->whereNull('product_id');
            if ($productId) {
                $q->orWhere('product_id', $productId);
            }
        });
    }

    /**
     * Check if the voucher is currently active and within its valid date range.
     */
    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if the voucher is currently valid for a given cart subtotal and items.
     *
     * @param float $subtotal
     * @param array<int, int> $productIds
     * @return array{valid: bool, message: string}
     */
    public function validateForCart(float $subtotal, array $productIds = []): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => "Voucher '{$this->code}' is no longer active."];
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return ['valid' => false, 'message' => "Voucher '{$this->code}' is not valid yet."];
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return ['valid' => false, 'message' => "Voucher '{$this->code}' has expired."];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => "Voucher '{$this->code}' has reached its maximum usage limit."];
        }

        if ($this->min_spend > 0 && $subtotal < (float) $this->min_spend) {
            $needed = number_format((float) $this->min_spend, 2);
            return ['valid' => false, 'message' => "Voucher '{$this->code}' requires a minimum spend of ₹{$needed}."];
        }

        if ($this->product_id !== null && !empty($productIds)) {
            if (!in_array((int) $this->product_id, array_map('intval', $productIds), true)) {
                $productName = $this->product?->title ?? 'a specific atelier piece';
                return ['valid' => false, 'message' => "Voucher '{$this->code}' is valid only for '{$productName}'."];
            }
        }

        return ['valid' => true, 'message' => 'Voucher applied successfully!'];
    }

    /**
     * Calculate discount amount for a given subtotal and cart items.
     */
    public function calculateDiscount(float $subtotal, array $items = []): float
    {
        $discount = 0.0;

        if ($this->product_id !== null) {
            // Apply only to the specific product in bag
            $targetSubtotal = 0.0;
            foreach ($items as $item) {
                if ((int) $item['id'] === (int) $this->product_id) {
                    $targetSubtotal += (float) $item['price'] * (int) $item['quantity'];
                }
            }

            if ($this->discount_type === 'percentage') {
                $discount = ($targetSubtotal * (float) $this->discount_value) / 100.0;
            } else {
                $discount = min($targetSubtotal, (float) $this->discount_value);
            }
        } else {
            // Storewide discount
            if ($this->discount_type === 'percentage') {
                $discount = ($subtotal * (float) $this->discount_value) / 100.0;
            } else {
                $discount = min($subtotal, (float) $this->discount_value);
            }
        }

        // Apply cap if defined
        if ($this->max_discount !== null && (float) $this->max_discount > 0) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(max(0.0, $discount), 2);
    }

    /**
     * Get human-friendly discount display string.
     */
    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.') . '% OFF';
        }

        return '₹' . number_format((float) $this->discount_value, 0) . ' OFF';
    }
}

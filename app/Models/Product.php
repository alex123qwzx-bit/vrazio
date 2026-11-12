<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    /**
     * The 4 authoritative product categories.
     */
    public const string CATEGORY_JEWELRY = 'jewelry';
    public const string CATEGORY_CLOTHING = 'clothing';
    public const string CATEGORY_WATCHES = 'watches';
    public const string CATEGORY_FOOTWEAR = 'footwear';

    public const array CATEGORIES = [
        self::CATEGORY_JEWELRY => 'Jewelry',
        self::CATEGORY_CLOTHING => 'Clothing',
        self::CATEGORY_WATCHES => 'Watches',
        self::CATEGORY_FOOTWEAR => 'Footwear',
    ];

    /**
     * Standard GST Rates supported across product categories.
     */
    public const array GST_RATES = [
        0 => '0% (Exempt)',
        3 => '3% (Precious Fine Jewellery)',
        5 => '5% (Footwear & Apparel < ₹1,000)',
        12 => '12% (Haute Couture & Apparel)',
        18 => '18% (Luxury Timepieces & Footwear)',
        28 => '28% (Luxury Goods & Special Articles)',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'title',
        'slug',
        'category',
        'metal_type',
        'gross_weight_grams',
        'net_weight_grams',
        'price',
        'gst_percent',
        'discount_percent',
        'final_price',
        'sale_price',
        'stock_quantity',
        'is_featured',
        'bis_hallmarked',
        'is_warranty_eligible',
        'warranty_period_months',
        'warranty_coverage',
        'warranty_exclusions',
        'warranty_remedies',
        'certification',
        'description',
        'specs',
        'images',
        'tags',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'gst_percent' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'final_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'gross_weight_grams' => 'decimal:2',
            'net_weight_grams' => 'decimal:2',
            'is_featured' => 'boolean',
            'bis_hallmarked' => 'boolean',
            'is_warranty_eligible' => 'boolean',
            'warranty_period_months' => 'integer',
            'specs' => 'array',
            'images' => 'array',
            'tags' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);

                // Ensure uniqueness by appending a suffix if needed
                $originalSlug = $product->slug;
                $counter = 1;

                while (static::where('slug', $product->slug)->exists()) {
                    $product->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        static::saving(function (Product $product): void {
            // Keep final_price synced with calculation formula
            $computedFinal = $product->calculateFinalPrice();
            $product->final_price = $computedFinal;

            if ($product->discount_percent !== null && (float) $product->discount_percent > 0) {
                $product->sale_price = $product->calculateDiscountedPrice();
            }

            // Ensure images array is never empty for new or updated products
            if (empty($product->images) || !is_array($product->images) || count($product->images) === 0) {
                $product->images = [self::getCategoryFallbackImage($product->category)];
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get all order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all vouchers specifically designated for this product.
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * Get active vouchers applicable to this product (specific or storewide).
     */
    public function getApplicableVouchers()
    {
        return Voucher::active()->forProduct($this->id)->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        $cat = strtolower(trim($category));
        $mapped = match ($cat) {
            'rings', 'necklaces', 'bracelets', 'earrings', 'solitaires', 'jewelry' => 'jewelry',
            'couture', 'clothes', 'clothing' => 'clothing',
            'watches', 'watch' => 'watches',
            'footwear', 'shoes' => 'footwear',
            default => $cat,
        };

        return $query->where('category', $mapped);
    }

    /*
    |--------------------------------------------------------------------------
    | Price & Calculation Engine (Requirements 3, 4, 5, 6)
    |--------------------------------------------------------------------------
    | Formula:
    | 1. Discount Amount = Base Price * Discount Percentage / 100
    | 2. Discounted Price = Base Price - Discount Amount
    | 3. GST Amount = Discounted Price * GST Percentage / 100
    | 4. Final Customer-Facing Price = Discounted Price + GST Amount
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the discount amount.
     */
    public function calculateDiscountAmount(?float $basePrice = null, ?float $discountPercent = null): float
    {
        $base = $basePrice ?? (float) $this->price;
        $disc = $discountPercent ?? (float) ($this->discount_percent ?? 0);

        if ($base <= 0 || $disc <= 0) {
            return 0.0;
        }

        return round($base * ($disc / 100.0), 2);
    }

    /**
     * Calculate the discounted taxable price.
     */
    public function calculateDiscountedPrice(?float $basePrice = null, ?float $discountPercent = null): float
    {
        $base = $basePrice ?? (float) $this->price;
        $discountAmount = $this->calculateDiscountAmount($base, $discountPercent);

        return max(0.0, round($base - $discountAmount, 2));
    }

    /**
     * Calculate the GST amount based on discounted taxable price.
     */
    public function calculateGstAmount(?float $basePrice = null, ?float $discountPercent = null, ?float $gstPercent = null): float
    {
        $discounted = $this->calculateDiscountedPrice($basePrice, $discountPercent);
        $gst = $gstPercent ?? (float) ($this->gst_percent ?? 0);

        if ($discounted <= 0 || $gst <= 0) {
            return 0.0;
        }

        return round($discounted * ($gst / 100.0), 2);
    }

    /**
     * Calculate the final customer-facing GST-inclusive price.
     */
    public function calculateFinalPrice(?float $basePrice = null, ?float $discountPercent = null, ?float $gstPercent = null): float
    {
        $discounted = $this->calculateDiscountedPrice($basePrice, $discountPercent);
        $gstAmount = $this->calculateGstAmount($basePrice, $discountPercent, $gstPercent);

        return round($discounted + $gstAmount, 2);
    }

    /**
     * Determine if this product has a customer-facing discount configured.
     */
    public function hasDiscount(): bool
    {
        return (float) ($this->discount_percent ?? 0) > 0;
    }

    /**
     * Get the discount badge label (e.g. '20% OFF').
     */
    protected function discountBadge(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->hasDiscount()) {
                    return null;
                }

                $disc = (float) $this->discount_percent;
                $formatted = ($disc == (int) $disc) ? (string) (int) $disc : rtrim(rtrim(number_format($disc, 2), '0'), '.');

                return $formatted . '% OFF';
            },
        );
    }

    /**
     * Get the final GST-inclusive price formatted in Indian currency format.
     */
    protected function formattedFinalPrice(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $amount = (float) ($this->final_price ?? $this->calculateFinalPrice());

                return '₹' . $this->formatIndianNumber($amount);
            },
        );
    }

    /**
     * Scope a query to only include products that are in stock.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to search products by title, SKU, or description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the effective price (final GST-inclusive price if available, otherwise sale/regular price).
     */
    protected function effectivePrice(): Attribute
    {
        return Attribute::make(
            get: fn (): string => (string) ($this->final_price ?? $this->sale_price ?? $this->price),
        );
    }

    /**
     * Get the price formatted in Indian currency format.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $amount = (float) ($this->final_price ?? $this->sale_price ?? $this->price);

                return '₹' . $this->formatIndianNumber($amount);
            },
        );
    }

    /**
     * Get the primary image URL or a category-appropriate placeholder.
     */
    protected function primaryImage(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $images = $this->images;

                if (is_array($images) && count($images) > 0 && !empty($images[0])) {
                    return self::resolveImageUrl((string) $images[0], $this->category);
                }

                return self::getCategoryFallbackImage($this->category);
            },
        );
    }

    /**
     * Get all product images resolved to proper URLs.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<list<string>, never>
     */
    protected function imageUrls(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                $images = $this->images;
                if (!is_array($images)) {
                    $images = json_decode((string) ($images ?? '[]'), true) ?? [];
                }

                if (empty($images)) {
                    return [self::getCategoryFallbackImage($this->category)];
                }

                return array_values(array_map(fn ($img) => self::resolveImageUrl((string) $img, $this->category), $images));
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get a high-resolution, luxury photograph matching product category.
     */
    public static function getCategoryFallbackImage(?string $category = null): string
    {
        $normalized = strtolower(trim((string) $category));

        return match ($normalized) {
            'rings' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=1200&q=85',
            'necklaces' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=1200&q=85',
            'bracelets' => 'https://images.unsplash.com/photo-1611591475155-42e924a10977?auto=format&fit=crop&w=1200&q=85',
            'earrings' => 'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=1200&q=85',
            'solitaires' => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?auto=format&fit=crop&w=1200&q=85',
            'clothing', 'couture' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=1200&q=85',
            'watches' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=85',
            'footwear' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=1200&q=85',
            default => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=1200&q=85',
        };
    }

    /**
     * Resolve an image path or URL into a fully-qualified or asset URL.
     * Verifies disk presence to prevent broken 404 image icons.
     */
    public static function resolveImageUrl(?string $path, ?string $category = null): string
    {
        if (empty($path)) {
            return self::getCategoryFallbackImage($category);
        }

        // External URLs (e.g. Unsplash, CDN, S3, http/https/protocol-relative)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        // Local public directory assets like images/... or uploads/... if present
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        // Check public storage disk
        if (str_starts_with($cleanPath, 'storage/')) {
            $diskSubPath = substr($cleanPath, 8);
            if (file_exists(storage_path('app/public/' . $diskSubPath))) {
                return asset($cleanPath);
            }
        } elseif (file_exists(storage_path('app/public/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // If local file is missing on disk, return high-res category fallback instead of a broken 404
        return self::getCategoryFallbackImage($category);
    }

    /**
     * Format a number in Indian numbering system (e.g., 12,34,567.00).
     */
    private function formatIndianNumber(float $number): string
    {
        $decimal = number_format($number, 2, '.', '');
        $parts = explode('.', $decimal);
        $integerPart = $parts[0];
        $decimalPart = $parts[1];

        // Handle the last 3 digits
        $lastThree = substr($integerPart, -3);
        $remaining = substr($integerPart, 0, -3);

        if ($remaining !== '' && $remaining !== false) {
            $lastThree = ',' . $lastThree;
            $formatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining) . $lastThree;
        } else {
            $formatted = $lastThree;
        }

        return $formatted . '.' . $decimalPart;
    }
}

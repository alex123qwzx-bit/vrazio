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
        'sale_price',
        'stock_quantity',
        'is_featured',
        'bis_hallmarked',
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
            'sale_price' => 'decimal:2',
            'gross_weight_grams' => 'decimal:2',
            'net_weight_grams' => 'decimal:2',
            'is_featured' => 'boolean',
            'bis_hallmarked' => 'boolean',
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
        return $query->where('category', $category);
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
     * Get the effective price (sale price if available, otherwise regular price).
     */
    protected function effectivePrice(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->sale_price ?? $this->price,
        );
    }

    /**
     * Get the price formatted in Indian currency format.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $amount = (float) ($this->sale_price ?? $this->price);

                return '₹' . $this->formatIndianNumber($amount);
            },
        );
    }

    /**
     * Get the primary image URL or a placeholder.
     */
    protected function primaryImage(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $images = $this->images;

                if (is_array($images) && count($images) > 0 && !empty($images[0])) {
                    return self::resolveImageUrl((string) $images[0]);
                }

                return 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80';
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
                    return ['https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=1200&q=85'];
                }

                return array_values(array_map(fn ($img) => self::resolveImageUrl((string) $img), $images));
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve an image path or URL into a fully-qualified or asset URL.
     */
    public static function resolveImageUrl(?string $path): string
    {
        if (empty($path)) {
            return 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80';
        }

        // External URLs (e.g. Unsplash, CDN, S3, http/https/protocol-relative)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        // Already prefixed with storage/
        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        // Local public directory assets like images/... or uploads/... if present
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        // Otherwise assume public disk storage path (e.g. products/xyz.jpg)
        return asset('storage/' . $cleanPath);
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

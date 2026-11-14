<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_title',
        'product_sku',
        'unit_price',
        'quantity',
        'size',
        'line_total',
        'status',
        'cancelled_at',
        'refunded_amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isActive(): bool
    {
        return $this->status !== 'cancelled';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the order this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the GST HSN code for this item.
     */
    public function getHsnCode(): string
    {
        $search = strtolower($this->product_title.' '.$this->product_sku.' '.($this->product?->category ?? ''));

        if (str_contains($search, 'sunglass') || str_contains($search, 'sun0') || str_contains($search, 'eyewear') || str_contains($search, 'glass')) {
            return '9004';
        }

        if (str_contains($search, 'watch') || str_contains($search, 'timepiece')) {
            return '9102';
        }

        if (str_contains($search, 'shoe') || str_contains($search, 'footwear') || str_contains($search, 'boot') || str_contains($search, 'sandal')) {
            return '6403';
        }

        if (str_contains($search, 'couture') || str_contains($search, 'dress') || str_contains($search, 'saree') || str_contains($search, 'lehenga') || str_contains($search, 'sherwani') || str_contains($search, 'clothing')) {
            return '6204';
        }

        if (str_contains($search, 'bag') || str_contains($search, 'wallet') || str_contains($search, 'leather')) {
            return '4202';
        }

        return '7113'; // Precious Jewellery
    }

    /**
     * Get the GST rate percentage for this item.
     */
    public function getGstPercent(): float
    {
        if ($this->product && $this->product->gst_percent !== null) {
            return (float) $this->product->gst_percent;
        }

        $hsn = $this->getHsnCode();
        if ($hsn === '9004' || $hsn === '9102' || $hsn === '4202') {
            return 18.0;
        }
        if ($hsn === '6204' || $hsn === '6403') {
            return 12.0;
        }

        return 3.0; // Default Jewellery
    }

    /**
     * Get formatted GST rate string (e.g. "18%", "3%").
     */
    public function getGstRateFormatted(): string
    {
        $rate = $this->getGstPercent();

        return ($rate == (int) $rate ? (string) (int) $rate : number_format($rate, 1)).'%';
    }
}

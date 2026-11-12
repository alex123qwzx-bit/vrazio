<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasFactory;

    public const string STATUS_PENDING = 'pending';
    public const string STATUS_APPROVED = 'approved';
    public const string STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'content',
        'photos',
        'is_verified_purchase',
        'status',
        'is_featured',
        'sort_order',
        'admin_reply',
        'admin_replied_at',
        'admin_reply_by',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'photos' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'admin_replied_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_reply_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if a user has a delivered purchase for a product.
     */
    public static function findVerifiedOrder(int $userId, int $productId): ?Order
    {
        return Order::where('user_id', $userId)
            ->where('fulfillment_status', 'delivered')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId)
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
                    });
            })
            ->latest('delivered_at')
            ->first();
    }

    /**
     * Get author display name safely.
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->user && !empty($this->user->name)) {
            return $this->user->name;
        }

        return 'Verified Patron';
    }
}

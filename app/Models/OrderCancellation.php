<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class OrderCancellation extends Model
{
    use HasFactory;

    public const string STATUS_PENDING_REVIEW = 'pending_review';
    public const string STATUS_APPROVED = 'approved';
    public const string STATUS_REJECTED = 'rejected';
    public const string STATUS_COMPLETED = 'completed';

    public const string TYPE_FULL = 'full';
    public const string TYPE_PARTIAL = 'partial';

    public const array REASONS = [
        'Ordered by mistake' => 'Ordered by mistake',
        'Changed my mind' => 'Changed my mind',
        'Wrong product selected' => 'Wrong product selected',
        'Found a better price' => 'Found a better price',
        'Delivery delay' => 'Delivery delay',
        'Other' => 'Other',
    ];

    protected $fillable = [
        'cancellation_number',
        'order_id',
        'user_id',
        'type',
        'order_item_id',
        'reason',
        'customer_notes',
        'admin_notes',
        'rejection_reason',
        'status',
        'refund_amount',
        'refund_method',
        'is_stock_restored',
        'stock_restored_at',
        'prive_discount_restored',
        'prive_discount_restored_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'is_stock_restored' => 'boolean',
            'stock_restored_at' => 'datetime',
            'prive_discount_restored' => 'boolean',
            'prive_discount_restored_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderCancellation $cancellation): void {
            if (empty($cancellation->cancellation_number)) {
                $datePrefix = 'VRZ-CAN-' . date('Ymd') . '-';
                do {
                    $candidate = $datePrefix . strtoupper(Str::random(4));
                } while (static::where('cancellation_number', $candidate)->exists());

                $cancellation->cancellation_number = $candidate;
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class, 'order_cancellation_id');
    }

    public function creditNote(): HasOne
    {
        return $this->hasOne(CreditNote::class, 'order_cancellation_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_COMPLETED => 'Completed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'gray',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderRefund extends Model
{
    use HasFactory;

    public const string STATUS_PENDING_APPROVAL = 'pending_approval';
    public const string STATUS_INITIATED = 'initiated';
    public const string STATUS_PROCESSING = 'processing';
    public const string STATUS_REFUNDED = 'refunded';
    public const string STATUS_FAILED = 'failed';

    protected $fillable = [
        'refund_number',
        'order_id',
        'order_cancellation_id',
        'gateway',
        'razorpay_payment_id',
        'razorpay_refund_id',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'gateway_response',
        'initiated_at',
        'completed_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'initiated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderRefund $refund): void {
            if (empty($refund->refund_number)) {
                $datePrefix = 'VRZ-RFND-' . date('Ymd') . '-';
                do {
                    $candidate = $datePrefix . strtoupper(Str::random(4));
                } while (static::where('refund_number', $candidate)->exists());

                $refund->refund_number = $candidate;
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(OrderCancellation::class, 'order_cancellation_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isInitiated(): bool
    {
        return $this->status === self::STATUS_INITIATED;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_INITIATED => 'Refund Initiated',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_FAILED => 'Refund Failed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_APPROVAL => 'warning',
            self::STATUS_INITIATED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_REFUNDED => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'gray',
        };
    }
}

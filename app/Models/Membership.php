<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'member_id',
        'tier',
        'tier_name',
        'fee_paid',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'starts_at',
        'expires_at',
        'next_order_discount_used',
        'next_order_discount_used_at',
        'next_order_order_id',
        'birthday_coupon_used_year',
        'card_token',
        'admin_notes',
    ];

    protected $casts = [
        'fee_paid' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'next_order_discount_used' => 'boolean',
        'next_order_discount_used_at' => 'datetime',
        'birthday_coupon_used_year' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Membership $membership): void {
            if (empty($membership->member_id)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $formattedNumber = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                $memberId = "VPC-{$year}-{$formattedNumber}";

                while (static::where('member_id', $memberId)->exists()) {
                    $count++;
                    $formattedNumber = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                    $memberId = "VPC-{$year}-{$formattedNumber}";
                }

                $membership->member_id = $memberId;
            }

            if (empty($membership->card_token)) {
                $membership->card_token = Str::random(40);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function nextOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'next_order_order_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>=', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')->orWhere('expires_at', '<', now());
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }

    public function isValid(): bool
    {
        return $this->isActive();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    public function hasAvailableDiscount(): bool
    {
        return $this->isActive() && ! $this->next_order_discount_used;
    }

    public function getRemainingDaysAttribute(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'suspended') {
            return 'Suspended';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        return 'Active';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->status === 'suspended') {
            return 'warning';
        }

        if ($this->isExpired()) {
            return 'danger';
        }

        return 'success';
    }
}

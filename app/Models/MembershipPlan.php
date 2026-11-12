<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'price',
        'validity_months',
        'next_order_discount_percent',
        'next_order_max_discount',
        'next_order_min_cart',
        'birthday_discount_percent',
        'birthday_max_discount',
        'birthday_min_cart',
        'benefits',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'next_order_discount_percent' => 'decimal:2',
        'next_order_max_discount' => 'decimal:2',
        'next_order_min_cart' => 'decimal:2',
        'birthday_discount_percent' => 'decimal:2',
        'birthday_max_discount' => 'decimal:2',
        'birthday_min_cart' => 'decimal:2',
        'benefits' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format((float) $this->price, 0);
    }
}

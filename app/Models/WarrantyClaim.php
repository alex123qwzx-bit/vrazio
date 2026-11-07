<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WarrantyClaim extends Model
{
    use HasFactory;

    /**
     * Supported issue types in Haute Joaillerie and Couture atelier.
     */
    public const ISSUE_TYPES = [
        'stone_loose_displaced' => 'Stone Loose / Displaced',
        'gold_plating_tarnish' => 'Gold Purity / Plating / Tarnishing',
        'clasp_lock_defect' => 'Clasp / Catch / Lock Malfunction',
        'broken_prong_setting' => 'Broken Prong / Damaged Setting',
        'embroidery_zari_pull' => 'Embroidery / Real Zari Thread Pull',
        'fabric_weave_flaw' => 'Fabric / Handloom Weave Flaw',
        'fitting_sizing_defect' => 'Bespoke Fitting / Sizing Discrepancy',
        'other_craftsmanship' => 'Other Master Atelier Craftsmanship Flaw',
    ];

    /**
     * Allowed statuses.
     */
    public const STATUSES = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'resolved' => 'Resolved',
    ];

    protected $fillable = [
        'claim_number',
        'order_id',
        'user_id',
        'customer_name',
        'phone',
        'email',
        'product_name',
        'issue_type',
        'description',
        'status',
        'is_checked',
        'checked_by',
        'checked_at',
        'admin_note',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'checked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WarrantyClaim $claim): void {
            if (empty($claim->claim_number)) {
                $year = date('Y');
                do {
                    $randomCode = strtoupper(Str::random(6));
                    $claimNumber = "WC-{$year}-{$randomCode}";
                } while (static::where('claim_number', $claimNumber)->exists());

                $claim->claim_number = $claimNumber;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function checkedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(WarrantyClaimImage::class, 'claim_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'under_review'])
            ->where('is_checked', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'resolved' => 'Resolved',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'gray',
            'under_review' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'resolved' => 'success',
            default => 'gray',
        };
    }

    public function getIssueTypeLabelAttribute(): string
    {
        return self::ISSUE_TYPES[$this->issue_type] ?? ucfirst(str_replace('_', ' ', $this->issue_type));
    }

    /**
     * Mark claim as checked by an admin atelier concierge.
     */
    public function markAsChecked(?int $adminUserId = null): void
    {
        $this->update([
            'is_checked' => true,
            'checked_by' => $adminUserId,
            'checked_at' => now(),
        ]);
    }
}

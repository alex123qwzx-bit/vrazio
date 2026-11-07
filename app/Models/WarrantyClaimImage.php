<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaimImage extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'claim_id',
        'image_path',
        'created_at',
    ];

    /**
     * Relationship to the warranty claim.
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'claim_id');
    }

    /**
     * Get the accessible URL for this claim photo (routed securely through authorization check).
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if ($this->id) {
            return route('warranty.image', $this->id);
        }

        if (str_starts_with($this->image_path, '/')) {
            return asset(ltrim($this->image_path, '/'));
        }

        return asset('uploads/warranty/' . basename($this->image_path));
    }
}

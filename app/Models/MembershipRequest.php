<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipRequest extends Model
{
    use HasFactory;

    // Request Types
    public const TYPE_CANCELLATION = 'cancellation';

    public const TYPE_REFUND = 'refund';

    public const TYPE_UPGRADE = 'upgrade';

    // General Request Statuses
    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REFUND_PROCESSING = 'refund_processing';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_PAYMENT_PENDING = 'payment_pending';

    public const STATUS_PAYMENT_RECEIVED = 'payment_received';

    public const STATUS_COMPLETED = 'completed';

    // Refund Specific Statuses
    public const REFUND_PENDING_REVIEW = 'pending_review';

    public const REFUND_UNDER_REVIEW = 'under_review';

    public const REFUND_APPROVED = 'approved';

    public const REFUND_REJECTED = 'rejected';

    public const REFUND_PROCESSING = 'refund_processing';

    public const REFUND_COMPLETED = 'refunded';

    protected $fillable = [
        'request_number',
        'membership_id',
        'user_id',
        'type',
        'status',
        'reason',
        'customer_notes',
        'is_refund_requested',
        'refund_amount',
        'refund_status',
        'refund_transaction_id',
        'refund_processed_at',
        'current_plan_id',
        'target_plan_id',
        'price_difference',
        'additional_payment_required',
        'payment_reference',
        'payment_received_at',
        'previous_membership_details',
        'upgraded_membership_details',
        'admin_notes',
        'admin_response',
        'auto_renewal_disabled',
        'actioned_by',
        'actioned_at',
        'audit_log',
    ];

    protected $casts = [
        'is_refund_requested' => 'boolean',
        'refund_amount' => 'decimal:2',
        'refund_processed_at' => 'datetime',
        'price_difference' => 'decimal:2',
        'additional_payment_required' => 'decimal:2',
        'payment_received_at' => 'datetime',
        'previous_membership_details' => 'array',
        'upgraded_membership_details' => 'array',
        'auto_renewal_disabled' => 'boolean',
        'actioned_at' => 'datetime',
        'audit_log' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (MembershipRequest $request): void {
            if (empty($request->request_number)) {
                $year = date('Y');
                $prefix = match ($request->type) {
                    self::TYPE_CANCELLATION => 'MCR',
                    self::TYPE_REFUND => 'MRF',
                    self::TYPE_UPGRADE => 'MUP',
                    default => 'MRQ',
                };

                $count = static::whereYear('created_at', $year)->count() + 1;
                $formattedNumber = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                $requestNumber = "{$prefix}-{$year}-{$formattedNumber}";

                while (static::where('request_number', $requestNumber)->exists()) {
                    $count++;
                    $formattedNumber = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                    $requestNumber = "{$prefix}-{$year}-{$formattedNumber}";
                }

                $request->request_number = $requestNumber;
            }

            if (empty($request->audit_log)) {
                $request->audit_log = [
                    [
                        'action' => 'submitted',
                        'timestamp' => now()->toISOString(),
                        'actor_id' => $request->user_id,
                        'actor_role' => 'customer',
                        'status' => $request->status,
                        'notes' => 'Initial request submitted by patron.',
                    ],
                ];
            }
        });
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'current_plan_id');
    }

    public function targetPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'target_plan_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function scopeCancellations(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CANCELLATION);
    }

    public function scopeRefunds(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('type', self::TYPE_REFUND)
                ->orWhere('is_refund_requested', true);
        });
    }

    public function scopeUpgrades(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_UPGRADE);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING_REVIEW, self::STATUS_UNDER_REVIEW]);
    }

    /**
     * Log an administrative action to the audit history.
     */
    public function logAuditAction(string $action, ?int $adminId, string $notes, ?string $newStatus = null): void
    {
        $log = $this->audit_log ?? [];
        $log[] = [
            'action' => $action,
            'timestamp' => now()->toISOString(),
            'actor_id' => $adminId,
            'actor_role' => 'admin',
            'status' => $newStatus ?? $this->status,
            'notes' => $notes,
        ];

        $this->audit_log = $log;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW => 'Pending Review',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_REFUND_PROCESSING => 'Refund Processing',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_PAYMENT_PENDING => 'Payment Pending',
            self::STATUS_PAYMENT_RECEIVED => 'Payment Received',
            self::STATUS_COMPLETED => 'Completed',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW => 'warning',
            self::STATUS_UNDER_REVIEW => 'info',
            self::STATUS_APPROVED, self::STATUS_REFUNDED, self::STATUS_COMPLETED, self::STATUS_PAYMENT_RECEIVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_REFUND_PROCESSING, self::STATUS_PAYMENT_PENDING => 'warning',
            default => 'gray',
        };
    }

    public function getRefundStatusLabelAttribute(): ?string
    {
        if (! $this->refund_status && ! $this->is_refund_requested) {
            return null;
        }

        return match ($this->refund_status) {
            self::REFUND_PENDING_REVIEW => 'Pending Review',
            self::REFUND_UNDER_REVIEW => 'Under Review',
            self::REFUND_APPROVED => 'Approved',
            self::REFUND_REJECTED => 'Rejected',
            self::REFUND_PROCESSING => 'Refund Processing',
            self::REFUND_COMPLETED => 'Refunded',
            default => ucfirst(str_replace('_', ' ', (string) ($this->refund_status ?? 'pending_review'))),
        };
    }

    public function getRefundStatusColorAttribute(): string
    {
        return match ($this->refund_status) {
            self::REFUND_PENDING_REVIEW => 'warning',
            self::REFUND_UNDER_REVIEW => 'info',
            self::REFUND_APPROVED, self::REFUND_COMPLETED => 'success',
            self::REFUND_REJECTED => 'danger',
            self::REFUND_PROCESSING => 'warning',
            default => 'gray',
        };
    }

    /**
     * Generate WhatsApp support link with prefilled template message.
     */
    public function getWhatsAppUrl(): string
    {
        $phone = '918791227210';
        $currentPlan = $this->membership?->tier_name ?? $this->currentPlan?->name ?? 'VRAZIO Privé Club';
        $typeLabel = ucfirst($this->type);
        $customerName = $this->user?->name ?? 'Valued Patron';

        $text = "Hello, I need assistance regarding my membership request.\n\n".
            "Request ID: {$this->request_number}\n".
            "Membership: {$currentPlan}\n".
            "Request Type: {$typeLabel}\n".
            "Customer Name: {$customerName}\n\n".
            'Please assist me.';

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
    }
}

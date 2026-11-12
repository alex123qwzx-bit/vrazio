<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'client_name',
        'client_email',
        'client_phone',
        'shipping_address',
        'shipping_method',
        'subtotal',
        'voucher_code',
        'prive_member_id',
        'discount_amount',
        'prive_discount_amount',
        'tax_gst',
        'shipping_cost',
        'total_amount',
        'payment_gateway',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_status',
        'fulfillment_status',
        'tracking_number',
        'tracking_courier',
        'tracking_notes',
        'packed_at',
        'shipped_at',
        'out_for_delivery_at',
        'delivered_at',
        'estimated_delivery_at',
        'delivery_time_slot',
        'otp_verified_at',
        'cancellation_status',
        'refund_status',
        'total_refunded',
        'cancelled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'prive_discount_amount' => 'decimal:2',
            'tax_gst' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'total_refunded' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Clean casual order tracking steps.
     */
    public const array CASUAL_STEPS = [
        'cancelled' => [
            'key' => 'cancelled',
            'step' => 0,
            'label' => 'Cancelled',
            'desc' => 'Order was cancelled',
            'aliases' => ['cancelled', 'canceled'],
        ],
        'order_placed' => [
            'key' => 'order_placed',
            'step' => 1,
            'label' => 'Order Placed',
            'desc' => 'Order confirmed & received',
            'aliases' => ['inscribed', 'pending', 'placed', 'order_placed'],
        ],
        'packed' => [
            'key' => 'packed',
            'step' => 2,
            'label' => 'Packed',
            'desc' => 'Packed safely in box',
            'aliases' => ['hallmarking', 'processing', 'packed'],
        ],
        'shipped' => [
            'key' => 'shipped',
            'step' => 3,
            'label' => 'Shipped',
            'desc' => 'Handed over to courier partner',
            'aliases' => ['armoured_transit', 'in_transit', 'shipped'],
        ],
        'out_for_delivery' => [
            'key' => 'out_for_delivery',
            'step' => 4,
            'label' => 'Out for Delivery',
            'desc' => 'Arrived in your city, delivering today',
            'aliases' => ['arrived', 'out_for_delivery'],
        ],
        'delivered' => [
            'key' => 'delivered',
            'step' => 5,
            'label' => 'Delivered',
            'desc' => 'Delivered to your doorstep',
            'aliases' => ['delivered'],
        ],
    ];

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
        static::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                do {
                    $candidate = 'VRZ-' . strtoupper(Str::random(8));
                } while (static::where('order_number', $candidate)->exists());

                $order->order_number = $candidate;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user who placed this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the warranty claim associated with this order.
     */
        /**
     * Get all cancellation requests filed for this order.
     */
    public function cancellations(): HasMany
    {
        return $this->hasMany(OrderCancellation::class);
    }

    /**
     * Get the active or most recent cancellation docket.
     */
    public function activeCancellation(): HasOne
    {
        return $this->hasOne(OrderCancellation::class)->latestOfMany();
    }

    /**
     * Get all financial refunds processed for this order.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class);
    }

    /**
     * Get all official GST credit notes issued for this order.
     */
    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function warrantyClaim(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WarrantyClaim::class);
    }

    /**
     * Determine if the given user or session verified phone has permission to view/manage this order.
     */
    public function isAccessibleBy(?User $user = null, ?string $verifiedPhone = null): bool
    {
        $user = $user ?? auth()->user();

        // 1. Admin can access everything
        if ($user && $user->role === 'admin') {
            return true;
        }

        // 2. Logged-in owner (by user_id or matching verified email/phone)
        if ($user) {
            if ($this->user_id && (int) $this->user_id === (int) $user->id) {
                return true;
            }
            if (!empty($user->phone) && $this->client_phone === $user->phone) {
                return true;
            }
            if (!empty($user->email) && strtolower((string) $this->client_email) === strtolower((string) $user->email)) {
                return true;
            }
        }

        // 3. Guest with verified OTP phone in session or recently placed order in session
        $recentOrder = session('recently_placed_order');
        if (!empty($recentOrder) && $recentOrder === $this->order_number) {
            return true;
        }

        $sessionPhone = $verifiedPhone ?? session('order_phone_verified');
        if (!empty($sessionPhone) && !empty($this->client_phone) && $sessionPhone === $this->client_phone) {
            return true;
        }

        return false;
    }

    /**
     * Check if this order is eligible for a warranty claim.
     */
    public function isEligibleForWarranty(): bool
    {
        return $this->fulfillment_status === 'delivered' && ! $this->relationLoaded('warrantyClaim') 
            ? ! $this->warrantyClaim()->exists() 
            : ($this->fulfillment_status === 'delivered' && $this->warrantyClaim === null);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the total formatted in Indian currency.
     */
    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $amount = (float) $this->total_amount;

                return '₹' . $this->formatIndianNumber($amount);
            },
        );
    }

    /**
     * Get the badge color for the current fulfillment status.
     */
    protected function statusBadgeColor(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match ($this->fulfillment_status) {
                'inscribed', 'order_placed' => 'amber',
                'hallmarking', 'packed' => 'blue',
                'armoured_transit', 'shipped' => 'indigo',
                'out_for_delivery' => 'sky',
                'delivered' => 'emerald',
                'cancelled' => 'rose',
                default => 'gray',
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Mark the order as paid.
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Map current fulfillment status to casual key: order_placed, packed, shipped, out_for_delivery, delivered.
     */
    public function getCasualStatusKey(): string
    {
        $raw = strtolower((string) ($this->fulfillment_status ?? 'order_placed'));
        foreach (self::CASUAL_STEPS as $key => $info) {
            if ($raw === $key || in_array($raw, $info['aliases'], true)) {
                return $key;
            }
        }
        return 'order_placed';
    }

    /**
     * Get clean casual label for display (e.g. Order Placed, Packed, Shipped, Out for Delivery, Delivered).
     */
    public function getCasualStatusLabel(): string
    {
        $key = $this->getCasualStatusKey();
        return self::CASUAL_STEPS[$key]['label'] ?? 'Order Placed';
    }

    /**
     * Get step index (1 to 5).
     */
    public function getCurrentStepNumber(): int
    {
        $key = $this->getCasualStatusKey();
        return self::CASUAL_STEPS[$key]['step'] ?? 1;
    }

    /**
     * Advance the fulfillment status to the next step.
     */
    public function advanceFulfillment(): bool
    {
        $flow = ['order_placed', 'packed', 'shipped', 'out_for_delivery', 'delivered'];
        $currentKey = $this->getCasualStatusKey();
        $currentIndex = array_search($currentKey, $flow, true);

        if ($currentIndex === false || $currentIndex >= count($flow) - 1) {
            return false;
        }

        $nextStatus = $flow[$currentIndex + 1];
        return $this->updateTrackingStatus($nextStatus);
    }

    /**
     * Update order tracking status, timestamps, courier details, and estimated delivery time.
     */
    public function updateTrackingStatus(
        string $newStatus,
        ?string $courier = null,
        ?string $trackingNumber = null,
        ?string $notes = null,
        mixed $estimatedDeliveryAt = null,
        ?string $deliveryTimeSlot = null
    ): bool {
        $updates = ['fulfillment_status' => $newStatus];

        if ($courier !== null) {
            $updates['tracking_courier'] = $courier;
        }
        if ($trackingNumber !== null) {
            $updates['tracking_number'] = $trackingNumber;
        }
        if ($notes !== null) {
            $updates['tracking_notes'] = $notes;
        }
        if ($estimatedDeliveryAt !== null) {
            $updates['estimated_delivery_at'] = $estimatedDeliveryAt ?: null;
        }
        if ($deliveryTimeSlot !== null) {
            $updates['delivery_time_slot'] = $deliveryTimeSlot ?: null;
        }

        $now = now();
        if (in_array($newStatus, ['packed', 'shipped', 'out_for_delivery', 'delivered'], true) && ! $this->packed_at) {
            $updates['packed_at'] = $now;
        }
        if (in_array($newStatus, ['shipped', 'out_for_delivery', 'delivered'], true) && ! $this->shipped_at) {
            $updates['shipped_at'] = $now;
        }
        if (in_array($newStatus, ['out_for_delivery', 'delivered'], true) && ! $this->out_for_delivery_at) {
            $updates['out_for_delivery_at'] = $now;
        }
        if ($newStatus === 'delivered' && ! $this->delivered_at) {
            $updates['delivered_at'] = $now;
        }

        return $this->update($updates);
    }

    /**
     * Get a friendly formatted estimated delivery string for display.
     */
    public function getEstimatedDeliveryText(): ?string
    {
        if (! $this->estimated_delivery_at) {
            return null;
        }

        $dateFormatted = $this->estimated_delivery_at->format('D, d M Y');
        
        if ($this->delivery_time_slot) {
            return "{$dateFormatted} ({$this->delivery_time_slot})";
        }

        // If time is not midnight (e.g. 14:00), format with time
        if ($this->estimated_delivery_at->format('H:i') !== '00:00') {
            return $this->estimated_delivery_at->format('D, d M Y, h:i A');
        }

        return $dateFormatted;
    }

    /**
     * Determine if order can be cancelled by patron.
     */
    public function canBeCancelled(): bool
    {
        if (in_array($this->fulfillment_status, ['cancelled', 'shipped', 'out_for_delivery', 'delivered'], true)) {
            return false;
        }

        if ($this->cancellation_status === 'cancelled') {
            return false;
        }

        $casualKey = $this->getCasualStatusKey();
        return in_array($casualKey, ['order_placed', 'packed'], true);
    }

    /**
     * Cancel the order and restore reserved product stock.
     */
    public function cancel(string $reason = 'Cancelled by customer'): bool
    {
        if (! $this->canBeCancelled()) {
            return false;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reason) {
            // Restore inventory for each line item
            foreach ($this->items as $item) {
                if ($item->product_id && $item->quantity > 0) {
                    \Illuminate\Support\Facades\DB::table('products')->where('id', $item->product_id)->increment('stock_quantity', (int) $item->quantity);
                }
            }

            $currentNotes = $this->tracking_notes ? $this->tracking_notes . "\n" : '';
            $this->update([
                'fulfillment_status' => 'cancelled',
                'tracking_notes' => $currentNotes . '[' . now()->toDateTimeString() . '] ' . $reason,
            ]);
        });

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Format a number in Indian numbering system.
     */
    private function formatIndianNumber(float $number): string
    {
        $decimal = number_format($number, 2, '.', '');
        $parts = explode('.', $decimal);
        $integerPart = $parts[0];
        $decimalPart = $parts[1];

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
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
                    $candidate = 'VRZ-'.strtoupper(Str::random(8));
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

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
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

    public function warrantyClaim(): HasOne
    {
        return $this->hasOne(WarrantyClaim::class);
    }

    /**
     * Determine if the given user or session verified phone has permission to view/manage this order.
     */
    public function isAccessibleBy(?User $user = null, ?string $verifiedPhone = null): bool
    {
        $user = $user ?? auth()->user();

        // 1. Admin / Super Admin can access everything
        if ($user && ($user->isAdmin() || in_array($user->role, ['admin', 'super_admin'], true))) {
            return true;
        }

        // 2. Logged-in owner (by user_id or matching verified email/phone)
        if ($user) {
            if ($this->user_id && (int) $this->user_id === (int) $user->id) {
                return true;
            }
            if (! empty($user->phone) && $this->client_phone === $user->phone) {
                return true;
            }
            if (! empty($user->email) && strtolower((string) $this->client_email) === strtolower((string) $user->email)) {
                return true;
            }
        }

        // 3. Guest with verified OTP phone in session or recently placed order in session
        $recentOrder = session('recently_placed_order');
        if (! empty($recentOrder) && $recentOrder === $this->order_number) {
            return true;
        }

        $sessionPhone = $verifiedPhone ?? session('order_phone_verified');
        if (! empty($sessionPhone) && ! empty($this->client_phone) && $sessionPhone === $this->client_phone) {
            return true;
        }

        // 4. Local development preview for unassigned guest order
        if (app()->environment('local') && empty($this->user_id)) {
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

                return '₹'.$this->formatIndianNumber($amount);
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

        DB::transaction(function () use ($reason) {
            // Restore inventory for each line item
            foreach ($this->items as $item) {
                if ($item->product_id && $item->quantity > 0) {
                    DB::table('products')->where('id', $item->product_id)->increment('stock_quantity', (int) $item->quantity);
                }
            }

            $currentNotes = $this->tracking_notes ? $this->tracking_notes."\n" : '';
            $this->update([
                'fulfillment_status' => 'cancelled',
                'tracking_notes' => $currentNotes.'['.now()->toDateTimeString().'] '.$reason,
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
            $lastThree = ','.$lastThree;
            $formatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining).$lastThree;
        } else {
            $formatted = $lastThree;
        }

        return $formatted.'.'.$decimalPart;
    }

    public const array STATE_CODES = [
        'JAMMU AND KASHMIR' => '01',
        'HIMACHAL PRADESH' => '02',
        'PUNJAB' => '03',
        'CHANDIGARH' => '04',
        'UTTARAKHAND' => '05',
        'HARYANA' => '06',
        'DELHI' => '07',
        'RAJASTHAN' => '08',
        'UTTAR PRADESH' => '09',
        'BIHAR' => '10',
        'SIKKIM' => '11',
        'ARUNACHAL PRADESH' => '12',
        'NAGALAND' => '13',
        'MANIPUR' => '14',
        'MIZORAM' => '15',
        'TRIPURA' => '16',
        'MEGHALAYA' => '17',
        'ASSAM' => '18',
        'WEST BENGAL' => '19',
        'JHARKHAND' => '20',
        'ODISHA' => '21',
        'CHHATTISGARH' => '22',
        'MADHYA PRADESH' => '23',
        'GUJARAT' => '24',
        'DAMAN AND DIU' => '25',
        'DADRA AND NAGAR HAVELI' => '26',
        'MAHARASHTRA' => '27',
        'ANDHRA PRADESH' => '28',
        'KARNATAKA' => '29',
        'GOA' => '30',
        'LAKSHADWEEP' => '31',
        'KERALA' => '32',
        'TAMIL NADU' => '33',
        'PUDUCHERRY' => '34',
        'ANDAMAN AND NICOBAR ISLANDS' => '35',
        'TELANGANA' => '36',
        'LADAKH' => '38',
    ];

    /**
     * Get the customer state name from shipping address.
     */
    public function getCustomerStateName(): string
    {
        $addr = is_array($this->shipping_address) ? $this->shipping_address : json_decode((string) $this->shipping_address, true);
        $state = trim($addr['state'] ?? '');

        return $state !== '' ? $state : 'Uttar Pradesh';
    }

    /**
     * Get the GST 2-digit state code for customer state.
     */
    public function getCustomerStateCode(): string
    {
        $state = strtoupper(trim($this->getCustomerStateName()));

        return self::STATE_CODES[$state] ?? '09';
    }

    /**
     * Get Place of Supply string (e.g. "Uttar Pradesh (09)").
     */
    public function getPlaceOfSupply(): string
    {
        return $this->getCustomerStateName().' ('.$this->getCustomerStateCode().')';
    }

    /**
     * Check if the order is inter-state (outside Uttar Pradesh 09).
     */
    public function isInterState(): bool
    {
        return $this->getCustomerStateCode() !== '09';
    }

    /**
     * Calculate effective GST rate in percentage.
     */
    public function getEffectiveGstRate(): float
    {
        $totalGst = (float) ($this->tax_gst ?? 0);
        if ($totalGst <= 0) {
            return 0.0;
        }

        $taxable = max(1.0, (float) ($this->subtotal ?? $this->total_amount) - (float) ($this->discount_amount ?? 0));

        return round(($totalGst / $taxable) * 100, 1);
    }

    public function getCgstRate(): float
    {
        return $this->isInterState() ? 0.0 : round($this->getEffectiveGstRate() / 2, 1);
    }

    public function getSgstRate(): float
    {
        return $this->isInterState() ? 0.0 : round($this->getEffectiveGstRate() / 2, 1);
    }

    public function getIgstRate(): float
    {
        return $this->isInterState() ? $this->getEffectiveGstRate() : 0.0;
    }

    public function getCgstAmount(): float
    {
        if ($this->isInterState()) {
            return 0.0;
        }

        $totalGst = (float) ($this->tax_gst ?? 0);

        return round($totalGst / 2, 2);
    }

    public function getSgstAmount(): float
    {
        if ($this->isInterState()) {
            return 0.0;
        }

        $totalGst = (float) ($this->tax_gst ?? 0);
        $cgst = round($totalGst / 2, 2);

        return round($totalGst - $cgst, 2);
    }

    public function getIgstAmount(): float
    {
        return $this->isInterState() ? (float) ($this->tax_gst ?? 0) : 0.0;
    }

    /**
     * Convert the order total amount to words in Indian numbering format.
     */
    public function getAmountInWords(?float $amount = null): string
    {
        $val = $amount ?? (float) $this->total_amount;

        return static::convertNumberToIndianWords($val);
    }

    /**
     * Convert an amount into Indian words (Crores, Lakhs, Thousands, Hundreds, Rupees, Paise).
     */
    public static function convertNumberToIndianWords(float $amount): string
    {
        $amount = round($amount, 2);
        if ($amount <= 0.0) {
            return 'Zero Only';
        }

        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        $words = [];

        $crores = (int) floor($rupees / 10000000);
        $rupees %= 10000000;

        $lakhs = (int) floor($rupees / 100000);
        $rupees %= 100000;

        $thousands = (int) floor($rupees / 1000);
        $rupees %= 1000;

        $hundreds = (int) floor($rupees / 100);
        $remainder = $rupees % 100;

        if ($crores > 0) {
            $words[] = static::convertSubHundred($crores).' Crore'.($crores > 1 ? 's' : '');
        }

        if ($lakhs > 0) {
            $words[] = static::convertSubHundred($lakhs).' Lakh';
        }

        if ($thousands > 0) {
            $words[] = static::convertSubHundred($thousands).' Thousand';
        }

        if ($hundreds > 0) {
            $words[] = static::convertSubHundred($hundreds).' Hundred';
        }

        if ($remainder > 0) {
            $words[] = static::convertSubHundred($remainder);
        }

        $result = implode(' ', $words);

        if ($paise > 0) {
            $paiseWords = static::convertSubHundred($paise).' Paise';
            $result = $result ? "{$result} and {$paiseWords}" : "{$paiseWords}";
        }

        return trim($result).' Only';
    }

    private static function convertSubHundred(int $number): string
    {
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen',
        ];

        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        $ten = (int) floor($number / 10);
        $one = $number % 10;

        return $tens[$ten].($one > 0 ? '-'.$ones[$one] : '');
    }
}

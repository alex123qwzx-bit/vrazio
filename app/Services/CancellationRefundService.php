<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\CancellationNotificationMail;
use App\Mail\RefundNotificationMail;
use App\Models\CreditNote;
use App\Models\Membership;
use App\Models\Order;
use App\Models\OrderCancellation;
use App\Models\OrderItem;
use App\Models\OrderRefund;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CancellationRefundService
{
    public function __construct(
        protected RazorpayService $razorpayService
    ) {}

    /**
     * Check if an order or individual order item is eligible for cancellation.
     *
     * @return array{eligible: bool, requires_review: bool, refund_amount: float, refund_method: string, message: string}
     */
    public function checkEligibility(Order $order, ?OrderItem $item = null): array
    {
        // 1. Prevent duplicate cancellation if already fully cancelled
        if ($order->fulfillment_status === 'cancelled' || $order->cancellation_status === 'cancelled') {
            return [
                'eligible' => false,
                'requires_review' => false,
                'refund_amount' => 0.0,
                'refund_method' => 'none',
                'message' => 'This order has already been cancelled.',
            ];
        }

        // 2. Prevent duplicate cancellation of individual item
        if ($item && $item->status === 'cancelled') {
            return [
                'eligible' => false,
                'requires_review' => false,
                'refund_amount' => 0.0,
                'refund_method' => 'none',
                'message' => 'This specific product has already been cancelled.',
            ];
        }

        // 3. Shipped & Out for Delivery: ordinary cancellation closed
        $casualKey = $order->getCasualStatusKey();
        if (in_array($casualKey, ['shipped', 'out_for_delivery'], true)) {
            return [
                'eligible' => false,
                'requires_review' => false,
                'refund_amount' => 0.0,
                'refund_method' => 'none',
                'message' => 'Consignment is in transit with our secure armoured courier. Direct cancellation is closed. You may decline delivery at doorstep or file a return upon delivery.',
            ];
        }

        // 4. Delivered: direct to returns / warranty policy
        if ($casualKey === 'delivered' || $order->delivered_at) {
            return [
                'eligible' => false,
                'requires_review' => false,
                'refund_amount' => 0.0,
                'refund_method' => 'none',
                'message' => 'This consignment has been delivered. Please refer to our Atelier Returns & Warranty Policy to initiate an exchange or return docket.',
            ];
        }

        // 5. Existing pending request check
        $pendingExists = OrderCancellation::where('order_id', $order->id)
            ->where('status', OrderCancellation::STATUS_PENDING_REVIEW)
            ->when($item, fn ($q) => $q->where('order_item_id', $item->id))
            ->exists();

        if ($pendingExists) {
            return [
                'eligible' => false,
                'requires_review' => false,
                'refund_amount' => 0.0,
                'refund_method' => 'none',
                'message' => 'A cancellation request for this consignment is currently pending administrative review.',
            ];
        }

        // 6. Calculate applicable refund amount
        $refundAmount = $item
            ? $this->calculateItemRefundAmount($order, $item)
            : $this->calculateOrderRefundAmount($order);

        // 7. Determine refund method
        $refundMethod = 'cod_no_refund';
        if ($order->payment_status === 'paid') {
            $refundMethod = ($order->payment_gateway === 'razorpay') ? 'razorpay' : 'manual_bank_transfer';
        }

        // 8. Packed / processing: Requires admin review
        $requiresReview = ($casualKey === 'packed');

        return [
            'eligible' => true,
            'requires_review' => $requiresReview,
            'refund_amount' => $refundAmount,
            'refund_method' => $refundMethod,
            'message' => $requiresReview
                ? 'Your order is currently being packed. Cancellation request will be submitted for immediate atelier manager review.'
                : 'Your order is eligible for immediate cancellation and refund processing.',
        ];
    }

    /**
     * Submit a customer or admin cancellation request.
     */
    public function requestCancellation(
        Order $order,
        string $reason,
        ?string $comments = null,
        ?OrderItem $item = null,
        ?User $user = null
    ): OrderCancellation {
        $eligibility = $this->checkEligibility($order, $item);

        if (! $eligibility['eligible']) {
            throw new Exception($eligibility['message']);
        }

        return DB::transaction(function () use ($order, $reason, $comments, $item, $user, $eligibility) {
            $type = $item ? OrderCancellation::TYPE_PARTIAL : OrderCancellation::TYPE_FULL;
            $status = $eligibility['requires_review']
                ? OrderCancellation::STATUS_PENDING_REVIEW
                : OrderCancellation::STATUS_APPROVED;

            $cancellation = OrderCancellation::create([
                'order_id' => $order->id,
                'user_id' => $user?->id ?? $order->user_id,
                'type' => $type,
                'order_item_id' => $item?->id,
                'reason' => $reason,
                'customer_notes' => $comments,
                'status' => $status,
                'refund_amount' => $eligibility['refund_amount'],
                'refund_method' => $eligibility['refund_method'],
            ]);

            if ($eligibility['requires_review']) {
                if ($item) {
                    $item->update(['status' => 'cancellation_requested']);
                }
                $order->update(['cancellation_status' => 'pending_review']);

                $this->safeSendMail(new CancellationNotificationMail($order, $cancellation, 'requested'), $order->client_email);
            } else {
                // Auto-approve and process immediately (for orders placed before packing, or unpaid orders)
                $this->processApprovedCancellation($cancellation, $user, 'Auto-approved by system (pre-fulfillment)');
            }

            return $cancellation;
        });
    }

    /**
     * Process an approved cancellation: restore stock, process refund, generate credit note, and restore Privé discount.
     */
    public function processApprovedCancellation(
        OrderCancellation $cancellation,
        ?User $approvedBy = null,
        ?string $adminNotes = null
    ): bool {
        return DB::transaction(function () use ($cancellation, $approvedBy, $adminNotes) {
            $cancellation = OrderCancellation::where('id', $cancellation->id)->lockForUpdate()->firstOrFail();
            $order = Order::with('items')->where('id', $cancellation->order_id)->lockForUpdate()->firstOrFail();

            if ($cancellation->status === OrderCancellation::STATUS_COMPLETED) {
                return true; // Already processed idempotently
            }

            $cancellation->update([
                'status' => OrderCancellation::STATUS_APPROVED,
                'approved_by' => $approvedBy?->id,
                'approved_at' => now(),
                'admin_notes' => $adminNotes ? trim($cancellation->admin_notes . "\n" . $adminNotes) : $cancellation->admin_notes,
            ]);

            // 1. Inventory Restoration (Strictly exactly once!)
            $this->restoreStock($cancellation, $order);

            // 2. Update Order/Item status
            if ($cancellation->type === OrderCancellation::TYPE_FULL) {
                $orderNotes = $order->tracking_notes ? $order->tracking_notes . "\n" : '';
                $order->update([
                    'fulfillment_status' => 'cancelled',
                    'cancellation_status' => 'cancelled',
                    'cancelled_at' => now(),
                    'tracking_notes' => $orderNotes . '[' . now()->toDateTimeString() . "] Cancelled: {$cancellation->reason}",
                ]);

                foreach ($order->items as $it) {
                    if ($it->status !== 'cancelled') {
                        $it->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                    }
                }
            } else {
                // Partial cancellation
                $item = $cancellation->item;
                if ($item) {
                    $item->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'refunded_amount' => $cancellation->refund_amount,
                    ]);
                }

                $activeItemsCount = $order->items()->where('status', 'active')->count();
                $order->update([
                    'cancellation_status' => ($activeItemsCount === 0) ? 'cancelled' : 'partially_cancelled',
                    'fulfillment_status' => ($activeItemsCount === 0) ? 'cancelled' : $order->fulfillment_status,
                ]);
            }

            // 3. Initiate Refund
            if ($cancellation->refund_amount > 0 && $order->payment_status === 'paid') {
                $this->executeRefund($cancellation, $order);
            } else {
                // No refund required (e.g. COD unpaid)
                $cancellation->update(['status' => OrderCancellation::STATUS_COMPLETED]);
            }

            // 4. Generate GST Credit Note
            if ($order->payment_status === 'paid' || $cancellation->refund_amount > 0) {
                $this->generateCreditNote($cancellation, $order);
            }

            // 5. Restore VRAZIO PRIVÉ CLUB Next Order / Birthday privilege (Strictly exactly once!)
            $this->restorePriveClubPrivilege($cancellation, $order);

            // 6. Send Approval Notification Email
            $this->safeSendMail(new CancellationNotificationMail($order, $cancellation, 'approved'), $order->client_email);

            return true;
        });
    }

    /**
     * Reject a pending cancellation request with reason.
     */
    public function rejectCancellation(
        OrderCancellation $cancellation,
        string $rejectionReason,
        ?User $rejectedBy = null,
        ?string $adminNotes = null
    ): bool {
        return DB::transaction(function () use ($cancellation, $rejectedBy, $rejectionReason, $adminNotes) {
            $cancellation = OrderCancellation::where('id', $cancellation->id)->lockForUpdate()->firstOrFail();
            $order = Order::where('id', $cancellation->order_id)->lockForUpdate()->firstOrFail();

            if (! $cancellation->isPending()) {
                throw new Exception('Only pending cancellation requests can be rejected.');
            }

            $cancellation->update([
                'status' => OrderCancellation::STATUS_REJECTED,
                'rejected_by' => $rejectedBy?->id,
                'rejected_at' => now(),
                'rejection_reason' => $rejectionReason,
                'admin_notes' => $adminNotes ? trim((string) $cancellation->admin_notes . "\n" . $adminNotes) : $cancellation->admin_notes,
            ]);

            // Revert request statuses
            if ($cancellation->type === OrderCancellation::TYPE_PARTIAL && $cancellation->item) {
                $cancellation->item->update(['status' => 'active']);
            }

            $order->update(['cancellation_status' => 'rejected']);

            $this->safeSendMail(new CancellationNotificationMail($order, $cancellation, 'rejected'), $order->client_email);

            return true;
        });
    }

    /**
     * Restore stock inventory exactly once.
     */
    public function restoreStock(OrderCancellation $cancellation, ?Order $order = null): bool
    {
        $cancellation = OrderCancellation::where('id', $cancellation->id)->lockForUpdate()->first();
        if (! $cancellation || $cancellation->is_stock_restored) {
            return false; // Guard against duplicate stock increments
        }

        $order = $order ?? Order::find($cancellation->order_id);
        if (! $order) {
            return false;
        }

        if ($cancellation->type === OrderCancellation::TYPE_FULL) {
            foreach ($order->items as $item) {
                if ($item->product_id && (int) $item->quantity > 0) {
                    Product::where('id', (int) $item->product_id)
                        ->lockForUpdate()
                        ->increment('stock_quantity', (int) $item->quantity);
                }
            }
        } elseif ($cancellation->item && $cancellation->item->product_id) {
            Product::where('id', (int) $cancellation->item->product_id)
                ->lockForUpdate()
                ->increment('stock_quantity', (int) $cancellation->item->quantity);
        }

        $cancellation->update([
            'is_stock_restored' => true,
            'stock_restored_at' => now(),
        ]);

        return true;
    }

    /**
     * Execute refund via Razorpay server API or record manual refund.
     */
    protected function executeRefund(OrderCancellation $cancellation, Order $order): OrderRefund
    {
        $gateway = ($order->payment_gateway === 'razorpay') ? 'razorpay' : 'manual';

        $refund = OrderRefund::create([
            'order_id' => $order->id,
            'order_cancellation_id' => $cancellation->id,
            'gateway' => $gateway,
            'razorpay_payment_id' => $order->razorpay_payment_id,
            'amount' => $cancellation->refund_amount,
            'currency' => 'INR',
            'status' => OrderRefund::STATUS_INITIATED,
            'initiated_at' => now(),
        ]);

        $order->update(['refund_status' => 'initiated']);
        $this->safeSendMail(new RefundNotificationMail($order, $refund, 'initiated'), $order->client_email);

        if ($gateway === 'razorpay' && $order->razorpay_payment_id) {
            try {
                $notes = [
                    'order_number' => $order->order_number,
                    'cancellation_number' => $cancellation->cancellation_number,
                    'reason' => substr($cancellation->reason, 0, 100),
                ];

                $rzpRefund = $this->razorpayService->createRefund(
                    $order->razorpay_payment_id,
                    (float) $cancellation->refund_amount,
                    $notes
                );

                $refund->update([
                    'razorpay_refund_id' => $rzpRefund->id ?? ('rfnd_' . Str::random(14)),
                    'status' => OrderRefund::STATUS_REFUNDED,
                    'completed_at' => now(),
                    'gateway_response' => (array) $rzpRefund,
                ]);

                $order->increment('total_refunded', $cancellation->refund_amount);
                $order->update(['refund_status' => 'refunded']);
                $cancellation->update(['status' => OrderCancellation::STATUS_COMPLETED]);

                $this->safeSendMail(new RefundNotificationMail($order, $refund, 'completed'), $order->client_email);
            } catch (\Throwable $e) {
                Log::error('Razorpay refund error: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                    'refund_id' => $refund->id,
                ]);

                $refund->update([
                    'status' => OrderRefund::STATUS_FAILED,
                    'failure_reason' => $e->getMessage(),
                ]);

                $order->update(['refund_status' => 'failed']);
                $this->safeSendMail(new RefundNotificationMail($order, $refund, 'failed'), $order->client_email);
            }
        } else {
            // Manual or COD refund
            $refund->update([
                'status' => OrderRefund::STATUS_PENDING_APPROVAL,
                'admin_notes' => 'Awaiting manual accounts bank transfer / NEFT payout to patron.',
            ]);
        }

        return $refund;
    }

    /**
     * Generate official GST Credit Note for accounting integrity.
     */
    public function generateCreditNote(OrderCancellation $cancellation, Order $order): CreditNote
    {
        $existing = CreditNote::where('order_cancellation_id', $cancellation->id)->first();
        if ($existing) {
            return $existing;
        }

        $itemsData = [];
        $taxableAmount = 0.0;
        $gstAmount = 0.0;

        if ($cancellation->type === OrderCancellation::TYPE_FULL) {
            $taxableAmount = round((float) $order->subtotal - (float) $order->discount_amount - (float) $order->prive_discount_amount, 2);
            $gstAmount = round((float) $order->tax_gst, 2);
            $totalCredit = (float) $cancellation->refund_amount > 0 ? (float) $cancellation->refund_amount : round($taxableAmount + $gstAmount, 2);

            foreach ($order->items as $it) {
                $itemsData[] = [
                    'title' => $it->product_title,
                    'sku' => $it->product_sku,
                    'size' => $it->size,
                    'quantity' => $it->quantity,
                    'unit_price' => (float) $it->unit_price,
                    'line_total' => (float) $it->line_total,
                ];
            }
        } elseif ($cancellation->item) {
            $it = $cancellation->item;
            $refundAmt = (float) $cancellation->refund_amount;
            $ratio = $order->total_amount > 0 ? ($refundAmt / (float) $order->total_amount) : 1.0;
            $gstAmount = round((float) $order->tax_gst * $ratio, 2);
            $taxableAmount = round($refundAmt - $gstAmount, 2);
            $totalCredit = $refundAmt;

            $itemsData[] = [
                'title' => $it->product_title,
                'sku' => $it->product_sku,
                'size' => $it->size,
                'quantity' => $it->quantity,
                'unit_price' => (float) $it->unit_price,
                'line_total' => (float) $it->line_total,
            ];
        } else {
            $taxableAmount = 0.0;
            $gstAmount = 0.0;
            $totalCredit = (float) $cancellation->refund_amount;
        }

        return CreditNote::create([
            'order_id' => $order->id,
            'order_cancellation_id' => $cancellation->id,
            'original_invoice_number' => 'VRZ-INV-' . $order->order_number,
            'invoice_date' => $order->created_at->toDateString(),
            'client_name' => $order->client_name,
            'client_email' => $order->client_email,
            'client_phone' => $order->client_phone,
            'client_address' => $order->shipping_address,
            'cancelled_items' => $itemsData,
            'taxable_amount' => $taxableAmount,
            'gst_amount' => $gstAmount,
            'total_credit_amount' => $totalCredit,
            'reason' => $cancellation->reason,
            'issued_at' => now(),
        ]);
    }

    /**
     * Restore VRAZIO PRIVÉ CLUB membership discount when a full order is cancelled.
     */
    protected function restorePriveClubPrivilege(OrderCancellation $cancellation, Order $order): void
    {
        if ($cancellation->prive_discount_restored || ((float) $order->prive_discount_amount <= 0 && empty($order->prive_member_id))) {
            return;
        }

        $membership = null;
        if (! empty($order->prive_member_id)) {
            $membership = Membership::where('member_id', $order->prive_member_id)
                ->orWhere('membership_number', $order->prive_member_id)
                ->first();
        }

        if (! $membership && $order->user_id) {
            $membership = Membership::where('user_id', $order->user_id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        if (! $membership) {
            return;
        }

        if ($membership->next_order_discount_used && ((float) $order->prive_discount_amount > 0 || $membership->next_order_order_id === $order->id)) {
            $membership->update([
                'next_order_discount_used' => false,
                'next_order_discount_used_at' => null,
                'next_order_order_id' => null,
            ]);
            Log::info("PRIVÉ Club next-order discount restored for {$membership->member_id} on order {$order->order_number}");
        }

        $orderYear = (int) ($order->created_at ? $order->created_at->format('Y') : date('Y'));
        if ((int) $membership->birthday_coupon_used_year === $orderYear) {
            $membership->update(['birthday_coupon_used_year' => null]);
            Log::info("PRIVÉ Club birthday coupon restored for {$membership->member_id} on order {$order->order_number}");
        }

        $cancellation->update([
            'prive_discount_restored' => true,
            'prive_discount_restored_at' => now(),
        ]);
    }

    /**
     * Calculate proportional refund amount for a single item within an order.
     */
    public function calculateItemRefundAmount(Order $order, OrderItem $item): float
    {
        if ($order->payment_status !== 'paid') {
            return 0.0;
        }

        $orderSubtotal = (float) $order->subtotal;
        if ($orderSubtotal <= 0) {
            return (float) $item->line_total;
        }

        $totalOrderDiscounts = (float) $order->discount_amount;
        $discountRatio = max(0.0, ($orderSubtotal - $totalOrderDiscounts) / $orderSubtotal);

        // Proportional net value + tax
        $itemShareOfPaid = (float) $item->line_total * $discountRatio;

        return round(min($itemShareOfPaid, (float) $order->total_amount), 2);
    }

    /**
     * Calculate total refund amount for an order.
     */
    public function calculateOrderRefundAmount(Order $order): float
    {
        if ($order->payment_status !== 'paid') {
            return 0.0; // COD / unpaid
        }

        $remaining = (float) $order->total_amount - (float) $order->total_refunded;

        return max(0.0, round($remaining, 2));
    }

    /**
     * Send email with graceful exception capture.
     */
    protected function safeSendMail(object $mailable, ?string $to): void
    {
        if (empty($to)) {
            return;
        }

        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Email notification failed to send: ' . $e->getMessage(), ['to' => $to]);
        }
    }
}

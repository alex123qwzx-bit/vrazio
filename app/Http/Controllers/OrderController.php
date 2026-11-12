<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display customer "My Orders" vault with live warranty claim actions and statuses.
     * Enforces authentication: only logged-in patrons can access /orders (showing their own orders).
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'Please sign in to view your orders.');
        }

        $user = auth()->user();
        $query = Order::with(['items.product', 'warrantyClaim', 'cancellations.refunds', 'creditNotes'])->latest();

        if ($user->isAdmin() || in_array($user->role, ['admin', 'super_admin'], true)) {
            // Admins and Super Admins can search across all orders
            $search = $request->query('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('client_phone', 'like', "%{$search}%")
                      ->orWhere('client_email', 'like', "%{$search}%");
                });
            }
        } else {
            // Patrons strictly see only their own orders
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('client_phone', $user->phone)
                  ->orWhere('client_email', $user->email);
            });

            $search = $request->query('search');
            if ($search) {
                $query->where('order_number', 'like', "%{$search}%");
            }
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'phone' => $user->phone,
            'search' => $search ?? null,
        ]);
    }

    /**
     * Display printable transit docket and confirmation.
     * Enforces ownership access check.
     */
    public function confirmation(Request $request): View|RedirectResponse
    {
        $orderNumber = $request->query('orderNumber') ?? $request->query('order_number');

        if (! $orderNumber) {
            return redirect()->route('home');
        }

        $order = Order::with(['items.product', 'warrantyClaim', 'cancellations.refunds', 'creditNotes'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Allow access if user is authorized or if this order was placed in the current session
        $recentOrder = session('recently_placed_order');
        if ($recentOrder !== $order->order_number && ! $order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to order confirmation.');
        }

        return view('orders.confirmation', compact('order'));
    }

    /**
     * Track an order by reference code with live warranty claim status.
     * Enforces ownership validation: prevents unverified phone lookup and unauthorized order inspection.
     */
    public function track(Request $request, ?string $orderNumber = null): View
    {
        $reference = $orderNumber ?? $request->input('order_number');
        $order = null;
        $accessDenied = false;

        if ($reference) {
            $cleaned = strtoupper(trim((string) $reference));
            if (! str_starts_with($cleaned, 'VRZ-')) {
                $cleanedWithPrefix = 'VRZ-' . ltrim($cleaned, '#');
            } else {
                $cleanedWithPrefix = $cleaned;
            }

            $locatedOrder = Order::with(['items.product', 'warrantyClaim.images', 'cancellations.refunds', 'creditNotes'])
                ->where('order_number', $cleaned)
                ->orWhere('order_number', $cleanedWithPrefix)
                ->latest()
                ->first();

            if ($locatedOrder) {
                if ($locatedOrder->isAccessibleBy(auth()->user())) {
                    $order = $locatedOrder;
                } else {
                    $accessDenied = true;
                }
            }
        }

        return view('orders.track', compact('order', 'reference', 'accessDenied'));
    }

    /**
     * Get real-time cancellation eligibility and refund breakdown for an order or item.
     */
    public function checkCancellationEligibility(Request $request, string $orderNumber): \Illuminate\Http\JsonResponse
    {
        $order = Order::with('items')->where('order_number', trim($orderNumber))->firstOrFail();

        if (! $order->isAccessibleBy(auth()->user())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $itemId = $request->query('item_id');
        $item = $itemId ? $order->items->firstWhere('id', (int) $itemId) : null;

        $service = app(\App\Services\CancellationRefundService::class);
        $eligibility = $service->checkEligibility($order, $item);

        return response()->json([
            'success' => true,
            'eligibility' => $eligibility,
            'order' => [
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'payment_gateway' => $order->payment_gateway,
                'total_amount' => (float) $order->total_amount,
            ],
            'item' => $item ? [
                'id' => $item->id,
                'title' => $item->product_title,
                'line_total' => (float) $item->line_total,
            ] : null,
        ]);
    }

    /**
     * Cancel an active order or individual item by customer.
     */
    public function cancel(Request $request, string $orderNumber): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $order = Order::with('items')->where('order_number', trim($orderNumber))->firstOrFail();

        if (! $order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to cancel this order.');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'order_item_id' => ['nullable', 'integer', 'exists:order_items,id'],
        ]);

        $reason = $validated['reason'] ?? 'Cancelled by customer via portal';
        $comments = $validated['customer_notes'] ?? null;
        $itemId = $validated['order_item_id'] ?? null;
        $item = $itemId ? $order->items->firstWhere('id', (int) $itemId) : null;

        $service = app(\App\Services\CancellationRefundService::class);

        try {
            $cancellation = $service->requestCancellation($order, $reason, $comments, $item, auth()->user());

            $msg = $cancellation->isPending()
                ? "Cancellation request for order #{$order->order_number} has been submitted for immediate atelier manager review."
                : "Order #{$order->order_number} has been successfully cancelled. Reserved inventory was restored to stock.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'cancellation' => $cancellation,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}

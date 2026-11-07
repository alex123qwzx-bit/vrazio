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
        $query = Order::with(['items.product', 'warrantyClaim'])->latest();

        if ($user->role === 'admin') {
            // Admins can search across all orders
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
        $orderNumber = $request->query('orderNumber');

        if (! $orderNumber) {
            return redirect()->route('home');
        }

        $order = Order::with(['items.product', 'warrantyClaim'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! $order->isAccessibleBy(auth()->user())) {
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

            $locatedOrder = Order::with(['items.product', 'warrantyClaim.images'])
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
}

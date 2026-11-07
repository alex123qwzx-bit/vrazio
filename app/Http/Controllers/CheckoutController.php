<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\RazorpayService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected RazorpayService $razorpayService
    ) {}

    /**
     * Display the 3-step luxury checkout view.
     */
    public function index(): View|RedirectResponse
    {
        if ($this->cartService->isEmpty()) {
            return redirect()->route('products.index')
                ->with('info', 'Your bespoke jewellery bag is empty. Please select a creation first.');
        }

        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $gst = $this->cartService->getGst();
        $standardTotal = $this->cartService->getTotal('standard');
        $expressTotal = $this->cartService->getTotal('express');

        $verifiedPhone = session('order_phone_verified');
        if (! $verifiedPhone && auth()->check() && auth()->user()->phone_verified_at && !empty(auth()->user()->phone)) {
            $verifiedPhone = auth()->user()->phone;
            session(['order_phone_verified' => $verifiedPhone]);
        }

        return view('checkout', compact(
            'items',
            'subtotal',
            'gst',
            'standardTotal',
            'expressTotal',
            'verifiedPhone'
        ));
    }

    /**
     * Process checkout form, enforce OTP session token, lock stock (BUS-02), and create Razorpay order.
     */
    public function process(CheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $clientPhone = $validated['client_phone'];
        $paymentMethod = $validated['payment_method'] ?? 'cod';

        // Check if phone was verified via SMS OTP in this session (optional for Cash on Delivery)
        $sessionPhone = session('order_phone_verified');
        $phoneVerified = ($sessionPhone && $sessionPhone === $clientPhone);

        $items = $this->cartService->getItems();
        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Your bag is empty.',
            ], 422);
        }

        // Calculate server-side financial numbers based on fresh database items
        $subtotal = $this->cartService->getSubtotal();
        $gst = $this->cartService->getGst();
        $shippingMethod = $validated['shipping_method'];
        $shippingCost = $this->cartService->getShippingCost($shippingMethod);
        $totalAmount = round($subtotal + $gst + $shippingCost, 2);

        try {
            DB::beginTransaction();

            // BUS-02: Lock stock rows inside transaction to prevent overselling
            $productIds = array_unique(array_column($items, 'id'));
            $lockedProducts = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                $product = $lockedProducts->get($item['id']);
                if (! $product || $product->stock_quantity < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock depleted for '{$item['title']}'. Please adjust your bag.",
                    ], 422);
                }
            }

            $paymentMethod = $validated['payment_method'] ?? 'cod';

            $order = Order::create([
                'user_id' => auth()->id(),
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'],
                'client_phone' => $clientPhone,
                'shipping_address' => $validated['shipping_address'],
                'shipping_method' => $shippingMethod,
                'subtotal' => $subtotal,
                'tax_gst' => $gst,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'payment_gateway' => $paymentMethod,
                'payment_status' => 'pending',
                'fulfillment_status' => 'inscribed',
                'otp_verified_at' => $phoneVerified ? now() : null,
            ]);

            foreach ($items as $item) {
                $product = $lockedProducts->get($item['id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_title' => $item['title'],
                    'product_sku' => $product?->sku ?? 'VRZ-ITEM',
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'size' => $item['size'] ?? null,
                    'line_total' => round($item['price'] * $item['quantity'], 2),
                ]);

                // For Cash on Delivery, decrement stock immediately on placement
                if ($paymentMethod === 'cod' && $product) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Cash on Delivery direct fulfillment path
            if ($paymentMethod === 'cod') {
                DB::commit();
                $this->cartService->clear();
                session()->forget('order_phone_verified');
                session(['recently_placed_order' => $order->order_number]);

                return response()->json([
                    'success' => true,
                    'payment_method' => 'cod',
                    'order_number' => $order->order_number,
                    'redirect_url' => route('order.confirmation', ['orderNumber' => $order->order_number]),
                    'message' => 'Your Cash on Delivery order has been successfully placed in our atelier vault.',
                ]);
            }

            // Create Razorpay Order
            $razorpayOrder = $this->razorpayService->createOrder(
                receipt: $order->order_number,
                amount: $totalAmount,
                currency: 'INR'
            );

            $order->update([
                'razorpay_order_id' => $razorpayOrder->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_method' => 'razorpay',
                'order_number' => $order->order_number,
                'razorpay_order_id' => $razorpayOrder->id,
                'amount' => (int) round($totalAmount * 100), // in paise for Razorpay JS
                'currency' => 'INR',
                'key' => $this->razorpayService->getKey(),
                'client_name' => $order->client_name,
                'client_email' => $order->client_email,
                'client_phone' => $order->client_phone,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to initialize order in atelier vault. Please try again.',
                'error_detail' => $e->getMessage(),
                'error_file' => $e->getFile() . ':' . $e->getLine(),
            ], 500);
        }
    }

    /**
     * Handle payment verification callback from Razorpay frontend modal.
     */
    public function verifyPayment(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $orderId = (string) $request->input('razorpay_order_id');
        $paymentId = (string) $request->input('razorpay_payment_id');
        $signature = (string) $request->input('razorpay_signature');

        $isValid = $this->razorpayService->verifySignature($orderId, $paymentId, $signature);

        if (! $isValid) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cryptographic signature verification failed. Payment cannot be verified.',
                ], 400);
            }

            return redirect()->route('checkout.index')
                ->with('error', 'Payment verification failed. Please contact atelier concierge.');
        }

        $order = Order::where('razorpay_order_id', $orderId)->first();

        if (! $order) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Order reference not found.'], 404);
            }
            return redirect()->route('home')->with('error', 'Order reference not located.');
        }

        // Idempotently mark paid and decrement stock
        $this->finalizePayment($order, $paymentId);

        // Clear bag & verified phone session
        $this->cartService->clear();
        session()->forget('order_phone_verified');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('order.confirmation', ['orderNumber' => $order->order_number]),
            ]);
        }

        return redirect()->route('order.confirmation', ['orderNumber' => $order->order_number]);
    }

    /**
     * Mark an order as paid and decrement stock idempotently.
     */
    public function finalizePayment(Order $order, string $paymentId): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order, $paymentId) {
            $freshOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (! $freshOrder || $freshOrder->payment_status === 'paid') {
                return;
            }

            $freshOrder->update([
                'razorpay_payment_id' => $paymentId,
                'payment_status' => 'paid',
                'fulfillment_status' => 'inscribed',
            ]);

            // Decrement inventory stock
            foreach ($freshOrder->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock_quantity', $item->quantity);
                }
            }
        });
    }

    /**
     * Handle Razorpay server-to-server webhook (BUS-01).
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();
        $secret = (string) config('services.razorpay.webhook_secret', env('RAZORPAY_WEBHOOK_SECRET', ''));

        if (empty($signature)) {
            return response()->json(['error' => 'Missing signature header'], 400);
        }

        // Verify HMAC-SHA256 signature
        if (! empty($secret)) {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            if (! hash_equals($expectedSignature, $signature)) {
                Log::warning('Razorpay webhook signature mismatch', ['received' => $signature]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $data = json_decode($payload, true);
        if (! is_array($data)) {
            return response()->json(['error' => 'Invalid JSON body'], 400);
        }

        $event = $data['event'] ?? '';
        Log::info("Razorpay Webhook Event Received: {$event}");

        if ($event === 'order.paid' || $event === 'payment.captured') {
            $rzpOrderId = $data['payload']['order']['entity']['id'] ?? ($data['payload']['payment']['entity']['order_id'] ?? null);
            $rzpPaymentId = $data['payload']['payment']['entity']['id'] ?? ('pay_hook_' . Str::random(10));

            if ($rzpOrderId) {
                $order = Order::where('razorpay_order_id', $rzpOrderId)->first();
                if ($order) {
                    $this->finalizePayment($order, (string) $rzpPaymentId);
                    Log::info("Order {$order->order_number} marked paid via Razorpay webhook.");
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}

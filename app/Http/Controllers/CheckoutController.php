<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteThemeSetting;
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

        // Require customer authentication to proceed to checkout / place an order
        if (! auth()->check()) {
            session()->put('url.intended', route('checkout.index'));
            return redirect()->route('login')
                ->with('info', 'Please sign in or create an account to proceed with your bespoke order.');
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

        $indianStates = CartService::INDIAN_STATES;
        $codStateRates = SiteThemeSetting::getSetting('cod_state_rates', []);
        if (! is_array($codStateRates)) {
            $codStateRates = json_decode((string) $codStateRates, true) ?: [];
        }

        return view('checkout', compact(
            'items',
            'subtotal',
            'gst',
            'standardTotal',
            'expressTotal',
            'verifiedPhone',
            'indianStates',
            'codStateRates'
        ));
    }

    /**
     * Process checkout form, enforce OTP session token, lock stock (BUS-02), and create Razorpay order.
     */
    public function process(CheckoutRequest $request): JsonResponse
    {
        // Enforce authentication at order placement
        if (! auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please sign in or create an account to place your order.',
                'redirect' => route('login'),
            ], 401);
        }

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
        $stateName = $validated['shipping_address']['state'] ?? '';
        $codFee = ($paymentMethod === 'cod') ? CartService::getCodFeeForState($stateName) : 0.0;
        $shippingCost = round($shippingCost + $codFee, 2);
        $totalAmount = round($subtotal + $gst + $shippingCost, 2);

        $queryLog = [];
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queryLog) {
            $queryLog[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time_ms' => $query->time,
            ];
        });

        try {
            // Check stock rows to prevent overselling
            $productIds = array_unique(array_column($items, 'id'));
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item['id']);
                if (! $product || $product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock depleted for '{$item['title']}'. Please adjust your bag.",
                    ], 422);
                }
            }

            $paymentMethod = $validated['payment_method'] ?? 'cod';
            $orderNumber = 'VRZ-' . strtoupper(Str::random(8));

            $order = Order::create([
                'order_number' => $orderNumber,
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
                $product = $products->get($item['id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => (int) $item['id'],
                    'product_title' => $item['title'],
                    'product_sku' => $product?->sku ?? 'VRZ-ITEM',
                    'unit_price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'size' => $item['size'] ?? null,
                    'line_total' => round((float) $item['price'] * (int) $item['quantity'], 2),
                ]);

                // For Cash on Delivery, decrement stock immediately on placement
                if ($paymentMethod === 'cod') {
                    Product::where('id', (int) $item['id'])
                        ->where('stock_quantity', '>=', (int) $item['quantity'])
                        ->decrement('stock_quantity', (int) $item['quantity']);
                }
            }

            // Cash on Delivery direct fulfillment path
            if ($paymentMethod === 'cod') {
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

            return response()->json([
                'success' => true,
                'payment_method' => 'razorpay',
                'is_simulated' => $this->razorpayService->isSimulated(),
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
            if (isset($order) && $order->exists) {
                try {
                    $order->items()->delete();
                    $order->delete();
                } catch (\Throwable) {
                    // Ignore rollback cleanup error
                }
            }
            Log::error('Order creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'queries' => $queryLog,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We made a mistake while initializing your order. Our team has been notified. Please try again shortly.',
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

        $order->update([
            'razorpay_payment_id' => $paymentId,
            'payment_status' => 'paid',
            'fulfillment_status' => 'inscribed',
        ]);

        // Decrement inventory stock atomically
        foreach ($order->items as $item) {
            if ($item->product_id) {
                Product::where('id', (int) $item->product_id)
                    ->where('stock_quantity', '>=', (int) $item->quantity)
                    ->decrement('stock_quantity', (int) $item->quantity);
            }
        }
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

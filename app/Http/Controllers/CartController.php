<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(): View
    {
        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $voucher = $this->cartService->getVoucher();
        $voucherDiscount = $this->cartService->getDiscount();
        $priveDiscount = $this->cartService->getPriveDiscount();
        $discount = $this->cartService->getTotalDiscount();
        $gst = $this->cartService->getGst();
        $total = $this->cartService->getTotal('standard');
        $membership = auth()->check() ? auth()->user()->activeMembership() : null;

        return view('cart.index', compact(
            'items',
            'subtotal',
            'voucher',
            'voucherDiscount',
            'priveDiscount',
            'discount',
            'membership',
            'gst',
            'total'
        ));
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $productId = (int) $request->input('product_id');
        $quantity = max(1, (int) $request->input('quantity', 1));
        $size = $request->input('size') ? trim((string) $request->input('size')) : null;

        $product = Product::findOrFail($productId);

        if ($product->stock_quantity < $quantity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity exceeds available stock.',
                ], 422);
            }
            return back()->with('error', 'Exceeds available stock.');
        }

        $this->cartService->addItem($product, $quantity, $size);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$product->title} added to your cart.",
                'item_count' => $this->cartService->getItemCount(),
                'subtotal' => $this->cartService->getSubtotal(),
                'discount' => $this->cartService->getDiscount(),
                'gst' => $this->cartService->getGst(),
                'total' => $this->cartService->getTotal(),
            ]);
        }

        return redirect()->route('cart.index')->with('success', "{$product->title} added to your cart.");
    }

    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $quantity = (int) $validated['quantity'];
        $this->cartService->updateQuantity($id, $quantity);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'subtotal' => $this->cartService->getSubtotal(),
                'discount' => $this->cartService->getDiscount(),
                'gst' => $this->cartService->getGst(),
                'total' => $this->cartService->getTotal(),
                'item_count' => $this->cartService->getItemCount(),
            ]);
        }

        return back();
    }

    public function remove(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $this->cartService->removeItem($id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'subtotal' => $this->cartService->getSubtotal(),
                'discount' => $this->cartService->getDiscount(),
                'gst' => $this->cartService->getGst(),
                'total' => $this->cartService->getTotal(),
                'item_count' => $this->cartService->getItemCount(),
            ]);
        }

        return back()->with('success', 'Item removed from your cart.');
    }

    public function clear(): RedirectResponse
    {
        $this->cartService->clear();
        return redirect()->route('cart.index');
    }

    /**
     * Apply a voucher code to the session cart.
     */
    public function applyVoucher(Request $request): JsonResponse|RedirectResponse
    {
        $code = (string) $request->input('code', '');
        $result = $this->cartService->applyVoucher($code);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return redirect()->route('cart.index')->with('success', $result['message']);
        }

        return redirect()->route('cart.index')->with('error', $result['message']);
    }

    /**
     * Remove the active voucher from session cart.
     */
    public function removeVoucher(Request $request): JsonResponse|RedirectResponse
    {
        $this->cartService->removeVoucher();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Voucher privilege removed.',
                'subtotal' => $this->cartService->getSubtotal(),
                'discount' => 0.0,
                'gst' => $this->cartService->getGst(),
                'total' => $this->cartService->getTotal(),
                'item_count' => $this->cartService->getItemCount(),
            ]);
        }

        return redirect()->route('cart.index')->with('info', 'Voucher privilege removed.');
    }
}

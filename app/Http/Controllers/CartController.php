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
        $gst = $this->cartService->getGst();
        $total = $this->cartService->getTotal('standard');

        return view('cart.index', compact('items', 'subtotal', 'gst', 'total'));
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
            ]);
        }

        return redirect()->route('cart.index')->with('success', "{$product->title} added to your cart.");
    }

    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        $this->cartService->updateQuantity($id, $quantity);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'subtotal' => $this->cartService->getSubtotal(),
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
}

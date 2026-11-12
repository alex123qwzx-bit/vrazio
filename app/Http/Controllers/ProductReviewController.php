<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    /**
     * Submit a customer review for a product.
     */
    public function store(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please sign in to your patron account to submit a review.',
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Please sign in to submit a review.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'min:10', 'max:2500'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        // Check verified purchase against customer orders
        $verifiedOrder = ProductReview::findVerifiedOrder((int) $user->id, (int) $product->id);
        $isVerified = $verifiedOrder !== null;

        // Check if customer already submitted a review for this product
        $existingReview = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            $msg = 'You have already submitted a review for this bespoke piece. Our atelier values your contribution.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        // Process photo uploads securely with strict extension resolution
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $safeExt = in_array(strtolower((string) $photo->extension()), ['jpg', 'jpeg', 'png', 'webp'], true)
                        ? $photo->extension()
                        : 'jpg';
                    $fileName = Str::uuid() . '.' . $safeExt;
                    $path = $photo->storeAs('reviews', $fileName, 'public');
                    $photoPaths[] = $path;
                }
            }
        }

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $verifiedOrder?->id,
            'rating' => (int) $validated['rating'],
            'title' => strip_tags(trim($validated['title'])),
            'content' => strip_tags(trim($validated['content'])),
            'photos' => !empty($photoPaths) ? $photoPaths : null,
            'is_verified_purchase' => $isVerified,
            'status' => ProductReview::STATUS_PENDING,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $successMessage = $isVerified
            ? 'Thank you for sharing your experience! Your Verified Purchase review has been submitted and will appear on the storefront upon moderation.'
            : 'Thank you! Your patron review has been submitted for moderation.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'is_verified_purchase' => $review->is_verified_purchase,
                    'status' => $review->status,
                ],
            ], 201);
        }

        return back()->with('review_success', $successMessage);
    }

    /**
     * Check verification eligibility for the logged-in customer.
     */
    public function checkEligibility(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'is_verified' => false,
                'has_reviewed' => false,
            ]);
        }

        $verifiedOrder = ProductReview::findVerifiedOrder((int) $user->id, (int) $product->id);
        $existingReview = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'authenticated' => true,
            'is_verified' => $verifiedOrder !== null,
            'order_number' => $verifiedOrder?->order_number,
            'has_reviewed' => $existingReview !== null,
            'review_status' => $existingReview?->status,
        ]);
    }

    /**
     * Display logged-in customer's submitted reviews.
     */
    public function customerReviews(): View
    {
        $user = Auth::user();
        $reviews = ProductReview::with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('account.reviews', compact('reviews'));
    }
}

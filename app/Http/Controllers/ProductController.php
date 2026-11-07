<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Supported categories across jewellery & couture atelier.
     */
    protected const CATEGORIES = ['rings', 'necklaces', 'bracelets', 'earrings', 'solitaires', 'couture'];

    /**
     * Display product catalog with multi-dimensional filtering.
     */
    public function index(Request $request): View
    {
        $query = Product::query()->inStock();

        // Category filter
        if ($request->filled('category')) {
            $category = $request->input('category');
            if (in_array($category, self::CATEGORIES, true)) {
                $query->where('category', $category);
            }
        }

        // Tag filter
        if ($request->filled('tag')) {
            $tag = (string) $request->input('tag');
            $query->where(function ($q) use ($tag): void {
                $q->whereJsonContains('tags', $tag)
                  ->orWhere('tags', 'like', "%{$tag}%");
            });
        }

        // Metal / Material filter
        if ($request->filled('metal_type')) {
            $query->where('metal_type', $request->input('metal_type'));
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        // Search keyword
        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        // Certification filter
        if ($request->boolean('certified_only')) {
            $query->whereNotNull('certification');
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'title_asc' => $query->orderBy('title', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $metalTypes = Product::select('metal_type')->whereNotNull('metal_type')->distinct()->pluck('metal_type');

        return view('products.index', compact('products', 'metalTypes'));
    }

    /**
     * Category dedicated exhibition.
     */
    public function category(string $category): View
    {
        abort_unless(
            in_array($category, self::CATEGORIES, true),
            404
        );

        // If category is couture, redirect or use couture view
        if ($category === 'couture') {
            return $this->couture(request());
        }

        $products = Product::where('category', $category)
            ->inStock()
            ->latest()
            ->paginate(12);

        $metalTypes = Product::where('category', $category)
            ->select('metal_type')
            ->distinct()
            ->pluck('metal_type');

        return view('products.index', [
            'products' => $products,
            'metalTypes' => $metalTypes,
            'currentCategory' => $category,
        ]);
    }

    /**
     * Dedicated Haute Couture & Royal Apparel Exhibition.
     */
    public function couture(Request $request): View
    {
        $query = Product::where('category', 'couture')->inStock();

        // Tag filter (e.g. Bridal Lehenga, Banarasi Silk, Sherwani, etc.)
        if ($request->filled('tag') && $request->input('tag') !== 'All Couture') {
            $tag = (string) $request->input('tag');
            $query->where(function ($q) use ($tag): void {
                $q->whereJsonContains('tags', $tag)
                  ->orWhere('tags', 'like', "%{$tag}%");
            });
        }

        // Fabric / Material filter
        if ($request->filled('metal_type')) {
            $query->where('metal_type', $request->input('metal_type'));
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        // Search
        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'title_asc' => $query->orderBy('title', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $tags = [
            'All Couture',
            'Bridal Lehenga',
            'Banarasi Silk',
            'Royal Sherwani',
            'Chikankari',
            'Organza Lehenga',
            'Tissue Silk',
            'Bandhgala',
            'Pashmina Cape',
            'Zardozi',
            'Wedding',
        ];

        $fabrics = Product::where('category', 'couture')
            ->select('metal_type')
            ->whereNotNull('metal_type')
            ->distinct()
            ->pluck('metal_type');

        return view('products.couture', [
            'products' => $products,
            'currentTag' => $request->input('tag'),
            'tags' => $tags,
            'fabrics' => $fabrics,
        ]);
    }

    /**
     * Display a specific piece with 4K atelier zoom and provenance specifications.
     */
    public function show(string $slug): View
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Full-text / LIKE search endpoint.
     */
    public function search(Request $request): View
    {
        $q = (string) $request->input('q', '');

        $products = Product::search($q)
            ->inStock()
            ->paginate(12)
            ->withQueryString();

        $metalTypes = Product::select('metal_type')->whereNotNull('metal_type')->distinct()->pluck('metal_type');

        return view('products.index', [
            'products' => $products,
            'metalTypes' => $metalTypes,
            'searchQuery' => $q,
        ]);
    }
}

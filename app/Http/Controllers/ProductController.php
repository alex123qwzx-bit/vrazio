<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Supported categories across luxury e-commerce catalog.
     */
    protected const PRIMARY_CATEGORIES = ['jewelry', 'clothing', 'watches', 'footwear'];
    protected const LEGACY_SUBCATEGORIES = ['rings', 'necklaces', 'bracelets', 'earrings', 'solitaires', 'couture', 'clothes'];

    /**
     * Display product catalog with multi-dimensional filtering.
     */
    public function index(Request $request): View
    {
        $query = Product::query()->inStock();

        // Category filter
        if ($request->filled('category')) {
            $cat = strtolower(trim((string) $request->input('category')));

            if ($cat === 'jewelry') {
                $query->where('category', 'jewelry');
            } elseif ($cat === 'clothing' || $cat === 'couture' || $cat === 'clothes') {
                $query->where('category', 'clothing');
            } elseif ($cat === 'watches' || $cat === 'watch') {
                $query->where('category', 'watches');
            } elseif ($cat === 'footwear' || $cat === 'shoes') {
                $query->where('category', 'footwear');
            } elseif (in_array($cat, ['rings', 'necklaces', 'bracelets', 'earrings', 'solitaires'], true)) {
                $query->where('category', 'jewelry')
                    ->where(function ($q) use ($cat): void {
                        $singular = rtrim($cat, 's');
                        $q->where('title', 'like', "%{$singular}%")
                          ->orWhere('tags', 'like', "%{$singular}%")
                          ->orWhere('tags', 'like', "%{$cat}%");
                    });
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

        // Price range (using effective / final price)
        if ($request->filled('min_price')) {
            $minP = (float) $request->input('min_price');
            $query->whereRaw('COALESCE(final_price, sale_price, price) >= ?', [$minP]);
        }
        if ($request->filled('max_price')) {
            $maxP = (float) $request->input('max_price');
            $query->whereRaw('COALESCE(final_price, sale_price, price) <= ?', [$maxP]);
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
            'price_low' => $query->orderByRaw('COALESCE(final_price, sale_price, price) ASC'),
            'price_high' => $query->orderByRaw('COALESCE(final_price, sale_price, price) DESC'),
            'title_asc' => $query->orderBy('title', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $metalTypes = Product::select('metal_type')->whereNotNull('metal_type')->distinct()->pluck('metal_type');

        return view('products.index', [
            'products' => $products,
            'metalTypes' => $metalTypes,
            'currentCategory' => $request->input('category'),
        ]);
    }

    /**
     * Category dedicated exhibition.
     */
    public function category(string $category): View
    {
        $cat = strtolower(trim($category));
        $validCategories = array_merge(self::PRIMARY_CATEGORIES, self::LEGACY_SUBCATEGORIES);

        abort_unless(
            in_array($cat, $validCategories, true),
            404
        );

        // If category is couture/clothes, map to clothing or use dedicated view
        if ($cat === 'couture' || $cat === 'clothes') {
            return $this->couture(request());
        }

        $query = Product::query()->inStock();

        if (in_array($cat, self::PRIMARY_CATEGORIES, true)) {
            $query->where('category', $cat);
        } else {
            // Legacy subcategory within jewelry
            $singular = rtrim($cat, 's');
            $query->where('category', 'jewelry')
                ->where(function ($q) use ($cat, $singular): void {
                    $q->where('title', 'like', "%{$singular}%")
                      ->orWhere('tags', 'like', "%{$singular}%")
                      ->orWhere('tags', 'like', "%{$cat}%");
                });
        }

        $products = $query->latest()->paginate(12);

        $metalTypes = Product::where('category', in_array($cat, self::PRIMARY_CATEGORIES, true) ? $cat : 'jewelry')
            ->select('metal_type')
            ->distinct()
            ->pluck('metal_type');

        return view('products.index', [
            'products' => $products,
            'metalTypes' => $metalTypes,
            'currentCategory' => $cat,
        ]);
    }

    /**
     * Dedicated Haute Couture & Royal Apparel Exhibition.
     */
    public function couture(Request $request): View
    {
        $query = Product::whereIn('category', ['clothing', 'couture'])->inStock();

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

        $fabrics = Product::whereIn('category', ['clothing', 'couture'])
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

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\HomepageManagerService;
use App\Services\ThemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(
        protected ThemeService $themeService,
        protected HomepageManagerService $homepageService
    ) {}

    public function index(Request $request): View
    {
        $themeSettings = $this->themeService->getSettings();
        
        // Support Draft Preview for authenticated administrators
        $isPreview = false;
        if ($request->query('preview') === 'draft' && Auth::check() && Auth::user()->isAdmin()) {
            $homepageConfig = $this->homepageService->getDraft();
            $isPreview = true;
        } else {
            $homepageConfig = $this->homepageService->getPublished();
        }

        // 1. Featured Products / Atelier Curations based on configuration
        $prodConfig = $homepageConfig['featured_products_section'] ?? [];
        $collectionType = $prodConfig['collection_type'] ?? 'featured';
        $limit = max(1, (int) ($prodConfig['limit'] ?? 8));

        if ($collectionType === 'manual' && !empty($prodConfig['selected_product_ids'])) {
            $featuredProducts = Product::whereIn('id', (array) $prodConfig['selected_product_ids'])
                ->inStock()
                ->get();
        } elseif ($collectionType === 'new_arrivals') {
            $featuredProducts = Product::inStock()
                ->latest()
                ->take($limit)
                ->get();
        } else {
            $featuredProducts = Product::featured()
                ->inStock()
                ->latest()
                ->take($limit)
                ->get();

            // Graceful fallback if no products are marked as featured yet
            if ($featuredProducts->isEmpty()) {
                $featuredProducts = Product::inStock()
                    ->latest()
                    ->take($limit)
                    ->get();
            }
        }

        // 2. Curated Categories from configuration or active DB categories
        $catConfig = $homepageConfig['category_section'] ?? [];
        $rawCatItems = $catConfig['items'] ?? [];
        
        $curatedCategories = [];
        if (!empty($rawCatItems) && is_array($rawCatItems)) {
            foreach ($rawCatItems as $item) {
                if (!isset($item['is_active']) || $item['is_active']) {
                    $curatedCategories[] = [
                        'name' => $item['name'] ?? 'Collection',
                        'slug' => $item['slug'] ?? 'collections',
                        'image' => $item['image'] ?? 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80',
                        'description' => $item['description'] ?? '',
                    ];
                }
            }
        }

        // If config is empty, fallback to database categories
        if (empty($curatedCategories)) {
            $dbCategories = Category::active()->orderBy('sort_order')->take(6)->get();
            foreach ($dbCategories as $cat) {
                $curatedCategories[] = [
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'image' => $cat->image ?? 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80',
                    'description' => $cat->description ?? '',
                ];
            }
        }

        // 3. Customer Testimonials & Reviews
        $testimonialConfig = $homepageConfig['testimonials_section'] ?? [];
        $reviewLimit = max(1, (int) ($testimonialConfig['review_count'] ?? 3));
        
        $featuredReviews = ProductReview::approved()
            ->featured()
            ->with(['user', 'product'])
            ->orderBy('sort_order')
            ->latest()
            ->take($reviewLimit)
            ->get();

        return view('home', compact(
            'themeSettings',
            'homepageConfig',
            'featuredProducts',
            'curatedCategories',
            'featuredReviews',
            'isPreview'
        ));
    }
}

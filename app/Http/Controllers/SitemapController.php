<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic Google-compliant XML Sitemap.
     */
    public function index(): Response
    {
        $staticUrls = [
            [
                'loc' => url('/'),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('products.index'),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => route('products.clothes'),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => route('prive-club.index'),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => route('pages.about'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('pages.faq'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('pages.warranty'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('pages.shipping'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('pages.returns'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('pages.privacy'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('pages.terms'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
        ];

        // Categories
        $categories = Product::select('category')->distinct()->whereNotNull('category')->pluck('category')->toArray();
        if (empty($categories)) {
            $categories = ['rings', 'necklaces', 'bracelets', 'earrings', 'solitaires', 'jewelry', 'clothing', 'watches', 'footwear'];
        }
        foreach ($categories as $cat) {
            $staticUrls[] = [
                'loc' => route('products.category', ['category' => $cat]),
                'lastmod' => now()->startOfWeek()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Active Products
        $products = Product::where('stock_quantity', '>', 0)
            ->latest('updated_at')
            ->get(['slug', 'updated_at']);

        $content = view('sitemap', [
            'staticUrls' => $staticUrls,
            'products' => $products,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}

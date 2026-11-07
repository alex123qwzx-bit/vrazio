<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ThemeService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected ThemeService $themeService
    ) {}

    public function index(): View
    {
        $themeSettings = $this->themeService->getSettings();
        
        $featuredProducts = Product::featured()
            ->inStock()
            ->latest()
            ->take(8)
            ->get();

        $curatedCategories = [
            [
                'name' => 'Rings',
                'slug' => 'rings',
                'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80',
                'description' => 'Solitaires, cocktail & eternity bands crafted in 18KT gold.',
            ],
            [
                'name' => 'Necklaces',
                'slug' => 'necklaces',
                'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=800&q=80',
                'description' => 'Imperial chokers, diamond pendants & bespoke haute colliers.',
            ],
            [
                'name' => 'Bracelets',
                'slug' => 'bracelets',
                'image' => 'https://images.unsplash.com/photo-1611591475810-7e3fbe3f8f1c?auto=format&fit=crop&w=800&q=80',
                'description' => 'Tennis bracelets and sculpted cuffs in Platinum 950.',
            ],
            [
                'name' => 'Earrings',
                'slug' => 'earrings',
                'image' => 'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=800&q=80',
                'description' => 'Cascade chandeliers, diamond studs & royal drop earrings.',
            ],
            [
                'name' => 'Solitaires',
                'slug' => 'solitaires',
                'image' => 'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?auto=format&fit=crop&w=800&q=80',
                'description' => 'GIA certified triple-excellent cut diamond solitaires.',
            ],
            [
                'name' => 'Haute Couture',
                'slug' => 'couture',
                'image' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=800&q=80',
                'description' => 'Bridal lehengas, pure Banarasi silks & royal velvet sherwanis.',
            ],
        ];

        return view('home', compact('themeSettings', 'featuredProducts', 'curatedCategories'));
    }
}

@extends('layouts.app')

@section('title', isset($currentCategory) ? ucfirst($currentCategory) . ' Collection' : 'High Jewellery Catalog')
@section('meta_description', 'Explore VRAZIO master catalogue of BIS Hallmarked rings, necklaces, bracelets, earrings, and solitaires.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'Collections' => route('products.index'),
            isset($currentCategory) ? ucfirst($currentCategory) : (isset($searchQuery) ? 'Search: '.$searchQuery : 'All Pieces') => ''
        ]" />

        {{-- Page Header --}}
        <div class="text-center max-w-3xl mx-auto my-8">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                Atelier Vault
            </span>
            <h1 class="font-display text-3xl sm:text-5xl font-bold text-[#0E261C] mt-2">
                @if(isset($currentCategory))
                    The {{ ucfirst($currentCategory) }} Collection
                @elseif(isset($searchQuery))
                    Results for &ldquo;{{ $searchQuery }}&rdquo;
                @else
                    All Haute Joaillerie Creations
                @endif
            </h1>
            <div class="w-20 h-px bg-[#D4AF37] mx-auto my-4"></div>
            <p class="text-xs sm:text-sm text-[#0E261C]/70">
                Each piece is certified, hallmarked, and available for secure armoured delivery across India.
            </p>
        </div>

        {{-- Filter & Catalog Grid Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mt-10">
            {{-- Left Sidebar: Filters --}}
            <aside class="lg:col-span-1" x-data="{ mobileFiltersOpen: false }">
                {{-- Mobile Filter Trigger --}}
                <div class="lg:hidden mb-4">
                    <button @click="mobileFiltersOpen = !mobileFiltersOpen" 
                            class="w-full py-3 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-widest font-semibold flex items-center justify-center gap-2">
                        <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>Filter &amp; Refine Vault</span>
                    </button>
                </div>

                <div :class="mobileFiltersOpen ? 'block' : 'hidden lg:block'" 
                     class="bg-white border border-[#D4AF37]/20 p-6 shadow-sm sticky top-28">
                    <form action="{{ route('products.index') }}" method="GET" class="space-y-6">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif

                        {{-- Search Filter --}}
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-2">Search Vault</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Title, SKU, or gem..." 
                                   class="w-full text-xs px-3 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                        </div>

                        {{-- Category Filter --}}
                        <div class="border-t border-gray-100 pt-5">
                            <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-3">Category</label>
                            @php
                                $activeCat = request('category', $currentCategory ?? '');
                                $primaryCats = [
                                    'jewelry' => 'Jewelry',
                                    'clothing' => 'Clothing',
                                    'watches' => 'Watches',
                                    'footwear' => 'Footwear',
                                ];
                                $legacyJewelrySubs = [
                                    'rings' => 'Rings',
                                    'necklaces' => 'Necklaces',
                                    'bracelets' => 'Bracelets',
                                    'earrings' => 'Earrings',
                                    'solitaires' => 'Solitaires',
                                ];
                            @endphp
                            <div class="space-y-2">
                                <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#D4AF37]">
                                    <input type="radio" name="category" value="" 
                                           {{ empty($activeCat) ? 'checked' : '' }}
                                           class="text-[#0E261C] focus:ring-[#D4AF37] mr-2">
                                    All Categories
                                </label>
                                @foreach($primaryCats as $slug => $label)
                                    <label class="flex items-center text-xs font-semibold text-[#0E261C] cursor-pointer hover:text-[#D4AF37]">
                                        <input type="radio" name="category" value="{{ $slug }}" 
                                               {{ $activeCat === $slug ? 'checked' : '' }}
                                               class="text-[#0E261C] focus:ring-[#D4AF37] mr-2">
                                        {{ $label }}
                                    </label>
                                @endforeach
                                <div class="pl-4 pt-1 space-y-1.5 border-l-2 border-[#D4AF37]/30 ml-2">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 block font-bold">Jewelry Styles</span>
                                    @foreach($legacyJewelrySubs as $slug => $label)
                                        <label class="flex items-center text-xs text-[#0E261C]/70 cursor-pointer hover:text-[#D4AF37]">
                                            <input type="radio" name="category" value="{{ $slug }}" 
                                                   {{ $activeCat === $slug ? 'checked' : '' }}
                                                   class="text-[#0E261C] focus:ring-[#D4AF37] mr-2">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Price Range Filter --}}
                        <div class="border-t border-gray-100 pt-5">
                            <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-3">Price Range (₹)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" 
                                       class="text-xs px-2.5 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" 
                                       class="text-xs px-2.5 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-4 space-y-2">
                            <button type="submit" 
                                    class="w-full py-3 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-widest font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                                Apply Refinements
                            </button>
                            <a href="{{ route('products.index') }}" 
                               class="block text-center text-[10px] uppercase tracking-wider text-gray-500 hover:text-[#9B1B30] pt-1">
                                Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            {{-- Right Main Area: Products Grid --}}
            <main class="lg:col-span-3">
                {{-- Toolbar --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white border border-[#D4AF37]/20 px-6 py-4 mb-6 shadow-sm gap-4">
                    <div class="text-xs text-gray-600">
                        Presenting <span class="font-bold text-[#0E261C]">{{ $products->total() }}</span> commissioned pieces
                    </div>

                    {{-- Sort Dropdown --}}
                    <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2">
                        @foreach(request()->except('sort', 'page') as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <label for="sortSelect" class="text-xs uppercase tracking-wider text-gray-500 whitespace-nowrap">Sort By:</label>
                        <select id="sortSelect" name="sort" onchange="this.form.submit()" 
                                class="text-xs py-1.5 px-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none bg-white">
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest Curations</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>Title: A to Z</option>
                        </select>
                    </form>
                </div>

                {{-- Products Grid --}}
                @if($products->isEmpty())
                    <div class="bg-white border border-[#D4AF37]/20 p-16 text-center shadow-sm">
                        <div class="w-12 h-12 mx-auto text-[#D4AF37] mb-4">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h3 class="font-display text-xl font-bold text-[#0E261C] mb-2">No Curations Matched Your Criteria</h3>
                        <p class="text-xs text-gray-500 max-w-md mx-auto mb-6">
                            We invite you to broaden your filter parameters or connect with our private concierge for bespoke commissions.
                        </p>
                        <a href="{{ route('products.index') }}" 
                           class="inline-block px-8 py-3 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-widest font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                            View All Pieces
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>
@endsection

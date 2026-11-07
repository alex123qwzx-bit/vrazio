@extends('layouts.app')

@section('title', 'Haute Couture & Royal Apparel | Imperial Zardozi & Handloom Silks')
@section('meta_description', 'Discover VRAZIO bespoke Haute Couture: handcrafted Bridal Lehengas with 24KT gold zari, Banarasi Katan silk sarees, velvet royal sherwanis, and heirloom pashmina capes.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen">

    {{-- ═══════════════════════════════════════════════════════════════════
         HAUTE COUTURE EDITORIAL HERO BANNER
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="relative bg-[#0A1C15] text-[#FAF8F5] py-24 md:py-32 overflow-hidden border-b border-[#D4AF37]/20">
        {{-- Background Image with cinematic overlay --}}
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=2000&q=85" 
                 alt="VRAZIO Haute Couture Atelier" 
                 class="w-full h-full object-cover object-center filter brightness-40 contrast-110 scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-[#0A1C15] via-[#0E261C]/80 to-[#0A1C15]/90"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-transparent via-[#0A1C15]/50 to-[#0A1C15]"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-6 text-center">
            <div class="inline-flex items-center gap-3 border-y border-[#D4AF37]/40 py-1.5 px-6 mb-6">
                <span class="w-1.5 h-1.5 rotate-45 bg-[#D4AF37]"></span>
                <span class="text-xs uppercase tracking-[0.35em] text-[#D4AF37] font-semibold">
                    Atelier Sartorial • Haute Couture
                </span>
                <span class="w-1.5 h-1.5 rotate-45 bg-[#D4AF37]"></span>
            </div>

            <h1 class="font-display text-4xl sm:text-6xl md:text-7xl font-bold tracking-tight text-[#FAF8F5] leading-tight md:leading-[1.15]">
                Imperial Heirlooms &amp; Royal Apparel
            </h1>

            <p class="font-body text-base sm:text-lg md:text-xl text-[#FAF8F5]/85 font-light mt-6 max-w-3xl mx-auto leading-relaxed">
                Handcrafted bridal lehengas, pure Banarasi Katan silks, royal micro-velvet sherwanis, and ethereal Chikankari gowns woven with genuine 24-karat gold zari thread.
            </p>

            {{-- Trust Badges Strip --}}
            <div class="mt-10 pt-6 border-t border-[#D4AF37]/20 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-[11px] uppercase tracking-[0.2em] text-[#FAF8F5]/70">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Silk Mark India Certified
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Authentic 24KT Gold Zari
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Bespoke Made-to-Measure Fitting
                </span>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         TAG NAVIGATION BAR (CATEGORY & STYLE PILLS)
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="sticky top-20 z-30 bg-white/95 backdrop-blur-md border-b border-[#D4AF37]/20 shadow-sm py-4">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between gap-4 overflow-x-auto no-scrollbar py-1">
                <div class="flex items-center gap-2 text-xs flex-nowrap whitespace-nowrap">
                    <span class="text-[11px] uppercase tracking-widest text-gray-400 font-bold mr-2 hidden sm:inline">Couture Tags:</span>
                    @foreach($tags as $t)
                        @php
                            $isSelected = ($currentTag === $t) || (empty($currentTag) && $t === 'All Couture');
                            $url = $t === 'All Couture' ? route('products.couture') : route('products.couture', ['tag' => $t]);
                        @endphp
                        <a href="{{ $url }}" 
                           class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-full transition-all duration-300 {{ $isSelected ? 'bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37]' : 'bg-gray-100/80 text-[#0E261C]/80 hover:bg-[#D4AF37]/10 hover:text-[#0E261C] border border-transparent hover:border-[#D4AF37]/30' }}">
                            {{ $t === 'All Couture' ? '✨ All Couture' : '#' . $t }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         MAIN EXHIBITION CATALOG
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-8 border-b border-gray-200">
            <div>
                <p class="text-xs uppercase tracking-widest text-[#D4AF37] font-semibold">Atelier Collection</p>
                <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#0E261C]">
                    {{ $currentTag && $currentTag !== 'All Couture' ? $currentTag : 'All Haute Couture Pieces' }}
                </h2>
            </div>
            <p class="text-xs text-gray-500 mt-2 sm:mt-0 font-medium">
                Displaying {{ $products->total() }} Bespoke Creation{{ $products->total() === 1 ? '' : 's' }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Left Sidebar: Refinements --}}
            <aside class="lg:col-span-1">
                <div class="bg-white border border-[#D4AF37]/20 p-6 shadow-sm sticky top-40 space-y-6">
                    <h3 class="font-display text-lg font-bold text-[#0E261C] border-b border-gray-100 pb-3 flex items-center justify-between">
                        <span>Refine Garments</span>
                        <a href="{{ route('products.couture') }}" class="text-[10px] text-[#9B1B30] uppercase tracking-wider hover:underline">Reset</a>
                    </h3>

                    <form action="{{ route('products.couture') }}" method="GET" class="space-y-6">
                        @if($currentTag)
                            <input type="hidden" name="tag" value="{{ $currentTag }}">
                        @endif

                        {{-- Search Filter --}}
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-2">Search Couture</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Lehenga, Zari, Silk..." 
                                   class="w-full text-xs px-3 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                        </div>

                        {{-- Fabric / Weave Filter --}}
                        @if($fabrics->count() > 0)
                        <div class="border-t border-gray-100 pt-4">
                            <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-3">Fabric &amp; Weave</label>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-2">
                                @foreach($fabrics as $fabric)
                                    <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#D4AF37]">
                                        <input type="radio" name="metal_type" value="{{ $fabric }}" 
                                               {{ request('metal_type') === $fabric ? 'checked' : '' }}
                                               class="text-[#0E261C] focus:ring-[#D4AF37] mr-2">
                                        <span class="truncate">{{ $fabric }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Price Range Filter --}}
                        <div class="border-t border-gray-100 pt-4">
                            <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-3">Investment (₹)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" 
                                       class="text-xs px-3 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none w-full">
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" 
                                       class="text-xs px-3 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none w-full">
                            </div>
                        </div>

                        {{-- Sort --}}
                        <div class="border-t border-gray-100 pt-4">
                            <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-2">Sorting</label>
                            <select name="sort" class="w-full text-xs px-3 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none bg-white">
                                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest Commissions</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-3 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-widest font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Right: Product Grid --}}
            <main class="lg:col-span-3">
                @if($products->isEmpty())
                    <div class="text-center py-20 bg-white border border-dashed border-[#D4AF37]/30 p-12">
                        <svg class="w-12 h-12 text-[#D4AF37] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <h3 class="font-display text-xl font-bold text-[#0E261C] mb-2">No Couture Commissions Match This Filter</h3>
                        <p class="text-xs text-gray-500 mb-6">Our atelier creates bespoke pieces on order. Reset filters or consult our head master tailor.</p>
                        <a href="{{ route('products.couture') }}" class="inline-block px-6 py-2.5 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-widest font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors">
                            View All Couture
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

    {{-- ═══════════════════════════════════════════════════════════════════
         CRAFTSMANSHIP & ATELIER HERITAGE SECTION
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-white border-t border-[#D4AF37]/20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                    Master Karigari
                </span>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-2">
                    The Art of Imperial Needlework
                </h2>
                <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
                <p class="text-sm text-[#0E261C]/70">
                    Each garment is handwoven and hand-embroidered by multi-generational master artisans across India.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 bg-[#FAF8F5] border border-[#D4AF37]/20 text-center space-y-4">
                    <div class="w-14 h-14 bg-[#0E261C] text-[#D4AF37] mx-auto flex items-center justify-center font-display text-xl font-bold">
                        24K
                    </div>
                    <h3 class="font-display text-lg font-bold text-[#0E261C]">Authentic Real Gold Zari</h3>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Pure silver wire electroplated with 24KT gold, handwoven into heirloom Banarasi silks and velvet borders that retain their radiant luster across generations.
                    </p>
                </div>

                <div class="p-8 bg-[#FAF8F5] border border-[#D4AF37]/20 text-center space-y-4">
                    <div class="w-14 h-14 bg-[#0E261C] text-[#D4AF37] mx-auto flex items-center justify-center font-display text-xl font-bold">
                        1200h
                    </div>
                    <h3 class="font-display text-lg font-bold text-[#0E261C]">Zardozi &amp; Dabka Needlework</h3>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Over one thousand hours of meticulous metallic embroidery, incorporating seed pearls, cut-dana, and sequins on wooden adda frames in Lucknow and Awadh.
                    </p>
                </div>

                <div class="p-8 bg-[#FAF8F5] border border-[#D4AF37]/20 text-center space-y-4">
                    <div class="w-14 h-14 bg-[#0E261C] text-[#D4AF37] mx-auto flex items-center justify-center font-display text-xl font-bold">
                        1:1
                    </div>
                    <h3 class="font-display text-lg font-bold text-[#0E261C]">Bespoke Made-to-Measure</h3>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Every bridal lehenga and royal sherwani is custom tailored to your exact measurements, with virtual atelier fittings and armoured doorstep delivery.
                    </p>
                </div>
            </div>

            {{-- Bespoke Consultation Call to Action --}}
            <div class="mt-16 p-10 bg-[#0E261C] text-[#FAF8F5] border border-[#D4AF37]/40 text-center max-w-4xl mx-auto space-y-6">
                <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                    Private Bridal Appointments
                </span>
                <h3 class="font-display text-2xl sm:text-4xl font-bold">
                    Commission Your Bespoke Bridal Ensemble
                </h3>
                <p class="text-sm text-[#FAF8F5]/80 max-w-2xl mx-auto leading-relaxed">
                    Connect directly with our master couturiers and jewellery curators to coordinate matching bridal parure jewels and imperial wedding attire.
                </p>
                <div class="pt-2">
                    <a href="{{ route('pages.contact') }}" class="inline-block px-10 py-4 bg-[#9B1B30] text-white text-xs uppercase tracking-[0.25em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all duration-300">
                        Book Concierge Consultation
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

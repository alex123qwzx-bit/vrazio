@extends('layouts.app')

@php
    $images = $product->image_urls;
    $specs = is_array($product->specs) ? $product->specs : json_decode($product->specs ?? '[]', true);
    $discountPercent = (float) ($product->discount_percent ?? 0);
    $hasDiscount = $discountPercent > 0;
    $finalPrice = (float) ($product->final_price ?? $product->sale_price ?? $product->price);
@endphp

@section('title', $product->title)
@section('meta_description', Str::limit(strip_tags($product->description), 150))
@section('og_image', $images[0] ?? null)

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-8" 
     x-data="{
         activeImage: '{{ $images[0] }}',
         qty: 1,
         zoom: false,
         activeTab: 'specs'
     }">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'Collections' => route('products.index'),
            ucfirst($product->category) => route('products.category', $product->category),
            $product->title => ''
        ]" />

        {{-- Product Master Showcase Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 mt-6">
            {{-- Left Column: 4K Zoom Gallery --}}
            <div class="lg:col-span-7 space-y-4">
                {{-- Main Master Image Container with Zoom --}}
                <div class="relative bg-white border border-[#D4AF37]/30 aspect-square overflow-hidden shadow-sm group">
                    <img :src="activeImage" 
                         alt="{{ $product->title }}" 
                         class="w-full h-full object-cover object-center transition-transform duration-500 ease-out cursor-crosshair group-hover:scale-150 origin-center"
                         id="mainProductImage"
                         onerror="this.onerror=null; this.src='{{ \App\Models\Product::getCategoryFallbackImage($product->category) }}';">

                    {{-- Badges --}}
                    <div class="absolute top-4 left-4 flex flex-col gap-2 z-10 pointer-events-none">
                        @if($product->is_featured)
                            <span class="bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37]/40 text-[10px] uppercase font-bold tracking-widest px-3 py-1">
                                Master Creation
                            </span>
                        @endif
                        @if($product->bis_hallmarked)
                            <span class="bg-[#FAF8F5]/90 backdrop-blur text-[#0E261C] border border-[#D4AF37]/30 text-[9px] uppercase font-bold tracking-wider px-2.5 py-1 flex items-center gap-1.5">
                                <svg width="20" height="20" class="w-3 h-3 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                BIS Hallmarked Gold
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Thumbnail Strip --}}
                <div class="flex items-center gap-3 overflow-x-auto pb-2">
                    @foreach($images as $img)
                        <button type="button" 
                                @click="activeImage = '{{ $img }}'" 
                                :class="activeImage === '{{ $img }}' ? 'border-[#D4AF37] ring-1 ring-[#D4AF37]' : 'border-gray-200 opacity-60 hover:opacity-100'"
                                class="w-20 h-20 bg-white border shrink-0 overflow-hidden transition-all duration-200">
                            <img src="{{ $img }}" alt="{{ $product->title }} angle" onerror="this.onerror=null; this.src='{{ \App\Models\Product::getCategoryFallbackImage($product->category) }}';" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Right Column: Information Dossier & Acquisition --}}
            <div class="lg:col-span-5 flex flex-col justify-between">
                <div>
                    {{-- Category & SKU --}}
                    <div class="flex items-center justify-between text-xs uppercase tracking-widest text-[#D4AF37] font-semibold">
                        <span>{{ ucfirst($product->category) }} &bull; {{ $product->metal_type }}</span>
                        <span class="text-gray-400 font-mono text-[10px]">SKU: {{ $product->sku }}</span>
                    </div>

                    {{-- Title --}}
                    <h1 class="font-display text-2xl sm:text-4xl font-bold text-[#0E261C] mt-2 mb-2 leading-tight">
                        {{ $product->title }}
                    </h1>

                    {{-- Star Rating Summary Badge --}}
                    @php
                        $avgRating = $product->average_rating;
                        $reviewsCount = $product->reviews_count;
                    @endphp
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="flex text-[#D4AF37] gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <svg width="14" height="14" class="w-3.5 h-3.5 {{ $i <= round($avgRating) ? 'fill-current text-[#D4AF37]' : 'text-gray-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <a href="#reviews-section" class="text-xs text-[#0E261C]/80 hover:text-[#D4AF37] font-medium transition-colors">
                            {{ number_format($avgRating, 1) }} &bull; <span class="underline">{{ $reviewsCount }} {{ Str::plural('Review', $reviewsCount) }}</span>
                        </a>
                    </div>

                    {{-- Valuation & Pricing --}}
                    <div class="p-4 bg-white border border-[#D4AF37]/25 mb-6 flex items-baseline justify-between">
                        <div>
                            <span class="text-[10px] uppercase tracking-widest text-gray-400 block mb-1">Price:</span>
                            <div class="flex items-center gap-3">
                                @if($hasDiscount)
                                    <span class="px-2.5 py-1 rounded bg-[#9B1B30] text-white text-xs font-bold uppercase tracking-wider">
                                        {{ ((float) $discountPercent + 0) }}% OFF
                                    </span>
                                @endif
                                <span class="font-display text-3xl font-bold text-[#0E261C]">
                                    ₹{{ number_format($finalPrice, 0) }}
                                </span>
                            </div>

                        </div>

                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 bg-green-50 text-green-800 text-[10px] font-bold uppercase tracking-wider border border-green-200">
                                In Stock ({{ $product->stock_quantity }} Available)
                            </span>
                        </div>
                    </div>

                    {{-- Applicable Vouchers & Privileges --}}
                    @php
                        $applicableVouchers = $product->getApplicableVouchers();
                    @endphp
                    @if($applicableVouchers->isNotEmpty())
                        <div class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-orange-50/40 border border-[#D4AF37]/50 rounded-xs">
                            <div class="flex items-center gap-2 mb-2.5">
                                <svg class="w-4 h-4 text-[#D4AF37] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs uppercase tracking-widest font-bold text-[#0E261C]">Atelier Exclusive Privileges</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($applicableVouchers as $v)
                                    <div class="flex items-center justify-between bg-white border border-[#D4AF37]/30 p-2.5 shadow-2xs">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold text-xs text-[#0E261C] bg-[#FAF8F5] px-2 py-0.5 border border-dashed border-[#D4AF37] tracking-wider">{{ $v->code }}</span>
                                                <span class="text-xs font-semibold text-[#9B1B30]">{{ $v->discount_label }}</span>
                                            </div>
                                            @if($v->description)
                                                <p class="text-[11px] text-gray-500 mt-1">{{ $v->description }}</p>
                                            @elseif($v->min_spend > 0)
                                                <p class="text-[10px] text-gray-500 mt-0.5">Min. order ₹{{ number_format($v->min_spend) }}</p>
                                            @endif
                                        </div>
                                        <button type="button" 
                                                onclick="navigator.clipboard.writeText('{{ $v->code }}'); this.innerText = 'Copied!'; setTimeout(() => this.innerText = 'Copy Code', 2000);"
                                                class="px-3 py-1 bg-[#0E261C] hover:bg-[#D4AF37] text-[#D4AF37] hover:text-[#0E261C] text-[10px] uppercase font-bold tracking-wider transition-colors shrink-0 cursor-pointer">
                                            Copy Code
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- BIS & Certification Dossier Badge / Couture Badge --}}
                    @if($product->category === 'couture')
                        <div class="bg-[#0E261C] text-[#FAF8F5] p-5 border border-[#D4AF37]/40 mb-6 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg width="20" height="20" class="w-5 h-5 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span class="font-display text-sm font-semibold tracking-wider text-[#D4AF37]">
                                        Haute Couture &bull; Premium Quality
                                    </span>
                                </div>
                                <span class="text-[10px] uppercase font-mono tracking-widest text-[#FAF8F5]/60">Perfect Fit</span>
                            </div>
                            <p class="text-xs text-[#FAF8F5]/80 font-light leading-relaxed">
                                {{ $product->certification ?? 'Silk Mark Certified & Master Embroidery workmanship included.' }}
                            </p>
                        </div>
                    @else
                        <div class="bg-[#0E261C] text-[#FAF8F5] p-5 border border-[#D4AF37]/40 mb-6 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg width="20" height="20" class="w-5 h-5 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span class="font-display text-sm font-semibold tracking-wider text-[#D4AF37]">
                                        Government BIS Hallmarked
                                    </span>
                                </div>
                                <span class="text-[10px] uppercase font-mono tracking-widest text-[#FAF8F5]/60">HUID Registered</span>
                            </div>
                            <p class="text-xs text-[#FAF8F5]/80 font-light leading-relaxed">
                                Certified 100% genuine gold stamped with hallmark. {{ $product->certification ?? 'GIA / IGI / SGL Certificate included.' }}
                            </p>
                        </div>
                    @endif

                    {{-- Tags Matrix --}}
                    @php
                        $tags = is_array($product->tags) ? $product->tags : json_decode($product->tags ?? '[]', true);
                    @endphp
                    @if(!empty($tags) && count($tags) > 0)
                        <div class="mb-6 flex flex-wrap gap-2 items-center">
                            <span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Tags:</span>
                            @foreach($tags as $t)
                                <a href="{{ $product->category === 'couture' ? route('products.couture', ['tag' => $t]) : route('products.index', ['tag' => $t]) }}" 
                                   class="px-2.5 py-1 text-[11px] font-semibold tracking-wider bg-white text-[#0E261C] border border-[#D4AF37]/30 hover:border-[#D4AF37] hover:bg-[#D4AF37]/10 transition-colors">
                                    #{{ $t }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Weight Matrix (Jewellery Only) --}}
                    @if($product->category !== 'couture' && ($product->gross_weight_grams || $product->net_weight_grams))
                    <div class="grid grid-cols-2 gap-3 mb-6 text-xs">
                        <div class="bg-white border border-gray-200 p-3 text-center">
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Gross Weight</span>
                            <span class="font-bold text-[#0E261C] font-mono text-sm">{{ $product->gross_weight_grams ?? '—' }} g</span>
                        </div>
                        <div class="bg-white border border-gray-200 p-3 text-center">
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Net Gold Weight</span>
                            <span class="font-bold text-[#0E261C] font-mono text-sm">{{ $product->net_weight_grams ?? '—' }} g</span>
                        </div>
                    </div>
                    @endif

                    {{-- Add to Cart Form --}}
                    <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        {{-- Size Selector (BUS-04: Rings, Bangles, Haute Couture) --}}
                        @if($product->category === 'rings' || str_contains(strtolower($product->title), 'ring') || str_contains(strtolower($product->title), 'solitaire'))
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs uppercase tracking-wider font-semibold text-[#0E261C]">
                                        Select Ring Size (Indian / US)
                                    </label>
                                    <span class="text-[10px] text-[#D4AF37] font-medium">Complimentary resizing</span>
                                </div>
                                <select name="size" class="w-full text-xs px-3 py-2.5 bg-white border border-gray-300 focus:border-[#D4AF37] focus:ring-0">
                                    <option value="Size 10 (US 5.25)">Size 10 (US 5.25 &bull; 15.9mm)</option>
                                    <option value="Size 12 (US 6)" selected>Size 12 (US 6 &bull; 16.5mm) &mdash; Standard</option>
                                    <option value="Size 14 (US 7)">Size 14 (US 7 &bull; 17.3mm)</option>
                                    <option value="Size 16 (US 7.75)">Size 16 (US 7.75 &bull; 17.9mm)</option>
                                    <option value="Size 18 (US 8.5)">Size 18 (US 8.5 &bull; 18.5mm)</option>
                                    <option value="Size 20 (US 9.25)">Size 20 (US 9.25 &bull; 19.2mm)</option>
                                    <option value="Size 22 (US 10)">Size 22 (US 10 &bull; 19.8mm)</option>
                                </select>
                            </div>
                        @elseif($product->category === 'bangles' || str_contains(strtolower($product->title), 'bangle') || str_contains(strtolower($product->title), 'bracelet'))
                            <div class="mb-4">
                                <label class="block text-xs uppercase tracking-wider font-semibold text-[#0E261C] mb-1.5">
                                    Select Wrist / Bangle Diameter
                                </label>
                                <select name="size" class="w-full text-xs px-3 py-2.5 bg-white border border-gray-300 focus:border-[#D4AF37] focus:ring-0">
                                    <option value="Size 2.4 (2.25 inch / 57mm)">Size 2.4 (2.25" / 57mm &mdash; Petite)</option>
                                    <option value="Size 2.6 (2.375 inch / 60mm)" selected>Size 2.6 (2.375" / 60mm &mdash; Standard)</option>
                                    <option value="Size 2.8 (2.5 inch / 63.5mm)">Size 2.8 (2.5" / 63.5mm &mdash; Relaxed)</option>
                                </select>
                            </div>
                        @elseif($product->category === 'couture' || $product->category === 'clothes')
                            <div class="mb-4">
                                <label class="block text-xs uppercase tracking-wider font-semibold text-[#0E261C] mb-1.5">
                                    Select Atelier Fit / Size
                                </label>
                                <select name="size" class="w-full text-xs px-3 py-2.5 bg-white border border-gray-300 focus:border-[#D4AF37] focus:ring-0">
                                    <option value="XS (Bespoke Petite)">XS (Bespoke Petite)</option>
                                    <option value="S (Small)">S (Small)</option>
                                    <option value="M (Medium)" selected>M (Medium &mdash; Standard)</option>
                                    <option value="L (Large)">L (Large)</option>
                                    <option value="XL (Extra Large)">XL (Extra Large)</option>
                                    <option value="Custom Bespoke Fitting">Custom Bespoke Fitting (Concierge will call)</option>
                                </select>
                            </div>
                        @endif

                        <div class="flex items-center gap-4">
                            <div class="flex items-center border border-gray-300 bg-white">
                                <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-3.5 py-3 text-sm hover:bg-gray-100 transition-colors">−</button>
                                <input type="number" name="quantity" x-model="qty" min="1" max="{{ $product->stock_quantity }}" class="w-12 text-center text-sm font-bold border-0 focus:ring-0">
                                <button type="button" @click="qty = Math.min({{ $product->stock_quantity }}, qty + 1)" class="px-3.5 py-3 text-sm hover:bg-gray-100 transition-colors">+</button>
                            </div>

                            <button type="submit" 
                                    class="flex-grow py-4 px-8 bg-[#9B1B30] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] border border-[#9B1B30] hover:border-[#D4AF37] transition-all duration-300 shadow-xl flex items-center justify-center gap-2">
                                <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <span>Add to Cart</span>
                            </button>
                        </div>
                    </form>

                    {{-- Delivery & Returns Guarantees --}}
                    <div class="mt-8 pt-6 border-t border-gray-200 grid grid-cols-2 gap-4 text-[11px] text-gray-600">
                        <div class="flex items-center gap-2">
                            <svg width="16" height="16" class="w-4 h-4 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Free Insured Delivery</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="16" height="16" class="w-4 h-4 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>15-Day Easy Returns</span>
                        </div>
                        @if($product->is_warranty_eligible)
                        <div class="col-span-2 mt-2 p-2.5 bg-white border border-[#D4AF37]/30 rounded-xs flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D4AF37] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span class="text-xs text-[#0E261C]"><strong>{{ $product->warranty_period_months ?? 12 }}-Month Warranty Protection</strong></span>
                            </div>
                            <a href="{{ url('/prive-club') }}" class="text-[10px] font-bold uppercase tracking-wider text-[#D4AF37] hover:underline bg-[#0E261C] px-2 py-1 rounded-xs">PRIVÉ Privilege &rarr;</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs: Details & Specifications --}}
        <div class="mt-20 border-t border-[#D4AF37]/20 pt-10">
            <div class="flex items-center justify-center gap-8 border-b border-gray-200">
                <button type="button" 
                        @click="activeTab = 'specs'" 
                        :class="activeTab === 'specs' ? 'border-[#D4AF37] text-[#0E261C] font-bold' : 'border-transparent text-gray-500 hover:text-[#0E261C]'"
                        class="pb-4 text-xs uppercase tracking-[0.2em] border-b-2 transition-all">
                    Product Details &amp; Specs
                </button>
                <button type="button" 
                        @click="activeTab = 'description'" 
                        :class="activeTab === 'description' ? 'border-[#D4AF37] text-[#0E261C] font-bold' : 'border-transparent text-gray-500 hover:text-[#0E261C]'"
                        class="pb-4 text-xs uppercase tracking-[0.2em] border-b-2 transition-all">
                    Atelier Narrative
                </button>
                <button type="button" 
                        @click="activeTab = 'certification'" 
                        :class="activeTab === 'certification' ? 'border-[#D4AF37] text-[#0E261C] font-bold' : 'border-transparent text-gray-500 hover:text-[#0E261C]'"
                        class="pb-4 text-xs uppercase tracking-[0.2em] border-b-2 transition-all">
                    Hallmark &amp; Certification
                </button>
            </div>

            <div class="py-8 max-w-4xl mx-auto">
                {{-- Specs Tab --}}
                <div x-show="activeTab === 'specs'" class="bg-white border border-[#D4AF37]/20 p-8 shadow-sm">
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-3 font-semibold text-gray-400 uppercase tracking-wider w-1/3">Stock Reference</td>
                                <td class="py-3 font-mono font-bold text-[#0E261C]">{{ $product->sku }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 font-semibold text-gray-400 uppercase tracking-wider">Precious Metal Alloy</td>
                                <td class="py-3 text-[#0E261C] font-semibold">{{ $product->metal_type }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 font-semibold text-gray-400 uppercase tracking-wider">Gross Weight</td>
                                <td class="py-3 text-[#0E261C] font-mono">{{ $product->gross_weight_grams ?? 'N/A' }} grams</td>
                            </tr>
                            <tr>
                                <td class="py-3 font-semibold text-gray-400 uppercase tracking-wider">Net Weight</td>
                                <td class="py-3 text-[#0E261C] font-mono">{{ $product->net_weight_grams ?? 'N/A' }} grams</td>
                            </tr>
                            @if(!empty($specs))
                                @foreach($specs as $key => $val)
                                    <tr>
                                        <td class="py-3 font-semibold text-gray-400 uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                        <td class="py-3 text-[#0E261C]">{{ is_array($val) ? json_encode($val) : $val }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Description Tab --}}
                <div x-show="activeTab === 'description'" class="bg-white border border-[#D4AF37]/20 p-8 shadow-sm prose max-w-none text-xs sm:text-sm text-[#0E261C]/80 leading-relaxed font-light">
                    {!! nl2br(e($product->description)) !!}
                </div>

                {{-- Certification Tab --}}
                <div x-show="activeTab === 'certification'" class="bg-white border border-[#D4AF37]/20 p-8 shadow-sm space-y-4 text-xs text-[#0E261C]/80 leading-relaxed">
                    <h3 class="font-display text-base font-bold text-[#0E261C]">Authenticity Guarantee</h3>
                    <p>
                        This creation is guaranteed authentic, crafted in our state-certified workshop and evaluated by certified gemologists. Gold purity is strictly assayed in conformity with the Bureau of Indian Standards (BIS) Hallmarking scheme under the Hallmarking Act.
                    </p>
                    <p>
                        Diamonds and precious gemstones are verified natural, unenhanced (unless explicitly stated), and ethically sourced following the Kimberley Process Certification Scheme.
                    </p>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             CUSTOMER REVIEWS & CRAFTSMANSHIP RATINGS
        ═══════════════════════════════════════════════════════════════════ --}}
        <section id="reviews-section" 
                 x-data="{
                     showModal: false,
                     selectedRating: 5,
                     hoverRating: 0,
                     activePhotoModal: null,
                     submitting: false,
                     files: []
                 }" 
                 class="mt-20 border-t border-[#D4AF37]/20 pt-16">

            <div class="text-center max-w-xl mx-auto mb-12">
                <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">Patron Testimonials</span>
                <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#0E261C] mt-2">Craftsmanship &amp; Patron Reviews</h2>
                <div class="w-12 h-px bg-[#D4AF37] mx-auto my-3"></div>
                <p class="text-xs sm:text-sm text-[#0E261C]/70 font-light">
                    Authentic impressions and certified experiences from patrons who commissioned this piece.
                </p>
            </div>

            @if(session('review_success'))
                <div class="mb-8 p-4 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-900 text-xs sm:text-sm flex items-center justify-between">
                    <span>{{ session('review_success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                {{-- Left Summary Column (span 4) --}}
                <div class="lg:col-span-4 bg-white border border-[#D4AF37]/25 p-6 sm:p-8 shadow-xs sticky top-24">
                    <div class="text-center pb-6 border-b border-gray-100">
                        <div class="font-display text-5xl font-bold text-[#0E261C]">
                            {{ number_format($avgRating, 1) }}
                        </div>
                        <div class="flex items-center justify-center text-[#D4AF37] gap-1 my-2">
                            @for($i = 1; $i <= 5; $i++)
                                <svg width="18" height="18" class="w-4.5 h-4.5 {{ $i <= round($avgRating) ? 'fill-current text-[#D4AF37]' : 'text-gray-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-xs text-gray-500 font-light">
                            Based on {{ $reviewsCount }} {{ Str::plural('patron evaluation', $reviewsCount) }}
                        </p>
                    </div>

                    {{-- Rating Breakdown Bars --}}
                    @php
                        $breakdown = $ratingBreakdown ?? [
                            'percentages' => [5 => 100, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
                            'counts' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
                        ];
                    @endphp
                    <div class="space-y-2.5 my-6">
                        @foreach([5, 4, 3, 2, 1] as $star)
                            @php
                                $pct = $breakdown['percentages'][$star] ?? 0;
                                $cnt = $breakdown['counts'][$star] ?? 0;
                            @endphp
                            <div class="flex items-center text-xs text-gray-600 gap-2">
                                <span class="w-7 text-right font-mono">{{ $star }} ★</span>
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#D4AF37] transition-all duration-500" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="w-8 text-right font-mono text-[11px] text-gray-400">({{ $cnt }})</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Write Review CTA --}}
                    <div class="pt-4 border-t border-gray-100 text-center">
                        <button type="button" 
                                @click="showModal = true"
                                class="w-full py-3.5 px-6 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-[0.2em] transition-all cursor-pointer">
                            Write a Patron Review
                        </button>
                        <p class="text-[10px] text-gray-400 mt-2.5 flex items-center justify-center gap-1">
                            <svg width="12" height="12" class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Verified against delivered order records
                        </p>
                    </div>
                </div>

                {{-- Right Column: Reviews Stream (span 8) --}}
                <div class="lg:col-span-8">
                    @if($approvedReviews->isEmpty())
                        <div class="bg-white border border-[#D4AF37]/20 p-12 text-center shadow-2xs">
                            <div class="w-14 h-14 rounded-full bg-[#FAF8F5] border border-[#D4AF37]/30 flex items-center justify-center mx-auto mb-4 text-[#D4AF37]">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </div>
                            <h3 class="font-display text-lg font-bold text-[#0E261C]">No Reviews Published Yet</h3>
                            <p class="text-xs sm:text-sm text-[#0E261C]/70 font-light mt-1.5 max-w-md mx-auto">
                                Be the first patron to share your craftsmanship appraisal for this haute creation.
                            </p>
                            <button type="button" 
                                    @click="showModal = true"
                                    class="mt-6 inline-flex items-center px-8 py-3 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-widest transition-all cursor-pointer">
                                Submit First Review
                            </button>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($approvedReviews as $rev)
                                <div class="bg-white border border-[#D4AF37]/20 p-6 sm:p-7 shadow-2xs">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-4 border-b border-gray-100">
                                        <div>
                                            {{-- Star rating --}}
                                            <div class="flex text-[#D4AF37] gap-0.5 mb-1.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg width="14" height="14" class="w-3.5 h-3.5 {{ $i <= $rev->rating ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                @endfor
                                            </div>
                                            <h4 class="font-display font-bold text-base text-[#0E261C]">{{ $rev->title }}</h4>
                                        </div>

                                        <div class="flex items-center gap-2 self-start">
                                            @if($rev->is_verified_purchase)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] font-semibold uppercase tracking-wider rounded-xs">
                                                    <svg width="11" height="11" class="w-2.5 h-2.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    Verified Purchase
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-gray-400 font-mono">{{ $rev->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>

                                    <p class="text-xs sm:text-sm text-[#0E261C]/80 font-light mt-3 leading-relaxed">
                                        {{ $rev->content }}
                                    </p>

                                    {{-- Customer Photos --}}
                                    @if(!empty($rev->photos))
                                        <div class="flex flex-wrap gap-2.5 mt-4 pt-3 border-t border-gray-50">
                                            @foreach($rev->photos as $photo)
                                                @php
                                                    $photoUrl = asset('storage/' . $photo);
                                                @endphp
                                                <button type="button" 
                                                        @click="activePhotoModal = '{{ $photoUrl }}'" 
                                                        class="w-16 h-16 sm:w-20 sm:h-20 object-cover border border-[#D4AF37]/30 overflow-hidden hover:opacity-90 transition-opacity cursor-pointer">
                                                    <img src="{{ $photoUrl }}" alt="Patron review photo" class="w-full h-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Patron Author Footer --}}
                                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                        <span class="font-display font-semibold text-[#0E261C]">{{ $rev->author_name }}</span>
                                    </div>

                                    {{-- Admin Official Response --}}
                                    @if(!empty($rev->admin_reply))
                                        <div class="mt-4 p-4 bg-[#FAF8F5] border-l-2 border-[#D4AF37]">
                                            <span class="block text-[10px] uppercase tracking-widest text-[#D4AF37] font-semibold">VRAZIO Atelier Response</span>
                                            <p class="text-xs text-[#0E261C]/80 italic mt-1 leading-relaxed">{{ $rev->admin_reply }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            {{-- Pagination --}}
                            <div class="mt-8">
                                {{ $approvedReviews->fragment('reviews-section')->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════
                 WRITE A REVIEW MODAL
            ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="showModal" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-xs flex items-center justify-center p-4">

                <div @click.away="showModal = false" 
                     class="bg-white border border-[#D4AF37]/40 w-full max-w-xl p-6 sm:p-8 relative shadow-2xl">
                    
                    {{-- Close Button --}}
                    <button type="button" 
                            @click="showModal = false" 
                            class="absolute top-5 right-5 text-gray-400 hover:text-[#0E261C] transition-colors">
                        <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    @guest
                        <div class="text-center py-6">
                            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">Patron Authentication</span>
                            <h3 class="font-display text-2xl font-bold text-[#0E261C] mt-2 mb-3">Sign in to Review</h3>
                            <p class="text-xs sm:text-sm text-[#0E261C]/70 font-light mb-6">
                                To ensure genuine client testimonials, please sign in with your patron account to review this creation.
                            </p>
                            <a href="{{ route('login') }}" 
                               class="inline-block px-8 py-3.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-widest transition-all">
                                Sign In to Patron Vault
                            </a>
                        </div>
                    @else
                        <div>
                            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">Patron Feedback</span>
                            <h3 class="font-display text-2xl font-bold text-[#0E261C] mt-1 mb-1">Evaluate This Creation</h3>
                            <p class="text-xs text-gray-500 font-light mb-6">
                                {{ $product->title }}
                            </p>

                            <form action="{{ route('products.reviews.store', $product->slug) }}" 
                                  method="POST" 
                                  enctype="multipart/form-data"
                                  @submit="submitting = true">
                                @csrf

                                {{-- Star Rating Selector --}}
                                <div class="mb-5">
                                    <label class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-2">
                                        Overall Rating <span class="text-rose-600">*</span>
                                    </label>
                                    <input type="hidden" name="rating" :value="selectedRating">
                                    <div class="flex items-center gap-1.5 cursor-pointer">
                                        <template x-for="star in 5" :key="star">
                                            <button type="button" 
                                                    @click="selectedRating = star"
                                                    @mouseenter="hoverRating = star"
                                                    @mouseleave="hoverRating = 0"
                                                    class="p-1 focus:outline-hidden transition-transform hover:scale-110">
                                                <svg width="24" height="24" 
                                                     class="w-6 h-6" 
                                                     :class="(hoverRating ? hoverRating >= star : selectedRating >= star) ? 'text-[#D4AF37] fill-current' : 'text-gray-300'"
                                                     viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <span class="text-xs font-mono text-gray-500 ml-2" x-text="selectedRating + ' / 5 Stars'"></span>
                                    </div>
                                </div>

                                {{-- Title Input --}}
                                <div class="mb-4">
                                    <label for="review_title" class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                                        Review Headline <span class="text-rose-600">*</span>
                                    </label>
                                    <input type="text" 
                                           id="review_title" 
                                           name="title" 
                                           required 
                                           maxlength="120"
                                           placeholder="e.g. Exceptional brilliance & immaculate setting"
                                           class="w-full px-3.5 py-2.5 text-xs bg-[#FAF8F5] border border-gray-300 focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] focus:outline-hidden text-[#0E261C]">
                                </div>

                                {{-- Experience Textarea --}}
                                <div class="mb-4">
                                    <label for="review_content" class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                                        Your Experience <span class="text-rose-600">*</span>
                                    </label>
                                    <textarea id="review_content" 
                                              name="content" 
                                              rows="4" 
                                              required 
                                              minlength="10" 
                                              maxlength="2500"
                                              placeholder="Share details regarding craftsmanship, gemstone clarity, and packaging..."
                                              class="w-full px-3.5 py-2.5 text-xs bg-[#FAF8F5] border border-gray-300 focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] focus:outline-hidden text-[#0E261C]"></textarea>
                                </div>

                                {{-- Optional Photo Upload --}}
                                <div class="mb-6">
                                    <label class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                                        Product Photographs (Optional, up to 5 images)
                                    </label>
                                    <input type="file" 
                                           name="photos[]" 
                                           multiple 
                                           accept="image/jpeg,image/png,image/webp"
                                           class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:border file:border-[#D4AF37] file:text-[10px] file:uppercase file:font-semibold file:bg-[#0E261C] file:text-[#D4AF37] hover:file:bg-[#D4AF37] hover:file:text-[#0E261C] file:cursor-pointer">
                                    <p class="text-[10px] text-gray-400 mt-1">Accepts JPG, PNG, WEBP up to 5MB each.</p>
                                </div>

                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-4">
                                    <button type="button" 
                                            @click="showModal = false"
                                            class="text-xs uppercase tracking-wider text-gray-500 hover:text-gray-900 transition-colors">
                                        Cancel
                                    </button>

                                    <button type="submit" 
                                            :disabled="submitting"
                                            class="px-8 py-3.5 bg-[#9B1B30] text-white hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-[0.2em] transition-all disabled:opacity-50 cursor-pointer">
                                        <span x-show="!submitting">Submit for Moderation</span>
                                        <span x-show="submitting" x-cloak>Submitting...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endguest
                </div>
            </div>

            {{-- Full Image Lightbox Modal --}}
            <div x-show="activePhotoModal" 
                 x-cloak
                 @click="activePhotoModal = null" 
                 class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4">
                <div class="relative max-w-4xl max-h-[90vh]">
                    <img :src="activePhotoModal" alt="Enlarged patron photo" class="max-w-full max-h-[90vh] object-contain border border-[#D4AF37]">
                    <button type="button" class="absolute top-4 right-4 text-white text-2xl font-bold">&times;</button>
                </div>
            </div>
        </section>

        {{-- Related Pieces Spotlight --}}
        @if($relatedProducts->isNotEmpty())
            <div class="mt-20 border-t border-[#D4AF37]/20 pt-16">
                <div class="text-center max-w-xl mx-auto mb-12">
                    <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">Harmonious Pairings</span>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#0E261C] mt-2">Related Curations</h2>
                    <div class="w-12 h-px bg-[#D4AF37] mx-auto my-3"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

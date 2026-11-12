@props(['product'])

@php
    $images = $product->image_urls ?? [];
    $defaultFallback = \App\Models\Product::getCategoryFallbackImage($product->category);
    $rawPrimary = !empty($images[0]) && !str_contains($images[0], '/images/products/ring-') && !str_contains($images[0], '/images/products/placeholder-') ? $images[0] : $defaultFallback;
    $rawHover = !empty($images[1]) && !str_contains($images[1], '/images/products/ring-') ? $images[1] : $rawPrimary;
    $primaryImg = str_replace('w=1200', 'w=600&q=75', $rawPrimary);
    $hoverImg = str_replace('w=1200', 'w=600&q=75', $rawHover);
    $discountPercent = (float) ($product->discount_percent ?? 0);
    $hasDiscount = $discountPercent > 0;
    $finalPrice = (float) ($product->final_price ?? $product->sale_price ?? $product->price);
    $catLabel = match(strtolower((string)$product->category)) {
        'jewelry' => 'Jewelry',
        'clothing', 'couture' => 'Clothing',
        'watches' => 'Watches',
        'footwear' => 'Footwear',
        default => ucfirst((string)$product->category),
    };
    $tags = is_array($product->tags) ? $product->tags : json_decode($product->tags ?? '[]', true);
@endphp

<div class="group relative bg-white border border-[#D4AF37]/15 hover:border-[#D4AF37]/60 transition-all duration-500 flex flex-col justify-between overflow-hidden shadow-sm hover:shadow-xl">
    {{-- Badges --}}
    <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5 items-start">
        @if($product->is_featured)
            <span class="bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37]/40 text-[10px] uppercase font-bold tracking-widest px-2.5 py-0.5">
                Featured
            </span>
        @endif
        @if($product->category === 'clothing' || $product->category === 'couture')
            <span class="bg-[#0E261C]/90 backdrop-blur text-[#D4AF37] border border-[#D4AF37]/30 text-[9px] uppercase font-semibold tracking-wider px-2 py-0.5 flex items-center gap-1">
                <svg width="10" height="10" class="w-2.5 h-2.5 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Haute Couture
            </span>
        @elseif($product->bis_hallmarked)
            <span class="bg-[#FAF8F5]/90 backdrop-blur text-[#0E261C] border border-[#D4AF37]/30 text-[9px] uppercase font-semibold tracking-wider px-2 py-0.5 flex items-center gap-1">
                <svg width="10" height="10" class="w-2.5 h-2.5 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                BIS Hallmarked
            </span>
        @endif
    </div>

    {{-- Product Image with Hover Flip --}}
    <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-square overflow-hidden bg-[#FAF8F5]">
        <img src="{{ $primaryImg }}" 
             alt="{{ $product->title }}" 
             loading="lazy"
             onerror="this.onerror=null; this.src='{{ $defaultFallback }}';"
             class="w-full h-full object-cover object-center transition-all duration-700 ease-out group-hover:scale-105 group-hover:opacity-0 absolute inset-0">
        <img src="{{ $hoverImg }}" 
             alt="{{ $product->title }} alternate view" 
             loading="lazy"
             onerror="this.onerror=null; this.src='{{ $defaultFallback }}';"
             class="w-full h-full object-cover object-center transition-all duration-700 ease-out opacity-0 scale-95 group-hover:scale-100 group-hover:opacity-100 absolute inset-0">
    </a>

    {{-- Product Details --}}
    <div class="p-5 flex flex-col flex-grow justify-between text-center bg-white border-t border-[#D4AF37]/10">
        <div>
            <div class="text-[11px] uppercase tracking-[0.2em] text-[#D4AF37] font-semibold mb-1 truncate">
                {{ $product->metal_type }} &bull; {{ $catLabel }}
            </div>
            <h3 class="font-display text-base font-semibold text-[#0E261C] line-clamp-1 group-hover:text-[#D4AF37] transition-colors duration-300">
                <a href="{{ route('products.show', $product->slug) }}">
                    {{ $product->title }}
                </a>
            </h3>

            {{-- Tags Strip --}}
            @if(!empty($tags) && count($tags) > 0)
                <div class="flex items-center justify-center gap-1.5 flex-wrap mt-1.5">
                    @foreach(array_slice($tags, 0, 2) as $tag)
                        <span class="text-[9px] uppercase tracking-wider px-1.5 py-0.5 bg-gray-50 text-gray-600 border border-gray-100">
                            #{{ $tag }}
                        </span>
                    @endforeach
                </div>
            @elseif($product->certification)
                <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider">
                    {{ $product->certification }}
                </p>
            @endif
        </div>

        {{-- Requirement 5: Simplified Customer Price Display: [DISCOUNT BADGE] [FINAL GST-INCLUSIVE PRICE] --}}
        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
            <div class="text-left flex items-center gap-1.5">
                @if($hasDiscount)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#9B1B30] text-white tracking-wider">
                        {{ ((float) $discountPercent + 0) }}% OFF
                    </span>
                    <span class="font-display text-base font-bold text-[#0E261C]">
                        ₹{{ number_format($finalPrice, 0) }}
                    </span>
                @else
                    <span class="font-display text-base font-bold text-[#0E261C]">
                        ₹{{ number_format($finalPrice, 0) }}
                    </span>
                @endif
            </div>

            <form action="{{ route('cart.add') }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" 
                        class="p-2 border border-[#0E261C] text-[#0E261C] hover:bg-[#0E261C] hover:text-[#D4AF37] transition-all duration-300"
                        title="Add to Cart">
                    <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

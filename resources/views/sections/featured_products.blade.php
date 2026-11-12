@php
    $fpConfig = $homepageConfig['featured_products_section'] ?? [];
    $fpBadge = $fpConfig['badge'] ?? 'Atelier Curations';
    $fpHeading = $fpConfig['heading'] ?? 'Signature Pieces';
    $fpDesc = $fpConfig['description'] ?? 'Individually hallmarked creations exemplifying our highest standards of craftsmanship.';
    $fpBtnLabel = $fpConfig['button_label'] ?? 'View Entire High Jewellery Vault';
    $fpBtnLink = $fpConfig['button_link'] ?? route('products.index');
@endphp

@if($featuredProducts->isNotEmpty())
    <section class="py-20 bg-white border-y border-[#D4AF37]/15">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                    {{ $fpBadge }}
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#0E261C] mt-2">
                    {{ $fpHeading }}
                </h2>
                <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
                <p class="text-sm md:text-base text-[#0E261C]/70 font-light">
                    {{ $fpDesc }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                @foreach($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-14 text-center">
                <a href="{{ $fpBtnLink }}" 
                   class="inline-flex items-center justify-center px-10 py-4 text-xs font-semibold uppercase tracking-[0.25em] bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all duration-300">
                    {{ $fpBtnLabel }}
                </a>
            </div>
        </div>
    </section>
@endif

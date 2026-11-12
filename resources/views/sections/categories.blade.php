@php
    $catConfig = $homepageConfig['category_section'] ?? [];
    $catBadge = $catConfig['badge'] ?? 'Haute Collections';
    $catHeading = $catConfig['heading'] ?? 'Curated Atelier Tiers';
    $catDesc = $catConfig['description'] ?? 'Explore signature compositions forged in 18KT yellow gold, rose gold, and platinum.';
@endphp

<section class="py-20 md:py-28 bg-[#FAF8F5]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                {{ $catBadge }}
            </span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#0E261C] mt-2">
                {{ $catHeading }}
            </h2>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
            <p class="text-sm md:text-base text-[#0E261C]/70 font-light">
                {{ $catDesc }}
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-5">
            @foreach($curatedCategories as $cat)
                @php
                    $catImg = $cat['image'] ?? '';
                    $catImgUrl = \Illuminate\Support\Str::startsWith($catImg, ['http://', 'https://', '/']) ? $catImg : asset('storage/' . $catImg);
                    $targetSlug = $cat['slug'] ?? 'collections';
                    $targetUrl = \Illuminate\Support\Str::startsWith($targetSlug, '/') ? $targetSlug : route('products.category', $targetSlug);
                @endphp
                <a href="{{ $targetUrl }}" 
                   class="group relative aspect-[3/4] overflow-hidden bg-[#0E261C] border border-[#D4AF37]/20 flex flex-col justify-end p-5 shadow-sm hover:shadow-2xl transition-all duration-500">
                    <img src="{{ $catImgUrl }}" 
                         alt="{{ $cat['name'] }}" 
                         loading="lazy"
                         class="absolute inset-0 w-full h-full object-cover object-center filter brightness-90 group-hover:scale-110 group-hover:brightness-75 transition-all duration-700 ease-out">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0A1C15] via-[#0A1C15]/40 to-transparent"></div>

                    <div class="relative z-10 text-center">
                        <span class="block text-[10px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold mb-1 opacity-90 group-hover:tracking-[0.35em] transition-all">
                            Collection
                        </span>
                        <h3 class="font-display text-lg md:text-xl font-bold text-[#FAF8F5]">
                            {{ $cat['name'] }}
                        </h3>
                        @if(!empty($cat['description']))
                            <p class="text-[10px] text-[#FAF8F5]/70 line-clamp-2 mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                {{ $cat['description'] }}
                            </p>
                        @endif
                        <span class="inline-block mt-3 text-[9px] uppercase tracking-widest text-[#D4AF37] font-semibold border-b border-[#D4AF37]/50">
                            Shop Now &rarr;
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

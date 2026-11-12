@php
    $trustConfig = $homepageConfig['trust_badges_section'] ?? [];
    $trustItems = $trustConfig['items'] ?? [
        ['title' => '100% BIS Hallmarked Gold & Silk Mark', 'link' => '/shipping-policy', 'is_active' => true],
        ['title' => 'GIA • IGI • SGL Certified Diamonds', 'link' => '/about', 'is_active' => true],
        ['title' => 'Complimentary Armoured Doorstep Transit', 'link' => '/shipping-policy', 'is_active' => true],
    ];
@endphp

@if(!isset($trustConfig['is_active']) || $trustConfig['is_active'])
    <section class="bg-[#0A1C15] border-y border-[#D4AF37]/20 py-5">
        <div class="max-w-7xl mx-auto px-6 flex flex-wrap items-center justify-center gap-6 md:gap-12 text-[11px] sm:text-xs uppercase tracking-[0.2em] text-[#FAF8F5]/80">
            @foreach($trustItems as $item)
                @if(!isset($item['is_active']) || $item['is_active'])
                    <a href="{{ $item['link'] ?? '#' }}" class="flex items-center gap-2.5 hover:text-[#D4AF37] transition-colors group">
                        <span class="w-2 h-2 rotate-45 bg-[#D4AF37] shrink-0 group-hover:scale-125 transition-transform"></span>
                        <span class="font-medium font-body">{{ $item['title'] ?? '' }}</span>
                        @if(!empty($item['description']))
                            <span class="hidden lg:inline text-[#FAF8F5]/40 text-[10px] lowercase italic font-light font-body">({{ $item['description'] }})</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </section>
@endif

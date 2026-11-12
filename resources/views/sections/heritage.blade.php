@php
    $hConfig = $homepageConfig['heritage_section'] ?? [];
    $hBadge = $hConfig['badge'] ?? 'Heritage & Provenance';
    $hHeading = $hConfig['heading'] ?? 'Where Alchemy Meets Imperial Precision';
    $hP1 = $hConfig['paragraph_1'] ?? 'At VRAZIO, jewellery transcends ornamentation — it becomes an enduring legacy. Conceived in the heart of artisanal heritage, our atelier merges age-old hand-setting practices with micron-level metallurgical precision.';
    $hP2 = $hConfig['paragraph_2'] ?? 'Each solitaire is individually curated by certified gemologists, adhering to triple-excellent proportions. Every ounce of gold is 100% BIS Hallmarked, ensuring absolute purity and ethical provenance.';
    
    $hImg = $hConfig['main_image'] ?? 'https://images.unsplash.com/photo-1531995811006-35cb42e1a022?auto=format&fit=crop&w=1000&q=80';
    $hImgUrl = \Illuminate\Support\Str::startsWith($hImg, ['http://', 'https://', '/']) ? $hImg : asset('storage/' . $hImg);

    $floatTitle = $hConfig['floating_badge_title'] ?? '3 Generations';
    $floatSubtitle = $hConfig['floating_badge_subtitle'] ?? 'Of Master Goldsmithing';
    
    $stats = $hConfig['stats'] ?? [
        ['value' => '0.01%', 'label' => 'Top Tier Gemstone Selection'],
        ['value' => '100%', 'label' => 'Insured Armoured Transit'],
    ];

    $btnLabel = $hConfig['button_label'] ?? 'Discover Our Private Atelier Process';
    $btnLink = $hConfig['button_link'] ?? route('pages.about');
@endphp

<section class="py-24 bg-[#0E261C] text-[#FAF8F5] relative overflow-hidden">
    <div class="absolute -right-20 -top-20 w-96 h-96 rounded-full bg-[#D4AF37]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-96 h-96 rounded-full bg-[#D4AF37]/5 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Atelier Imagery Collage --}}
            <div class="relative">
                <div class="border border-[#D4AF37]/30 p-3 sm:p-4 bg-[#0A1C15]">
                    <img src="{{ $hImgUrl }}" 
                         alt="{{ $hHeading }}" 
                         class="w-full aspect-[4/3] object-cover filter contrast-110">
                </div>
                {{-- Floating Badge --}}
                @if(!empty($floatTitle))
                    <div class="absolute -bottom-6 -right-4 sm:-bottom-8 sm:right-6 bg-[#0A1C15] border border-[#D4AF37] p-5 shadow-2xl max-w-xs text-center">
                        <span class="block font-display text-2xl md:text-3xl font-bold text-[#D4AF37]">{{ $floatTitle }}</span>
                        @if(!empty($floatSubtitle))
                            <span class="text-[10px] uppercase tracking-[0.2em] text-[#FAF8F5]/80">{{ $floatSubtitle }}</span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Textual Narrative --}}
            <div class="lg:pl-6">
                <div class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold mb-3">
                    <span class="w-2 h-px bg-[#D4AF37]"></span>
                    {{ $hBadge }}
                </div>

                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#FAF8F5] leading-tight">
                    {{ $hHeading }}
                </h2>

                <div class="w-20 h-px bg-[#D4AF37] my-6"></div>

                <div class="space-y-4 text-sm sm:text-base text-[#FAF8F5]/80 font-light leading-relaxed">
                    @if(!empty($hP1))
                        <p>{{ $hP1 }}</p>
                    @endif
                    @if(!empty($hP2))
                        <p>{{ $hP2 }}</p>
                    @endif
                </div>

                @if(!empty($stats))
                    <div class="grid grid-cols-2 gap-6 mt-8 pt-8 border-t border-[#D4AF37]/20">
                        @foreach($stats as $st)
                            <div>
                                <div class="font-display text-2xl sm:text-3xl font-bold text-[#D4AF37]">{{ $st['value'] ?? '' }}</div>
                                <div class="text-xs uppercase tracking-wider text-[#FAF8F5]/70 mt-1">{{ $st['label'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(!empty($btnLabel))
                    <div class="mt-8">
                        <a href="{{ $btnLink }}" 
                           class="inline-flex items-center gap-3 text-xs uppercase tracking-[0.25em] text-[#D4AF37] hover:text-white font-semibold group transition-colors">
                            <span>{{ $btnLabel }}</span>
                            <svg width="16" height="16" class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

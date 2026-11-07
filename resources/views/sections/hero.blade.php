@php
    $heroConfig = $themeSettings['hero_config'] ?? [];
    if (is_string($heroConfig)) {
        $heroConfig = json_decode($heroConfig, true) ?? [];
    }

    $defaultHeadline = $heroConfig['headline'] ?? 'Exquisite Craftsmanship, Timeless Elegance';
    $defaultSubtitle = $heroConfig['subtitle'] ?? 'Discover our curated collection of BIS Hallmarked fine jewellery & solitaires, crafted by master artisans.';
    $defaultBgImage = $heroConfig['background_image'] ?? 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=2000&q=85';
    $defaultCtaLabel = $heroConfig['cta_label'] ?? 'Explore Collections';
    $defaultCtaLink = $heroConfig['cta_link'] ?? route('products.index');

    $slides = [
        [
            'badge' => 'Haute Joaillerie • Atelier Privé',
            'headline' => $defaultHeadline,
            'subtitle' => $defaultSubtitle,
            'image' => $defaultBgImage,
            'cta_primary_label' => $defaultCtaLabel,
            'cta_primary_link' => $defaultCtaLink,
            'cta_secondary_label' => 'Atelier Heritage',
            'cta_secondary_link' => route('pages.about'),
        ],
        [
            'badge' => 'Haute Couture & Royal Apparel',
            'headline' => 'Imperial Zardozi & Banarasi Heirlooms',
            'subtitle' => 'Bespoke bridal lehengas, handwoven Banarasi Katan silks, and royal micro-velvet sherwanis embellished with authentic 24KT gold zari.',
            'image' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=2000&q=85',
            'cta_primary_label' => 'Explore Couture',
            'cta_primary_link' => route('products.couture'),
            'cta_secondary_label' => 'Bridal Lehengas',
            'cta_secondary_link' => route('products.couture', ['tag' => 'Bridal Lehenga']),
        ],
        [
            'badge' => 'Rare Solitaires & Sovereign Emeralds',
            'headline' => 'Flawless Brilliance, Forged in Platinum',
            'subtitle' => 'Triple-excellent cut D-Flawless diamonds certified by GIA and untreated Zambian emeralds, sculpted in dense Platinum 950 and 18KT gold.',
            'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=2000&q=85',
            'cta_primary_label' => 'View Solitaires',
            'cta_primary_link' => url('/collections/solitaires'),
            'cta_secondary_label' => 'Private Concierge',
            'cta_secondary_link' => route('pages.contact'),
        ],
    ];
@endphp

<section class="relative min-h-[88vh] md:min-h-[94vh] flex items-center justify-center overflow-hidden bg-[#0A1C15]"
         x-data="{
             activeSlide: 0,
             totalSlides: {{ count($slides) }},
             timer: null,
             duration: 6500,
             isHovered: false,
             init() {
                 this.startTimer();
             },
             startTimer() {
                 if (this.timer) clearInterval(this.timer);
                 this.timer = setInterval(() => {
                     if (!this.isHovered) {
                         this.next();
                     }
                 }, this.duration);
             },
             next() {
                 this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
             },
             prev() {
                 this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
             },
             goTo(index) {
                 this.activeSlide = index;
                 this.startTimer();
             }
         }"
         @mouseenter="isHovered = true"
         @mouseleave="isHovered = false">

    {{-- ═══════════════════════════════════════════════════════════════════
         3 ANIMATED BANNER PICTURES WITH KEN BURNS SLOW ZOOM & CROSS-FADE
    ═══════════════════════════════════════════════════════════════════ --}}
    @foreach($slides as $index => $slide)
        <div class="absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out pointer-events-none"
             :class="activeSlide === {{ $index }} ? 'opacity-100' : 'opacity-0'">
            <img src="{{ $slide['image'] }}" 
                 alt="{{ $slide['headline'] }}" 
                 class="w-full h-full object-cover object-center filter brightness-45 contrast-125 transform transition-transform duration-[7000ms] ease-out"
                 :class="activeSlide === {{ $index }} ? 'scale-115' : 'scale-100'">
            <div class="absolute inset-0 bg-gradient-to-t from-[#0A1C15] via-[#0E261C]/65 to-[#0A1C15]/85"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-transparent via-[#0A1C15]/45 to-[#0A1C15]"></div>
        </div>
    @endforeach

    {{-- Content Hero Box (Animated Switch per Slide) --}}
    <div class="relative z-10 max-w-5xl mx-auto px-6 py-20 text-center text-[#FAF8F5]">
        @foreach($slides as $index => $slide)
            <div x-show="activeSlide === {{ $index }}"
                 x-transition:enter="transition ease-out duration-700 delay-150"
                 x-transition:enter-start="opacity-0 translate-y-6"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300 absolute inset-x-0"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">
                
                {{-- Slide Badge --}}
                <div class="inline-flex items-center gap-3 border-y border-[#D4AF37]/50 py-1.5 px-6 mb-6">
                    <span class="w-1.5 h-1.5 rotate-45 bg-[#D4AF37]"></span>
                    <span class="text-xs uppercase tracking-[0.35em] text-[#D4AF37] font-semibold">
                        {{ $slide['badge'] }}
                    </span>
                    <span class="w-1.5 h-1.5 rotate-45 bg-[#D4AF37]"></span>
                </div>

                {{-- Headline --}}
                <h1 class="font-display text-4xl sm:text-6xl md:text-7xl font-bold tracking-tight text-[#FAF8F5] leading-tight md:leading-[1.15] max-w-4xl mx-auto drop-shadow-lg">
                    {{ $slide['headline'] }}
                </h1>

                {{-- Subtitle --}}
                <p class="font-body text-base sm:text-lg md:text-xl text-[#FAF8F5]/85 font-light mt-6 max-w-2xl mx-auto leading-relaxed drop-shadow">
                    {{ $slide['subtitle'] }}
                </p>

                {{-- Action Buttons --}}
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-5">
                    <a href="{{ $slide['cta_primary_link'] }}" 
                       class="inline-flex items-center justify-center px-10 py-4 text-xs font-semibold uppercase tracking-[0.25em] bg-[#9B1B30] text-white hover:bg-[#D4AF37] hover:text-[#0E261C] border border-[#9B1B30] hover:border-[#D4AF37] transition-all duration-300 shadow-2xl">
                        {{ $slide['cta_primary_label'] }}
                    </a>
                    <a href="{{ $slide['cta_secondary_link'] }}" 
                       class="inline-flex items-center justify-center px-10 py-4 text-xs font-semibold uppercase tracking-[0.25em] bg-transparent text-[#D4AF37] border border-[#D4AF37]/60 hover:bg-[#D4AF37]/10 hover:border-[#D4AF37] transition-all duration-300">
                        {{ $slide['cta_secondary_label'] }}
                    </a>
                </div>
            </div>
        @endforeach

        {{-- Certifications Strip --}}
        <div class="mt-20 pt-8 border-t border-[#D4AF37]/20 flex flex-wrap items-center justify-center gap-6 md:gap-10 text-[11px] uppercase tracking-[0.2em] text-[#FAF8F5]/70">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                100% BIS Hallmarked Gold &amp; Silk Mark
            </span>
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                GIA &bull; IGI &bull; SGL Certified Diamonds
            </span>
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Complimentary Armoured Doorstep Transit
            </span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         INTERACTIVE NAVIGATION CONTROLS (NEXT / PREV & SLIDE INDICATORS)
    ═══════════════════════════════════════════════════════════════════ --}}
    {{-- Previous Arrow --}}
    <button @click="prev()" 
            aria-label="Previous Slide"
            class="hidden md:flex absolute left-6 top-1/2 -translate-y-1/2 z-20 w-12 h-12 items-center justify-center border border-[#D4AF37]/30 text-[#D4AF37] bg-[#0E261C]/50 hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all duration-300">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
    </button>

    {{-- Next Arrow --}}
    <button @click="next()" 
            aria-label="Next Slide"
            class="hidden md:flex absolute right-6 top-1/2 -translate-y-1/2 z-20 w-12 h-12 items-center justify-center border border-[#D4AF37]/30 text-[#D4AF37] bg-[#0E261C]/50 hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all duration-300">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
    </button>

    {{-- Numbered Slide Indicators with Progress Bar --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-3">
        @foreach($slides as $index => $slide)
            <button @click="goTo({{ $index }})" 
                    class="group flex items-center gap-2 py-2 px-3 text-xs font-mono transition-all duration-300"
                    :class="activeSlide === {{ $index }} ? 'text-[#D4AF37]' : 'text-white/50 hover:text-white'">
                <span class="text-[10px] tracking-widest font-semibold">0{{ $index + 1 }}</span>
                <span class="block h-0.5 transition-all duration-500 rounded-full"
                      :class="activeSlide === {{ $index }} ? 'w-8 bg-[#D4AF37]' : 'w-3 bg-white/30 group-hover:bg-white/60'"></span>
            </button>
        @endforeach
    </div>
</section>

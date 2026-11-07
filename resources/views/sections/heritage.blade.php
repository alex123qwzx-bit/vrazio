<section class="py-24 bg-[#0E261C] text-[#FAF8F5] relative overflow-hidden">
    <div class="absolute -right-20 -top-20 w-96 h-96 rounded-full bg-[#D4AF37]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-96 h-96 rounded-full bg-[#D4AF37]/5 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Atelier Imagery Collage --}}
            <div class="relative">
                <div class="border border-[#D4AF37]/30 p-3 sm:p-4 bg-[#0A1C15]">
                    <img src="https://images.unsplash.com/photo-1531995811006-35cb42e1a022?auto=format&fit=crop&w=1000&q=80" 
                         alt="Master Jeweller setting diamond solitaire" 
                         class="w-full aspect-[4/3] object-cover filter contrast-110">
                </div>
                {{-- Floating Badge --}}
                <div class="absolute -bottom-6 -right-4 sm:-bottom-8 sm:right-6 bg-[#0A1C15] border border-[#D4AF37] p-5 shadow-2xl max-w-xs text-center">
                    <span class="block font-display text-2xl md:text-3xl font-bold text-[#D4AF37]">3 Generations</span>
                    <span class="text-[10px] uppercase tracking-[0.2em] text-[#FAF8F5]/80">Of Master Goldsmithing</span>
                </div>
            </div>

            {{-- Textual Narrative --}}
            <div class="lg:pl-6">
                <div class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold mb-3">
                    <span class="w-2 h-px bg-[#D4AF37]"></span>
                    Heritage &amp; Provenance
                </div>

                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#FAF8F5] leading-tight">
                    Where Alchemy Meets Imperial Precision
                </h2>

                <div class="w-20 h-px bg-[#D4AF37] my-6"></div>

                <div class="space-y-4 text-sm sm:text-base text-[#FAF8F5]/80 font-light leading-relaxed">
                    <p>
                        At VRAZIO, jewellery transcends ornamentation — it becomes an enduring legacy. Conceived in the heart of artisanal heritage, our atelier merges age-old hand-setting practices with micron-level metallurgical precision.
                    </p>
                    <p>
                        Each solitaire is individually curated by certified gemologists, adhering to triple-excellent proportions. Every ounce of gold is 100% BIS Hallmarked, ensuring absolute purity and ethical provenance.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-6 mt-8 pt-8 border-t border-[#D4AF37]/20">
                    <div>
                        <div class="font-display text-2xl sm:text-3xl font-bold text-[#D4AF37]">0.01%</div>
                        <div class="text-xs uppercase tracking-wider text-[#FAF8F5]/70 mt-1">Top Tier Gemstone Selection</div>
                    </div>
                    <div>
                        <div class="font-display text-2xl sm:text-3xl font-bold text-[#D4AF37]">100%</div>
                        <div class="text-xs uppercase tracking-wider text-[#FAF8F5]/70 mt-1">Insured Armoured Transit</div>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('pages.about') }}" 
                       class="inline-flex items-center gap-3 text-xs uppercase tracking-[0.25em] text-[#D4AF37] hover:text-white font-semibold group transition-colors">
                        <span>Discover Our Private Atelier Process</span>
                        <svg width="16" height="16" class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

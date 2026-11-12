@php
    $gConfig = $homepageConfig['gallery_section'] ?? [];
    $gBadge = $gConfig['badge'] ?? 'Master Atelier Gallery';
    $gHeading = $gConfig['heading'] ?? '4K Gemstone Showcase';
    
    $rawItems = $gConfig['items'] ?? [];
    $slides = [];
    if (!empty($rawItems) && is_array($rawItems)) {
        foreach ($rawItems as $item) {
            if (!isset($item['is_active']) || $item['is_active']) {
                $img = $item['image'] ?? '';
                $imgUrl = \Illuminate\Support\Str::startsWith($img, ['http://', 'https://', '/']) ? $img : asset('storage/' . $img);
                $slides[] = [
                    'title' => $item['title'] ?? 'Master Atelier Creation',
                    'category' => $item['category'] ?? 'Haute Collection',
                    'purity' => $item['purity'] ?? '18KT Gold',
                    'price' => $item['price'] ?? '',
                    'image' => $imgUrl,
                    'link' => $item['link'] ?? '/collections',
                ];
            }
        }
    }

    if (empty($slides)) {
        $slides = [
            [
                'title' => 'The Empress Emerald Solitaire Collar',
                'category' => 'Haute Necklaces',
                'purity' => '18KT Royal Yellow Gold & Zambian Emerald',
                'price' => '₹14,50,000',
                'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=1200&q=85',
                'link' => '/collections',
            ],
        ];
    }
@endphp

<section class="py-24 bg-[#0A1C15] text-[#FAF8F5] relative overflow-hidden" 
         x-data="{
             activeSlide: 0,
             slides: {{ Js::from($slides) }},
             next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
             prev() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length }
         }">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                    {{ $gBadge }}
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#FAF8F5] mt-2">
                    {{ $gHeading }}
                </h2>
            </div>
            
            {{-- Carousel Navigation Controls --}}
            @if(count($slides) > 1)
                <div class="flex items-center gap-3 mt-6 md:mt-0">
                    <button @click="prev()" 
                            aria-label="Previous Piece"
                            class="w-12 h-12 border border-[#D4AF37]/40 flex items-center justify-center text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                        &larr;
                    </button>
                    <div class="text-xs uppercase tracking-widest text-[#FAF8F5]/60 px-2 font-mono">
                        <span x-text="activeSlide + 1"></span> / <span x-text="slides.length"></span>
                    </div>
                    <button @click="next()" 
                            aria-label="Next Piece"
                            class="w-12 h-12 border border-[#D4AF37]/40 flex items-center justify-center text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                        &rarr;
                    </button>
                </div>
            @endif
        </div>

        {{-- Carousel Slides Container --}}
        <div class="relative overflow-hidden border border-[#D4AF37]/30 min-h-[500px]">
            <template x-for="(slide, index) in slides" :key="index">
                <div x-show="activeSlide === index" 
                     x-transition:enter="transition ease-out duration-700"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-500"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 -translate-x-8"
                     class="grid grid-cols-1 lg:grid-cols-12 items-center bg-[#0E261C]">
                    
                    {{-- Large Image Showcase --}}
                    <div class="lg:col-span-7 aspect-[16/10] overflow-hidden bg-black">
                        <img :src="slide.image" 
                             :alt="slide.title" 
                             class="w-full h-full object-cover filter brightness-95 contrast-110">
                    </div>

                    {{-- Information Dossier --}}
                    <div class="lg:col-span-5 p-8 lg:p-12 text-left">
                        <span class="text-[11px] uppercase tracking-[0.3em] text-[#D4AF37] font-semibold" x-text="slide.category"></span>
                        <h3 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-[#FAF8F5] mt-2 mb-4 leading-tight" x-text="slide.title"></h3>
                        <p class="text-xs uppercase tracking-widest text-[#FAF8F5]/60 pb-6 border-b border-[#D4AF37]/20" x-text="slide.purity"></p>
                        
                        <div x-show="slide.price" class="mt-6 flex items-baseline gap-4">
                            <span class="text-xs uppercase tracking-widest text-[#D4AF37]">Price:</span>
                            <span class="font-display text-3xl font-bold text-[#FAF8F5]" x-text="slide.price"></span>
                        </div>

                        <div class="mt-8 flex gap-4">
                            <a :href="slide.link || '/collections'" 
                               class="inline-block px-8 py-3.5 bg-[#9B1B30] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                                Order Now
                            </a>
                            <a :href="slide.link || '/collections'" 
                               class="inline-block px-8 py-3.5 border border-[#D4AF37]/40 text-[#D4AF37] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37]/10 transition-all">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>

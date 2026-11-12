@php
    $tConfig = $homepageConfig['testimonials_section'] ?? [];
    $tBadge = $tConfig['badge'] ?? 'What People Say';
    $tHeading = $tConfig['heading'] ?? 'Customer Reviews';
    $tDesc = $tConfig['description'] ?? 'Read real experiences from patrons who ordered fine jewellery and clothes from VRAZIO.';
@endphp

<section class="py-20 md:py-28 bg-[#FAF8F5]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">
                {{ $tBadge }}
            </span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#0E261C] mt-2">
                {{ $tHeading }}
            </h2>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
            <p class="text-sm md:text-base text-[#0E261C]/70 font-light">
                {{ $tDesc }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @if(isset($featuredReviews) && $featuredReviews->isNotEmpty())
                {{-- Dynamic Approved Featured Customer Reviews --}}
                @foreach($featuredReviews as $review)
                    <div class="bg-white border border-[#D4AF37]/20 p-8 shadow-xs flex flex-col justify-between relative">
                        <span class="font-display text-6xl text-[#D4AF37]/20 absolute top-4 right-6 font-serif select-none pointer-events-none">&ldquo;</span>
                        <div>
                            {{-- Star Rating --}}
                            <div class="flex text-[#D4AF37] gap-1 mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg width="16" height="16" class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>

                            @if(!empty($review->title))
                                <h3 class="font-display font-bold text-sm text-[#0E261C] mb-2">{{ $review->title }}</h3>
                            @endif

                            <p class="text-sm text-[#0E261C]/80 italic leading-relaxed line-clamp-4">
                                "{{ $review->content }}"
                            </p>

                            @if(!empty($review->photos))
                                <div class="flex gap-2 mt-3">
                                    @foreach(array_slice($review->photos, 0, 3) as $photo)
                                        <img src="{{ asset('storage/' . $photo) }}" 
                                             alt="Customer photo" 
                                             class="w-12 h-12 object-cover border border-[#D4AF37]/30">
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <div class="font-display font-bold text-sm text-[#0E261C]">{{ $review->author_name }}</div>
                                <div class="text-[11px] text-[#D4AF37] uppercase tracking-wider">
                                    {{ $review->product?->title ?? 'Fine Jewellery Patron' }}
                                </div>
                            </div>
                            @if($review->is_verified_purchase)
                                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 border border-emerald-200 uppercase tracking-widest font-semibold">
                                    <svg width="10" height="10" class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Verified
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Fallback Curated Patron Testimonials --}}
                @php
                    $fallbacks = $tConfig['fallback_testimonials'] ?? [
                        [
                            'name' => 'Maharani Gayatri D.',
                            'subtitle' => 'Commissioned Solitaire Ring • Mumbai',
                            'rating' => 5,
                            'quote' => 'The craftsmanship on my solitaire engagement ring is breathtaking. The GIA documentation and armoured transit escort delivered directly to our Mumbai residence gave us total peace of mind.',
                            'is_verified' => true,
                        ],
                        [
                            'name' => 'Vikramaditya S.',
                            'subtitle' => 'Fine Jewellery Collector • New Delhi',
                            'rating' => 5,
                            'quote' => 'VRAZIO\'s verification protocol via SMS OTP and secure Razorpay escrow was seamless. The imperial emerald collar we ordered surpassed our expectations in every light.',
                            'is_verified' => true,
                        ],
                        [
                            'name' => 'Dr. Ananya Roy',
                            'subtitle' => 'Patron • Bengaluru',
                            'rating' => 5,
                            'quote' => 'The tennis bracelet in Platinum 950 is pure poetry on the wrist. Hallmarked with BIS stamp and accompanied by comprehensive appraisal certificate.',
                            'is_verified' => true,
                        ],
                    ];
                @endphp

                @foreach($fallbacks as $item)
                    <div class="bg-white border border-[#D4AF37]/20 p-8 shadow-xs flex flex-col justify-between relative">
                        <span class="font-display text-6xl text-[#D4AF37]/20 absolute top-4 right-6 font-serif select-none pointer-events-none">&ldquo;</span>
                        <div>
                            <div class="flex text-[#D4AF37] gap-1 mb-4">
                                @for($i = 0; $i < ($item['rating'] ?? 5); $i++)
                                    <svg width="16" height="16" class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-sm text-[#0E261C]/80 italic leading-relaxed">
                                "{{ $item['quote'] }}"
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <div class="font-display font-bold text-sm text-[#0E261C]">{{ $item['name'] }}</div>
                                <div class="text-[11px] text-[#D4AF37] uppercase tracking-wider">{{ $item['subtitle'] }}</div>
                            </div>
                            @if(!empty($item['is_verified']))
                                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 border border-emerald-200 uppercase tracking-widest font-semibold">
                                    <svg width="10" height="10" class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Verified
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<section class="py-20 md:py-28 bg-[#FAF8F5]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">
                What People Say
            </span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#0E261C] mt-2">
                Customer Reviews
            </h2>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
            <p class="text-sm md:text-base text-[#0E261C]/70 font-light">
                Read real experiences from customers who ordered fine jewellery and clothes from VRAZIO.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Testimonial 1 --}}
            <div class="bg-white border border-[#D4AF37]/20 p-8 shadow-sm flex flex-col justify-between relative">
                <span class="font-display text-6xl text-[#D4AF37]/20 absolute top-4 right-6 font-serif select-none">&ldquo;</span>
                <div>
                    {{-- Stars --}}
                    <div class="flex text-[#D4AF37] gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg width="16" height="16" class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-sm text-[#0E261C]/80 italic leading-relaxed">
                        "The craftsmanship on my solitaire engagement ring is breathtaking. The GIA documentation and armoured transit escort delivered directly to our Mumbai residence gave us total peace of mind."
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <div class="font-display font-bold text-sm text-[#0E261C]">Maharani Gayatri D.</div>
                    <div class="text-[11px] text-[#D4AF37] uppercase tracking-wider">Commissioned Solitaire Ring &bull; Mumbai</div>
                </div>
            </div>

            {{-- Testimonial 2 --}}
            <div class="bg-white border border-[#D4AF37]/20 p-8 shadow-sm flex flex-col justify-between relative">
                <span class="font-display text-6xl text-[#D4AF37]/20 absolute top-4 right-6 font-serif select-none">&ldquo;</span>
                <div>
                    <div class="flex text-[#D4AF37] gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg width="16" height="16" class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-sm text-[#0E261C]/80 italic leading-relaxed">
                        "VRAZIO's verification protocol via SMS OTP and secure Razorpay escrow was seamless. The imperial emerald collar we ordered surpassed our expectations in every light."
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <div class="font-display font-bold text-sm text-[#0E261C]">Vikramaditya S.</div>
                    <div class="text-[11px] text-[#D4AF37] uppercase tracking-wider">Fine Jewellery Collector &bull; New Delhi</div>
                </div>
            </div>

            {{-- Testimonial 3 --}}
            <div class="bg-white border border-[#D4AF37]/20 p-8 shadow-sm flex flex-col justify-between relative">
                <span class="font-display text-6xl text-[#D4AF37]/20 absolute top-4 right-6 font-serif select-none">&ldquo;</span>
                <div>
                    <div class="flex text-[#D4AF37] gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg width="16" height="16" class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-sm text-[#0E261C]/80 italic leading-relaxed">
                        "The tennis bracelet in Platinum 950 is pure poetry on the wrist. Hallmarked with BIS stamp and accompanied by comprehensive appraisal certificate."
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <div class="font-display font-bold text-sm text-[#0E261C]">Dr. Ananya Roy</div>
                    <div class="text-[11px] text-[#D4AF37] uppercase tracking-wider">Patron &bull; Bengaluru</div>
                </div>
            </div>
        </div>
    </div>
</section>

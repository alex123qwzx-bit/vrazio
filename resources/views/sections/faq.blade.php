<section class="py-20 md:py-28 bg-[#0E261C] text-[#FAF8F5]">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                Clientele Concierge
            </span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[#FAF8F5] mt-2">
                Frequently Asked Questions
            </h2>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
            <p class="text-sm md:text-base text-[#FAF8F5]/70 font-light">
                Insights into hallmarking authentication, armoured transit logistics, and our master atelier protocols.
            </p>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            @php
                $faqs = [
                    [
                        'q' => 'How does VRAZIO verify BIS Hallmarking and Gemstone Certifications?',
                        'a' => 'Every gold jewel crafted at VRAZIO bears the official Bureau of Indian Standards (BIS) hallmark, featuring the BIS logo, purity grade in karats/fineness (e.g., 750 for 18KT, 916 for 22KT), 6-digit HUID code, and assaying center emblem. All solitaire diamonds above 0.30 carats come with GIA, IGI, or SGL dossiers verifiable online in global gemological registries.'
                    ],
                    [
                        'q' => 'What is the procedure for Armoured Doorstep Transit and Insurance?',
                        'a' => 'All orders are dispatched in tamper-evident sealed security vaults via specialized armoured logistical partners (such as Sequel Logistics / Brink’s). Shipments are 100% insured until delivery. For security reasons, transit requires OTP confirmation by the patron upon handover.'
                    ],
                    [
                        'q' => 'Why is Phone SMS OTP verification mandatory before payment?',
                        'a' => 'To safeguard patron security and ensure secure consignment delivery, every order requires two-factor validation via SMS OTP powered by Fast2SMS. This ensures only authorized patrons commission high-value pieces and provides seamless dispatch coordination.'
                    ],
                    [
                        'q' => 'What payment gateways are supported for high jewellery purchases?',
                        'a' => 'We utilize Razorpay with enterprise-level 256-bit encryption. We accept Indian and International Credit/Debit Cards, Net Banking across all major banks, UPI, and verified Bank Wire / NEFT / RTGS transfers with escrow settlement.'
                    ],
                    [
                        'q' => 'Can I commission a bespoke design or customized solitaire setting?',
                        'a' => 'Yes. Our private atelier welcomes bespoke commissions. You may schedule a private consultation with our Master Goldsmith to select custom rough gems, custom metal alloys, and personalized hand inscriptions.'
                    ],
                    [
                        'q' => 'What is the VRAZIO return and exchange policy?',
                        'a' => 'We offer a 15-day complimentary inspection and exchange period for standard collection pieces in original condition with security tags intact. Bespoke and custom engraved commissions are non-refundable but covered under our Lifetime Craftsmanship Warranty.'
                    ]
                ];
            @endphp

            @foreach($faqs as $index => $item)
                <div class="border border-[#D4AF37]/20 bg-[#0A1C15]/70 overflow-hidden transition-all duration-200">
                    <button @click="active = (active === {{ $index }} ? null : {{ $index }})" 
                            class="w-full px-6 py-5 text-left flex items-center justify-between text-sm md:text-base font-display font-semibold text-[#FAF8F5] hover:text-[#D4AF37] transition-colors">
                        <span>{{ $item['q'] }}</span>
                        <span class="ml-4 font-mono text-lg text-[#D4AF37]" x-text="active === {{ $index }} ? '−' : '+'"></span>
                    </button>
                    <div x-show="active === {{ $index }}" 
                         x-collapse
                         x-cloak
                         class="px-6 pb-6 text-xs sm:text-sm text-[#FAF8F5]/80 font-light leading-relaxed border-t border-[#D4AF37]/10 pt-4">
                        {{ $item['a'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <p class="text-xs uppercase tracking-widest text-[#FAF8F5]/60">
                Have an inquiry requiring bespoke concierge attention?
            </p>
            <a href="{{ route('pages.contact') }}" class="inline-block mt-3 text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold border-b border-[#D4AF37]">
                Connect with the Master Concierge &rarr;
            </a>
        </div>
    </div>

    {{-- JSON-LD FAQPage Schema --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        @foreach($faqs as $index => $item)
        {
          "@type": "Question",
          "name": "{{ addslashes($item['q']) }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ addslashes($item['a']) }}"
          }
        }@if(!$loop->last),@endif
        @endforeach
      ]
    }
    </script>
</section>

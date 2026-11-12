@extends('layouts.app')

@section('title', 'VRAZIO PRIVÉ CLUB | Exclusive Atelier Membership')
@section('meta_description', 'Unlock exclusive atelier privileges, members-only warranty claims, celebratory birthday discounts, and dedicated concierge services with VRAZIO PRIVÉ CLUB.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen">

    {{-- ═══════════════════════════════════════════════════════════════════
         HERO SECTION
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="relative text-white py-16 sm:py-24 overflow-hidden border-b border-[#D4AF37]/30" style="background: linear-gradient(180deg, #0E261C 0%, #133326 50%, #0E261C 100%) !important; background-color: #0E261C !important; color: #ffffff !important;">
        {{-- Luxury Aura glow --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_30%,rgba(212,175,55,0.18),transparent_60%)] pointer-events-none"></div>

        <div class="relative max-w-5xl mx-auto px-6 text-center">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-[#D4AF37]/15 border border-[#D4AF37]/40" style="background-color: rgba(212, 175, 55, 0.2) !important; border-color: rgba(212, 175, 55, 0.5) !important; color: #D4AF37 !important; text-xs font-bold uppercase tracking-[0.25em] mb-6">
                <span>✦ ATELIER PRIVILEGE CIRCLE ✦</span>
            </div>

            <h1 class="font-heading text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6" style="color: #ffffff !important;">
                VRAZIO <span style="color: #D4AF37 !important;">PRIVÉ CLUB</span>
            </h1>

            <p class="font-light text-base sm:text-lg max-w-2xl mx-auto leading-relaxed mb-8" style="color: rgba(255, 255, 255, 0.9) !important;">
                An intimate circle of fine jewellery connoisseurs and couture patrons. Gain instant access to members-only warranty protection, celebratory birthday gifts, and exclusive order privileges.
            </p>

            {{-- 100% Digital Card Guarantee Pill --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs" style="background-color: rgba(0, 0, 0, 0.45) !important; border: 1px solid rgba(212, 175, 55, 0.4) !important; color: #FAF8F5 !important;">
                <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>100% Digital Privilege Pass:</strong> Instant card issuance on your device • No physical cards • Secure QR verification</span>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         EXISTING MEMBER BANNER (IF AUTH & HAS MEMBERSHIP)
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($userMembership)
        <section class="max-w-5xl mx-auto px-6 -mt-8 relative z-20">
            <div class="bg-white border-2 border-[#D4AF37] p-6 sm:p-8 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 text-left">
                    <div class="w-14 h-14 rounded-full bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] flex items-center justify-center shrink-0">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#D4AF37] block">Active Privilege Tier</span>
                        <h3 class="font-heading text-xl font-bold text-[#0E261C]">{{ $userMembership->tier_name }} ({{ $userMembership->member_id }})</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Valid until {{ $userMembership->expires_at->format('M d, Y') }} • Next order discount {{ $userMembership->next_order_discount_used ? 'redeemed' : 'available' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('account') }}" class="w-full md:w-auto px-6 py-2.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs font-bold uppercase tracking-wider rounded text-center hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                        View Digital Card &rarr;
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         MEMBERSHIP TIERS CARDS
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-bold">Select Your Atelier Tier</span>
            <h2 class="font-heading text-2xl sm:text-4xl font-bold text-[#0E261C] mt-2">Annual Membership Plans</h2>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-3"></div>
            <p class="text-xs sm:text-sm text-gray-600 max-w-lg mx-auto">12-month full validity from enrollment date. No recurring auto-debit.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            @foreach($plans as $plan)
                @php
                    $isFeatured = (bool) $plan->is_featured;
                    $benefits = is_array($plan->benefits) ? $plan->benefits : json_decode($plan->benefits ?? '[]', true);
                @endphp
                <div class="relative bg-white rounded-2xl p-7 flex flex-col justify-between transition-all duration-300 hover:shadow-xl {{ $isFeatured ? 'border-2 border-[#D4AF37] shadow-2xl' : 'border border-gray-200 shadow-md' }}"
                     style="{{ $isFeatured ? 'border: 2px solid #D4AF37 !important; padding-top: 2.75rem !important;' : 'padding-top: 1.75rem !important;' }}">
                    @if($isFeatured)
                        <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); -webkit-transform: translateX(-50%); z-index: 20; white-space: nowrap; background: linear-gradient(135deg, #D4AF37 0%, #F3E5AB 50%, #D4AF37 100%); color: #0E261C; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.18em; padding: 4px 16px; border-radius: 9999px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); border: 1px solid rgba(14,38,28,0.2);">
                            Most Prestigious &bull; Featured
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between mb-3" style="{{ $isFeatured ? 'margin-top: 0.75rem !important;' : '' }}">
                            <h3 class="font-heading text-xl font-bold text-[#0E261C]">{{ $plan->name }}</h3>
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $plan->validity_months }} Months</span>
                        </div>

                        <p class="text-xs text-gray-500 min-h-[32px] mb-6">{{ $plan->tagline }}</p>

                        <div class="p-4 bg-[#FAF8F5] border border-gray-100 rounded-xl mb-6">
                            <div class="flex items-baseline gap-1">
                                <span class="font-sans text-2xl sm:text-3xl font-semibold text-[#0E261C]">₹</span>
                                <span class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C]">{{ number_format((float) $plan->price, 0) }}</span>
                                <span class="text-xs text-gray-500 font-sans">/ year</span>
                            </div>
                            <span class="text-[10px] text-emerald-800 font-semibold block mt-1">✦ Instant digital issuance upon checkout</span>
                        </div>

                        {{-- Next Order & Birthday Highlights --}}
                        <div class="space-y-2 mb-6 text-xs min-h-[92px]">
                            <div class="flex items-center justify-between p-2.5 bg-emerald-50 text-emerald-900 border border-emerald-200 rounded-lg">
                                <span class="font-semibold">Next Order Privilege:</span>
                                <strong class="font-mono">{{ (float)$plan->next_order_discount_percent }}% OFF (Max ₹{{ number_format((float)$plan->next_order_max_discount) }})</strong>
                            </div>

                            @if((float)$plan->birthday_discount_percent > 0)
                                <div class="flex items-center justify-between p-2.5 bg-amber-50 text-amber-900 border border-amber-200 rounded-lg">
                                    <span class="font-semibold">Birthday Month Gift:</span>
                                    <strong class="font-mono">{{ (float)$plan->birthday_discount_percent }}% OFF (Max ₹{{ number_format((float)$plan->birthday_max_discount) }})</strong>
                                </div>
                            @else
                                <div class="flex items-center justify-between p-2.5 bg-gray-50/80 text-gray-400 border border-dashed border-gray-200 rounded-lg">
                                    <span>Birthday Month Gift:</span>
                                    <span class="italic text-[11px]">Gold &amp; Gold Max Tiers</span>
                                </div>
                            @endif
                        </div>

                        {{-- Benefits List --}}
                        <div class="border-t border-gray-100 pt-5 mb-6">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-[#D4AF37] block mb-3">Included Privileges</span>
                            <ul class="space-y-2.5 text-xs text-gray-700">
                                @foreach($benefits as $b)
                                    <li class="flex items-start gap-2.5">
                                        <svg class="w-4 h-4 text-[#D4AF37] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $b }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        @if($userMembership && $userMembership->tier === $plan->slug)
                            <button disabled class="w-full py-3 bg-gray-100 text-gray-500 font-bold uppercase text-xs tracking-wider rounded-lg cursor-not-allowed">
                                Current Active Tier
                            </button>
                        @else
                            <a href="{{ route('prive-club.checkout', $plan->slug) }}" 
                               class="w-full block text-center py-3.5 {{ $isFeatured ? 'bg-gradient-to-r from-[#0E261C] to-[#1c3f30] text-[#D4AF37] border border-[#D4AF37]' : 'bg-[#0E261C] text-white hover:bg-[#D4AF37] hover:text-[#0E261C]' }} font-bold uppercase text-xs tracking-[0.2em] rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                Join as {{ $plan->name }} &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         COMPARISON MATRIX TABLE
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="max-w-5xl mx-auto px-6 py-12">
        <div class="bg-white border border-[#D4AF37]/30 rounded-2xl overflow-hidden shadow-lg">
            <div class="p-6 bg-[#0E261C] text-white flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-heading text-xl font-bold">Privilege Comparison Matrix</h3>
                    <p class="text-xs text-gray-300 mt-1">Detailed comparison across non-member patrons and our three exclusive tiers.</p>
                </div>
                <span class="text-xs font-bold text-[#D4AF37] px-3 py-1 bg-[#D4AF37]/10 border border-[#D4AF37]/30 rounded-full">
                    Atelier Standard
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#FAF8F5] border-b border-gray-200 text-[#0E261C] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-6 font-bold">Privilege / Feature</th>
                            <th class="py-3.5 px-4 font-semibold text-gray-500">Non-Member</th>
                            <th class="py-3.5 px-4 font-bold text-emerald-800">Member (₹299)</th>
                            <th class="py-3.5 px-4 font-bold text-amber-700 bg-amber-50/50">Gold (₹499)</th>
                            <th class="py-3.5 px-4 font-bold text-gray-900">Gold Max (₹999)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-[#0E261C]">One-Time Next Order Discount</td>
                            <td class="py-3.5 px-4 text-gray-400">—</td>
                            <td class="py-3.5 px-4 font-bold text-emerald-800">5% (Max ₹100, min ₹999)</td>
                            <td class="py-3.5 px-4 font-bold text-amber-700 bg-amber-50/50">10% (Max ₹250, min ₹1,499)</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">15% (Max ₹500, min ₹1,999)</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-[#0E261C]">Birthday Month Special Gift</td>
                            <td class="py-3.5 px-4 text-gray-400">—</td>
                            <td class="py-3.5 px-4 text-gray-400">—</td>
                            <td class="py-3.5 px-4 font-bold text-amber-700 bg-amber-50/50">10% (Max ₹150, min ₹1,499)</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">15% (Max ₹300, min ₹1,999)</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-[#0E261C]">Craftsmanship Warranty Claims</td>
                            <td class="py-3.5 px-4 text-rose-600 font-semibold">Locked ✕</td>
                            <td class="py-3.5 px-4 text-emerald-700 font-semibold">Full Access ✓</td>
                            <td class="py-3.5 px-4 text-emerald-700 font-semibold bg-amber-50/50">Priority Review ✓</td>
                            <td class="py-3.5 px-4 text-emerald-700 font-bold">Master Vault Fast-Track ✓</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-[#0E261C]">Personalized Digital Membership Card</td>
                            <td class="py-3.5 px-4 text-gray-400">—</td>
                            <td class="py-3.5 px-4">Emerald Digital Pass</td>
                            <td class="py-3.5 px-4 bg-amber-50/50">Gold Foil Digital Pass</td>
                            <td class="py-3.5 px-4 font-bold">Obsidian Royal Pass</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-[#0E261C]">QR Code Ledger Verification</td>
                            <td class="py-3.5 px-4 text-gray-400">—</td>
                            <td class="py-3.5 px-4">✓ Instant</td>
                            <td class="py-3.5 px-4 bg-amber-50/50">✓ Instant</td>
                            <td class="py-3.5 px-4">✓ Instant</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-[#0E261C]">Atelier Support Channel</td>
                            <td class="py-3.5 px-4">Standard Email</td>
                            <td class="py-3.5 px-4">Member Priority Desk</td>
                            <td class="py-3.5 px-4 font-semibold bg-amber-50/50">Priority WhatsApp Concierge</td>
                            <td class="py-3.5 px-4 font-bold">Dedicated VIP Private Master</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-[#0E261C]">Transit Insurance</td>
                            <td class="py-3.5 px-4">Standard Transit</td>
                            <td class="py-3.5 px-4">Full Vault Escort</td>
                            <td class="py-3.5 px-4 bg-amber-50/50">Full Vault Escort</td>
                            <td class="py-3.5 px-4 font-bold">Armoured Priority Dispatch</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
         100% DIGITAL GUARANTEE & SUSTAINABILITY MANIFESTO
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="max-w-4xl mx-auto px-6 py-12 text-center">
        <div class="p-8 bg-gradient-to-r from-emerald-950/10 via-amber-600/10 to-emerald-950/10 border border-[#D4AF37]/40 rounded-2xl">
            <div class="w-12 h-12 mx-auto rounded-full bg-[#0E261C] text-[#D4AF37] flex items-center justify-center mb-4 shadow">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="font-heading text-xl sm:text-2xl font-bold text-[#0E261C] mb-2">100% Digital Atelier Pass</h3>
            <p class="text-xs sm:text-sm text-gray-700 leading-relaxed max-w-xl mx-auto mb-4">
                To uphold environmental stewardship and prevent physical card loss or counterfeit duplicates, all VRAZIO PRIVÉ CLUB memberships are strictly 100% digital. Your credential is permanently linked to your customer account and verifiable anytime via its unique QR ledger token.
            </p>
            <span class="text-[11px] uppercase tracking-widest text-[#D4AF37] font-bold">Instant Activation &bull; Zero Printing &bull; Zero Waste</span>
        </div>
    </section>

</div>
@endsection

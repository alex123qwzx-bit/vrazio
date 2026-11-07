@extends('layouts.app')

@section('title', 'Vault Exchange & Return Policy')
@section('meta_description', 'Official VRAZIO 15-day vault inspection, return, and complimentary exchange charter for fine jewellery pieces.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="['Legal & Governance' => '', 'Return & Exchange Policy' => '']" />

        <div class="bg-white border border-[#D4AF37]/20 p-8 sm:p-12 shadow-sm prose max-w-none text-xs sm:text-sm text-[#0E261C]/80 leading-relaxed font-light">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold block mb-2">Patron Assurance</span>
            <h1 class="font-display text-3xl font-bold text-[#0E261C] mb-6">15-Day Vault Exchange &amp; Return Charter</h1>
            
            <p>
                We wish every VRAZIO piece to bring unreserved joy to its patron. If an acquired creation does not meet your discerning expectations, we extend a 15-day complimentary exchange privilege.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">1. Eligibility Criteria &amp; Hygiene Exclusions</h3>
            <ul>
                <li>The item must be returned within 15 calendar days from the date of OTP-verified delivery.</li>
                <li>The piece must remain unworn, unaltered, and undamaged, with the unbroken tamper-evident VRAZIO security seal intact.</li>
                <li>Original certification dossiers (BIS Hallmarking certificate, GIA/IGI laboratory cards) and bespoke packaging must accompany the piece.</li>
                <li><strong>Pierced Jewellery &amp; Earrings Hygiene Exclusion:</strong> For sanitary and health protection regulations under Indian standards, earrings and ear-cuffs cannot be returned or exchanged once the sealed transparent sterile tamper blister has been broken, unless an assay metal discrepancy is verified.</li>
                <li><strong>Custom Monograms &amp; Inscriptions:</strong> Any item customized with client-requested laser monogramming, custom ring sizing beyond our standard atelier range, or bespoke gemstone settings is strictly final sale, but retains full eligibility for lifetime craftsmanship repairs and cleaning.</li>
            </ul>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">2. Bespoke &amp; Custom Commissions</h3>
            <p>
                Pieces commissioned with bespoke gemstone sourcing, personalized laser inscriptions, or custom sizing under 48 or over 62 are non-refundable, but are covered under our Lifetime Craftsmanship Guarantee and complimentary resizing service.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">3. Reverse Armoured Collection</h3>
            <p>
                To return a piece, contact the Master Concierge. We will arrange a secure armoured pickup from your address with sealed tamper-evident evidence envelopes. Patrons are not required to bear transit costs for returns within India.
            </p>
        </div>
    </div>
</div>
@endsection

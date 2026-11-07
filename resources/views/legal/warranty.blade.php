@extends('layouts.app')

@section('title', 'Lifetime Craftsmanship & Hallmarking Warranty')
@section('meta_description', 'Learn about VRAZIO Lifetime Craftsmanship Warranty: complimentary prong inspection, professional ultrasonic cleaning, and BIS hallmark authenticity.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="['Legal & Governance' => '', 'Lifetime Warranty' => '']" />

        <div class="bg-white border border-[#D4AF37]/20 p-8 sm:p-12 shadow-sm prose max-w-none text-xs sm:text-sm text-[#0E261C]/80 leading-relaxed font-light">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold block mb-2">Perpetual Guarantee</span>
            <h1 class="font-display text-3xl font-bold text-[#0E261C] mb-6">Lifetime Craftsmanship Warranty</h1>
            
            <p>
                Every jewel forged in the VRAZIO Atelier carries a perpetual commitment to excellence. We stand unreservedly behind the structural integrity and gemological veracity of our creations.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">1. Scope of Coverage</h3>
            <p>
                Our Lifetime Warranty covers any defects in metallurgy, prong integrity, structural clasp mechanisms, and stone setting craftsmanship that may arise during normal ownership.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">2. Complimentary Annual Spa &amp; Inspection</h3>
            <p>
                Patrons are entitled to lifetime complimentary ultrasonic steam cleaning, rhodium re-plating (for white gold creations), and microscopic prong tight-check at our private workshop once every 12 months.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">3. Hallmarking Assaying Guarantee</h3>
            <p>
                The Bureau of Indian Standards (BIS) karatage stamp engraved on your piece is backed by our permanent metallurgical indemnity. Should any piece fall short of its stated gold purity upon laboratory assay, VRAZIO will refund 100% of the purchase value plus a 10% atelier compensatory settlement.
            </p>
        </div>
    </div>
</div>
@endsection

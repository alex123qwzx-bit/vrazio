@extends('layouts.app')

@section('title', 'Atelier Heritage & Master Goldsmithing')
@section('meta_description', 'Discover the heritage of VRAZIO: three generations of master goldsmithing, ethically sourced GIA certified diamonds, and BIS hallmarked fine jewellery.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-6">
        <x-breadcrumbs :links="['Atelier' => '', 'Heritage & Provenance' => '']" />

        <div class="text-center max-w-2xl mx-auto my-10">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">The House of VRAZIO</span>
            <h1 class="font-display text-4xl sm:text-5xl font-bold text-[#0E261C] mt-2">A Three-Generation Legacy of Haute Joaillerie</h1>
            <div class="w-20 h-px bg-[#D4AF37] mx-auto my-5"></div>
            <p class="text-sm text-[#0E261C]/70 font-light leading-relaxed">
                Sculpting precious metals and rare gemstones into transcendent expressions of beauty, integrity, and royal lineage.
            </p>
        </div>

        <div class="space-y-12 text-xs sm:text-sm text-[#0E261C]/80 leading-relaxed font-light">
            <div class="border border-[#D4AF37]/30 bg-white p-8 sm:p-12 shadow-sm">
                <h2 class="font-display text-2xl font-bold text-[#0E261C] mb-4">Our Philosophy</h2>
                <p class="mb-4">
                    Founded on the uncompromising pursuit of perfection, VRAZIO represents the zenith of Indian fine jewellery craftsmanship fused with European classical proportion. We believe true luxury lies not in mass replication, but in the slow, meticulous alchemy of the master craftsman’s hands.
                </p>
                <p>
                    Every commission that leaves our atelier is numbered, hallmarked, and inscribed. We use exclusively certified BIS hallmarked gold and responsibly sourced conflict-free diamonds under the Kimberley Process.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="bg-[#0E261C] text-[#FAF8F5] p-8 border border-[#D4AF37]/30">
                    <span class="font-display text-3xl font-bold text-[#D4AF37] block mb-2">100%</span>
                    <span class="text-xs uppercase tracking-widest font-semibold text-[#D4AF37]">BIS Assayed</span>
                    <p class="text-[11px] text-[#FAF8F5]/70 mt-2">Every gram of gold is rigorously tested and stamped with official Bureau of Indian Standards HUID codes.</p>
                </div>
                <div class="bg-[#0E261C] text-[#FAF8F5] p-8 border border-[#D4AF37]/30">
                    <span class="font-display text-3xl font-bold text-[#D4AF37] block mb-2">Triple Ex</span>
                    <span class="text-xs uppercase tracking-widest font-semibold text-[#D4AF37]">Diamond Cuts</span>
                    <p class="text-[11px] text-[#FAF8F5]/70 mt-2">Certified Excellent Cut, Polish, and Symmetry for unmatched fire, brilliance, and scintillation.</p>
                </div>
                <div class="bg-[#0E261C] text-[#FAF8F5] p-8 border border-[#D4AF37]/30">
                    <span class="font-display text-3xl font-bold text-[#D4AF37] block mb-2">Private</span>
                    <span class="text-xs uppercase tracking-widest font-semibold text-[#D4AF37]">Vault Transit</span>
                    <p class="text-[11px] text-[#FAF8F5]/70 mt-2">Armoured carrier with two-factor patron verification and full door-to-door insurance coverage.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

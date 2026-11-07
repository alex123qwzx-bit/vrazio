@extends('layouts.app')

@section('title', 'Armoured Transit & Delivery Protocol')
@section('meta_description', 'Discover how VRAZIO ensures 100% insured, armoured doorstep transit across India with two-factor OTP verification handover.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="['Legal & Governance' => '', 'Armoured Transit Policy' => '']" />

        <div class="bg-white border border-[#D4AF37]/20 p-8 sm:p-12 shadow-sm prose max-w-none text-xs sm:text-sm text-[#0E261C]/80 leading-relaxed font-light">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold block mb-2">Security Governance</span>
            <h1 class="font-display text-3xl font-bold text-[#0E261C] mb-6">Armoured Transit &amp; Insurance Protocol</h1>
            
            <p>
                At VRAZIO, the safeguarding of your commissioned jewellery begins long before handover. Every parcel dispatched from our Master Atelier is treated as a high-security sovereign transport consignment.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">1. Complimentary Armoured Doorstep Transit</h3>
            <p>
                We provide 100% complimentary armoured transit on all standard orders across the territory of India. Consignments are conveyed in tamper-evident, dual-key sealed security pods via specialized logistics escorts (Sequel Logistics, Brink’s, or BVC Logistics).
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">2. 100% Comprehensive Transit Insurance</h3>
            <p>
                From the instant an order leaves our private vault until physical OTP-authenticated delivery to the registered patron, the consignment is insured for 100% of its declared value by Lloyd’s syndicate underwriters. In the exceedingly improbable event of loss or damage in transit, the patron is indemnified in full or issued an identical remade piece.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">3. Patron OTP Verification Handover</h3>
            <p>
                For security reasons, transit couriers will not leave packages unattended or with third parties. Handover requires the patron to present a government-issued photo ID matching the order dossier and furnish the dynamic SMS delivery OTP provided on the day of consignment arrival.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">4. Delivery Timelines</h3>
            <ul>
                <li><strong>Metro Regions (Mumbai, Delhi NCR, Bengaluru, Chennai, Kolkata, Hyderabad):</strong> 3 to 5 business days following final hallmarking.</li>
                <li><strong>Tier II &amp; Tier III Indian Cities:</strong> 5 to 7 business days.</li>
                <li><strong>Priority Insured Express Transit:</strong> 48 to 72 hours via dedicated regional courier routing.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Terms of Commission & Atelier Service')
@section('meta_description', 'Official VRAZIO Terms of Commission and Atelier Service, outlining precious metal assaying, order execution, and client agreements.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="['Legal & Governance' => '', 'Terms of Service' => '']" />

        <div class="bg-white border border-[#D4AF37]/20 p-8 sm:p-12 shadow-sm prose max-w-none text-xs sm:text-sm text-[#0E261C]/80 leading-relaxed font-light">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold block mb-2">Legal Framework</span>
            <h1 class="font-display text-3xl font-bold text-[#0E261C] mb-6">Terms of Commission &amp; Atelier Engagement</h1>
            
            <p>
                By commissioning a jewel, placing an order, or utilizing the VRAZIO digital atelier, patrons accept these terms and conditions.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">1. Bullion Pricing &amp; Live Gold Rate Market Volatility</h3>
            <p>
                Due to real-time market fluctuations in international gold, platinum, and diamond spot rates, prices are locked strictly upon successful authorization of the transaction via Razorpay. While in the shopping bag prior to checkout, prices reflect real-time bullion benchmarks. In the rare circumstance of a technical computation anomaly or exchange rate outage, VRAZIO reserves the right to review the commission and remit an immediate 100% full refund to the originating payment source.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">2. Government BIS Hallmarking &amp; HUID Registration</h3>
            <p>
                All jewellery pieces conform strictly to the Hallmarking Regulations of the Bureau of Indian Standards (BIS). Every precious metal creation carries a laser-engraved 6-digit alphanumeric HUID (Hallmark Unique Identification) code verifiable via the BIS Care portal.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">3. Armoured Transit, Package Weight Verification &amp; Delivery Seals</h3>
            <p>
                All high jewellery consignments travel via dedicated, insured armoured courier services with tamper-evident security holograms. The recipient patron must inspect the outer security docket, confirm that the tamper-evident seal is intact, and verify the courier airway bill (AWB) tare weight before providing the delivery OTP. To safeguard against transit substitution or weight disputes, patrons are required to record a continuous unboxing video from sealed package to gem extraction.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] mt-8 mb-3">4. High-Value Disputes &amp; Mandatory Arbitration</h3>
            <p>
                These terms are governed in all respects by the laws of the Republic of India. Any controversy, claim, or dispute arising out of high jewellery transactions exceeding ₹50,000 shall be resolved through binding expedited arbitration under the Indian Arbitration and Conciliation Act, 1996, conducted in the English language with a sole arbitrator seated in Mumbai, Maharashtra.
            </p>
        </div>
    </div>
</div>
@endsection

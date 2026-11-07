@extends('layouts.app')

@section('title', 'Privacy & Cookie Policy')
@section('meta_description', 'Official VRAZIO Privacy and Cookie Charter. Learn how we safeguard patron personal data, payment encryption, and cookie protocols under Indian IT Act and data protection laws.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="['Legal & Governance' => '', 'Privacy & Cookie Charter' => '']" />

        <div class="bg-white border border-[#D4AF37]/20 p-8 sm:p-12 shadow-sm text-xs sm:text-sm text-[#0E261C]/80 leading-relaxed font-light space-y-6">
            <div>
                <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold block mb-2">Data Protection</span>
                <h1 class="font-display text-3xl font-bold text-[#0E261C]">Patron Privacy &amp; Cookie Charter</h1>
                <p class="text-xs text-gray-500 mt-1">Effective Date: October 2026 &bull; Certified Compliant</p>
            </div>
            
            <p>
                At VRAZIO, the confidentiality of our clientele is paramount. We safeguard patron identities, transaction values, and delivery destinations with enterprise-grade cryptographic security protocols.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] pt-4 border-t border-gray-100">1. Information Collected</h3>
            <p>
                We collect personal identifiers solely necessary to fulfill high jewellery commissions, complete mandatory government BIS hallmarking and tax invoicing compliance (under Indian PMLA guidelines for high-value precious goods), and coordinate insured transit.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] pt-4 border-t border-gray-100">2. Third-Party Data Processors &amp; DPDP Act 2023 Compliance</h3>
            <p>
                In compliance with the Digital Personal Data Protection (DPDP) Act 2023 and the Information Technology Act 2000, we disclose all authorized third-party data fiduciaries and processors utilized in the operation of VRAZIO:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-xs">
                <li><strong>Fast2SMS (SMS Gateway):</strong> Patron mobile numbers are transmitted to Fast2SMS solely for dispatching time-sensitive one-time passwords (OTPs) and armoured delivery PINs. Numbers are not marketed or shared further.</li>
                <li><strong>Razorpay Software Private Limited:</strong> Credit card credentials, net banking access, and UPI transactions are processed directly within Razorpay's PCI-DSS Level 1 certified vault. VRAZIO never stores payment secrets.</li>
                <li><strong>Google Firebase Authentication &amp; Google Identity:</strong> Used for secure patron federated identity and cryptographic SMS verification tokens.</li>
                <li><strong>Google Analytics 4 (GA4):</strong> Aggregated, consent-gated telemetry for site performance, loaded only upon explicit cookie protocol acceptance.</li>
                <li><strong>Resend Inc.:</strong> High-reputation transactional mail server utilized to dispatch account verification tokens, PDF tax invoices, and transit status notices.</li>
            </ul>

            <h3 class="font-display text-lg font-bold text-[#0E261C] pt-4 border-t border-gray-100">3. Payment Gateway Escrow Discretion</h3>
            <p>
                Credit card credentials, net banking access, and UPI secrets are processed directly within Razorpay's PCI-DSS Level 1 certified environment. VRAZIO never stores patron credit card or banking secrets on its servers.
            </p>

            <h3 id="cookies" class="font-display text-lg font-bold text-[#0E261C] pt-4 border-t border-gray-100">4. Cookie &amp; Tracking Protocol</h3>
            <p>
                To provide an effortless atelier experience, VRAZIO utilizes first-party cookies and localized browser storage:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-xs">
                <li><strong>Strictly Necessary Cookies:</strong> Used for keeping your shopping bag persisted across pages, authenticating your patron login session, and preventing Cross-Site Request Forgery (CSRF).</li>
                <li><strong>Security &amp; reCAPTCHA:</strong> Utilized by Google to safeguard payment gateways and SMS dispatch against automated bot spam.</li>
                <li><strong>Analytics (Google Analytics GA4):</strong> Anonymous aggregated traffic statistics to measure page performance and site speed without identifying individual patrons.</li>
            </ul>
            <p class="text-xs text-gray-600">
                You can manage or disable cookies at any time through your browser preferences. Note that disabling essential cookies may impact shopping bag functionality and checkout operations.
            </p>

            <h3 class="font-display text-lg font-bold text-[#0E261C] pt-4 border-t border-gray-100">5. Patron Rights &amp; Concierge Contact</h3>
            <p>
                Patrons may at any time request an export or complete deletion of their personal dossier from our records by contacting our Data Protection Officer at <a href="mailto:vrazioofficial@gmail.com" class="text-emerald-800 underline font-semibold">vrazioofficial@gmail.com</a> or via WhatsApp at <strong>+91 89796 22310</strong>.
            </p>
        </div>
    </div>
</div>
@endsection

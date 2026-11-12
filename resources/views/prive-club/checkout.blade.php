@extends('layouts.app')

@section('title', 'Join VRAZIO PRIVÉ CLUB | ' . $plan->name)

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12 md:py-16">
    <div class="max-w-2xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'PRIVÉ CLUB' => route('prive-club.index'),
            'Enroll in ' . $plan->name => ''
        ]" />

        <div class="text-center my-8">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-bold">Atelier Privilege Enrollment</span>
            <h1 class="font-heading text-3xl sm:text-4xl font-bold text-[#0E261C] mt-2">
                Join as {{ $plan->name }}
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-2">100% digital membership with immediate activation upon payment.</p>
        </div>

        {{-- Order Summary Card --}}
        <div class="bg-white border-2 border-[#D4AF37] rounded-2xl p-6 sm:p-8 shadow-xl mb-8">
            <div class="flex items-start justify-between border-b border-gray-100 pb-5 mb-5">
                <div>
                    <span class="text-[10px] uppercase font-bold text-[#D4AF37] tracking-widest block">Selected Plan</span>
                    <h2 class="font-heading text-2xl font-bold text-[#0E261C]">{{ $plan->name }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $plan->tagline }}</p>
                </div>
                <div class="text-right">
                    <span class="font-display text-2xl font-bold text-[#0E261C]">{{ $plan->formatted_price }}</span>
                    <span class="text-[10px] text-gray-400 block">/ 12 months</span>
                </div>
            </div>

            {{-- Privilege breakdown --}}
            <div class="space-y-3 text-xs text-gray-700 mb-6">
                <div class="flex justify-between py-1 border-b border-gray-100">
                    <span>One-Time Next Order Privilege:</span>
                    <strong class="font-mono text-emerald-700">{{ (float) $plan->next_order_discount_percent }}% OFF (Max ₹{{ number_format((float)$plan->next_order_max_discount) }})</strong>
                </div>
                @if((float) $plan->birthday_discount_percent > 0)
                <div class="flex justify-between py-1 border-b border-gray-100">
                    <span>Birthday Month Celebration:</span>
                    <strong class="font-mono text-amber-700">{{ (float) $plan->birthday_discount_percent }}% OFF (Max ₹{{ number_format((float)$plan->birthday_max_discount) }})</strong>
                </div>
                @endif
                <div class="flex justify-between py-1 border-b border-gray-100">
                    <span>Warranty Claim Access:</span>
                    <strong class="text-emerald-700">Full Members-Only Coverage</strong>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-100">
                    <span>Credential Format:</span>
                    <strong class="text-gray-900">100% Digital Card with QR Ledger</strong>
                </div>
                <div class="flex justify-between pt-2 text-sm font-bold text-[#0E261C]">
                    <span>Total Membership Fee:</span>
                    <span class="font-display text-xl text-[#0E261C]">{{ $plan->formatted_price }}</span>
                </div>
            </div>

            @if(str_starts_with((string)$razorpayKey, 'rzp_test_'))
                <div class="mb-5 p-3.5 bg-emerald-50/80 border border-emerald-300 rounded-xl text-xs text-emerald-950 shadow-sm">
                    <div class="flex items-center gap-2 font-bold text-[11px] uppercase tracking-wider text-emerald-900 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        <span>Razorpay Test Sandbox Active</span>
                    </div>
                    <p class="text-[11px] text-emerald-800 leading-snug">
                        Use Indian Test Visa: <strong class="font-mono bg-white px-1.5 py-0.5 rounded border border-emerald-300 text-emerald-900">4100 2800 0000 1007</strong> (or RuPay: <strong class="font-mono bg-white px-1.5 py-0.5 rounded border border-emerald-300 text-emerald-900">6527 6589 0000 1005</strong>) &bull; Exp: 12/28 &bull; CVV: 123
                    </p>
                    <p class="text-[11px] text-emerald-800 mt-1">
                        Or instant UPI: <strong class="font-mono bg-white px-1.5 py-0.5 rounded border border-emerald-300 text-emerald-900">success@razorpay</strong>
                    </p>
                </div>
            @endif

            @if(!empty($razorpayError))
                <div class="p-4 mb-6 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>Razorpay Gateway Notice</span>
                    </div>
                    <p class="font-mono text-[11px] leading-relaxed break-all">{{ $razorpayError }}</p>
                    @if(config('app.debug'))
                        <p class="mt-2 text-[10px] text-gray-600 italic">Debug mode active: Direct test enrollment will be used if payment order was not generated.</p>
                    @endif
                </div>
            @endif

            {{-- Enrollment Form --}}
            <form id="priveEnrollmentForm" action="{{ route('prive-club.enroll') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id" value="">
                <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="{{ $razorpayOrderId }}">
                <input type="hidden" name="razorpay_signature" id="razorpay_signature" value="">

                <button type="button" id="payButton"
                        class="w-full py-4 bg-gradient-to-r from-[#0E261C] via-[#1a4433] to-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs font-bold uppercase tracking-[0.2em] rounded-lg shadow-xl hover:shadow-2xl hover:scale-[1.01] transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Activate {{ $plan->name }} &bull; {{ $plan->formatted_price }}</span>
                </button>
            </form>

            <p class="text-[11px] text-center text-gray-500 mt-4 leading-relaxed">
                By activating membership, you agree to the VRAZIO PRIVÉ CLUB Terms. Your digital card will be issued immediately in your account dashboard.
            </p>
        </div>
    </div>
</div>

{{-- Razorpay Checkout script --}}
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('payButton').addEventListener('click', function(e) {
        e.preventDefault();
        const razorpayKey = "{{ $razorpayKey }}";
        const orderId = "{{ $razorpayOrderId }}";
        const form = document.getElementById('priveEnrollmentForm');

        if (!razorpayKey || razorpayKey === '' || !orderId || orderId === '') {
            alert('Payment gateway order initialization is pending. Please reload the page or contact our atelier concierge.');
            return;
        }

        const options = {
            key: razorpayKey,
            amount: {{ (int) ($plan->price * 100) }},
            currency: "INR",
            name: "VRAZIO Haute Joaillerie",
            description: "VRAZIO PRIVÉ CLUB - {{ $plan->name }} (12 Months)",
            image: "{{ asset('images/favicon-32x32.png') }}",
            order_id: orderId,
            handler: function (response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                form.submit();
            },
            prefill: {
                name: "{{ $user->name }}",
                email: "{{ $user->email }}",
                contact: "{{ $user->phone }}"
            },
            theme: {
                color: "#0E261C"
            }
        };

        try {
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (resp) {
                console.error('Razorpay payment failed:', resp);
                let reason = resp.error ? (resp.error.description || resp.error.reason || 'Payment failed') : 'Payment failed';
                if (reason.toLowerCase().includes('international')) {
                    reason += "\n\n💡 Quick Fix: Use Indian Domestic Test Card: 4100 2800 0000 1007 (CVV: 123) or UPI (success@razorpay). Global cards (like 4111...) require International Payments enabled in your Razorpay Dashboard.";
                }
                alert("Payment declined or interrupted:\n" + reason);
            });
            rzp.open();
        } catch(err) {
            console.error('Razorpay modal open error:', err);
            alert('Unable to launch secure payment modal. Please try again.');
        }
    });
</script>
@endsection

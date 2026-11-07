@extends('layouts.app')

@section('title', 'Secure Atelier Checkout')
@section('meta_description', 'Complete your bespoke fine jewellery commission with encrypted 256-bit Razorpay escrow, armoured transit escort, and BIS hallmark warranty.')

@push('head')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-10" 
     x-data="{
         step: 1,
         shippingMethod: 'standard',
         phone: '{{ $verifiedPhone ?? '' }}',
         otpVerified: {{ !empty($verifiedPhone) ? 'true' : 'false' }},
         otpModalOpen: false,
         otpCode: '',
         otpSending: false,
         otpVerifying: false,
         otpCountdown: 0,
         otpCooldownInterval: null,
         errorMessage: '',
         successMessage: '',

         // Form Data
         clientName: '',
         clientEmail: '',
         clientPhone: '{{ $verifiedPhone ?? '' }}',
         street: '',
         city: '',
         state: 'Maharashtra',
         pincode: '',

         startOtpFlow() {
             this.errorMessage = '';
             this.successMessage = '';

             const phoneRegex = /^[6-9]\d{9}$/;
             if (!phoneRegex.test(this.clientPhone)) {
                 this.errorMessage = 'Please enter a valid 10-digit Indian mobile number to receive OTP.';
                 return;
             }

             this.otpModalOpen = true;
             this.sendOtp();
         },

         async sendOtp() {
             this.otpSending = true;
             this.errorMessage = '';
             try {
                 const res = await fetch('/api/checkout/otp/send', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({ phone: this.clientPhone })
                 });
                 const data = await res.json();
                 if (data.success) {
                     this.successMessage = data.message;
                     if (data.dev_otp) {
                         this.successMessage += ' [Dev Simulator OTP: ' + data.dev_otp + ']';
                         this.otpCode = data.dev_otp;
                     }
                     this.startCountdown(30);
                 } else {
                     this.errorMessage = data.message || 'Unable to dispatch OTP. Please retry.';
                 }
             } catch (err) {
                 this.errorMessage = 'Network protocol failure. Please retry.';
             }
             this.otpSending = false;
         },

         startCountdown(sec) {
             this.otpCountdown = sec;
             if (this.otpCooldownInterval) clearInterval(this.otpCooldownInterval);
             this.otpCooldownInterval = setInterval(() => {
                 this.otpCountdown--;
                 if (this.otpCountdown <= 0) {
                     clearInterval(this.otpCooldownInterval);
                     this.otpCooldownInterval = null;
                 }
             }, 1000);
         },

         async verifyOtp() {
             this.otpVerifying = true;
             this.errorMessage = '';
             try {
                 const res = await fetch('/api/checkout/otp/verify', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({ phone: this.clientPhone, otp: this.otpCode })
                 });
                 const data = await res.json();
                 if (data.success) {
                     this.otpVerified = true;
                     this.otpModalOpen = false;
                     this.successMessage = 'Mobile successfully validated. Proceed to Payment.';
                     this.step = 3;
                 } else {
                     this.errorMessage = data.message || 'Invalid or expired OTP authentication cipher.';
                 }
             } catch (err) {
                 this.errorMessage = 'Network verification failure. Please retry.';
             }
             this.otpVerifying = false;
         },

         async initializePayment() {
             this.errorMessage = '';
             try {
                 const payload = {
                     client_name: this.clientName,
                     client_email: this.clientEmail,
                     client_phone: this.clientPhone,
                     shipping_address: {
                         street: this.street,
                         city: this.city,
                         state: this.state,
                         pincode: this.pincode
                     },
                     shipping_method: this.shippingMethod
                 };

                 const res = await fetch('{{ route('checkout.process') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify(payload)
                 });

                 const data = await res.json();

                 if (!res.ok || !data.success) {
                     this.errorMessage = data.message || 'Order initialization failed.';
                     return;
                 }

                 // Launch Razorpay Modal
                 const options = {
                     key: data.key,
                     amount: data.amount,
                     currency: data.currency,
                     name: 'VRAZIO Atelier',
                     description: 'Haute Joaillerie Commission ' + data.order_number,
                     image: '{{ asset('images/vrazio-card-icon.png') }}',
                     order_id: data.razorpay_order_id,
                     prefill: {
                         name: data.client_name,
                         email: data.client_email,
                         contact: data.client_phone
                     },
                     theme: {
                         color: '#0E261C'
                     },
                     handler: async (response) => {
                         // Callback to server verification
                         const verifyRes = await fetch('{{ route('checkout.verify-payment') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json'
                             },
                             body: JSON.stringify({
                                 razorpay_order_id: response.razorpay_order_id,
                                 razorpay_payment_id: response.razorpay_payment_id,
                                 razorpay_signature: response.razorpay_signature
                             })
                         });
                         const verifyData = await verifyRes.json();
                         if (verifyData.success && verifyData.redirect_url) {
                             window.location.href = verifyData.redirect_url;
                         } else {
                             alert('Payment verification issue: ' + (verifyData.message || 'Please contact concierge.'));
                         }
                     }
                 };

                 const rzp = new Razorpay(options);
                 rzp.on('payment.failed', function (response){
                     alert('Payment declined: ' + response.error.description);
                 });
                 rzp.open();

             } catch (err) {
                 this.errorMessage = 'Checkout network error. Please contact atelier concierge.';
             }
         }
     }">

    <div class="max-w-7xl mx-auto px-6">
        <x-breadcrumbs :links="['Bespoke Bag' => route('cart.index'), 'Escrow Checkout' => '']" />

        {{-- Checkout Header --}}
        <div class="text-center max-w-xl mx-auto my-8">
            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">Consignment Security</span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-2">Atelier Escrow Checkout</h1>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
        </div>

        {{-- Step Navigation Indicator --}}
        <div class="max-w-3xl mx-auto mb-12">
            <div class="flex items-center justify-between text-xs uppercase tracking-wider font-semibold">
                <div :class="step >= 1 ? 'text-[#0E261C] border-[#D4AF37]' : 'text-gray-400 border-gray-200'" class="flex items-center gap-2 pb-2 border-b-2 flex-1 text-center justify-center">
                    <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">1</span>
                    <span>Patron Dossier</span>
                </div>
                <div :class="step >= 2 ? 'text-[#0E261C] border-[#D4AF37]' : 'text-gray-400 border-gray-200'" class="flex items-center gap-2 pb-2 border-b-2 flex-1 text-center justify-center">
                    <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">2</span>
                    <span>Logistics &amp; OTP</span>
                </div>
                <div :class="step >= 3 ? 'text-[#0E261C] border-[#D4AF37]' : 'text-gray-400 border-gray-200'" class="flex items-center gap-2 pb-2 border-b-2 flex-1 text-center justify-center">
                    <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">3</span>
                    <span>Razorpay Settlement</span>
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        <div x-show="errorMessage" x-cloak class="max-w-3xl mx-auto mb-6 p-4 bg-red-50 border border-red-200 text-red-800 text-xs">
            <span x-text="errorMessage"></span>
        </div>
        <div x-show="successMessage" x-cloak class="max-w-3xl mx-auto mb-6 p-4 bg-green-50 border border-green-200 text-green-800 text-xs">
            <span x-text="successMessage"></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            {{-- Left Main: Checkout Forms --}}
            <div class="lg:col-span-8 bg-white border border-[#D4AF37]/20 p-6 sm:p-10 shadow-sm">
                {{-- STEP 1: Patron & Address --}}
                <div x-show="step === 1" class="space-y-6">
                    <h2 class="font-display text-xl font-bold text-[#0E261C] pb-3 border-b border-gray-100">
                        1. Patron Consignment Address
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-600 mb-1 font-semibold">Patron Full Name *</label>
                            <input type="text" x-model="clientName" required placeholder="e.g. Maharani Gayatri Devi" 
                                   class="w-full text-xs px-4 py-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-600 mb-1 font-semibold">Email for Transit Certificate *</label>
                            <input type="email" x-model="clientEmail" required placeholder="patron@domain.com" 
                                   class="w-full text-xs px-4 py-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-600 mb-1 font-semibold">Indian Mobile Number (Mandatory SMS OTP) *</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 bg-gray-50 border border-r-0 border-gray-200 text-xs text-gray-500 font-mono">+91</span>
                            <input type="tel" x-model="clientPhone" maxlength="10" placeholder="9876543210" 
                                   class="w-full text-xs px-4 py-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none font-mono">
                        </div>
                        <span class="text-[10px] text-gray-500 mt-1 block">A 6-digit cryptographic verification OTP will be sent to this number.</span>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-600 mb-1 font-semibold">Street &amp; Residence Suite *</label>
                        <input type="text" x-model="street" placeholder="Flat / House / Suite, Street address" 
                               class="w-full text-xs px-4 py-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-600 mb-1 font-semibold">City *</label>
                            <input type="text" x-model="city" placeholder="e.g. Mumbai" 
                                   class="w-full text-xs px-4 py-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-600 mb-1 font-semibold">State *</label>
                            <select x-model="state" class="w-full text-xs px-3 py-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none bg-white">
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Delhi">Delhi NCR</option>
                                <option value="Karnataka">Karnataka</option>
                                <option value="Tamil Nadu">Tamil Nadu</option>
                                <option value="Gujarat">Gujarat</option>
                                <option value="Rajasthan">Rajasthan</option>
                                <option value="Telangana">Telangana</option>
                                <option value="West Bengal">West Bengal</option>
                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                <option value="Other">Other Territory</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-600 mb-1 font-semibold">PIN Code *</label>
                            <input type="text" x-model="pincode" maxlength="6" placeholder="400001" 
                                   class="w-full text-xs px-4 py-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none font-mono">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="button" 
                                @click="
                                    if(!clientName || !clientEmail || !clientPhone || !street || !city || !pincode) {
                                        errorMessage = 'Please complete all dossier fields before continuing.';
                                        return;
                                    }
                                    errorMessage = '';
                                    step = 2;
                                "
                                class="w-full py-4 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-[0.25em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                            Proceed to Logistics &amp; Security Validation &rarr;
                        </button>
                    </div>
                </div>

                {{-- STEP 2: Logistics & OTP Verification --}}
                <div x-show="step === 2" x-cloak class="space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h2 class="font-display text-xl font-bold text-[#0E261C]">
                            2. Armoured Transit &amp; Mobile OTP Authentication
                        </h2>
                        <button type="button" @click="step = 1" class="text-[11px] text-gray-500 hover:text-[#0E261C] uppercase tracking-wider">
                            &larr; Edit Address
                        </button>
                    </div>

                    {{-- Logistics Selection --}}
                    <div class="space-y-3">
                        <label class="block text-xs uppercase tracking-wider text-gray-600 font-semibold mb-2">Select Transit Protocol</label>

                        <label class="flex items-start gap-4 p-4 border cursor-pointer transition-all"
                               :class="shippingMethod === 'standard' ? 'border-[#D4AF37] bg-[#FAF8F5]' : 'border-gray-200'">
                            <input type="radio" name="shippingMethod" value="standard" x-model="shippingMethod" class="mt-1 text-[#0E261C] focus:ring-[#D4AF37]">
                            <div class="flex-grow">
                                <div class="flex justify-between items-baseline">
                                    <span class="font-display font-bold text-sm text-[#0E261C]">Complimentary Armoured Doorstep Transit</span>
                                    <span class="text-xs font-semibold text-green-700 uppercase tracking-wider">FREE</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">Sealed vault transit with armed escort. Estimated 5-7 business days across India.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-4 p-4 border cursor-pointer transition-all"
                               :class="shippingMethod === 'express' ? 'border-[#D4AF37] bg-[#FAF8F5]' : 'border-gray-200'">
                            <input type="radio" name="shippingMethod" value="express" x-model="shippingMethod" class="mt-1 text-[#0E261C] focus:ring-[#D4AF37]">
                            <div class="flex-grow">
                                <div class="flex justify-between items-baseline">
                                    <span class="font-display font-bold text-sm text-[#0E261C]">Priority Insured Express Transit</span>
                                    <span class="text-xs font-semibold font-mono text-[#0E261C]">₹999</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">Direct chartered armoured courier. 2-3 business days guaranteed delivery with photo ID signoff.</p>
                            </div>
                        </label>
                    </div>

                    {{-- Mobile OTP Verification Box --}}
                    <div class="p-6 bg-[#0E261C] text-[#FAF8F5] border border-[#D4AF37]/30 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">
                                Mandatory Two-Factor Consignment Shield
                            </span>
                            <template x-if="otpVerified">
                                <span class="bg-green-700/80 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1">
                                    ✓ Authenticated
                                </span>
                            </template>
                        </div>

                        <p class="text-xs text-[#FAF8F5]/80 font-light leading-relaxed mb-4">
                            To ensure high-value vault provenance, an SMS OTP will be dispatched to patron mobile 
                            <strong class="font-mono text-[#D4AF37]" x-text="'+91 ' + clientPhone"></strong>.
                        </p>

                        <div x-show="!otpVerified">
                            <button type="button" 
                                    @click="startOtpFlow()" 
                                    class="px-8 py-3.5 bg-[#9B1B30] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all">
                                Request SMS OTP Cipher
                            </button>
                        </div>

                        <div x-show="otpVerified" class="text-xs text-green-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Phone Verified. You are cleared to proceed with payment.</span>
                        </div>
                    </div>

                    <div class="pt-4 flex gap-4">
                        <button type="button" @click="step = 1" class="px-6 py-4 border border-gray-300 text-xs uppercase tracking-wider font-semibold text-gray-700 hover:bg-gray-100">
                            Back
                        </button>
                        <button type="button" 
                                :disabled="!otpVerified"
                                @click="step = 3"
                                :class="otpVerified ? 'bg-[#0E261C] text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C]' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                class="flex-grow py-4 text-xs uppercase tracking-[0.25em] font-semibold transition-all">
                            Continue to Razorpay Settlement &rarr;
                        </button>
                    </div>
                </div>

                {{-- STEP 3: Razorpay Payment Confirmation --}}
                <div x-show="step === 3" x-cloak class="space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h2 class="font-display text-xl font-bold text-[#0E261C]">
                            3. Final Payment Settlement
                        </h2>
                        <button type="button" @click="step = 2" class="text-[11px] text-gray-500 hover:text-[#0E261C] uppercase tracking-wider">
                            &larr; Change Transit Tier
                        </button>
                    </div>

                    {{-- Review Recap --}}
                    <div class="bg-[#FAF8F5] border border-gray-200 p-6 space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500 uppercase tracking-wider">Patron:</span>
                            <span class="font-bold text-[#0E261C]" x-text="clientName"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 uppercase tracking-wider">Dispatch Address:</span>
                            <span class="font-semibold text-[#0E261C]" x-text="street + ', ' + city + ', ' + state + ' ' + pincode"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 uppercase tracking-wider">Authenticated Mobile:</span>
                            <span class="font-mono font-bold text-[#0E261C]" x-text="'+91 ' + clientPhone"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 uppercase tracking-wider">Transit Tier:</span>
                            <span class="font-semibold text-[#0E261C]" x-text="shippingMethod === 'standard' ? 'Complimentary Armoured Doorstep Transit' : 'Priority Insured Express'"></span>
                        </div>
                    </div>

                    <div class="p-4 border border-[#D4AF37]/30 bg-white">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-[#0E261C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <div>
                                <h3 class="font-display font-bold text-sm text-[#0E261C]">256-Bit Escrow Gateway via Razorpay</h3>
                                <p class="text-[11px] text-gray-500">Supports all major Credit Cards, Net Banking, UPI, and verified Bank Wire.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="button" 
                                @click="initializePayment()" 
                                class="w-full py-4 bg-[#9B1B30] text-white text-xs uppercase tracking-[0.25em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all duration-300 shadow-2xl">
                            Authorize &amp; Commission Order via Razorpay
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Column: Cart Breakdown Summary --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white border border-[#D4AF37]/20 p-6 sm:p-8 shadow-sm">
                    <h3 class="font-display text-base font-bold text-[#0E261C] pb-4 border-b border-gray-100">
                        Order Dossier ({{ count($items) }} Pieces)
                    </h3>

                    <ul role="list" class="divide-y divide-gray-100 my-4 max-h-72 overflow-y-auto pr-2">
                        @foreach($items as $item)
                            <li class="py-3 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $item['image'] ?? 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=100&q=80' }}" 
                                         alt="{{ $item['title'] }}" class="w-12 h-12 object-cover border border-gray-200">
                                    <div>
                                        <h4 class="font-semibold text-[#0E261C] line-clamp-1">{{ $item['title'] }}</h4>
                                        <span class="text-gray-400 font-mono text-[10px]">Qty: {{ $item['quantity'] }}</span>
                                    </div>
                                </div>
                                <span class="font-mono font-bold text-[#0E261C]">₹{{ number_format((float) ($item['price'] * $item['quantity']), 0) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="space-y-2.5 text-xs text-[#0E261C]/80 pt-4 border-t border-gray-100">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-mono font-semibold">₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>GST (3%):</span>
                            <span class="font-mono font-semibold">₹{{ number_format($gst, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Armoured Transit:</span>
                            <span class="font-mono font-semibold text-green-700" 
                                  x-text="shippingMethod === 'standard' ? 'Complimentary (₹0)' : '₹999.00'"></span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 flex justify-between font-bold text-sm text-[#0E261C]">
                            <span>Final Investment:</span>
                            <span class="font-display text-lg text-[#0E261C]" 
                                  x-text="shippingMethod === 'standard' ? '₹{{ number_format($standardTotal, 2) }}' : '₹{{ number_format($expressTotal, 2) }}'"></span>
                        </div>
                    </div>
                </div>

                {{-- Guarantee Card --}}
                <div class="p-6 bg-[#0E261C] text-[#FAF8F5] border border-[#D4AF37]/30 text-center space-y-3">
                    <span class="block text-xs uppercase tracking-[0.2em] text-[#D4AF37] font-semibold">VRAZIO Atelier Seal</span>
                    <p class="text-[11px] text-[#FAF8F5]/80 font-light leading-relaxed">
                        Every purchase includes physical BIS hallmark certificate and individual gemological appraisal docket.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- OTP Authentication Modal --}}
    <div x-show="otpModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="otpModalOpen = false" 
             class="bg-[#0E261C] border border-[#D4AF37] p-8 max-w-md w-full text-[#FAF8F5] shadow-2xl relative">
            <button type="button" @click="otpModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-white">&times;</button>

            <div class="text-center mb-6">
                <span class="w-2 h-2 rotate-45 bg-[#D4AF37] inline-block mb-2"></span>
                <h3 class="font-display text-2xl font-bold text-[#D4AF37]">Patron Verification</h3>
                <p class="text-xs text-[#FAF8F5]/70 mt-1">
                    Enter the 6-digit authentication cipher dispatched via SMS to:
                </p>
                <p class="font-mono text-sm text-[#D4AF37] font-bold mt-1" x-text="'+91 ' + clientPhone"></p>
            </div>

            <div class="space-y-4">
                <div>
                    <input type="text" 
                           x-model="otpCode" 
                           maxlength="6" 
                           placeholder="&bull; &bull; &bull; &bull; &bull; &bull;" 
                           class="w-full text-center font-mono text-2xl tracking-[0.5em] py-3 bg-[#0A1C15] border border-[#D4AF37]/40 text-[#FAF8F5] focus:border-[#D4AF37] focus:outline-none">
                </div>

                <button type="button" 
                        @click="verifyOtp()" 
                        :disabled="otpVerifying || otpCode.length !== 6" 
                        class="w-full py-3.5 bg-[#9B1B30] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all disabled:opacity-50">
                    <span x-show="!otpVerifying">Authenticate Cipher</span>
                    <span x-show="otpVerifying">Verifying Provenance...</span>
                </button>

                <div class="text-center text-xs text-gray-400">
                    <template x-if="otpCountdown > 0">
                        <span>Resend available in <strong class="font-mono text-[#D4AF37]" x-text="otpCountdown"></strong>s</span>
                    </template>
                    <template x-if="otpCountdown <= 0">
                        <button type="button" @click="sendOtp()" class="text-[#D4AF37] hover:underline uppercase text-[10px] tracking-wider">
                            Resend SMS OTP
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

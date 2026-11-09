@extends('layouts.app')

@section('title', 'Contact Us | VRAZIO')
@section('meta_description', 'Get in touch with VRAZIO for fine jewellery inquiries, orders, custom designs, and customer support.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="['Contact Us' => '']" />

        <div class="text-center max-w-xl mx-auto my-10">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">Get In Touch</span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-2">Contact Us</h1>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
            <p class="text-xs sm:text-sm text-gray-600 font-normal">
                Have questions about our jewellery, custom designs, an existing order, or warranty? We are here to help you anytime.
            </p>
        </div>

        {{-- Direct WhatsApp Banner --}}
        <div class="mb-10 bg-emerald-50 border border-emerald-300 p-5 rounded-lg shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 text-center sm:text-left">
                <div class="w-12 h-12 rounded-full bg-[#25D366] text-white flex items-center justify-center shrink-0 shadow">
                    <svg width="24" height="24" class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.761.821 2.796.821h.005c3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.773-5.768zm7.135 5.769c-.002 3.935-3.201 7.134-7.136 7.134-1.257 0-2.434-.332-3.468-.944l-4.562 1.196 1.217-4.444c-.672-1.076-1.027-2.327-1.026-3.61.002-3.935 3.202-7.135 7.137-7.135 3.935 0 7.138 3.2 7.138 7.133z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-950">Chat Directly on WhatsApp</h3>
                    <p class="text-xs text-emerald-800">Message us at <strong>+91 87912 27210</strong> for instant answers, custom orders, or photos.</p>
                </div>
            </div>
            <a href="https://wa.me/918791227210?text=Hi%20VRAZIO%2C%20I%20have%20an%20inquiry" 
               target="_blank" 
               rel="noopener noreferrer"
               class="px-5 py-2.5 bg-[#25D366] hover:bg-[#1ebd5b] text-white text-xs font-bold uppercase tracking-wider rounded shadow transition-all whitespace-nowrap flex items-center gap-2">
                <span>Start WhatsApp Chat</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white border border-[#D4AF37]/20 p-8 sm:p-12 shadow-sm">
            {{-- Contact Information --}}
            <div class="space-y-6">
                <div>
                    <span class="text-xs uppercase tracking-widest text-[#D4AF37] font-bold block mb-1">WhatsApp &amp; Voice Concierge</span>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="https://wa.me/918791227210" target="_blank" class="text-emerald-700 hover:underline flex items-center gap-1.5 font-mono text-sm font-semibold">
                            <span>WhatsApp: +91 87912 27210</span>
                        </a>
                        <span class="text-gray-300">|</span>
                        <a href="tel:+918791227210" class="text-[#0E261C] hover:text-[#D4AF37] underline font-mono text-xs font-semibold">
                            Direct Voice: +91 87912 27210
                        </a>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Available Mon &ndash; Sat, 10:00 AM &ndash; 8:00 PM IST</p>
                </div>

                <div>
                    <span class="text-xs uppercase tracking-widest text-[#D4AF37] font-bold block mb-1">Email Us</span>
                    <p class="text-xs sm:text-sm text-[#0E261C] font-mono">
                        <a href="mailto:vrazioofficial@gmail.com" class="hover:text-[#D4AF37] transition-colors underline">
                            vrazioofficial@gmail.com
                        </a>
                    </p>
                    <p class="text-[11px] text-gray-500 mt-0.5">We typically respond within 2 to 4 business hours.</p>
                </div>

                <div>
                    <span class="text-xs uppercase tracking-widest text-[#D4AF37] font-bold block mb-1">Follow Our Social Media</span>
                    <div class="flex items-center gap-4 mt-2">
                        <a href="https://www.instagram.com/vrazio.official/" target="_blank" rel="noopener noreferrer" 
                           class="flex items-center gap-1.5 text-xs text-pink-700 font-semibold hover:underline">
                            <span>Instagram (@vrazio.official)</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-4 mt-2">
                        <a href="https://www.facebook.com/share/1CJBJZbnkG/" target="_blank" rel="noopener noreferrer" 
                           class="flex items-center gap-1.5 text-xs text-blue-700 font-semibold hover:underline">
                            <span>Facebook Page</span>
                        </a>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <span class="text-xs uppercase tracking-widest text-[#0E261C] font-bold block mb-1">Office Location</span>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        VRAZIO Fine Jewellery &amp; Atelier<br>
                        Connaught Place, New Delhi &bull; India 110001
                    </p>
                </div>
            </div>

            {{-- Inquiry Form with Luxury Alpine.js Thank You State --}}
            <div x-data="{
                    submitted: false,
                    senderName: '',
                    handleSubmit() {
                        this.submitted = true;
                    }
                 }">
                <div x-show="submitted" x-cloak class="p-8 bg-emerald-50 border border-emerald-300 text-center space-y-4 rounded-sm">
                    <div class="w-14 h-14 rounded-full bg-[#0E261C] border border-[#D4AF37] flex items-center justify-center mx-auto text-[#D4AF37]">
                        <svg width="28" height="28" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-bold block">Inquiry Dispatched</span>
                    <h3 class="font-display text-2xl font-bold text-[#0E261C]">Thank You, <span x-text="senderName || 'Valued Patron'"></span></h3>
                    <p class="text-xs sm:text-sm text-gray-700 leading-relaxed max-w-md mx-auto">
                        Your bespoke inquiry has been received by our Atelier Concierge. A gemological specialist will contact you via email or WhatsApp within 2–4 business hours.
                    </p>
                    <div class="pt-2">
                        <button type="button" @click="submitted = false; senderName = ''" class="text-xs text-[#0E261C] underline font-semibold hover:text-[#D4AF37]">
                            Submit another inquiry &rarr;
                        </button>
                    </div>
                </div>

                <form x-show="!submitted" @submit.prevent="handleSubmit()" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-700 mb-1 font-semibold">Your Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="senderName" required placeholder="Full Name" 
                               class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-700 mb-1 font-semibold">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" required placeholder="yourname@gmail.com" 
                               class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-700 mb-1 font-semibold">Phone Number</label>
                        <input type="tel" placeholder="+91 87912 27210" 
                               class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none font-mono rounded-xs">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-700 mb-1 font-semibold">Subject / Question Type</label>
                        <select class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none bg-white rounded-xs">
                            <option>Product Information &amp; Price</option>
                            <option>Custom Jewellery &amp; Design</option>
                            <option>Order Status / Delivery</option>
                            <option>Warranty &amp; Service</option>
                            <option>Other Questions</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-700 mb-1 font-semibold">Message <span class="text-red-500">*</span></label>
                        <textarea rows="4" required placeholder="Tell us how we can help you..." 
                                  class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs leading-relaxed"></textarea>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-md">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

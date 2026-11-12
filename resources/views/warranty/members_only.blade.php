@extends('layouts.app')

@section('title', 'Exclusive Privé Privilege | Warranty Claims')

@section('content')
<div class="bg-[#FAF8F5] min-h-[75vh] py-16 flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-6 text-center">
        {{-- Luxury Shield Lock Emblem --}}
        <div class="w-20 h-20 mx-auto rounded-full bg-[#0E261C] border-2 border-[#D4AF37] flex items-center justify-center shadow-xl mb-6">
            <svg class="w-10 h-10 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-[#0E261C] text-[#D4AF37] text-xs font-bold uppercase tracking-widest mb-4 border border-[#D4AF37]/50">
            <span>✦ VRAZIO PRIVÉ CLUB EXCLUSIVE ✦</span>
        </div>

        <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mb-4">
            Members-Only Warranty Service
        </h1>

        <p class="text-sm sm:text-base text-gray-700 leading-relaxed mb-6">
            Warranty claim access is an exclusive privilege reserved for active <strong>VRAZIO PRIVÉ CLUB</strong> members. Join our membership programme to activate comprehensive craftsmanship warranty protection, complimentary vault cleaning, prong inspection, and priority support.
        </p>

        @if(isset($order))
            <div class="p-4 bg-white border border-[#D4AF37]/30 rounded-lg max-w-md mx-auto mb-8 text-xs text-left shadow-xs">
                <div class="flex justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Order Reference:</span>
                    <strong class="font-mono text-[#0E261C]">#{{ $order->order_number }}</strong>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Delivered On:</span>
                    <span>{{ $order->delivered_at ? $order->delivered_at->format('M d, Y') : 'Delivered' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Warranty Status:</span>
                    <span class="text-amber-800 font-bold">Privé Membership Required</span>
                </div>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ url('/prive-club') }}" 
               class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-[#0E261C] to-[#1c3f30] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase font-bold tracking-[0.2em] rounded shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all">
                Join VRAZIO PRIVÉ CLUB &rarr;
            </a>
            <a href="{{ route('orders.index') }}" 
               class="w-full sm:w-auto px-6 py-3.5 bg-white text-gray-700 border border-gray-300 text-xs uppercase font-semibold tracking-wider rounded hover:bg-gray-50 transition-colors">
                Return to My Orders
            </a>
        </div>
    </div>
</div>
@endsection

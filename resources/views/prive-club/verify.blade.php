@extends('layouts.app')

@section('title', 'Verify Membership | ' . $memberId)
@section('meta_description', 'Official VRAZIO Atelier Ledger QR verification for PRIVÉ CLUB credential ' . $memberId)

@section('content')
<div class="bg-[#FAF8F5] min-h-[80vh] py-16 flex items-center justify-center">
    <div class="max-w-md w-full mx-auto px-6">
        @if($membership && $isValid)
            <div class="bg-white border-2 border-[#D4AF37] rounded-3xl p-8 shadow-2xl text-center relative overflow-hidden">
                {{-- Decorative watermarks --}}
                <div class="absolute -top-12 -right-12 w-36 h-36 rounded-full bg-[#D4AF37]/10 blur-xl pointer-events-none"></div>

                {{-- Verified Checkmark --}}
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-800 border-2 border-emerald-500 flex items-center justify-center mb-4 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#D4AF37] block mb-1">
                    VRAZIO Ledger Verified
                </span>

                <h1 class="font-heading text-2xl font-bold text-[#0E261C] mb-1">
                    Authentic Privé Pass
                </h1>

                <div class="my-5 p-4 bg-[#FAF8F5] border border-[#D4AF37]/30 rounded-xl space-y-2.5 text-xs text-left">
                    <div class="flex justify-between py-1 border-b border-gray-200">
                        <span class="text-gray-500">Member ID:</span>
                        <strong class="font-mono text-[#0E261C] text-sm">{{ $membership->member_id }}</strong>
                    </div>

                    <div class="flex justify-between py-1 border-b border-gray-200">
                        <span class="text-gray-500">Privilege Tier:</span>
                        <span class="font-bold text-[#0E261C] uppercase tracking-wider">{{ $membership->tier_name }}</span>
                    </div>

                    <div class="flex justify-between py-1 border-b border-gray-200">
                        <span class="text-gray-500">Status:</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-widest">Active &bull; Valid</span>
                    </div>

                    <div class="flex justify-between py-1 border-b border-gray-200">
                        <span class="text-gray-500">Member Since:</span>
                        <span class="font-medium text-gray-700">{{ $membership->starts_at->format('M Y') }}</span>
                    </div>

                    <div class="flex justify-between py-1">
                        <span class="text-gray-500">Valid Until:</span>
                        <span class="font-bold text-[#0E261C] font-mono">{{ $membership->expires_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <div class="text-[11px] text-gray-500 leading-relaxed mb-6">
                    This digital credential has been verified against the official VRAZIO Haute Joaillerie ledger. No physical card is required.
                </div>

                <a href="{{ route('home') }}" class="inline-block px-6 py-2.5 bg-[#0E261C] text-[#D4AF37] text-xs font-bold uppercase tracking-widest rounded hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors">
                    Explore Collections &rarr;
                </a>
            </div>
        @else
            <div class="bg-white border-2 border-rose-300 rounded-3xl p-8 shadow-2xl text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-rose-100 text-rose-700 border-2 border-rose-400 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-rose-600 block mb-1">
                    Verification Notice
                </span>

                <h1 class="font-heading text-2xl font-bold text-[#0E261C] mb-2">
                    Invalid or Expired Pass
                </h1>

                <p class="text-xs text-gray-600 mb-6 leading-relaxed">
                    Member ID <strong class="font-mono text-gray-800">{{ $memberId }}</strong> could not be validated or the membership term has expired.
                </p>

                <a href="{{ route('prive-club.index') }}" class="inline-block px-6 py-2.5 bg-[#0E261C] text-[#D4AF37] text-xs font-bold uppercase tracking-widest rounded hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors">
                    Join VRAZIO PRIVÉ CLUB &rarr;
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

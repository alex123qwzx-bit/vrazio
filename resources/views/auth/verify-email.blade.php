@extends('layouts.app')

@section('title', 'Verify Your Email Address | VRAZIO')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-16">
    <div class="max-w-lg mx-auto px-6">
        <x-breadcrumbs :links="['Verify Email' => '']" />

        <div class="bg-white border border-[#D4AF37]/30 p-8 sm:p-10 shadow-lg rounded-lg mt-8 text-center space-y-6">
            {{-- Icon --}}
            <div class="w-16 h-16 bg-amber-50 border border-[#D4AF37] text-[#D4AF37] rounded-full mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <div>
                <span class="text-[11px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold block mb-1">
                    Email Confirmation
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-bold text-[#0E261C]">
                    Verify Your Email Address
                </h1>
            </div>

            {{-- Notifications --}}
            @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-xs sm:text-sm text-left">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border-l-4 border-red-600 text-red-800 text-xs sm:text-sm text-left">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                Thank you for creating an account with <strong>VRAZIO</strong>! We have sent a verification link to your email:
            </p>

            <div class="p-3 bg-gray-50 border border-gray-200 rounded font-mono text-xs sm:text-sm text-[#0E261C] font-semibold">
                {{ auth()->user()?->email ?? 'your registered email' }}
            </div>

            <p class="text-xs text-gray-500 leading-relaxed">
                Please check your inbox (and spam folder) and click the confirmation link to activate your account.
            </p>

            {{-- Actions --}}
            <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <form action="{{ route('verification.send') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-3 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-wider transition-all">
                        Resend Verification Email
                    </button>
                </form>

                <div class="flex items-center gap-4 text-xs">
                    <a href="{{ route('account') }}" class="text-[#0E261C] underline font-medium hover:text-[#D4AF37]">
                        Go to Account
                    </a>
                    <span class="text-gray-300">|</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-red-700 underline font-medium hover:text-red-900">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>

            {{-- WhatsApp Note --}}
            <div class="pt-4 border-t border-gray-100 text-xs text-gray-500">
                Having trouble receiving the email? 
                <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20need%20help%20verifying%20my%20account%20email%20{{ auth()->user()?->email ?? '' }}" 
                   target="_blank" 
                   class="text-emerald-700 font-semibold hover:underline">
                    WhatsApp us at +91 89796 22310
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

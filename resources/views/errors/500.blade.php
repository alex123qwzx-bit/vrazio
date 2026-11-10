@extends('layouts.app')

@section('title', 'We Made a Mistake | 500')
@section('meta_description', 'We made a mistake. Our master technicians are attending to it. Your patron information and order history remain completely secure.')

@section('content')
<div class="bg-[#FAF8F5] min-h-[75vh] flex items-center justify-center py-20 px-6">
    <div class="max-w-xl mx-auto text-center">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-[#0E261C] border-2 border-[#D4AF37] mb-8 shadow-xl">
            <span class="font-display text-3xl font-bold text-[#D4AF37]">500</span>
        </div>

        <span class="block text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold mb-2">
            We Made a Mistake
        </span>

        <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mb-4">
            Something Went Wrong on Our End
        </h1>

        <p class="text-sm text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
            {{ $message ?? 'We apologize for the inconvenience. An unexpected error occurred and our engineering team has been automatically alerted. Rest assured, your account, orders, and cart remain safely protected.' }}
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('home') }}" 
               class="w-full sm:w-auto px-8 py-4 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.25em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-lg">
                Return to Grand Foyer
            </a>
            <a href="https://wa.me/918791227210?text=Hello%20VRAZIO%20Concierge,%20I%20encountered%20an%20issue%20on%20the%20website" 
               target="_blank"
               class="w-full sm:w-auto px-8 py-4 bg-emerald-700 text-white text-xs uppercase tracking-[0.25em] font-semibold hover:bg-emerald-800 transition-all shadow-sm">
                WhatsApp Concierge
            </a>
        </div>
    </div>
</div>
@endsection

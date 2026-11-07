@extends('layouts.app')

@section('title', 'Piece Not Found | 404')
@section('meta_description', 'The bespoke jewellery creation or atelier page you seek is no longer within our active collection vault.')

@section('content')
<div class="bg-[#FAF8F5] min-h-[75vh] flex items-center justify-center py-20 px-6">
    <div class="max-w-xl mx-auto text-center">
        {{-- Luxury Monogram Icon --}}
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-[#0E261C] border-2 border-[#D4AF37] mb-8 shadow-xl">
            <span class="font-display text-3xl font-bold text-[#D4AF37]">404</span>
        </div>

        <span class="block text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold mb-2">
            Archival Record Unavailable
        </span>

        <h1 class="font-display text-4xl sm:text-5xl font-bold text-[#0E261C] mb-4">
            Bespoke Creation Not Found
        </h1>

        <p class="text-sm text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
            The page or rare gemstone creation you are seeking may have been retired to the atelier archives, or the link has changed.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('home') }}" 
               class="w-full sm:w-auto px-8 py-4 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.25em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-lg">
                Return to Atelier
            </a>
            <a href="{{ route('products.index') }}" 
               class="w-full sm:w-auto px-8 py-4 bg-white text-[#0E261C] border border-gray-300 text-xs uppercase tracking-[0.25em] font-semibold hover:border-[#D4AF37] transition-all shadow-sm">
                Explore Haute Joaillerie
            </a>
        </div>

        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-xs text-gray-500">
                Require bespoke concierge assistance? 
                <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20hit%20a%20missing%20page%20and%20need%20help" 
                   target="_blank" 
                   class="text-emerald-700 font-semibold underline hover:text-emerald-900">
                    Connect with our Private Concierge
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

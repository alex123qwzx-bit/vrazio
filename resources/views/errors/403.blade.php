@extends('layouts.app')

@section('title', 'Restricted Vault Access | 403')
@section('meta_description', 'This private atelier chamber is reserved for authorized patron credentials only.')

@section('content')
<div class="bg-[#FAF8F5] min-h-[75vh] flex items-center justify-center py-20 px-6">
    <div class="max-w-xl mx-auto text-center">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-[#0E261C] border-2 border-[#D4AF37] mb-8 shadow-xl">
            <span class="font-display text-3xl font-bold text-[#D4AF37]">403</span>
        </div>

        <span class="block text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold mb-2">
            Restricted Vault
        </span>

        <h1 class="font-display text-4xl sm:text-5xl font-bold text-[#0E261C] mb-4">
            Authorized Patrons Only
        </h1>

        <p class="text-sm text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
            You do not possess the clearance required to view this confidential atelier dossier.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('login') }}" 
               class="w-full sm:w-auto px-8 py-4 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.25em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-lg">
                Patron Sign-In
            </a>
            <a href="{{ route('home') }}" 
               class="w-full sm:w-auto px-8 py-4 bg-white text-[#0E261C] border border-gray-300 text-xs uppercase tracking-[0.25em] font-semibold hover:border-[#D4AF37] transition-all shadow-sm">
                Return to Foyer
            </a>
        </div>
    </div>
</div>
@endsection

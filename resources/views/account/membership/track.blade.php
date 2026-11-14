@extends('layouts.app')

@section('title', 'Docket ' . $request->request_number . ' | VRAZIO PRIVÉ CLUB')
@section('meta_description', 'Track your VRAZIO PRIVÉ CLUB membership request status and view concierge updates.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-10 md:py-14">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <x-breadcrumbs :links="[
            'Home' => route('home'),
            'My Account' => route('account'),
            'Manage Membership' => route('account.membership.manage'),
            $request->request_number => ''
        ]" />

        <div class="my-8 bg-white border border-[#D4AF37]/40 rounded-2xl p-6 sm:p-10 shadow-md">
            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-6 mb-6">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-[0.25em] text-[#D4AF37]">
                        Membership Request Tracking
                    </span>
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-[#0E261C] mt-1">
                        Docket #{{ $request->request_number }}
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Submitted on {{ $request->created_at->format('d M Y, h:i A') }} • Last updated {{ $request->updated_at->format('d M Y, h:i A') }}
                    </p>
                </div>
                <div>
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ $request->status === 'approved' || $request->status === 'completed' || $request->status === 'refunded' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($request->status === 'rejected' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                        {{ $request->status_label }}
                    </span>
                </div>
            </div>

            {{-- Summary Details Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-[#FAF8F5] rounded-xl border border-gray-100 text-xs mb-8">
                <div>
                    <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Request Type</span>
                    <span class="font-bold text-[#0E261C] uppercase">{{ $request->type }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Associated Plan</span>
                    <span class="font-bold text-[#0E261C]">{{ $membership?->tier_name ?? $request->currentPlan?->name ?? 'VRAZIO Privé' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Patron Name</span>
                    <span class="font-bold text-[#0E261C]">{{ $request->user->name }}</span>
                </div>
            </div>

            {{-- Reason & Notes --}}
            <div class="space-y-4 text-xs mb-8">
                <div>
                    <h3 class="font-bold uppercase tracking-wider text-[11px] text-gray-500">Reason Stated</h3>
                    <p class="text-gray-800 font-medium mt-1">{{ $request->reason }}</p>
                </div>

                @if($request->customer_notes)
                    <div>
                        <h3 class="font-bold uppercase tracking-wider text-[11px] text-gray-500">Patron Comments</h3>
                        <p class="text-gray-700 bg-gray-50 p-3 rounded border border-gray-100 mt-1 leading-relaxed">{{ $request->customer_notes }}</p>
                    </div>
                @endif

                @if($request->is_refund_requested)
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded text-xs text-amber-900 space-y-1">
                        <span class="block font-bold">Refund Request Review:</span>
                        <p class="text-[11px] text-amber-800">
                            Refund Status: <strong>{{ $request->refund_status_label ?? 'Pending Review' }}</strong>
                            @if((float) $request->refund_amount > 0)
                                • Reviewed Amount: ₹{{ number_format((float) $request->refund_amount, 2) }}
                            @endif
                            @if($request->refund_transaction_id)
                                • Transaction Ref: <code class="font-mono">{{ $request->refund_transaction_id }}</code>
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            {{-- Concierge Response Box --}}
            <div class="p-5 bg-gradient-to-r from-[#FAF8F5] to-white border border-[#D4AF37]/50 rounded-xl mb-8">
                <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-[#D4AF37] block mb-1">
                    Atelier Concierge Response
                </span>
                @if($request->admin_response)
                    <p class="text-xs text-[#0E261C] font-serif italic text-sm leading-relaxed">
                        “{{ $request->admin_response }}”
                    </p>
                @else
                    <p class="text-xs text-gray-500 italic">
                        Your request is actively under concierge docket evaluation. Updates will appear here and via official communications.
                    </p>
                @endif
            </div>

            {{-- WhatsApp Contact & Navigation --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('account.membership.manage') }}" class="text-xs text-gray-600 hover:text-gray-900 font-semibold uppercase tracking-wider">
                    &larr; Return to Manage Membership
                </a>

                <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#25D366] text-white hover:bg-[#1EBE5D] text-xs font-bold uppercase tracking-wider rounded-xs transition-colors shadow-xs">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.102-.115.434-.506.549-.68.116-.173.232-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                    Contact Support on WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

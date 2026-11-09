@extends('layouts.app')

@section('title', 'Warranty Claim ' . $claim->claim_number . ' | VRAZIO Atelier')
@section('meta_description', 'Track and inspect the live status of your VRAZIO master craftsmanship warranty claim.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12 md:py-16" 
     x-data="{
         lightboxOpen: false,
         activeImage: '',
         currentStatus: '{{ $claim->status }}',
         currentStatusLabel: '{{ $claim->status_label }}',
         currentStatusColor: '{{ $claim->status_color }}',
         isChecked: {{ $claim->is_checked ? 'true' : 'false' }},
         adminNote: {{ json_encode($claim->admin_note) }},
         openLightbox(src) {
             this.activeImage = src;
             this.lightboxOpen = true;
         },
         pollStatus() {
             fetch('{{ route('api.warranty.check', $order->order_number) }}')
                 .then(res => res.json())
                 .then(data => {
                     if (data.success && data.has_claim) {
                         this.currentStatus = data.status;
                         this.currentStatusLabel = data.status_label;
                         this.currentStatusColor = data.status_color;
                         this.isChecked = data.is_checked;
                         if (data.admin_note !== this.adminNote) {
                             this.adminNote = data.admin_note;
                         }
                     }
                 })
                 .catch(() => {});
         },
         init() {
             // Poll status every 15 seconds to reflect instant admin changes
             setInterval(() => this.pollStatus(), 15000);
         }
     }">

    <div class="max-w-4xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'My Orders' => route('orders.index'),
            'Order #' . $order->order_number => route('order.track', $order->order_number),
            'Claim ' . $claim->claim_number => ''
        ]" />

        {{-- Greeting Message Box (from VRAZIO) --}}
        <div class="mt-6 mb-8 bg-[#0E261C] text-[#FAF8F5] p-6 sm:p-8 border border-[#D4AF37]/40 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-[#D4AF37]/5 rounded-full pointer-events-none"></div>

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-[#D4AF37]/20 border border-[#D4AF37] text-[#D4AF37] rounded-full shrink-0 flex items-center justify-center font-bold text-xl">
                    ✓
                </div>
                <div class="space-y-2 flex-1">
                    <span class="text-[10px] uppercase font-mono tracking-widest text-[#D4AF37]">
                        Warranty Claim Received
                    </span>
                    <h2 class="font-display text-xl sm:text-2xl font-bold text-[#FAF8F5]">
                        Thank you, {{ $claim->customer_name }}!
                    </h2>
                    <p class="text-xs sm:text-sm text-[#FAF8F5]/85 font-light leading-relaxed">
                        You have successfully claimed your warranty with <strong class="text-white font-semibold">VRAZIO</strong>. Our team will review your defect details within 24&ndash;48 hours and get back to you with the next steps.
                    </p>

                    <div class="pt-2">
                        <a href="https://wa.me/918791227210?text=Hi%20VRAZIO%2C%20following%20up%20on%20my%20warranty%20claim%20{{ $claim->claim_number }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-[#25D366] hover:bg-[#1ebd5b] text-white text-xs font-bold rounded shadow transition-all">
                            <svg width="16" height="16" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.761.821 2.796.821h.005c3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.773-5.768zm7.135 5.769c-.002 3.935-3.201 7.134-7.136 7.134-1.257 0-2.434-.332-3.468-.944l-4.562 1.196 1.217-4.444c-.672-1.076-1.027-2.327-1.026-3.61.002-3.935 3.202-7.135 7.137-7.135 3.935 0 7.138 3.2 7.138 7.133z"/></svg>
                            <span>Questions? Chat with us on WhatsApp (+91 87912 27210)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Claim Dossier Card --}}
        <div class="bg-white border border-[#D4AF37]/30 shadow-xl overflow-hidden mb-12">
            {{-- Header Bar with Checked Live Badge --}}
            <div class="p-6 bg-[#FAF8F5] border-b border-[#D4AF37]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase font-mono tracking-wider text-gray-500">Claim Docket:</span>
                        <span class="font-mono font-bold text-sm text-[#0E261C]">{{ $claim->claim_number }}</span>
                    </div>
                    <span class="text-[11px] text-gray-400">Filed on {{ $claim->created_at->format('F d, Y — h:i A') }}</span>
                </div>

                {{-- Live Status Badge --}}
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-900 border border-green-300 text-xs font-bold uppercase tracking-wider rounded-sm">
                        <svg width="14" height="14" class="w-3.5 h-3.5 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Warranty Claimed ✔</span>
                    </span>

                    {{-- Live Status Pill --}}
                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-sm shadow-sm"
                          :class="{
                              'bg-gray-100 text-gray-800 border border-gray-300': currentStatus === 'submitted',
                              'bg-amber-100 text-amber-900 border border-amber-300': currentStatus === 'under_review',
                              'bg-emerald-100 text-emerald-900 border border-emerald-300': currentStatus === 'approved',
                              'bg-red-100 text-red-900 border border-red-300': currentStatus === 'rejected',
                              'bg-blue-100 text-blue-900 border border-blue-300': currentStatus === 'resolved'
                          }"
                          x-text="currentStatusLabel">
                    </span>
                </div>
            </div>

            {{-- Status Progress Timeline --}}
            <div class="p-6 sm:p-8 border-b border-gray-100 bg-white">
                <span class="text-[10px] uppercase font-bold tracking-widest text-[#D4AF37] block mb-4">
                    Claim Resolution Lifecycle
                </span>

                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    {{-- Step 1: Submitted --}}
                    <div class="space-y-1">
                        <div class="h-1.5 w-full bg-[#0E261C]"></div>
                        <span class="font-bold text-[#0E261C] block text-[11px]">1. Submitted</span>
                        <span class="text-[10px] text-gray-400">Vault Registered</span>
                    </div>

                    {{-- Step 2: Under Review --}}
                    <div class="space-y-1">
                        <div class="h-1.5 w-full transition-colors"
                             :class="['under_review', 'approved', 'rejected', 'resolved'].includes(currentStatus) ? 'bg-[#0E261C]' : 'bg-gray-200'"></div>
                        <span class="font-bold block text-[11px]"
                              :class="['under_review', 'approved', 'rejected', 'resolved'].includes(currentStatus) ? 'text-[#0E261C]' : 'text-gray-400'">
                            2. Review
                        </span>
                        <span class="text-[10px] text-gray-400">Master Karigar</span>
                    </div>

                    {{-- Step 3: Decision (Approved / Rejected) --}}
                    <div class="space-y-1">
                        <div class="h-1.5 w-full transition-colors"
                             :class="{
                                 'bg-emerald-600': currentStatus === 'approved' || currentStatus === 'resolved',
                                 'bg-red-600': currentStatus === 'rejected',
                                 'bg-gray-200': ['submitted', 'under_review'].includes(currentStatus)
                             }"></div>
                        <span class="font-bold block text-[11px]"
                              :class="{
                                  'text-emerald-700': currentStatus === 'approved' || currentStatus === 'resolved',
                                  'text-red-700': currentStatus === 'rejected',
                                  'text-gray-400': ['submitted', 'under_review'].includes(currentStatus)
                              }">
                            3. Decision
                        </span>
                        <span class="text-[10px] text-gray-400" 
                              x-text="currentStatus === 'rejected' ? 'Claim Rejected' : (currentStatus === 'approved' ? 'Approved' : 'Evaluation')"></span>
                    </div>

                    {{-- Step 4: Resolved --}}
                    <div class="space-y-1">
                        <div class="h-1.5 w-full transition-colors"
                             :class="currentStatus === 'resolved' ? 'bg-[#0E261C]' : 'bg-gray-200'"></div>
                        <span class="font-bold block text-[11px]"
                              :class="currentStatus === 'resolved' ? 'text-[#0E261C]' : 'text-gray-400'">
                            4. Resolved
                        </span>
                        <span class="text-[10px] text-gray-400">Escort Dispatched</span>
                    </div>
                </div>
            </div>

            {{-- Live Admin Atelier Note (if provided) --}}
            <div x-show="adminNote" x-cloak class="p-6 bg-[#0E261C]/5 border-b border-[#D4AF37]/30">
                <div class="flex items-start gap-3">
                    <span class="text-base text-[#D4AF37]">✦</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-[#0E261C] block mb-1">
                            Official Atelier Note from VRAZIO Concierge:
                        </span>
                        <p class="text-xs sm:text-sm text-[#0E261C] leading-relaxed italic bg-white p-4 border border-[#D4AF37]/30 shadow-sm"
                           x-text="adminNote">
                        </p>
                    </div>
                </div>
            </div>

            {{-- Claim Content Details --}}
            <div class="p-6 sm:p-10 space-y-8">
                {{-- Particulars Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-gray-100 text-xs">
                    <div class="space-y-3">
                        <h4 class="font-bold uppercase tracking-wider text-[#0E261C]">Order &amp; Product</h4>
                        <div class="space-y-1.5">
                            <p><span class="text-gray-400">Order Reference:</span> <strong class="text-[#0E261C] font-mono">{{ $order->order_number }}</strong></p>
                            <p><span class="text-gray-400">Creation Title:</span> <span class="font-semibold text-[#0E261C]">{{ $claim->product_name ?? 'Bespoke Atelier Creation' }}</span></p>
                            <p><span class="text-gray-400">Order Delivered:</span> {{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-bold uppercase tracking-wider text-[#0E261C]">Patron Details</h4>
                        <div class="space-y-1.5">
                            <p><span class="text-gray-400">Client:</span> {{ $claim->customer_name }}</p>
                            <p><span class="text-gray-400">Telephone:</span> {{ $claim->phone }}</p>
                            <p><span class="text-gray-400">Email:</span> {{ $claim->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Issue Classification & Narrative --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase font-bold tracking-wider text-[#0E261C]">Defect Classification:</span>
                        <span class="px-2.5 py-0.5 bg-[#0E261C] text-[#D4AF37] text-xs font-semibold uppercase tracking-wider rounded-sm">
                            {{ $claim->issue_type_label }}
                        </span>
                    </div>

                    <div class="bg-[#FAF8F5] p-5 border border-gray-200">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-gray-400 block mb-2">Reported Defect Description:</span>
                        <p class="text-xs sm:text-sm text-[#0E261C] leading-relaxed whitespace-pre-line font-light">
                            {{ $claim->description }}
                        </p>
                    </div>
                </div>

                {{-- Photographic Evidence Gallery with Lightbox Click --}}
                <div class="space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#0E261C] block">
                        Submitted Photographic Evidence ({{ $claim->images->count() }} Photos)
                    </span>
                    <span class="text-[11px] text-gray-400 block">Click on any photo to inspect with 4K zoom lightbox.</span>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-2">
                        @foreach($claim->images as $photo)
                            <div class="group relative bg-[#FAF8F5] border border-[#D4AF37]/30 aspect-square overflow-hidden cursor-pointer shadow-sm hover:shadow-lg transition-all"
                                 @click="openLightbox('{{ $photo->url }}')">
                                <img src="{{ $photo->url }}" 
                                     alt="Defect photo evidence" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="text-white text-xs uppercase tracking-widest font-semibold flex items-center gap-1.5 bg-black/60 px-3 py-1 rounded">
                                        <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        Inspect
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Action Strip --}}
                <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ route('orders.index') }}" 
                       class="text-xs uppercase tracking-widest text-gray-500 hover:text-[#0E261C] transition-colors">
                        &larr; Return to My Orders
                    </a>

                    <a href="{{ route('pages.contact') }}" 
                       class="px-6 py-2.5 bg-[#FAF8F5] border border-[#D4AF37]/50 text-[#0E261C] text-xs uppercase tracking-widest font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors">
                        Speak with Dedicated Concierge
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         FULLSCREEN LIGHTBOX ZOOM MODAL
    ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="lightboxOpen" 
         x-cloak 
         @keydown.escape.window="lightboxOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 sm:p-8"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Close Button --}}
        <button @click="lightboxOpen = false" 
                class="absolute top-6 right-6 text-white/70 hover:text-white p-2 text-2xl font-light z-50"
                aria-label="Close Lightbox">
            &times;
        </button>

        <div class="max-w-4xl max-h-[85vh] relative" @click.away="lightboxOpen = false">
            <img :src="activeImage" alt="Inspecting defect photo" class="max-h-[85vh] w-auto max-w-full object-contain mx-auto shadow-2xl border border-white/20">
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'My Reviews & Testimonials | VRAZIO')
@section('meta_description', 'View and manage your submitted reviews and verified purchase testimonials on VRAZIO Haute Joaillerie.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-10 md:py-14">
    <div class="max-w-5xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'Home' => route('home'),
            'My Account' => route('account'),
            'My Reviews' => ''
        ]" />

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6 mb-10 pb-6 border-b border-[#D4AF37]/20">
            <div>
                <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">Patron Feedback</span>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-1">My Reviews &amp; Testimonials</h1>
            </div>
            <a href="{{ route('account') }}" class="inline-flex items-center text-xs uppercase tracking-widest text-[#0E261C] hover:text-[#D4AF37] font-semibold">
                &larr; Back to Account Dashboard
            </a>
        </div>

        @if($reviews->isEmpty())
            <div class="bg-white border border-[#D4AF37]/20 p-12 text-center shadow-xs">
                <div class="w-16 h-16 rounded-full bg-[#FAF8F5] border border-[#D4AF37]/30 flex items-center justify-center mx-auto mb-4 text-[#D4AF37]">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <h3 class="font-display text-xl font-bold text-[#0E261C]">No Reviews Submitted Yet</h3>
                <p class="text-sm text-[#0E261C]/70 font-light mt-2 max-w-md mx-auto">
                    You have not submitted any reviews yet. Commission or order a piece, and share your craftsmanship experience with the atelier.
                </p>
                <div class="mt-8">
                    <a href="{{ route('orders.index') }}" class="inline-flex items-center px-8 py-3.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-widest transition-all">
                        View Past Orders
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-6">
                @foreach($reviews as $rev)
                    <div class="bg-white border border-[#D4AF37]/20 p-6 sm:p-8 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                @if($rev->product && !empty($rev->product->images))
                                    @php
                                        $pImg = $rev->product->images[0] ?? null;
                                    @endphp
                                    <img src="{{ Str::startsWith($pImg, ['http://', 'https://', '/']) ? $pImg : asset('storage/' . $pImg) }}" 
                                         alt="{{ $rev->product->title }}" 
                                         class="w-16 h-16 sm:w-20 sm:h-20 object-cover border border-[#D4AF37]/30 shrink-0">
                                @endif
                                <div>
                                    <a href="{{ $rev->product ? route('products.show', $rev->product->slug) : '#' }}" class="font-display font-bold text-base sm:text-lg text-[#0E261C] hover:text-[#D4AF37] transition-colors">
                                        {{ $rev->product?->title ?? 'Bespoke Item' }}
                                    </a>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <div class="flex text-[#D4AF37] gap-0.5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg width="14" height="14" class="w-3.5 h-3.5 {{ $i <= $rev->rating ? 'fill-current text-[#D4AF37]' : 'text-gray-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500 font-mono">{{ $rev->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-start">
                                @if($rev->is_verified_purchase)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] font-semibold uppercase tracking-wider rounded-xs">
                                        <svg width="12" height="12" class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Verified Purchase
                                    </span>
                                @endif

                                @if($rev->status === 'approved')
                                    <span class="px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 text-[10px] font-semibold uppercase tracking-wider rounded-xs">
                                        Published
                                    </span>
                                @elseif($rev->status === 'pending')
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-300 text-[10px] font-semibold uppercase tracking-wider rounded-xs">
                                        Under Atelier Review
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-semibold uppercase tracking-wider rounded-xs">
                                        Not Approved
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <h4 class="font-display font-bold text-sm sm:text-base text-[#0E261C]">{{ $rev->title }}</h4>
                            <p class="text-xs sm:text-sm text-[#0E261C]/80 font-light mt-1.5 leading-relaxed">{{ $rev->content }}</p>

                            @if(!empty($rev->photos))
                                <div class="flex flex-wrap gap-2 mt-4">
                                    @foreach($rev->photos as $photo)
                                        <img src="{{ asset('storage/' . $photo) }}" 
                                             alt="Customer Photo" 
                                             class="w-16 h-16 object-cover border border-[#D4AF37]/30 hover:opacity-90 cursor-pointer">
                                    @endforeach
                                </div>
                            @endif

                            @if($rev->admin_reply)
                                <div class="mt-4 p-4 bg-[#FAF8F5] border-l-2 border-[#D4AF37]">
                                    <span class="block text-[10px] uppercase tracking-widest text-[#D4AF37] font-semibold">VRAZIO Atelier Response</span>
                                    <p class="text-xs text-[#0E261C]/80 italic mt-1">{{ $rev->admin_reply }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="mt-8">
                    {{ $reviews->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Shopping Cart | VRAZIO Atelier')
@section('meta_description', 'Review your selected VRAZIO fine jewellery and haute couture creations, verify appraisal details, and proceed to secure checkout.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-6">
        <x-breadcrumbs :links="['Shopping Cart' => '']" />

        <div class="text-center max-w-xl mx-auto my-8">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">Your Bag</span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-2">Shopping Cart</h1>
            <div class="w-16 h-px bg-[#D4AF37] mx-auto my-4"></div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-xs sm:text-sm flex items-center justify-between rounded-xs">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(empty($items))
            <div class="bg-white border border-[#D4AF37]/20 p-16 text-center max-w-2xl mx-auto shadow-sm">
                <div class="w-16 h-16 mx-auto text-[#D4AF37] mb-4">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h2 class="font-display text-2xl font-bold text-[#0E261C] mb-2">Your Cart is Empty</h2>
                <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto mb-8 font-light">
                    Looks like you haven't added any jewellery or clothes to your cart yet. Explore our latest pieces and find something you love.
                </p>
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center justify-center px-10 py-4 text-xs font-semibold uppercase tracking-[0.2em] bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all duration-300">
                    Explore Collections
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                {{-- Left: Item Table --}}
                <div class="lg:col-span-8 bg-white border border-[#D4AF37]/20 p-6 sm:p-8 shadow-sm">
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-100">
                            @foreach($items as $key => $item)
                                <li class="py-6 flex flex-col sm:flex-row items-center gap-6">
                                    <div class="w-24 h-24 sm:w-28 sm:h-28 aspect-square bg-[#FAF8F5] border border-[#D4AF37]/20 shrink-0 overflow-hidden">
                                        <img src="{{ $item['image'] ?? 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=400&q=80' }}" 
                                             alt="{{ $item['title'] }}" 
                                             class="w-full h-full object-cover">
                                    </div>

                                    <div class="flex-grow text-center sm:text-left">
                                        <h3 class="font-display text-base font-semibold text-[#0E261C]">
                                            <a href="{{ route('products.show', $item['slug']) }}" class="hover:text-[#D4AF37]">
                                                {{ $item['title'] }}
                                            </a>
                                        </h3>
                                        @if(!empty($item['size']))
                                            <div class="mt-1">
                                                <span class="inline-block text-[11px] font-mono text-gray-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-xs">
                                                    Size: {{ $item['size'] }}
                                                </span>
                                            </div>
                                        @endif
                                        <p class="text-xs text-[#D4AF37] font-semibold mt-1">
                                            ₹{{ number_format((float) $item['price'], 0) }} per piece
                                        </p>
                                    </div>

                                    {{-- Quantity Form --}}
                                    <form action="{{ route('cart.update', $key) }}" method="POST" class="flex items-center border border-gray-300">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}" class="px-2.5 py-1 text-sm hover:bg-gray-100">−</button>
                                        <span class="px-3 py-1 text-xs font-bold font-mono">{{ $item['quantity'] }}</span>
                                        <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="px-2.5 py-1 text-sm hover:bg-gray-100">+</button>
                                    </form>

                                    {{-- Subtotal & Remove --}}
                                    <div class="text-right flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto">
                                        <span class="font-display text-base font-bold text-[#0E261C]">
                                            ₹{{ number_format((float) ($item['price'] * $item['quantity']), 0) }}
                                        </span>
                                        <form action="{{ route('cart.remove', $key) }}" method="POST" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] text-[#9B1B30] hover:underline uppercase tracking-wider">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('products.index') }}" class="text-xs uppercase tracking-widest text-[#0E261C] hover:text-[#D4AF37] flex items-center gap-1.5 font-semibold">
                            <span>&larr;</span>
                            <span>Continue Shopping</span>
                        </a>
                        <form action="{{ route('cart.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs uppercase tracking-widest text-gray-400 hover:text-[#9B1B30]">
                                Clear Cart
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right: Order Summary --}}
                <div class="lg:col-span-4 bg-white border border-[#D4AF37]/20 p-6 sm:p-8 shadow-sm h-fit space-y-6">
                    <h2 class="font-display text-lg font-bold text-[#0E261C] border-b border-gray-100 pb-4">
                        Order Summary
                    </h2>

                    <div class="space-y-3 text-xs text-[#0E261C]/80">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-mono font-semibold text-[#0E261C]">₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>GST (3%):</span>
                            <span class="font-mono font-semibold text-[#0E261C]">₹{{ number_format($gst, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-green-700">
                            <span>Shipping:</span>
                            <span class="font-semibold uppercase tracking-wider text-[10px]">Free Delivery</span>
                        </div>
                        <div class="border-t border-gray-100 pt-4 flex justify-between text-sm font-bold text-[#0E261C]">
                            <span>Total Amount:</span>
                            <span class="font-display text-xl text-[#0E261C]">₹{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <a href="{{ route('checkout.index') }}" 
                           class="w-full block text-center py-4 bg-[#9B1B30] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all duration-300 shadow-xl">
                            Proceed to Checkout
                        </a>
                    </div>

                    <div class="p-4 bg-[#FAF8F5] border border-[#D4AF37]/20 text-[11px] text-gray-600 space-y-2">
                        <div class="flex items-center gap-2">
                            <svg width="16" height="16" class="w-4 h-4 text-[#D4AF37] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span class="text-emerald-800 font-medium">Cash on Delivery Available • Online Payment Optional</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="16" height="16" class="w-4 h-4 text-[#D4AF37] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Free insured delivery all over India</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

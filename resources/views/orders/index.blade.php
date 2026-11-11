@extends('layouts.app')

@section('title', 'My Orders | VRAZIO')
@section('meta_description', 'View your orders, track delivery, and claim warranty on your VRAZIO pieces.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-10 md:py-14">
    <div class="max-w-6xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'Home' => route('home'),
            'My Orders' => ''
        ]" />

        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between my-8 gap-4 pb-6 border-b border-gray-200">
            <div>
                <span class="text-[11px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">
                    Customer Portal
                </span>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-1">
                    My Orders
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Track your orders, view receipts, and request warranty support.
                </p>
            </div>

            {{-- Quick Search by Order # or Phone --}}
            <form action="{{ route('orders.index') }}" method="GET" class="flex items-center gap-2 max-w-sm w-full">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Enter Order #, Phone, or Email..."
                       class="text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none w-full bg-white rounded-xs">
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#0E261C] text-[#D4AF37] text-xs uppercase font-semibold tracking-wider hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors shrink-0 shadow-xs">
                    Find Order
                </button>
            </form>
        </div>

        {{-- Notifications --}}
        @if(session('info'))
            <div class="mb-6 p-4 bg-amber-50 border-l-4 border-[#D4AF37] text-amber-900 text-xs sm:text-sm">
                {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-[#9B1B30] text-red-900 text-xs sm:text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Orders Listing --}}
        @if($orders->isEmpty())
            <div class="bg-white border border-[#D4AF37]/30 p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 bg-[#0E261C]/5 border border-[#D4AF37]/30 text-[#D4AF37] mx-auto flex items-center justify-center rounded-full">
                    <svg width="32" height="32" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h3 class="font-display text-xl font-bold text-[#0E261C]">No Orders Found</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto">
                    We could not find any orders with this search. Please enter your 10-digit phone number or Order ID in the search box above.
                </p>
                <div class="pt-2">
                    <a href="{{ route('products.index') }}" 
                       class="inline-block px-8 py-3 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-widest font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors">
                        Browse Collections
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-6">
                @foreach($orders as $order)
                    @php
                        $isDelivered = ($order->fulfillment_status === 'delivered');
                        $claim = $order->warrantyClaim;
                    @endphp

                    <div class="bg-white border border-[#D4AF37]/25 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                        {{-- Card Header --}}
                        <div class="bg-[#FAF8F5] p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4 text-xs">
                            <div class="flex items-center gap-4 flex-wrap">
                                <div>
                                    <span class="text-gray-400 block uppercase text-[10px]">Order Number</span>
                                    <strong class="font-mono text-[#0E261C] text-sm">{{ $order->order_number }}</strong>
                                </div>
                                <div class="border-l border-gray-200 pl-4 hidden sm:block">
                                    <span class="text-gray-400 block uppercase text-[10px]">Order Date</span>
                                    <span>{{ $order->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="border-l border-gray-200 pl-4">
                                    <span class="text-gray-400 block uppercase text-[10px]">Total Amount</span>
                                    <strong class="text-[#0E261C]">₹{{ number_format((float) $order->total_amount, 2) }}</strong>
                                </div>
                            </div>

                            {{-- Status Badges --}}
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Delivery Status Badge --}}
                                <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-sm {{ $order->fulfillment_status === 'cancelled' ? 'bg-rose-100 text-rose-800 border border-rose-300' : ($isDelivered ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                                    {{ $order->getCasualStatusLabel() }}
                                </span>

                                {{-- Estimated Delivery Badge if set and not yet delivered --}}
                                @if(!$isDelivered && $order->getEstimatedDeliveryText())
                                    <span class="px-2.5 py-1 text-[10px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-sm">
                                        Expected: {{ $order->getEstimatedDeliveryText() }}
                                    </span>
                                @endif

                                {{-- Warranty Claim Badge if claim exists --}}
                                @if($claim)
                                    <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-sm bg-emerald-50 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                                        <svg width="20" height="20" class="w-3 h-3 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Warranty Claimed ({{ $claim->status_label }})</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Body: Items List --}}
                        <div class="p-6 divide-y divide-gray-100">
                            @foreach($order->items as $item)
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center gap-4">
                                    <div class="w-16 h-16 bg-[#FAF8F5] border border-gray-200 shrink-0 overflow-hidden">
                                        <img src="{{ $item->product?->primary_image ?? 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=200&q=80' }}" 
                                             alt="{{ $item->product_title }}" 
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="font-display text-sm font-semibold text-[#0E261C]">
                                            @if($item->product)
                                                <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-[#D4AF37]">
                                                    {{ $item->product_title }}
                                                </a>
                                            @else
                                                {{ $item->product_title }}
                                            @endif
                                        </h4>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            SKU: {{ $item->product_sku }} &bull; Qty: {{ $item->quantity }}
                                        </div>
                                    </div>
                                    <div class="text-right text-xs font-semibold text-[#0E261C]">
                                        ₹{{ number_format((float) $item->line_total, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Card Footer: Action Buttons --}}
                        <div class="p-5 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-[11px] text-gray-500">
                                Shipped to: <span class="text-gray-800 font-medium">{{ $order->client_name }}</span> ({{ $order->client_phone }})
                            </div>

                            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                                {{-- Invoice Button (Web View & Download) --}}
                                <a href="{{ route('order.invoice', $order->order_number) }}" 
                                   target="_blank"
                                   title="View & Download Invoice"
                                   class="px-3.5 py-2 bg-white border border-[#D4AF37]/50 text-[#0E261C] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-wider transition-colors flex items-center gap-1.5">
                                    <svg width="14" height="14" class="w-3.5 h-3.5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Invoice</span>
                                </a>

                                {{-- Track Order Button --}}
                                <a href="{{ route('order.track', $order->order_number) }}" 
                                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:text-[#0E261C] hover:border-[#D4AF37] text-xs font-semibold uppercase tracking-wider transition-colors">
                                    Track Order
                                </a>

                                @if($order->canBeCancelled())
                                    <form action="{{ route('order.cancel', $order->order_number) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you wish to cancel order #{{ $order->order_number }}? Reserved inventory will be restored.');"
                                          class="inline-block">
                                        @csrf
                                        <button type="submit" 
                                                class="px-4 py-2 bg-white border border-rose-300 text-rose-700 hover:bg-rose-700 hover:text-white text-xs font-semibold uppercase tracking-wider transition-colors">
                                            Cancel Order
                                        </button>
                                    </form>
                                @endif

                                {{-- Warranty Actions: Claim Warranty / View Claim --}}
                                @if($claim)
                                    <a href="{{ route('warranty.show', $claim->claim_number) }}" 
                                       class="px-4 py-2 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-wider transition-colors flex items-center gap-1.5 shadow-sm">
                                        <svg width="14" height="14" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>View Warranty Claim</span>
                                    </a>
                                @elseif($isDelivered)
                                    <a href="{{ route('warranty.create', $order->order_number) }}" 
                                       class="px-4 py-2 bg-[#9B1B30] text-white hover:bg-[#7e1425] text-xs font-semibold uppercase tracking-wider transition-colors flex items-center gap-1.5 shadow-sm">
                                        <svg width="14" height="14" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>Claim Warranty</span>
                                    </a>
                                @else
                                    <button disabled 
                                            class="px-4 py-2 bg-gray-200 text-gray-400 text-xs font-semibold uppercase tracking-wider cursor-not-allowed"
                                            title="Available once delivered">
                                        Claim Warranty (Pending Delivery)
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Pagination --}}
                <div class="pt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

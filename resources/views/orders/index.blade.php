@extends('layouts.app')

@section('title', 'My Orders | VRAZIO')
@section('meta_description', 'View your orders, track delivery, claim warranty, and manage cancellations & refunds on your VRAZIO pieces.')

@section('content')
<div x-data="cancellationManager()" class="bg-[#FAF8F5] min-h-screen py-10 md:py-14">
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
                    Track your orders, view GST receipts, request warranty support, and track refund dockets.
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

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-900 text-xs sm:text-sm">
                {{ session('success') }}
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
                        $isCancelled = in_array($order->fulfillment_status, ['cancelled', 'partially_cancelled'], true);
                        $claim = $order->warrantyClaim;
                        $cancellations = $order->cancellations;
                        $hasActiveReview = $cancellations->where('status', 'pending_review')->isNotEmpty();
                        $casualKey = $order->getCasualStatusKey();
                        $inTransit = in_array($casualKey, ['shipped', 'out_for_delivery'], true);
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
                                {{-- Delivery / Order Status Badge --}}
                                <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-sm {{ $order->fulfillment_status === 'cancelled' ? 'bg-rose-100 text-rose-800 border border-rose-300' : ($order->fulfillment_status === 'partially_cancelled' ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($isDelivered ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-amber-100 text-amber-800 border border-amber-200')) }}">
                                    {{ $order->getCasualStatusLabel() }}
                                </span>

                                {{-- Pending Review Pill --}}
                                @if($hasActiveReview)
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-900 border border-amber-300 rounded-sm flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Cancellation Under Review</span>
                                    </span>
                                @endif

                                {{-- Estimated Delivery Badge if set and not yet delivered / cancelled --}}
                                @if(!$isDelivered && !$isCancelled && $order->getEstimatedDeliveryText())
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
                                    <div class="w-16 h-16 bg-[#FAF8F5] border border-gray-200 shrink-0 overflow-hidden relative">
                                        <img src="{{ $item->product?->primary_image ?? 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=200&q=80' }}" 
                                             alt="{{ $item->product_title }}" 
                                             class="w-full h-full object-cover {{ $item->status === 'cancelled' ? 'opacity-50 grayscale' : '' }}">
                                        @if($item->status === 'cancelled')
                                            <span class="absolute inset-0 bg-white/70 flex items-center justify-center text-[9px] font-bold text-rose-700 uppercase tracking-tighter">Cancelled</span>
                                        @endif
                                    </div>

                                    <div class="flex-grow">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-display text-sm font-semibold text-[#0E261C]">
                                                @if($item->product)
                                                    <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-[#D4AF37]">
                                                        {{ $item->product_title }}
                                                    </a>
                                                @else
                                                    {{ $item->product_title }}
                                                @endif
                                            </h4>

                                            @if($item->status === 'cancelled')
                                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200 rounded-xs">
                                                    Item Cancelled
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                            <span>SKU: {{ $item->product_sku }}</span>
                                            <span>&bull;</span>
                                            <span>Qty: {{ $item->quantity }}</span>

                                            @if($item->status === 'cancelled' && (float) $item->refunded_amount > 0)
                                                <span>&bull;</span>
                                                <span class="text-emerald-700 font-medium">Refunded: ₹{{ number_format((float) $item->refunded_amount, 2) }}</span>
                                            @endif

                                            {{-- Partial Item Cancellation Trigger --}}
                                            @if($item->status !== 'cancelled' && $order->items->count() > 1 && $order->canBeCancelled())
                                                <span>&bull;</span>
                                                <button type="button" 
                                                        @click="openCancelModal('{{ $order->order_number }}', {{ $item->id }}, '{{ addslashes($item->product_title) }}')"
                                                        class="text-rose-600 hover:text-rose-800 underline font-medium hover:font-semibold transition-all">
                                                    Cancel This Item
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-right text-xs font-semibold text-[#0E261C]">
                                        <span class="{{ $item->status === 'cancelled' ? 'line-through text-gray-400' : '' }}">
                                            ₹{{ number_format((float) $item->line_total, 2) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Cancellation & Refund Tracking Dossier --}}
                        @if($cancellations->isNotEmpty())
                            <div class="px-6 pb-6 pt-0 space-y-3">
                                @foreach($cancellations as $can)
                                    <div class="p-4 bg-[#FAF8F5] border border-[#D4AF37]/35 rounded-xs space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200/60 pb-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full {{ $can->status === 'completed' ? 'bg-emerald-600' : ($can->status === 'rejected' ? 'bg-rose-600' : 'bg-amber-500 animate-pulse') }}"></span>
                                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#0E261C]">
                                                    {{ $can->type === 'partial' ? 'Item Cancellation' : 'Full Consignment Cancellation' }} &bull; Docket <span class="font-mono">{{ $can->cancellation_number }}</span>
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-xs {{ $can->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($can->status === 'approved' ? 'bg-blue-100 text-blue-800' : ($can->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-900')) }}">
                                                    {{ $can->status_label }}
                                                </span>
                                                <span class="text-[10px] text-gray-400">{{ $can->created_at->format('M d, Y h:i A') }}</span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                            <div>
                                                <span class="text-[10px] text-gray-400 block uppercase tracking-wider">Reason Filed</span>
                                                <span class="font-medium text-[#0E261C]">{{ $can->reason }}</span>
                                                @if($can->customer_notes)
                                                    <p class="text-[11px] text-gray-600 italic mt-0.5">"{{ $can->customer_notes }}"</p>
                                                @endif
                                            </div>

                                            <div>
                                                <span class="text-[10px] text-gray-400 block uppercase tracking-wider">Refund Value</span>
                                                <span class="font-bold text-[#0E261C]">₹{{ number_format((float) $can->refund_amount, 2) }}</span>
                                                <span class="text-[10px] text-gray-500 block">Via {{ strtoupper($can->refund_method) }}</span>
                                            </div>

                                            <div>
                                                <span class="text-[10px] text-gray-400 block uppercase tracking-wider">Refund Gateway Status</span>
                                                @php $latestRefund = $can->refunds->last(); @endphp
                                                @if($latestRefund)
                                                    <span class="font-semibold {{ $latestRefund->status === 'refunded' ? 'text-emerald-700' : ($latestRefund->status === 'failed' ? 'text-rose-700' : 'text-amber-700') }}">
                                                        {{ $latestRefund->status_label }}
                                                    </span>
                                                    @if($latestRefund->razorpay_refund_id)
                                                        <span class="text-[10px] font-mono text-gray-500 block truncate" title="{{ $latestRefund->razorpay_refund_id }}">
                                                            Ref: {{ $latestRefund->razorpay_refund_id }}
                                                        </span>
                                                    @endif
                                                @elseif((float) $can->refund_amount > 0)
                                                    <span class="text-amber-700 font-medium">Pending Processing</span>
                                                @else
                                                    <span class="text-gray-500">No Refund Applicable</span>
                                                @endif
                                            </div>

                                            <div>
                                                <span class="text-[10px] text-gray-400 block uppercase tracking-wider">Atelier Records</span>
                                                @if($order->creditNotes->isNotEmpty())
                                                    <a href="{{ route('credit_note.show', $order->creditNotes->first()->credit_note_number) }}" 
                                                       target="_blank" 
                                                       class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#0E261C] hover:text-[#D4AF37] underline">
                                                        <svg class="w-3.5 h-3.5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        <span>GST Credit Note</span>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400 text-[11px]">Adjustment Note in queue</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($can->status === 'rejected' && $can->rejection_reason)
                                            <div class="p-2.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xs">
                                                <strong class="font-semibold block text-[10px] uppercase tracking-wider text-rose-900">Atelier Concierge Decision:</strong>
                                                {{ $can->rejection_reason }}
                                            </div>
                                        @elseif($can->status === 'pending_review')
                                            <div class="p-2.5 bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xs flex items-center gap-2">
                                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>This cancellation request is under priority review by our master packing atelier. You will receive an email update upon decision.</span>
                                            </div>
                                        @elseif((float) $can->refund_amount > 0 && (!$latestRefund || $latestRefund->status !== 'refunded'))
                                            <div class="text-[11px] text-gray-600 flex items-center gap-1.5 pt-1">
                                                <svg class="w-3.5 h-3.5 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Refunds are automatically disbursed to your original payment account within 5–7 banking days via Razorpay.</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Card Footer: Action Buttons --}}
                        <div class="p-5 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-[11px] text-gray-500">
                                Shipped to: <span class="text-gray-800 font-medium">{{ $order->client_name }}</span> ({{ $order->client_phone }})
                            </div>

                            <div class="flex items-center gap-2.5 w-full sm:w-auto flex-wrap justify-end">
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

                                {{-- Cancellation Trigger Button --}}
                                @if($order->canBeCancelled())
                                    <button type="button" 
                                            @click="openCancelModal('{{ $order->order_number }}')"
                                            class="px-4 py-2 bg-white border border-rose-300 text-rose-700 hover:bg-rose-700 hover:text-white text-xs font-semibold uppercase tracking-wider transition-colors">
                                        Cancel Order
                                    </button>
                                @elseif($inTransit)
                                    <button type="button"
                                            @click="openTransitNotice()"
                                            class="px-4 py-2 bg-white border border-amber-300 text-amber-800 hover:bg-amber-50 text-xs font-semibold uppercase tracking-wider transition-colors">
                                        Cancel / Return Info
                                    </button>
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

    {{-- Interactive Cancellation Modal --}}
    <div x-show="modalOpen" 
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         @keydown.escape.window="closeModal()">
        
        <div class="bg-white border border-[#D4AF37] max-w-lg w-full p-6 sm:p-8 shadow-2xl relative my-8"
             @click.outside="closeModal()">
            
            {{-- Close Button --}}
            <button type="button" @click="closeModal()" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-light leading-none">
                &times;
            </button>

            {{-- Modal Header --}}
            <div class="mb-5 pb-4 border-b border-gray-100">
                <span class="text-[10px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold block">
                    Consignment Cancellation Protocol
                </span>
                <h3 class="font-display text-xl sm:text-2xl font-bold text-[#0E261C] mt-1">
                    <template x-if="itemId">
                        <span>Cancel Piece: <span x-text="itemTitle" class="text-[#D4AF37]"></span></span>
                    </template>
                    <template x-if="!itemId">
                        <span>Cancel Order #<span x-text="orderNumber"></span></span>
                    </template>
                </h3>
            </div>

            {{-- Form --}}
            <form :action="'/orders/' + orderNumber + '/cancel'" method="POST" @submit="isSubmitting = true">
                @csrf
                <input type="hidden" name="order_item_id" :value="itemId">

                {{-- Dynamic Eligibility Warning if Ineligible --}}
                <template x-if="!isEligible && !isLoadingEligibility">
                    <div class="mb-5 p-3.5 bg-rose-50 border-l-4 border-rose-600 text-rose-900 text-xs">
                        <strong class="block font-semibold">Cancellation Not Available</strong>
                        <span x-text="eligibilityMessage"></span>
                    </div>
                </template>

                {{-- Reason Dropdown (Required) --}}
                <div class="mb-4">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                        Cancellation Reason <span class="text-rose-600">*</span>
                    </label>
                    <select name="reason" x-model="reason" required 
                            class="w-full text-xs p-3 border border-gray-300 focus:border-[#D4AF37] focus:outline-none bg-white">
                        <option value="Ordered by mistake">Ordered by mistake</option>
                        <option value="Changed my mind">Changed my mind</option>
                        <option value="Wrong product selected">Wrong product selected</option>
                        <option value="Found a better price">Found a better price</option>
                        <option value="Delivery delay">Delivery delay</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                {{-- Additional Comments (Optional) --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                        Additional Comments (Optional)
                    </label>
                    <textarea name="customer_notes" x-model="customerNotes" rows="2" 
                              placeholder="Please share any feedback or instructions for our concierge team..."
                              class="w-full text-xs p-3 border border-gray-300 focus:border-[#D4AF37] focus:outline-none"></textarea>
                </div>

                {{-- Live Financial & Policy Breakdown Card --}}
                <div class="mb-6 p-4 bg-[#FAF8F5] border border-[#D4AF37]/35 rounded-xs space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 uppercase text-[10px] tracking-wider">Applicable Refund</span>
                        <template x-if="isLoadingEligibility">
                            <span class="text-gray-400 italic">Calculating...</span>
                        </template>
                        <template x-if="!isLoadingEligibility">
                            <span class="font-bold text-sm text-[#0E261C]">₹<span x-text="Number(refundAmount).toFixed(2)"></span></span>
                        </template>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-gray-600 border-t border-gray-200/50 pt-2">
                        <span>Refund Method:</span>
                        <span class="font-semibold text-gray-800" x-text="formatRefundMethod(refundMethod)"></span>
                    </div>

                    <template x-if="requiresReview">
                        <div class="mt-2 p-2 bg-amber-50 border border-amber-200 text-amber-900 text-[11px] rounded-xs">
                            <strong>Atelier Notice:</strong> Because this consignment is currently in packaging preparation, your request will be routed to our concierge supervisor for swift approval.
                        </div>
                    </template>

                    <template x-if="!requiresReview && refundAmount > 0">
                        <div class="mt-2 text-[10px] text-gray-500">
                            Disbursement timeline: 5–7 business days to your original bank/card source via Razorpay.
                        </div>
                    </template>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="closeModal()" 
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold uppercase tracking-wider transition-colors">
                        Keep Order
                    </button>

                    <button type="submit" 
                            :disabled="!isEligible || isSubmitting || isLoadingEligibility"
                            class="px-6 py-2.5 bg-[#9B1B30] hover:bg-[#7e1425] text-white text-xs font-semibold uppercase tracking-wider transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span x-text="isSubmitting ? 'Processing...' : 'Confirm Cancellation'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Armoured Transit / Post-Dispatch Notice Modal --}}
    <div x-show="transitModalOpen" 
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="closeTransitModal()">
        
        <div class="bg-white border border-[#D4AF37] max-w-md w-full p-6 sm:p-8 shadow-2xl relative"
             @click.outside="closeTransitModal()">
            
            <button type="button" @click="closeTransitModal()" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-light leading-none">
                &times;
            </button>

            <span class="text-[10px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold block">
                Armoured Transit Protocol
            </span>
            <h3 class="font-display text-xl font-bold text-[#0E261C] mt-1 mb-3">
                Consignment Dispatched
            </h3>

            <p class="text-xs text-gray-600 leading-relaxed mb-4">
                Your consignment has passed our secure atelier gates and is currently in transit with our armoured logistics partner. Standard pre-dispatch cancellation is closed.
            </p>

            <div class="space-y-3 mb-6 bg-[#FAF8F5] p-4 border border-[#D4AF37]/30 text-xs text-gray-700">
                <div class="flex items-start gap-2">
                    <span class="font-bold text-[#D4AF37]">1.</span>
                    <span><strong>Doorstep Refusal:</strong> You may decline acceptance upon arrival at your doorstep. The courier will return the sealed consignment directly to our atelier vault for an immediate refund.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="font-bold text-[#D4AF37]">2.</span>
                    <span><strong>Atelier Returns & Warranty:</strong> Once delivered, you can initiate a hassle-free exchange or return within our standard patron policy.</span>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <a href="https://wa.me/918791227210" target="_blank" 
                   class="px-4 py-2 bg-[#0E261C] text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-wider transition-colors text-center w-full">
                    Concierge WhatsApp
                </a>
                <button type="button" @click="closeTransitModal()" 
                        class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold uppercase tracking-wider text-center">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cancellationManager() {
    return {
        modalOpen: false,
        transitModalOpen: false,
        orderNumber: '',
        itemId: null,
        itemTitle: '',
        isLoadingEligibility: false,
        refundAmount: 0,
        refundMethod: 'razorpay',
        requiresReview: false,
        eligibilityMessage: '',
        isEligible: true,
        reason: 'Ordered by mistake',
        customerNotes: '',
        isSubmitting: false,

        openCancelModal(orderNum, itemId = null, itemTitle = '') {
            this.orderNumber = orderNum;
            this.itemId = itemId;
            this.itemTitle = itemTitle;
            this.reason = 'Ordered by mistake';
            this.customerNotes = '';
            this.modalOpen = true;
            this.isLoadingEligibility = true;
            this.isEligible = true;
            this.refundAmount = 0;
            this.requiresReview = false;

            const url = '/orders/' + encodeURIComponent(orderNum) + '/cancel-eligibility' + (itemId ? '?item_id=' + itemId : '');
            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.isLoadingEligibility = false;
                if (data.success && data.eligibility) {
                    this.isEligible = data.eligibility.eligible;
                    this.requiresReview = data.eligibility.requires_review;
                    this.refundAmount = data.eligibility.refund_amount;
                    this.refundMethod = data.eligibility.refund_method;
                    this.eligibilityMessage = data.eligibility.message;
                }
            })
            .catch(err => {
                this.isLoadingEligibility = false;
                console.error('Error fetching eligibility:', err);
            });
        },

        openTransitNotice() {
            this.transitModalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },

        closeTransitModal() {
            this.transitModalOpen = false;
        },

        formatRefundMethod(method) {
            if (method === 'razorpay') return 'Original Payment Gateway (Razorpay)';
            if (method === 'cod') return 'Cash On Delivery (Zero upfront fee)';
            if (method === 'unpaid') return 'Unpaid (No refund required)';
            return method ? method.toUpperCase() : 'Standard';
        }
    };
}
</script>
@endsection

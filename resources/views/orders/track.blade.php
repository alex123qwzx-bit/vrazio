@extends('layouts.app')

@section('title', 'Track Your Order | VRAZIO')
@section('meta_description', 'Track your VRAZIO order delivery in real time with courier and status updates.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-10 sm:py-14">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="[
            'Home' => route('home'),
            'Track Order' => ''
        ]" />

        {{-- Header --}}
        <div class="text-center max-w-xl mx-auto my-8">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">
                Delivery Updates
            </span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-1">
                Track Your Order
            </h1>
            <div class="w-12 h-0.5 bg-[#D4AF37] mx-auto my-3"></div>
            <p class="text-xs sm:text-sm text-gray-500">
                Enter your order number or phone number to see where your package is.
            </p>
        </div>

        {{-- Tracking Input Form --}}
        <div class="bg-white border border-[#D4AF37]/30 p-6 sm:p-8 max-w-xl mx-auto shadow-xs mb-10 rounded-xs">
            <form action="{{ route('order.track') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="order_number" value="{{ $reference ?? '' }}" 
                       placeholder="Enter Order # (e.g. VRZ-88219) or Phone..." required 
                       class="flex-grow text-xs px-4 py-3 border border-gray-300 focus:border-[#D4AF37] focus:outline-none uppercase font-mono rounded-xs">
                <button type="submit" 
                        class="px-8 py-3 bg-[#0E261C] text-[#D4AF37] text-xs uppercase tracking-wider font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all rounded-xs shrink-0 shadow-xs">
                    Track Order
                </button>
            </form>
        </div>

        @if(isset($order) && $order)
            @php
                $currentStepNum = $order->getCurrentStepNumber();
                $steps = [
                    1 => [
                        'key' => 'order_placed',
                        'label' => 'Order Placed',
                        'desc' => 'Confirmed & received',
                        'time' => $order->created_at,
                    ],
                    2 => [
                        'key' => 'packed',
                        'label' => 'Packed',
                        'desc' => 'Safely boxed & sealed',
                        'time' => $order->packed_at,
                    ],
                    3 => [
                        'key' => 'shipped',
                        'label' => 'Shipped',
                        'desc' => 'On the way with courier',
                        'time' => $order->shipped_at,
                    ],
                    4 => [
                        'key' => 'out_for_delivery',
                        'label' => 'Out for Delivery',
                        'desc' => 'Arrived in your city',
                        'time' => $order->out_for_delivery_at,
                    ],
                    5 => [
                        'key' => 'delivered',
                        'label' => 'Delivered',
                        'desc' => 'Delivered to your address',
                        'time' => $order->delivered_at,
                    ],
                ];
            @endphp

            {{-- Order Found Card --}}
            <div class="bg-white border border-[#D4AF37]/30 p-6 sm:p-10 shadow-xs rounded-xs">
                {{-- Order Meta Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-gray-200 gap-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold block">Order Reference</span>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#0E261C] mt-0.5">
                            {{ $order->order_number }}
                        </h2>
                        <div class="text-xs text-gray-500 mt-1">
                            Placed on {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : 'Recent' }}
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:text-right">
                        <div>
                            <span class="inline-block px-3 py-1 bg-green-50 text-green-800 text-xs font-bold uppercase tracking-wider rounded-full border border-green-300">
                                {{ $order->getCasualStatusLabel() }}
                            </span>
                            <div class="text-[11px] text-gray-500 mt-1">
                                Payment: <strong class="text-gray-800">{{ strtoupper($order->payment_status ?? 'PAID') }}</strong>
                            </div>
                        </div>

                        {{-- Invoice Action --}}
                        <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank"
                           class="px-4 py-2 bg-gray-100 hover:bg-[#D4AF37] hover:text-[#0E261C] text-gray-800 text-xs font-semibold uppercase tracking-wider transition-colors rounded-xs border border-gray-200 flex items-center gap-1.5 shadow-xs">
                            <svg width="16" height="16" class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Invoice
                        </a>

                        @if($order->canBeCancelled())
                            <form action="{{ route('order.cancel', $order->order_number) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you wish to cancel order #{{ $order->order_number }}? Reserved atelier items will be restored.');"
                                  class="inline-block">
                                @csrf
                                <button type="submit" 
                                        class="px-4 py-2 bg-rose-50 hover:bg-rose-700 text-rose-700 hover:text-white text-xs font-semibold uppercase tracking-wider transition-colors rounded-xs border border-rose-300 flex items-center gap-1.5 shadow-xs">
                                    Cancel Order
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Cancelled Status Notice --}}
                @if($order->fulfillment_status === 'cancelled')
                    <div class="mt-6 p-4 bg-rose-50 border border-rose-200 rounded-xs flex items-center gap-3 text-rose-800">
                        <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-800 flex items-center justify-center font-bold text-base shrink-0">
                            ✕
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-rose-800 block">
                                Order Cancelled
                            </span>
                            <span class="text-sm font-medium text-rose-900">
                                This order was cancelled. Any reserved items have been restored to our vault inventory. If you need further assistance, please contact Concierge on WhatsApp.
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Estimated Delivery Date & Time (Set by Admin) --}}
                @if($order->getEstimatedDeliveryText())
                    <div class="mt-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-base shrink-0">
                                📅
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-800 block">
                                    Expected Delivery Date &amp; Time
                                </span>
                                <span class="text-sm sm:text-base font-bold text-[#0E261C]">
                                    {{ $order->getEstimatedDeliveryText() }}
                                </span>
                            </div>
                        </div>
                        <span class="text-[11px] text-emerald-800 font-medium px-2.5 py-1 bg-white border border-emerald-200 rounded-full shrink-0">
                            Guaranteed On Time
                        </span>
                    </div>
                @endif

                {{-- Delivery Progress Timeline --}}
                <div class="my-10">
                    <h3 class="text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-6 text-center">
                        Order Progress
                    </h3>

                    {{-- Horizontal Timeline Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 relative">
                        @foreach($steps as $num => $step)
                            @php
                                $isCompleted = $num <= $currentStepNum;
                                $isCurrent = $num === $currentStepNum;
                            @endphp
                            <div class="flex flex-col items-center text-center relative z-10">
                                {{-- Circle Icon --}}
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs mb-2.5 transition-all shadow-xs {{ $isCompleted ? 'bg-[#0E261C] text-[#D4AF37] border-2 border-[#D4AF37]' : 'bg-gray-100 text-gray-400 border border-gray-200' }}">
                                    @if($isCompleted && $num < $currentStepNum)
                                        <svg width="16" height="16" class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        {{ $num }}
                                    @endif
                                </div>

                                {{-- Step Label --}}
                                <h4 class="font-display text-xs font-bold {{ $isCompleted ? 'text-[#0E261C]' : 'text-gray-400' }}">
                                    {{ $step['label'] }}
                                </h4>

                                <p class="text-[10px] text-gray-500 mt-0.5 leading-snug">
                                    {{ $step['desc'] }}
                                </p>

                                @if($step['time'])
                                    <span class="text-[9px] text-[#D4AF37] font-semibold mt-1">
                                        {{ $step['time']->format('d M, h:i A') }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Courier Partner & Tracking Notes (If Added by Admin) --}}
                @if($order->tracking_courier || $order->tracking_number || $order->tracking_notes)
                    <div class="mb-8 p-4 sm:p-5 bg-amber-50/70 border border-amber-200 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-[#0E261C] block">
                                🚚 Courier Tracking Details
                            </span>
                            <div class="text-xs text-gray-800">
                                @if($order->tracking_courier)
                                    <span>Courier: <strong>{{ $order->tracking_courier }}</strong></span>
                                @endif
                                @if($order->tracking_number)
                                    <span class="ml-3">Tracking / AWB: <strong class="font-mono bg-white px-2 py-0.5 border border-amber-300 rounded text-[#0E261C]">{{ $order->tracking_number }}</strong></span>
                                @endif
                            </div>
                            @if($order->tracking_notes)
                                <p class="text-xs text-gray-600 mt-1 italic">
                                    "{{ $order->tracking_notes }}"
                                </p>
                            @endif
                        </div>

                        <div class="shrink-0">
                            <a href="https://wa.me/918791227210?text={{ urlencode('Hi VRAZIO, I am checking delivery status for order ' . $order->order_number) }}" 
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#25D366] text-white text-xs font-semibold rounded-xs hover:bg-[#1EBE5D] transition-colors shadow-xs">
                                WhatsApp Courier Help
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Items in Order --}}
                <div class="pt-6 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs uppercase tracking-wider text-gray-500 font-bold">
                            Items in Your Order
                        </h3>
                        <span class="text-xs font-semibold text-gray-700">
                            Total: <strong class="text-[#0E261C] font-mono text-sm">₹{{ number_format((float) $order->total_amount, 2) }}</strong>
                        </span>
                    </div>

                    <ul class="divide-y divide-gray-100 text-xs sm:text-sm">
                        @foreach($order->items as $item)
                            <li class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-[#D4AF37]"></span>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $item->product_title }}</span>
                                        <span class="text-xs text-gray-400 ml-1">× {{ $item->quantity }}</span>
                                        @if($item->product_sku)
                                            <span class="block text-[11px] text-gray-400 font-mono">SKU: {{ $item->product_sku }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="font-mono font-bold text-[#0E261C]">
                                    ₹{{ number_format((float) $item->line_total, 2) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Warranty Section --}}
                <div class="mt-8 pt-6 border-t border-gray-200 bg-[#FAF8F5] -mx-6 sm:-mx-10 -mb-6 sm:-mb-10 p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                    @if($order->warrantyClaim)
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm shrink-0">✓</span>
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-800 block">Warranty Claimed</span>
                                <span class="text-xs text-gray-700">Claim #<strong class="font-mono">{{ $order->warrantyClaim->claim_number }}</strong> (Status: <strong>{{ ucfirst($order->warrantyClaim->status) }}</strong>)</span>
                            </div>
                        </div>
                        <a href="{{ route('warranty.show', $order->warrantyClaim->claim_number) }}" 
                           class="px-5 py-2.5 bg-[#0E261C] text-[#D4AF37] text-xs font-semibold uppercase tracking-wider hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors shrink-0 shadow-xs rounded-xs">
                            View Warranty Status &rarr;
                        </a>
                    @elseif($order->getCasualStatusKey() === 'delivered')
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm shrink-0">✦</span>
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-800 block">Delivered &amp; Protected</span>
                                <span class="text-xs text-gray-600">Your order is delivered and eligible for VRAZIO's Lifetime Warranty.</span>
                            </div>
                        </div>
                        <a href="{{ route('warranty.create', $order->order_number) }}" 
                           class="px-5 py-2.5 bg-[#9B1B30] text-white hover:bg-[#7e1425] text-xs font-semibold uppercase tracking-wider transition-colors shrink-0 shadow-xs rounded-xs">
                            Claim Warranty Now
                        </a>
                    @else
                        <div class="text-xs text-gray-500">
                            <span>✦ BIS Hallmarked &amp; Lifetime Craftsmanship Warranty active on all authentic pieces.</span>
                        </div>
                        <a href="https://wa.me/918791227210" target="_blank" rel="noopener noreferrer" 
                           class="text-xs font-semibold text-[#0E261C] hover:text-[#D4AF37] flex items-center gap-1">
                            Questions? WhatsApp Concierge &rarr;
                        </a>
                    @endif
                </div>
            </div>
        @elseif($accessDenied ?? false)
            <div class="bg-white border border-[#D4AF37]/50 p-8 text-center max-w-xl mx-auto shadow-xs rounded-xs">
                <div class="w-12 h-12 bg-amber-50 text-[#D4AF37] mx-auto rounded-full flex items-center justify-center mb-3">
                    <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="font-display text-lg font-bold text-[#0E261C]">Patron Verification Required</h3>
                <p class="text-xs text-gray-600 mt-2 max-w-sm mx-auto leading-relaxed">
                    Order <strong class="font-mono text-gray-900">"{{ $reference }}"</strong> requires patron authentication. Please sign in to your patron account or verify the mobile number registered to this order.
                </p>
                <div class="mt-5 flex justify-center gap-3">
                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-[#0E261C] text-[#D4AF37] text-xs font-semibold uppercase tracking-wider rounded-xs hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors">
                        Sign In to Account
                    </a>
                </div>
            </div>
        @elseif($reference)
            <div class="bg-white border border-red-200 p-8 text-center max-w-xl mx-auto shadow-xs rounded-xs">
                <div class="w-12 h-12 bg-red-50 text-red-600 mx-auto rounded-full flex items-center justify-center mb-3">
                    <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="font-display text-lg font-bold text-gray-900">Order Not Found</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    We could not locate an order matching <strong class="font-mono text-gray-800">"{{ $reference }}"</strong>. Please check the order number or enter your registered phone number.
                </p>
                <div class="mt-4 flex justify-center gap-3">
                    <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-[#0E261C] text-[#D4AF37] text-xs font-semibold uppercase tracking-wider rounded-xs">
                        Browse My Orders
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

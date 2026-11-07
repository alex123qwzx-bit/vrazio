@extends('layouts.app')

@section('title', 'Order Confirmed — ' . $order->order_number)
@section('meta_description', 'Your VRAZIO fine jewellery commission has been successfully authenticated and placed in the master inscription docket.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        {{-- Success Banner --}}
        <div class="bg-white border border-[#D4AF37]/30 p-8 sm:p-12 shadow-sm text-center relative overflow-hidden">
            <div class="w-16 h-16 bg-[#0E261C] border border-[#D4AF37] rounded-full flex items-center justify-center mx-auto text-[#D4AF37] mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>

            <span class="text-xs uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">Atelier Commission Confirmed</span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-2">
                Consignment Reference: {{ $order->order_number }}
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-2 max-w-lg mx-auto">
                Thank you, {{ $order->client_name }}. Your fine jewellery piece has been reserved in our private vault and entered the master inscription and assay queue.
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('order.invoice.download', $order->order_number) }}" 
                   class="px-6 py-2.5 bg-[#D4AF37] hover:bg-[#c5a028] text-[#0E261C] text-xs uppercase tracking-wider font-bold shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Invoice (PDF)
                </a>
                <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank"
                   class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs uppercase tracking-wider font-semibold transition-all">
                    View &amp; Print Invoice
                </a>
                <a href="{{ route('order.track', $order->order_number) }}" 
                   class="px-6 py-2.5 bg-[#0E261C] hover:bg-[#D4AF37] text-[#D4AF37] hover:text-[#0E261C] text-xs uppercase tracking-wider font-semibold transition-all">
                    Track Order
                </a>
            </div>
        </div>

        {{-- Order Progress Timeline --}}
        <div class="mt-8 bg-white border border-[#D4AF37]/20 p-6 sm:p-8 shadow-sm">
            <h2 class="font-display text-base font-bold text-[#0E261C] mb-6 text-center">
                Order Delivery Progress
            </h2>

            @php
                $currentStepNum = $order->getCurrentStepNumber();
                $timelineStages = [
                    1 => ['label' => 'Order Placed', 'desc' => 'Confirmed & received'],
                    2 => ['label' => 'Packed', 'desc' => 'Safely boxed & sealed'],
                    3 => ['label' => 'Shipped', 'desc' => 'On the way with courier'],
                    4 => ['label' => 'Out for Delivery', 'desc' => 'Arrived in your city'],
                    5 => ['label' => 'Delivered', 'desc' => 'Delivered to your doorstep'],
                ];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-center">
                @foreach($timelineStages as $stepIndex => $stage)
                    @php $isDone = $stepIndex <= $currentStepNum; @endphp
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs mb-2 {{ $isDone ? 'bg-[#0E261C] text-[#D4AF37] border-2 border-[#D4AF37]' : 'bg-gray-100 text-gray-400 border border-gray-200' }}">
                            {{ $stepIndex }}
                        </div>
                        <h4 class="font-display text-xs font-bold {{ $isDone ? 'text-[#0E261C]' : 'text-gray-400' }}">
                            {{ $stage['label'] }}
                        </h4>
                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $stage['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Order Dossier Breakdown --}}
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Consignment Docket Items --}}
            <div class="bg-white border border-[#D4AF37]/20 p-6 shadow-sm">
                <h3 class="font-display text-sm font-bold text-[#0E261C] pb-3 border-b border-gray-100">
                    Items in Your Order
                </h3>
                <ul class="divide-y divide-gray-100 text-xs">
                    @foreach($order->items as $item)
                        <li class="py-3 flex justify-between">
                            <div>
                                <span class="font-semibold text-[#0E261C] block">{{ $item->product_title }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">SKU: {{ $item->product_sku }} &bull; Qty: {{ $item->quantity }}</span>
                            </div>
                            <span class="font-mono font-bold text-[#0E261C]">₹{{ number_format((float) $item->line_total, 2) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-1.5 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span class="font-mono">₹{{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>GST (3%):</span>
                        <span class="font-mono">₹{{ number_format((float) $order->tax_gst, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Insured Delivery:</span>
                        <span class="font-mono">{{ (float)$order->shipping_cost > 0 ? '₹'.number_format((float)$order->shipping_cost, 2) : 'Free' }}</span>
                    </div>
                    <div class="pt-2 border-t flex justify-between font-bold text-sm text-[#0E261C]">
                        <span>Total Paid:</span>
                        <span class="font-display font-bold text-base">₹{{ number_format((float) $order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Escrow & Dispatch Details --}}
            <div class="bg-white border border-[#D4AF37]/20 p-6 shadow-sm space-y-4 text-xs">
                <h3 class="font-display text-sm font-bold text-[#0E261C] pb-3 border-b border-gray-100">
                    Escrow &amp; Transit Details
                </h3>

                <div>
                    <span class="text-gray-400 uppercase tracking-wider text-[10px] block">Patron Contact</span>
                    <p class="font-semibold text-[#0E261C] mt-0.5">{{ $order->client_name }}</p>
                    <p class="text-gray-600">{{ $order->client_email }}</p>
                    <p class="text-gray-600 font-mono">+91 {{ $order->client_phone }} (Verified via SMS OTP)</p>
                </div>

                <div>
                    <span class="text-gray-400 uppercase tracking-wider text-[10px] block">Insured Destination</span>
                    @php $addr = $order->shipping_address ?? []; @endphp
                    <p class="text-gray-700 mt-0.5">
                        {{ $addr['street'] ?? '' }}<br>
                        {{ $addr['city'] ?? '' }}, {{ $addr['state'] ?? '' }} — {{ $addr['pincode'] ?? '' }}
                    </p>
                </div>

                <div>
                    <span class="text-gray-400 uppercase tracking-wider text-[10px] block">Razorpay Settlement Ref</span>
                    <p class="font-mono text-gray-700 mt-0.5">{{ $order->razorpay_payment_id ?? 'Awaiting Confirmation' }}</p>
                    <span class="inline-block mt-1 px-2 py-0.5 bg-green-50 text-green-800 text-[10px] font-bold uppercase tracking-wider border border-green-200">
                        {{ strtoupper($order->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('home') }}" class="inline-block text-xs uppercase tracking-[0.25em] text-[#0E261C] hover:text-[#D4AF37] font-semibold border-b border-[#0E261C]">
                &larr; Return to VRAZIO Atelier Home
            </a>
        </div>
    </div>
</div>
@endsection

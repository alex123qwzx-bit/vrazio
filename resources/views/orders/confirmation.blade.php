@extends('layouts.app')

@section('title', 'Order Confirmed — ' . $order->order_number)
@section('meta_description', 'Your VRAZIO fine jewellery commission has been successfully authenticated and placed in the master inscription docket.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        {{-- Success Banner --}}
        <div class="bg-white border border-[#D4AF37]/30 p-8 sm:p-12 shadow-sm text-center relative overflow-hidden">
            <img src="{{ asset('images/vrazio-logo.png') }}" alt="VRAZIO Brand Logo" class="w-28 h-28 mx-auto rounded-xl border-2 border-[#D4AF37] shadow-md mb-4 object-contain bg-[#0E261C]">

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
                    <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
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

        {{-- Mandatory WhatsApp Order Confirmation --}}
        @if($order->fulfillment_status !== 'cancelled')
            <div class="mt-8 bg-gradient-to-r from-[#0E261C] to-[#153a2b] border-2 border-[#D4AF37] p-6 sm:p-8 shadow-xl text-white relative rounded-xs">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-2.5 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#D4AF37]/20 border border-[#D4AF37] rounded-full text-[11px] font-bold text-[#D4AF37] uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            Mandatory Step &bull; Final Verification
                        </div>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#FAF8F5]">
                            Confirm Order on WhatsApp Support
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-200 max-w-xl font-light leading-relaxed">
                            To ensure genuine patron authenticity and prevent fraudulent consignment dispatches, our concierge requires a quick 1-tap confirmation on WhatsApp before releasing order <strong class="text-[#D4AF37] font-mono">#{{ $order->order_number }}</strong> for master vault packaging.
                        </p>
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs text-gray-300 pt-1">
                            <span>Concierge Desk: <strong class="text-[#D4AF37] font-mono">+91 87912 27210</strong></span>
                            <span class="hidden sm:inline">&bull;</span>
                            <span class="text-emerald-300 font-semibold">Instant Response 24/7</span>
                        </div>
                    </div>

                    <div class="shrink-0 text-center w-full md:w-auto">
                        <a href="https://wa.me/918791227210?text={{ urlencode('Hello VRAZIO Concierge, I genuinely confirm my order #' . $order->order_number . ' (Amount: Rs. ' . number_format((float)$order->total_amount, 2) . '). Please proceed with vault packaging and insured dispatch.') }}" 
                           target="_blank"
                           class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-[#25D366] hover:bg-[#20ba59] text-white font-bold text-xs uppercase tracking-[0.2em] transition-all shadow-lg hover:shadow-emerald-500/30 w-full md:w-auto rounded-xs">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            Confirm via WhatsApp
                        </a>
                        <p class="text-[10px] text-gray-300 mt-2 font-mono">
                            Tap to send pre-filled verification docket
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-8 bg-rose-50 border border-rose-200 p-6 text-center rounded-xs text-rose-800">
                <span class="text-xs uppercase tracking-widest font-bold block mb-1">Order Cancelled</span>
                <p class="text-sm font-medium">This order has been cancelled and vault reservation released. No further action is needed.</p>
            </div>
        @endif

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
                    <span class="text-gray-400 uppercase tracking-wider text-[10px] block">Enterprise Credentials</span>
                    <p class="font-semibold text-[#0E261C] mt-0.5">VRAZIO</p>
                    <p class="text-gray-600 font-mono text-[11px]">UDYAM: UDYAM-UP-59-0089382 &bull; 3% GST</p>
                </div>

                <div>
                    <span class="text-gray-400 uppercase tracking-wider text-[10px] block">Payment &amp; Settlement Ref</span>
                    <p class="font-mono text-gray-700 mt-0.5">{{ $order->razorpay_payment_id ?? 'Awaiting Confirmation' }}</p>
                    <span class="inline-block mt-1 px-2 py-0.5 bg-green-50 text-green-800 text-[10px] font-bold uppercase tracking-wider border border-green-200">
                        {{ strtoupper($order->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        @if($order->canBeCancelled())
            <div class="mt-10 text-center">
                <form action="{{ route('order.cancel', $order->order_number) }}" method="POST"
                      onsubmit="return confirm('Are you sure you wish to cancel order #{{ $order->order_number }}? Reserved atelier items will be restored.');"
                      class="inline-block">
                    @csrf
                    <button type="submit" 
                            class="text-xs text-rose-700 hover:text-rose-900 underline font-medium hover:font-bold">
                        Need to cancel this order? Click here to cancel
                    </button>
                </form>
            </div>
        @endif

        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="inline-block text-xs uppercase tracking-[0.25em] text-[#0E261C] hover:text-[#D4AF37] font-semibold border-b border-[#0E261C]">
                &larr; Return to VRAZIO Atelier Home
            </a>
        </div>
    </div>
</div>
@endsection

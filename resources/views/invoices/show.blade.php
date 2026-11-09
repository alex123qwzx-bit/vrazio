<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} | VRAZIO Atelier</title>

    {{-- Favicon & App Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&family=Alex+Brush&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #EFECE6;
            color: #1a1a1a;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        .font-script {
            font-family: 'Alex Brush', cursive;
        }

        .invoice-sheet {
            background-color: #FAF8F5;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(212, 175, 55, 0.15);
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        @media print {
            body {
                background-color: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-sheet {
                box-shadow: none !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
            }
        }
    </style>
</head>
<body class="py-8 sm:py-12 px-4 sm:px-6">

    {{-- Top Action Bar (Hidden when Printing) --}}
    <div class="max-w-[820px] mx-auto mb-6 flex flex-wrap items-center justify-between gap-4 no-print bg-[#0E261C] text-[#FAF8F5] p-4 rounded-lg shadow-md border border-[#D4AF37]/30">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.index') }}" class="text-xs uppercase tracking-wider text-[#D4AF37] hover:underline flex items-center gap-1 font-semibold">
                &larr; Back to My Orders
            </a>
            <span class="text-gray-500">|</span>
            <span class="text-xs text-gray-300">Invoice: <strong class="text-white font-mono">#{{ $order->order_number }}</strong></span>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" 
                    class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs uppercase tracking-wider font-semibold rounded transition-colors flex items-center gap-2">
                <svg width="16" height="16" class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Invoice
            </button>

            <a href="{{ route('order.invoice.download', $order->order_number) }}" 
               class="px-5 py-2 bg-[#D4AF37] hover:bg-[#c5a028] text-[#0E261C] text-xs uppercase tracking-wider font-bold rounded shadow-sm transition-all flex items-center gap-2">
                <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download PDF
            </a>
        </div>
    </div>

    {{-- Main Printable Invoice Sheet --}}
    <div class="invoice-sheet p-8 sm:p-12 md:p-14">
        {{-- Background Monogram Watermark --}}
        <div class="absolute right-6 bottom-32 text-[#D4AF37] opacity-[0.035] font-display text-[320px] select-none pointer-events-none leading-none">
            V
        </div>

        {{-- Header Section: Brand & Invoice Meta --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-8 border-b border-[#D4AF37]/20">
            {{-- Brand Logo & Taglines --}}
            <div class="flex items-center sm:items-start gap-4 sm:gap-5">
                <img src="{{ asset('images/vrazio-logo.png') }}" alt="VRAZIO Emblem" class="w-20 h-20 sm:w-24 sm:h-24 object-contain rounded-full border border-[#D4AF37]/40 shadow-sm shrink-0 bg-[#0E261C]">
                <div>
                    <div class="font-display text-3xl sm:text-4xl font-bold tracking-[0.2em] text-[#0E261C]">
                        VR<span class="text-[#D4AF37]">A</span>ZIO
                    </div>
                    <div class="text-[11px] uppercase tracking-[0.3em] text-[#D4AF37] font-bold mt-1">
                        — ATELIER &amp; HAUTE COUTURE —
                    </div>
                    <div class="text-[11px] text-gray-700 mt-2 space-y-0.5">
                        <div class="font-semibold text-[#0E261C]">NAME OF ENTERPRISE: <span class="font-bold tracking-wide">VRAZIO</span></div>
                        <div class="font-mono text-gray-700 text-xs">UDYAM REG. NO.: <span class="font-bold text-[#0E261C]">UDYAM-UP-59-0089382</span></div>
                        <div class="text-gray-500 text-[10px] uppercase tracking-wider">HSN 7113 • 3% GST PRECIOUS JEWELLERY</div>
                    </div>
                </div>
            </div>

            {{-- Invoice Title & Meta Details --}}
            <div class="sm:text-right">
                <h1 class="font-display text-3xl sm:text-4xl tracking-[0.2em] text-[#0E261C] uppercase font-normal">
                    INVOICE
                </h1>
                <div class="w-16 h-0.5 bg-[#D4AF37] sm:ml-auto mt-1 mb-4"></div>

                <div class="space-y-1 text-xs sm:text-[13px]">
                    <div class="flex sm:justify-end gap-3">
                        <span class="text-gray-500 uppercase tracking-wider text-[11px]">INVOICE NO:</span>
                        <span class="font-mono font-bold text-[#0E261C]">VRZ-INV-{{ str_replace('VRZ-', '', $order->order_number) }}</span>
                    </div>
                    <div class="flex sm:justify-end gap-3">
                        <span class="text-gray-500 uppercase tracking-wider text-[11px]">DATE:</span>
                        <span class="font-bold text-[#0E261C]">{{ $order->created_at ? $order->created_at->format('d M Y') : date('d M Y') }}</span>
                    </div>
                    <div class="flex sm:justify-end gap-3">
                        <span class="text-gray-500 uppercase tracking-wider text-[11px]">ORDER NO:</span>
                        <span class="font-mono font-bold text-[#0E261C]">#{{ $order->order_number }}</span>
                    </div>
                    <div class="flex sm:justify-end gap-3">
                        <span class="text-gray-500 uppercase tracking-wider text-[11px]">PAYMENT:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-[#0E261C] text-[#D4AF37] uppercase">
                            {{ $order->payment_status ?? 'PAID' }} ({{ strtoupper($order->payment_gateway ?? 'ONLINE') }})
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- BILL TO Section --}}
        <div class="py-8">
            <span class="text-[10px] uppercase tracking-[0.25em] text-gray-500 font-bold block mb-1">
                BILL TO
            </span>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-[#0E261C]">
                {{ $order->client_name }}
            </h2>
            <div class="text-xs sm:text-sm text-gray-600 mt-2 leading-relaxed">
                @php
                    $addr = is_array($order->shipping_address) ? $order->shipping_address : json_decode((string)$order->shipping_address, true);
                @endphp
                @if(!empty($addr['street'])) <div>{{ $addr['street'] }}</div> @endif
                @if(!empty($addr['city']) || !empty($addr['state'])) 
                    <div>{{ $addr['city'] ?? '' }}{{ !empty($addr['city']) && !empty($addr['state']) ? ', ' : '' }}{{ $addr['state'] ?? '' }} {{ $addr['pincode'] ?? '' }}</div>
                @endif
                <div class="mt-1 text-gray-500 flex flex-wrap items-center gap-2">
                    <span>Phone: <strong class="text-gray-800">{{ $order->client_phone }}</strong></span>
                    @if(!empty($order->client_email))
                        <span class="text-[#D4AF37]">•</span>
                        <span>Email: <strong class="text-gray-800">{{ $order->client_email }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Product Items Table --}}
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#0E261C] text-[#FAF8F5] text-[10px] uppercase tracking-wider">
                        <th class="py-3.5 px-4 font-semibold text-center w-12">#</th>
                        <th class="py-3.5 px-4 font-semibold">PRODUCT DESCRIPTION</th>
                        <th class="py-3.5 px-4 font-semibold text-center w-16">QTY</th>
                        <th class="py-3.5 px-4 font-semibold text-right w-32">UNIT PRICE</th>
                        <th class="py-3.5 px-4 font-semibold text-right w-32">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E2D8] text-xs sm:text-sm">
                    @foreach($order->items as $idx => $item)
                        <tr class="hover:bg-[#FAF8F5]/80 transition-colors">
                            <td class="py-4 px-4 text-center text-gray-400 font-mono text-xs">{{ $idx + 1 }}</td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-[#0E261C] text-sm">{{ $item->product_title }}</div>
                                @if(!empty($item->product_sku))
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">SKU: {{ $item->product_sku }}</div>
                                @endif
                                @if(!empty($item->size))
                                    <div class="text-[11px] text-[#D4AF37] font-semibold mt-0.5">Size / Fit: {{ $item->size }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center font-mono">{{ $item->quantity }}</td>
                            <td class="py-4 px-4 text-right font-mono text-gray-700">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                            <td class="py-4 px-4 text-right font-mono font-bold text-[#0E261C]">₹{{ number_format((float)$item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary & Warranty Card Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start mb-10">
            {{-- Left Side: Script Thank You & Warranty Overview --}}
            <div class="md:col-span-7 space-y-4">
                <div>
                    <div class="font-script text-4xl sm:text-5xl text-[#0E261C] leading-tight">
                        Thank You
                    </div>
                    <div class="text-[10px] uppercase tracking-[0.25em] text-gray-500 font-bold mt-1">
                        FOR CHOOSING VRAZIO ATELIER
                    </div>
                </div>

                {{-- Warranty Overview Card --}}
                <div class="bg-white border border-[#E2D9CC] p-4 sm:p-5 rounded-md shadow-xs relative">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#0E261C] uppercase tracking-wider mb-2">
                        <span class="text-[#D4AF37] text-base leading-none">✦</span>
                        <span>Warranty &amp; Authenticity Guarantee</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Every authentic VRAZIO creation is BIS hallmarked and covered under our Comprehensive Lifetime Craftsmanship Warranty. Includes complimentary gem inspection, prong tightening, and ultrasonic cleaning.
                    </p>
                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                        <span class="text-gray-500">Need support or claim?</span>
                        <a href="{{ route('orders.index') }}" class="text-[#D4AF37] font-bold hover:underline">
                            vrazio.com/orders &rarr;
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Side: Financial Totals with Left Gold Accent Line --}}
            <div class="md:col-span-5 border-l-2 border-[#D4AF37] pl-6 space-y-2.5">
                <div class="flex justify-between text-xs sm:text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-mono font-semibold text-gray-900">₹{{ number_format((float)($order->subtotal ?? $order->total_amount), 2) }}</span>
                </div>
                <div class="flex justify-between text-xs sm:text-sm text-gray-600">
                    <span>Tax / GST (3%)</span>
                    <span class="font-mono font-semibold text-gray-900">₹{{ number_format((float)($order->tax_gst ?? 0), 2) }}</span>
                </div>
                <div class="flex justify-between text-xs sm:text-sm text-gray-600">
                    <span>Insured Shipping</span>
                    <span class="font-mono font-semibold text-gray-900">
                        @if((float)$order->shipping_cost > 0)
                            ₹{{ number_format((float)$order->shipping_cost, 2) }}
                        @else
                            <span class="text-emerald-700 font-bold">FREE</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between text-xs sm:text-sm text-gray-600">
                    <span>Discount</span>
                    <span class="font-mono font-semibold text-gray-900">₹0.00</span>
                </div>

                <div class="border-t border-[#D4AF37]/50 my-2 pt-2"></div>

                <div class="flex justify-between items-baseline">
                    <span class="font-display text-lg font-bold text-[#0E261C]">Total</span>
                    <span class="font-display text-2xl font-bold text-[#0E261C]">
                        ₹{{ number_format((float)$order->total_amount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Bottom Footer Bar --}}
        <div class="pt-6 border-t border-[#D4AF37]/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <div class="flex flex-wrap items-center gap-3">
                <span class="flex items-center gap-1">
                    <svg width="14" height="14" class="w-3.5 h-3.5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.102-.115.434-.506.549-.68.116-.173.232-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                    +91 87912 27210
                </span>
                <span class="text-[#D4AF37]">•</span>
                <span>vrazioofficial@gmail.com</span>
                <span class="text-[#D4AF37]">•</span>
                <span>@vrazio.official</span>
            </div>

            <div class="text-[10px] uppercase tracking-widest text-[#D4AF37] font-semibold">
                ✦ TIMELESS BEAUTY, ALWAYS YOURS
            </div>
        </div>
    </div>

</body>
</html>

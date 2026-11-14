<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice #{{ $order->order_number }} | VRAZIO Atelier</title>

    {{-- Favicon & App Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&family=Alex+Brush&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #EFECE6;
            color: #1a1a1a;
            padding: 24px 12px;
            -webkit-font-smoothing: antialiased;
        }

        .invoice-wrapper {
            max-width: 820px;
            margin: 0 auto;
        }

        /* Top Action Bar */
        .action-bar {
            background-color: #0E261C;
            color: #FAF8F5;
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid rgba(212, 175, 55, 0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .action-bar-left {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
        }

        .action-bar-left a {
            color: #D4AF37;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-bar-left a:hover {
            text-decoration: underline;
        }

        .action-bar-sep {
            color: #555555;
        }

        .action-bar-num {
            color: #E0E0E0;
        }

        .action-bar-num strong {
            color: #FFFFFF;
            font-family: monospace;
        }

        .action-bar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-print {
            padding: 7px 14px;
            background-color: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            border: none;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-print:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .btn-download {
            padding: 7px 16px;
            background-color: #D4AF37;
            color: #0E261C;
            border: none;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background 0.2s;
        }

        .btn-download:hover {
            background-color: #c5a028;
        }

        /* Luxury Double Gold Border Invoice Sheet */
        .invoice-card-outer {
            border: 2px solid #C5A059;
            background-color: #FFFFFF;
            padding: 4px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 2px;
        }

        .invoice-card-inner {
            border: 1px solid #C5A059;
            background-color: #FAF8F5;
            padding: 24px 28px;
        }

        /* Header Emblem & Brand Heading */
        .brand-emblem-wrap {
            text-align: center;
            margin-bottom: 2px;
        }

        .brand-emblem-img {
            width: 52px;
            height: 52px;
            max-width: 52px;
            max-height: 52px;
            border-radius: 50%;
            display: block;
            margin: 0 auto 6px auto;
            border: 1px solid rgba(212, 175, 55, 0.6);
            background-color: #0E261C;
            object-fit: contain;
        }

        .brand-title {
            text-align: center;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 26px;
            font-weight: bold;
            color: #0E261C;
            letter-spacing: 6px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .brand-tagline {
            text-align: center;
            font-size: 9px;
            letter-spacing: 3px;
            color: #777777;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 4px;
        }

        .tax-invoice-heading {
            text-align: center;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 19px;
            font-weight: bold;
            color: #0E261C;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 12px;
            margin-bottom: 18px;
        }

        /* Two-Column Metadata */
        .meta-layout-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .meta-col-left {
            width: 52%;
            vertical-align: top;
            padding-right: 14px;
            font-size: 12px;
            line-height: 1.5;
            color: #222222;
        }

        .meta-col-right {
            width: 48%;
            vertical-align: top;
            padding-left: 14px;
            text-align: right;
            font-size: 12px;
            line-height: 1.6;
        }

        .seller-name {
            font-weight: bold;
            font-size: 13px;
            color: #0E261C;
            margin-bottom: 2px;
        }

        .billto-wrap {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #E8E2D8;
        }

        .billto-title {
            font-weight: bold;
            font-size: 11px;
            color: #0E261C;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .billto-name {
            font-weight: bold;
            font-size: 13px;
            color: #0E261C;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Items Table */
        .table-responsive {
            overflow-x: auto;
            margin-top: 14px;
            margin-bottom: 16px;
            -webkit-overflow-scrolling: touch;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #CCCCCC;
            font-size: 12px;
            min-width: 520px;
        }

        .items-table thead tr {
            background-color: #0E261C;
            color: #FFFFFF;
        }

        .items-table th {
            padding: 8px 8px;
            border: 1px solid #0E261C;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table tbody tr {
            background-color: #FFFFFF;
        }

        .items-table td {
            padding: 9px 8px;
            border: 1px solid #CCCCCC;
            vertical-align: middle;
            color: #222222;
        }

        .item-title {
            font-weight: bold;
            color: #0E261C;
            font-size: 12.5px;
        }

        .item-meta {
            font-size: 11px;
            color: #666666;
            font-family: monospace;
            margin-top: 2px;
        }

        /* Financial Summary */
        .summary-wrapper-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1.55;
        }

        .summary-table td {
            padding: 2px 0;
        }

        .summary-table .s-lbl {
            text-align: right;
            padding-right: 14px;
            color: #333333;
            width: 60%;
        }

        .summary-table .s-val {
            text-align: right;
            font-family: monospace;
            font-weight: 500;
            color: #111111;
            width: 40%;
        }

        .total-emerald-bar {
            background-color: #0E261C;
            color: #FFFFFF;
            padding: 10px 14px;
            margin-top: 8px;
            border-radius: 2px;
            text-align: right;
        }

        .total-amount-row {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #FFFFFF;
        }

        .total-words-row {
            font-size: 10.5px;
            color: #E8D5A0;
            margin-top: 3px;
            line-height: 1.3;
        }

        /* Footer Notes */
        .footer-legal-notes {
            font-size: 11px;
            line-height: 1.5;
            color: #222222;
            margin-top: 14px;
            border-top: 1px dotted #C5A059;
            padding-top: 10px;
        }

        .signature-row {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            color: #0E261C;
            margin-top: 16px;
            padding-right: 14px;
        }

        .signoff-centered {
            text-align: center;
            margin-top: 16px;
            padding-top: 4px;
        }

        .signoff-script-text {
            font-family: 'Alex Brush', cursive, serif;
            font-size: 24px;
            color: #0E261C;
            line-height: 1.1;
        }

        .signoff-atelier-text {
            font-family: 'Inter', sans-serif;
            font-size: 10.5px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-left: 6px;
        }

        .signoff-timeless {
            font-size: 9px;
            letter-spacing: 2.5px;
            color: #777777;
            text-transform: uppercase;
            margin-top: 3px;
            font-weight: bold;
        }

        /* Print Media Styles */
        @media print {
            body {
                background-color: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-card-outer {
                box-shadow: none !important;
                max-width: 100% !important;
                border: 2px solid #C5A059 !important;
            }
            .invoice-card-inner {
                border: 1px solid #C5A059 !important;
                background-color: #FAF8F5 !important;
            }
        }

        /* Mobile Viewport Responsiveness */
        @media (max-width: 640px) {
            body {
                padding: 10px 6px !important;
            }

            .invoice-card-inner {
                padding: 16px 12px !important;
            }

            .action-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
            }

            .action-bar-right {
                width: 100% !important;
            }

            .btn-print, .btn-download {
                flex: 1 !important;
                justify-content: center !important;
                text-align: center !important;
            }

            .meta-col-left, .meta-col-right {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .meta-col-right {
                text-align: left !important;
                margin-top: 12px !important;
                padding-top: 10px !important;
                border-top: 1px solid #E8E2D8 !important;
            }

            .summary-spacer {
                display: none !important;
            }

            .summary-content {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>

@php
    $logoPath = public_path('images/vrazio-card-icon.png');
    if (!file_exists($logoPath)) {
        $logoPath = public_path('images/vrazio-logo.png');
    }
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    $addr = is_array($order->shipping_address) ? $order->shipping_address : json_decode((string)$order->shipping_address, true);
    $stateName = $order->getCustomerStateName();
    $stateCode = $order->getCustomerStateCode();
@endphp

<div class="invoice-wrapper">

    {{-- Top Action Bar (Hidden when Printing) --}}
    <div class="action-bar no-print">
        <div class="action-bar-left">
            <a href="{{ route('orders.index') }}">&larr; Back to My Orders</a>
            <span class="action-bar-sep">|</span>
            <span class="action-bar-num">Invoice: <strong>#{{ $order->order_number }}</strong></span>
        </div>

        <div class="action-bar-right">
            <button onclick="window.print()" class="btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D4AF37;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>

            <a href="{{ route('order.invoice.download', $order->order_number) }}" class="btn-download">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download PDF
            </a>
        </div>
    </div>

    {{-- Luxury Double-Border Invoice Sheet --}}
    <div class="invoice-card-outer">
        <div class="invoice-card-inner">

            {{-- Centered Header Emblem & Brand Title --}}
            <div class="brand-emblem-wrap">
                @if(!empty($logoBase64))
                    <img src="data:image/png;base64,{{ $logoBase64 }}" 
                         width="52" 
                         height="52" 
                         class="brand-emblem-img" 
                         alt="VRAZIO Emblem" />
                @else
                    <img src="{{ asset('images/vrazio-card-icon.png') }}" 
                         width="52" 
                         height="52" 
                         class="brand-emblem-img" 
                         alt="VRAZIO Emblem" />
                @endif
            </div>

            <div class="brand-title">V R A Z I O</div>
            <div class="brand-tagline">LUXURY &bull; HANDCRAFTED</div>
            <div class="tax-invoice-heading">TAX INVOICE</div>

            {{-- Two-Column Metadata Section: Seller + Bill To vs Invoice Metadata --}}
            <table class="meta-layout-table">
                <tr>
                    {{-- Left Column: Seller Details & Bill To --}}
                    <td class="meta-col-left">
                        <div class="seller-name">VRAZIO ATELIER</div>
                        <div><strong>UDYAM:</strong> <span style="font-family: monospace;">UDYAM-UP-59-0089382</span></div>
                        <div><strong>GSTIN:</strong> <span style="font-family: monospace;">08AAAFV1234F1Z5</span></div>
                        <div><strong>Address:</strong> Civil Lines, Moradabad, Uttar Pradesh - 244001</div>
                        <div><strong>Email:</strong> vrazioofficial@gmail.com</div>
                        <div><strong>WhatsApp:</strong> +91 87912 27210</div>

                        <div class="billto-wrap">
                            <div class="billto-title">BILL TO:</div>
                            <div class="billto-name">{{ strtoupper($order->client_name) }}</div>
                            @if(!empty($addr['street']))
                                <div style="text-transform: uppercase;">{{ strtoupper($addr['street']) }}</div>
                            @endif
                            @if(!empty($addr['city']) || !empty($addr['state']))
                                <div>{{ $addr['city'] ?? '' }}{{ !empty($addr['city']) && !empty($addr['state']) ? ', ' : '' }}{{ $addr['state'] ?? '' }}{{ !empty($addr['pincode']) ? ' - ' . $addr['pincode'] : '' }}</div>
                            @endif
                            <div><strong>Ph:</strong> {{ $order->client_phone }}</div>
                            <div><strong>State Code:</strong> {{ $stateCode }} ({{ $stateName }})</div>
                        </div>
                    </td>

                    {{-- Right Column: Invoice Metadata --}}
                    <td class="meta-col-right">
                        <div><strong style="color: #0E261C;">INVOICE NO:</strong> <span style="font-family: monospace; font-weight: bold;">VRZ-INV-{{ str_replace('VRZ-', '', $order->order_number) }}</span></div>
                        <div><strong style="color: #0E261C;">DATE:</strong> {{ $order->created_at ? $order->created_at->format('d M Y') : date('d M Y') }}</div>
                        <div><strong style="color: #0E261C;">ORDER NO:</strong> <span style="font-family: monospace; font-weight: bold;">#{{ $order->order_number }}</span></div>
                        <div><strong style="color: #0E261C;">PAYMENT:</strong> <span style="font-weight: bold; text-transform: uppercase;">{{ $order->payment_status ?? 'PENDING' }} ({{ strtoupper($order->payment_gateway ?? 'COD') }})</span></div>
                        <div><strong style="color: #0E261C;">PLACE OF SUPPLY:</strong> <span style="font-weight: bold;">{{ $order->getPlaceOfSupply() }}</span></div>
                    </td>
                </tr>
            </table>

            {{-- Products Table --}}
            <div class="table-responsive">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 6%; text-align: center;">#</th>
                            <th style="width: 50%; text-align: left;">PRODUCT DESCRIPTION</th>
                            <th style="width: 8%; text-align: center;">QTY</th>
                            <th style="width: 18%; text-align: right;">UNIT PRICE (₹)</th>
                            <th style="width: 18%; text-align: right;">TOTAL (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $idx => $item)
                            <tr>
                                <td style="text-align: center; font-family: monospace; color: #555555;">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="item-title">{{ $item->product_title }}</div>
                                    <div class="item-meta">
                                        SKU: {{ $item->product_sku }} | HSN: {{ $item->getHsnCode() }} | GST: {{ $item->getGstRateFormatted() }}@if(!empty($item->size)) | Size: {{ $item->size }}@endif
                                    </div>
                                </td>
                                <td style="text-align: center; font-family: monospace;">{{ $item->quantity }}</td>
                                <td style="text-align: right; font-family: monospace;">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                                <td style="text-align: right; font-family: monospace; font-weight: bold; color: #0E261C;">₹{{ number_format((float)$item->line_total, 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Empty row for visual spacing matching Image 3 if 1 or 2 items --}}
                        @if($order->items->count() < 3)
                            <tr>
                                <td style="height: 18px;">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Summary & Total Block --}}
            <table class="summary-wrapper-table">
                <tr>
                    <td class="summary-spacer" style="width: 48%;"></td>
                    <td class="summary-content" style="width: 52%; vertical-align: top;">
                        <table class="summary-table">
                            <tr>
                                <td class="s-lbl">Subtotal:</td>
                                <td class="s-val">₹{{ number_format((float)($order->subtotal ?? $order->total_amount), 2) }}</td>
                            </tr>

                            @if(!$order->isInterState())
                                <tr>
                                    <td class="s-lbl">CGST ({{ number_format($order->getCgstRate(), 1) }}%):</td>
                                    <td class="s-val">₹{{ number_format($order->getCgstAmount(), 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="s-lbl">SGST ({{ number_format($order->getSgstRate(), 1) }}%):</td>
                                    <td class="s-val">₹{{ number_format($order->getSgstAmount(), 2) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="s-lbl">IGST ({{ number_format($order->getIgstRate(), 1) }}%):</td>
                                    <td class="s-val">₹{{ number_format($order->getIgstAmount(), 2) }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td class="s-lbl">Insured Shipping:</td>
                                <td class="s-val">
                                    @if((float)$order->shipping_cost > 0)
                                        ₹{{ number_format((float)$order->shipping_cost, 2) }}
                                    @else
                                        FREE
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="s-lbl">Discount:</td>
                                <td class="s-val">
                                    @if((float)($order->discount_amount ?? 0) > 0)
                                        -₹{{ number_format((float)$order->discount_amount, 2) }}
                                    @else
                                        ₹0.00
                                    @endif
                                </td>
                            </tr>
                        </table>

                        {{-- Deep Emerald Total Bar --}}
                        <div class="total-emerald-bar">
                            <div class="total-amount-row">
                                TOTAL (INR): &nbsp;&nbsp; ₹{{ number_format((float)$order->total_amount, 2) }}
                            </div>
                            <div class="total-words-row">
                                In Words: {{ $order->getAmountInWords() }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Footer Notes: Bank Details & Warranty --}}
            <div class="footer-legal-notes">
                <div><strong>BANK DETAILS:</strong> VRAZIO ATELIER, ICICI BANK, A/C No: 123450506781, IFSC: ICIC0001234</div>
                <div style="margin-top: 3px;">
                    <strong>WARRANTY:</strong> Each accessory is backed by VRAZIO's authentic craftsmanship guarantee. Track warranty at <a href="{{ route('orders.index') }}" style="color: #0E261C; text-decoration: underline;">vrazio.com/orders</a>
                </div>
            </div>

            {{-- Signature Block --}}
            <div class="signature-row">
                For VRAZIO
            </div>

            {{-- Centered Bottom Signoff --}}
            <div class="signoff-centered">
                <div class="signoff-script-text">
                    Thank You <span class="signoff-atelier-text">FOR CHOOSING VRAZIO ATELIER</span>
                </div>
                <div class="signoff-timeless">
                    TIMELESS BEAUTY, ALWAYS YOURS
                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>

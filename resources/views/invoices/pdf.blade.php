<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $order->order_number }} | VRAZIO</title>
    <style>
        @page {
            margin: 25px 35px 35px 35px;
            size: a4 portrait;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Serif', 'Georgia', 'Times New Roman', serif;
            color: #1a1a1a;
            background-color: #FAF8F5;
            font-size: 11px;
            line-height: 1.4;
            position: relative;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            bottom: 120px;
            right: 40px;
            font-size: 320px;
            font-family: 'DejaVu Serif', serif;
            color: #D4AF37;
            opacity: 0.05;
            z-index: -100;
            line-height: 1;
        }

        .container {
            width: 100%;
            padding: 10px 15px;
        }

        /* Header Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .header-table td {
            vertical-align: top;
        }

        .brand-logo {
            font-size: 34px;
            font-weight: bold;
            color: #0E261C;
            letter-spacing: 4px;
            line-height: 1.1;
        }

        .brand-logo .gold {
            color: #D4AF37;
        }

        .brand-subtitle {
            font-size: 10px;
            letter-spacing: 3px;
            color: #D4AF37;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
        }

        .brand-tagline {
            font-size: 8px;
            letter-spacing: 2px;
            color: #777777;
            text-transform: uppercase;
        }

        .invoice-title-block {
            text-align: right;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: normal;
            letter-spacing: 4px;
            color: #0E261C;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .invoice-title-line {
            width: 60px;
            height: 2px;
            background-color: #D4AF37;
            float: right;
            margin-bottom: 12px;
        }

        .clear {
            clear: both;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 6px;
        }

        .meta-table td {
            padding: 2px 0;
            text-align: right;
        }

        .meta-table .label {
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-right: 12px;
            font-size: 9px;
        }

        .meta-table .val {
            font-weight: bold;
            color: #0E261C;
        }

        /* Bill To Section */
        .billto-section {
            margin-bottom: 22px;
        }

        .billto-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #888888;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .customer-name {
            font-size: 16px;
            font-weight: bold;
            color: #0E261C;
            margin-bottom: 4px;
        }

        .customer-address {
            color: #555555;
            font-size: 10px;
            line-height: 1.5;
        }

        /* Products Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #0E261C;
            color: #FAF8F5;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 9px 10px;
            font-weight: bold;
        }

        .items-table th.center, .items-table td.center {
            text-align: center;
        }

        .items-table th.right, .items-table td.right {
            text-align: right;
        }

        .items-table td {
            padding: 10px 10px;
            border-bottom: 1px solid #E8E2D8;
            font-size: 10.5px;
            color: #2b2b2b;
            vertical-align: middle;
        }

        .item-title {
            font-weight: bold;
            color: #0E261C;
            font-size: 11px;
        }

        .item-sku {
            font-size: 9px;
            color: #888888;
            margin-top: 2px;
        }

        /* Summary Section */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .summary-table td {
            vertical-align: top;
        }

        .thankyou-box {
            width: 58%;
            padding-right: 25px;
        }

        .thank-you-script {
            font-family: 'DejaVu Serif', cursive, serif;
            font-style: italic;
            font-size: 32px;
            color: #0E261C;
            line-height: 1.1;
            margin-bottom: 2px;
        }

        .thank-you-sub {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: #888888;
            font-weight: bold;
            margin-bottom: 15px;
        }

        /* Warranty Overview Box */
        .warranty-card {
            border: 1px solid #E2D9CC;
            background-color: #FFFFFF;
            padding: 10px 12px;
            border-radius: 3px;
        }

        .warranty-card-title {
            font-size: 10px;
            font-weight: bold;
            color: #0E261C;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .warranty-card-title .icon {
            color: #D4AF37;
        }

        .warranty-card-text {
            font-size: 9px;
            color: #555555;
            line-height: 1.45;
        }

        .warranty-link {
            font-size: 8.5px;
            color: #D4AF37;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Totals Block */
        .totals-box {
            width: 42%;
            border-left: 2px solid #D4AF37;
            padding-left: 18px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .totals-table td {
            padding: 4px 0;
        }

        .totals-table .t-label {
            color: #555555;
            text-align: left;
        }

        .totals-table .t-val {
            text-align: right;
            font-weight: bold;
            color: #0E261C;
        }

        .totals-divider {
            border-top: 1px solid #D4AF37;
            margin: 6px 0;
        }

        .totals-total-row td {
            padding-top: 8px;
        }

        .totals-total-label {
            font-size: 14px;
            font-weight: bold;
            color: #0E261C;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .totals-total-val {
            font-size: 16px;
            font-weight: bold;
            color: #0E261C;
            text-align: right;
        }

        /* Footer */
        .invoice-footer {
            border-top: 1px solid #D4AF37;
            padding-top: 12px;
            margin-top: 15px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            color: #666666;
        }

        .footer-table td {
            vertical-align: middle;
        }

        .footer-contacts {
            text-align: left;
        }

        .footer-tagline {
            text-align: right;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #888888;
            font-size: 8px;
        }

        .footer-bullet {
            color: #D4AF37;
            padding: 0 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- Watermark Monogram --}}
    <div class="watermark">V</div>

    <div class="container">
        {{-- Header: Brand & Invoice Meta --}}
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <div class="brand-logo">VR<span class="gold">A</span>ZIO</div>
                    <div class="brand-subtitle">— ATELIER &amp; HAUTE COUTURE —</div>
                    <div class="brand-tagline">TIMELESS BEAUTY, ALWAYS YOURS</div>
                </td>
                <td style="width: 45%;">
                    <div class="invoice-title-block">
                        <div class="invoice-title">INVOICE</div>
                        <div class="invoice-title-line"></div>
                        <div class="clear"></div>

                        <table class="meta-table">
                            <tr>
                                <td class="label">INVOICE NO:</td>
                                <td class="val">VRZ-INV-{{ str_replace('VRZ-', '', $order->order_number) }}</td>
                            </tr>
                            <tr>
                                <td class="label">DATE:</td>
                                <td class="val">{{ $order->created_at ? $order->created_at->format('d M Y') : date('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="label">ORDER NO:</td>
                                <td class="val">#{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <td class="label">PAYMENT:</td>
                                <td class="val" style="color: #0E261C;">{{ strtoupper($order->payment_status ?? 'PAID') }} ({{ strtoupper($order->payment_gateway ?? 'ONLINE') }})</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Bill To Section --}}
        <div class="billto-section">
            <div class="billto-label">BILL TO</div>
            <div class="customer-name">{{ $order->client_name }}</div>
            <div class="customer-address">
                @php
                    $addr = is_array($order->shipping_address) ? $order->shipping_address : json_decode((string)$order->shipping_address, true);
                @endphp
                @if(!empty($addr['street'])) {{ $addr['street'] }}<br> @endif
                @if(!empty($addr['city']) || !empty($addr['state'])) 
                    {{ $addr['city'] ?? '' }}{{ !empty($addr['city']) && !empty($addr['state']) ? ', ' : '' }}{{ $addr['state'] ?? '' }} 
                @endif
                @if(!empty($addr['pincode'])) - {{ $addr['pincode'] }} @endif
                <br>
                <span>Phone: {{ $order->client_phone }}</span>
                @if(!empty($order->client_email))
                    <span style="color: #D4AF37;"> • </span><span>Email: {{ $order->client_email }}</span>
                @endif
            </div>
        </div>

        {{-- Products Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th class="center" style="width: 6%;">#</th>
                    <th style="width: 48%;">PRODUCT DESCRIPTION</th>
                    <th class="center" style="width: 10%;">QTY</th>
                    <th class="right" style="width: 18%;">UNIT PRICE</th>
                    <th class="right" style="width: 18%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td>
                            <div class="item-title">{{ $item->product_title }}</div>
                            @if(!empty($item->product_sku))
                                <div class="item-sku">SKU: {{ $item->product_sku }}</div>
                            @endif
                            @if(!empty($item->size))
                                <div class="item-sku" style="color: #8C6D23; font-weight: bold;">Size: {{ $item->size }}</div>
                            @endif
                        </td>
                        <td class="center">{{ $item->quantity }}</td>
                        <td class="right">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                        <td class="right" style="font-weight: bold;">₹{{ number_format((float)$item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary & Warranty Section --}}
        <table class="summary-table">
            <tr>
                {{-- Thank You & Warranty Overview --}}
                <td class="thankyou-box">
                    <div class="thank-you-script">Thank You</div>
                    <div class="thank-you-sub">FOR CHOOSING VRAZIO ATELIER</div>

                    {{-- Warranty Overview --}}
                    <div class="warranty-card">
                        <div class="warranty-card-title">
                            <span class="icon">✦</span> Warranty &amp; Authenticity Guarantee
                        </div>
                        <div class="warranty-card-text">
                            Every piece is crafted to haute joaillerie standards, BIS hallmarked, and backed by VRAZIO's Lifetime Craftsmanship Guarantee. Includes complimentary cleaning, prong inspection, and stone verification.
                        </div>
                        <div class="warranty-link">
                            Claim or track warranty anytime at vrazio.com/orders
                        </div>
                    </div>
                </td>

                {{-- Financial Totals with Gold Accent Left Border --}}
                <td class="totals-box">
                    <table class="totals-table">
                        <tr>
                            <td class="t-label">Subtotal</td>
                            <td class="t-val">₹{{ number_format((float)($order->subtotal ?? $order->total_amount), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="t-label">Tax / GST (3%)</td>
                            <td class="t-val">₹{{ number_format((float)($order->tax_gst ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="t-label">Insured Shipping</td>
                            <td class="t-val">
                                @if((float)$order->shipping_cost > 0)
                                    ₹{{ number_format((float)$order->shipping_cost, 2) }}
                                @else
                                    <span style="color: #0E261C;">FREE</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="t-label">Discount</td>
                            <td class="t-val">₹0.00</td>
                        </tr>
                        <tr>
                            <td colspan="2"><div class="totals-divider"></div></td>
                        </tr>
                        <tr class="totals-total-row">
                            <td class="totals-total-label">Total</td>
                            <td class="totals-total-val">₹{{ number_format((float)$order->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Bottom Footer --}}
        <div class="invoice-footer">
            <table class="footer-table">
                <tr>
                    <td class="footer-contacts">
                        <span>WhatsApp: +91 89796 22310</span>
                        <span class="footer-bullet">|</span>
                        <span>vrazioofficial@gmail.com</span>
                        <span class="footer-bullet">|</span>
                        <span>@vrazio.official</span>
                    </td>
                    <td class="footer-tagline">
                        <span style="color: #D4AF37;">✦</span> TIMELESS BEAUTY, ALWAYS YOURS
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>

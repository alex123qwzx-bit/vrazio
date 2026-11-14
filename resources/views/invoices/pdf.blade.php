<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tax Invoice - {{ $order->order_number }} | VRAZIO</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 8mm 8mm 8mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Serif', 'Georgia', serif;
            color: #1a1a1a;
            background-color: #FFFFFF;
            font-size: 9px;
            line-height: 1.35;
        }

        .invoice-outer-frame {
            border: 2px solid #C5A059;
            padding: 3px;
            background-color: #FFFFFF;
        }

        .invoice-inner-frame {
            border: 1px solid #C5A059;
            padding: 16px 20px 14px 20px;
            background-color: #FAF8F5;
        }

        .header-emblem-wrap {
            text-align: center;
            margin-bottom: 3px;
        }

        .brand-logo-img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: block;
            margin: 0 auto;
        }

        .brand-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #0E261C;
            letter-spacing: 6px;
            line-height: 1.1;
            margin-top: 4px;
        }

        .brand-tagline {
            text-align: center;
            font-size: 8px;
            letter-spacing: 2.5px;
            color: #777777;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 3px;
        }

        .tax-invoice-heading {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            color: #0E261C;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 12px;
        }

        .meta-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .meta-table td {
            vertical-align: top;
            font-size: 9px;
            line-height: 1.45;
        }

        .seller-block {
            color: #222222;
        }

        .seller-name {
            font-weight: bold;
            font-size: 10.5px;
            color: #0E261C;
            margin-bottom: 1px;
        }

        .billto-block {
            margin-top: 10px;
            color: #222222;
        }

        .billto-label {
            font-weight: bold;
            font-size: 9.5px;
            color: #0E261C;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .billto-customer {
            font-weight: bold;
            font-size: 10px;
            color: #0E261C;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .invoice-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-details-table td {
            padding: 1px 0;
            text-align: right;
            font-size: 9px;
            line-height: 1.45;
        }

        .invoice-details-table .lbl {
            font-weight: bold;
            color: #0E261C;
            padding-right: 6px;
        }

        .invoice-details-table .val {
            color: #222222;
        }

        /* Table */
        .items-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table th {
            background-color: #0E261C;
            color: #FFFFFF;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 6px;
            font-weight: bold;
            border: 1px solid #0E261C;
        }

        .items-table td {
            padding: 6px 6px;
            border: 1px solid #CCCCCC;
            font-size: 9px;
            color: #1a1a1a;
            vertical-align: middle;
        }

        .item-title {
            font-weight: bold;
            color: #0E261C;
            font-size: 9.5px;
        }

        .item-meta {
            font-size: 8px;
            color: #666666;
            margin-top: 2px;
        }

        /* Summary */
        .summary-wrapper-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            line-height: 1.4;
        }

        .summary-table td {
            padding: 2px 0;
        }

        .summary-table .s-lbl {
            text-align: right;
            padding-right: 12px;
            color: #222222;
            width: 60%;
        }

        .summary-table .s-val {
            text-align: right;
            color: #111111;
            font-weight: normal;
            width: 40%;
        }

        .total-box {
            background-color: #0E261C;
            color: #FFFFFF;
            padding: 7px 10px;
            margin-top: 5px;
            border-radius: 2px;
        }

        .total-box-amount {
            font-size: 11px;
            font-weight: bold;
            color: #FFFFFF;
            letter-spacing: 0.5px;
            text-align: right;
        }

        .total-box-words {
            font-size: 8px;
            color: #FFFFFF;
            padding-top: 2px;
            text-align: right;
            line-height: 1.25;
        }

        /* Bottom Footer Notes */
        .footer-notes {
            font-size: 8px;
            line-height: 1.4;
            color: #222222;
            margin-top: 10px;
            border-top: 1px dotted #C5A059;
            padding-top: 8px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .signature-table td {
            text-align: right;
            font-size: 9.5px;
            font-weight: bold;
            color: #0E261C;
            padding-right: 15px;
        }

        .signoff-box {
            text-align: center;
            margin-top: 12px;
        }

        .signoff-script {
            font-size: 15px;
            font-style: italic;
            color: #0E261C;
        }

        .signoff-atelier {
            font-style: normal;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .signoff-tagline {
            font-size: 7.5px;
            letter-spacing: 2px;
            color: #777777;
            text-transform: uppercase;
            margin-top: 3px;
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

<div class="invoice-outer-frame">
    <div class="invoice-inner-frame">

        {{-- Centered Header Emblem & Brand Title --}}
        <div class="header-emblem-wrap">
            @if(!empty($logoBase64))
                <img src="data:image/png;base64,{{ $logoBase64 }}" class="brand-logo-img" alt="VRAZIO Emblem">
            @endif
        </div>

        <div class="brand-title">V R A Z I O</div>
        <div class="brand-tagline">LUXURY &bull; HANDCRAFTED</div>
        <div class="tax-invoice-heading">TAX INVOICE</div>

        {{-- Two-Column Metadata Section --}}
        <table class="meta-table">
            <tr>
                {{-- Left: Seller Details & Bill To --}}
                <td style="width: 52%; padding-right: 12px;">
                    <div class="seller-block">
                        <div class="seller-name">VRAZIO ATELIER</div>
                        <div><strong>UDYAM:</strong> UDYAM-UP-59-0089382</div>
                        <div><strong>GSTIN:</strong> 08AAAFV1234F1Z5</div>
                        <div><strong>Address:</strong> Civil Lines, Moradabad, Uttar Pradesh - 244001</div>
                        <div><strong>Email:</strong> vrazioofficial@gmail.com</div>
                        <div><strong>WhatsApp:</strong> +91 87912 27210</div>
                    </div>

                    <div class="billto-block">
                        <div class="billto-label">BILL TO:</div>
                        <div class="billto-customer">{{ strtoupper($order->client_name) }}</div>
                        @if(!empty($addr['street']))
                            <div>{{ strtoupper($addr['street']) }}</div>
                        @endif
                        @if(!empty($addr['city']) || !empty($addr['state']))
                            <div>{{ $addr['city'] ?? '' }}{{ !empty($addr['city']) && !empty($addr['state']) ? ', ' : '' }}{{ $addr['state'] ?? '' }}{{ !empty($addr['pincode']) ? ' - ' . $addr['pincode'] : '' }}</div>
                        @endif
                        <div><strong>Ph:</strong> {{ $order->client_phone }}</div>
                        <div><strong>State Code:</strong> {{ $stateCode }} ({{ $stateName }})</div>
                    </div>
                </td>

                {{-- Right: Invoice Metadata --}}
                <td style="width: 48%; padding-left: 12px;">
                    <table class="invoice-details-table">
                        <tr>
                            <td class="lbl">INVOICE NO:</td>
                            <td class="val">VRZ-INV-{{ str_replace('VRZ-', '', $order->order_number) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">DATE:</td>
                            <td class="val">{{ $order->created_at ? $order->created_at->format('d M Y') : date('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">ORDER NO:</td>
                            <td class="val">#{{ $order->order_number }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">PAYMENT:</td>
                            <td class="val">{{ strtoupper($order->payment_status ?? 'PENDING') }} ({{ strtoupper($order->payment_gateway ?? 'COD') }})</td>
                        </tr>
                        <tr>
                            <td class="lbl">PLACE OF SUPPLY:</td>
                            <td class="val">{{ $order->getPlaceOfSupply() }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Product Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">#</th>
                    <th style="width: 48%; text-align: left;">PRODUCT DESCRIPTION</th>
                    <th style="width: 10%; text-align: center;">QTY</th>
                    <th style="width: 18%; text-align: right;">UNIT PRICE (&#8377;)</th>
                    <th style="width: 18%; text-align: right;">TOTAL (&#8377;)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>
                            <div class="item-title">{{ $item->product_title }}</div>
                            <div class="item-meta">
                                SKU: {{ $item->product_sku }} | HSN: {{ $item->getHsnCode() }} | GST: {{ $item->getGstRateFormatted() }}@if(!empty($item->size)) | Size: {{ $item->size }}@endif
                            </div>
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ number_format((float)$item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: bold; color: #0E261C;">{{ number_format((float)$item->line_total, 2) }}</td>
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

        {{-- Summary and Total Section --}}
        <table class="summary-wrapper-table">
            <tr>
                <td style="width: 48%;"></td>
                <td style="width: 52%; vertical-align: top;">
                    <table class="summary-table">
                        <tr>
                            <td class="s-lbl">Subtotal:</td>
                            <td class="s-val">{{ number_format((float)($order->subtotal ?? $order->total_amount), 2) }}</td>
                        </tr>
                        @if(!$order->isInterState())
                            <tr>
                                <td class="s-lbl">CGST ({{ number_format($order->getCgstRate(), 1) }}%):</td>
                                <td class="s-val">{{ number_format($order->getCgstAmount(), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="s-lbl">SGST ({{ number_format($order->getSgstRate(), 1) }}%):</td>
                                <td class="s-val">{{ number_format($order->getSgstAmount(), 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="s-lbl">IGST ({{ number_format($order->getIgstRate(), 1) }}%):</td>
                                <td class="s-val">{{ number_format($order->getIgstAmount(), 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="s-lbl">Insured Shipping:</td>
                            <td class="s-val">
                                @if((float)$order->shipping_cost > 0)
                                    {{ number_format((float)$order->shipping_cost, 2) }}
                                @else
                                    FREE
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="s-lbl">Discount:</td>
                            <td class="s-val">
                                @if((float)($order->discount_amount ?? 0) > 0)
                                    -{{ number_format((float)$order->discount_amount, 2) }}
                                @else
                                    0.00
                                @endif
                            </td>
                        </tr>
                    </table>

                    {{-- Dark Green Total Bar --}}
                    <div class="total-box">
                        <div class="total-box-amount">
                            TOTAL (INR): &nbsp;&nbsp; &#8377;{{ number_format((float)$order->total_amount, 2) }}
                        </div>
                        <div class="total-box-words">
                            In Words: {{ $order->getAmountInWords() }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Footer Notes: Bank Details & Warranty --}}
        <div class="footer-notes">
            <div><strong>BANK DETAILS:</strong> VRAZIO ATELIER, ICICI BANK, A/C No: 123450506781, IFSC: ICIC0001234</div>
            <div style="margin-top: 2px;">
                <strong>WARRANTY:</strong> Each accessory is backed by VRAZIO's authentic craftsmanship guarantee. Track warranty at vrazio.com/orders
            </div>
        </div>

        {{-- Signoff: For VRAZIO --}}
        <table class="signature-table">
            <tr>
                <td>For VRAZIO</td>
            </tr>
        </table>

        {{-- Centered Signoff --}}
        <div class="signoff-box">
            <div class="signoff-script">
                Thank You <span class="signoff-atelier">FOR CHOOSING VRAZIO ATELIER</span>
            </div>
            <div class="signoff-tagline">
                TIMELESS BEAUTY, ALWAYS YOURS
            </div>
        </div>

    </div>
</div>

</body>
</html>

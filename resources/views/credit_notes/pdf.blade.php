<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Note - {{ $creditNote->credit_note_number }} | VRAZIO</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
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
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .header-table td {
            vertical-align: top;
        }

        .brand-logo {
            font-size: 32px;
            font-weight: bold;
            color: #0E261C;
            letter-spacing: 4px;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 9px;
            letter-spacing: 3px;
            color: #D4AF37;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            margin-bottom: 8px;
        }

        .company-meta {
            font-size: 8.5px;
            color: #555555;
            line-height: 1.5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title {
            text-align: right;
            font-size: 26px;
            font-weight: bold;
            color: #9B1B30;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .doc-meta {
            margin-top: 8px;
            text-align: right;
            font-size: 9.5px;
            color: #444444;
            line-height: 1.6;
        }

        .doc-meta strong {
            color: #0E261C;
        }

        .bill-to-box {
            background-color: #ffffff;
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .bill-to-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #D4AF37;
            margin-bottom: 4px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #0E261C;
            color: #D4AF37;
            padding: 8px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 10px;
        }

        .items-table tr:nth-child(even) td {
            background-color: #ffffff;
        }

        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .totals-table td {
            padding: 5px 8px;
            font-size: 10.5px;
        }

        .totals-table tr.grand-total td {
            border-top: 2px solid #0E261C;
            border-bottom: 2px solid #0E261C;
            font-size: 13px;
            font-weight: bold;
            color: #9B1B30;
            padding: 8px;
        }

        .statutory-box {
            background-color: #ffffff;
            border-left: 3px solid #D4AF37;
            padding: 10px 14px;
            font-size: 9px;
            color: #555555;
            line-height: 1.5;
            margin-top: 15px;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #D4AF37;
            padding-top: 10px;
            text-align: center;
            font-size: 8.5px;
            color: #777777;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="brand-logo">VRAZIO</div>
                <div class="brand-subtitle">&mdash; ATELIER &amp; HAUTE COUTURE &mdash;</div>
                <div class="company-meta">
                    <strong>Enterprise:</strong> VRAZIO<br>
                    <strong>UDYAM REG. NO:</strong> UDYAM-UP-59-0089382<br>
                    <strong>GST Compliance:</strong> Section 34 CGST Act, 2017 &bull; Central Tax Rules
                </div>
            </td>
            <td style="width: 45%;">
                <div class="doc-title">CREDIT NOTE</div>
                <div class="doc-meta">
                    <strong>CREDIT NOTE NO:</strong> {{ $creditNote->credit_note_number }}<br>
                    <strong>DATE OF ISSUE:</strong> {{ $creditNote->issued_at->format('d M Y') }}<br>
                    <strong>ORIGINAL INVOICE:</strong> {{ $creditNote->original_invoice_number }}<br>
                    <strong>INVOICE DATE:</strong> {{ $creditNote->invoice_date->format('d M Y') }}<br>
                    <strong>ORDER REF:</strong> #{{ $creditNote->order->order_number }}
                </div>
            </td>
        </tr>
    </table>

    <div class="bill-to-box">
        <div class="bill-to-title">CREDITED TO PATRON</div>
        <strong style="font-size: 12px; color: #0E261C;">{{ $creditNote->client_name }}</strong><br>
        @if($creditNote->client_phone) Phone: {{ $creditNote->client_phone }} &bull; @endif
        @if($creditNote->client_email) Email: {{ $creditNote->client_email }} @endif
        @if(!empty($creditNote->client_address))
            <br>
            @php
                $addr = is_array($creditNote->client_address) ? $creditNote->client_address : json_decode((string)$creditNote->client_address, true);
            @endphp
            {{ $addr['street'] ?? '' }}{{ !empty($addr['city']) ? ', ' . $addr['city'] : '' }}{{ !empty($addr['state']) ? ', ' . $addr['state'] : '' }}{{ !empty($addr['pincode']) ? ' - ' . $addr['pincode'] : '' }}
        @endif
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 47%;">Cancelled Product Description</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Taxable (₹)</th>
                <th style="width: 20%; text-align: right;">Total Credit (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = is_array($creditNote->cancelled_items) ? $creditNote->cancelled_items : json_decode((string)$creditNote->cancelled_items, true);
            @endphp
            @foreach($items as $idx => $ci)
                <tr>
                    <td style="color: #888;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $ci['title'] ?? 'Product' }}</strong><br>
                        <span style="font-size: 8.5px; color: #666; font-family: monospace;">SKU: {{ $ci['sku'] ?? 'VRZ' }}</span>
                        @if(!empty($ci['size']))
                            &bull; <span style="font-size: 8.5px; color: #666;">Size: {{ $ci['size'] }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $ci['quantity'] ?? 1 }}</td>
                    <td style="text-align: right;">₹{{ number_format((float) ($ci['taxable_amount'] ?? $ci['line_total']), 2) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #0E261C;">₹{{ number_format((float) ($ci['line_total'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td style="color: #666;">Taxable Settlement:</td>
            <td style="text-align: right; font-weight: bold;">₹{{ number_format((float) $creditNote->taxable_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #666;">GST Adjustment:</td>
            <td style="text-align: right; font-weight: bold;">₹{{ number_format((float) $creditNote->gst_amount, 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td>TOTAL CREDIT REFUND:</td>
            <td style="text-align: right;">₹{{ number_format((float) $creditNote->total_credit_amount, 2) }}</td>
        </tr>
    </table>

    <div class="statutory-box">
        <strong>Statutory GST Credit Note Declaration:</strong><br>
        This Credit Note is issued in accordance with Section 34 of the Central Goods and Services Tax Act, 2017. Tax liability is adjusted as goods were cancelled prior to or during fulfillment. Original invoice {{ $creditNote->original_invoice_number }} stands amended by the credit value stated herein and is retained in the atelier permanent archive.
        <br><br>
        <strong>Reason for Issuance:</strong> {{ $creditNote->reason }}
    </div>

    <div class="footer">
        WhatsApp Concierge: +91 87912 27210 &bull; Email: vrazioofficail@gmail.com &bull; Instagram: @vrazio.official<br>
        This is a computer generated official credit note requiring no physical signature.
    </div>
</body>
</html>

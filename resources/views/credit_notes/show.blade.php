@extends('layouts.app')

@section('title', 'Credit Note - ' . $creditNote->credit_note_number . ' | VRAZIO Atelier')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('orders.index') }}" class="text-xs uppercase tracking-widest text-[#0E261C] hover:text-[#D4AF37] flex items-center gap-1.5 font-semibold">
                <span>&larr;</span>
                <span>Back to My Orders</span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('credit_note.download', $creditNote->credit_note_number) }}" 
                   class="px-4 py-2 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-wider transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download Official PDF</span>
                </a>
            </div>
        </div>

        <div class="bg-white border border-[#D4AF37]/30 shadow-xl p-8 sm:p-12 rounded-sm text-[#0E261C]">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start border-b border-gray-100 pb-8 gap-6">
                <div>
                    <h1 class="font-display text-3xl font-bold tracking-wider text-[#0E261C]">VRAZIO</h1>
                    <span class="text-[10px] tracking-[0.25em] text-[#D4AF37] uppercase font-semibold block mt-0.5">&mdash; ATELIER &amp; HAUTE COUTURE &mdash;</span>
                    <p class="text-xs text-gray-500 mt-3 leading-relaxed">
                        UDYAM REG. NO: UDYAM-UP-59-0089382<br>
                        GST Compliance: Section 34 CGST Act, 2017
                    </p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="px-3 py-1 bg-rose-50 text-rose-800 border border-rose-200 text-xs font-bold uppercase tracking-wider rounded-sm inline-block mb-3">
                        Official Credit Note
                    </span>
                    <div class="text-xs space-y-1 text-gray-600">
                        <div><span class="text-gray-400">Credit Note #:</span> <strong class="font-mono text-[#0E261C]">{{ $creditNote->credit_note_number }}</strong></div>
                        <div><span class="text-gray-400">Issued On:</span> {{ $creditNote->issued_at->format('M d, Y') }}</div>
                        <div><span class="text-gray-400">Original Invoice:</span> <strong class="font-mono">{{ $creditNote->original_invoice_number }}</strong></div>
                        <div><span class="text-gray-400">Order Ref:</span> #{{ $creditNote->order->order_number }}</div>
                    </div>
                </div>
            </div>

            {{-- Patron / Bill To --}}
            <div class="my-8 p-4 bg-[#FAF8F5] border border-gray-200 rounded-xs text-xs">
                <span class="text-[10px] uppercase font-bold text-[#D4AF37] tracking-widest block mb-1">Credited To Patron</span>
                <strong class="text-sm font-semibold text-[#0E261C] block">{{ $creditNote->client_name }}</strong>
                <p class="text-gray-600 mt-1">
                    @if($creditNote->client_phone) Phone: {{ $creditNote->client_phone }} &bull; @endif
                    @if($creditNote->client_email) Email: {{ $creditNote->client_email }} @endif
                </p>
            </div>

            {{-- Cancelled Items Table --}}
            <div class="overflow-x-auto mb-8">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#0E261C] text-[#D4AF37] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Cancelled Product</th>
                            <th class="py-3 px-4 text-center">Qty</th>
                            <th class="py-3 px-4 text-right">Taxable</th>
                            <th class="py-3 px-4 text-right">Credit Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $items = is_array($creditNote->cancelled_items) ? $creditNote->cancelled_items : json_decode((string)$creditNote->cancelled_items, true);
                        @endphp
                        @foreach($items as $i => $item)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-3 px-4">
                                    <strong class="text-sm text-[#0E261C] block">{{ $item['title'] ?? 'Product' }}</strong>
                                    <span class="text-[11px] text-gray-500 font-mono">SKU: {{ $item['sku'] ?? 'VRZ' }}</span>
                                    @if(!empty($item['size']))
                                        <span class="text-[11px] text-gray-500">&bull; Size: {{ $item['size'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-medium">{{ $item['quantity'] ?? 1 }}</td>
                                <td class="py-3 px-4 text-right font-mono">₹{{ number_format((float) ($item['taxable_amount'] ?? $item['line_total']), 2) }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-[#0E261C]">₹{{ number_format((float) ($item['line_total'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="flex justify-end mb-8">
                <div class="w-full sm:w-1/2 space-y-2 text-xs border-t border-gray-100 pt-4">
                    <div class="flex justify-between text-gray-600">
                        <span>Taxable Settlement:</span>
                        <span class="font-mono">₹{{ number_format((float) $creditNote->taxable_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>GST Liability Adjustment:</span>
                        <span class="font-mono">₹{{ number_format((float) $creditNote->gst_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-rose-800 border-t-2 border-[#0E261C] pt-2">
                        <span>Total Refund Credited:</span>
                        <span class="font-mono">₹{{ number_format((float) $creditNote->total_credit_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Statutory Note --}}
            <div class="p-4 bg-amber-50/60 border-l-4 border-[#D4AF37] text-xs text-amber-950 leading-relaxed">
                <strong>Statutory GST Credit Note Notice:</strong><br>
                Issued pursuant to Section 34 of the Central Goods and Services Tax Act, 2017. Stated tax liability and inventory consignments stand amended. The original invoice is preserved in the atelier permanent archive for ledger compliance.
                <br><br>
                <strong>Reason:</strong> {{ $creditNote->reason }}
            </div>
        </div>
    </div>
</div>
@endsection

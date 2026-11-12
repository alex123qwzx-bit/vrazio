<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class CreditNoteController extends Controller
{
    /**
     * Display credit note web view.
     */
    public function show(string $creditNoteNumber): View|RedirectResponse
    {
        $creditNote = CreditNote::with('order.items')
            ->where('credit_note_number', trim($creditNoteNumber))
            ->firstOrFail();

        if (! $creditNote->order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to credit note dossier.');
        }

        return view('credit_notes.show', compact('creditNote'));
    }

    /**
     * Download the luxury credit note PDF.
     */
    public function download(string $creditNoteNumber): Response
    {
        $creditNote = CreditNote::with('order.items')
            ->where('credit_note_number', trim($creditNoteNumber))
            ->firstOrFail();

        if (! $creditNote->order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to credit note dossier.');
        }

        $pdf = Pdf::loadView('credit_notes.pdf', compact('creditNote'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
                'dpi' => 96,
            ]);

        return $pdf->download("VRAZIO-CreditNote-{$creditNote->credit_note_number}.pdf");
    }

    /**
     * View latest credit note by order number.
     */
    public function forOrder(string $orderNumber): View|RedirectResponse
    {
        $order = Order::where('order_number', trim($orderNumber))->firstOrFail();

        if (! $order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to credit note.');
        }

        $creditNote = CreditNote::where('order_id', $order->id)->latest()->firstOrFail();

        return view('credit_notes.show', compact('creditNote'));
    }
}

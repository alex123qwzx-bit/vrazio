<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    /**
     * Display printable, luxury VRAZIO invoice matching the reference design.
     */
    public function show(string $orderNumber): View|RedirectResponse
    {
        $order = Order::with(['items.product', 'warrantyClaim'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! $order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to invoice dossier.');
        }

        return view('invoices.show', compact('order'));
    }

    /**
     * Download the luxury VRAZIO invoice as a PDF file.
     */
    public function download(string $orderNumber): Response
    {
        $order = Order::with(['items.product', 'warrantyClaim'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! $order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to invoice dossier.');
        }

        $pdf = Pdf::loadView('invoices.pdf', compact('order'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
                'dpi' => 96,
            ]);

        return $pdf->download("VRAZIO-Invoice-{$order->order_number}.pdf");
    }

    /**
     * Stream the invoice PDF inline in browser.
     */
    public function stream(string $orderNumber): Response
    {
        $order = Order::with(['items.product', 'warrantyClaim'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! $order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to invoice dossier.');
        }

        $pdf = Pdf::loadView('invoices.pdf', compact('order'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
                'dpi' => 96,
            ]);

        return $pdf->stream("VRAZIO-Invoice-{$order->order_number}.pdf");
    }
}

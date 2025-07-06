<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function Invoice($id)
    {
        $invoice = \App\Models\Invoice::with('customer', 'items')->findOrFail($id);
        // Ensure the invoice exists
        if (!$invoice) {
            return redirect()->back()->withErrors(['error' => 'Invoice not found']);
        }

        // Usar el facade PDF de Barryvdh para footers y paginación
        $pdf = \PDF::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("invoice-{$invoice->id}.pdf");
    }
}

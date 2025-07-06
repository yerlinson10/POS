<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class PdfController extends Controller
{
    public function Invoice($id)
    {
        // Cargar solo los campos necesarios para la factura y relaciones
        $invoice = \App\Models\Invoice::with([
            'customer',
            'items'
        ])->findOrFail($id);

        // Prepara los valores formateados para la vista
        $customerFullName = '';
        if ($invoice->customer) {
            $customerFullName = trim($invoice->customer->first_name . ' ' . $invoice->customer->last_name);
        }
        $taxLabel = '';
        $taxAmountFormatted = '';
        if (isset($invoice->tax_amount) && $invoice->tax_amount > 0) {
            $taxPercent = $invoice->tax_percentage ?? 19;
            $taxLabel = 'IVA (' . number_format($taxPercent, 2) . '%):';
            $taxAmountFormatted = '$' . number_format($invoice->tax_amount, 2);
        }

        // Puedes preparar otros valores formateados aquí si lo deseas

        $pdf = \PDF::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tax_label' => $taxLabel,
            'tax_amount_formatted' => $taxAmountFormatted,
            // Agrega aquí otros valores formateados si los usas en la vista
        ])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'isRemoteEnabled' => false, // si no usas imágenes remotas
                'show_warnings' => false,
                'enable_font_subsetting' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->stream("invoice-{$invoice->id}.pdf");
    }
}

<?php

namespace App\Http\Controllers;

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

        $taxLabel = '';
        $taxAmountFormatted = '';
        if (isset($invoice->tax_amount) && $invoice->tax_amount > 0) {
            $taxPercent = $invoice->tax_percentage ?? 19;
            $taxLabel = 'IVA (' . number_format($taxPercent, 2) . '%):';
            $taxAmountFormatted = '$' . number_format($invoice->tax_amount, 2);
        }

        $typeInvoice = app(\App\Services\SystemSettingService::class)->get('invoice_type', 'A4', auth()->id());

        $templateInvoice = $typeInvoice === 'A4' ? 'pdf.invoice' : 'pdf.invoice80mm';

        $pdf = \PDF::loadView($templateInvoice, [
            'invoice' => $invoice,
            'tax_label' => $taxLabel,
            'tax_amount_formatted' => $taxAmountFormatted,
        ])->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'isRemoteEnabled' => false, // si no usas imágenes remotas
                'show_warnings' => false,
                'enable_font_subsetting' => true,
                'defaultFont' => 'sans-serif',
            ]);

        if($typeInvoice === 'A4') {
            $pdf->setPaper('A4', 'portrait');
        } else {
            $pdf->setPaper(array(0, 0, 204, 1000));

        }
        return $pdf->stream("invoice-{$invoice->id}.pdf");
    }
}

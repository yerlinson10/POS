<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id ?? 'XXXX' }}</title>
    <style>
        /* Variables CSS para colores - fácil mantenimiento */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --accent-color: #f59e0b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
        }

        /* Reset y configuración base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: var(--text-primary);
            background-color: white;
        }

        /* Contenedor principal */
        .invoice-container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 10px;
            box-sizing: border-box;
            /* overflow: hidden; */
            /* eliminado para evitar cortes de contenido */
        }

        /* Header de la factura */
        .invoice-header {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 6px 6px 0 0;
            padding: 10px;
            box-sizing: border-box;
        }

        .header-top {
            width: 100%;
            display: table;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 10px;
            box-sizing: border-box;
        }

        .invoice-meta {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
            box-sizing: border-box;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 8px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .company-details {
            color: var(--text-secondary);
            font-size: 10px;
            line-height: 1.4;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 8px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .invoice-number {
            font-size: 14px;
            color: var(--text-primary);
            margin-bottom: 5px;
            font-weight: bold;
        }

        .invoice-date {
            color: var(--text-secondary);
            font-size: 11px;
        }

        /* Tipo de factura badge */
        .invoice-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .invoice-type.paid {
            background-color: var(--success-color);
            color: white;
        }

        .invoice-type.pending {
            background-color: var(--accent-color);
            color: white;
        }

        .invoice-type.canceled {
            background-color: var(--danger-color);
            color: white;
        }

        /* Información del cliente */
        .customer-section {
            width: 100%;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 10px;
            box-sizing: border-box;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border-color);
        }

        .customer-info {
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        .customer-details {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
            box-sizing: border-box;
        }

        .billing-details {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            box-sizing: border-box;
        }

        .info-group {
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            color: var(--text-secondary);
            font-size: 10px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .info-value {
            color: var(--text-primary);
            font-size: 12px;
        }

        /* Tabla de productos */
        .products-section {
            margin-bottom: 20px;
            page-break-inside: auto;
            page-break-after: auto;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border-color);
            margin-top: 10px;
            table-layout: fixed;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            overflow: hidden;
        }

        .products-table th {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            padding: 6px 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: left;
            border-bottom: 2px solid var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .products-table td {
            padding: 6px 3px;
            border-bottom: 1px solid var(--border-color);
            font-size: 9px;
            vertical-align: top;
            word-wrap: break-word;
            overflow: hidden;
        }

        .products-table tr:nth-child(even) {
            background-color: var(--light-color);
        }

        /* Columnas específicas - Más estrechas para evitar desbordamiento */
        .col-item {
            width: 42%;
        }

        .col-qty {
            width: 10%;
            text-align: center;
            font-size: 8px;
        }

        .col-price {
            width: 16%;
            text-align: right;
            font-size: 8px;
        }

        .col-discount {
            width: 14%;
            text-align: right;
            font-size: 8px;
        }

        .col-total {
            width: 18%;
            text-align: right;
            font-weight: bold;
            font-size: 8px;
        }

        /* Sección de totales */
        .totals-section {
            width: 100%;
            margin-bottom: 20px;
        }

        .totals-table {
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        .totals-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 10px;
            box-sizing: border-box;
        }

        .totals-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            box-sizing: border-box;
        }

        .totals-grid {
            width: 100%;
            border: 1px solid var(--border-color);
            border-collapse: collapse;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .totals-grid tr {
            border-bottom: 1px solid var(--border-color);
        }

        .totals-grid td {
            padding: 8px 8px;
            font-size: 10px;
        }

        .totals-grid .label {
            background: linear-gradient(135deg, var(--light-color) 0%, #e2e8f0 100%);
            font-weight: bold;
            color: var(--text-secondary);
            width: 70%;
        }

        .totals-grid .value {
            text-align: right;
            font-weight: bold;
            color: var(--text-primary);
            width: 65%;
        }

        .totals-grid .total-final {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            font-size: 13px;
            font-weight: bold;
        }

        /* Descuentos especiales */
        .discount-info {
            background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 20%, #fef3c7 100%);
            border: 2px solid var(--accent-color);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
        }

        .discount-title {
            color: var(--accent-color);
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .discount-details {
            color: #92400e;
            font-size: 11px;
        }

        /* Notas y términos */
        .notes-section {
            margin-top: 20px;
            border-top: 1px solid var(--border-color);
            padding-top: 15px;
        }

        .notes-title {
            font-weight: bold;
            color: var(--text-secondary);
            font-size: 12px;
            margin-bottom: 8px;
        }

        .notes-content {
            color: var(--text-primary);
            font-size: 11px;
            line-height: 1.4;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 30px;
            border-top: 1px solid var(--border-color);
            padding-top: 15px;
            text-align: center;
            color: var(--text-muted);
            font-size: 10px;
        }

        /* Utilidades */
        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-bold {
            font-weight: bold !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: var(--success-color) !important;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        /* Responsive adjustments para DomPDF */
        @page {
            margin: 1cm;
            size: A4;
        }

        .page-break {
            page-break-before: always;
        }

        /* Mejoras adicionales para PDF */
        .invoice-container {
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        /* Evitar cortes de página en secciones importantes */
        .customer-section,
        .totals-section {
            page-break-inside: avoid;
        }

        /* Asegurar que las tablas no se corten mal */
        .products-table,
        .totals-grid {
            page-break-inside: auto;
            max-width: 100%;
            table-layout: fixed;
        }

        .products-table tr {
            page-break-inside: auto;
        }

        /* Forzar que todos los elementos respeten el ancho máximo */
        * {
            max-width: 98%;
            word-wrap: break-word;
        }
    </style>
</head>

<body>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-top">
                <div class="company-info">
                    <div class="company-name">{{ config('app.name', 'Your Company') }}</div>
                    <div class="company-details">
                        <div>Company Address</div>
                        <div>City, ZIP</div>
                        <div>Phone: (xxx) xxx-xxxx</div>
                        <div>info@company.com</div>
                        <div>TAX ID: xxx.xxx.xxx-x</div>
                    </div>
                </div>
                <div class="invoice-meta">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">#{{ $invoice->id ?? 'XXXX' }}</div>
                    <div class="invoice-date">
                        Date: {{ $invoice->created_at ? $invoice->created_at->format('d/m/Y') : date('d/m/Y') }}
                    </div>
                    <div class="invoice-type {{ strtolower($invoice->status ?? 'paid') }}">
                        {{ ucfirst($invoice->status ?? 'Paid') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="customer-section">
            <div class="section-title">Customer Information</div>
            <div class="customer-info">
                <div class="customer-details">
                    <div class="info-group">
                        <span class="info-label">Customer:</span>
                        <div class="info-value">{{ $invoice->customer->name ?? 'General Customer' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Document:</span>
                        <div class="info-value">{{ $invoice->customer->document ?? 'N/A' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Phone:</span>
                        <div class="info-value">{{ $invoice->customer->phone ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="billing-details">
                    <div class="info-group">
                        <span class="info-label">Email:</span>
                        <div class="info-value">{{ $invoice->customer->email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Address:</span>
                        <div class="info-value">{{ $invoice->customer->address ?? 'N/A' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Salesperson:</span>
                        <div class="info-value">{{ $invoice->user->name ?? 'System' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descuento Global (si aplica) -->
        @if ($invoice->discount_percentage > 0 || $invoice->discount_amount > 0)
            <div class="discount-info">
                <div class="discount-title">Discount Applied</div>
                <div class="discount-details">
                    @if ($invoice->discount_percentage > 0)
                        Discount of {{ number_format($invoice->discount_percentage, 2) }}% applied to the entire
                        invoice
                    @endif
                    @if ($invoice->discount_amount > 0)
                        Fixed discount of ${{ number_format($invoice->discount_amount, 2) }} applied
                    @endif
                </div>
            </div>
        @endif

        <!-- Productos -->
        <div class="products-section">
            <div class="section-title">Product Details</div>
            <table class="products-table">
                <thead>
                    <tr>
                        <th class="col-item" style="color: var(--dark-color); text-align:left;">Product</th>
                        <th class="col-qty" style="color: var(--dark-color); text-align:center;">Qty</th>
                        <th class="col-price" style="color: var(--dark-color); text-align:right;">Price</th>
                        {{-- <th class="col-discount" style="color: var(--dark-color); text-align:right;">Discount</th> --}}
                        <th class="col-total" style="color: var(--dark-color); text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items ?? [] as $item)
                        <tr>
                            <td class="col-item" style="text-align:left;">
                                <strong>{{ $item->product->name ?? 'Product' }}</strong>
                                @if ($item->product->sku ?? false)
                                    <br><small class="text-muted">{{ $item->product->sku }}</small>
                                @endif
                            </td>
                            <td class="col-qty" style="text-align:center;">{{ number_format($item->quantity, 2) }}</td>
                            <td class="col-price" style="text-align:right;">${{ number_format($item->unit_price, 2) }}
                            </td>
                            {{-- <td class="col-discount" style="text-align:right;">
                            @if ($item->discount_percentage > 0)
                                {{ number_format($item->discount_percentage, 2) }}%
                            @elseif($item->discount_amount > 0)
                                ${{ number_format($item->discount_amount, 2) }}
                            @else
                                -
                            @endif
                        </td> --}}
                            <td class="col-total" style="text-align:right;">${{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No products</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Salto de página manual si hay muchos productos -->
        {{-- @if (count($invoice->items ?? []) > 15)
            <div class="page-break"></div>
        @endif --}}

        <!-- Totales -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="totals-left">
                    <!-- Espacio para notas adicionales -->
                </div>
                <div class="totals-right">
                    <table class="totals-grid">
                        <tr>
                            <td class="label">Subtotal:</td>
                            <td class="value">${{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                        </tr>
                        @if ($invoice->discount_value > 0)
                            <tr>
                                <td class="label text-danger">Discount:</td>
                                @if ($invoice->discount_type == 'percentage')
                                    <td class="value text-danger">
                                        -${{ number_format($invoice->subtotal * ($invoice->discount_value / 100), 2) }}
                                        ({{ $invoice->discount_value }}%)
                                    </td>
                                @else
                                    <td class="value text-danger">
                                        -${{ number_format($invoice->discount_value, 2) }}
                                    </td>
                                @endif

                            </tr>
                        @endif
                        @if (!empty($invoice->tax_label) && !empty($invoice->tax_amount_formatted))
                            <tr>
                                <td class="label">{{ $invoice->tax_label }}</td>
                                <td class="value">{{ $invoice->tax_amount_formatted }}</td>
                            </tr>
                        @endif
                        <tr class="total-final">
                            <td class="label">TOTAL:</td>
                            <td class="value">${{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="invoice-footer">
            <div style="margin-bottom: 5px;"><strong>Thank you for your purchase | {{ config('app.name') }}</strong>
            </div>
            <div>This document is a printed representation of the electronic invoice</div>
            <div style="margin-top: 5px; font-size: 9px;">Generated on {{ date('d/m/Y H:i') }}</div>
        </footer>
        <!-- Notas -->
        @if ($invoice->notes ?? false)
            <div class="notes-section">
                <div class="notes-title">Notes:</div>
                <div class="notes-content">{{ $invoice->notes }}</div>
            </div>
        @endif
    </div>
</body>

</html>

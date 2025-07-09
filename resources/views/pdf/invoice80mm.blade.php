<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $invoice->id ?? 'XXXX' }}</title>
    <style>
        html {
            width: 80mm;
            margin: 0;
        }

        body {
            width: 68mm;
            margin: 0;
            /* padding: 10px; */
            font-size: 12px;
        }

        .header,
        .footer {
            text-align: center;
        }

        .header .company {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .info,
        .items,
        .totals {
            width: 100%;
            margin-bottom: 4px;
        }

        .info td {
            padding: 2px 0;
        }

        .items th,
        .items td {
            padding: 2px 0;
        }

        .items th {
            border-bottom: 1px dashed #000;
        }

        .items td {
            border-bottom: 1px dotted #ccc;
        }

        .items .qty,
        .items .price,
        .items .total {
            text-align: right;
        }

        .totals td {
            padding: 2px 0;
        }

        .totals .label {
            text-align: left;
        }

        .totals .value {
            text-align: right;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company">{{ config('app.name', 'My Company') }}</div>
        <div>Street in front of my house, Santiago DR</div>
        <div>{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
        <div>{{$invoice->status == 'paid' ? 'Invoice' : 'Quotation'}} #: {{ $invoice->id ?? 'XXXX' }}</div>
    </div>

    <table class="info">
        <tr>
            <td>Tel:</td>
            <td>829-217-3552</td>
        </tr>
        <tr>
            <td>Customer:</td>
            <td>{{ $invoice->customer->full_name ?? 'Final consumer' }}</td>
        </tr>
    </table>

    <div class="separator"></div>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="qty">Qty</th>
                <th class="price">Unit P.</th>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ Illuminate\Support\Str::limit($item->product->name, 20) }}</td>
                    <td class="qty">{{ number_format($item->quantity, 0) }}</td>
                    <td class="price">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="total">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator"></div>

    <table class="totals">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="value">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if ($invoice->discount_value > 0)
                @if ($invoice->discount_type == 'percentage')
                <tr>
                    <td class="label">Discount:</td>
                    <td class="value text-danger">
                        -${{ number_format($invoice->subtotal * ($invoice->discount_value / 100), 2) }}
                        ({{ $invoice->discount_value }}%)
                    </td>
                </tr>
                @else
                <tr>
                    <td class="label">Discount:</td>
                    <td class="value text-danger">
                        -${{ number_format($invoice->discount_value, 2) }}
                    </td>
                    </td>
                @endif
        @endif
        @if (!empty($invoice->tax_amount_formatted))
            <tr>
                <td class="label">VAT:</td>
                <td class="value">{{ $invoice->tax_amount_formatted }}</td>
            </tr>
        @endif
        <tr>
            <td class="label"><strong>Total:</strong></td>
            <td class="value"><strong>{{ number_format($invoice->total_amount, 2) }}</strong></td>
        </tr>
    </table>

    <div class="separator"></div>

    <div class="footer">
        <div>THANK YOU FOR YOUR PURCHASE!</div>
        <div>{{ config('app.name') }}</div>
    </div>
</body>

</html>

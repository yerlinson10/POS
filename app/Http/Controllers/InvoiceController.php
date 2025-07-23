<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    protected InvoiceService $service;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir', 'status']);
        $invoices = $this->service->filterAndPaginate($filters);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices->through(fn($invoice) => [
                'id' => $invoice->id,
                'date' => $invoice->date->format('Y-m-d'),
                'customer' => $invoice->customer ? [
                    'id' => $invoice->customer->id,
                    'name' => $invoice->customer->first_name . ' ' . $invoice->customer->last_name,
                    'email' => $invoice->customer->email,
                ] : null,
                'total_amount' => $invoice->total_amount,
                'status' => $invoice->status,
                'payment_method' => $invoice->payment_method,
                'items_count' => $invoice->items->count(),
                'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
            ]),
            'filters' => $filters,
            'statuses' => ['quotation', 'paid', 'canceled'],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = $this->service->find($id);

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $this->authorize('view', $invoice);

        return Inertia::render('Invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'date' => $invoice->date->format('Y-m-d'),
                'customer_id' => $invoice->customer_id,
                'customer' => $invoice->customer ? [
                    'id' => $invoice->customer->id,
                    'name' => $invoice->customer->first_name . ' ' . $invoice->customer->last_name,
                    'email' => $invoice->customer->email,
                    'phone' => $invoice->customer->phone,
                    'address' => $invoice->customer->address,
                ] : null,
                'subtotal_amount' => $invoice->subtotal,
                'discount_type' => $invoice->discount_type,
                'discount_value' => $invoice->discount_value,
                'discount_amount' => $invoice->discount_amount,
                'tax_amount' => $invoice->tax_amount,
                'total_amount' => $invoice->total_amount,
                'status' => $invoice->status,
                'payment_method' => $invoice->payment_method,
                'items' => $invoice->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'sku' => $item->product->sku,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_amount' => $item->line_total,
                ]),
                'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
            ],
            // Pass any flash data that might contain stock errors
            'stock_error' => session('stock_error'),
            'message' => session('message'),
        ]);
    }

    /**
     * Update invoice status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:quotation,paid,canceled'
        ]);

        try {
            $invoice = Invoice::findOrFail($id);

            $this->authorize('update', $invoice);

            // Additional validation for status transitions
            $this->validateStatusTransition($invoice->status, $request->status);

            $this->service->updateStatus($id, $request->status);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invoice status updated successfully.'
                ]);
            }

            // Redirect back with success message for regular requests
            return redirect()->back()
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Invoice status updated successfully.'
                ]);
        } catch (\Throwable $th) {

            // Try to decode the exception message as JSON to check for structured error
            $errorData = json_decode($th->getMessage(), true);

            if ($errorData && is_array($errorData) && isset($errorData['error']) && $errorData['error'] === 'insufficient_stock') {
                // Handle structured stock validation error
                $message = $this->formatStockErrorMessage($errorData);

                if ($request->expectsJson()) {
                    \Log::info("Returning JSON response for stock error");
                    return response()->json([
                        'message' => 'Insufficient stock',
                        'error' => $errorData,
                        'formatted_message' => $message
                    ], 422);
                }

                // For Inertia requests, use redirect with session data
                return redirect()->back()
                    ->with('stock_error', $errorData)
                    ->with('message', [
                        'type' => 'error',
                        'text' => $message
                    ])
                    ->withErrors([
                        'stock' => 'Insufficient stock for some products'
                    ]);
            }

            // Handle other errors normally - simplified
            $errorMessage = $th->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error updating invoice status',
                    'error' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error: ' . $errorMessage
                ]);
        }
    }    /**
         * Format stock error message for display
         */
    private function formatStockErrorMessage(array $errorData): string
    {
        $message = "Cannot change invoice #{$errorData['invoice_id']} status for customer {$errorData['customer']} due to insufficient stock:\n\n";

        foreach ($errorData['unavailable_products'] as $product) {
            $message .= "• {$product['product_name']}: ";
            $message .= "Required {$product['required_quantity']}, ";
            $message .= "available {$product['available_stock']}, ";
            $message .= "missing {$product['missing_stock']}\n";
        }

        return $message;
    }

    /**
     * Validate status transitions based on business rules
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        // Define allowed transitions
        $allowedTransitions = [
            'quotation' => ['paid', 'canceled'],
            'canceled' => ['quotation'],
            'paid' => [], // No transitions allowed from paid status
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            throw new \Exception("Invalid current status: {$currentStatus}");
        }

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new \Exception("Cannot change status from '{$currentStatus}' to '{$newStatus}'. Invalid transition.");
        }
    }

    /**
     * Show the form for editing a quotation.
     */
    public function edit(string $id)
    {
        try {
            $invoice = $this->service->find($id);
            if (!$invoice) {
                return redirect()->route('invoices.index')
                    ->with('message', [
                        'type' => 'error',
                        'text' => 'Invoice not found.'
                    ]);
            }

            $this->authorize('update', $invoice);

            $data = $this->service->getQuotationForEdit((int) $id);
            return Inertia::render('Invoices/Edit', $data);
        } catch (\Exception $e) {
            return redirect()->route('invoices.show', $id)
                ->with('message', [
                    'type' => 'error',
                    'text' => $e->getMessage()
                ]);
        }
    }

    /**
     * Update the specified quotation.
     */
    public function update(UpdateInvoiceRequest $request, string $id)
    {
        $invoice = $this->service->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $this->authorize('update', $invoice);

        try {
            $invoice = $this->service->updateQuotation((int) $id, $request->validated());

            return response()->json([
                'message' => 'Quotation updated successfully',
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}

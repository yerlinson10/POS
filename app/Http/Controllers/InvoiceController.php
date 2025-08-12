<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use Inertia\Inertia;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
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
     *
     * @param Request $request
     *
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
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
     *
     * @param string|int $id
     *
     * @return \Inertia\Response
     */
    public function show(string|int $id): \Inertia\Response
    {
        $invoice = $this->service->find($id);

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $this->authorize('view', $invoice);

        InvoiceResource::withoutWrapping();
        return Inertia::render('Invoices/Show', [
            'invoice' => InvoiceResource::make($invoice),
            'stock_error' => session('stock_error'),
            'message' => session('message'),
        ]);
    }

    /**
     * Actualiza el estado de una factura.
     *
     * Este método maneja la transición de estados de la factura (cotización, pagada, cancelada).
     * Incluye validación de stock insuficiente y retorna mensajes estructurados para la UI.
     *
     * @param Request $request
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException Si la validación de estado falla.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si la factura no existe.
     *
     * @details
     * - Si ocurre un error de stock insuficiente, retorna un mensaje formateado y detalles del error.
     * - Si ocurre otro error, retorna mensaje de error genérico.
     */
    public function updateStatus(Request $request, string $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:quotation,paid,canceled'
        ]);

        try {
            $invoice = Invoice::findOrFail($id);

            $this->authorize('update', $invoice);

            // Additional validation for status transitions
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
    }
    /**
     * Formatea el mensaje de error de stock insuficiente para mostrarlo en la UI.
     *
     * @param array $errorData Datos estructurados del error de stock.
     * @return string Mensaje legible para el usuario.
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
     * Muestra el formulario para editar una cotización.
     *
     * Si la factura no existe, redirige con mensaje de error.
     * Si ocurre una excepción, redirige a la vista de la factura con el mensaje de error.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function edit(string $id): \Illuminate\Http\RedirectResponse|\Inertia\Response
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
     * Actualiza la cotización especificada.
     *
     * Si la factura no existe, retorna 404.
     * Si ocurre una excepción, retorna mensaje de error y código 422.
     *
     * @param UpdateInvoiceRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateInvoiceRequest $request, string $id): \Illuminate\Http\JsonResponse
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

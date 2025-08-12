<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;
use \App\Helpers\StockHelper;
use \App\DTOs\InvoiceEditDTO;


/**
 * Servicio para la gestión de facturas.
 *
 * Centraliza la lógica de negocio relacionada con facturas, incluyendo filtrado, edición,
 * validación de stock, transiciones de estado y actualización de cotizaciones.
 */
class InvoiceService
{
    /**
     * Filtra y pagina facturas según los filtros proporcionados.
     *
     * @param array $filters Filtros de búsqueda y paginación.
     * @return LengthAwarePaginator Paginador de facturas.
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $invoicesQuery = Invoice::with(['customer', 'items.product', 'user'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoices.id', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->withAdvancedFilters($filters, [
                'id',
                'date',
                'total_amount',
                'status',
                'created_at',
                'customer.first_name',
                'customer.last_name'
            ]);

        return $invoicesQuery
            ->paginate($perPage)
            ->appends($filters);
    }


    /**
     * Busca una factura por ID con relaciones cargadas.
     *
     * @param int $id ID de la factura.
     * @return Invoice|null Factura encontrada o null.
     */
    public function find(int $id): ?Invoice
    {
        return Invoice::with(['customer', 'items.product', 'user', 'debt', 'payments'])->find($id);
    }


    /**
     * Actualiza el estado de una factura, validando reglas de negocio y stock.
     *
     * @param int $id ID de la factura.
     * @param string $status Nuevo estado ('quotation', 'paid', 'canceled').
     * @return Invoice Factura actualizada.
     * @throws Exception Si la transición de estado no es válida o hay stock insuficiente.
     */
    public function updateStatus(int $id, string $status): Invoice
    {
        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($id);
        $oldStatus = $invoice->status;

        // Validar transición de estado
        $this->validateStatusTransition($oldStatus, $status);

        // Validar stock antes de cambiar a pagada
        if ($oldStatus === 'quotation' && $status === 'paid') {
            $stockValidation = StockHelper::validateStockAvailability($invoice);

            if (!$stockValidation['success']) {
                $customerName = 'Customer without name';
                if ($invoice->customer) {
                    $firstName = $invoice->customer->first_name ?? '';
                    $lastName = $invoice->customer->last_name ?? '';
                    $customerName = trim($firstName . ' ' . $lastName);
                    if (empty($customerName)) {
                        $customerName = $invoice->customer->email ?? 'Customer without name';
                    }
                }

                $errorResponse = [
                    'error' => 'insufficient_stock',
                    'message' => 'Some products do not have sufficient stock',
                    'invoice_id' => (int) $invoice->id,
                    'customer' => (string) $customerName,
                    'unavailable_products' => $stockValidation['unavailable_products']
                ];

                throw new Exception(json_encode($errorResponse));
            }
        }

        DB::beginTransaction();

        try {
            $invoice->update(['status' => $status]);

            // Descontar stock si pasa a pagada
            if ($oldStatus === 'quotation' && $status === 'paid') {
                foreach ($invoice->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock', $item->quantity);
                    }
                }
            }

            DB::commit();

            return $invoice->fresh(['customer', 'items.product']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Valida las transiciones de estado de la factura según reglas de negocio.
     *
     * @param string $currentStatus Estado actual.
     * @param string $newStatus Nuevo estado.
     * @throws Exception Si la transición no es válida.
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $allowedTransitions = [
            'quotation' => ['paid', 'canceled'],
            'canceled' => ['quotation'],
            'paid' => [],
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            throw new Exception("Invalid current status: {$currentStatus}");
        }

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new Exception("Cannot change status from '{$currentStatus}' to '{$newStatus}'. Invalid transition.");
        }
    }


    // La validación de stock ahora se realiza mediante StockHelper::validateStockAvailability
    /**
     * Obtiene los datos necesarios para editar una cotización y los encapsula en un DTO.
     *
     * @param int $id ID de la factura.
     * @return InvoiceEditDTO DTO con datos de factura, clientes y productos.
     * @throws Exception Si la factura no existe o no es una cotización.
     */
    public function getQuotationForEdit(int $id): InvoiceEditDTO
    {
        $invoice = $this->find($id);

        if (!$invoice) {
            throw new Exception('Invoice not found');
        }

        if ($invoice->status !== 'quotation') {
            throw new Exception('Only quotations can be edited.');
        }

        $customers = \App\Models\Customer::select('id', 'first_name', 'last_name', 'email', 'phone', 'address')
            ->orderBy('first_name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'full_name' => $c->first_name . ' ' . $c->last_name,
                'email' => $c->email,
                'phone' => $c->phone,
                'address' => $c->address,
            ])->toArray();

        $products = \App\Models\Product::with(['category', 'unitMeasure'])
            ->where('stock', '>', 0)
            ->select('id', 'name', 'sku', 'price', 'stock', 'category_id', 'unit_measure_id')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'price' => (float) $p->price,
                'stock' => (int) $p->stock,
                'category' => $p->category->name,
                'unit_measure' => $p->unitMeasure->code,
            ])->toArray();

        $invoiceArr = [
            'id' => $invoice->id,
            'date' => $invoice->date->format('Y-m-d'),
            'customer_id' => $invoice->customer_id,
            'customer' => $invoice->customer ? [
                'id' => $invoice->customer->id,
                'full_name' => $invoice->customer->first_name . ' ' . $invoice->customer->last_name,
                'email' => $invoice->customer->email,
                'phone' => $invoice->customer->phone,
                'address' => $invoice->customer->address,
            ] : null,
            'subtotal' => $invoice->subtotal,
            'discount_type' => $invoice->discount_type,
            'discount_value' => $invoice->discount_value,
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
                    'price' => (float) $item->product->price,
                    'stock' => (int) $item->product->stock,
                ],
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->toArray(),
            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
        ];

        return new InvoiceEditDTO($invoiceArr, $customers, $products);
    }


    /**
     * Actualiza una cotización existente con los datos proporcionados.
     *
     * @param int $id ID de la factura.
     * @param array $data Datos validados para la actualización.
     * @return Invoice Factura actualizada.
     * @throws Exception Si la factura no es una cotización o ocurre un error en la transacción.
     */
    public function updateQuotation(int $id, array $data): Invoice
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->status !== 'quotation') {
            throw new Exception('Only quotations can be edited.');
        }

        DB::beginTransaction();

        try {
            $invoice->update([
                'date' => $data['date'],
                'subtotal' => $data['subtotal'],
                'discount_type' => $data['discount_type'],
                'discount_value' => $data['discount_value'],
                'total_amount' => $data['total_amount'],
            ]);

            $invoice->items()->delete();

            foreach ($data['items'] as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'line_total' => $itemData['line_total'],
                ]);
            }

            DB::commit();

            return $invoice->load(['customer', 'items.product']);

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Error updating quotation: ' . $e->getMessage());
        }
    }
}

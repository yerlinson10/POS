<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;

class InvoiceService
{
    /**
     * Filtrar y paginar facturas.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(array $filters)
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

    public function find(int $id): ?Invoice
    {
        return Invoice::with(['customer', 'items.product', 'user', 'debt', 'payments'])->find($id);
    }

    public function updateStatus(int $id, string $status): Invoice
    {
        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($id);
        $oldStatus = $invoice->status;

        // Validate status transitions based on business rules
        $this->validateStatusTransition($oldStatus, $status);

        // Check stock availability before making any changes
        if ($oldStatus === 'quotation' && $status === 'paid') {

            $stockValidation = $this->validateStockAvailability($invoice);

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

            // Handle stock changes based on status changes
            if ($oldStatus === 'quotation' && $status === 'paid') {
                // Reduce stock when marking as paid (stock already validated)
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
            throw new Exception("Invalid current status: {$currentStatus}");
        }

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new Exception("Cannot change status from '{$currentStatus}' to '{$newStatus}'. Invalid transition.");
        }
    }

    /**
     * Validate stock availability for all items in an invoice
     */
    private function validateStockAvailability(Invoice $invoice): array
    {
        $unavailableProducts = [];
        $allStockAvailable = true;

        foreach ($invoice->items as $item) {
            $product = $item->product; // Use the loaded relationship instead of querying again
            if (!$product) {
                $unavailableProducts[] = [
                    'product_id' => $item->product_id,
                    'product_name' => 'Product not found',
                    'product_sku' => null,
                    'required_quantity' => (int) $item->quantity,
                    'available_stock' => 0,
                    'missing_stock' => (int) $item->quantity
                ];
                $allStockAvailable = false;
            } elseif ($product->stock < $item->quantity) {
                $unavailableProducts[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name ?? 'Product without name',
                    'product_sku' => $product->sku ?? null,
                    'required_quantity' => (int) $item->quantity,
                    'available_stock' => (int) $product->stock,
                    'missing_stock' => (int) ($item->quantity - $product->stock)
                ];
                $allStockAvailable = false;
            }
        }
        return [
            'success' => $allStockAvailable,
            'unavailable_products' => $unavailableProducts
        ];
    }

    /**
     * Get quotation for editing
     */
    public function getQuotationForEdit(int $id): array
    {
        $invoice = $this->find($id);

        if (!$invoice) {
            throw new Exception('Invoice not found');
        }

        // Only allow editing quotations
        if ($invoice->status !== 'quotation') {
            throw new Exception('Only quotations can be edited.');
        }

        // Get customers and products for the edit form
        $customers = \App\Models\Customer::select('id', 'first_name', 'last_name', 'email', 'phone', 'address')
            ->orderBy('first_name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'full_name' => $c->first_name . ' ' . $c->last_name,
                'email' => $c->email,
                'phone' => $c->phone,
                'address' => $c->address,
            ]);

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
            ]);

        return [
            'invoice' => [
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
                ]),
                'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
            ],
            'customers' => $customers,
            'products' => $products,
        ];
    }

    /**
     * Update quotation
     */
    public function updateQuotation(int $id, array $data): Invoice
    {
        $invoice = Invoice::findOrFail($id);
        // Only allow editing quotations
        if ($invoice->status !== 'quotation') {
            throw new Exception('Only quotations can be edited.');
        }

        DB::beginTransaction();

        try {
            // Update invoice basic info
            $invoice->update([
                'date' => $data['date'],
                'subtotal' => $data['subtotal'],
                'discount_type' => $data['discount_type'],
                'discount_value' => $data['discount_value'],
                'total_amount' => $data['total_amount'],
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Create new items
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

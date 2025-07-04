<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class InvoiceService
{
    public function find(int $id): ?Invoice
    {
        return Invoice::with(['customer', 'items.product', 'user'])->find($id);
    }

    public function updateStatus(int $id, string $status): Invoice
    {
        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($id);
        $oldStatus = $invoice->status;

        // Validate status transitions based on business rules
        $this->validateStatusTransition($oldStatus, $status);

        // Check stock availability before making any changes
        if ($oldStatus === 'pending' && $status === 'paid') {

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
            if ($oldStatus === 'pending' && $status === 'paid') {
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
            'pending' => ['paid', 'canceled'],
            'canceled' => ['pending'],
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
}

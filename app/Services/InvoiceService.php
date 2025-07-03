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
        $invoice = Invoice::findOrFail($id);
        $oldStatus = $invoice->status;

        // Validate status transitions based on business rules
        $this->validateStatusTransition($oldStatus, $status);

        DB::beginTransaction();

        try {
            $invoice->update(['status' => $status]);

            // Handle stock changes based on status changes
            if ($oldStatus === 'pending' && $status === 'paid') {
                // Reduce stock when marking as paid
                foreach ($invoice->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        if ($product->stock < $item->quantity) {
                            throw new Exception("Insufficient stock for product: {$product->name}");
                        }
                        $product->decrement('stock', $item->quantity);
                    }
                }
            } elseif ($oldStatus === 'canceled' && $status === 'pending') {
                // Check stock availability when reactivating
                foreach ($invoice->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product && $product->stock < $item->quantity) {
                        throw new Exception("Insufficient stock for product: {$product->name}");
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
}

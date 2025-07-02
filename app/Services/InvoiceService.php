<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function getFilteredInvoices(array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->with(['customer', 'user', 'items.product'])
            ->latest('created_at');

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($search) {
                        $customerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage)->withQueryString();
    }

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // Create the invoice
            $invoice = Invoice::create([
                'customer_id' => $data['customer_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'date' => $data['date'] ?? now(),
                'total_amount' => 0, // Will be calculated from items
                'status' => $data['status'] ?? 'paid',
            ]);

            $totalAmount = 0;

            // Create invoice items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Check stock availability
                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $unitPrice = $itemData['unit_price'] ?? $product->price;
                $lineTotal = $itemData['quantity'] * $unitPrice;

                // Create invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                // Update product stock
                $product->decrement('stock', $itemData['quantity']);

                $totalAmount += $lineTotal;
            }

            // Update invoice total
            $invoice->update(['total_amount' => $totalAmount]);

            return $invoice->load(['customer', 'user', 'items.product']);
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            // Restore stock from old items
            foreach ($invoice->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Delete old items
            $invoice->items()->delete();

            // Update invoice
            $invoice->update([
                'customer_id' => $data['customer_id'],
                'date' => $data['date'],
                'status' => $data['status'],
            ]);

            $totalAmount = 0;

            // Create new items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Check stock availability
                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $unitPrice = $itemData['unit_price'] ?? $product->price;
                $lineTotal = $itemData['quantity'] * $unitPrice;

                // Create invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                // Update product stock
                $product->decrement('stock', $itemData['quantity']);

                $totalAmount += $lineTotal;
            }

            // Update invoice total
            $invoice->update(['total_amount' => $totalAmount]);

            return $invoice->load(['customer', 'user', 'items.product']);
        });
    }

    public function deleteInvoice(Invoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            // Restore stock from items
            foreach ($invoice->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Delete invoice (items will be cascade deleted)
            return $invoice->delete();
        });
    }

    public function getDashboardStats(): array
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        return [
            'today_sales' => Invoice::where('date', $today)
                ->where('status', 'paid')
                ->sum('total_amount'),
            'month_sales' => Invoice::where('date', '>=', $startOfMonth)
                ->where('status', 'paid')
                ->sum('total_amount'),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'total_invoices' => Invoice::count(),
        ];
    }
}

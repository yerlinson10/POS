<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    public function index()
    {
        $stats = $this->invoiceService->getDashboardStats();

        // Get recent invoices
        $recentInvoices = Invoice::with(['customer', 'user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer->first_name . ' ' . $invoice->customer->last_name,
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'date' => $invoice->date,
                ];
            });

        // Get low stock products (stock <= 10)
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'price' => $product->price,
                ];
            });

        // Get out of stock products
        $outOfStockProducts = Product::where('stock', '<=', 0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                ];
            });

        // Get sales chart data (last 7 days)
        $salesChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $sales = Invoice::where('date', $date)
                ->where('status', 'paid')
                ->sum('total_amount');

            $salesChartData[] = [
                'date' => $date,
                'sales' => (float) $sales,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'salesChartData' => $salesChartData,
        ]);
    }
}

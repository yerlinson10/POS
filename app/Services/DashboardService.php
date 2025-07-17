<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\PosSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all dashboard data
     */
    public function getDashboardData(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return [
            'stats' => $this->getGeneralStats(),
            'todaySales' => $this->getTodaySales($today),
            'monthlySales' => $this->getMonthlySales($startOfMonth, $endOfMonth),
            'lowStockProducts' => $this->getLowStockProducts(),
            'recentSales' => $this->getRecentSales(),
            'salesChart' => $this->getSalesChartData(),
            'topProducts' => $this->getTopProducts(),
        ];
    }

    /**
     * Get general statistics
     */
    private function getGeneralStats(): array
    {
        return [
            'total_customers' => Customer::count(),
            'total_products' => Product::count(),
            'total_categories' => DB::table('categories')->count(),
            'low_stock_count' => Product::where('stock', '<=', 10)->count(),
        ];
    }

    /**
     * Get today's sales data
     */
    private function getTodaySales(Carbon $today): array
    {
        $startOfDay = $today->copy()->startOfDay();
        $endOfDay = $today->copy()->endOfDay();

        $todayInvoices = Invoice::where('date', '>=', $startOfDay)
            ->where('date', '<=', $endOfDay)
            ->where('status', 'paid')
            ->get();

        return [
            'count' => $todayInvoices->count(),
            'total' => $todayInvoices->sum('total_amount'),
            'cash_sales' => $todayInvoices->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $todayInvoices->where('payment_method', 'card')->sum('total_amount'),
        ];
    }

    /**
     * Get monthly sales data
     */
    private function getMonthlySales(Carbon $startOfMonth, Carbon $endOfMonth): array
    {
        $monthlyInvoices = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'paid')
            ->get();

        return [
            'count' => $monthlyInvoices->count(),
            'total' => $monthlyInvoices->sum('total_amount'),
            'average_per_day' => $monthlyInvoices->count() > 0
                ? $monthlyInvoices->sum('total_amount') / Carbon::now()->day
                : 0,
        ];
    }

    /**
     * Get low stock products
     */
    private function getLowStockProducts(): array
    {
        return Product::with(['category', 'unitMeasure'])
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'category' => $product->category->name,
                    'unit_measure' => $product->unitMeasure->code,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent sales
     */
    private function getRecentSales(): array
    {
        return Invoice::with(['customer', 'user'])
            ->where('status', 'paid')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'date' => $invoice->date,
                    'customer' => $invoice->customer->full_name,
                    'total_amount' => $invoice->total_amount,
                    'payment_method' => $invoice->payment_method,
                ];
            })
            ->toArray();
    }

    /**
     * Get sales chart data for the last 7 days
     */
    private function getSalesChartData(): array
    {
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $sales = Invoice::where('date', '>=', $startOfDay)
                ->where('date', '<=', $endOfDay)
                ->where('status', 'paid')
                ->sum('total_amount');

            return [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'sales' => (float) $sales,
            ];
        });

        return $last7Days->values()->toArray();
    }

    /**
     * Get top selling products
     */
    private function getTopProducts(): array
    {
        return DB::table('invoice_items')
            ->select('products.name', 'products.id', DB::raw('SUM(invoice_items.quantity) as total_sold'))
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', 'paid')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'total_sold' => (int) $product->total_sold,
                ];
            })
            ->toArray();
    }
}

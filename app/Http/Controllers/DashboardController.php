<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\PosSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Estadísticas generales
        $stats = $this->getGeneralStats();

        // Ventas de hoy
        $todaySales = $this->getTodaySales($today);

        // Ventas del mes
        $monthlySales = $this->getMonthlySales($startOfMonth, $endOfMonth);

        // Productos con bajo stock
        $lowStockProducts = $this->getLowStockProducts();

        // Últimas ventas
        $recentSales = $this->getRecentSales();

        // Gráfico de ventas por día (últimos 7 días)
        $salesChart = $this->getSalesChartData();

        // Top productos vendidos
        $topProducts = $this->getTopProducts();

        // Sesión POS activa
        $activeSession = PosSession::getActiveSession($userId);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'todaySales' => $todaySales,
            'monthlySales' => $monthlySales,
            'lowStockProducts' => $lowStockProducts,
            'recentSales' => $recentSales,
            'salesChart' => $salesChart,
            'topProducts' => $topProducts,
            'activeSession' => $activeSession,
        ]);
    }

    private function getGeneralStats()
    {
        return [
            'total_customers' => Customer::count(),
            'total_products' => Product::count(),
            'total_categories' => DB::table('categories')->count(),
            'low_stock_count' => Product::where('stock', '<=', 10)->count(),
        ];
    }

    private function getTodaySales($today)
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

    private function getMonthlySales($startOfMonth, $endOfMonth)
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

    private function getLowStockProducts()
    {
        return Product::with(['category', 'unitMeasure'])
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => $product->stock,
                    'category' => $product->category->name,
                    'unit' => $product->unitMeasure->code,
                ];
            });
    }

    private function getRecentSales()
    {
        return Invoice::with(['customer', 'user'])
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'customer_name' => $invoice->customer->full_name,
                    'total_amount' => $invoice->total_amount,
                    'payment_method' => $invoice->payment_method,
                    'date' => $invoice->date->format('Y-m-d H:i'),
                    'user_name' => $invoice->user->name ?? 'N/A',
                ];
            });
    }

    private function getSalesChartData()
    {
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
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

        return $last7Days->values();
    }

    private function getTopProducts()
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.status', 'paid')
            ->where('invoices.date', '>=', Carbon::now()->startOfMonth())
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(invoice_items.quantity) as total_sold'),
                DB::raw('SUM(invoice_items.line_total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'total_sold' => (int) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });
    }
}

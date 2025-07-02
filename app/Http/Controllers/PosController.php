<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display the POS interface.
     */
    public function index()
    {
        $customers = Customer::select('id', 'first_name', 'last_name', 'email', 'phone')->get();
        $products = Product::with(['category', 'unitMeasure'])
            ->select('id', 'sku', 'name', 'price', 'stock', 'category_id', 'unit_measure_id')
            ->where('stock', '>', 0)
            ->get();

        $stats = $this->invoiceService->getDashboardStats();

        return Inertia::render('POS/Index', [
            'customers' => $customers,
            'products' => $products,
            'stats' => $stats,
        ]);
    }

    /**
     * Process a sale through POS.
     */
    public function processSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        try {
            $saleData = [
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'date' => now(),
                'status' => 'paid',
                'items' => $request->items,
            ];

            $invoice = $this->invoiceService->createInvoice($saleData);

            return response()->json([
                'success' => true,
                'message' => 'Sale processed successfully',
                'invoice' => $invoice,
                'change' => $request->amount_paid - $invoice->total_amount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Search products for POS.
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('search', '');

        $products = Product::with(['category', 'unitMeasure'])
            ->select('id', 'sku', 'name', 'price', 'stock', 'category_id', 'unit_measure_id')
            ->where('stock', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    /**
     * Get customers for POS.
     */
    public function searchCustomers(Request $request)
    {
        $search = $request->get('search', '');

        $customers = Customer::select('id', 'first_name', 'last_name', 'email', 'phone')
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}

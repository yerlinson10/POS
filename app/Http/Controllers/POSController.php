<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ProductService;
use App\Services\CustomerService;

class POSController extends Controller
{
    protected ProductService $productService;
    protected CustomerService $customerService;

    public function __construct(ProductService $productService, CustomerService $customerService)
    {
        $this->productService = $productService;
        $this->customerService = $customerService;
    }

    /**
     * Display the POS interface.
     */
    public function index()
    {
        return Inertia::render('POS/Index');
    }

    /**
     * Get products for POS with pagination and search.
     */
    public function getProducts(Request $request)
    {
        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir', 'current_page']);
        $perPage = (int) ($filters['per_page'] ?? 20);
        $currentPage = (int) ($filters['current_page'] ?? 1);

        $productsQuery = Product::with(['category', 'unitMeasure'])
            ->where('stock', '>', 0) // Only show products with stock
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%");
                })
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->withAdvancedFilters($filters, [
                'id',
                'sku',
                'name',
                'price',
                'stock',
                'category.name',
                'unitMeasure.code'
            ]);

        // Set the current page
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $products = $productsQuery
            ->paginate($perPage)
            ->appends($filters);

        return response()->json([
            'products' => [
                'data' => $products->through(fn($p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'category' => $p->category->name,
                    'unit_measure' => $p->unitMeasure->code,
                    'price' => (float) $p->price,
                    'stock' => (int) $p->stock,
                    'image' => $p->image ?? null,
                ])->items(),
            ],
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'filters' => $filters,
        ]);
    }

    /**
     * Get customers for POS with search.
     */
    public function getCustomers(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $customersQuery = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->limit($limit);

        $customers = $customersQuery->get()->map(fn($c) => [
            'id' => $c->id,
            'full_name' => $c->first_name . ' ' . $c->last_name,
            'email' => $c->email,
            'phone' => $c->phone,
            'address' => $c->address,
        ]);

        return response()->json(['customers' => $customers]);
    }

    /**
     * Create a new customer from POS.
     */
    public function createCustomer(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            $customer = $this->customerService->create($validated);

            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'full_name' => $customer->first_name . ' ' . $customer->last_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                ],
                'message' => 'Customer created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating customer: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Process a sale/invoice.
     */
    public function processSale(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.line_total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Verify stock availability
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
            }

            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'date' => now(),
                'total_amount' => $validated['total_amount'],
                'status' => 'completed',
                'subtotal' => $validated['subtotal'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
            ]);

            // Create invoice items and update stock
            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'invoice' => $invoice->load(['customer', 'items.product']),
                'message' => 'Sale processed successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing sale: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get real-time product updates (for stock changes).
     */
    public function getProductUpdates(Request $request)
    {
        $productIds = $request->get('product_ids', []);

        if (empty($productIds)) {
            return response()->json(['products' => []]);
        }

        $products = Product::whereIn('id', $productIds)
            ->select('id', 'stock', 'price')
            ->get()
            ->keyBy('id')
            ->map(fn($p) => [
                'stock' => (int) $p->stock,
                'price' => (float) $p->price,
            ]);

        return response()->json(['products' => $products]);
    }
}

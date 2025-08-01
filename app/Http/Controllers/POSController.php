<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\CustomerService;
use App\Services\PosSessionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;

class POSController extends Controller
{
    protected ProductService $productService;
    protected CustomerService $customerService;
    protected PosSessionService $posSessionService;

    public function __construct(
        ProductService $productService,
        CustomerService $customerService,
        PosSessionService $posSessionService
    ) {
        $this->productService = $productService;
        $this->customerService = $customerService;
        $this->posSessionService = $posSessionService;
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
        $currentPage = (int) ($filters['current_page'] ?? 1);

        // Resolver la página actual para la paginación
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        // Usar el servicio para obtener los productos filtrados y paginados
        $products = $this->productService->filterAndPaginate($filters);

        // Filtrar productos con stock > 0 (si no se hace en el servicio)
        $products->getCollection()->transform(function ($p) {
            return [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'category' => $p->category->name,
                'unit_measure' => $p->unitMeasure->code,
                'price' => (float) $p->price,
                'stock' => (int) $p->stock,
                'image' => $p->image ?? null,
            ];
        });

        $filteredData = $products->getCollection()->filter(fn($p) => $p['stock'] > 0)->values();

        return response()->json([
            'products' => [
                'data' => $filteredData,
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
        $filters = [
            'search' => $request->get('search'),
            'per_page' => 1000 // Un valor alto para traer todos y luego limitar
        ];
        $limit = (int) $request->get('limit', 10);

        $customers = $this->customerService->filterAndPaginate($filters)
            ->getCollection()
            ->take($limit)
            ->map(fn($c) => [
                'id' => $c->id,
                'full_name' => $c->full_name,
                'email' => $c->email,
                'phone' => $c->phone,
                'address' => $c->address,
            ]);

        return response()->json(['customers' => $customers]);
    }

    /**
     * Create a new customer from POS.
     */
    public function createCustomer(StoreCustomerRequest $data)
    {

        try {
            $customer = $this->customerService->create($data->validated());

            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
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
    public function processSale(StoreSaleRequest $data)
    {
        $validated = $data->validated();

        try {
            // Verificar que hay una sesión POS activa
            $activeSession = $this->posSessionService->getActiveSession();
            if (!$activeSession) {
                return response()->json([
                    'message' => 'There is no active POS session. You must open one before processing sales.',
                    'requires_session' => true
                ], 422);
            }

            DB::beginTransaction();

            // Only query products once and map by id for efficiency
            $productIds = collect($validated['items'])->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // Check stock only if the invoice is paid
            if ($validated['status'] === 'paid') {
                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);
                    if (!$product) {
                        throw new \Exception("Product not found: ID {$item['product_id']}");
                    }
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}");
                    }
                }
            }

            // Create the invoice with session reference
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'] ?? 1,
                'user_id' => Auth::id(),
                'pos_session_id' => $activeSession->id,
                'date' => now(),
                'total_amount' => $validated['total_amount'],
                'status' => $validated['status'],
                'subtotal' => $validated['subtotal'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
            ]);

            // Create the items and update stock if applicable
            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);

                if ($validated['status'] === 'paid') {
                    $product = $products->get($item['product_id']);
                    $product->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            $statusMessages = [
                'paid' => 'Sale processed and payment completed successfully',
                'quotation' => 'Quotation registered successfully, payment is pending'
            ];

            return response()->json([
                'invoice' => $invoice->load(['customer', 'items.product', 'posSession']),
                'session' => $activeSession,
                'message' => $statusMessages[$validated['status']]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing sale: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Process a sale with debt creation.
     */
    public function processSaleWithDebt(Request $request)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after:today',
            'description' => 'nullable|string|max:255',
            'cart_data' => 'required|array',
            'cart_data.customer_id' => 'required|exists:customers,id',
            'cart_data.total_amount' => 'required|numeric|min:0',
            'cart_data.subtotal' => 'required|numeric|min:0',
            'cart_data.items' => 'required|array|min:1',
        ]);

        try {
            // Verificar que hay una sesión POS activa
            $activeSession = $this->posSessionService->getActiveSession();
            if (!$activeSession) {
                return response()->json([
                    'message' => 'There is no active POS session. You must open one before processing sales.',
                    'requires_session' => true
                ], 422);
            }

            $cartData = $request->cart_data;
            $paidAmount = $request->paid_amount;
            $totalAmount = $cartData['total_amount'];
            
            // Validar que el monto pagado no sea mayor al total
            if ($paidAmount > $totalAmount) {
                return response()->json([
                    'message' => 'Paid amount cannot be greater than total amount'
                ], 422);
            }
            
            DB::beginTransaction();

            // Verificar stock para todos los productos
            $productIds = collect($cartData['items'])->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($cartData['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: ID {$item['product_id']}");
                }
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
            }

            // Crear la factura
            $invoice = Invoice::create([
                'customer_id' => $cartData['customer_id'],
                'user_id' => Auth::id(),
                'pos_session_id' => $activeSession->id,
                'date' => now(),
                'total_amount' => $totalAmount,
                'status' => $paidAmount >= $totalAmount ? 'paid' : 'quotation',
                'subtotal' => $cartData['subtotal'],
                'discount_type' => $cartData['discount_type'] ?? null,
                'discount_value' => $cartData['discount_value'] ?? 0,
                'payment_method' => $paidAmount >= $totalAmount ? ($cartData['payment_method'] ?? 'cash') : 'mixed',
            ]);

            // Crear los items de la factura
            foreach ($cartData['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);

                // Reducir stock solo si hay pago (aunque sea parcial)
                if ($paidAmount > 0) {
                    $product = $products->get($item['product_id']);
                    $product->decrement('stock', $item['quantity']);
                }
            }

            // Crear la deuda del cliente si hay monto pendiente
            $debtAmount = $totalAmount - $paidAmount;
            
            if ($debtAmount > 0) {
                \App\Models\CustomerDebt::create([
                    'customer_id' => $cartData['customer_id'],
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'original_amount' => $debtAmount,
                    'remaining_amount' => $debtAmount,
                    'paid_amount' => 0, // La deuda inicia sin pagos
                    'debt_date' => now()->toDateString(),
                    'due_date' => $request->due_date,
                    'status' => 'pending',
                    'notes' => $request->description ?? "Debt created from POS sale - Invoice #{$invoice->id}",
                ]);
            }

            // Registrar el pago parcial si hay uno
            if ($paidAmount > 0) {
                \App\Models\Payment::create([
                    'payment_type' => 'income',
                    'amount' => $paidAmount,
                    'payment_method' => $cartData['payment_method'] ?? 'cash',
                    'category' => 'sales',
                    'description' => "Payment for invoice #{$invoice->id}" . ($debtAmount > 0 ? " (Partial payment)" : ""),
                    'customer_id' => $cartData['customer_id'],
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'invoice' => $invoice->load(['customer', 'items.product']),
                'debt_amount' => $debtAmount,
                'paid_amount' => $paidAmount,
                'message' => $debtAmount > 0 
                    ? 'Sale processed with debt created successfully' 
                    : 'Sale processed and payment completed successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing sale with debt: ' . $e->getMessage()
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

    /**
     * Get active POS session status for the current user.
     */
    public function getSessionStatus()
    {
        $activeSession = $this->posSessionService->getActiveSession();

        return response()->json([
            'has_active_session' => $activeSession !== null,
            'session' => $activeSession ? [
                'id' => $activeSession->id,
                'opened_at' => $activeSession->opened_at,
                'initial_cash' => $activeSession->initial_cash,
                'user_name' => $activeSession->user->name,
            ] : null,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\InvoiceDebt;
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
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,transfer,other',
            'payment_amount' => 'required|numeric|min:0',
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

            // Calcular totales
            $subtotal = collect($request->items)->sum('total');
            $discount = $request->discount ?? 0;
            $totalAmount = $subtotal - $discount;
            $paidAmount = $request->payment_amount;

            // Validar que el monto pagado sea menor al total (para crear deuda)
            if ($paidAmount >= $totalAmount) {
                return response()->json([
                    'message' => 'Paid amount must be less than total amount to create debt'
                ], 422);
            }

            $debtAmount = $totalAmount - $paidAmount;

            DB::beginTransaction();

            // Verificar stock para todos los productos
            $productIds = collect($request->items)->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
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
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id(),
                'pos_session_id' => $activeSession->id,
                'date' => now(),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'debt_amount' => $debtAmount,
                'status' => $paidAmount >= $totalAmount ? 'paid' : 'unpaid',
                'payment_status' => $paidAmount <= 0 ? 'debt' : ($paidAmount >= $totalAmount ? 'paid' : 'partial'),
                'subtotal' => $subtotal,
                'discount_type' => $discount > 0 ? 'fixed' : null,
                'discount_value' => $discount,
                'payment_method' => $paidAmount >= $totalAmount ? $request->payment_method : 'mixed',
                'due_date' => now()->addDays(15), // 15 días para pagar la deuda
            ]);

            // Crear los items de la factura
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['total'],
                ]);

                // Reducir stock solo si hay pago (aunque sea parcial)
                if ($paidAmount > 0) {
                    $product = $products->get($item['product_id']);
                    $product->decrement('stock', $item['quantity']);
                }
            }

            // Crear la deuda del cliente si hay monto pendiente
            $debt = null;
            if ($debtAmount > 0) {
                $debt = \App\Models\CustomerDebt::create([
                    'customer_id' => $request->customer_id,
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'original_amount' => $totalAmount, // Monto original de la factura completa
                    'remaining_amount' => $debtAmount, // Monto que aún debe
                    'paid_amount' => $paidAmount, // Monto ya pagado
                    'debt_date' => now()->toDateString(),
                    'status' => 'pending',
                    'due_date' => now()->addDays(30)->toDateString(), // Default 30 days
                    'notes' => "Debt from Invoice ID #{$invoice->id} - Partial payment sale",
                ]);

                // Crear la relación entre invoice y debt usando el pivot
                \App\Models\InvoiceDebt::create([
                    'invoice_id' => $invoice->id,
                    'customer_debt_id' => $debt->id,
                    'debt_amount' => $debtAmount, // Opcional: para referencia rápida
                    'notes' => "Debt association for partial payment sale",
                ]);
            } else {
                // Si se paga completo, crear una deuda con estado 'paid' para mantener historial
                $debt = \App\Models\CustomerDebt::create([
                    'customer_id' => $request->customer_id,
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'original_amount' => $totalAmount,
                    'remaining_amount' => 0,
                    'paid_amount' => $totalAmount,
                    'debt_date' => now()->toDateString(),
                    'status' => 'paid',
                    'due_date' => now()->toDateString(),
                    'notes' => "Full payment for Invoice ID #{$invoice->id}",
                ]);

                // Crear la relación entre invoice y debt usando el pivot
                \App\Models\InvoiceDebt::create([
                    'invoice_id' => $invoice->id,
                    'customer_debt_id' => $debt->id,
                    'debt_amount' => 0,
                    'notes' => "Full payment association",
                ]);
            }

            // Registrar el pago si hay uno (tanto si es pago completo como parcial)
            if ($paidAmount > 0) {
                \App\Models\Payment::create([
                    'type' => 'income',
                    'category' => $debtAmount > 0 ? 'debt_payment' : 'sales',
                    'amount' => $paidAmount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => now()->toDateString(),
                    'description' => "Payment for invoice ID #{$invoice->id}" . ($debtAmount > 0 ? " (Partial payment)" : " (Full payment)"),
                    'customer_id' => $request->customer_id,
                    'customer_debt_id' => $debt->id, // Relacionar con la deuda (siempre existe ahora)
                    'user_id' => Auth::id(),
                    'pos_session_id' => $activeSession->id,
                    'reference_number' => "INV-{$invoice->id}-" . strtoupper(substr(md5(time()), 0, 6)),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
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

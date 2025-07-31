<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Services\CustomerService;
use App\Services\SupplierService;
use App\Services\CustomerDebtService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    protected PaymentService $service;
    protected CustomerService $customerService;
    protected SupplierService $supplierService;
    protected CustomerDebtService $debtService;

    public function __construct(
        PaymentService $service,
        CustomerService $customerService,
        SupplierService $supplierService,
        CustomerDebtService $debtService
    ) {
        $this->service = $service;
        $this->customerService = $customerService;
        $this->supplierService = $supplierService;
        $this->debtService = $debtService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('payments:view');

        $filters = $request->only([
            'per_page', 'search', 'sort_by', 'sort_dir', 'type', 'category',
            'payment_method', 'customer_id', 'supplier_id', 'date_from',
            'date_to', 'amount_from', 'amount_to'
        ]);
        
        $payments = $this->service->filterAndPaginate($filters);

        return Inertia::render('Payments/Index', [
            'payments' => $payments->through(fn($payment) => [
                'id' => $payment->id,
                'type' => $payment->type,
                'category' => $payment->category,
                'category_label' => $payment->category_label,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_method_label' => $payment->payment_method_label,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'description' => $payment->description,
                'reference_number' => $payment->reference_number,
                'customer' => $payment->customer ? [
                    'id' => $payment->customer->id,
                    'full_name' => $payment->customer->full_name,
                ] : null,
                'supplier' => $payment->supplier ? [
                    'id' => $payment->supplier->id,
                    'company_name' => $payment->supplier->company_name,
                ] : null,
                'user' => $payment->user?->name,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            ]),
            'filters' => $filters,
            'stats' => $this->service->getStats(),
            'financial_summary' => $this->service->getFinancialSummary($filters),
            'customers' => $this->customerService->all()->map(fn($customer) => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
            ]),
            'suppliers' => $this->supplierService->getAllActive()->map(fn($supplier) => [
                'id' => $supplier->id,
                'company_name' => $supplier->company_name,
            ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('payments:create');

        $type = $request->get('type', 'income');
        $category = $request->get('category');

        return Inertia::render('Payments/Form', [
            'payment' => null,
            'type' => $type,
            'category' => $category,
            'customers' => $this->customerService->all()->map(fn($customer) => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
            ]),
            'suppliers' => $this->supplierService->getAllActive()->map(fn($supplier) => [
                'id' => $supplier->id,
                'company_name' => $supplier->company_name,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('payments:create');

        $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,check,other',
            'payment_date' => 'required|date',
            'description' => 'required|string|max:500',
            'reference_number' => 'nullable|string|max:50',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->create($request->all());

            return redirect()->route('payments.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Pago registrado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al registrar pago: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('payments:show');

        $payment = $this->service->find($id);

        if (!$payment) {
            return redirect()->route('payments.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Pago no encontrado.'
                ]);
        }

        return Inertia::render('Payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'type' => $payment->type,
                'category' => $payment->category,
                'category_label' => $payment->category_label,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_method_label' => $payment->payment_method_label,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'description' => $payment->description,
                'reference_number' => $payment->reference_number,
                'notes' => $payment->notes,
                'metadata' => $payment->metadata,
                'customer' => $payment->customer ? [
                    'id' => $payment->customer->id,
                    'full_name' => $payment->customer->full_name,
                    'email' => $payment->customer->email,
                    'phone' => $payment->customer->phone,
                ] : null,
                'supplier' => $payment->supplier ? [
                    'id' => $payment->supplier->id,
                    'company_name' => $payment->supplier->company_name,
                    'contact_name' => $payment->supplier->contact_name,
                    'email' => $payment->supplier->email,
                ] : null,
                'customer_debt' => $payment->customerDebt ? [
                    'id' => $payment->customerDebt->id,
                    'invoice_id' => $payment->customerDebt->invoice_id,
                    'remaining_amount' => $payment->customerDebt->remaining_amount,
                ] : null,
                'user' => $payment->user?->name,
                'pos_session' => $payment->posSession ? [
                    'id' => $payment->posSession->id,
                    'opened_at' => $payment->posSession->opened_at->format('Y-m-d H:i:s'),
                ] : null,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $payment->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('payments:edit');

        $payment = $this->service->find($id);

        if (!$payment) {
            return redirect()->route('payments.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Pago no encontrado.'
                ]);
        }

        // No permitir editar pagos de deuda o pagos a proveedores automáticos
        if (in_array($payment->category, ['debt_payment', 'supplier_payment'])) {
            return redirect()->route('payments.show', $payment->id)
                ->with('message', [
                    'type' => 'warning',
                    'text' => 'Este tipo de pago no puede ser editado.'
                ]);
        }

        return Inertia::render('Payments/Form', [
            'payment' => [
                'id' => $payment->id,
                'type' => $payment->type,
                'category' => $payment->category,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'description' => $payment->description,
                'reference_number' => $payment->reference_number,
                'customer_id' => $payment->customer_id,
                'supplier_id' => $payment->supplier_id,
                'notes' => $payment->notes,
            ],
            'customers' => $this->customerService->all()->map(fn($customer) => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
            ]),
            'suppliers' => $this->supplierService->getAllActive()->map(fn($supplier) => [
                'id' => $supplier->id,
                'company_name' => $supplier->company_name,
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('payments:edit');

        $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,check,other',
            'payment_date' => 'required|date',
            'description' => 'required|string|max:500',
            'reference_number' => 'nullable|string|max:50',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $payment = $this->service->find($id);
            
            // No permitir editar pagos de deuda o pagos a proveedores automáticos
            if (in_array($payment->category, ['debt_payment', 'supplier_payment'])) {
                throw new \Exception('Este tipo de pago no puede ser editado.');
            }

            $this->service->update($id, $request->all());

            return redirect()->route('payments.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Pago actualizado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al actualizar pago: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('payments:delete');

        try {
            $this->service->delete($id);

            return redirect()->route('payments.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Pago eliminado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al eliminar pago: ' . $th->getMessage()
                ]);
        }
    }

    /**
     * Registrar pago de deuda de cliente.
     */
    public function recordDebtPayment(Request $request)
    {
        $this->authorize('payments:create');

        $request->validate([
            'customer_debt_id' => 'required|exists:customer_debts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,check,other',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->service->recordCustomerDebtPayment(
                $request->customer_debt_id,
                $request->amount,
                $request->payment_method,
                $request->description
            );

            return response()->json([
                'message' => 'Pago de deuda registrado exitosamente.',
                'type' => 'success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al registrar pago: ' . $th->getMessage(),
                'type' => 'error'
            ], 400);
        }
    }

    /**
     * Registrar pago a proveedor.
     */
    public function recordSupplierPayment(Request $request)
    {
        $this->authorize('payments:create');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,check,other',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->service->recordSupplierPayment(
                $request->supplier_id,
                $request->amount,
                $request->payment_method,
                $request->description
            );

            return response()->json([
                'message' => 'Pago a proveedor registrado exitosamente.',
                'type' => 'success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al registrar pago: ' . $th->getMessage(),
                'type' => 'error'
            ], 400);
        }
    }

    /**
     * Dashboard de pagos del día.
     */
    public function dashboard(Request $request)
    {
        $this->authorize('payments:view');

        return Inertia::render('Payments/Dashboard', [
            'today_payments' => $this->service->getTodayPayments()->map(fn($payment) => [
                'id' => $payment->id,
                'type' => $payment->type,
                'category_label' => $payment->category_label,
                'amount' => $payment->amount,
                'payment_method_label' => $payment->payment_method_label,
                'description' => $payment->description,
                'customer' => $payment->customer?->full_name,
                'supplier' => $payment->supplier?->company_name,
                'user' => $payment->user?->name,
                'created_at' => $payment->created_at->format('H:i:s'),
            ]),
            'stats' => $this->service->getStats(),
            'financial_summary' => $this->service->getFinancialSummary([
                'date_from' => now()->startOfMonth()->toDateString(),
                'date_to' => now()->toDateString(),
            ]),
        ]);
    }
}

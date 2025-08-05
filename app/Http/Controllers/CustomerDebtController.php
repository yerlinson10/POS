<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\CustomerDebt;
use Illuminate\Http\Request;
use App\Services\CustomerDebtService;
use App\Services\CustomerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerDebtController extends Controller
{
    use AuthorizesRequests;

    protected CustomerDebtService $service;
    protected CustomerService $customerService;

    public function __construct(CustomerDebtService $service, CustomerService $customerService)
    {
        $this->service = $service;
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('customer_debts:view');

        $filters = $request->only([
            'per_page', 'search', 'sort_by', 'sort_dir', 'status',
            'customer_id', 'date_from', 'date_to', 'due_date_from',
            'due_date_to', 'overdue'
        ]);

        $debts = $this->service->filterAndPaginate($filters);

        return Inertia::render('CustomerDebts/Index', [
            'debts' => $debts->through(fn($debt) => [
                'id' => $debt->id,
                'customer_id' => $debt->customer->id,
                'customer_name' => $debt->customer->full_name,
                'customer' => [
                    'id' => $debt->customer->id,
                    'full_name' => $debt->customer->full_name,
                    'email' => $debt->customer->email,
                    'phone' => $debt->customer->phone,
                ],
                'invoice_id' => $debt->invoice_id,
                'original_amount' => (float) $debt->original_amount,
                'remaining_amount' => (float) $debt->remaining_amount,
                'paid_amount' => (float) $debt->paid_amount,
                'debt_date' => $debt->debt_date,
                'due_date' => $debt->due_date,
                'status' => $debt->status,
                'days_overdue' => $this->calculateDaysOverdue($debt),
                'user' => $debt->user?->name,
                'created_at' => $debt->created_at,
            ]),
            'filters' => $filters,
            'stats' => $this->service->getStats(),
            'customers' => $this->customerService->all()->map(fn($customer) => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
            ]),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('customer_debts:show');

        $debt = $this->service->find($id);

        if (!$debt) {
            return redirect()->route('customer-debts.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Deuda no encontrada.'
                ]);
        }

        return Inertia::render('CustomerDebts/Show', [
            'debt' => [
                'id' => $debt->id,
                'customer' => [
                    'id' => $debt->customer->id,
                    'full_name' => $debt->customer->full_name,
                    'email' => $debt->customer->email,
                    'phone' => $debt->customer->phone,
                    'address' => $debt->customer->address,
                ],
                'invoice' => [
                    'id' => $debt->invoice->id,
                    'date' => $debt->invoice->date->format('Y-m-d'),
                    'total_amount' => $debt->invoice->total_amount,
                ],
                'original_amount' => $debt->original_amount,
                'remaining_amount' => $debt->remaining_amount,
                'paid_amount' => $debt->paid_amount,
                'debt_date' => $debt->debt_date,
                'due_date' => $debt->due_date,
                'status' => $debt->status,
                'days_overdue' => $debt->days_overdue,
                'notes' => $debt->notes,
                'user' => $debt->user?->name,
                'created_at' => $debt->created_at->format('Y-m-d H:i:s'),
            ],
            'payments' => $debt->payments->map(fn($payment) => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'description' => $payment->description,
                'user' => $payment->user?->name,
            ]),
            'customer_summary' => $this->service->getCustomerDebtSummary($debt->customer_id),
        ]);
    }

    /**
     * Registrar un pago de deuda.
     */
    public function addPayment(Request $request, string $id)
    {
        $this->authorize('customer_debts:add_payment');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,check,other',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->service->addPayment(
                $id,
                $request->amount,
                $request->payment_method,
                $request->description
            );

            return redirect()->back()
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Pago registrado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al registrar pago: ' . $th->getMessage()
                ]);
        }
    }

    /**
     * Obtener resumen de deudas de un cliente.
     */
    public function customerSummary(Request $request, string $customerId)
    {
        $this->authorize('customer_debts:view');

        try {
            $summary = $this->service->getCustomerDebtSummary($customerId);
            return response()->json($summary);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 404);
        }
    }

    /**
     * Obtener deudas vencidas.
     */
    public function overdue(Request $request)
    {
        $this->authorize('customer_debts:view');

        $overdueDebts = $this->service->getOverdueDebts();

        return Inertia::render('CustomerDebts/Overdue', [
            'debts' => $overdueDebts->map(fn($debt) => [
                'id' => $debt->id,
                'customer' => [
                    'id' => $debt->customer->id,
                    'full_name' => $debt->customer->full_name,
                    'email' => $debt->customer->email,
                    'phone' => $debt->customer->phone,
                ],
                'invoice_id' => $debt->invoice_id,
                'remaining_amount' => $debt->remaining_amount,
                'due_date' => $debt->due_date,
                'days_overdue' => $debt->days_overdue,
                'status' => $debt->status,
            ]),
        ]);
    }

    /**
     * Eliminar una deuda.
     */
    public function destroy(string $id)
    {
        $this->authorize('customer_debts:delete');

        try {
            $this->service->delete($id);

            return redirect()->route('customer-debts.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Deuda eliminada exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al eliminar deuda: ' . $th->getMessage()
                ]);
        }
    }

    /**
     * API endpoint para obtener deudas de un cliente.
     */
    public function apiByCustomer(Request $request, string $customerId)
    {
        $this->authorize('customer_debts:view');

        $debts = $this->service->getDebtsByCustomer($customerId);

        return response()->json($debts->map(fn($debt) => [
            'id' => $debt->id,
            'invoice_id' => $debt->invoice_id,
            'remaining_amount' => $debt->remaining_amount,
            'due_date' => $debt->due_date,
            'status' => $debt->status,
        ]));
    }

    private function calculateDaysOverdue($debt)
    {
        if (!$debt->due_date || $debt->status === 'paid') {
            return 0;
        }

        $today = now();
        $dueDate = is_string($debt->due_date) ? now()->parse($debt->due_date) : $debt->due_date;

        return $today->gt($dueDate) ? $today->diffInDays($dueDate) : 0;
    }
}

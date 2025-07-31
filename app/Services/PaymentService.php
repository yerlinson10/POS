<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentService
{
    /**
     * Filtrar y paginar pagos.
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $sortBy = $filters['sort_by'] ?? 'payment_date';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return Payment::with(['customer', 'supplier', 'customerDebt', 'user', 'posSession'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                if (is_array($type)) {
                    $query->whereIn('type', $type);
                } else {
                    $query->where('type', $type);
                }
            })
            ->when($filters['category'] ?? null, function ($query, $category) {
                if (is_array($category)) {
                    $query->whereIn('category', $category);
                } else {
                    $query->where('category', $category);
                }
            })
            ->when($filters['payment_method'] ?? null, function ($query, $paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            })
            ->when($filters['customer_id'] ?? null, function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->when($filters['supplier_id'] ?? null, function ($query, $supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->whereDate('payment_date', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->whereDate('payment_date', '<=', $dateTo);
            })
            ->when($filters['amount_from'] ?? null, function ($query, $amountFrom) {
                $query->where('amount', '>=', $amountFrom);
            })
            ->when($filters['amount_to'] ?? null, function ($query, $amountTo) {
                $query->where('amount', '<=', $amountTo);
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    /**
     * Buscar un pago por ID.
     */
    public function find(int $id): ?Payment
    {
        return Payment::with([
            'customer', 
            'supplier', 
            'customerDebt', 
            'user', 
            'posSession'
        ])->find($id);
    }

    /**
     * Crear un nuevo pago.
     */
    public function create(array $data): Payment
    {
        return Payment::create(array_merge($data, [
            'user_id' => auth()->id(),
        ]));
    }

    /**
     * Actualizar un pago existente.
     */
    public function update(int $id, array $data): Payment
    {
        $payment = Payment::findOrFail($id);
        $payment->update($data);
        return $payment->fresh();
    }

    /**
     * Eliminar un pago.
     */
    public function delete(int $id): void
    {
        $payment = Payment::findOrFail($id);
        
        // Si es un pago de deuda, revertir los cambios en la deuda
        if ($payment->customer_debt_id) {
            $debt = $payment->customerDebt;
            if ($debt) {
                $debt->paid_amount -= $payment->amount;
                $debt->remaining_amount += $payment->amount;
                
                // Actualizar estado
                if ($debt->paid_amount <= 0) {
                    $debt->status = 'pending';
                } elseif ($debt->remaining_amount > 0) {
                    $debt->status = 'partial';
                }
                
                $debt->save();

                // Actualizar factura
                $debt->invoice->decrement('paid_amount', $payment->amount);
                $debt->invoice->update([
                    'payment_status' => $debt->invoice->paid_amount <= 0 ? 'debt' : 'partial'
                ]);
            }
        }
        
        // Si es un pago a proveedor, revertir la deuda del proveedor
        if ($payment->supplier_id && $payment->category === 'supplier_payment') {
            $payment->supplier->increment('current_debt', $payment->amount);
        }
        
        $payment->delete();
    }

    /**
     * Registrar pago de deuda de cliente.
     */
    public function recordCustomerDebtPayment(int $customerDebtId, float $amount, string $paymentMethod = 'cash', string $description = null): Payment
    {
        $customerDebtService = new CustomerDebtService();
        return $customerDebtService->addPayment($customerDebtId, $amount, $paymentMethod, $description);
    }

    /**
     * Registrar pago a proveedor.
     */
    public function recordSupplierPayment(int $supplierId, float $amount, string $paymentMethod = 'cash', string $description = null): Payment
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        if ($amount > $supplier->current_debt) {
            throw new \Exception('El monto a pagar no puede ser mayor a la deuda actual');
        }
        
        $supplier->payDebt($amount, $paymentMethod, $description);
        
        return $supplier->payments()->latest()->first();
    }

    /**
     * Registrar otro tipo de ingreso.
     */
    public function recordOtherIncome(float $amount, string $description, string $paymentMethod = 'cash', array $metadata = []): Payment
    {
        return $this->create([
            'type' => 'income',
            'category' => 'other_income',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_date' => now()->toDateString(),
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Registrar otro tipo de egreso.
     */
    public function recordOtherExpense(float $amount, string $description, string $paymentMethod = 'cash', array $metadata = []): Payment
    {
        return $this->create([
            'type' => 'expense',
            'category' => 'other_expense',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_date' => now()->toDateString(),
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Obtener resumen financiero.
     */
    public function getFinancialSummary(array $filters = []): array
    {
        $query = Payment::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }

        return [
            'total_income' => $query->clone()->income()->sum('amount'),
            'total_expenses' => $query->clone()->expense()->sum('amount'),
            'net_flow' => $query->clone()->income()->sum('amount') - $query->clone()->expense()->sum('amount'),
            'debt_payments' => $query->clone()->byCategory('debt_payment')->sum('amount'),
            'supplier_payments' => $query->clone()->byCategory('supplier_payment')->sum('amount'),
            'other_income' => $query->clone()->byCategory('other_income')->sum('amount'),
            'other_expenses' => $query->clone()->byCategory('other_expense')->sum('amount'),
        ];
    }

    /**
     * Obtener estadísticas de pagos.
     */
    public function getStats(): array
    {
        return [
            'total_payments' => Payment::count(),
            'total_income' => Payment::income()->sum('amount'),
            'total_expenses' => Payment::expense()->sum('amount'),
            'today_income' => Payment::income()->today()->sum('amount'),
            'today_expenses' => Payment::expense()->today()->sum('amount'),
            'this_month_income' => Payment::income()->thisMonth()->sum('amount'),
            'this_month_expenses' => Payment::expense()->thisMonth()->sum('amount'),
            'debt_payments_count' => Payment::byCategory('debt_payment')->count(),
            'supplier_payments_count' => Payment::byCategory('supplier_payment')->count(),
        ];
    }

    /**
     * Obtener pagos de hoy.
     */
    public function getTodayPayments()
    {
        return Payment::with(['customer', 'supplier', 'user'])
            ->today()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener pagos del mes actual.
     */
    public function getThisMonthPayments()
    {
        return Payment::with(['customer', 'supplier', 'user'])
            ->thisMonth()
            ->orderBy('payment_date', 'desc')
            ->get();
    }
}

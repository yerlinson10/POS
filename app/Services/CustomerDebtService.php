<?php

namespace App\Services;

use App\Models\CustomerDebt;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerDebtService
{
    /**
     * Filtrar y paginar deudas de clientes.
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $sortBy = $filters['sort_by'] ?? 'debt_date';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return CustomerDebt::with(['customer', 'invoice', 'user'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if (is_array($status)) {
                    $query->whereIn('status', $status);
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($filters['customer_id'] ?? null, function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->whereDate('debt_date', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->whereDate('debt_date', '<=', $dateTo);
            })
            ->when($filters['due_date_from'] ?? null, function ($query, $dueDateFrom) {
                $query->whereDate('due_date', '>=', $dueDateFrom);
            })
            ->when($filters['due_date_to'] ?? null, function ($query, $dueDateTo) {
                $query->whereDate('due_date', '<=', $dueDateTo);
            })
            ->when($filters['overdue'] ?? null, function ($query, $overdue) {
                if ($overdue === 'yes') {
                    $query->where('due_date', '<', now())
                        ->where('status', '!=', 'paid');
                }
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    /**
     * Buscar una deuda por ID.
     */
    public function find(int $id): ?CustomerDebt
    {
        return CustomerDebt::with(['customer', 'invoice', 'user', 'payments'])->find($id);
    }

    /**
     * Crear una nueva deuda desde una factura.
     */
    public function createFromInvoice(int $invoiceId, float $debtAmount, $dueDate = null, string $notes = null): CustomerDebt
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        if ($debtAmount > $invoice->total_amount) {
            throw new \Exception('El monto de la deuda no puede ser mayor al total de la factura');
        }

        // Crear la deuda
        $debt = CustomerDebt::create([
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'original_amount' => $debtAmount,
            'remaining_amount' => $debtAmount,
            'paid_amount' => 0,
            'debt_date' => now()->toDateString(),
            'due_date' => $dueDate,
            'status' => 'pending',
            'notes' => $notes,
        ]);

        // Actualizar la factura
        $paidAmount = $invoice->total_amount - $debtAmount;
        $invoice->update([
            'paid_amount' => $paidAmount,
            'debt_amount' => $debtAmount,
            'payment_status' => $paidAmount > 0 ? 'partial' : 'debt',
            'due_date' => $dueDate,
        ]);

        return $debt;
    }

    /**
     * Registrar un pago de deuda.
     */
    public function addPayment(int $debtId, float $amount, string $paymentMethod = 'cash', string $description = null)
    {
        $debt = CustomerDebt::findOrFail($debtId);
        return $debt->addPayment($amount, $paymentMethod, $description);
    }

    /**
     * Obtener deudas vencidas.
     */
    public function getOverdueDebts()
    {
        return CustomerDebt::with(['customer', 'invoice'])
            ->overdue()
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Obtener deudas por cliente.
     */
    public function getDebtsByCustomer(int $customerId)
    {
        return CustomerDebt::with(['invoice'])
            ->where('customer_id', $customerId)
            ->where('status', '!=', 'paid')
            ->orderBy('debt_date', 'desc')
            ->get();
    }

    /**
     * Obtener resumen de deudas por cliente.
     */
    public function getCustomerDebtSummary(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);
        
        return [
            'customer' => $customer,
            'total_debt' => $customer->total_debt,
            'pending_debts' => $customer->debts()->pending()->count(),
            'partial_debts' => $customer->debts()->partial()->count(),
            'overdue_debts' => $customer->debts()->overdue()->count(),
            'oldest_debt' => $customer->debts()
                ->where('status', '!=', 'paid')
                ->oldest('debt_date')
                ->first(),
        ];
    }

    /**
     * Obtener estadísticas generales de deudas.
     */
    public function getStats(): array
    {
        return [
            'total_debts' => CustomerDebt::count(),
            'pending_debts' => CustomerDebt::pending()->count(),
            'partial_debts' => CustomerDebt::partial()->count(),
            'paid_debts' => CustomerDebt::paid()->count(),
            'overdue_debts' => CustomerDebt::overdue()->count(),
            'total_debt_amount' => CustomerDebt::where('status', '!=', 'paid')->sum('remaining_amount'),
            'total_paid_amount' => CustomerDebt::sum('paid_amount'),
            'customers_with_debt' => CustomerDebt::where('status', '!=', 'paid')
                ->distinct('customer_id')
                ->count('customer_id'),
        ];
    }

    /**
     * Actualizar estado de deudas vencidas.
     */
    public function updateOverdueStatus(): void
    {
        CustomerDebt::where('due_date', '<', now())
            ->whereIn('status', ['pending', 'partial'])
            ->update(['status' => 'overdue']);
    }

    /**
     * Eliminar una deuda (solo si no tiene pagos asociados).
     */
    public function delete(int $id): void
    {
        $debt = CustomerDebt::findOrFail($id);
        
        if ($debt->payments()->exists()) {
            throw new \Exception('No se puede eliminar la deuda porque tiene pagos registrados');
        }
        
        // Actualizar la factura
        $debt->invoice->update([
            'paid_amount' => $debt->invoice->total_amount,
            'debt_amount' => 0,
            'payment_status' => 'paid',
        ]);
        
        $debt->delete();
    }
}

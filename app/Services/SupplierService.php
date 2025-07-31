<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierService
{
    /**
     * Filtrar y paginar proveedores.
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $sortBy = $filters['sort_by'] ?? 'company_name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return Supplier::when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        })
        ->when($filters['status'] ?? null, function ($query, $status) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        })
        ->when($filters['has_debt'] ?? null, function ($query, $hasDebt) {
            if ($hasDebt === 'yes') {
                $query->where('current_debt', '>', 0);
            } elseif ($hasDebt === 'no') {
                $query->where('current_debt', '<=', 0);
            }
        })
        ->orderBy($sortBy, $sortDir)
        ->paginate($perPage);
    }

    /**
     * Obtener todos los proveedores.
     */
    public function all()
    {
        return Supplier::orderBy('company_name')->get();
    }

    /**
     * Obtener todos los proveedores activos.
     */
    public function getAllActive()
    {
        return Supplier::active()->orderBy('company_name')->get();
    }

    /**
     * Buscar un proveedor por ID.
     */
    public function find(int $id): ?Supplier
    {
        return Supplier::with(['products', 'payments'])->find($id);
    }

    /**
     * Crear un nuevo proveedor.
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Actualizar un proveedor existente.
     */
    public function update(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Eliminar un proveedor.
     */
    public function delete(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        
        // Verificar si tiene productos asociados
        if ($supplier->products()->exists()) {
            throw new \Exception('No se puede eliminar el proveedor porque tiene productos asociados');
        }
        
        $supplier->delete();
    }

    /**
     * Obtener proveedores con deudas.
     */
    public function getSuppliersWithDebt()
    {
        return Supplier::withDebt()
            ->orderBy('current_debt', 'desc')
            ->get();
    }

    /**
     * Agregar deuda a un proveedor.
     */
    public function addDebt(int $supplierId, float $amount, string $description = null): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $supplier->addDebt($amount, $description);
    }

    /**
     * Pagar deuda de un proveedor.
     */
    public function payDebt(int $supplierId, float $amount, string $paymentMethod = 'cash', string $description = null): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $supplier->payDebt($amount, $paymentMethod, $description);
    }

    /**
     * Obtener estadísticas de proveedores.
     */
    public function getStats(): array
    {
        return [
            'total' => Supplier::count(),
            'active' => Supplier::active()->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
            'with_debt' => Supplier::withDebt()->count(),
            'total_debt' => Supplier::sum('current_debt'),
        ];
    }
}

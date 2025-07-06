<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Customer;

class CustomerService
{
    public function filterAndPaginate($filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $customersQuery = Customer::when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('customers.first_name', 'like', "%{$search}%")
                        ->orWhere('customers.last_name', 'like', "%{$search}%")
                        ->orWhere('customers.email', 'like', "%{$search}%")
                        ->orWhere('customers.phone', 'like', "%{$search}%");
                });
            })
            ->withAdvancedFilters($filters, [
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'address',
                'created_at'
            ]);

        return $customersQuery
                ->paginate($perPage)
                ->appends($filters);
    }
    /**
     * Listar productos con paginación.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::all()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener todos los productos (sin paginar).
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return Customer::all()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar un producto por ID.
     *
     * @param  int  $id
     * @return Customer|null
     */
    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * Crear un nuevo producto.
     *
     * @param  array  $data
     * @return Customer
     *
     */
    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Actualizar un producto existente.
     *
     * @param  int    $id
     * @param  array  $data
     * @return Customer
     *
     */
    public function update(int $id, array $data): Customer
    {
        $product = Customer::findOrFail($id);
        $product->update($data);
        return $product;
    }

    /**
     * Eliminar un producto.
     *
     * @param  int  $id
     * @return void
     */
    public function delete(int $id): void
    {
        Customer::findOrFail($id)->delete();
    }
}

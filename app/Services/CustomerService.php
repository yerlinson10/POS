<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Customer;

class CustomerService
{
    /**
     * Filtrar y paginar clientes.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     * @throws \DomainException
     */
    public function filterAndPaginate($filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        try {
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
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage)
                    ->appends($filters);
        } catch (\Throwable $e) {
            throw new \DomainException('Error al filtrar clientes: ' . $e->getMessage());
        }
    }

    /**
     * Listar clientes con paginación.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Obtener todos los clientes (sin paginar).
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return Customer::orderBy('created_at', 'desc')->get();
    }

    /**
     * Buscar un cliente por ID.
     *
     * @param  int  $id
     * @return Customer
     * @throws \DomainException
     */
    public function find(int $id): Customer
    {
        $customer = Customer::find($id);
        if (!$customer) {
            throw new \DomainException('Cliente no encontrado.');
        }
        return $customer;
    }

    /**
     * Crear un nuevo cliente.
     *
     * @param  array  $data
     * @return Customer
     * @throws \DomainException
     */
    public function create(array $data): Customer
    {
        try {
            return Customer::create($data);
        } catch (\Throwable $e) {
            throw new \DomainException('Error al crear cliente: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar un cliente existente.
     *
     * @param  int    $id
     * @param  array  $data
     * @return Customer
     * @throws \DomainException
     */
    public function update(int $id, array $data): Customer
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->update($data);
            return $customer;
        } catch (\Throwable $e) {
            throw new \DomainException('Error al actualizar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un cliente.
     *
     * @param  int  $id
     * @return void
     * @throws \DomainException
     */
    public function delete(int $id): void
    {
        try {
            Customer::findOrFail($id)->delete();
        } catch (\Throwable $e) {
            throw new \DomainException('Error al eliminar cliente: ' . $e->getMessage());
        }
    }
}

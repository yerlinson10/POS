<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Customer;

class CustomerService
{
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

<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Listar productos con paginación.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'unitMeasure'])
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
        return Product::with(['category', 'unitMeasure'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Buscar un producto por ID.
     *
     * @param  int  $id
     * @return Product|null
     */
    public function find(int $id): ?Product
    {
        return Product::with(['category', 'unitMeasure'])->find($id);
    }

    /**
     * Crear un nuevo producto.
     *
     * @param  array  $data
     * @return Product
     *
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Actualizar un producto existente.
     *
     * @param  int    $id
     * @param  array  $data
     * @return Product
     *
     */
    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
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
        Product::findOrFail($id)->delete();
    }

}

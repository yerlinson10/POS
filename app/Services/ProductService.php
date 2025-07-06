<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductService
{

    private $fields = [
        'id',
        'sku',
        'name',
        'price',
        'stock',
        'created_at',
        'category.name',
        'unitMeasure.code'
    ];

    /**
     * List products with advanced filters.
     * @param mixed $filters
     *
     * @return [type]
     */
    public function filterAndPaginate($filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $productsQuery = Product::with(['category', 'unitMeasure'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%");
                })
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('unitMeasure', function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%");
                    });
            })
            ->withAdvancedFilters($filters, $this->fields);

        return $productsQuery
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

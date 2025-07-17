<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Category;

class CategoryService
{
    /**
     * Filtrar y paginar categorías.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $categoriesQuery = Category::when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('categories.name', 'like', "%{$search}%");
                });
            })
            ->withAdvancedFilters($filters, [
                'id',
                'name'
            ]);

        return $categoriesQuery
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
        return Category::orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    /**
     * Obtener todos los productos (sin paginar).
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return Category::orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Buscar un producto por ID.
     *
     * @param  int  $id
     * @return Category|null
     */
    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Crear un nuevo producto.
     *
     * @param  array  $data
     * @return Category
     *
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Actualizar un producto existente.
     *
     * @param  int    $id
     * @param  array  $data
     * @return Category
     *
     */
    public function update(int $id, array $data): Category
    {
        $product = Category::findOrFail($id);
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
        Category::findOrFail($id)->delete();
    }
}

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
     * @param array $filters Filtros de búsqueda y paginación.
     * @return LengthAwarePaginator Paginador de categorías.
     * @throws \DomainException Si ocurre un error inesperado.
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        try {
            $categoriesQuery = Category::when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('categories.name', 'like', "%{$search}%");
                });
            })
                // Preparado para relaciones futuras
                // ->with(['relacion'])
                ->withAdvancedFilters($filters, [
                    'id',
                    'name'
                ]);

            return $categoriesQuery
                ->paginate($perPage)
                ->appends($filters);
        } catch (\Throwable $e) {
            throw new \DomainException('Error al filtrar categorías: ' . $e->getMessage());
        }
    }

    /**
     * Listar categorías con paginación.
     *
     * @param int $perPage Número de elementos por página.
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener todas las categorías (sin paginar).
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return Category::orderBy('created_at', 'desc')
            // Preparado para relaciones futuras
            // ->with(['relacion'])
            ->get();
    }

    /**
     * Buscar una categoría por ID.
     *
     * @param  int  $id
     * @return Category|null
     * @throws \DomainException Si no se encuentra la categoría.
     */
    public function find(int $id): ?Category
    {
        $category = Category::find($id);
        if (!$category) {
            throw new \DomainException('Categoría no encontrada.');
        }
        return $category;
    }

    /**
     * Crear una nueva categoría.
     *
     * @param  array  $data
     * @return Category
     * @throws \DomainException Si ocurre un error al crear.
     */
    public function create(array $data): Category
    {
        try {
            return Category::create($data);
        } catch (\Throwable $e) {
            throw new \DomainException('Error al crear categoría: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar una categoría existente.
     *
     * @param  int    $id
     * @param  array  $data
     * @return Category
     * @throws \DomainException Si ocurre un error al actualizar.
     */
    public function update(int $id, array $data): Category
    {
        try {
            $category = Category::findOrFail($id);
            $category->update($data);
            return $category;
        } catch (\Throwable $e) {
            throw new \DomainException('Error al actualizar categoría: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar una categoría.
     *
     * @param  int  $id
     * @return void
     * @throws \DomainException Si ocurre un error al eliminar.
     */
    public function delete(int $id): void
    {
        try {
            Category::findOrFail($id)->delete();
        } catch (\Throwable $e) {
            throw new \DomainException('Error al eliminar categoría: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\UnitMeasure;

class UnitMeasureService
{
    /**
     * Listar unidades de medida con paginación.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return UnitMeasure::all()
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
        return UnitMeasure::all()
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Buscar un producto por ID.
     *
     * @param  int  $id
     * @return UnitMeasure|null
     */
    public function find(int $id): ?UnitMeasure
    {
        return UnitMeasure::find($id);
    }

    /**
     * Crear un nuevo producto.
     *
     * @param  array  $data
     * @return UnitMeasure
     *
     */
    public function create(array $data): UnitMeasure
    {
        return UnitMeasure::create($data);
    }

    /**
     * Actualizar un producto existente.
     *
     * @param  int    $id
     * @param  array  $data
     * @return UnitMeasure
     *
     */
    public function update(int $id, array $data): UnitMeasure
    {
        $product = UnitMeasure::findOrFail($id);
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
        UnitMeasure::findOrFail($id)->delete();
    }
}

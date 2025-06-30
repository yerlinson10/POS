<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasAdvancedFilters
{
    /**
     * Aplica filtros y ordenamiento avanzados a un query Eloquent.
     * Soporta ordenamiento por relaciones usando notación de punto (ej: category.name)
     *
     * @param Builder $query
     * @param array $filters
     * @param array $allowedSorts (opcional) Lista blanca de campos permitidos para ordenar
     * @return Builder
     */
    public function scopeWithAdvancedFilters(Builder $query, array $filters, array $allowedSorts = [])
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        // Si hay lista blanca, solo permite esos campos
        if ($allowedSorts && !in_array($sortBy, $allowedSorts) && !str_contains($sortBy, '.')) {
            $sortBy = 'created_at';
        }

        // Ordenamiento por relación (ej: category.name)
        if (str_contains($sortBy, '.')) {
            [$relation, $column] = explode('.', $sortBy, 2);
            $relationTable = $query->getModel()->$relation()->getRelated()->getTable();
            $parentTable = $query->getModel()->getTable();
            $relationKey = $query->getModel()->$relation()->getForeignKeyName();
            $ownerKey = $query->getModel()->$relation()->getOwnerKeyName();

            $query->leftJoin($relationTable, $parentTable.'.'.$relationKey, '=', $relationTable.'.'.$ownerKey)
                ->orderBy($relationTable.'.'.$column, $sortDir)
                ->select($parentTable.'.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query;
    }
}

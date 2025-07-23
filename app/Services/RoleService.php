<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoleService
{
    /**
     * Filtrar y paginar roles.
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $rolesQuery = Role::with(['permissions', 'users'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc');

        return $rolesQuery->paginate($perPage)->appends($filters);
    }

    /**
     * Obtener todos los roles.
     */
    public function all(): Collection
    {
        return Role::with(['permissions', 'users'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar un rol por ID.
     */
    public function find(int $id): ?Role
    {
        return Role::with(['permissions', 'users'])->find($id);
    }

    /**
     * Crear un nuevo rol.
     */
    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        // Asignar permisos si se proporcionan
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load(['permissions', 'users']);
    }

    /**
     * Actualizar un rol existente.
     */
    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);

        $role->update([
            'name' => $data['name'],
        ]);

        // Sincronizar permisos
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load(['permissions', 'users']);
    }

    /**
     * Eliminar un rol.
     */
    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);

        // Verificar que no sea un rol del sistema
        if (in_array($role->name, ['Super Admin', 'Admin'])) {
            throw new \Exception('No se puede eliminar un rol del sistema.');
        }

        // Desasignar de todos los usuarios
        $role->users()->detach();

        $role->delete();
    }

    /**
     * Obtener todos los permisos disponibles.
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * Obtener permisos agrupados por módulo.
     */
    public function getPermissionsGrouped(): array
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode(':', $permission->name);
            if (count($parts) === 2) {
                $module = $parts[0];
                $action = $parts[1];

                if (!isset($grouped[$module])) {
                    $grouped[$module] = [];
                }

                $grouped[$module][] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'action' => $action,
                    'display_name' => ucfirst($action),
                ];
            }
        }

        return $grouped;
    }

    /**
     * Obtener estadísticas de roles.
     */
    public function getStats(): array
    {
        return [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'users_with_roles' => \App\Models\User::has('roles')->count(),
            'users_without_roles' => \App\Models\User::doesntHave('roles')->count(),
        ];
    }
}

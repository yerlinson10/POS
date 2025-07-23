<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserService
{
    /**
     * Filtrar y paginar usuarios.
     */
    public function filterAndPaginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $usersQuery = User::with(['roles', 'permissions'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->withAdvancedFilters($filters, [
                'id',
                'name',
                'email',
                'created_at'
            ]);

        return $usersQuery
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * Obtener todos los usuarios.
     */
    public function all(): Collection
    {
        return User::with(['roles', 'permissions'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar un usuario por ID.
     */
    public function find(int $id): ?User
    {
        return User::with(['roles', 'permissions'])->find($id);
    }

    /**
     * Crear un nuevo usuario.
     */
    public function create(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        $user = User::create($userData);

        // Asignar roles si se proporcionan
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->assignRole($data['roles']);
        }

        // Asignar permisos directos si se proporcionan
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $user->givePermissionTo($data['permissions']);
        }

        return $user->load(['roles', 'permissions']);
    }

    /**
     * Actualizar un usuario existente.
     */
    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // Solo actualizar contraseña si se proporciona
        if (isset($data['password']) && !empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $user->update($userData);

        // Sincronizar roles
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        // Sincronizar permisos directos
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return $user->load(['roles', 'permissions']);
    }

    /**
     * Eliminar un usuario.
     */
    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        // Remover todos los roles y permisos antes de eliminar
        $user->roles()->detach();
        $user->permissions()->detach();

        $user->delete();
    }

    /**
     * Obtener todos los roles disponibles.
     */
    public function getAllRoles(): Collection
    {
        return Role::all();
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
                ];
            }
        }

        return $grouped;
    }
}

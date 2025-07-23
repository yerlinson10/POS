<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;

class RoleController extends Controller
{
    use AuthorizesRequests;

    protected RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('roles:view');

        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir']);
        $roles = $this->service->filterAndPaginate($filters);

        return Inertia::render('Roles/Index', [
            'roles' => $roles->through(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users->count(),
                'created_at' => $role->created_at->format('Y-m-d H:i:s'),
            ]),
            'filters' => $filters,
            'stats' => $this->service->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('roles:create');

        return Inertia::render('Roles/Form', [
            'role' => null,
            'permissions' => $this->service->getPermissionsGrouped(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            $this->service->create($request->validated());

            return redirect()->route('roles.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Rol creado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al crear rol: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('roles:show');

        $role = $this->service->find($id);

        if (!$role) {
            return redirect()->route('roles.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Rol no encontrado.'
                ]);
        }

        return Inertia::render('Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->map(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                ]),
                'users' => $role->users->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
                'users_count' => $role->users->count(),
                'created_at' => $role->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('roles:edit');

        $role = $this->service->find($id);

        if (!$role) {
            return redirect()->route('roles.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Rol no encontrado.'
                ]);
        }

        return Inertia::render('Roles/Form', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
            'permissions' => $this->service->getPermissionsGrouped(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        try {
            $this->service->update($id, $request->validated());

            return redirect()->route('roles.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Rol actualizado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al actualizar rol: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('roles:delete');

        try {
            $this->service->delete($id);

            return redirect()->route('roles.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Rol eliminado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al eliminar rol: ' . $th->getMessage()
                ]);
        }
    }
}

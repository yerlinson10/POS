<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('users:view');

        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir', 'role']);
        $users = $this->service->filterAndPaginate($filters);

        return Inertia::render('Users/Index', [
            'users' => $users->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions_count' => $user->permissions->count(),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ]),
            'filters' => $filters,
            'roles' => $this->service->getAllRoles()->pluck('name', 'name'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('users:create');

        return Inertia::render('Users/Form', [
            'user' => null,
            'roles' => $this->service->getAllRoles()->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'action' => explode(':', $permission->name)[1] ?? $permission->name,
                ]),
            ]),
            'permissions' => $this->service->getPermissionsGrouped(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $this->service->create($request->validated());

            return redirect()->route('users.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Usuario creado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al crear usuario: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('users:show');

        $user = $this->service->find($id);

        if (!$user) {
            return redirect()->route('users.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Usuario no encontrado.'
                ]);
        }

        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'roles' => $user->roles->map(fn($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ]),
                'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('users:edit');

        $user = $this->service->find($id);

        if (!$user) {
            return redirect()->route('users.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Usuario no encontrado.'
                ]);
        }

        return Inertia::render('Users/Form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getDirectPermissions()->pluck('name'),
            ],
            'roles' => $this->service->getAllRoles()->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'action' => explode(':', $permission->name)[1] ?? $permission->name,
                ]),
            ]),
            'permissions' => $this->service->getPermissionsGrouped(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $this->service->update($id, $request->validated());

            return redirect()->route('users.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Usuario actualizado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al actualizar usuario: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('users:delete');

        try {
            // Verificar que no sea el usuario actual
            if (auth()->id() == $id) {
                return redirect()->back()
                    ->with('message', [
                        'type' => 'error',
                        'text' => 'No puedes eliminar tu propia cuenta.'
                    ]);
            }

            $this->service->delete($id);

            return redirect()->route('users.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Usuario eliminado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al eliminar usuario: ' . $th->getMessage()
                ]);
        }
    }
}

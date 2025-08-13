<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    protected CustomerService $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }
    /**
     * Muestra la lista de clientes con filtros y paginación.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);
        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir']);
        $customers = $this->service->filterAndPaginate($filters);
        return Inertia::render('Customers/Index', [
            'customers' => CustomerResource::collection($customers),
            'filters' => $filters,
        ]);
    }

    /**
     * Muestra un cliente específico.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $customer = $this->service->find($id);
            $this->authorize('view', $customer);
            return response()->json(new CustomerResource($customer));
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize('create', Customer::class);
        return Inertia::render('Customers/Form', [
            'customer' => null,
        ]);
    }

    /**
     * Almacena un nuevo cliente en la base de datos.
     *
     * @param StoreCustomerRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(StoreCustomerRequest $request)
    {
        $this->authorize('create', Customer::class);
        try {
            $customer = $this->service->create($request->validated());
            if ($request->expectsJson()) {
                return response()->json(new CustomerResource($customer), 201);
            }
            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Customer created.'
                ]);
        } catch (\DomainException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => $e->getMessage()
                ])
                ->withInput();
        }
    }


    /**
     * Muestra el formulario para editar un cliente existente.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function edit(string $id)
    {
        try {
            $customer = $this->service->find($id);
            $this->authorize('update', $customer);
            return Inertia::render('Customers/Form', [
                'customer' => new CustomerResource($customer)
            ]);
        } catch (\DomainException $e) {
            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => $e->getMessage()
                ]);
        }
    }

    /**
     * Actualiza un cliente existente en la base de datos.
     *
     * @param UpdateCustomerRequest $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        try {
            $customer = $this->service->find($id);
            $this->authorize('update', $customer);
            $this->service->update($id, $request->validated());
            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Customer updated.'
                ]);
        } catch (\DomainException $e) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => $e->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Elimina un cliente de la base de datos.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        try {
            $customer = $this->service->find($id);
            $this->authorize('delete', $customer);
            $this->service->delete($id);
            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Customer deleted.'
                ]);
        } catch (\DomainException $e) {
            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => $e->getMessage()
                ]);
        }
    }
}

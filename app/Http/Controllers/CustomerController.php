<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;

class CustomerController extends Controller
{
    protected CustomerService $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir']);
        $customers = $this->service->filterAndPaginate($filters);

        return Inertia::render('Customers/Index', [
            'customers' => $customers->through(fn($p) => [
                'id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'email' => $p->email,
                'phone' => $p->phone,
                'address' => $p->address
            ]),
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = $this->service->find($id);
        return $customer
            ? response()->json($customer)
            : response()->json(['message' => 'No encontrado'], 404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Customers/Form', [
            'customer' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $customer = $this->service->create($request->validated());

            // If request expects JSON (for modal), return the customer data
            if ($request->expectsJson()) {
                return response()->json([
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->first_name . ' ' . $customer->last_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'address' => $customer->address,
                    ]
                ], 201);
            }

            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Customer created.'
                ]);
        } catch (\Throwable $th) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error creating customer',
                    'error' => $th->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error creating customer: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $p = $this->service->find($id);
        if (!$p) {
            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Customer not found.'
                ]);
        }

        return Inertia::render('Customers/Form', [
            'customer' => [
                'id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'email' => $p->email,
                'phone' => $p->phone,
                'address' => $p->address
            ],
            'categories' => \App\Models\Category::all(),
            'unit_measures' => \App\Models\UnitMeasure::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        try {

            $this->service->update($id, $request->validated());

            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Customer updated.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error updating customer: ' . $th->getMessage()
                ])
                ->withInput();
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return redirect()->route('customers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Customer deleted.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error deleting customer: ' . $th->getMessage()
                ]);
        }

    }
}

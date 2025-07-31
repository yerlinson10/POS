<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Services\SupplierService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;

class SupplierController extends Controller
{
    use AuthorizesRequests;

    protected SupplierService $service;

    public function __construct(SupplierService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('suppliers:view');

        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir', 'status', 'has_debt']);
        $suppliers = $this->service->filterAndPaginate($filters);

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers->through(fn($supplier) => [
                'id' => $supplier->id,
                'company_name' => $supplier->company_name,
                'contact_name' => $supplier->contact_name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'current_debt' => $supplier->current_debt,
                'is_active' => $supplier->is_active,
                'products_count' => $supplier->products()->count(),
                'created_at' => $supplier->created_at->format('Y-m-d H:i:s'),
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
        $this->authorize('suppliers:create');

        return Inertia::render('Suppliers/Form', [
            'supplier' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $this->authorize('suppliers:create');

        try {
            $this->service->create($request->validated());

            return redirect()->route('suppliers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Proveedor creado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al crear proveedor: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('suppliers:show');

        $supplier = $this->service->find($id);

        if (!$supplier) {
            return redirect()->route('suppliers.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Proveedor no encontrado.'
                ]);
        }

        return Inertia::render('Suppliers/Show', [
            'supplier' => [
                'id' => $supplier->id,
                'company_name' => $supplier->company_name,
                'contact_name' => $supplier->contact_name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'city' => $supplier->city,
                'country' => $supplier->country,
                'tax_id' => $supplier->tax_id,
                'notes' => $supplier->notes,
                'current_debt' => $supplier->current_debt,
                'is_active' => $supplier->is_active,
                'created_at' => $supplier->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $supplier->updated_at->format('Y-m-d H:i:s'),
            ],
            'products' => $supplier->products->map(fn($product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
            ]),
            'recent_payments' => $supplier->payments()
                ->with('user')
                ->latest()
                ->take(10)
                ->get()
                ->map(fn($payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'description' => $payment->description,
                    'user' => $payment->user?->name,
                ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('suppliers:edit');

        $supplier = $this->service->find($id);

        if (!$supplier) {
            return redirect()->route('suppliers.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Proveedor no encontrado.'
                ]);
        }

        return Inertia::render('Suppliers/Form', [
            'supplier' => [
                'id' => $supplier->id,
                'company_name' => $supplier->company_name,
                'contact_name' => $supplier->contact_name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'city' => $supplier->city,
                'country' => $supplier->country,
                'tax_id' => $supplier->tax_id,
                'notes' => $supplier->notes,
                'current_debt' => $supplier->current_debt,
                'is_active' => $supplier->is_active,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, string $id)
    {
        $this->authorize('suppliers:edit');

        try {
            $this->service->update($id, $request->validated());

            return redirect()->route('suppliers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Proveedor actualizado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al actualizar proveedor: ' . $th->getMessage()
                ])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('suppliers:delete');

        try {
            $this->service->delete($id);

            return redirect()->route('suppliers.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Proveedor eliminado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al eliminar proveedor: ' . $th->getMessage()
                ]);
        }
    }

    /**
     * API endpoint para obtener proveedores activos.
     */
    public function apiIndex(Request $request)
    {
        $this->authorize('suppliers:view');

        $suppliers = $this->service->getAllActive();

        return response()->json($suppliers->map(fn($supplier) => [
            'id' => $supplier->id,
            'company_name' => $supplier->company_name,
            'contact_name' => $supplier->contact_name,
            'current_debt' => $supplier->current_debt,
        ]));
    }

    /**
     * Pagar deuda a proveedor.
     */
    public function payDebt(Request $request, string $id)
    {
        $this->authorize('suppliers:pay_debt');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,check,other',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->service->payDebt(
                $id,
                $request->amount,
                $request->payment_method,
                $request->description
            );

            return redirect()->back()
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Pago registrado exitosamente.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error al registrar pago: ' . $th->getMessage()
                ]);
        }
    }
}

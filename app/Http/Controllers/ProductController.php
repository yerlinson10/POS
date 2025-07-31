<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;

class ProductController extends Controller
{
    use AuthorizesRequests;

    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('products:view');

        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir']);
        $products = $this->service->filterAndPaginate($filters);

        return Inertia::render('Products/Index', [
            'products' => $products->through(fn($p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'category' => $p->category->name,
                'unit_measure' => $p->unitMeasure->code,
                'price' => $p->price,
                'stock' => $p->stock,
            ]),
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('products:show');

        $product = $this->service->find($id);
        return $product
            ? response()->json($product)
            : response()->json(['message' => 'No encontrado'], 404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('products:create');

        return Inertia::render('Products/Form', [
            'categories' => \App\Models\Category::all(),
            'unit_measures' => \App\Models\UnitMeasure::all(),
            'suppliers' => \App\Models\Supplier::active()->get(),
            'product' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('products:create');

        try {
            $this->service->create($request->validated());

            return redirect()->route('products.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Product created.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error creating product: ' . $th->getMessage()
                ])
                ->withInput();
        }

    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('products:edit');

        $p = $this->service->find($id);
        if (!$p) {
            return redirect()->route('products.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Product not found.'
                ]);
        }

        return Inertia::render('Products/Form', [
            'product' => [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'description' => $p->description,
                'category_id' => $p->category_id,
                'unit_measure_id' => $p->unit_measure_id,
                'supplier_id' => $p->supplier_id,
                'price' => $p->price,
                'stock' => $p->stock,
            ],
            'categories' => \App\Models\Category::all(),
            'unit_measures' => \App\Models\UnitMeasure::all(),
            'suppliers' => \App\Models\Supplier::active()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $this->authorize('products:edit');

        try {
            $this->service->update($id, $request->validated());

            return redirect()->route('products.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Product updated.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error updating product: ' . $th->getMessage()
                ])
                ->withInput();
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('products:delete');

        try {
            $this->service->delete($id);
            return redirect()->route('products.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Product deleted.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error deleting product: ' . $th->getMessage()
                ]);
        }

    }
}

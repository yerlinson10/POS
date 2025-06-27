<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;

class ProductController extends Controller
{
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
        $perPage = $request->query('per_page', 10);
        $products = $this->service->paginate((int)$perPage);

        return Inertia::render('Products/Index', [
            'products' => $products->through(fn($p) => [
                'id'               => $p->id,
                'sku'              => $p->sku,
                'name'             => $p->name,
                'category'         => $p->category->name,
                'unit_measure'     => $p->unitMeasure->code,
                'price'            => $p->price,
                'stock'            => $p->stock,
            ]),
            'filters' => $request->only(['per_page', 'search']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
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
        return Inertia::render('Products/Form', [
            'categories'    => \App\Models\Category::all(),
            'unit_measures' => \App\Models\UnitMeasure::all(),
            'product'       => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->service->create($request->validated());
        return redirect()->route('products.index')
            ->with('success', 'Producto creado correctamente.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $p = $this->service->find($id);

        return Inertia::render('Products/Form', [
            'product'       => [
                'id'               => $p->id,
                'sku'              => $p->sku,
                'name'             => $p->name,
                'description'      => $p->description,
                'category_id'      => $p->category_id,
                'unit_measure_id'  => $p->unit_measure_id,
                'price'            => $p->price,
                'stock'            => $p->stock,
            ],
            'categories'    => \App\Models\Category::all(),
            'unit_measures' => \App\Models\UnitMeasure::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $this->service->update($id, $request->validated());
        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->delete($id);
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado.');
    }
}

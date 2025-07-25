<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use App\Models\UnitMeasure;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir']);
        $categories = $this->service->filterAndPaginate($filters);

        return Inertia::render('Categories/Index', [
            'categories' => $categories->through(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
            ]),
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = $this->service->find($id);
        if (!$category) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $this->authorize('view', $category);

        return response()->json($category);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Categories/Form', [
            'category' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);

        try {
            $this->service->create($request->validated());

            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Category created.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error creating category: ' . $th->getMessage()
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
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Category not found.'
                ]);
        }

        $this->authorize('update', $p);

        return Inertia::render('Categories/Form', [
            'category' => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        $category = $this->service->find($id);
        if (!$category) {
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Category not found.'
                ]);
        }

        $this->authorize('update', $category);

        try {
            $this->service->update($id, $request->validated());

            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Category updated.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error updating category: ' . $th->getMessage()
                ])
                ->withInput();
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = $this->service->find($id);
        if (!$category) {
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Category not found.'
                ]);
        }

        $this->authorize('delete', $category);

        try {
            $this->service->delete($id);
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Category deleted.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error deleting category: ' . $th->getMessage()
                ]);
        }

    }
}

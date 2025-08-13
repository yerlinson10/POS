<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use App\Models\UnitMeasure;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }
    /**
     * Muestra la lista de categorías con filtros y paginación.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);
        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir']);
        $categories = $this->service->filterAndPaginate($filters);
        return Inertia::render('Categories/Index', [
            'categories' => CategoryResource::collection($categories),
            'filters' => $filters,
        ]);
    }

    /**
     * Muestra una categoría específica.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $category = $this->service->find($id);
            $this->authorize('view', $category);
            return response()->json(new CategoryResource($category));
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Muestra el formulario para crear una nueva categoría.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize('create', Category::class);
        return Inertia::render('Categories/Form');
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     *
     * @param StoreCategoryRequest $request
     * @return \Illuminate\Http\RedirectResponse
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
     * Muestra el formulario para editar una categoría existente.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function edit(string $id)
    {
        try {
            $category = $this->service->find($id);
            $this->authorize('update', $category);
            return Inertia::render('Categories/Form', [
                'category' => new CategoryResource($category)
            ]);
        } catch (\DomainException $e) {
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => $e->getMessage()
                ]);
        }
    }

    /**
     * Actualiza una categoría existente en la base de datos.
     *
     * @param UpdateCategoryRequest $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $category = $this->service->find($id);
            $this->authorize('update', $category);
            $this->service->update($id, $request->validated());
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Category updated.'
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
     * Elimina una categoría de la base de datos.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        try {
            $category = $this->service->find($id);
            $this->authorize('delete', $category);
            $this->service->delete($id);
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Category deleted.'
                ]);
        } catch (\DomainException $e) {
            return redirect()->route('categories.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => $e->getMessage()
                ]);
        }
    }
}

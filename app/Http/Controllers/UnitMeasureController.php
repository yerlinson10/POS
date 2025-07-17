<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\UnitMeasure;
use Illuminate\Http\Request;
use App\Services\UnitMeasureService;
use App\Http\Requests\UnitMeasure\StoreUnitMeasureRequest;
use App\Http\Requests\UnitMeasure\UpdateUnitMeasureRequest;

class UnitMeasureController extends Controller
{

    protected UnitMeasureService $service;

    public function __construct(UnitMeasureService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['per_page', 'search', 'sort_by', 'sort_dir']);
        $units = $this->service->filterAndPaginate($filters);

        return Inertia::render('Units/Index', [
            'units' => $units->through(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
            ]),
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unit = $this->service->find($id);
        return $unit
            ? response()->json($unit)
            : response()->json(['message' => 'No encontrado'], 404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Units/Form', [
            'unit' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitMeasureRequest $request)
    {
        try {

            $this->service->create($request->validated());

            return redirect()->route('unit-measures.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Unit of measure created.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error creating unit of measure: ' . $th->getMessage()
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
            return redirect()->route('unit-measures.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Unit of measure not found.'
                ]);
        }

        return Inertia::render('Units/Form', [
            'unit' => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitMeasureRequest $request, string $id)
    {
        try {
            $this->service->update($id, $request->validated());

            return redirect()->route('unit-measures.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Unit of measure updated.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error updating unit of measure: ' . $th->getMessage()
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
            return redirect()->route('unit-measures.index')
                ->with('message', [
                    'type' => 'success',
                    'text' => 'Unit of measure deleted.'
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('message', [
                    'type' => 'error',
                    'text' => 'Error deleting unit of measure: ' . $th->getMessage()
                ]);
        }

    }
}

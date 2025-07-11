<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\DashboardWidgetService;

class DashboardWidgetController extends Controller
{
    public function __construct(
        private DashboardWidgetService $widgetService
    ) {}

    /**
     * Mostrar el dashboard dinámico
     */
    public function index(): Response
    {
        $widgets = $this->widgetService->getUserWidgets();
        $filterOptions = $this->widgetService->getFilterOptions();

        return Inertia::render('DynamicDashboard', [
            'widgets' => $widgets,
            'filterOptions' => $filterOptions
        ]);
    }

    /**
     * Obtener widgets del usuario
     */
    public function getWidgets(): JsonResponse
    {
        $widgets = $this->widgetService->getUserWidgets();
        return response()->json($widgets);
    }

    /**
     * Crear un nuevo widget
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'widget_type' => 'required|string',
            'title' => 'required|string|max:255',
            'x' => 'integer|min:0',
            'y' => 'integer|min:0',
            'width' => 'integer|min:1|max:12',
            'height' => 'integer|min:1|max:12',
            'config' => 'nullable|array',
            'filters' => 'nullable|array',
            'advanced_filters' => 'nullable|array'
        ]);

        Log::info('🆕 CREATE Widget Request Data:', [
            'all_data' => $request->all(),
            'advanced_filters' => $request->get('advanced_filters')
        ]);

        $widget = $this->widgetService->createWidget($request->all());

        // Obtener el widget con datos
        $widgets = $this->widgetService->getUserWidgets();
        $widgetWithData = collect($widgets)->first(function ($w) use ($widget) {
            return $w['id'] === $widget->id;
        });

        return response()->json($widgetWithData, 201);
    }

    /**
     * Actualizar un widget
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'x' => 'sometimes|integer|min:0',
            'y' => 'sometimes|integer|min:0',
            'width' => 'sometimes|integer|min:1|max:12',
            'height' => 'sometimes|integer|min:1|max:12',
            'config' => 'sometimes|nullable|array',
            'filters' => 'sometimes|nullable|array',
            'advanced_filters' => 'sometimes|nullable|array'
        ]);

        Log::info('✏️ UPDATE Widget Request Data:', [
            'widget_id' => $id,
            'all_data' => $request->all(),
            'advanced_filters' => $request->get('advanced_filters')
        ]);

        $widget = $this->widgetService->updateWidget($id, $request->all());

        // Obtener el widget actualizado con datos
        $widgets = $this->widgetService->getUserWidgets();
        $widgetWithData = collect($widgets)->first(function ($w) use ($widget) {
            return $w['id'] === $widget->id;
        });

        return response()->json($widgetWithData);
    }

    /**
     * Actualizar posiciones de múltiples widgets
     */
    public function updatePositions(Request $request): JsonResponse
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|integer',
            'widgets.*.x' => 'required|integer|min:0',
            'widgets.*.y' => 'required|integer|min:0',
            'widgets.*.w' => 'required|integer|min:1|max:12',
            'widgets.*.h' => 'required|integer|min:1|max:12'
        ]);

        $this->widgetService->updateWidgetPositions($request->widgets);

        return response()->json(['message' => 'Posiciones actualizadas correctamente']);
    }

    /**
     * Eliminar un widget
     */
    public function destroy(int $id): JsonResponse
    {
        $this->widgetService->deleteWidget($id);
        return response()->json(['message' => 'Widget eliminado correctamente']);
    }

    /**
     * Obtener datos actualizados de un widget
     */
    public function refreshWidget(int $id): JsonResponse
    {
        $widgets = $this->widgetService->getUserWidgets();
        $widget = collect($widgets)->first(function ($w) use ($id) {
            return $w['id'] === $id;
        });

        if (!$widget) {
            return response()->json(['error' => 'Widget no encontrado'], 404);
        }

        return response()->json($widget);
    }

    /**
     * Obtener opciones de filtros
     */
    public function getFilterOptions(Request $request): JsonResponse
    {
        $widgetType = $request->query('widget_type');
        $options = $this->widgetService->getFilterOptions($widgetType);
        return response()->json($options);
    }
}

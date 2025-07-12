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
     * Show the dynamic dashboard
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
     * Get user widgets
     */
    public function getWidgets(): JsonResponse
    {
        $widgets = $this->widgetService->getUserWidgets();
        return response()->json($widgets);
    }

    /**
     * Create a new widget
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

        // Get the widget with data
        $widgets = $this->widgetService->getUserWidgets();
        $widgetWithData = collect($widgets)->first(function ($w) use ($widget) {
            return $w['id'] === $widget->id;
        });

        return response()->json($widgetWithData, 201);
    }

    /**
     * Update a widget
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

        // Get the updated widget with data
        $widgets = $this->widgetService->getUserWidgets();
        $widgetWithData = collect($widgets)->first(function ($w) use ($widget) {
            return $w['id'] === $widget->id;
        });

        return response()->json($widgetWithData);
    }

    /**
     * Update positions of multiple widgets
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

        return response()->json(['message' => 'Positions updated successfully']);
    }

    /**
     * Delete a widget
     */
    public function destroy(int $id): JsonResponse
    {
        $this->widgetService->deleteWidget($id);
        return response()->json(['message' => 'Widget deleted successfully']);
    }

    /**
     * Get updated data for a widget
     */
    public function refreshWidget(int $id): JsonResponse
    {
        $widgets = $this->widgetService->getUserWidgets();
        $widget = collect($widgets)->first(function ($w) use ($id) {
            return $w['id'] === $id;
        });

        if (!$widget) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        return response()->json($widget);
    }

    /**
     * Get filter options
     */
    public function getFilterOptions(Request $request): JsonResponse
    {
        $widgetType = $request->query('widget_type');
        $options = $this->widgetService->getFilterOptions($widgetType);
        return response()->json($options);
    }
}


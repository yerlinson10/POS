<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\InvoiceItem;
use App\Models\DashboardWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardWidgetService
{
    /**
     * Obtener todos los widgets del usuario actual
     */
    public function getUserWidgets($userId = null): array
    {
        $userId = $userId ?? Auth::id();

        return DashboardWidget::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('y')
            ->orderBy('x')
            ->get()
            ->map(function ($widget) {
                return [
                    'id' => $widget->id,
                    'type' => $widget->widget_type,
                    'title' => $widget->title,
                    'x' => $widget->x,
                    'y' => $widget->y,
                    'w' => $widget->width,
                    'h' => $widget->height,
                    'config' => $widget->config,
                    'filters' => $widget->filters,
                    'advanced_filters' => $widget->advanced_filters,
                    'data' => $this->getWidgetData($widget)
                ];
            })
            ->toArray();
    }

    /**
     * Crear un nuevo widget
     */
    public function createWidget(array $data): DashboardWidget
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        // Obtener la definición del widget para el tamaño por defecto
        $widgetDefinitions = [
            'sales_chart' => ['w' => 6, 'h' => 4],
            'sales_stats' => ['w' => 4, 'h' => 2],
            'top_products' => ['w' => 6, 'h' => 4],
            'low_stock' => ['w' => 6, 'h' => 3],
            'recent_sales' => ['w' => 6, 'h' => 4],
            'payment_methods' => ['w' => 4, 'h' => 3],
            'monthly_revenue' => ['w' => 6, 'h' => 4],
            'customer_stats' => ['w' => 4, 'h' => 3]
        ];

        $widgetType = $data['widget_type'];
        $defaultSize = $widgetDefinitions[$widgetType] ?? ['w' => 4, 'h' => 3];

        // Calcular la próxima posición disponible
        $position = $this->calculateNextPosition($data['user_id'], $defaultSize['w'], $defaultSize['h']);

        // Asignar posición y tamaño automáticamente
        $data['x'] = $position['x'];
        $data['y'] = $position['y'];
        $data['width'] = $defaultSize['w'];
        $data['height'] = $defaultSize['h'];

        return DashboardWidget::create($data);
    }

    /**
     * Calcular la próxima posición disponible en el grid
     */
    private function calculateNextPosition(int $userId, int $width, int $height): array
    {
        // Obtener todos los widgets existentes del usuario
        $existingWidgets = DashboardWidget::where('user_id', $userId)
            ->where('is_active', true)
            ->get(['x', 'y', 'width', 'height']);

        $gridColumns = 12; // GridStack usa 12 columnas
        $currentRow = 0;

        // Si no hay widgets, empezar en (0,0)
        if ($existingWidgets->isEmpty()) {
            return ['x' => 0, 'y' => 0];
        }

        // Crear un mapa de posiciones ocupadas
        $occupiedPositions = [];
        foreach ($existingWidgets as $widget) {
            for ($y = $widget->y; $y < $widget->y + $widget->height; $y++) {
                for ($x = $widget->x; $x < $widget->x + $widget->width; $x++) {
                    $occupiedPositions[$y][$x] = true;
                }
            }
        }

        // Buscar la primera posición libre
        while (true) {
            for ($x = 0; $x <= $gridColumns - $width; $x++) {
                if ($this->isPositionFree($occupiedPositions, $x, $currentRow, $width, $height)) {
                    return ['x' => $x, 'y' => $currentRow];
                }
            }
            $currentRow++;
        }
    }

    /**
     * Verificar si una posición está libre
     */
    private function isPositionFree(array $occupiedPositions, int $x, int $y, int $width, int $height): bool
    {
        for ($checkY = $y; $checkY < $y + $height; $checkY++) {
            for ($checkX = $x; $checkX < $x + $width; $checkX++) {
                if (isset($occupiedPositions[$checkY][$checkX])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Actualizar un widget existente
     */
    public function updateWidget(int $id, array $data): DashboardWidget
    {
        $widget = DashboardWidget::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $widget->update($data);
        return $widget;
    }

    /**
     * Actualizar posiciones de múltiples widgets
     */
    public function updateWidgetPositions(array $widgets): void
    {
        foreach ($widgets as $widgetData) {
            $this->updateWidget($widgetData['id'], [
                'x' => $widgetData['x'],
                'y' => $widgetData['y'],
                'width' => $widgetData['w'],
                'height' => $widgetData['h']
            ]);
        }
    }

    /**
     * Eliminar un widget
     */
    public function deleteWidget(int $id): void
    {
        DashboardWidget::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();
    }

    /**
     * Obtener datos para un widget específico
     */
    public function getWidgetData(DashboardWidget $widget): array
    {
        $filters = $widget->filters ?? [];
        $advancedFilters = $widget->advanced_filters ?? [];

        switch ($widget->widget_type) {
            case 'sales_chart':
                return $this->getSalesChartData($filters, $advancedFilters);
            case 'sales_stats':
                return $this->getSalesStats($filters, $advancedFilters);
            case 'top_products':
                return $this->getTopProducts($filters, $advancedFilters);
            case 'low_stock':
                return $this->getLowStockProducts($filters, $advancedFilters);
            case 'recent_sales':
                return $this->getRecentSales($filters, $advancedFilters);
            case 'payment_methods':
                return $this->getPaymentMethodsData($filters, $advancedFilters);
            case 'monthly_revenue':
                return $this->getMonthlyRevenueData($filters, $advancedFilters);
            case 'customer_stats':
                return $this->getCustomerStats($filters, $advancedFilters);
            default:
                return [];
        }
    }

    /**
     * Datos de gráfico de ventas
     */
    private function getSalesChartData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');
        $groupBy = $filters['group_by'] ?? 'day';

        $query = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Aplicar filtros avanzados
        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        if (isset($filters['payment_method']) && $filters['payment_method'] !== 'all' && !empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== 'all' && !empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        switch ($groupBy) {
            case 'hour':
                return $query->select(
                    DB::raw('HOUR(date) as period'),
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw('HOUR(date)'))
                ->orderBy('period')
                ->get()
                ->map(function ($item) {
                    return [
                        'period' => $item->period . ':00',
                        'total' => (float) $item->total,
                        'count' => (int) $item->count
                    ];
                })
                ->toArray();

            case 'month':
                return $query->select(
                    DB::raw('DATE_FORMAT(date, "%Y-%m") as period'),
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw('DATE_FORMAT(date, "%Y-%m")'))
                ->orderBy('period')
                ->get()
                ->map(function ($item) {
                    return [
                        'period' => $item->period,
                        'total' => (float) $item->total,
                        'count' => (int) $item->count
                    ];
                })
                ->toArray();

            default: // day
                return $query->select(
                    DB::raw('DATE(date) as period'),
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('period')
                ->get()
                ->map(function ($item) {
                    return [
                        'period' => $item->period,
                        'total' => (float) $item->total,
                        'count' => (int) $item->count
                    ];
                })
                ->toArray();
        }
    }

    /**
     * Estadísticas de ventas
     */
    private function getSalesStats(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Aplicar filtros avanzados
        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        if (isset($filters['payment_method']) && $filters['payment_method'] !== 'all' && !empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $invoices = $query->get();

        return [
            'total_sales' => (float) $invoices->sum('total_amount'),
            'total_count' => $invoices->count(),
            'average_sale' => $invoices->count() > 0 ? (float) $invoices->avg('total_amount') : 0,
            'cash_sales' => (float) $invoices->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => (float) $invoices->where('payment_method', 'card')->sum('total_amount'),
        ];
    }

    /**
     * Top productos vendidos
     */
    private function getTopProducts(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');
        $limit = $filters['limit'] ?? 10;

        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.date', [$dateFrom, $dateTo]);

        // Aplicar filtros avanzados (nota: se necesita adaptar para query builder)
        if (!empty($advancedFilters)) {
            foreach ($advancedFilters as $group) {
                $groupOperator = $group['operator'] ?? 'AND';

                $query->where(function ($subQuery) use ($group, $groupOperator) {
                    foreach ($group['filters'] as $index => $filter) {
                        $field = $filter['field'];
                        $operator = $filter['operator'];
                        $value = $filter['value'];

                        // Los campos ya vienen con el prefijo correcto (products.name, categories.name, etc.)
                        // Solo mapear si no tienen prefijo
                        if (!str_contains($field, '.')) {
                            $field = 'products.' . $field;
                        }

                        $method = $index === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where');

                        switch ($operator) {
                            case 'is':
                            case 'equals':
                                $subQuery->$method($field, '=', $value);
                                break;
                            case 'is_not':
                            case 'not_equals':
                                $subQuery->$method($field, '!=', $value);
                                break;
                            case 'contains':
                                $subQuery->$method($field, 'LIKE', "%{$value}%");
                                break;
                            case 'not_contains':
                                $subQuery->$method($field, 'NOT LIKE', "%{$value}%");
                                break;
                            case 'starts_with':
                                $subQuery->$method($field, 'LIKE', "{$value}%");
                                break;
                            case 'ends_with':
                                $subQuery->$method($field, 'LIKE', "%{$value}");
                                break;
                            case 'greater_than':
                                $subQuery->$method($field, '>', $value);
                                break;
                            case 'less_than':
                                $subQuery->$method($field, '<', $value);
                                break;
                            case 'greater_equal':
                                $subQuery->$method($field, '>=', $value);
                                break;
                            case 'less_equal':
                                $subQuery->$method($field, '<=', $value);
                                break;
                            case 'between':
                                if (is_array($value) && count($value) === 2) {
                                    $subQuery->$method($field, '>=', $value[0])
                                            ->where($field, '<=', $value[1]);
                                }
                                break;
                            case 'is_empty':
                                $subQuery->$method(function ($q) use ($field) {
                                    $q->whereNull($field)->orWhere($field, '=', '');
                                });
                                break;
                            case 'is_not_empty':
                                $subQuery->$method($field, '!=', '')
                                        ->whereNotNull($field);
                                break;
                            case 'is_null':
                                $subQuery->whereNull($field);
                                break;
                            case 'is_not_null':
                                $subQuery->whereNotNull($field);
                                break;
                            case 'is_true':
                                $subQuery->$method($field, true);
                                break;
                            case 'is_false':
                                $subQuery->$method($field, false);
                                break;
                        }
                    }
                });
            }
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('products.category_id', $filters['category_id']);
        }

        return $query->select(
            'products.id',
            'products.name',
            'products.sku',
            'categories.name as category_name',
            DB::raw('SUM(invoice_items.quantity) as total_sold'),
            DB::raw('SUM(invoice_items.line_total) as total_revenue')
        )
        ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
        ->orderBy('total_sold', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'category' => $item->category_name,
                'total_sold' => (int) $item->total_sold,
                'total_revenue' => (float) $item->total_revenue,
            ];
        })
        ->toArray();
    }

    /**
     * Productos con bajo stock
     */
    private function getLowStockProducts(array $filters, array $advancedFilters = []): array
    {
        $threshold = $filters['stock_threshold'] ?? 10;
        $limit = $filters['limit'] ?? 10;

        // Verificar si hay filtros avanzados que requieren joins
        $needsJoins = false;
        if (!empty($advancedFilters)) {
            foreach ($advancedFilters as $group) {
                foreach ($group['filters'] as $filter) {
                    if (str_contains($filter['field'], 'categories.')) {
                        $needsJoins = true;
                        break 2;
                    }
                }
            }
        }

        if ($needsJoins) {
            // Usar query builder con joins cuando se necesitan filtros de relaciones
            $query = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('unit_measures', 'products.unit_measure_id', '=', 'unit_measures.id')
                ->where('products.stock', '<=', $threshold);

            // Aplicar filtros avanzados (versión para query builder)
            if (!empty($advancedFilters)) {
                foreach ($advancedFilters as $group) {
                    $groupOperator = $group['operator'] ?? 'AND';

                    $query->where(function ($subQuery) use ($group, $groupOperator) {
                        foreach ($group['filters'] as $index => $filter) {
                            $field = $filter['field'];
                            $operator = $filter['operator'];
                            $value = $filter['value'];

                            // Asegurar que los campos tengan el prefijo correcto
                            if (!str_contains($field, '.')) {
                                $field = 'products.' . $field;
                            }

                            $method = $index === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where');

                            switch ($operator) {
                                case 'is':
                                case 'equals':
                                    $subQuery->$method($field, '=', $value);
                                    break;
                                case 'is_not':
                                case 'not_equals':
                                    $subQuery->$method($field, '!=', $value);
                                    break;
                                case 'contains':
                                    $subQuery->$method($field, 'LIKE', "%{$value}%");
                                    break;
                                case 'not_contains':
                                    $subQuery->$method($field, 'NOT LIKE', "%{$value}%");
                                    break;
                                case 'starts_with':
                                    $subQuery->$method($field, 'LIKE', "{$value}%");
                                    break;
                                case 'ends_with':
                                    $subQuery->$method($field, 'LIKE', "%{$value}");
                                    break;
                                case 'greater_than':
                                    $subQuery->$method($field, '>', $value);
                                    break;
                                case 'less_than':
                                    $subQuery->$method($field, '<', $value);
                                    break;
                                case 'greater_equal':
                                    $subQuery->$method($field, '>=', $value);
                                    break;
                                case 'less_equal':
                                    $subQuery->$method($field, '<=', $value);
                                    break;
                                case 'between':
                                    if (is_array($value) && count($value) === 2) {
                                        $subQuery->$method($field, '>=', $value[0])
                                                ->where($field, '<=', $value[1]);
                                    }
                                    break;
                                case 'is_empty':
                                    $subQuery->$method(function ($q) use ($field) {
                                        $q->whereNull($field)->orWhere($field, '=', '');
                                    });
                                    break;
                                case 'is_not_empty':
                                    $subQuery->$method($field, '!=', '')
                                            ->whereNotNull($field);
                                    break;
                                case 'is_null':
                                    $subQuery->whereNull($field);
                                    break;
                                case 'is_not_null':
                                    $subQuery->whereNotNull($field);
                                    break;
                                case 'is_true':
                                    $subQuery->$method($field, true);
                                    break;
                                case 'is_false':
                                    $subQuery->$method($field, false);
                                    break;
                            }
                        }
                    });
                }
            }

            if (isset($filters['category_id']) && $filters['category_id'] !== 'all') {
                $query->where('products.category_id', $filters['category_id']);
            }

            return $query->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.stock',
                'categories.name as category_name',
                'unit_measures.code as unit_code'
            )
            ->orderBy('products.stock', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'stock' => $item->stock,
                    'category' => $item->category_name ?? 'Sin categoría',
                    'unit' => $item->unit_code ?? 'UN',
                ];
            })
            ->toArray();
        } else {
            // Usar Eloquent cuando no hay filtros de relaciones
            $query = Product::with(['category', 'unitMeasure'])
                ->where('stock', '<=', $threshold);

            // Debug: Ver cuántos productos hay antes de filtros avanzados
            $countBeforeAdvanced = Product::where('stock', '<=', $threshold)->count();
            $countWithA = Product::where('stock', '<=', $threshold)->where('name', 'LIKE', '%a%')->count();

            Log::info('LowStockProducts Before Advanced Filters:', [
                'threshold' => $threshold,
                'total_low_stock' => $countBeforeAdvanced,
                'with_letter_a' => $countWithA
            ]);

            // Aplicar filtros avanzados
            $query = $this->applyAdvancedFilters($query, $advancedFilters);

            if (isset($filters['category_id']) && $filters['category_id'] !== 'all') {
                $query->where('category_id', $filters['category_id']);
            }

            // Debug: Log SQL y resultados
            Log::info('LowStockProducts Query SQL:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $results = $query->orderBy('stock', 'asc')
                ->limit($limit)
                ->get();

            Log::info('LowStockProducts Results:', [
                'count' => $results->count(),
                'results' => $results->take(3)->toArray() // Solo los primeros 3 para no llenar logs
            ]);

            return $results->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'stock' => $product->stock,
                        'category' => $product->category->name ?? 'Sin categoría',
                        'unit' => $product->unitMeasure->code ?? 'UN',
                    ];
                })
                ->toArray();
        }
    }

    /**
     * Ventas recientes
     */
    private function getRecentSales(array $filters, array $advancedFilters = []): array
    {
        $limit = $filters['limit'] ?? 10;

        $query = Invoice::with(['customer', 'user'])
            ->where('status', 'paid');

        // Aplicar filtros avanzados
        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        if (isset($filters['payment_method']) && $filters['payment_method'] !== 'all' && !empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== 'all' && !empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'customer_name' => $invoice->customer->full_name ?? 'Cliente genérico',
                    'total_amount' => (float) $invoice->total_amount,
                    'payment_method' => $invoice->payment_method,
                    'date' => $invoice->date->format('Y-m-d H:i'),
                    'user_name' => $invoice->user->name ?? 'N/A',
                ];
            })
            ->toArray();
    }

    /**
     * Datos de métodos de pago
     */
    private function getPaymentMethodsData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Aplicar filtros avanzados
        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        return $query->select(
            'payment_method',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total')
        )
        ->groupBy('payment_method')
        ->get()
        ->map(function ($item) {
            return [
                'method' => $item->payment_method,
                'count' => (int) $item->count,
                'total' => (float) $item->total,
                'label' => $item->payment_method === 'cash' ? 'Efectivo' : 'Tarjeta'
            ];
        })
        ->toArray();
    }

    /**
     * Datos de ingresos mensuales
     */
    private function getMonthlyRevenueData(array $filters, array $advancedFilters = []): array
    {
        $months = $filters['months'] ?? 12;
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();

        $query = Invoice::where('status', 'paid')
            ->where('date', '>=', $startDate);

        // Aplicar filtros avanzados
        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        if (isset($filters['payment_method']) && $filters['payment_method'] !== 'all' && !empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query->select(
            DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as sales_count')
        )
        ->groupBy(DB::raw('DATE_FORMAT(date, "%Y-%m")'))
        ->orderBy('month')
        ->get()
        ->map(function ($item) {
            return [
                'month' => $item->month,
                'revenue' => (float) $item->revenue,
                'sales_count' => (int) $item->sales_count
            ];
        })
        ->toArray();
    }

    /**
     * Estadísticas de clientes
     */
    private function getCustomerStats(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        // Aplicar filtros avanzados a la consulta de nuevos clientes
        $newCustomersQuery = Customer::whereBetween('created_at', [$dateFrom, $dateTo]);
        $newCustomersQuery = $this->applyAdvancedFilters($newCustomersQuery, $advancedFilters);
        $newCustomers = $newCustomersQuery->count();

        // Clientes con compras
        $customersWithPurchasesQuery = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);
        $customersWithPurchasesQuery = $this->applyAdvancedFilters($customersWithPurchasesQuery, $advancedFilters);
        $customersWithPurchases = $customersWithPurchasesQuery->distinct('customer_id')->count();

        // Top clientes (aplicar filtros avanzados es más complejo aquí, se omite por ahora)
        $topCustomers = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.date', [$dateFrom, $dateTo])
            ->select(
                'customers.id',
                DB::raw('CONCAT(customers.first_name, " ", customers.last_name) as name'),
                DB::raw('SUM(invoices.total_amount) as total_spent'),
                DB::raw('COUNT(invoices.id) as purchase_count')
            )
            ->groupBy('customers.id', 'customers.first_name', 'customers.last_name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        return [
            'new_customers' => $newCustomers,
            'customers_with_purchases' => $customersWithPurchases,
            'total_customers' => Customer::count(),
            'top_customers' => $topCustomers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'total_spent' => (float) $customer->total_spent,
                    'purchase_count' => (int) $customer->purchase_count
                ];
            })->toArray()
        ];
    }

    /**
     * Obtener opciones de filtros disponibles
     */
    public function getFilterOptions(string $widgetType = null): array
    {
        $baseOptions = [
            'payment_methods' => [
                ['value' => 'cash', 'label' => 'Efectivo'],
                ['value' => 'card', 'label' => 'Tarjeta']
            ],
            'users' => DB::table('users')->select('id', 'name as label', 'id as value')->get()->toArray(),
            'categories' => DB::table('categories')->select('id', 'name as label', 'id as value')->get()->toArray(),
            'group_by_options' => [
                ['value' => 'hour', 'label' => 'Por hora'],
                ['value' => 'day', 'label' => 'Por día'],
                ['value' => 'month', 'label' => 'Por mes']
            ],
        ];

        // Campos específicos según el tipo de widget
        $baseOptions['available_fields'] = $this->getAvailableFieldsForWidget($widgetType);

        return $baseOptions;
    }

    /**
     * Obtener campos disponibles según el tipo de widget
     */
    private function getAvailableFieldsForWidget(string $widgetType = null): array
    {
        if (!$widgetType) {
            return $this->getAllAvailableFields();
        }

        switch ($widgetType) {
            case 'sales_chart':
            case 'sales_stats':
            case 'monthly_revenue':
                return [
                    // Campos de Invoice
                    ['field' => 'total_amount', 'label' => 'Total de Factura', 'type' => 'number'],
                    ['field' => 'payment_method', 'label' => 'Método de Pago', 'type' => 'string'],
                    ['field' => 'date', 'label' => 'Fecha de Factura', 'type' => 'date'],
                    ['field' => 'status', 'label' => 'Estado de Factura', 'type' => 'string'],
                    ['field' => 'notes', 'label' => 'Notas de Factura', 'type' => 'string'],

                    // Campos de User
                    ['field' => 'user_id', 'label' => 'Usuario', 'type' => 'number'],

                    // Campos de Customer
                    ['field' => 'customer_id', 'label' => 'Cliente', 'type' => 'number'],
                ];

            case 'top_products':
                return [
                    // Campos de Product
                    ['field' => 'products.name', 'label' => 'Nombre de Producto', 'type' => 'string'],
                    ['field' => 'products.sku', 'label' => 'SKU de Producto', 'type' => 'string'],
                    ['field' => 'products.price', 'label' => 'Precio de Producto', 'type' => 'number'],
                    ['field' => 'products.stock', 'label' => 'Stock de Producto', 'type' => 'number'],
                    ['field' => 'products.is_active', 'label' => 'Producto Activo', 'type' => 'boolean'],

                    // Campos de Category
                    ['field' => 'categories.name', 'label' => 'Categoría', 'type' => 'string'],

                    // Campos de Invoice (para fechas de venta)
                    ['field' => 'invoices.date', 'label' => 'Fecha de Venta', 'type' => 'date'],
                    ['field' => 'invoices.total_amount', 'label' => 'Total de Venta', 'type' => 'number'],
                ];

            case 'low_stock':
                return [
                    // Campos de Product
                    ['field' => 'products.name', 'label' => 'Nombre de Producto', 'type' => 'string'],
                    ['field' => 'products.sku', 'label' => 'SKU de Producto', 'type' => 'string'],
                    ['field' => 'products.price', 'label' => 'Precio de Producto', 'type' => 'number'],
                    ['field' => 'products.stock', 'label' => 'Stock de Producto', 'type' => 'number'],
                    ['field' => 'products.is_active', 'label' => 'Producto Activo', 'type' => 'boolean'],

                    // Campos de Category (relación)
                    ['field' => 'categories.name', 'label' => 'Nombre de Categoría', 'type' => 'string'],
                    ['field' => 'products.category_id', 'label' => 'ID de Categoría', 'type' => 'number'],
                ];

            case 'recent_sales':
                return [
                    // Campos de Invoice
                    ['field' => 'total_amount', 'label' => 'Total de Factura', 'type' => 'number'],
                    ['field' => 'payment_method', 'label' => 'Método de Pago', 'type' => 'string'],
                    ['field' => 'date', 'label' => 'Fecha de Factura', 'type' => 'date'],
                    ['field' => 'status', 'label' => 'Estado de Factura', 'type' => 'string'],

                    // Campos de Customer
                    ['field' => 'customer_id', 'label' => 'Cliente', 'type' => 'number'],

                    // Campos de User
                    ['field' => 'user_id', 'label' => 'Usuario', 'type' => 'number'],
                ];

            case 'payment_methods':
                return [
                    // Campos de Invoice
                    ['field' => 'total_amount', 'label' => 'Total de Factura', 'type' => 'number'],
                    ['field' => 'payment_method', 'label' => 'Método de Pago', 'type' => 'string'],
                    ['field' => 'date', 'label' => 'Fecha de Factura', 'type' => 'date'],
                    ['field' => 'status', 'label' => 'Estado de Factura', 'type' => 'string'],
                ];

            case 'customer_stats':
                return [
                    // Campos de Customer
                    ['field' => 'first_name', 'label' => 'Nombre de Cliente', 'type' => 'string'],
                    ['field' => 'last_name', 'label' => 'Apellido de Cliente', 'type' => 'string'],
                    ['field' => 'email', 'label' => 'Email de Cliente', 'type' => 'string'],
                    ['field' => 'phone', 'label' => 'Teléfono de Cliente', 'type' => 'string'],
                    ['field' => 'address', 'label' => 'Dirección de Cliente', 'type' => 'string'],
                    ['field' => 'created_at', 'label' => 'Fecha de Registro', 'type' => 'date'],
                ];

            default:
                return $this->getAllAvailableFields();
        }
    }

    /**
     * Obtener todos los campos disponibles
     */
    private function getAllAvailableFields(): array
    {
        return [
            // Campos de Invoice
            ['field' => 'invoices.total_amount', 'label' => 'Total de Factura', 'type' => 'number'],
            ['field' => 'invoices.payment_method', 'label' => 'Método de Pago', 'type' => 'string'],
            ['field' => 'invoices.date', 'label' => 'Fecha de Factura', 'type' => 'date'],
            ['field' => 'invoices.status', 'label' => 'Estado de Factura', 'type' => 'string'],
            ['field' => 'invoices.notes', 'label' => 'Notas de Factura', 'type' => 'string'],

            // Campos de Customer
            ['field' => 'customers.first_name', 'label' => 'Nombre de Cliente', 'type' => 'string'],
            ['field' => 'customers.last_name', 'label' => 'Apellido de Cliente', 'type' => 'string'],
            ['field' => 'customers.email', 'label' => 'Email de Cliente', 'type' => 'string'],
            ['field' => 'customers.phone', 'label' => 'Teléfono de Cliente', 'type' => 'string'],
            ['field' => 'customers.address', 'label' => 'Dirección de Cliente', 'type' => 'string'],

            // Campos de Product
            ['field' => 'products.name', 'label' => 'Nombre de Producto', 'type' => 'string'],
            ['field' => 'products.sku', 'label' => 'SKU de Producto', 'type' => 'string'],
            ['field' => 'products.price', 'label' => 'Precio de Producto', 'type' => 'number'],
            ['field' => 'products.stock', 'label' => 'Stock de Producto', 'type' => 'number'],
            ['field' => 'products.is_active', 'label' => 'Producto Activo', 'type' => 'boolean'],

            // Campos de User
            ['field' => 'users.name', 'label' => 'Nombre de Usuario', 'type' => 'string'],
            ['field' => 'users.email', 'label' => 'Email de Usuario', 'type' => 'string'],

            // Campos de Category
            ['field' => 'categories.name', 'label' => 'Nombre de Categoría', 'type' => 'string'],
        ];
    }

    /**
     * Aplicar filtros avanzados a una query
     */
    private function applyAdvancedFilters($query, array $advancedFilters)
    {
        if (empty($advancedFilters)) {
            return $query;
        }

        // Debug temporal
        Log::info('ApplyAdvancedFilters called with:', [
            'advanced_filters' => $advancedFilters,
            'is_empty' => empty($advancedFilters),
            'count' => count($advancedFilters),
            'query_type' => get_class($query)
        ]);

        foreach ($advancedFilters as $group) {
            $groupOperator = $group['operator'] ?? 'AND';

            $query->where(function ($subQuery) use ($group, $groupOperator, $query) {
                foreach ($group['filters'] as $index => $filter) {
                    $field = $filter['field'];
                    $operator = $filter['operator'];
                    $value = $filter['value'];
                    $type = $filter['type'];

                    // Detectar si es Query Builder o Eloquent
                    $isQueryBuilder = str_contains(get_class($query), 'Builder') && !str_contains(get_class($query), 'Eloquent');

                    // Para Eloquent, limpiar prefijos de tabla si el campo pertenece a la tabla principal
                    if (!$isQueryBuilder && str_contains($field, 'products.')) {
                        $field = str_replace('products.', '', $field);
                    }

                    $method = $index === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where');

                    Log::info('Applying filter:', [
                        'field' => $field,
                        'original_field' => $filter['field'],
                        'operator' => $operator,
                        'value' => $value,
                        'query_type' => get_class($query),
                        'is_query_builder' => $isQueryBuilder
                    ]);

                    switch ($operator) {
                        case 'is':
                        case 'equals':
                            $subQuery->$method($field, '=', $value);
                            break;
                        case 'is_not':
                        case 'not_equals':
                            $subQuery->$method($field, '!=', $value);
                            break;
                        case 'contains':
                            $subQuery->$method($field, 'LIKE', "%{$value}%");
                            break;
                        case 'not_contains':
                            $subQuery->$method($field, 'NOT LIKE', "%{$value}%");
                            break;
                        case 'starts_with':
                            $subQuery->$method($field, 'LIKE', "{$value}%");
                            break;
                        case 'ends_with':
                            $subQuery->$method($field, 'LIKE', "%{$value}");
                            break;
                        case 'greater_than':
                        case 'after':
                            $subQuery->$method($field, '>', $value);
                            break;
                        case 'less_than':
                        case 'before':
                            $subQuery->$method($field, '<', $value);
                            break;
                        case 'greater_equal':
                            $subQuery->$method($field, '>=', $value);
                            break;
                        case 'less_equal':
                            $subQuery->$method($field, '<=', $value);
                            break;
                        case 'between':
                            if (is_array($value) && count($value) === 2) {
                                $subQuery->$method($field, '>=', $value[0])
                                        ->where($field, '<=', $value[1]);
                            }
                            break;
                        case 'is_empty':
                            $subQuery->$method(function ($q) use ($field) {
                                $q->whereNull($field)->orWhere($field, '=', '');
                            });
                            break;
                        case 'is_not_empty':
                            $subQuery->$method($field, '!=', '')
                                    ->whereNotNull($field);
                            break;
                        case 'is_null':
                            $subQuery->$method($field, null);
                            break;
                        case 'is_not_null':
                            $subQuery->whereNotNull($field);
                            break;
                        case 'is_true':
                            $subQuery->$method($field, true);
                            break;
                        case 'is_false':
                            $subQuery->$method($field, false);
                            break;
                    }
                }
            });
        }

        return $query;
    }
}

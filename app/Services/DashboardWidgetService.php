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
     * Get all widgets for the current user
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
     * Create a new widget
     */
    public function createWidget(array $data): DashboardWidget
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        // Get widget definition for default size
        $widgetDefinitions = [
            'sales_chart' => ['w' => 6, 'h' => 4],
            'sales_stats' => ['w' => 4, 'h' => 2],
            'top_products' => ['w' => 6, 'h' => 4],
            'low_stock' => ['w' => 6, 'h' => 3],
            'recent_sales' => ['w' => 6, 'h' => 4],
            'payment_methods' => ['w' => 4, 'h' => 3],
            'monthly_revenue' => ['w' => 6, 'h' => 4],
            'customer_stats' => ['w' => 4, 'h' => 3],
            // New widgets
            'inventory_value' => ['w' => 4, 'h' => 3],
            'daily_targets' => ['w' => 6, 'h' => 3],
            'hourly_sales' => ['w' => 6, 'h' => 4],
            'category_performance' => ['w' => 5, 'h' => 4],
            'profit_margin' => ['w' => 4, 'h' => 3],
            'expense_tracking' => ['w' => 5, 'h' => 3],
            'top_customers' => ['w' => 6, 'h' => 4],
            'sales_forecast' => ['w' => 6, 'h' => 4]
        ];

        $widgetType = $data['widget_type'];
        $defaultSize = $widgetDefinitions[$widgetType] ?? ['w' => 4, 'h' => 3];

        // Calculate next available position
        $position = $this->calculateNextPosition($data['user_id'], $defaultSize['w'], $defaultSize['h']);

        // Assign position and size automatically
        $data['x'] = $position['x'];
        $data['y'] = $position['y'];
        $data['width'] = $defaultSize['w'];
        $data['height'] = $defaultSize['h'];

        return DashboardWidget::create($data);
    }

    /**
     * Calculate next available position in the grid
     */
    private function calculateNextPosition(int $userId, int $width, int $height): array
    {
        // Get all existing widgets for the user
        $existingWidgets = DashboardWidget::where('user_id', $userId)
            ->where('is_active', true)
            ->get(['x', 'y', 'width', 'height']);

        $gridColumns = 12; // GridStack uses 12 columns
        $currentRow = 0;

        // If no widgets, start at (0,0)
        if ($existingWidgets->isEmpty()) {
            return ['x' => 0, 'y' => 0];
        }

        // Create a map of occupied positions
        $occupiedPositions = [];
        foreach ($existingWidgets as $widget) {
            for ($y = $widget->y; $y < $widget->y + $widget->height; $y++) {
                for ($x = $widget->x; $x < $widget->x + $widget->width; $x++) {
                    $occupiedPositions[$y][$x] = true;
                }
            }
        }

        // Find the first free position
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
     * Check if a position is free
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
     * Update an existing widget
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
     * Update positions of multiple widgets
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
     * Delete a widget
     */
    public function deleteWidget(int $id): void
    {
        DashboardWidget::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();
    }

    /**
     * Get data for a specific widget
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
            // New widget cases
            case 'inventory_value':
                return $this->getInventoryValueData($filters, $advancedFilters);
            case 'daily_targets':
                return $this->getDailyTargetsData($filters, $advancedFilters);
            case 'hourly_sales':
                return $this->getHourlySalesData($filters, $advancedFilters);
            case 'category_performance':
                return $this->getCategoryPerformanceData($filters, $advancedFilters);
            case 'profit_margin':
                return $this->getProfitMarginData($filters, $advancedFilters);
            case 'expense_tracking':
                return $this->getExpenseTrackingData($filters, $advancedFilters);
            case 'top_customers':
                return $this->getTopCustomersData($filters, $advancedFilters);
            case 'sales_forecast':
                return $this->getSalesForecastData($filters, $advancedFilters);
            default:
                return [];
        }
    }

    /**
     * Sales chart data
     */
    private function getSalesChartData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');
        $groupBy = $filters['group_by'] ?? 'day';

        $query = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Apply advanced filters
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
                    DB::raw('EXTRACT(HOUR FROM date) as period'),
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw('EXTRACT(HOUR FROM date)'))
                ->orderBy('period')
                ->get()
                ->map(function ($item) {
                    return [
                        'period' => str_pad($item->period, 2, '0', STR_PAD_LEFT) . ':00',
                        'total' => (float) $item->total,
                        'count' => (int) $item->count
                    ];
                })
                ->toArray();

            case 'month':
                return $query->select(
                    DB::raw("TO_CHAR(date, 'YYYY-MM') as period"),
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw("TO_CHAR(date, 'YYYY-MM')"))
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
     * Sales statistics
     */
    private function getSalesStats(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Apply advanced filters
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
     * Top sold products
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

        // Apply advanced filters (note: needs adaptation for query builder)
        if (!empty($advancedFilters)) {
            $query->where(function ($mainQuery) use ($advancedFilters) {
                foreach ($advancedFilters as $groupIndex => $group) {
                    $groupOperator = $group['operator'] ?? 'AND';

                    // Determine the method for connecting this group with the previous ones
                    $groupMethod = $groupIndex === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where');

                    $mainQuery->$groupMethod(function ($subQuery) use ($group, $groupOperator, $groupIndex) {
                        foreach ($group['filters'] as $index => $filter) {
                            $field = $filter['field'];
                            $operator = $filter['operator'];
                            $value = $filter['value'];

                            // Fields already have correct prefix (products.name, categories.name, etc.)
                            // Only map if no prefix
                            if (!str_contains($field, '.')) {
                                $field = 'products.' . $field;
                            }

                            // Within the group, use the group's operator for connecting filters
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
            });
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
     * Products with low stock
     */
    private function getLowStockProducts(array $filters, array $advancedFilters = []): array
    {
        $threshold = $filters['stock_threshold'] ?? 10;
        $limit = $filters['limit'] ?? 10;

        // Check if advanced filters require joins
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
            // Use query builder with joins when relation filters are needed
            $query = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('unit_measures', 'products.unit_measure_id', '=', 'unit_measures.id')
                ->where('products.stock', '<=', $threshold);

            // Apply advanced filters (query builder version)
            if (!empty($advancedFilters)) {
                $query->where(function ($mainQuery) use ($advancedFilters) {
                    foreach ($advancedFilters as $groupIndex => $group) {
                        $groupOperator = $group['operator'] ?? 'AND';

                        // Determine the method for connecting this group with the previous ones
                        $groupMethod = $groupIndex === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where');

                        $mainQuery->$groupMethod(function ($subQuery) use ($group, $groupOperator, $groupIndex) {
                            foreach ($group['filters'] as $index => $filter) {
                                $field = $filter['field'];
                                $operator = $filter['operator'];
                                $value = $filter['value'];

                                // Ensure fields have correct prefix
                                if (!str_contains($field, '.')) {
                                    $field = 'products.' . $field;
                                }

                                // Within the group, use the group's operator for connecting filters
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
                });
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
                    'category' => $item->category_name ?? 'No category',
                    'unit' => $item->unit_code ?? 'UN',
                ];
            })
            ->toArray();
        } else {
            // Use Eloquent when there are no relation filters
            $query = Product::with(['category', 'unitMeasure'])
                ->where('stock', '<=', $threshold);

            // Debug: See how many products before advanced filters
            $countBeforeAdvanced = Product::where('stock', '<=', $threshold)->count();
            $countWithA = Product::where('stock', '<=', $threshold)->where('name', 'LIKE', '%a%')->count();

            Log::info('LowStockProducts Before Advanced Filters:', [
                'threshold' => $threshold,
                'total_low_stock' => $countBeforeAdvanced,
                'with_letter_a' => $countWithA
            ]);

            // Apply advanced filters
            $query = $this->applyAdvancedFilters($query, $advancedFilters);

            if (isset($filters['category_id']) && $filters['category_id'] !== 'all') {
                $query->where('category_id', $filters['category_id']);
            }

            // Debug: Log SQL and results
            Log::info('LowStockProducts Query SQL:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $results = $query->orderBy('stock', 'asc')
                ->limit($limit)
                ->get();

            Log::info('LowStockProducts Results:', [
                'count' => $results->count(),
                'results' => $results->take(3)->toArray() // Only first 3 to avoid filling logs
            ]);

            return $results->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'stock' => $product->stock,
                        'category' => $product->category->name ?? 'No category',
                        'unit' => $product->unitMeasure->code ?? 'UN',
                    ];
                })
                ->toArray();
        }
    }

    /**
     * Recent sales
     */
    private function getRecentSales(array $filters, array $advancedFilters = []): array
    {
        $limit = $filters['limit'] ?? 10;

        $query = Invoice::with(['customer', 'user'])
            ->where('status', 'paid');

        // Apply advanced filters
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
                    'customer_name' => $invoice->customer->full_name ?? 'Generic customer',
                    'total_amount' => (float) $invoice->total_amount,
                    'payment_method' => $invoice->payment_method,
                    'date' => $invoice->date->format('Y-m-d H:i'),
                    'user_name' => $invoice->user->name ?? 'N/A',
                ];
            })
            ->toArray();
    }

    /**
     * Payment methods data
     */
    private function getPaymentMethodsData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        // Apply advanced filters
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
                'label' => $item->payment_method === 'cash' ? 'Cash' : 'Card'
            ];
        })
        ->toArray();
    }

    /**
     * Monthly revenue data
     */
    private function getMonthlyRevenueData(array $filters, array $advancedFilters = []): array
    {
        $months = $filters['months'] ?? 12;
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();

        $query = Invoice::where('status', 'paid')
            ->where('date', '>=', $startDate);

        // Apply advanced filters
        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        if (isset($filters['payment_method']) && $filters['payment_method'] !== 'all' && !empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query->select(
            DB::raw("TO_CHAR(date, 'YYYY-MM') as month"),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as sales_count')
        )
        ->groupBy(DB::raw("TO_CHAR(date, 'YYYY-MM')"))
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
     * Customer statistics
     */
    private function getCustomerStats(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        // Apply advanced filters to new customers query
        $newCustomersQuery = Customer::whereBetween('created_at', [$dateFrom, $dateTo]);
        $newCustomersQuery = $this->applyAdvancedFilters($newCustomersQuery, $advancedFilters);
        $newCustomers = $newCustomersQuery->count();

        // Customers with purchases
        $customersWithPurchasesQuery = Invoice::where('status', 'paid')
            ->whereBetween('date', [$dateFrom, $dateTo]);
        $customersWithPurchasesQuery = $this->applyAdvancedFilters($customersWithPurchasesQuery, $advancedFilters);
        $customersWithPurchases = $customersWithPurchasesQuery->distinct('customer_id')->count();

        // Top customers (applying advanced filters is more complex here, omitted for now)
        $topCustomers = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.date', [$dateFrom, $dateTo])
            ->select(
                'customers.id',
                'customers.first_name',
                'customers.last_name',
                DB::raw("(customers.first_name || ' ' || customers.last_name) as name"),
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
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'name' => $customer->name, // Ya viene de la consulta SQL
                    'total_spent' => (float) $customer->total_spent,
                    'purchase_count' => (int) $customer->purchase_count
                ];
            })->toArray()
        ];
    }

    /**
     * Get available filter options
     */
    public function getFilterOptions(string $widgetType = null): array
    {
        $baseOptions = [
            'payment_methods' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'card', 'label' => 'Card']
            ],
            'users' => DB::table('users')->select('id', 'name as label', 'id as value')->get()->toArray(),
            'categories' => DB::table('categories')->select('id', 'name as label', 'id as value')->get()->toArray(),
            'unit_measures' => DB::table('unit_measures')->select('id', 'name as label', 'id as value')->get()->toArray(),
            'group_by_options' => [
                ['value' => 'hour', 'label' => 'By hour'],
                ['value' => 'day', 'label' => 'By day'],
                ['value' => 'month', 'label' => 'By month']
            ],
        ];

        // Specific fields by widget type
        $baseOptions['available_fields'] = $this->getAvailableFieldsForWidget($widgetType);

        return $baseOptions;
    }

    /**
     * Get available fields by widget type
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
                    // Invoice fields
                    ['field' => 'total_amount', 'label' => 'Invoice Total', 'type' => 'number'],
                    ['field' => 'payment_method', 'label' => 'Payment Method', 'type' => 'string'],
                    ['field' => 'date', 'label' => 'Invoice Date', 'type' => 'date'],
                    ['field' => 'status', 'label' => 'Invoice Status', 'type' => 'string'],
                    ['field' => 'notes', 'label' => 'Invoice Notes', 'type' => 'string'],

                    // User fields
                    ['field' => 'user_id', 'label' => 'User', 'type' => 'number'],

                    // Customer fields
                    ['field' => 'customer_id', 'label' => 'Customer', 'type' => 'number'],
                ];

            case 'top_products':
                return [
                    // Product fields
                    ['field' => 'products.name', 'label' => 'Product Name', 'type' => 'string'],
                    ['field' => 'products.sku', 'label' => 'Product SKU', 'type' => 'string'],
                    ['field' => 'products.price', 'label' => 'Product Price', 'type' => 'number'],
                    ['field' => 'products.stock', 'label' => 'Product Stock', 'type' => 'number'],
                    ['field' => 'products.is_active', 'label' => 'Product Active', 'type' => 'boolean'],

                    // Category fields
                    ['field' => 'categories.name', 'label' => 'Category', 'type' => 'string'],

                    // Invoice fields (for sale dates)
                    ['field' => 'invoices.date', 'label' => 'Sale Date', 'type' => 'date'],
                    ['field' => 'invoices.total_amount', 'label' => 'Sale Total', 'type' => 'number'],

                    ['field' => 'unit_measures.code', 'label' => 'Unit Code', 'type' => 'string'],
                ];

            case 'low_stock':
                return [
                    // Product fields
                    ['field' => 'products.name', 'label' => 'Product Name', 'type' => 'string'],
                    ['field' => 'products.sku', 'label' => 'Product SKU', 'type' => 'string'],
                    ['field' => 'products.price', 'label' => 'Product Price', 'type' => 'number'],
                    ['field' => 'products.stock', 'label' => 'Product Stock', 'type' => 'number'],
                    ['field' => 'products.is_active', 'label' => 'Product Active', 'type' => 'boolean'],

                    // Category fields (relation)
                    ['field' => 'categories.name', 'label' => 'Category Name', 'type' => 'string'],
                    ['field' => 'products.category_id', 'label' => 'Category ID', 'type' => 'number'],

                    ['field' => 'products.unit_measure_id', 'label' => 'Unit Measure ID', 'type' => 'number'],
                    ['field' => 'unit_measures.code', 'label' => 'Unit Code', 'type' => 'string'],
                ];

            case 'recent_sales':
                return [
                    // Invoice fields
                    ['field' => 'total_amount', 'label' => 'Invoice Total', 'type' => 'number'],
                    ['field' => 'payment_method', 'label' => 'Payment Method', 'type' => 'string'],
                    ['field' => 'date', 'label' => 'Invoice Date', 'type' => 'date'],
                    ['field' => 'status', 'label' => 'Invoice Status', 'type' => 'string'],

                    // Customer fields
                    ['field' => 'customer_id', 'label' => 'Customer', 'type' => 'number'],

                    // User fields
                    ['field' => 'user_id', 'label' => 'User', 'type' => 'number'],
                ];

            case 'payment_methods':
                return [
                    // Invoice fields
                    ['field' => 'total_amount', 'label' => 'Invoice Total', 'type' => 'number'],
                    ['field' => 'payment_method', 'label' => 'Payment Method', 'type' => 'string'],
                    ['field' => 'date', 'label' => 'Invoice Date', 'type' => 'date'],
                    ['field' => 'status', 'label' => 'Invoice Status', 'type' => 'string'],
                ];

            case 'customer_stats':
                return [
                    // Customer fields
                    ['field' => 'first_name', 'label' => 'Customer First Name', 'type' => 'string'],
                    ['field' => 'last_name', 'label' => 'Customer Last Name', 'type' => 'string'],
                    ['field' => 'email', 'label' => 'Customer Email', 'type' => 'string'],
                    ['field' => 'phone', 'label' => 'Customer Phone', 'type' => 'string'],
                    ['field' => 'address', 'label' => 'Customer Address', 'type' => 'string'],
                    ['field' => 'created_at', 'label' => 'Registration Date', 'type' => 'date'],
                ];

            default:
                return $this->getAllAvailableFields();
        }
    }

    /**
     * Get all available fields
     */
    private function getAllAvailableFields(): array
    {
        return [
            // Invoice fields
            ['field' => 'invoices.total_amount', 'label' => 'Invoice Total', 'type' => 'number'],
            ['field' => 'invoices.payment_method', 'label' => 'Payment Method', 'type' => 'string'],
            ['field' => 'invoices.date', 'label' => 'Invoice Date', 'type' => 'date'],
            ['field' => 'invoices.status', 'label' => 'Invoice Status', 'type' => 'string'],
            ['field' => 'invoices.notes', 'label' => 'Invoice Notes', 'type' => 'string'],

            // Customer fields
            ['field' => 'customers.first_name', 'label' => 'Customer First Name', 'type' => 'string'],
            ['field' => 'customers.last_name', 'label' => 'Customer Last Name', 'type' => 'string'],
            ['field' => 'customers.email', 'label' => 'Customer Email', 'type' => 'string'],
            ['field' => 'customers.phone', 'label' => 'Customer Phone', 'type' => 'string'],
            ['field' => 'customers.address', 'label' => 'Customer Address', 'type' => 'string'],

            // Product fields
            ['field' => 'products.name', 'label' => 'Product Name', 'type' => 'string'],
            ['field' => 'products.sku', 'label' => 'Product SKU', 'type' => 'string'],
            ['field' => 'products.price', 'label' => 'Product Price', 'type' => 'number'],
            ['field' => 'products.stock', 'label' => 'Product Stock', 'type' => 'number'],
            ['field' => 'products.is_active', 'label' => 'Product Active', 'type' => 'boolean'],
            ['field' => 'products.categories.name', 'label' => 'Category', 'type' => 'number'],
            ['field' => 'products.unit_measures.code', 'label' => 'Unit Code', 'type' => 'number'],


            // User fields
            ['field' => 'users.name', 'label' => 'User Name', 'type' => 'string'],
            ['field' => 'users.email', 'label' => 'User Email', 'type' => 'string'],

            // Category fields
            ['field' => 'categories.name', 'label' => 'Category Name', 'type' => 'string'],
        ];
    }

    /**
     * Apply advanced filters to a query
     */
    private function applyAdvancedFilters($query, array $advancedFilters)
    {
        if (empty($advancedFilters)) {
            return $query;
        }

        // Temporary debug
        Log::info('ApplyAdvancedFilters called with:', [
            'advanced_filters' => $advancedFilters,
            'is_empty' => empty($advancedFilters),
            'count' => count($advancedFilters),
            'query_type' => get_class($query)
        ]);

        // Apply all groups within a single where clause to handle inter-group logic properly
        $query->where(function ($mainQuery) use ($advancedFilters) {
            foreach ($advancedFilters as $groupIndex => $group) {
                $groupOperator = $group['operator'] ?? 'AND';

                // Determine the method for connecting this group with the previous ones
                // First group always uses 'where', subsequent groups use the operator from the CURRENT group
                // to determine how it connects with the PREVIOUS groups
                $groupMethod = $groupIndex === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where');

                $mainQuery->$groupMethod(function ($subQuery) use ($group, $groupOperator, $groupIndex) {
                    foreach ($group['filters'] as $index => $filter) {
                        $field = $filter['field'];
                        $operator = $filter['operator'];
                        $value = $filter['value'];
                        $type = $filter['type'];

                        // Detect if Query Builder or Eloquent
                        $isQueryBuilder = str_contains(get_class($subQuery), 'Builder') && !str_contains(get_class($subQuery), 'Eloquent');

                        // For Eloquent, remove table prefixes if field belongs to main table
                        if (!$isQueryBuilder && str_contains($field, 'products.')) {
                            $field = str_replace('products.', '', $field);
                        }

                        // Within the group, use the group's operator for connecting filters
                        $method = $index === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where');

                        Log::info('Applying filter:', [
                            'group_index' => $groupIndex,
                            'filter_index' => $index,
                            'field' => $field,
                            'original_field' => $filter['field'],
                            'operator' => $operator,
                            'value' => $value,
                            'group_operator' => $groupOperator,
                            'group_method' => $groupIndex === 0 ? 'where' : (strtolower($groupOperator) === 'or' ? 'orWhere' : 'where'),
                            'filter_method' => $method,
                            'query_type' => get_class($subQuery),
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
        });

        return $query;
    }

    /**
     * Inventory value data
     */
    private function getInventoryValueData(array $filters, array $advancedFilters = []): array
    {
        $query = Product::select('categories.name as category', DB::raw('SUM(products.stock * products.price) as value'))
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.stock', '>', 0)
            ->groupBy('categories.name');

        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        return $query->get()->toArray();
    }

    /**
     * Daily targets data
     */
    private function getDailyTargetsData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(7)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        // Mock data for daily targets - replace with actual target tracking logic
        $period = Carbon::parse($dateFrom);
        $data = [];

        while ($period->lte(Carbon::parse($dateTo))) {
            $actualSales = Invoice::where('status', 'paid')
                ->whereBetween('created_at', [
                    $period->format('Y-m-d') . ' 00:00:00',
                    $period->format('Y-m-d') . ' 23:59:59'
                ])
                ->sum('total_amount');

            $data[] = [
                'date' => $period->format('Y-m-d'),
                'target' => 1000, // Replace with actual target value
                'actual' => (float) $actualSales
            ];

            $period->addDay();
        }

        return $data;
    }

    /**
     * Hourly sales data
     */
    private function getHourlySalesData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = Invoice::select(
            DB::raw('EXTRACT(HOUR FROM created_at) as hour'),
            DB::raw('SUM(total_amount) as total')
        )
        ->where('status', 'paid')
        ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
        ->groupBy(DB::raw('EXTRACT(HOUR FROM created_at)'))
        ->orderBy('hour');

        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        return $query->get()->toArray();
    }

    /**
     * Category performance data
     */
    private function getCategoryPerformanceData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = InvoiceItem::select(
            'categories.name as category',
            DB::raw('SUM(invoice_items.line_total) as total_sales')
        )
        ->join('products', 'invoice_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
        ->where('invoices.status', 'paid')
        ->whereBetween('invoices.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
        ->groupBy('categories.name');

        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        return $query->get()->toArray();
    }

    /**
     * Profit margin data
     */
    private function getProfitMarginData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');
        $groupBy = $filters['group_by'] ?? 'day';

        $dateFormat = match ($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        // Mock profit margin calculation - replace with actual cost tracking
        $query = Invoice::select(
            DB::raw("TO_CHAR(created_at, '$dateFormat') as period"),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('SUM(total_amount * 0.6) as cost'), // Mock 60% cost ratio
            DB::raw('ROUND(((SUM(total_amount) - SUM(total_amount * 0.6)) / SUM(total_amount)) * 100, 2) as margin_percentage')
        )
        ->where('status', 'paid')
        ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
        ->groupBy(DB::raw("TO_CHAR(created_at, '$dateFormat')"))
        ->orderBy('period');

        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        return $query->get()->toArray();
    }

    /**
     * Expense tracking data
     */
    private function getExpenseTrackingData(array $filters, array $advancedFilters = []): array
    {
        // Mock expense data - replace with actual expense tracking system
        return [
            ['category' => 'Rent', 'amount' => 1500],
            ['category' => 'Utilities', 'amount' => 300],
            ['category' => 'Supplies', 'amount' => 800],
            ['category' => 'Marketing', 'amount' => 500],
            ['category' => 'Other', 'amount' => 200]
        ];
    }

    /**
     * Top customers data
     */
    private function getTopCustomersData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');
        $limit = $filters['limit'] ?? 10;

        $query = Customer::select(
            'customers.id',
            'customers.first_name',
            'customers.last_name',
            DB::raw('SUM(invoices.total_amount) as total_spent'),
            DB::raw('COUNT(invoices.id) as order_count'),
            DB::raw('MAX(invoices.created_at) as last_purchase'),
            DB::raw("'active' as status") // Mock status
        )
        ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
        ->where('invoices.status', 'paid')
        ->whereBetween('invoices.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
        ->groupBy('customers.id', 'customers.first_name', 'customers.last_name')
        ->orderBy('total_spent', 'desc')
        ->limit($limit);

        $query = $this->applyAdvancedFilters($query, $advancedFilters);

        return $query->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'name' => trim($customer->first_name . ' ' . $customer->last_name),
                'total_spent' => (float) $customer->total_spent,
                'order_count' => (int) $customer->order_count,
                'last_purchase' => $customer->last_purchase,
                'status' => $customer->status
            ];
        })->toArray();
    }

    /**
     * Sales forecast data
     */
    private function getSalesForecastData(array $filters, array $advancedFilters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        // Get historical data
        $historical = Invoice::select(
            DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as period"),
            DB::raw('SUM(total_amount) as value'),
            DB::raw('false as is_forecast')
        )
        ->where('status', 'paid')
        ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
        ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
        ->orderBy('period')
        ->get()
        ->toArray();

        // Simple forecast calculation - replace with actual forecasting algorithm
        $avgDaily = collect($historical)->avg('value');
        $forecast = [];
        $period = Carbon::parse($dateTo)->addDay();

        for ($i = 0; $i < 7; $i++) {
            $forecast[] = [
                'period' => $period->format('Y-m-d'),
                'value' => $avgDaily * (1 + (rand(-10, 10) / 100)), // Add some variation
                'is_forecast' => true
            ];
            $period->addDay();
        }

        return array_merge($historical, $forecast);
    }
}


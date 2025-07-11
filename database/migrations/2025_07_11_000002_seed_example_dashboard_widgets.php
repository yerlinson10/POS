<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar widgets de ejemplo para el usuario con ID 1 (admin)
        $widgets = [
            [
                'user_id' => 1,
                'widget_type' => 'sales_chart',
                'title' => 'Ventas Últimos 7 Días',
                'x' => 0,
                'y' => 0,
                'width' => 6,
                'height' => 4,
                'config' => json_encode([
                    'chartType' => 'line',
                    'colors' => ['#3b82f6'],
                    'showLegend' => true,
                    'showGrid' => true
                ]),
                'filters' => json_encode([
                    'group_by' => 'day'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 1,
                'widget_type' => 'sales_stats',
                'title' => 'Estadísticas del Mes',
                'x' => 6,
                'y' => 0,
                'width' => 6,
                'height' => 4,
                'config' => json_encode([]),
                'filters' => json_encode([
                    'date_from' => now()->startOfMonth()->format('Y-m-d'),
                    'date_to' => now()->format('Y-m-d')
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 1,
                'widget_type' => 'top_products',
                'title' => 'Top 5 Productos',
                'x' => 0,
                'y' => 4,
                'width' => 6,
                'height' => 4,
                'config' => json_encode([
                    'chartType' => 'bar',
                    'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6']
                ]),
                'filters' => json_encode([
                    'limit' => 5
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 1,
                'widget_type' => 'low_stock',
                'title' => 'Productos con Bajo Stock',
                'x' => 6,
                'y' => 4,
                'width' => 6,
                'height' => 4,
                'config' => json_encode([]),
                'filters' => json_encode([
                    'stock_threshold' => 10,
                    'limit' => 10
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('dashboard_widgets')->insert($widgets);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('dashboard_widgets')->where('user_id', 1)->delete();
    }
};

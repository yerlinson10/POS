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
        // Verificar si existe al menos un usuario antes de insertar widgets
        $firstUser = DB::table('users')->first();

        if (!$firstUser) {
            // Si no hay usuarios, crear uno temporal para los widgets
            $userId = DB::table('users')->insertGetId([
                'name' => 'Admin User',
                'email' => 'admin@system.local',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $firstUser->id;
        }

        // Insert example widgets for the first available user
        $widgets = [
            [
                'user_id' => $userId,
                'widget_type' => 'sales_chart',
                'title' => 'Sales Last 7 Days',
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
                'user_id' => $userId,
                'widget_type' => 'sales_stats',
                'title' => 'Statistics of the Month',
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
                'user_id' => $userId,
                'widget_type' => 'top_products',
                'title' => 'Top 5 Products',
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
                'user_id' => $userId,
                'widget_type' => 'low_stock',
                'title' => 'Low Stock Products',
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
        // Eliminar todos los widgets de ejemplo
        DB::table('dashboard_widgets')->whereIn('widget_type', [
            'sales_chart',
            'sales_stats',
            'top_products',
            'low_stock'
        ])->delete();
    }
};

<?php
/**
 * Script manual para agregar la columna advanced_filters a dashboard_widgets
 * Ejecutar desde el directorio raíz del proyecto: php database/scripts/add_advanced_filters_column.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Obtener conexión a la base de datos
    $db = \Illuminate\Support\Facades\DB::connection();

    // Verificar si la columna ya existe
    $columns = $db->select("SHOW COLUMNS FROM dashboard_widgets LIKE 'advanced_filters'");

    if (empty($columns)) {
        echo "Agregando columna 'advanced_filters' a la tabla dashboard_widgets...\n";

        // Agregar la columna
        $db->statement("ALTER TABLE dashboard_widgets ADD COLUMN advanced_filters JSON NULL AFTER filters");

        echo "✅ Columna 'advanced_filters' agregada exitosamente!\n";

        // Verificar la estructura de la tabla
        echo "\nEstructura actual de la tabla dashboard_widgets:\n";
        $structure = $db->select("DESCRIBE dashboard_widgets");
        foreach ($structure as $column) {
            echo "- {$column->Field} ({$column->Type})\n";
        }

    } else {
        echo "✅ La columna 'advanced_filters' ya existe en la tabla dashboard_widgets.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Script completado exitosamente!\n";
?>

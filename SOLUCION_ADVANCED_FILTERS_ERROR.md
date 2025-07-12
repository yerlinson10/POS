# Solución al Error: Column 'advanced_filters' not found

## Problema
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'advanced_filters' in 'field list'
```

Este error indica que la tabla `dashboard_widgets` no tiene la columna `advanced_filters` necesaria para los filtros avanzados.

## Soluciones

### Opción 1: Actualizar PHP y ejecutar migración de Laravel (RECOMENDADO)

1. **Actualizar PHP a versión 8.2+**:
   - En Laragon: Menú → PHP → Cambiar a PHP 8.2 o superior
   - Reiniciar Laragon

2. **Ejecutar migración**:
   ```bash
   cd c:\laragon\www\POS
   php artisan migrate
   ```

### Opción 2: Ejecutar script PHP manual

```bash
cd c:\laragon\www\POS
php database/scripts/add_advanced_filters_column.php
```

### Opción 3: Ejecutar SQL manualmente

1. Abrir phpMyAdmin o tu cliente MySQL
2. Seleccionar la base de datos del POS
3. Ejecutar esta query:

```sql
ALTER TABLE `dashboard_widgets` 
ADD COLUMN `advanced_filters` JSON NULL AFTER `filters`;
```

### Opción 4: Usar HeidiSQL, MySQL Workbench, etc.

1. Conectar a tu base de datos MySQL
2. Navegar a la tabla `dashboard_widgets`
3. Agregar nueva columna:
   - Nombre: `advanced_filters`
   - Tipo: `JSON`
   - Permitir NULL: Sí
   - Posición: Después de `filters`

## Verificación

Después de aplicar cualquiera de las soluciones, verifica que la columna existe:

```sql
DESCRIBE dashboard_widgets;
```

Deberías ver la columna `advanced_filters` de tipo `json` en la lista.

## Causa del Problema

- El proyecto requiere PHP 8.2+ pero tu entorno tiene PHP 7.4.26
- La migración `2025_07_11_000003_add_advanced_filters_to_dashboard_widgets_table` no se pudo ejecutar
- Sin esta columna, Laravel no puede guardar los filtros avanzados

## Después de la Solución

Una vez agregada la columna, podrás:
- ✅ Crear widgets con filtros avanzados
- ✅ Usar todos los operadores de filtros
- ✅ Aplicar filtros contextuales por tipo de widget

¡El dashboard funcionará completamente!

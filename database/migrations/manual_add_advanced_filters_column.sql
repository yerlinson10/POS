-- Migración manual para agregar la columna advanced_filters
-- Ejecutar esta query en tu base de datos MySQL

ALTER TABLE `dashboard_widgets`
ADD COLUMN `advanced_filters` JSON NULL AFTER `filters`;

-- Verificar que la columna se agregó correctamente
DESCRIBE `dashboard_widgets`;

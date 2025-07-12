# Dashboard Dinámico - Instrucciones de Instalación

## Resumen
Se ha implementado un sistema completo de dashboard dinámico con GridStack.js que permite a los usuarios crear y personalizar widgets con filtros avanzados.

## Características Implementadas

### 1. Backend
- **Modelo DashboardWidget**: Para almacenar configuraciones de widgets por usuario
- **DashboardWidgetService**: Servicio para manejar la lógica de widgets y datos
- **DashboardWidgetController**: Controlador API para operaciones CRUD de widgets
- **Migración**: Base de datos para widgets personalizados

### 2. Frontend
- **Dashboard Dinámico**: Interfaz principal con GridStack.js
- **Widgets Personalizables**: 8 tipos diferentes de widgets
- **Filtros Avanzados**: Sistema completo de filtros por fechas, usuarios, categorías, etc.
- **Configuración Visual**: Personalización de colores, tipos de gráficos, etc.

### 3. Tipos de Widgets Disponibles
1. **Gráfico de Ventas** - Ventas en el tiempo (línea/barras)
2. **Estadísticas de Ventas** - Métricas clave de ventas
3. **Productos Más Vendidos** - Lista/gráfico de top productos
4. **Productos con Bajo Stock** - Tabla de productos para reposición
5. **Ventas Recientes** - Últimas transacciones
6. **Métodos de Pago** - Distribución por métodos (circular/barras)
7. **Ingresos Mensuales** - Evolución mensual de ingresos
8. **Estadísticas de Clientes** - Métricas y top clientes

## Pasos de Instalación

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

### 2. Instalar Dependencias NPM (ya instaladas)
```bash
npm install chart.js chartjs-adapter-date-fns date-fns vue-chartjs
```

### 3. Compilar Assets
```bash
npm run build
# o para desarrollo
npm run dev
```

### 4. Verificar Rutas
Las rutas del dashboard dinámico ya están configuradas en `/routes/web.php`:
- `/dashboard/dynamic` - Vista principal
- `/dashboard/widgets/*` - API endpoints

## Uso del Sistema

### Acceso
1. Ir a Dashboard normal (`/dashboard`)
2. Hacer clic en "Dashboard Dinámico" en la esquina superior derecha
3. O acceder directamente a `/dashboard/dynamic`

### Crear Widgets
1. Hacer clic en "Agregar Widget"
2. Seleccionar tipo de widget
3. Configurar título, tamaño y filtros
4. Guardar

### Personalizar Widgets
1. Hacer clic en el menú "..." del widget
2. Seleccionar "Configurar"
3. Ajustar apariencia, filtros y configuración
4. Guardar cambios

### Organizar Dashboard
- Arrastrar y soltar widgets para reorganizar
- Redimensionar widgets desde las esquinas
- Las posiciones se guardan automáticamente

## Filtros Avanzados Disponibles

### Filtros Globales
- **Rango de Fechas**: Desde/hasta
- **Método de Pago**: Efectivo/Tarjeta/Todos
- **Usuario**: Filtrar por vendedor específico
- **Categoría**: Para productos (donde aplique)

### Filtros Específicos por Widget
- **Agrupación**: Por hora/día/mes (gráficos temporales)
- **Límite**: Número de registros a mostrar
- **Umbral de Stock**: Para productos con bajo stock

## Tipos de Gráficos Soportados
- **Línea**: Para tendencias temporales
- **Barras**: Para comparaciones
- **Circular (Pie)**: Para distribuciones
- **Dona (Doughnut)**: Variante del circular
- **Tablas**: Para datos detallados
- **Métricas**: Para KPIs importantes

## Características Técnicas

### Persistencia
- Todas las configuraciones se guardan por usuario
- Posiciones y tamaños de widgets se mantienen
- Filtros personalizados se preservan

### Rendimiento
- Carga lazy de datos
- Actualización individual de widgets
- Caché de consultas optimizada

### Responsive
- Grid adaptativo para diferentes pantallas
- Widgets redimensionables
- Interfaz móvil-friendly

## API Endpoints

### Widgets
- `GET /dashboard/widgets` - Listar widgets del usuario
- `POST /dashboard/widgets` - Crear nuevo widget
- `PUT /dashboard/widgets/{id}` - Actualizar widget
- `DELETE /dashboard/widgets/{id}` - Eliminar widget
- `POST /dashboard/widgets/positions` - Actualizar posiciones
- `GET /dashboard/widgets/{id}/refresh` - Refrescar datos

### Opciones
- `GET /dashboard/filter-options` - Obtener opciones de filtros

## Configuración Adicional

### Personalización de Colores
Cada widget puede tener hasta 4 colores personalizados que se aplican automáticamente según el tipo de gráfico.

### Opciones de Exportación (Futuro)
El sistema está preparado para agregar funcionalidades de exportación de datos y gráficos.

## Solución de Problemas

### Error de Migraciones
Si hay problemas con PHP, verificar la versión mínima requerida (PHP 8.2+)

### Problemas de Compilación
Asegurarse de que todas las dependencias están instaladas:
```bash
npm install
```

### Datos No Aparecen
Verificar que hay datos en la base de datos (ventas, productos, clientes) para que los widgets muestren información.

## Próximos Pasos Sugeridos

1. **Exportación**: Agregar funcionalidad para exportar dashboards
2. **Plantillas**: Crear plantillas predefinidas de dashboards
3. **Compartir**: Permitir compartir configuraciones entre usuarios
4. **Alertas**: Sistema de notificaciones basado en métricas
5. **Más Widgets**: Agregar widgets de inventario, proveedores, etc.

El sistema está completamente funcional y listo para usar. Los usuarios pueden crear dashboards completamente personalizados con múltiples widgets y filtros avanzados.

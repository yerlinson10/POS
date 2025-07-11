// Ejemplos de uso de la API del Dashboard Dinámico

// 1. Obtener todos los widgets del usuario actual
const getWidgets = async () => {
    try {
        const response = await fetch('/dashboard/widgets');
        const widgets = await response.json();
        return widgets;
    } catch (error) {
        console.error('Error obteniendo widgets:', error);
    }
};

// 2. Crear un nuevo widget
const createWidget = async (widgetData) => {
    try {
        const response = await fetch('/dashboard/widgets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(widgetData)
        });

        const newWidget = await response.json();
        return newWidget;
    } catch (error) {
        console.error('Error creando widget:', error);
    }
};

// 3. Actualizar configuración de un widget
const updateWidget = async (widgetId, updateData) => {
    try {
        const response = await fetch(`/dashboard/widgets/${widgetId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(updateData)
        });

        const updatedWidget = await response.json();
        return updatedWidget;
    } catch (error) {
        console.error('Error actualizando widget:', error);
    }
};

// 4. Actualizar posiciones de múltiples widgets
const updateWidgetPositions = async (widgets) => {
    try {
        const response = await fetch('/dashboard/widgets/positions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ widgets })
        });

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error actualizando posiciones:', error);
    }
};

// 5. Refrescar datos de un widget específico
const refreshWidget = async (widgetId) => {
    try {
        const response = await fetch(`/dashboard/widgets/${widgetId}/refresh`);
        const refreshedWidget = await response.json();
        return refreshedWidget;
    } catch (error) {
        console.error('Error refrescando widget:', error);
    }
};

// 6. Eliminar un widget
const deleteWidget = async (widgetId) => {
    try {
        const response = await fetch(`/dashboard/widgets/${widgetId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const result = await response.json();
        return result;
    } catch (error) {
    }
};

// 7. Obtener opciones de filtros disponibles
const getFilterOptions = async () => {
    try {
        const response = await fetch('/dashboard/filter-options');
        const options = await response.json();
        return options;
    } catch (error) {
        console.error('Error obteniendo opciones de filtros:', error);
    }
};

// Ejemplos de datos para crear widgets
const exampleWidgets = {
    salesChart: {
        widget_type: 'sales_chart',
        title: 'Ventas por Día',
        x: 0,
        y: 0,
        width: 6,
        height: 4,
        config: {
            chartType: 'line',
            colors: ['#3b82f6'],
            showLegend: true,
            showGrid: true
        },
        filters: {
            date_from: '2025-07-01',
            date_to: '2025-07-11',
            group_by: 'day'
        }
    },

    topProducts: {
        widget_type: 'top_products',
        title: 'Top 10 Productos',
        x: 6,
        y: 0,
        width: 6,
        height: 4,
        config: {
            chartType: 'bar',
            colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6']
        },
        filters: {
            limit: 10,
            date_from: '2025-07-01'
        }
    },

    paymentMethods: {
        widget_type: 'payment_methods',
        title: 'Distribución de Pagos',
        x: 0,
        y: 4,
        width: 4,
        height: 3,
        config: {
            chartType: 'doughnut',
            showLegend: true
        },
        filters: {
            date_from: '2025-07-01',
            date_to: '2025-07-11'
        }
    },

    lowStock: {
        widget_type: 'low_stock',
        title: 'Productos con Bajo Stock',
        x: 4,
        y: 4,
        width: 8,
        height: 3,
        config: {},
        filters: {
            stock_threshold: 10,
            limit: 15
        }
    }
};

// Función para crear un dashboard de ejemplo
const createExampleDashboard = async () => {

    for (const [key, widgetData] of Object.entries(exampleWidgets)) {
        await createWidget(widgetData);
        // Pequeña pausa entre creaciones
        await new Promise(resolve => setTimeout(resolve, 500));
    }


};

// Exportar funciones para uso en consola
window.dashboardAPI = {
    getWidgets,
    createWidget,
    updateWidget,
    updateWidgetPositions,
    refreshWidget,
    deleteWidget,
    getFilterOptions,
    createExampleDashboard,
    exampleWidgets
};

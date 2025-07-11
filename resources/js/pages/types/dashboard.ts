export interface Widget {
    id: number;
    type: string;
    title: string;
    x: number;
    y: number;
    w: number;
    h: number;
    config: WidgetConfig;
    filters: WidgetFilters;
    advanced_filters?: AdvancedFilterGroup[];
    data: any;
}

export interface WidgetConfig {
    chartType?: 'line' | 'bar' | 'pie' | 'doughnut' | 'candlestick';
    colors?: string[];
    showLegend?: boolean;
    showGrid?: boolean;
    tableColumns?: string[];
    metric?: string;
    format?: 'currency' | 'number' | 'percentage';
}

export interface AdvancedFilter {
    field: string;
    operator: string;
    value: any;
    type: 'string' | 'number' | 'date' | 'boolean';
}

export interface AdvancedFilterGroup {
    operator: 'AND' | 'OR';
    filters: AdvancedFilter[];
}

export interface WidgetFilters {
    date_from?: string;
    date_to?: string;
    payment_method?: string;
    user_id?: number;
    category_id?: number;
    group_by?: 'hour' | 'day' | 'month';
    limit?: number;
    stock_threshold?: number;
}

export interface FilterOptions {
    payment_methods: Array<{ value: string; label: string }>;
    users: Array<{ value: number; label: string }>;
    categories: Array<{ value: number; label: string }>;
    group_by_options: Array<{ value: string; label: string }>;
    available_fields: Array<{
        field: string;
        label: string;
        type: 'string' | 'number' | 'date' | 'boolean';
        table?: string;
    }>;
}

export interface ChartData {
    labels: string[];
    datasets: Array<{
        label: string;
        data: number[];
        backgroundColor?: string | string[];
        borderColor?: string;
        fill?: boolean;
    }>;
}

export interface TableData {
    headers: string[];
    rows: Array<Record<string, any>>;
}

export interface StatsData {
    value: number | string;
    label: string;
    change?: number;
    format?: 'currency' | 'number' | 'percentage';
}

export const WIDGET_TYPES = {
    SALES_CHART: 'sales_chart',
    SALES_STATS: 'sales_stats',
    TOP_PRODUCTS: 'top_products',
    LOW_STOCK: 'low_stock',
    RECENT_SALES: 'recent_sales',
    PAYMENT_METHODS: 'payment_methods',
    MONTHLY_REVENUE: 'monthly_revenue',
    CUSTOMER_STATS: 'customer_stats'
} as const;

export const CHART_TYPES = {
    LINE: 'line',
    BAR: 'bar',
    PIE: 'pie',
    DOUGHNUT: 'doughnut',
    CANDLESTICK: 'candlestick'
} as const;

export const WIDGET_DEFINITIONS = {
    [WIDGET_TYPES.SALES_CHART]: {
        name: 'Gráfico de Ventas',
        description: 'Gráfico de ventas en el tiempo',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.LINE, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.SALES_STATS]: {
        name: 'Estadísticas de Ventas',
        description: 'Métricas clave de ventas',
        defaultSize: { w: 4, h: 2 },
        supportedCharts: []
    },
    [WIDGET_TYPES.TOP_PRODUCTS]: {
        name: 'Productos Más Vendidos',
        description: 'Lista de productos más vendidos',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.BAR, CHART_TYPES.PIE]
    },
    [WIDGET_TYPES.LOW_STOCK]: {
        name: 'Productos con Bajo Stock',
        description: 'Productos que necesitan reposición',
        defaultSize: { w: 6, h: 3 },
        supportedCharts: []
    },
    [WIDGET_TYPES.RECENT_SALES]: {
        name: 'Ventas Recientes',
        description: 'Últimas ventas realizadas',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: []
    },
    [WIDGET_TYPES.PAYMENT_METHODS]: {
        name: 'Métodos de Pago',
        description: 'Distribución por métodos de pago',
        defaultSize: { w: 4, h: 3 },
        supportedCharts: [CHART_TYPES.PIE, CHART_TYPES.DOUGHNUT, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.MONTHLY_REVENUE]: {
        name: 'Ingresos Mensuales',
        description: 'Evolución de ingresos por mes',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.LINE, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.CUSTOMER_STATS]: {
        name: 'Estadísticas de Clientes',
        description: 'Métricas de clientes',
        defaultSize: { w: 4, h: 3 },
        supportedCharts: []
    }
};

export const FILTER_OPERATORS = {
    STRING: {
        'is': 'Es igual a',
        'is_not': 'No es igual a',
        'contains': 'Contiene',
        'not_contains': 'No contiene',
        'starts_with': 'Comienza con',
        'ends_with': 'Termina con',
        'is_empty': 'Está vacío',
        'is_not_empty': 'No está vacío'
    },
    NUMBER: {
        'equals': 'Igual a',
        'not_equals': 'No igual a',
        'greater_than': 'Mayor que',
        'less_than': 'Menor que',
        'greater_equal': 'Mayor o igual que',
        'less_equal': 'Menor o igual que',
        'between': 'Entre',
        'is_null': 'Es nulo',
        'is_not_null': 'No es nulo'
    },
    DATE: {
        'equals': 'Igual a',
        'not_equals': 'No igual a',
        'after': 'Después de',
        'before': 'Antes de',
        'between': 'Entre',
        'is_null': 'Es nulo',
        'is_not_null': 'No es nulo'
    },
    BOOLEAN: {
        'is_true': 'Es verdadero',
        'is_false': 'Es falso',
        'is_null': 'Es nulo'
    }
} as const;

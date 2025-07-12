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
    CUSTOMER_STATS: 'customer_stats',
    // New widgets
    INVENTORY_VALUE: 'inventory_value',
    DAILY_TARGETS: 'daily_targets',
    HOURLY_SALES: 'hourly_sales',
    CATEGORY_PERFORMANCE: 'category_performance',
    PROFIT_MARGIN: 'profit_margin',
    EXPENSE_TRACKING: 'expense_tracking',
    TOP_CUSTOMERS: 'top_customers',
    SALES_FORECAST: 'sales_forecast'
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
        name: 'Sales Chart',
        description: 'Sales chart over time',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.LINE, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.SALES_STATS]: {
        name: 'Sales Statistics',
        description: 'Key sales metrics',
        defaultSize: { w: 4, h: 2 },
        supportedCharts: []
    },
    [WIDGET_TYPES.TOP_PRODUCTS]: {
        name: 'Top Products',
        description: 'Best selling products list',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.BAR, CHART_TYPES.PIE]
    },
    [WIDGET_TYPES.LOW_STOCK]: {
        name: 'Low Stock Products',
        description: 'Products that need restocking',
        defaultSize: { w: 6, h: 3 },
        supportedCharts: []
    },
    [WIDGET_TYPES.RECENT_SALES]: {
        name: 'Recent Sales',
        description: 'Latest sales transactions',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: []
    },
    [WIDGET_TYPES.PAYMENT_METHODS]: {
        name: 'Payment Methods',
        description: 'Payment method distribution',
        defaultSize: { w: 4, h: 3 },
        supportedCharts: [CHART_TYPES.PIE, CHART_TYPES.DOUGHNUT, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.MONTHLY_REVENUE]: {
        name: 'Monthly Revenue',
        description: 'Monthly revenue evolution',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.LINE, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.CUSTOMER_STATS]: {
        name: 'Customer Statistics',
        description: 'Customer metrics',
        defaultSize: { w: 4, h: 3 },
        supportedCharts: []
    },
    // New widgets definitions
    [WIDGET_TYPES.INVENTORY_VALUE]: {
        name: 'Inventory Value',
        description: 'Total inventory value and breakdown',
        defaultSize: { w: 4, h: 3 },
        supportedCharts: [CHART_TYPES.PIE, CHART_TYPES.DOUGHNUT]
    },
    [WIDGET_TYPES.DAILY_TARGETS]: {
        name: 'Daily Targets',
        description: 'Daily sales targets vs actual',
        defaultSize: { w: 6, h: 3 },
        supportedCharts: [CHART_TYPES.BAR, CHART_TYPES.LINE]
    },
    [WIDGET_TYPES.HOURLY_SALES]: {
        name: 'Hourly Sales',
        description: 'Sales distribution by hour',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.LINE, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.CATEGORY_PERFORMANCE]: {
        name: 'Category Performance',
        description: 'Sales by product category',
        defaultSize: { w: 5, h: 4 },
        supportedCharts: [CHART_TYPES.BAR, CHART_TYPES.PIE, CHART_TYPES.DOUGHNUT]
    },
    [WIDGET_TYPES.PROFIT_MARGIN]: {
        name: 'Profit Margin',
        description: 'Profit margin analysis',
        defaultSize: { w: 4, h: 3 },
        supportedCharts: [CHART_TYPES.LINE, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.EXPENSE_TRACKING]: {
        name: 'Expense Tracking',
        description: 'Business expenses overview',
        defaultSize: { w: 5, h: 3 },
        supportedCharts: [CHART_TYPES.PIE, CHART_TYPES.BAR]
    },
    [WIDGET_TYPES.TOP_CUSTOMERS]: {
        name: 'Top Customers',
        description: 'Highest value customers',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: []
    },
    [WIDGET_TYPES.SALES_FORECAST]: {
        name: 'Sales Forecast',
        description: 'Predicted sales trends',
        defaultSize: { w: 6, h: 4 },
        supportedCharts: [CHART_TYPES.LINE, CHART_TYPES.BAR]
    }
};

export const FILTER_OPERATORS = {
    STRING: {
        'is': 'Equals',
        'is_not': 'Not equals',
        'contains': 'Contains',
        'not_contains': 'Does not contain',
        'starts_with': 'Starts with',
        'ends_with': 'Ends with',
        'is_empty': 'Is empty',
        'is_not_empty': 'Is not empty'
    },
    NUMBER: {
        'equals': 'Equals',
        'not_equals': 'Not equals',
        'greater_than': 'Greater than',
        'less_than': 'Less than',
        'greater_equal': 'Greater or equal',
        'less_equal': 'Less or equal',
        'between': 'Between',
        'is_null': 'Is null',
        'is_not_null': 'Is not null'
    },
    DATE: {
        'equals': 'Equals',
        'not_equals': 'Not equals',
        'after': 'After',
        'before': 'Before',
        'between': 'Between',
        'is_null': 'Is null',
        'is_not_null': 'Is not null'
    },
    BOOLEAN: {
        'is_true': 'Is true',
        'is_false': 'Is false',
        'is_null': 'Is null'
    }
} as const;

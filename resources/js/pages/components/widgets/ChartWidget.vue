<script setup lang="ts">
import { computed } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    ChartData,
    ChartOptions
} from 'chart.js';
import { Line, Bar, Pie, Doughnut } from 'vue-chartjs';
import type { Widget } from '../../types/dashboard';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

interface Props {
    widget: Widget;
    formatCurrency: (amount: number) => string;
}

const props = defineProps<Props>();

const chartComponent = computed(() => {
    switch (props.widget.config.chartType) {
        case 'bar':
            return Bar;
        case 'pie':
            return Pie;
        case 'doughnut':
            return Doughnut;
        default:
            return Line;
    }
});

const chartData = computed<ChartData<any>>(() => {
    if (!props.widget.data || !Array.isArray(props.widget.data)) {
        return {
            labels: [],
            datasets: []
        };
    }

    const data = props.widget.data;

    switch (props.widget.type) {
        case 'sales_chart':
            return {
                labels: data.map((item: any) => item.period),
                datasets: [{
                    label: 'Ventas',
                    data: data.map((item: any) => item.total),
                    backgroundColor: props.widget.config.chartType === 'line'
                        ? 'rgba(59, 130, 246, 0.1)'
                        : props.widget.config.colors?.[0] || '#3b82f6',
                    borderColor: props.widget.config.colors?.[0] || '#3b82f6',
                    borderWidth: 2,
                    fill: props.widget.config.chartType === 'line'
                }]
            };

        case 'payment_methods':
            return {
                labels: data.map((item: any) => item.label),
                datasets: [{
                    label: 'Ventas por Método',
                    data: data.map((item: any) => item.total),
                    backgroundColor: [
                        '#3b82f6',
                        '#ef4444',
                        '#10b981',
                        '#f59e0b',
                        '#8b5cf6'
                    ]
                }]
            };

        case 'monthly_revenue':
            return {
                labels: data.map((item: any) => item.month),
                datasets: [{
                    label: 'Ingresos Mensuales',
                    data: data.map((item: any) => item.revenue),
                    backgroundColor: props.widget.config.chartType === 'line'
                        ? 'rgba(16, 185, 129, 0.1)'
                        : props.widget.config.colors?.[0] || '#10b981',
                    borderColor: props.widget.config.colors?.[0] || '#10b981',
                    borderWidth: 2,
                    fill: props.widget.config.chartType === 'line'
                }]
            };

        case 'top_products':
            return {
                labels: data.map((item: any) => item.name),
                datasets: [{
                    label: 'Cantidad Vendida',
                    data: data.map((item: any) => item.total_sold),
                    backgroundColor: props.widget.config.colors || [
                        '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
                        '#f97316', '#06b6d4', '#84cc16', '#a855f7', '#ec4899'
                    ]
                }]
            };

        default:
            return {
                labels: [],
                datasets: []
            };
    }
});

const chartOptions = computed<ChartOptions<any>>(() => {
    const baseOptions: ChartOptions<any> = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: props.widget.config.showLegend !== false,
                position: 'bottom'
            },
            title: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: (context: any) => {
                        if (props.widget.type === 'sales_chart' ||
                            props.widget.type === 'payment_methods' ||
                            props.widget.type === 'monthly_revenue') {
                            return `${context.dataset.label}: ${props.formatCurrency(context.parsed.y || context.parsed)}`;
                        }
                        return `${context.dataset.label}: ${context.parsed.y || context.parsed}`;
                    }
                }
            }
        },
        scales: {}
    };

    // Configurar escalas para gráficos que no sean circulares
    if (props.widget.config.chartType !== 'pie' && props.widget.config.chartType !== 'doughnut') {
        baseOptions.scales = {
            x: {
                grid: {
                    display: props.widget.config.showGrid !== false
                }
            },
            y: {
                grid: {
                    display: props.widget.config.showGrid !== false
                },
                ticks: {
                    callback: function(value: any) {
                        if (props.widget.type === 'sales_chart' ||
                            props.widget.type === 'payment_methods' ||
                            props.widget.type === 'monthly_revenue') {
                            return props.formatCurrency(value);
                        }
                        return value;
                    }
                }
            }
        };
    }

    return baseOptions;
});

const hasData = computed(() => {
    return props.widget.data && Array.isArray(props.widget.data) && props.widget.data.length > 0;
});
</script>

<template>
    <div class="h-full flex items-center justify-center">
        <div v-if="!hasData" class="text-center text-muted-foreground">
            <p>No hay datos disponibles</p>
        </div>

        <div v-else class="w-full h-full" style="min-height: 200px;">
            <component
                :is="chartComponent"
                :data="chartData"
                :options="chartOptions"
            />
        </div>
    </div>
</template>

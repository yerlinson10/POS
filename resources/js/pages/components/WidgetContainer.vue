<script setup lang="ts">
import { ref, computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { MoreHorizontal, RefreshCw, X, Settings } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import ChartWidget from './widgets/ChartWidget.vue';
import TableWidget from './widgets/TableWidget.vue';
import StatsWidget from './widgets/StatsWidget.vue';
import WidgetSettings from './WidgetSettings.vue';
import type { Widget, FilterOptions } from '../types/dashboard';

interface Props {
    widget: Widget;
    filterOptions: FilterOptions;
}

interface Emits {
    (e: 'remove', widgetId: number): void;
    (e: 'refresh', widgetId: number): void;
    (e: 'updated', widget: Widget): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const showSettings = ref(false);
const isRefreshing = ref(false);

const widgetComponent = computed(() => {
    switch (props.widget.type) {
        case 'sales_chart':
        case 'payment_methods':
        case 'monthly_revenue':
        case 'top_products':
        case 'inventory_value':
        case 'daily_targets':
        case 'hourly_sales':
        case 'category_performance':
        case 'profit_margin':
        case 'expense_tracking':
        case 'sales_forecast':
            return ChartWidget;
        case 'low_stock':
        case 'recent_sales':
        case 'top_customers':
            return TableWidget;
        case 'sales_stats':
        case 'customer_stats':
            return StatsWidget;
        default:
            return StatsWidget;
    }
});

const handleRefresh = async () => {
    isRefreshing.value = true;
    try {
        emit('refresh', props.widget.id);
    } finally {
        setTimeout(() => {
            isRefreshing.value = false;
        }, 500);
    }
};

const handleWidgetUpdate = (updatedWidget: Widget) => {
    emit('updated', updatedWidget);
};

const handleRemove = () => {
    if (confirm('Are you sure you want to delete this widget?')) {
        emit('remove', props.widget.id);
    }
};

const handleSettingsClose = () => {
    showSettings.value = false;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Card class="widget-container h-full flex flex-col">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2 flex-shrink-0">
            <div>
                <CardTitle class="text-sm font-medium">{{ widget.title }}</CardTitle>
                <CardDescription class="text-xs">
                    <Badge variant="outline" class="text-xs">{{ widget.type }}</Badge>
                </CardDescription>
            </div>

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                        <MoreHorizontal class="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="handleRefresh" :disabled="isRefreshing">
                        <RefreshCw :class="{ 'animate-spin': isRefreshing }" class="h-4 w-4 mr-2" />
                        Refresh
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="showSettings = true">
                        <Settings class="h-4 w-4 mr-2" />
                        Configure
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="handleRemove" class="text-destructive">
                        <X class="h-4 w-4 mr-2" />
                        Delete
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </CardHeader>

        <CardContent class="card-content flex-1 p-4 overflow-auto min-h-0">
            <component
                :is="widgetComponent"
                :widget="widget"
                :format-currency="formatCurrency"
            />
        </CardContent>

        <!-- Configuration Modal -->
        <WidgetSettings
            v-if="showSettings"
            :widget="widget"
            :filter-options="filterOptions"
            @close="handleSettingsClose"
            @updated="handleWidgetUpdate"
        />
    </Card>
</template>

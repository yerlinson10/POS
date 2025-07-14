<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { toast } from 'vue-sonner';
import { WIDGET_DEFINITIONS, CHART_TYPES } from '../types/dashboard';
import AdvancedFilters from './AdvancedFilters.vue';
import type { Widget, FilterOptions } from '../types/dashboard';

interface Props {
    widget: Widget;
    filterOptions: FilterOptions;
}

interface Emits {
    (e: 'close'): void;
    (e: 'updated', widget: Widget): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const isLoading = ref(false);

const form = reactive({
    title: props.widget.title,
    config: {
        chartType: props.widget.config?.chartType || 'line',
        colors: props.widget.config?.colors || ['#3b82f6', '#ef4444', '#10b981', '#f59e0b'],
        showLegend: props.widget.config?.showLegend ?? true,
        showGrid: props.widget.config?.showGrid ?? true,
        tableColumns: props.widget.config?.tableColumns || [],
        metric: props.widget.config?.metric || '',
        format: props.widget.config?.format || 'number'
    },
    filters: {
        date_from: props.widget.filters?.date_from || '',
        date_to: props.widget.filters?.date_to || '',
        payment_method: props.widget.filters?.payment_method || 'all',
        user_id: props.widget.filters?.user_id || 'all',
        category_id: props.widget.filters?.category_id || 'all',
        group_by: props.widget.filters?.group_by || 'day',
        limit: props.widget.filters?.limit || 10,
        stock_threshold: props.widget.filters?.stock_threshold || 10
    },
    advanced_filters: Array.isArray(props.widget.advanced_filters) ? props.widget.advanced_filters : []
});

const widgetDef = WIDGET_DEFINITIONS[props.widget.type as keyof typeof WIDGET_DEFINITIONS];

const availableChartTypes = computed(() => {
    if (!widgetDef) return [];
    return widgetDef.supportedCharts.map(type => ({
        value: type,
        label: getChartTypeLabel(type)
    }));
});

const getChartTypeLabel = (type: string) => {
    switch (type) {
        case CHART_TYPES.LINE: return 'Line';
        case CHART_TYPES.BAR: return 'Bar';
        case CHART_TYPES.PIE: return 'Pie';
        case CHART_TYPES.DOUGHNUT: return 'Doughnut';
        case CHART_TYPES.CANDLESTICK: return 'Candlestick';
        default: return type;
    }
};

const updateWidget = async () => {
    isLoading.value = true;

    try {

        const response = await fetch(`/dynamic-dashboard/widgets/${props.widget.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(form)
        });

        if (!response.ok) {
            throw new Error('Error updating widget');
        }

        const updatedWidget = await response.json();

        emit('updated', updatedWidget);
        emit('close');
        toast.success('Widget updated successfully');
    } catch (error) {
        console.error('Error updating widget:', error);
        toast.error('Error updating widget');
    } finally {
        isLoading.value = false;
    }
};

const handleClose = () => {
    emit('close');
};

// Inicializar valores por defecto si no existen
if (!form.config.colors) {
    form.config.colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b'];
}
if (form.config.showLegend === undefined) {
    form.config.showLegend = true;
}
if (form.config.showGrid === undefined) {
    form.config.showGrid = true;
}
</script>

<template>
    <Dialog :open="true" @update:open="handleClose">
        <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Configure Widget: {{ widget.title }}</DialogTitle>
                <DialogDescription>
                    Customize widget appearance and filters
                </DialogDescription>
            </DialogHeader>

            <Tabs default-value="general" class="w-full">
                <TabsList class="grid w-full grid-cols-4">
                    <TabsTrigger value="general">General</TabsTrigger>
                    <TabsTrigger value="appearance">Appearance</TabsTrigger>
                    <TabsTrigger value="filters">Filters</TabsTrigger>
                    <TabsTrigger value="advanced">Advanced</TabsTrigger>
                </TabsList>

                <!-- General Configuration -->
                <TabsContent value="general" class="space-y-4">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="title">Widget Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="Widget name"
                            />
                        </div>

                        <div v-if="availableChartTypes.length > 0" class="space-y-2">
                            <Label for="chartType">Chart Type</Label>
                            <Select v-model="form.config.chartType">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select chart type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="chartType in availableChartTypes"
                                        :key="chartType.value"
                                        :value="chartType.value"
                                    >
                                        {{ chartType.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </TabsContent>

                <!-- Appearance Configuration -->
                <TabsContent value="appearance" class="space-y-4">
                    <div class="space-y-4">
                        <div v-if="availableChartTypes.length > 0" class="space-y-4">
                            <!-- Main colors -->
                            <div class="space-y-2">
                                <Label>Colors</Label>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="(color, index) in form.config.colors?.slice(0, 4)" :key="index" class="space-y-1">
                                        <Label :for="`color-${index}`" class="text-xs">Color {{ index + 1 }}</Label>
                                        <Input
                                            :id="`color-${index}`"
                                            type="color"
                                            v-model="form.config.colors[index]"
                                            class="h-8"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="widget.type === 'top_products' || widget.type === 'recent_sales' || widget.type === 'low_stock'" class="space-y-2">
                            <Label for="limit">Record limit</Label>
                            <Input
                                id="limit"
                                type="number"
                                min="1"
                                max="50"
                                v-model.number="form.filters.limit"
                                placeholder="10"
                            />
                        </div>
                    </div>
                </TabsContent>

                <!-- Filter Configuration -->
                <TabsContent value="filters" class="space-y-4">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="dateFrom">Date From</Label>
                                <Input
                                    id="dateFrom"
                                    type="date"
                                    v-model="form.filters.date_from"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="dateTo">Date To</Label>
                                <Input
                                    id="dateTo"
                                    type="date"
                                    v-model="form.filters.date_to"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="paymentMethod">Payment Method</Label>
                                <Select v-model="form.filters.payment_method">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All methods" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        <SelectItem
                                            v-for="method in filterOptions.payment_methods"
                                            :key="method.value"
                                            :value="method.value"
                                        >
                                            {{ method.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="user">User</Label>
                                <Select v-model="form.filters.user_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All users" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        <SelectItem
                                            v-for="user in filterOptions.users"
                                            :key="user.value"
                                            :value="user.value.toString()"
                                        >
                                            {{ user.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div v-if="widget.type === 'sales_chart' || widget.type === 'monthly_revenue'" class="space-y-2">
                            <Label for="groupBy">Group by</Label>
                            <Select v-model="form.filters.group_by">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select grouping" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in filterOptions.group_by_options"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div v-if="widget.type === 'top_products' || widget.type === 'low_stock'" class="space-y-2">
                            <Label for="category">Category</Label>
                            <Select v-model="form.filters.category_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="All categories" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem
                                        v-for="category in filterOptions.categories"
                                        :key="category.value"
                                        :value="category.value.toString()"
                                    >
                                        {{ category.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div v-if="widget.type === 'low_stock'" class="space-y-2">
                            <Label for="stockThreshold">Low Stock Threshold</Label>
                            <Input
                                id="stockThreshold"
                                type="number"
                                min="1"
                                v-model.number="form.filters.stock_threshold"
                                placeholder="10"
                            />
                        </div>
                    </div>
                </TabsContent>

                <!-- Advanced Filters -->
                <TabsContent value="advanced" class="space-y-4">
                    <AdvancedFilters
                        v-model="form.advanced_filters"
                        :filter-options="filterOptions"
                        :widget-type="widget.type"
                    />
                </TabsContent>
            </Tabs>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2 pt-4 border-t">
                <Button variant="outline" @click="handleClose" :disabled="isLoading">
                    Cancel
                </Button>
                <Button @click="updateWidget" :disabled="isLoading">
                    {{ isLoading ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>

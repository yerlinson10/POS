<script setup lang="ts">
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    ChartBar,
    PieChart,
    TrendingUp,
    Package,
    Users,
    CreditCard,
    Calendar,
    AlertTriangle,
    CheckCircle,
    Settings,
    Filter,
    Palette
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import type { Widget, FilterOptions } from '../types/dashboard';
import { WIDGET_DEFINITIONS } from '../types/dashboard';
import AdvancedFilters from './AdvancedFilters.vue';

interface Props {
    filterOptions: FilterOptions;
}

interface Emits {
    (e: 'close'): void;
    (e: 'created', widget: Widget): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const isLoading = ref(false);
const currentStep = ref(1);
const totalSteps = 3;

const form = ref({
    title: '',
    widget_type: '',
    config: {
        chartType: 'line',
        colors: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b'],
        showLegend: true,
        showGrid: true
    },
    filters: {
        date_from: '',
        date_to: '',
        payment_method: 'all',
        user_id: 'all',
        advanced_filters: []
    }
});

const selectedWidgetDef = computed(() => {
    return form.value.widget_type ? WIDGET_DEFINITIONS[form.value.widget_type as keyof typeof WIDGET_DEFINITIONS] : null;
});

const canProceedToStep = computed(() => {
    switch (currentStep.value) {
        case 1:
            return !!form.value.widget_type;
        case 2:
            return !!form.value.title.trim();
        case 3:
            return true;
        default:
            return false;
    }
});

const isFormValid = computed(() => {
    return form.value.title.trim() && form.value.widget_type;
});

const stepTitle = computed(() => {
    switch (currentStep.value) {
        case 1:
            return 'Seleccionar Tipo de Widget';
        case 2:
            return 'Configurar Widget';
        case 3:
            return 'Configurar Filtros';
        default:
            return '';
    }
});

const availableChartTypes = computed(() => {
    return selectedWidgetDef.value?.supportedCharts || [];
});

const hasChartConfig = computed(() => {
    return (selectedWidgetDef.value?.supportedCharts?.length || 0) > 0;
});

const getWidgetIcon = (widgetType: string) => {
    const iconMap: Record<string, any> = {
        'sales_chart': ChartBar,
        'sales_stats': TrendingUp,
        'top_products': Package,
        'low_stock': AlertTriangle,
        'recent_sales': Calendar,
        'payment_methods': CreditCard,
        'monthly_revenue': PieChart,
        'customer_stats': Users
    };
    return iconMap[widgetType] || ChartBar;
};

const getChartTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        'line': 'Línea',
        'bar': 'Barras',
        'pie': 'Circular',
        'doughnut': 'Dona',
        'candlestick': 'Velas'
    };
    return labels[type] || type;
};

const getChartTypeDescription = (type: string) => {
    const descriptions: Record<string, string> = {
        'line': 'Tendencias en el tiempo',
        'bar': 'Comparación entre categorías',
        'pie': 'Distribución porcentual',
        'doughnut': 'Distribución con espacio central',
        'candlestick': 'Análisis de precios'
    };
    return descriptions[type] || '';
};

const nextStep = () => {
    if (currentStep.value < totalSteps && canProceedToStep.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const handleWidgetTypeChange = (type: string) => {
    form.value.widget_type = type;

    // Auto-generar título basado en el tipo
    if (selectedWidgetDef.value && !form.value.title) {
        form.value.title = selectedWidgetDef.value.name;
    }

    // Configurar el tipo de gráfico por defecto
    if (selectedWidgetDef.value && (selectedWidgetDef.value.supportedCharts?.length ?? 0) > 0) {
        form.value.config.chartType = selectedWidgetDef.value.supportedCharts[0];
    }
};

const createWidget = async () => {
    if (!isFormValid.value) {
        toast.error('Por favor completa todos los campos requeridos');
        return;
    }

    isLoading.value = true;

    try {
        const response = await fetch('/dashboard/widgets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(form.value)
        });

        if (!response.ok) {
            throw new Error('Error al crear widget');
        }

        const newWidget = await response.json();
        toast.success('Widget creado exitosamente');
        emit('created', newWidget);
    } catch (error) {
        console.error('Error creating widget:', error);
        toast.error('Error al crear widget');
    } finally {
        isLoading.value = false;
    }
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Dialog :open="true" @update:open="handleClose">
        <DialogContent class="max-w-5xl max-h-[95vh] overflow-hidden flex flex-col">
            <DialogHeader class="flex-shrink-0">
                <DialogTitle class="flex items-center gap-2">
                    <Settings class="h-5 w-5" />
                    Crear Nuevo Widget
                </DialogTitle>
                <DialogDescription>
                    {{ stepTitle }} ({{ currentStep }}/{{ totalSteps }})
                </DialogDescription>
            </DialogHeader>

            <!-- Progress Steps -->
            <div class="flex items-center justify-center gap-4 py-4 flex-shrink-0">
                <div v-for="step in totalSteps" :key="step" class="flex items-center">
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all',
                            step <= currentStep
                                ? 'bg-primary text-primary-foreground shadow-lg'
                                : 'bg-muted text-muted-foreground'
                        ]"
                    >
                        <CheckCircle v-if="step < currentStep" class="h-4 w-4" />
                        <span v-else>{{ step }}</span>
                    </div>
                    <div
                        v-if="step < totalSteps"
                        :class="[
                            'w-16 h-0.5 mx-2 transition-all',
                            step < currentStep ? 'bg-primary' : 'bg-muted'
                        ]"
                    ></div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto">
                <!-- Step 1: Widget Type Selection -->
                <div v-if="currentStep === 1" class="space-y-6">
                    <div class="text-center space-y-2">
                        <h3 class="text-lg font-semibold">¿Qué tipo de widget deseas crear?</h3>
                        <p class="text-muted-foreground">Selecciona el tipo de widget que mejor se adapte a tus necesidades</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Card
                            v-for="(def, type) in WIDGET_DEFINITIONS"
                            :key="type"
                            :class="[
                                'cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-[1.02]',
                                form.widget_type === type
                                    ? 'ring-2 ring-primary border-primary bg-gradient-to-br from-primary/5 to-primary/10'
                                    : 'hover:border-primary/50'
                            ]"
                            @click="handleWidgetTypeChange(type)"
                        >
                            <CardHeader class="pb-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'p-2 rounded-lg transition-all',
                                            form.widget_type === type
                                                ? 'bg-primary text-primary-foreground shadow-md'
                                                : 'bg-muted text-muted-foreground'
                                        ]"
                                    >
                                        <component :is="getWidgetIcon(type)" class="h-5 w-5" />
                                    </div>
                                    <div class="flex-1">
                                        <CardTitle class="text-base">{{ def.name }}</CardTitle>
                                        <Badge
                                            v-if="form.widget_type === type"
                                            variant="default"
                                            class="text-xs mt-1"
                                        >
                                            ✓ Seleccionado
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="pt-0">
                                <CardDescription class="text-sm mb-3">
                                    {{ def.description }}
                                </CardDescription>
                                <div class="flex items-center justify-between text-xs text-muted-foreground">
                                    <span>Tamaño: {{ def.defaultSize.w }}×{{ def.defaultSize.h }}</span>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                        <span>Disponible</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Step 2: Basic Configuration -->
                <div v-else-if="currentStep === 2" class="space-y-6">
                    <div class="text-center space-y-2">
                        <h3 class="text-lg font-semibold">Configurar tu widget</h3>
                        <p class="text-muted-foreground">Personaliza el título y las opciones básicas</p>
                    </div>

                    <div v-if="selectedWidgetDef" class="space-y-6">
                        <!-- Widget Preview -->
                        <Card class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 border-blue-200 dark:border-blue-800">
                            <CardHeader>
                                <div class="flex items-center gap-3">
                                    <div class="p-3 bg-primary/10 rounded-lg">
                                        <component :is="getWidgetIcon(form.widget_type)" class="h-6 w-6 text-primary" />
                                    </div>
                                    <div>
                                        <CardTitle>{{ selectedWidgetDef.name }}</CardTitle>
                                        <CardDescription>{{ selectedWidgetDef.description }}</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>

                        <!-- Basic Configuration -->
                        <div class="grid gap-6">
                            <div class="space-y-2">
                                <Label for="title" class="text-base font-medium">Título del Widget</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    placeholder="Ej: Ventas de este mes"
                                    class="text-base"
                                />
                                <p class="text-sm text-muted-foreground">
                                    Este será el título que aparecerá en tu widget
                                </p>
                            </div>

                            <!-- Chart Type Selection (if applicable) -->
                            <div v-if="hasChartConfig" class="space-y-4">
                                <Label class="text-base font-medium flex items-center gap-2">
                                    <Palette class="h-4 w-4" />
                                    Tipo de Gráfico
                                </Label>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <Card
                                        v-for="chartType in availableChartTypes"
                                        :key="chartType"
                                        :class="[
                                            'cursor-pointer transition-all',
                                            form.config.chartType === chartType
                                                ? 'ring-2 ring-primary border-primary bg-primary/5'
                                                : 'hover:border-primary/50'
                                        ]"
                                        @click="form.config.chartType = chartType"
                                    >
                                        <CardContent class="p-4 text-center">
                                            <div class="text-sm font-medium">{{ getChartTypeLabel(chartType) }}</div>
                                            <div class="text-xs text-muted-foreground mt-1">{{ getChartTypeDescription(chartType) }}</div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>

                            <!-- Basic Filters -->
                            <div class="space-y-4">
                                <Label class="text-base font-medium flex items-center gap-2">
                                    <Filter class="h-4 w-4" />
                                    Filtros Básicos
                                </Label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="date_from">Fecha desde</Label>
                                        <Input
                                            id="date_from"
                                            type="date"
                                            v-model="form.filters.date_from"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="date_to">Fecha hasta</Label>
                                        <Input
                                            id="date_to"
                                            type="date"
                                            v-model="form.filters.date_to"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Advanced Filters -->
                <div v-else-if="currentStep === 3" class="space-y-6">
                    <div class="text-center space-y-2">
                        <h3 class="text-lg font-semibold">Filtros Avanzados (Opcional)</h3>
                        <p class="text-muted-foreground">Crea filtros complejos para datos más específicos</p>
                    </div>

                    <AdvancedFilters
                        v-model="form.filters.advanced_filters"
                        :filter-options="props.filterOptions"
                    />
                </div>
            </div>

            <Separator class="flex-shrink-0" />

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center pt-4 flex-shrink-0">
                <Button
                    variant="outline"
                    @click="currentStep === 1 ? handleClose() : prevStep()"
                    :disabled="isLoading"
                >
                    {{ currentStep === 1 ? 'Cancelar' : 'Anterior' }}
                </Button>

                <div class="flex gap-2">
                    <Button
                        v-if="currentStep < totalSteps"
                        @click="nextStep"
                        :disabled="!canProceedToStep || isLoading"
                        class="min-w-[100px]"
                    >
                        Siguiente
                    </Button>
                    <Button
                        v-else
                        @click="createWidget"
                        :disabled="!isFormValid || isLoading"
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 min-w-[140px]"
                    >
                        <CheckCircle class="h-4 w-4 mr-2" />
                        {{ isLoading ? 'Creando...' : 'Crear Widget' }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

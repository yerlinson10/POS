<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack.css';
import WidgetContainer from './components/WidgetContainer.vue';
import WidgetCreator from './components/WidgetCreator.vue';
import { Button } from '@/components/ui/button';
import { Plus, RefreshCw } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import type { Widget, FilterOptions } from './types/dashboard';

interface Props {
    widgets: Widget[];
    filterOptions: FilterOptions;
}

const props = defineProps<Props>();

const gridInstance = ref<GridStack | null>(null);
const gridContainer = ref<HTMLElement>();
const widgets = ref<Widget[]>([...props.widgets]);
const showWidgetCreator = ref(false);
const isRefreshing = ref(false);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Dashboard Dinámico', href: '/dashboard/dynamic' }
];

onMounted(() => {
    initializeGrid();
});

const initializeGrid = () => {
    if (!gridContainer.value) return;

    gridInstance.value = GridStack.init({
        cellHeight: 80,
        column: 12,
        margin: 8,
        resizable: { handles: 'e, se, s, sw, w' },
        float: false,
        animate: true,
        minRow: 1,
        maxRow: 0,
        acceptWidgets: true,
    }, gridContainer.value);

    // Eventos del grid
    gridInstance.value.on('change', (event: any, items: any[]) => {
        updateWidgetPositions(items);
    });

    // GridStack automáticamente reconoce los elementos existentes con atributos gs-*
    // No necesitamos llamar addWidgetToGrid para widgets existentes
};

const addWidgetToGrid = (widget: Widget) => {
    if (!gridInstance.value) return;

    // Esperar al siguiente tick para que Vue renderice el nuevo widget
    nextTick(() => {
        const widgetElement = document.querySelector(`[gs-id="${widget.id}"]`) as HTMLElement;
        if (widgetElement && gridInstance.value) {
            // Hacer que GridStack reconozca el nuevo elemento
            gridInstance.value.makeWidget(widgetElement);
        }
    });
};

const updateWidgetPositions = async (items: any[]) => {
    try {
        const updatedWidgets = items.map(item => ({
            id: parseInt(item.id),
            x: item.x,
            y: item.y,
            w: item.w,
            h: item.h
        }));

        await fetch('/dashboard/widgets/positions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ widgets: updatedWidgets })
        });
    } catch (error) {
        console.error('Error updating widget positions:', error);
        toast.error('Error al actualizar posiciones de widgets');
    }
};

const handleWidgetCreated = (newWidget: Widget) => {
    widgets.value.push(newWidget);
    addWidgetToGrid(newWidget);
    showWidgetCreator.value = false;
    toast.success('Widget creado correctamente');

    // Refrescar el layout después de agregar un widget
    setTimeout(() => {
        refreshGridLayout();
    }, 100);
};

const removeWidget = async (widgetId: number) => {
    try {
        await fetch(`/dashboard/widgets/${widgetId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        // Remover del grid
        const widgetElement = document.querySelector(`[gs-id="${widgetId}"]`);
        if (widgetElement && gridInstance.value) {
            gridInstance.value.removeWidget(widgetElement as HTMLElement, false);
        }

        // Remove from state
        widgets.value = widgets.value.filter((w: Widget) => w.id !== widgetId);
        toast.success('Widget deleted successfully');
    } catch (error) {
        console.error('Error removing widget:', error);
        toast.error('Error deleting widget');
    }
};

const refreshWidget = async (widgetId: number) => {
    try {
        const response = await fetch(`/dashboard/widgets/${widgetId}/refresh`);
        const updatedWidget = await response.json();

        const index = widgets.value.findIndex((w: Widget) => w.id === widgetId);
        if (index !== -1) {
            widgets.value[index] = updatedWidget;
        }

        toast.success('Widget updated');
    } catch (error) {
        console.error('Error refreshing widget:', error);
        toast.error('Error updating widget');
    }
};

const handleWidgetUpdated = (updatedWidget: Widget) => {
    const index = widgets.value.findIndex((w: Widget) => w.id === updatedWidget.id);
    if (index !== -1) {
        widgets.value[index] = updatedWidget;
        toast.success('Widget updated successfully');
    }
};

const refreshAllWidgets = async () => {
    isRefreshing.value = true;
    try {
        const response = await fetch('/dashboard/widgets');
        const updatedWidgets = await response.json();
        widgets.value = updatedWidgets;
        toast.success('All widgets updated');
    } catch (error) {
        console.error('Error refreshing widgets:', error);
        toast.error('Error updating widgets');
    } finally {
        isRefreshing.value = false;
    }
};

const refreshGridLayout = () => {
    if (gridInstance.value) {
        // Forzar un resize/refresh del grid
        nextTick(() => {
            gridInstance.value?.compact();
        });
    }
};
</script>

<template>
    <Head title="Dashboard Dinámico" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 w-full mx-auto">
            <!-- Header con controles -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Dashboard Dinámico</h1>
                    <p class="text-muted-foreground">
                        Personaliza tu dashboard con widgets y filtros avanzados
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        @click="refreshAllWidgets"
                        variant="outline"
                        size="sm"
                        :disabled="isRefreshing"
                    >
                        <RefreshCw :class="{ 'animate-spin': isRefreshing }" class="h-4 w-4 mr-2" />
                        Actualizar Todo
                    </Button>

                    <Button
                        @click="showWidgetCreator = true"
                        size="sm"
                    >
                        <Plus class="h-4 w-4 mr-2" />
                        Agregar Widget
                    </Button>
                </div>
            </div>

            <!-- Grid de widgets -->
            <div class="grid-stack" ref="gridContainer">
                <template v-for="widget in widgets" :key="widget.id">
                    <div class="grid-stack-item"
                         :gs-id="widget.id.toString()"
                         :gs-x="widget.x.toString()"
                         :gs-y="widget.y.toString()"
                         :gs-w="widget.w.toString()"
                         :gs-h="widget.h.toString()"
                         :gs-min-w="2"
                         :gs-min-h="2">
                        <div class="grid-stack-item-content" :id="`widget-${widget.id}`">
                            <WidgetContainer
                                :widget="widget"
                                :filter-options="filterOptions"
                                @remove="removeWidget"
                                @refresh="refreshWidget"
                                @updated="handleWidgetUpdated"
                            />
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal para crear widget -->
            <WidgetCreator
                v-if="showWidgetCreator"
                :filter-options="filterOptions"
                @close="showWidgetCreator = false"
                @created="handleWidgetCreated"
            />
        </div>
    </AppLayout>
</template>

<style>
.grid-stack-item-content {
    background-color: hsl(var(--background));
    border-radius: 0.5rem;
    border: 1px solid hsl(var(--border));
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    overflow: hidden;
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.grid-stack-item-removing {
    opacity: 0.5;
}

.grid-stack-placeholder > .placeholder-content {
    background-color: rgb(219 234 254);
    border: 2px dashed rgb(147 197 253);
    border-radius: 0.5rem;
}

/* Asegurar que los widgets se vean correctamente en scroll */
.grid-stack {
    position: relative;
    z-index: 1;
}

.grid-stack-item {
    z-index: 2;
}

.grid-stack-item.ui-draggable-dragging {
    z-index: 999;
}
</style>

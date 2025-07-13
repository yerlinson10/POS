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
    { title: 'Dashboard', href: '/dynamic-dashboard' },
    { title: 'Dashboard Dinámico', href: '/dynamic-dashboard' }
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

        await fetch('/dynamic-dashboard/widgets/positions', {
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
        await fetch(`/dynamic-dashboard/widgets/${widgetId}`, {
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
        const response = await fetch(`/dynamic-dashboard/widgets/${widgetId}/refresh`);
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
        const response = await fetch('/dynamic-dashboard/widgets');
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
    <Head title="Dynamic Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 w-full mx-auto">
            <!-- Header con controles -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Dynamic Dashboard</h1>
                    <p class="text-muted-foreground">
                       Customize your dashboard with advanced widgets and filters
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
                        Update All
                    </Button>

                    <Button
                        @click="showWidgetCreator = true"
                        size="sm"
                    >
                        <Plus class="h-4 w-4 mr-2" />
                        Add Widget
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

<style scoped>
/* Custom Scrollbar Styles */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: hsl(var(--muted) / 0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.3);
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.5);
}

::-webkit-scrollbar-thumb:active {
    background: hsl(var(--muted-foreground) / 0.7);
}

::-webkit-scrollbar-corner {
    background: hsl(var(--muted) / 0.3);
}

/* Firefox scrollbar styling */
* {
    scrollbar-width: thin;
    scrollbar-color: hsl(var(--muted-foreground) / 0.3) hsl(var(--muted) / 0.3);
}

/* Grid container - no transforms that break positioning */
.grid-stack {
    position: relative;
    z-index: 1;
    /* Ensure smooth scrolling */
    -webkit-overflow-scrolling: touch;
    /* Remove transforms that can break positioning */
}

/* Grid items - clean positioning */
.grid-stack-item {
    /* Add spacing between cards */
    margin: 6px;
    /* Clean positioning without transforms */
    position: absolute;
    z-index: 2;
    /* Ensure borders stay visible during scroll */
    contain: layout style;
}

/* Widget content - fixed scroll issues with ultra-subtle borders */
.grid-stack-item-content {
    background-color: hsl(var(--background));
    border-radius: 16px;
    /* Ultra-subtle border - barely visible */
    border: 1px solid hsl(var(--border) / 0.15);
    /* Much softer, more elegant shadow */
    box-shadow:
        0 1px 2px 0 rgb(0 0 0 / 0.03),
        0 1px 3px 0 rgb(0 0 0 / 0.02);

    /* Critical: Remove overflow hidden and use auto */
    overflow: visible;

    /* Clean positioning */
    position: relative;
    width: calc(100% - 12px); /* Account for margin */
    height: calc(100% - 12px); /* Account for margin */

    /* Layout */
    display: flex;
    flex-direction: column;

    /* Ensure borders are preserved */
    border-style: solid;
    border-width: 1px;

    /* Smooth transitions for interactions */
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Subtle hover effect with enhanced border visibility */
.grid-stack-item-content:hover {
    border-color: hsl(var(--border) / 0.3);
    box-shadow:
        0 2px 4px 0 rgb(0 0 0 / 0.04),
        0 1px 2px 0 rgb(0 0 0 / 0.02);
    /* Very subtle lift effect */
    transform: translateY(-1px);
}

/* Card content should handle its own overflow with custom scrollbar */
.grid-stack-item-content > * {
    overflow: auto;
    flex: 1;
}

/* Custom scrollbar for card content - más específico */
.grid-stack-item-content .card-content::-webkit-scrollbar,
.grid-stack-item-content .widget-container::-webkit-scrollbar,
.card-content::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.grid-stack-item-content .card-content::-webkit-scrollbar-track,
.grid-stack-item-content .widget-container::-webkit-scrollbar-track,
.card-content::-webkit-scrollbar-track {
    background: hsl(var(--muted) / 0.1);
    border-radius: 3px;
}

.grid-stack-item-content .card-content::-webkit-scrollbar-thumb,
.grid-stack-item-content .widget-container::-webkit-scrollbar-thumb,
.card-content::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.3);
    border-radius: 3px;
    transition: background-color 0.2s ease;
}

.grid-stack-item-content .card-content::-webkit-scrollbar-thumb:hover,
.grid-stack-item-content .widget-container::-webkit-scrollbar-thumb:hover,
.card-content::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.5);
}

/* Para tablas dentro de widgets */
.grid-stack-item-content .table-container::-webkit-scrollbar,
.table-container::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.grid-stack-item-content .table-container::-webkit-scrollbar-track,
.table-container::-webkit-scrollbar-track {
    background: hsl(var(--muted) / 0.2);
    border-radius: 4px;
}

.grid-stack-item-content .table-container::-webkit-scrollbar-thumb,
.table-container::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.4);
    border-radius: 4px;
}

.grid-stack-item-content .table-container::-webkit-scrollbar-thumb:hover,
.table-container::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.6);
}

/* Removing animation */
.grid-stack-item-removing {
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

/* Placeholder styling with subtle appearance */
.grid-stack-placeholder > .placeholder-content {
    background-color: hsl(var(--primary) / 0.1);
    border: 2px dashed hsl(var(--primary) / 0.3);
    border-radius: 12px;
    opacity: 0.7;
    margin: 6px;
}

/* Dragging effects - careful with transforms */
.grid-stack-item.ui-draggable-dragging {
    z-index: 999;
    /* Enhanced shadow during drag */
    box-shadow:
        0 20px 40px -4px rgb(0 0 0 / 0.15),
        0 10px 20px -2px rgb(0 0 0 / 0.1);
    /* Only scale, no rotation that can break borders */
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

.grid-stack-item.ui-draggable-dragging .grid-stack-item-content {
    border-color: hsl(var(--primary) / 0.6);
    box-shadow:
        0 0 0 2px hsl(var(--primary) / 0.2),
        0 20px 40px -4px rgb(0 0 0 / 0.15);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .grid-stack-item {
        margin: 4px;
    }

    .grid-stack-item-content {
        border-radius: 8px;
        width: calc(100% - 8px);
        height: calc(100% - 8px);
    }

    .grid-stack-placeholder > .placeholder-content {
        margin: 4px;
        border-radius: 8px;
    }

    /* Smaller scrollbars on mobile */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
}

/* Ensure widget containers handle overflow properly */
.widget-container {
    height: 100%;
    overflow: auto;
    display: flex;
    flex-direction: column;
}

/* Fix for card headers and content with subtle styling */
.grid-stack-item-content .card {
    height: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: none; /* Remove duplicate border */
    box-shadow: none; /* Remove duplicate shadow */
    background: transparent;
}

.grid-stack-item-content .card-content {
    flex: 1;
    overflow: auto;
    min-height: 0; /* Important for flex overflow */
}

/* Table scrollbar customization */
.grid-stack-item-content .table-container {
    overflow: auto;
    scrollbar-width: thin;
}

/* Smooth scrolling for all scrollable elements */
.grid-stack-item-content * {
    scroll-behavior: smooth;
}

/* Focus states for accessibility */
.grid-stack-item-content:focus-within {
    border-color: hsl(var(--primary) / 0.4);
    outline: none;
}
</style>

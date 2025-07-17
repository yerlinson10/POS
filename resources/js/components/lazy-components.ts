// lazy-components.ts - Componentes lazy loading
import { defineAsyncComponent } from 'vue'

// Componente de loading simple
const LoadingComponent = {
    template: `
        <div class="flex items-center justify-center p-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            <span class="ml-2 text-sm text-muted-foreground">Loading...</span>
        </div>
    `
}

// Componente de error simple
const ErrorComponent = {
    template: `
        <div class="flex items-center justify-center p-4 text-destructive">
            <span class="text-sm">Failed to load component</span>
        </div>
    `
}

// Lazy loading de componentes pesados
export const ProductSelectionModal = defineAsyncComponent({
    loader: () => import('../pages/POS/components/ProductSelectionModal.vue'),
    loadingComponent: LoadingComponent,
    errorComponent: ErrorComponent,
    delay: 200,
    timeout: 3000
})

export const CustomerSelector = defineAsyncComponent({
    loader: () => import('../pages/POS/components/CustomerSelector.vue'),
    loadingComponent: LoadingComponent,
    delay: 100
})

export const DiscountDialog = defineAsyncComponent({
    loader: () => import('../pages/POS/components/DiscountDialog.vue'),
    loadingComponent: LoadingComponent,
    delay: 100
})

export const SaleSuccessDialog = defineAsyncComponent({
    loader: () => import('../pages/POS/components/SaleSuccessDialog.vue'),
    loadingComponent: LoadingComponent,
    delay: 100
})

// Widget components con lazy loading
export const ChartWidget = defineAsyncComponent({
    loader: () => import('../pages/components/widgets/ChartWidget.vue'),
    loadingComponent: LoadingComponent,
    delay: 200
})

export const TableWidget = defineAsyncComponent({
    loader: () => import('../pages/components/widgets/TableWidget.vue'),
    loadingComponent: LoadingComponent,
    delay: 200
})

export const StatsWidget = defineAsyncComponent({
    loader: () => import('../pages/components/widgets/StatsWidget.vue'),
    loadingComponent: LoadingComponent,
    delay: 200
})

// Componentes de configuración
export const WidgetSettings = defineAsyncComponent({
    loader: () => import('../pages/components/WidgetSettings.vue'),
    loadingComponent: LoadingComponent,
    delay: 300
})

export const AdvancedFilters = defineAsyncComponent({
    loader: () => import('../pages/components/AdvancedFilters.vue'),
    loadingComponent: LoadingComponent,
    delay: 300
})

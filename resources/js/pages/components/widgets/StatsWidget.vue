<script setup lang="ts">
import { computed } from 'vue';
import { TrendingUp, Users, ShoppingCart, DollarSign } from 'lucide-vue-next';
import type { Widget } from '../../types/dashboard';

interface Props {
    widget: Widget;
    formatCurrency: (amount: number) => string;
}

const props = defineProps<Props>();

const statsData = computed(() => {
    if (!props.widget.data) {
        return [];
    }

    const data = props.widget.data;

    switch (props.widget.type) {
        case 'sales_stats':
            return [
                {
                    title: 'Total Ventas',
                    value: props.formatCurrency(data.total_sales || 0),
                    description: `${data.total_count || 0} transacciones`,
                    icon: DollarSign,
                    change: null
                },
                {
                    title: 'Venta Promedio',
                    value: props.formatCurrency(data.average_sale || 0),
                    description: 'Por transacción',
                    icon: TrendingUp,
                    change: null
                },
                {
                    title: 'Ventas en Efectivo',
                    value: props.formatCurrency(data.cash_sales || 0),
                    description: 'Efectivo',
                    icon: DollarSign,
                    change: null
                },
                {
                    title: 'Ventas con Tarjeta',
                    value: props.formatCurrency(data.card_sales || 0),
                    description: 'Tarjeta',
                    icon: ShoppingCart,
                    change: null
                }
            ];

        case 'customer_stats':
            return [
                {
                    title: 'Total Clientes',
                    value: data.total_customers || 0,
                    description: 'Clientes registrados',
                    icon: Users,
                    change: null
                },
                {
                    title: 'Nuevos Clientes',
                    value: data.new_customers || 0,
                    description: 'En el período',
                    icon: TrendingUp,
                    change: null
                },
                {
                    title: 'Clientes Activos',
                    value: data.customers_with_purchases || 0,
                    description: 'Con compras',
                    icon: ShoppingCart,
                    change: null
                }
            ];

        default:
            return [];
    }
});

const topCustomers = computed(() => {
    if (props.widget.type === 'customer_stats' && props.widget.data?.top_customers) {
        return props.widget.data.top_customers;
    }
    return [];
});

const hasData = computed(() => {
    return statsData.value.length > 0;
});
</script>

<template>
    <div class="h-full overflow-auto">
        <div v-if="!hasData" class="flex items-center justify-center h-full">
            <p class="text-muted-foreground">No hay datos disponibles</p>
        </div>

        <div v-else class="space-y-4">
            <!-- Métricas principales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div
                    v-for="stat in statsData"
                    :key="stat.title"
                    class="p-3 border rounded-lg bg-muted/30"
                >
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs text-muted-foreground">{{ stat.title }}</p>
                            <p class="text-sm font-bold">{{ stat.value }}</p>
                            <p class="text-xs text-muted-foreground">{{ stat.description }}</p>
                        </div>
                        <component :is="stat.icon" class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </div>

            <!-- Top clientes para customer_stats -->
            <div v-if="widget.type === 'customer_stats' && topCustomers.length > 0" class="space-y-2">
                <h4 class="text-xs font-semibold">Top Clientes</h4>
                <div class="space-y-2">
                    <div
                        v-for="customer in topCustomers.slice(0, 3)"
                        :key="customer.id"
                        class="flex items-center justify-between p-2 bg-muted/30 rounded text-xs"
                    >
                        <div>
                            <p class="font-medium">{{ customer.name }}</p>
                            <p class="text-muted-foreground">{{ customer.purchase_count }} compras</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">{{ formatCurrency(customer.total_spent) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

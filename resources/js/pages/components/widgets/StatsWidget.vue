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
                    title: 'Total Sales',
                    value: props.formatCurrency(data.total_sales || 0),
                    description: `${data.total_count || 0} transactions`,
                    icon: DollarSign,
                    change: null
                },
                {
                    title: 'Average Sale',
                    value: props.formatCurrency(data.average_sale || 0),
                    description: 'Per transaction',
                    icon: TrendingUp,
                    change: null
                },
                {
                    title: 'Cash Sales',
                    value: props.formatCurrency(data.cash_sales || 0),
                    description: 'Cash',
                    icon: DollarSign,
                    change: null
                },
                {
                    title: 'Card Sales',
                    value: props.formatCurrency(data.card_sales || 0),
                    description: 'Card',
                    icon: ShoppingCart,
                    change: null
                }
            ];

        case 'customer_stats':
            return [
                {
                    title: 'Total Customers',
                    value: data.total_customers || 0,
                    description: 'Registered customers',
                    icon: Users,
                    change: null
                },
                {
                    title: 'New Customers',
                    value: data.new_customers || 0,
                    description: 'In the period',
                    icon: TrendingUp,
                    change: null
                },
                {
                    title: 'Active Customers',
                    value: data.customers_with_purchases || 0,
                    description: 'With purchases',
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
            <p class="text-muted-foreground">No data available</p>
        </div>

        <div v-else class="space-y-4">
            <!-- Main metrics -->
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

            <!-- Top customers for customer_stats -->
            <div v-if="widget.type === 'customer_stats' && topCustomers.length > 0" class="space-y-2">
                <h4 class="text-xs font-semibold">Top Customers</h4>
                <div class="space-y-2">
                    <div
                        v-for="customer in topCustomers.slice(0, 3)"
                        :key="customer.id"
                        class="flex items-center justify-between p-2 bg-muted/30 rounded text-xs"
                    >
                        <div>
                            <p class="font-medium">
                                {{ customer.name || `${customer.first_name || ''} ${customer.last_name || ''}`.trim() }}
                            </p>
                            <p class="text-muted-foreground">{{ customer.purchase_count || customer.order_count }} purchases</p>
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

<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import type { Widget } from '../../types/dashboard';

interface Props {
    widget: Widget;
    formatCurrency: (amount: number) => string;
}

const props = defineProps<Props>();

const tableData = computed(() => {
    if (!props.widget.data || !Array.isArray(props.widget.data)) {
        return {
            headers: [],
            rows: []
        };
    }

    const data = props.widget.data;

    switch (props.widget.type) {
        case 'top_products':
            return {
                headers: ['Producto', 'SKU', 'Vendidos', 'Ingresos'],
                rows: data.map((item: any) => ({
                    name: item.name,
                    sku: item.sku,
                    total_sold: item.total_sold,
                    total_revenue: props.formatCurrency(item.total_revenue)
                }))
            };

        case 'low_stock':
            return {
                headers: ['Producto', 'SKU', 'Stock', 'Categoría'],
                rows: data.map((item: any) => ({
                    name: item.name,
                    sku: item.sku,
                    stock: item.stock,
                    category: item.category
                }))
            };

        case 'recent_sales':
            return {
                headers: ['Cliente', 'Total', 'Método', 'Fecha', 'Usuario'],
                rows: data.map((item: any) => ({
                    customer_name: item.customer_name,
                    total_amount: props.formatCurrency(item.total_amount),
                    payment_method: item.payment_method,
                    date: new Date(item.date).toLocaleDateString('es-CO'),
                    user_name: item.user_name
                }))
            };

        default:
            return {
                headers: [],
                rows: []
            };
    }
});

const getStockBadgeVariant = (stock: number) => {
    if (stock <= 5) return 'destructive';
    if (stock <= 10) return 'secondary';
    return 'default';
};

const getPaymentMethodBadge = (method: string) => {
    return method === 'cash' ? 'secondary' : 'default';
};

const getPaymentMethodText = (method: string) => {
    return method === 'cash' ? 'Efectivo' : 'Tarjeta';
};

const hasData = computed(() => {
    return tableData.value.rows.length > 0;
});
</script>

<template>
    <div class="h-full overflow-auto">
        <div v-if="!hasData" class="flex items-center justify-center h-full">
            <p class="text-muted-foreground">No hay datos disponibles</p>
        </div>

        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead
                        v-for="header in tableData.headers"
                        :key="header"
                        class="text-xs"
                    >
                        {{ header }}
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="(row, index) in tableData.rows" :key="index">
                    <template v-if="widget.type === 'top_products'">
                        <TableCell class="font-medium text-xs">{{ row.name }}</TableCell>
                        <TableCell class="text-xs text-muted-foreground">{{ row.sku }}</TableCell>
                        <TableCell class="text-xs">{{ row.total_sold }}</TableCell>
                        <TableCell class="text-xs font-medium">{{ row.total_revenue }}</TableCell>
                    </template>

                    <template v-else-if="widget.type === 'low_stock'">
                        <TableCell class="font-medium text-xs">{{ row.name }}</TableCell>
                        <TableCell class="text-xs text-muted-foreground">{{ row.sku }}</TableCell>
                        <TableCell class="text-xs">
                            <Badge :variant="getStockBadgeVariant(row.stock)">
                                {{ row.stock }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-xs">{{ row.category ?? '' }}</TableCell>
                    </template>

                    <template v-else-if="widget.type === 'recent_sales'">
                        <TableCell class="font-medium text-xs">{{ row.customer_name }}</TableCell>
                        <TableCell class="text-xs font-medium">{{ row.total_amount }}</TableCell>
                        <TableCell class="text-xs">
                            <Badge :variant="getPaymentMethodBadge(row.payment_method)">
                                {{ getPaymentMethodText(row.payment_method) }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-xs text-muted-foreground">{{ row.date }}</TableCell>
                        <TableCell class="text-xs">{{ row.user_name }}</TableCell>
                    </template>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>

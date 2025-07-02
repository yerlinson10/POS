<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    ShoppingCartIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    ChartBarIcon,
    PlusIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

interface DashboardStats {
    today_sales: number;
    month_sales: number;
    pending_invoices: number;
    total_invoices: number;
}

interface RecentInvoice {
    id: number;
    invoice_number: string;
    customer_name: string;
    total_amount: number;
    status: string;
    date: string;
}

interface Product {
    id: number;
    name: string;
    stock?: number;
    price: number;
}

interface SalesData {
    date: string;
    sales: number;
}

interface Props {
    stats: DashboardStats;
    recentInvoices: RecentInvoice[];
    lowStockProducts: Product[];
    outOfStockProducts: Product[];
    salesChartData: SalesData[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'paid': return 'text-green-600 bg-green-100';
        case 'pending': return 'text-yellow-600 bg-yellow-100';
        case 'cancelled': return 'text-red-600 bg-red-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'paid': return 'Pagada';
        case 'pending': return 'Pendiente';
        case 'cancelled': return 'Cancelada';
        default: return status;
    }
};

const maxSales = computed(() => {
    return Math.max(...props.salesChartData.map(d => d.sales));
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <!-- Today Sales -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <CurrencyDollarIcon class="h-6 w-6 text-green-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Ventas Hoy
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(stats.today_sales) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Month Sales -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ChartBarIcon class="h-6 w-6 text-blue-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Ventas del Mes
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(stats.month_sales) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Invoices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ClockIcon class="h-6 w-6 text-yellow-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Facturas Pendientes
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ stats.pending_invoices }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Invoices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <DocumentTextIcon class="h-6 w-6 text-purple-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Total Facturas
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ stats.total_invoices }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sales Chart -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                            Ventas Últimos 7 Días
                        </h3>
                        <div class="h-64">
                            <div class="flex items-end justify-between h-full space-x-2">
                                <div
                                    v-for="data in salesChartData"
                                    :key="data.date"
                                    class="flex flex-col items-center flex-1"
                                >
                                    <div
                                        class="bg-blue-500 w-full rounded-t"
                                        :style="{ height: maxSales > 0 ? `${(data.sales / maxSales) * 100}%` : '2px' }"
                                        :title="formatCurrency(data.sales)"
                                    ></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        {{ new Date(data.date).toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric' }) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                            Acciones Rápidas
                        </h3>
                        <div class="space-y-3">
                            <Link
                                href="/pos"
                                class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <ShoppingCartIcon class="h-4 w-4 mr-2" />
                                Nueva Venta
                            </Link>
                            <Link
                                href="/invoices/create"
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <DocumentTextIcon class="h-4 w-4 mr-2" />
                                Nueva Factura
                            </Link>
                            <Link
                                href="/products/create"
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <PlusIcon class="h-4 w-4 mr-2" />
                                Nuevo Producto
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Recent Invoices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Facturas Recientes
                            </h3>
                            <Link
                                href="/invoices"
                                class="text-sm text-indigo-600 hover:text-indigo-500"
                            >
                                Ver todas
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="invoice in recentInvoices"
                                :key="invoice.id"
                                class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ invoice.invoice_number }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                        {{ invoice.customer_name }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span :class="getStatusColor(invoice.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ getStatusText(invoice.status) }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(invoice.total_amount) }}
                                    </span>
                                    <Link
                                        :href="`/invoices/${invoice.id}`"
                                        class="text-indigo-600 hover:text-indigo-500"
                                    >
                                        <EyeIcon class="h-4 w-4" />
                                    </Link>
                                </div>
                            </div>
                            <div v-if="recentInvoices.length === 0" class="text-center py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay facturas recientes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Stock Bajo
                            </h3>
                            <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" />
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="product in lowStockProducts"
                                :key="product.id"
                                class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ product.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(product.price) }}
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ product.stock }} unidades
                                    </span>
                                </div>
                            </div>
                            <div v-if="lowStockProducts.length === 0" class="text-center py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay productos con stock bajo</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Alert -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Sin Stock
                            </h3>
                            <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="product in outOfStockProducts"
                                :key="product.id"
                                class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ product.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(product.price) }}
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Sin stock
                                    </span>
                                </div>
                            </div>
                            <div v-if="outOfStockProducts.length === 0" class="text-center py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay productos sin stock</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

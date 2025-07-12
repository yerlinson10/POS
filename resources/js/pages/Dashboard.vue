<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCard from './PosSession/components/StatsCard.vue';
import DashboardSalesChart from '@/components/DashboardSalesChart.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { AlertTriangle, Package, TrendingUp, ShoppingCart } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js'
import Icon from '@/components/Icon.vue'

interface Props {
    stats: {
        total_customers: number;
        total_products: number;
        total_categories: number;
        low_stock_count: number;
    };
    todaySales: {
        count: number;
        total: number;
        cash_sales: number;
        card_sales: number;
    };
    monthlySales: {
        count: number;
        total: number;
        average_per_day: number;
    };
    lowStockProducts: Array<{
        id: number;
        name: string;
        sku: string;
        stock: number;
        category: string;
        unit: string;
    }>;
    recentSales: Array<{
        id: number;
        customer_name: string;
        total_amount: number;
        payment_method: string;
        date: string;
        user_name: string;
    }>;
    salesChart: Array<{
        date: string;
        day: string;
        sales: number;
    }>;
    topProducts: Array<{
        id: number;
        name: string;
        sku: string;
        total_sold: number;
        total_revenue: number;
    }>;
    activeSession: any;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getPaymentMethodBadge = (method: string) => {
    return method === 'cash' ? 'secondary' : 'default';
};

const getPaymentMethodText = (method: string) => {
    return method === 'cash' ? 'Efectivo' : 'Tarjeta';
};
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 w-full  mx-auto">

            <!-- Dashboard Options -->
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Dashboard Estático</h2>
                <Link :href="route('dynamic-dashboard.dynamic')" class="inline-flex items-center">
                    <Button variant="outline">
                        <Icon name="grid-3x3" class="h-4 w-4 mr-2" />
                        Dashboard Dinámico
                    </Button>
                </Link>
            </div>

            <!-- Active POS Session Alert -->
            <div v-if="!activeSession"
                class="rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-800 dark:bg-orange-950">
                <div class="flex items-center gap-2">
                    <AlertTriangle class="h-5 w-5 text-orange-600" />
                    <p class="text-sm text-orange-800 dark:text-orange-200">
                        You do not have an active POS session.
                        <Link :href="route('sessions.create')" class="font-medium underline">Start session</Link>
                    </p>
                </div>
            </div>

            <!-- Main statistics -->
            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <StatsCard title="Today's Sales" :value="formatCurrency(todaySales.total)" icon="DollarSign"
                    color="green" />
                <StatsCard title="Monthly Sales" :value="formatCurrency(monthlySales.total)" icon="TrendingUp"
                    color="blue" />
                <StatsCard title="Total Products" :value="stats.total_products.toString()" icon="Package"
                    color="purple" />
                <StatsCard title="Total Customers" :value="stats.total_customers.toString()" icon="Users"
                    color="emerald" />
            </div>

            <!-- Charts and statistics row -->
            <div class="grid gap-4 grid-cols-1 md:grid-cols-3">
                <!-- Sales chart -->
                <div class="md:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Sales last 7 days</CardTitle>
                            <CardDescription>Daily sales trend</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <DashboardSalesChart :data="salesChart" />
                        </CardContent>
                    </Card>
                </div>

                <!-- Today's statistics -->
                <Card>
                    <CardHeader>
                        <CardTitle>Today's Summary</CardTitle>
                        <CardDescription>Daily sales breakdown</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Number of sales</span>
                            <span class="font-medium">{{ todaySales.count }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Cash sales</span>
                            <span class="font-medium">{{ formatCurrency(todaySales.cash_sales) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Card sales</span>
                            <span class="font-medium">{{ formatCurrency(todaySales.card_sales) }}</span>
                        </div>
                        <div class="pt-2 border-t">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Total of the day</span>
                                <span class="font-bold text-green-600">{{ formatCurrency(todaySales.total) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Bottom row with tables -->
            <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                <!-- Low stock products -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <AlertTriangle class="h-5 w-5 text-orange-500" />
                                Low Stock Products
                            </CardTitle>
                            <CardDescription>Products with 10 units or less</CardDescription>
                        </div>
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/products">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="lowStockProducts.length === 0" class="text-center py-4 text-muted-foreground">
                            <Package class="h-8 w-8 mx-auto mb-2 opacity-50" />
                            <p>No low stock products</p>
                        </div>
                        <Table v-else>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product</TableHead>
                                    <TableHead>Stock</TableHead>
                                    <TableHead>Unit</TableHead>
                                    <TableHead>Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="product in lowStockProducts" :key="product.id">
                                    <TableCell>
                                        <div>
                                            <p class="font-medium">{{ product.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ product.sku }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="product.stock <= 5 ? 'destructive' : 'secondary'">
                                            {{ product.stock }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ product.unit }}</TableCell>
                                    <TableCell>
                                        <Link v-if="product.id" :href="`/products/${product.id}/edit`" prefetch>
                                        <Button variant="outline" size="sm" class="h-7 px-2 text-xs cursor-pointer">
                                            <Icon name="ExternalLink" class="w-3 h-3 mr-1" />
                                            View
                                        </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Latest sales -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <ShoppingCart class="h-5 w-5 text-blue-500" />
                                Latest Sales
                            </CardTitle>
                            <CardDescription>Most recent sales</CardDescription>
                        </div>
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/invoices">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentSales.length === 0" class="text-center py-4 text-muted-foreground">
                            <ShoppingCart class="h-8 w-8 mx-auto mb-2 opacity-50" />
                            <p>No recent sales</p>
                        </div>
                        <Table v-else>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Method</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="sale in recentSales" :key="sale.id">
                                    <TableCell>
                                        <div>
                                            <p class="font-medium">{{ sale.customer_name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ sale.date }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="font-medium">{{ formatCurrency(sale.total_amount) }}</span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getPaymentMethodBadge(sale.payment_method)">
                                            {{ getPaymentMethodText(sale.payment_method) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Link v-if="sale.id" :href="`/invoices/${sale.id}/edit`" prefetch>
                                        <Button variant="outline" size="sm" class="h-7 px-2 text-xs cursor-pointer">
                                            <Icon name="ExternalLink" class="w-3 h-3 mr-1" />
                                            View
                                        </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <!-- Top products -->
            <Card class="w-full overflow-x-auto">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5 text-green-500" />
                        Best Selling Products (This month)
                    </CardTitle>
                    <CardDescription>Products with the highest sales volume</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="topProducts.length === 0" class="text-center py-4 text-muted-foreground">
                        <Package class="h-8 w-8 mx-auto mb-2 opacity-50" />
                        <p>No data for products sold this month</p>
                    </div>
                    <Table v-else>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Product</TableHead>
                                <TableHead>Quantity Sold</TableHead>
                                <TableHead>Revenue Generated</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="product in topProducts" :key="product.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ product.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ product.sku }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ product.total_sold }} units</Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="font-medium text-green-600">{{ formatCurrency(product.total_revenue)
                                    }}</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

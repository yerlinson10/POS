<template>
    <AppLayout title="Current POS Session">
        <div class="w-full mx-auto p-6 space-y-6">
            <!-- Header with session information -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-zinc-100">Active POS Session</h1>
                        <p class="text-gray-600 dark:text-zinc-400 mt-2">
                            Cashier: <strong>{{ session.user_name }}</strong> |
                            Started: <strong>{{ formatDateTime(session.opened_at) }}</strong>
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <Button @click="goToPOS" variant="default" class="cursor-pointer">
                            <ShoppingCart class="h-4 w-4 mr-2" />
                            Go to POS
                        </Button>
                        <Button @click="closeSession" variant="destructive" class="cursor-pointer">
                            <X class="h-4 w-4 mr-2" />
                            Close Session
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <StatsCard title="Total Sales" :value="formatCurrency(summary.total_sales)" icon="DollarSign"
                    color="green" />
                <StatsCard title="Sales" :value="summary.sales_count.toString()" icon="Receipt" color="blue" />
                <StatsCard title="Cash Sales" :value="formatCurrency(summary.cash_sales)" icon="Banknote"
                    color="emerald" />
                <StatsCard title="Card Sales" :value="formatCurrency(summary.card_sales)" icon="CreditCard"
                    color="purple" />
            </div>

            <!-- Cash information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Cash summary -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-zinc-100 mb-4">Cash Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-zinc-400">Initial cash:</span>
                            <span class="font-medium">${{ formatCurrency(session.initial_cash) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-zinc-400">Cash sales:</span>
                            <span class="font-medium text-green-600 dark:text-green-400">+${{
                                formatCurrency(summary.cash_sales) }}</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-zinc-700 pt-3 flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-zinc-100">Expected cash:</span>
                            <span class="font-bold text-lg">${{ formatCurrency(summary.expected_cash) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment methods -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-zinc-100 mb-4">Payment Methods</h2>
                    <div class="space-y-3">
                        <div v-for="(method, key) in summary.payment_methods_breakdown" :key="key"
                            class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <PaymentMethodIcon :method="key" />
                                <span class="capitalize dark:text-zinc-200">{{ getPaymentMethodName(key) }}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">${{ formatCurrency(method.total) }}</div>
                                <div class="text-sm text-gray-500 dark:text-zinc-400">{{ method.count }} sales</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales by hour chart -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-zinc-100 mb-4">Sales by Hour</h2>
                <SalesChart :data="summary.invoices_by_hour" />
            </div>

            <!-- Opening notes -->
            <div v-if="session.opening_notes" class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-zinc-100 mb-4">Opening Notes</h2>
                <p class="text-gray-700 dark:text-zinc-200 bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">{{
                    session.opening_notes }}</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '../../layouts/AppLayout.vue'
import { Button } from '../../components/ui/button'
// Make sure the file exists at this path, or update the import path if needed
import StatsCard from '@/pages/PosSession/components/StatsCard.vue'
import PaymentMethodIcon from '@/pages/PosSession/components/PaymentMethodIcon.vue'
import SalesChart from '@/pages/PosSession/components/SalesChart.vue'
import { ShoppingCart, X } from 'lucide-vue-next'
import { formatCurrency, formatDateTime } from '@/utils/format'
import type { PosSession, PosSessionSummary } from '../../types/pos'

interface Props {
    session: PosSession
    summary: PosSessionSummary
}

const props = defineProps<Props>()

// Methods
const getPaymentMethodName = (method: string) => {
    const names: Record<string, string> = {
        cash: 'Efectivo',
        card: 'Tarjeta',
        transfer: 'Transferencia',
        other: 'Otro'
    }
    return names[method] || method
}

const goToPOS = () => {
    router.visit(route('pos.index'))
}

const closeSession = () => {
    router.visit(route('sessions.edit', props.session.id))
}
</script>

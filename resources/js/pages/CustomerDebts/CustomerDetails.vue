<template>
    <AppLayout title="Customer Details">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Customer Header -->
                <div class="mb-6">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">{{ customer.full_name }}</h2>
                                    <p class="text-sm text-gray-600">Customer since {{ formatDate(customer.created_at) }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold" :class="{
                                        'text-green-600': totalDebt <= 0,
                                        'text-red-600': totalDebt > 0
                                    }">
                                        {{ totalDebt > 0 ? '-' : '' }}RD${{ formatCurrency(Math.abs(totalDebt)) }}
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ totalDebt > 0 ? 'Total Outstanding Debt' : 'No Outstanding Debt' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ customer.invoices?.length || 0 }}</div>
                                    <div class="text-sm text-blue-700">Total Invoices</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ customer.payments?.length || 0 }}</div>
                                    <div class="text-sm text-green-700">Total Payments</div>
                                </div>
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-red-600">{{ customer.customer_debts?.length || 0 }}</div>
                                    <div class="text-sm text-red-700">Active Debts</div>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600">RD${{ formatCurrency(totalPaid) }}</div>
                                    <div class="text-sm text-purple-700">Total Paid</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white shadow rounded-lg">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm'
                                ]"
                            >
                                {{ tab.name }}
                                <span v-if="tab.count !== undefined" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="[
                                    activeTab === tab.id ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                                ]">
                                    {{ tab.count }}
                                </span>
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Invoices Tab -->
                        <div v-if="activeTab === 'invoices'">
                            <div v-if="customer.invoices && customer.invoices.length > 0" class="space-y-4">
                                <div v-for="invoice in customer.invoices" :key="invoice.id"
                                     class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-medium text-gray-900">Invoice #{{ invoice.invoice_number }}</h3>
                                            <p class="text-sm text-gray-600">{{ formatDate(invoice.created_at) }}</p>
                                            <div v-if="invoice.customer_debts && invoice.customer_debts.length > 0" class="mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Has outstanding debt
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-gray-900">RD${{ formatCurrency(invoice.total_amount) }}</div>
                                            <div class="text-sm text-gray-600">{{ invoice.status }}</div>
                                        </div>
                                    </div>

                                    <!-- Show associated debts -->
                                    <div v-if="invoice.customer_debts && invoice.customer_debts.length > 0" class="mt-3 pl-4 border-l-2 border-red-200">
                                        <h4 class="text-sm font-medium text-red-800 mb-2">Associated Debts:</h4>
                                        <div v-for="debt in invoice.customer_debts" :key="debt.id" class="text-sm text-red-700 mb-1">
                                            • RD${{ formatCurrency(debt.remaining_amount) }} - {{ debt.status }}
                                            <span v-if="debt.due_date" class="text-red-600">(Due: {{ formatDate(debt.due_date) }})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                No invoices found for this customer.
                            </div>
                        </div>

                        <!-- Debts Tab -->
                        <div v-if="activeTab === 'debts'">
                            <div v-if="customer.customer_debts && customer.customer_debts.length > 0" class="space-y-4">
                                <div v-for="debt in customer.customer_debts" :key="debt.id"
                                     class="border rounded-lg p-4" :class="{
                                         'border-red-200 bg-red-50': debt.status === 'pending',
                                         'border-green-200 bg-green-50': debt.status === 'paid'
                                     }">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-medium text-gray-900">Debt #{{ debt.id }}</h3>
                                            <p class="text-sm text-gray-600">Created: {{ formatDate(debt.created_at) }}</p>
                                            <p v-if="debt.due_date" class="text-sm text-gray-600">Due: {{ formatDate(debt.due_date) }}</p>
                                            <p v-if="debt.description" class="text-sm text-gray-700 mt-1">{{ debt.description }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold" :class="{
                                                'text-red-600': debt.status === 'pending',
                                                'text-green-600': debt.status === 'paid'
                                            }">
                                                RD${{ formatCurrency(debt.remaining_amount) }}
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                                                'bg-red-100 text-red-800': debt.status === 'pending',
                                                'bg-green-100 text-green-800': debt.status === 'paid'
                                            }">
                                                {{ debt.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Show associated invoices -->
                                    <div v-if="debt.invoices && debt.invoices.length > 0" class="mt-3 pl-4 border-l-2 border-blue-200">
                                        <h4 class="text-sm font-medium text-blue-800 mb-2">Related Invoices:</h4>
                                        <div v-for="invoice in debt.invoices" :key="invoice.id" class="text-sm text-blue-700 mb-1">
                                            • Invoice #{{ invoice.invoice_number }} - RD${{ formatCurrency(invoice.total_amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                No debts found for this customer.
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div v-if="activeTab === 'payments'">
                            <div v-if="customer.payments && customer.payments.length > 0" class="space-y-4">
                                <div v-for="payment in customer.payments" :key="payment.id"
                                     class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-medium text-gray-900">Payment #{{ payment.id }}</h3>
                                            <p class="text-sm text-gray-600">{{ formatDate(payment.payment_date) }}</p>
                                            <p class="text-sm text-gray-700">Method: {{ payment.type }}</p>
                                            <p v-if="payment.reference_number" class="text-sm text-gray-600">
                                                Ref: {{ payment.reference_number }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-green-600">RD${{ formatCurrency(payment.amount) }}</div>
                                            <div class="text-sm text-gray-600">{{ payment.status }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                No payments found for this customer.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'

interface Customer {
    id: number
    full_name: string
    created_at: string
    invoices?: Array<{
        id: number
        invoice_number: string
        total_amount: number
        status: string
        created_at: string
        customer_debts?: Array<{
            id: number
            remaining_amount: number
            status: string
            due_date?: string
        }>
    }>
    customer_debts?: Array<{
        id: number
        remaining_amount: number
        status: string
        created_at: string
        due_date?: string
        description?: string
        invoices?: Array<{
            id: number
            invoice_number: string
            total_amount: number
        }>
    }>
    payments?: Array<{
        id: number
        amount: number
        type: string
        status: string
        payment_date: string
        reference_number?: string
    }>
}

const props = defineProps<{
    customer: Customer
}>()

const activeTab = ref('invoices')

const tabs = computed(() => [
    {
        id: 'invoices',
        name: 'Invoices',
        count: props.customer.invoices?.length || 0
    },
    {
        id: 'debts',
        name: 'Debts',
        count: props.customer.customer_debts?.filter(d => d.status === 'pending').length || 0
    },
    {
        id: 'payments',
        name: 'Payments',
        count: props.customer.payments?.length || 0
    }
])

const totalDebt = computed(() => {
    return props.customer.customer_debts?.reduce((sum, debt) => {
        return debt.status === 'pending' ? sum + debt.remaining_amount : sum
    }, 0) || 0
})

const totalPaid = computed(() => {
    return props.customer.payments?.reduce((sum, payment) => {
        return payment.status === 'completed' ? sum + payment.amount : sum
    }, 0) || 0
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-DO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-DO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>

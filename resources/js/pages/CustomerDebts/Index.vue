<template>
    <Head title="Customer Debts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header con Search -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="CreditCard" class="w-5 h-5 text-primary" />
                                Customer Debts Management
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                Track and manage customer debts and payments
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                                <Icon name="Receipt" class="w-3 h-3 md:w-4 md:h-4" />
                                <span v-if="debts?.total">{{ debts.total }} debts</span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Icon name="Search"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 md:w-4 md:h-4 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search customer debts..."
                                class="pl-7 md:pl-10 h-8 md:h-11 text-sm" @keyup.enter="search" />
                        </div>
                        <Button @click="search" size="sm"
                            class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm cursor-pointer">
                            <Icon name="Search" class="w-3 h-3 md:w-4 md:h-4" />
                        </Button>
                        <Button v-if="hasActiveFilters" @click="resetFilters" variant="outline" size="sm"
                            class="h-8 md:h-11 px-2 md:px-4 text-xs md:text-sm cursor-pointer">
                            <Icon name="RotateCcw" class="w-3 h-3 md:w-4 md:h-4" />
                        </Button>
                    </div>
                </div>

                <!-- Filters Toolbar -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 md:gap-4 p-2 md:p-4 bg-background border-b">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 md:gap-3">
                        <span class="text-xs md:text-sm font-medium text-muted-foreground hidden sm:inline">Sort:</span>
                        <div class="flex gap-2">
                            <Select v-model="filters.sort_by" @update:modelValue="search">
                                <SelectTrigger class="w-full sm:w-36 md:w-48 h-7 md:h-9 text-xs md:text-sm">
                                    <SelectValue placeholder="Sort by..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="created_at">Date</SelectItem>
                                    <SelectItem value="due_date">Due Date</SelectItem>
                                    <SelectItem value="customer.name">Customer</SelectItem>
                                    <SelectItem value="total_amount">Amount</SelectItem>
                                    <SelectItem value="status">Status</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="filters.sort_dir" @update:modelValue="search">
                                <SelectTrigger class="w-full sm:w-24 md:w-32 h-7 md:h-9 text-xs md:text-sm">
                                    <SelectValue placeholder="Order..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="asc">A-Z / Low-High</SelectItem>
                                    <SelectItem value="desc">Z-A / High-Low</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Status and Date Filters -->
                    <div class="flex items-center gap-2">
                        <Select v-model="filters.status" @update:modelValue="search">
                            <SelectTrigger class="w-full sm:w-32 md:w-40 h-7 md:h-9 text-xs md:text-sm">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="partial">Partial</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                                <SelectItem value="overdue">Overdue</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-muted/20 border-b">
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">Total Debts</div>
                        <div class="text-lg font-bold">${{ Number(summary?.total_debt || 0).toFixed(2) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">Overdue</div>
                        <div class="text-lg font-bold text-red-600">${{ Number(summary?.overdue_debt || 0).toFixed(2) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">Pending</div>
                        <div class="text-lg font-bold text-yellow-600">${{ Number(summary?.pending_debt || 0).toFixed(2) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">Paid This Month</div>
                        <div class="text-lg font-bold text-green-600">${{ Number(summary?.paid_this_month || 0).toFixed(2) }}</div>
                    </div>
                </div>

                <!-- Debts Table -->
                <div class="flex-1 overflow-hidden">
                    <div class="h-full overflow-y-auto">
                        <table class="w-full">
                            <thead
                                class="sticky top-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b">
                                <tr class="h-12">
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Customer & Invoice</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Amount</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Due Date</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Status</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="debt in (debts?.data || [])" :key="debt.id"
                                    class="border-b hover:bg-muted/50 transition-colors group">
                                    <!-- Customer & Invoice Info -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="font-medium text-sm group-hover:text-primary transition-colors">
                                                <Link
                                                    :href="route('customers.details', { customer: debt.customer_id })"
                                                    class="hover:text-blue-600 hover:underline cursor-pointer"
                                                >
                                                    {{ debt.customer_name }}
                                                </Link>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <Icon name="FileText" class="w-3 h-3" />
                                                <span>Invoice #{{ debt.invoice_id }}</span>
                                                <span class="text-muted-foreground">•</span>
                                                <span>{{ formatDate(debt.created_at) }}</span>
                                            </div>
                                            <div v-if="debt.description" class="text-xs text-muted-foreground">
                                                {{ debt.description }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex flex-col gap-1">
                                            <div class="font-semibold text-lg">
                                                {{ formatCurrency(debt.original_amount) }}
                                            </div>
                                            <div v-if="debt.paid_amount > 0" class="text-xs text-green-600">
                                                {{ formatCurrency(debt.paid_amount) }} paid ({{ getPaymentPercentage(debt) }}%)
                                            </div>
                                            <div v-if="debt.remaining_amount > 0" class="text-xs font-semibold"
                                                :class="{
                                                    'text-red-600': debt.status === 'overdue',
                                                    'text-yellow-600': debt.status === 'pending' || debt.status === 'partial'
                                                }">
                                                {{ formatCurrency(debt.remaining_amount) }} remaining
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Due Date -->
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-sm" :class="{
                                                'text-red-600 font-semibold': isOverdue(debt.due_date),
                                                'text-yellow-600': isDueSoon(debt.due_date),
                                                'text-muted-foreground': !isOverdue(debt.due_date) && !isDueSoon(debt.due_date)
                                            }">
                                                {{ formatDate(debt.due_date) }}
                                            </div>
                                            <div v-if="isOverdue(debt.due_date)" class="text-xs text-red-600 font-semibold">
                                                {{ getDaysOverdue(debt.due_date) }} days overdue
                                            </div>
                                            <div v-else-if="isDueSoon(debt.due_date)" class="text-xs text-yellow-600">
                                                Due in {{ getDaysUntilDue(debt.due_date) }} days
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': debt.status === 'paid',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': debt.status === 'pending',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400': debt.status === 'partial',
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': debt.status === 'overdue'
                                            }">
                                            {{ getStatusLabel(debt.status) }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <!-- Pay Debt Button -->
                                            <Button v-if="debt.status !== 'paid'"
                                                @click="openPaymentDialog(debt)"
                                                variant="ghost"
                                                class="h-8 w-8 p-0 text-green-600 hover:text-green-700 cursor-pointer">
                                                <Icon name="DollarSign" class="w-4 h-4" />
                                            </Button>

                                            <!-- View Invoice Button -->
                                            <Link :href="`/invoices/${debt.invoice_id}`">
                                                <Button variant="ghost" class="h-8 w-8 p-0 cursor-pointer">
                                                    <Icon name="Eye" class="w-4 h-4" />
                                                </Button>
                                            </Link>

                                            <!-- View Details Button -->
                                            <Button @click="openDetailsDialog(debt)"
                                                variant="ghost"
                                                class="h-8 w-8 p-0 cursor-pointer">
                                                <Icon name="Info" class="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="(debts?.data?.length || 0) > 0"
                    class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                    <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                        <div class="text-xs text-muted-foreground">
                            <span class="font-medium">{{ debts?.from || 0 }}-{{ debts?.to || 0 }}</span>
                            <span class="hidden xs:inline"> of </span>
                            <span class="xs:hidden">/</span>
                            <span class="font-medium">{{ debts?.total || 0 }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Page {{ debts?.current_page || 1 }}/{{ debts?.last_page || 1 }}
                        </div>
                    </div>

                    <Pagination v-slot="{ page: internalPage }" :items-per-page="filters.per_page"
                        :total="debts?.last_page || 1" :page="filters.page" @page-change="onPageChange" class="flex">
                        <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                            <PaginationPrevious @click="onPageChange(internalPage - 1)"
                                :disabled="(debts?.current_page || 1) <= 1" class="h-8 md:h-9" />
                            <template v-for="(item, idx) in pages" :key="idx">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === internalPage" @click="onPageChange(item.value)"
                                    class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                    {{ item.value }}
                                </PaginationItem>
                            </template>
                            <PaginationEllipsis :index="4" v-if="(debts?.last_page || 1) >= 4" class="h-8 md:h-9" />
                            <PaginationNext @click="onPageChange(internalPage + 1)"
                                :disabled="(debts?.current_page || 1) >= (debts?.last_page || 1)"
                                class="h-8 md:h-9" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>

        <!-- Payment Dialog -->
        <PayDebtDialog
            v-model:open="showPaymentDialog"
            :debt="selectedDebt"
            @payment-processed="handlePaymentProcessed"
        />

        <!-- Details Dialog -->
        <DebtDetailsDialog
            v-model:open="showDetailsDialog"
            :debt="selectedDebt"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import Icon from '@/components/Icon.vue'
import PayDebtDialog from './components/PayDebtDialog.vue'
import DebtDetailsDialog from './components/DebtDetailsDialog.vue'
import { type BreadcrumbItem } from '@/types'
import useFlashMessage from '@/composables/useFlashMessages'
import { useCustomerDebtFilters } from '@/composables/useCustomerDebtFilters'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customer Debts', href: '/customer-debts' },
]

interface CustomerDebt {
    id: number
    customer_id: number
    customer_name: string
    invoice_id: number
    original_amount: number
    paid_amount: number
    remaining_amount: number
    debt_date?: string
    due_date?: string
    status: 'pending' | 'partial' | 'paid' | 'overdue'
    days_overdue: number
    user?: string
    description?: string
    created_at: string
}

interface DebtSummary {
    total_debt: number
    overdue_debt: number
    pending_debt: number
    paid_this_month: number
}

const props = defineProps<{
    debts: {
        data: CustomerDebt[]
        total: number
        from: number
        to: number
        current_page: number
        last_page: number
    }
    summary: DebtSummary
    filters: {
        search: string
        sort_by: string
        sort_dir: string
        status: string
        page: number
        per_page: number
    }
}>()

const {
    filters,
    hasActiveFilters,
    resetFilters,
    search,
    onPageChange,
} = useCustomerDebtFilters(props)

// Payment functionality
const showPaymentDialog = ref(false)
const showDetailsDialog = ref(false)
const selectedDebt = ref<CustomerDebt | null>(null)

const openPaymentDialog = (debt: CustomerDebt) => {
    selectedDebt.value = debt
    showPaymentDialog.value = true
}

const openDetailsDialog = (debt: CustomerDebt) => {
    selectedDebt.value = debt
    showDetailsDialog.value = true
}

const handlePaymentProcessed = () => {
    showPaymentDialog.value = false
    selectedDebt.value = null
    search() // Refresh the list
}

// Utility functions
const formatDate = (dateString: string | undefined) => {
    return dateString ? new Date(dateString).toLocaleDateString() : 'N/A'
}

const formatCurrency = (amount: number | undefined) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount ?? 0)
}

const getPaymentPercentage = (debt: CustomerDebt) => {
    if (!debt.original_amount || debt.original_amount === 0) return 0
    return Math.round((debt.paid_amount / debt.original_amount) * 100)
}

const isOverdue = (dueDateString: string | undefined) => {
    if (!dueDateString) return false
    const dueDate = new Date(dueDateString)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return dueDate < today
}

const isDueSoon = (dueDateString: string | undefined) => {
    if (!dueDateString) return false
    const dueDate = new Date(dueDateString)
    const today = new Date()
    const diffTime = dueDate.getTime() - today.getTime()
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays > 0 && diffDays <= 7
}

const getDaysOverdue = (dueDateString: string | undefined) => {
    if (!dueDateString) return 0
    const dueDate = new Date(dueDateString)
    const today = new Date()
    const diffTime = today.getTime() - dueDate.getTime()
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getDaysUntilDue = (dueDateString: string | undefined) => {
    if (!dueDateString) return 0
    const dueDate = new Date(dueDateString)
    const today = new Date()
    const diffTime = dueDate.getTime() - today.getTime()
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getStatusLabel = (status: string) => {
    const labels = {
        pending: 'Pending',
        partial: 'Partial',
        paid: 'Paid',
        overdue: 'Overdue'
    }
    return labels[status as keyof typeof labels] || status
}

useFlashMessage()
</script>

<style scoped>
/* Custom breakpoint para pantallas extra pequeñas */
@media (min-width: 480px) {
    .xs\:flex-row {
        flex-direction: row;
    }

    .xs\:items-center {
        align-items: center;
    }

    .xs\:gap-0 {
        gap: 0;
    }

    .xs\:gap-4 {
        gap: 1rem;
    }

    .xs\:block {
        display: block;
    }

    .xs\:inline {
        display: inline;
    }

    .xs\:hidden {
        display: none;
    }
}

/* Scroll optimization */
@media (max-width: 767px) {
    .overflow-y-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

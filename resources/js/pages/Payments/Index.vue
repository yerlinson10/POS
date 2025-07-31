<template>
    <Head title="Payments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header con Search -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="Wallet" class="w-5 h-5 text-primary" />
                                Payments Management
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                Track all income and expense payments
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button @click="openNewPaymentDialog" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                New Payment
                            </Button>
                            <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                                <Icon name="Receipt" class="w-3 h-3 md:w-4 md:h-4" />
                                <span v-if="payments?.total">{{ payments.total }} payments</span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Icon name="Search"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 md:w-4 md:h-4 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search payments..."
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
                                    <SelectItem value="amount">Amount</SelectItem>
                                    <SelectItem value="payment_type">Type</SelectItem>
                                    <SelectItem value="payment_method">Method</SelectItem>
                                    <SelectItem value="category">Category</SelectItem>
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

                    <!-- Type and Method Filters -->
                    <div class="flex items-center gap-2">
                        <Select v-model="filters.payment_type" @update:modelValue="search">
                            <SelectTrigger class="w-full sm:w-32 md:w-40 h-7 md:h-9 text-xs md:text-sm">
                                <SelectValue placeholder="Type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="income">Income</SelectItem>
                                <SelectItem value="expense">Expense</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="filters.payment_method" @update:modelValue="search">
                            <SelectTrigger class="w-full sm:w-32 md:w-40 h-7 md:h-9 text-xs md:text-sm">
                                <SelectValue placeholder="Method" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="card">Card</SelectItem>
                                <SelectItem value="bank_transfer">Transfer</SelectItem>
                                <SelectItem value="check">Check</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-muted/20 border-b">
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">Total Income</div>
                        <div class="text-lg font-bold text-green-600">${{ Number(summary?.total_income || 0).toFixed(2) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">Total Expenses</div>
                        <div class="text-lg font-bold text-red-600">${{ Number(summary?.total_expenses || 0).toFixed(2) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">Net Balance</div>
                        <div class="text-lg font-bold" :class="{
                            'text-green-600': (summary?.total_income || 0) >= (summary?.total_expenses || 0),
                            'text-red-600': (summary?.total_income || 0) < (summary?.total_expenses || 0)
                        }">
                            ${{ Number((summary?.total_income || 0) - (summary?.total_expenses || 0)).toFixed(2) }}
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-muted-foreground">This Month</div>
                        <div class="text-lg font-bold text-blue-600">${{ Number(summary?.this_month_total || 0).toFixed(2) }}</div>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="flex-1 overflow-hidden">
                    <div class="h-full overflow-y-auto">
                        <table class="w-full">
                            <thead
                                class="sticky top-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b">
                                <tr class="h-12">
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Payment Info</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Type</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Method</th>
                                    <th class="text-right px-4 py-3 font-semibold text-sm">Amount</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Category</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payment in (payments?.data || [])" :key="payment.id"
                                    class="border-b hover:bg-muted/50 transition-colors group">
                                    <!-- Payment Info -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="font-medium text-sm group-hover:text-primary transition-colors">
                                                {{ payment.description || 'Payment' }}
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <Icon name="Calendar" class="w-3 h-3" />
                                                <span>{{ formatDate(payment.created_at) }}</span>
                                                <span v-if="payment.related_entity" class="text-muted-foreground">•</span>
                                                <span v-if="payment.related_entity">{{ payment.related_entity }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': payment.payment_type === 'income',
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': payment.payment_type === 'expense'
                                            }">
                                            <Icon :name="payment.payment_type === 'income' ? 'ArrowDown' : 'ArrowUp'" class="w-3 h-3 mr-1" />
                                            {{ payment.payment_type === 'income' ? 'Income' : 'Expense' }}
                                        </span>
                                    </td>

                                    <!-- Method -->
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <Icon :name="getPaymentMethodIcon(payment.payment_method)" class="w-4 h-4 text-muted-foreground" />
                                            <span class="text-sm capitalize">{{ payment.payment_method.replace('_', ' ') }}</span>
                                        </div>
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-4 py-4 text-right">
                                        <div class="font-semibold text-lg"
                                            :class="{
                                                'text-green-600': payment.payment_type === 'income',
                                                'text-red-600': payment.payment_type === 'expense'
                                            }">
                                            {{ payment.payment_type === 'income' ? '+' : '-' }}${{ Number(payment.amount).toFixed(2) }}
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm text-muted-foreground capitalize">
                                            {{ payment.category || 'General' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button @click="openEditPaymentDialog(payment)"
                                                variant="ghost"
                                                class="h-8 w-8 p-0 cursor-pointer">
                                                <Icon name="Edit" class="w-4 h-4" />
                                            </Button>

                                            <Button @click="openDetailsDialog(payment)"
                                                variant="ghost"
                                                class="h-8 w-8 p-0 cursor-pointer">
                                                <Icon name="Eye" class="w-4 h-4" />
                                            </Button>

                                            <AlertDialog>
                                                <AlertDialogTrigger as-child>
                                                    <Button variant="ghost" size="sm"
                                                        class="h-8 w-8 p-0 text-red-600 hover:text-red-700 cursor-pointer">
                                                        <Icon name="Trash2" class="w-4 h-4" />
                                                    </Button>
                                                </AlertDialogTrigger>
                                                <AlertDialogContent>
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            This action cannot be undone. It will permanently delete this
                                                            payment record.
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter>
                                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                        <AlertDialogAction @click="destroy(payment.id)"
                                                            class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]">
                                                            Continue
                                                        </AlertDialogAction>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="(payments?.data?.length || 0) > 0"
                    class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                    <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                        <div class="text-xs text-muted-foreground">
                            <span class="font-medium">{{ payments?.from || 0 }}-{{ payments?.to || 0 }}</span>
                            <span class="hidden xs:inline"> of </span>
                            <span class="xs:hidden">/</span>
                            <span class="font-medium">{{ payments?.total || 0 }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Page {{ payments?.current_page || 1 }}/{{ payments?.last_page || 1 }}
                        </div>
                    </div>

                    <Pagination v-slot="{ page: internalPage }" :items-per-page="filters.per_page"
                        :total="payments?.last_page || 1" :page="filters.page" @page-change="onPageChange" class="flex">
                        <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                            <PaginationPrevious @click="onPageChange(internalPage - 1)"
                                :disabled="(payments?.current_page || 1) <= 1" class="h-8 md:h-9" />
                            <template v-for="(item, idx) in pages" :key="idx">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === internalPage" @click="onPageChange(item.value)"
                                    class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                    {{ item.value }}
                                </PaginationItem>
                            </template>
                            <PaginationEllipsis :index="4" v-if="(payments?.last_page || 1) >= 4" class="h-8 md:h-9" />
                            <PaginationNext @click="onPageChange(internalPage + 1)"
                                :disabled="(payments?.current_page || 1) >= (payments?.last_page || 1)"
                                class="h-8 md:h-9" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>

        <!-- New/Edit Payment Dialog -->
        <PaymentFormDialog
            v-model:open="showPaymentFormDialog"
            :payment="selectedPayment"
            @payment-saved="handlePaymentSaved"
        />

        <!-- Payment Details Dialog -->
        <PaymentDetailsDialog
            v-model:open="showDetailsDialog"
            :payment="selectedPayment"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
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
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import Icon from '@/components/Icon.vue'
import PaymentFormDialog from './components/PaymentFormDialog.vue'
import PaymentDetailsDialog from './components/PaymentDetailsDialog.vue'
import { type BreadcrumbItem } from '@/types'
import useFlashMessage from '@/composables/useFlashMessages'
import { usePaymentFilters } from '@/composables/usePaymentFilters'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Payments', href: '/payments' },
]

interface Payment {
    id: number
    payment_type: 'income' | 'expense'
    amount: number
    payment_method: string
    category?: string
    description?: string
    related_entity?: string
    created_at: string
}

interface PaymentSummary {
    total_income: number
    total_expenses: number
    this_month_total: number
}

const props = defineProps<{
    payments: {
        data: Payment[]
        total: number
        from: number
        to: number
        current_page: number
        last_page: number
    }
    summary: PaymentSummary
    filters: {
        search: string
        sort_by: string
        sort_dir: string
        payment_type: string
        payment_method: string
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
    destroy,
} = usePaymentFilters(props)

// Dialog state
const showPaymentFormDialog = ref(false)
const showDetailsDialog = ref(false)
const selectedPayment = ref<Payment | null>(null)

const openNewPaymentDialog = () => {
    selectedPayment.value = null
    showPaymentFormDialog.value = true
}

const openEditPaymentDialog = (payment: Payment) => {
    selectedPayment.value = payment
    showPaymentFormDialog.value = true
}

const openDetailsDialog = (payment: Payment) => {
    selectedPayment.value = payment
    showDetailsDialog.value = true
}

const handlePaymentSaved = () => {
    showPaymentFormDialog.value = false
    selectedPayment.value = null
    search() // Refresh the list
}

// Utility functions
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const getPaymentMethodIcon = (method: string) => {
    const icons = {
        cash: 'Banknote',
        card: 'CreditCard',
        bank_transfer: 'Building2',
        check: 'FileText'
    }
    return icons[method as keyof typeof icons] || 'DollarSign'
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

<template>

    <Head title="Invoices" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header con Search -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold">Invoices</h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                Manage your sales invoices
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                                <Icon name="FileText" class="w-3 h-3 md:w-4 md:h-4" />
                                <span v-if="invoices.total">{{ invoices.total }} items</span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Icon name="Search"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 md:w-4 md:h-4 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search invoices..."
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
                        <span
                            class="text-xs md:text-sm font-medium text-muted-foreground hidden sm:inline">Filters:</span>
                        <div class="flex gap-2">
                            <Select v-model="filters.status" @update:modelValue="search">
                                <SelectTrigger class="w-full sm:w-32 md:w-40 h-7 md:h-9 text-xs md:text-sm">
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="quotation">Quotation</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="canceled">Canceled</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="filters.sort_by" @update:modelValue="search">
                                <SelectTrigger class="w-full sm:w-36 md:w-48 h-7 md:h-9 text-xs md:text-sm">
                                    <SelectValue placeholder="Sort by..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="created_at">Date Created</SelectItem>
                                    <SelectItem value="date">Invoice Date</SelectItem>
                                    <SelectItem value="total_amount">Total Amount</SelectItem>
                                    <SelectItem value="id">Invoice ID</SelectItem>
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
                </div>

                <!-- Invoices Table -->
                <div class="flex-1 overflow-hidden">
                    <div class="h-full overflow-y-auto">
                        <table class="w-full">
                            <thead
                                class="sticky top-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b">
                                <tr class="h-12">
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Invoice</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Customer</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Date</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Total</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Payment</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Status</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in invoices.data" :key="invoice.id"
                                    class="border-b hover:bg-muted/50 transition-colors group">

                                    <!-- Invoice -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-mono bg-primary/10 text-primary px-2 py-1 rounded-md text-sm font-medium">
                                                #{{ invoice.id }}
                                            </span>
                                            <div class="text-xs text-muted-foreground">
                                                {{ invoice.items_count }} item{{ invoice.items_count !== 1 ? 's' : '' }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Customer -->
                                    <td class="px-4 py-4">
                                        <div v-if="invoice.customer"
                                            class="font-medium text-sm group-hover:text-primary transition-colors">
                                            {{ invoice.customer.name }}
                                        </div>
                                        <div v-else class="text-sm text-muted-foreground">
                                            No customer
                                        </div>
                                        <div v-if="invoice.customer?.email" class="text-xs text-muted-foreground">
                                            {{ invoice.customer.email }}
                                        </div>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm">{{ formatDate(invoice.date) }}</div>
                                        <div class="text-xs text-muted-foreground">{{ formatDate(invoice.created_at) }}
                                        </div>
                                    </td>

                                    <!-- Total -->
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-sm">
                                            ${{ formatCurrency(invoice.total_amount) }}
                                        </div>
                                    </td>

                                    <!-- Payment Method -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <Icon :name="getPaymentMethodIcon(invoice.payment_method)"
                                                class="w-4 h-4 text-muted-foreground" />
                                            <span class="text-sm capitalize">{{ invoice.payment_method || 'cash'
                                                }}</span>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-4">
                                        <Badge :variant="getStatusVariant(invoice.status)" class="capitalize">
                                            {{ invoice.status }}
                                        </Badge>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Link :href="route('invoices.show', invoice.id)" prefetch
                                                :cacheFor="['30s', '1m']">
                                            <Button variant="ghost" class="h-8 w-8 p-0 cursor-pointer">
                                                <Icon name="Eye" class="w-4 h-4" />
                                            </Button>
                                            </Link>
                                            <Link v-if="invoice.status === 'quotation'"
                                                :href="route('invoices.edit', invoice.id)" prefetch
                                                :cacheFor="['30s', '1m']">
                                            <Button variant="ghost" class="h-8 w-8 p-0 cursor-pointer">
                                                <Icon name="Edit" class="w-4 h-4" />
                                            </Button>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Compact Pagination -->
                <div v-if="invoices.data.length > 0"
                    class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                    <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                        <div class="text-xs text-muted-foreground">
                            <span class="font-medium">{{ invoices.from || 0 }}-{{ invoices.to || 0 }}</span>
                            <span class="hidden xs:inline"> of </span>
                            <span class="xs:hidden">/</span>
                            <span class="font-medium">{{ invoices.total }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Page {{ invoices.current_page }}/{{ invoices.last_page }}
                        </div>
                    </div>

                    <Pagination :items-per-page="parseInt(filters.per_page)" :total="invoices.total"
                        :page="invoices.current_page" @page-change="onPageChange" class="flex">
                        <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                            <PaginationPrevious @click="onPageChange(invoices.current_page - 1)"
                                :disabled="invoices.current_page <= 1" class="h-8 md:h-9" />
                            <template v-for="(item, idx) in pages" :key="idx">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === invoices.current_page" @click="onPageChange(item.value)"
                                    class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                    {{ item.value }}
                                </PaginationItem>
                            </template>
                            <PaginationEllipsis :index="4" v-if="invoices.last_page >= 4" class="h-8 md:h-9" />
                            <PaginationNext @click="onPageChange(invoices.current_page + 1)"
                                :disabled="invoices.current_page >= invoices.last_page" class="h-8 md:h-9" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
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
import { type BreadcrumbItem } from '@/types'
import useFlashMessage from '@/composables/useFlashMessages'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
]

interface Invoice {
    id: number
    date: string
    customer_id?: number
    customer?: {
        id: number
        name: string
        email: string
    }
    total_amount: number
    status: 'quotation' | 'paid' | 'canceled'
    payment_method: 'cash' | 'card' | 'transfer' | 'other'
    items_count: number
    created_at: string
    discount_value?: number
}

interface Props {
    invoices: {
        data: Invoice[]
        current_page: number
        last_page: number
        per_page: number
        total: number
        from: number
        to: number
        prev_page_url: string | null
        next_page_url: string | null
    }
    filters: {
        search?: string
        status?: string
        sort_by?: string
        sort_dir?: string
        per_page?: string
        page?: number
    }
    statuses: string[]
}

const props = defineProps<Props>()

const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    sort_by: props.filters.sort_by || 'created_at',
    sort_dir: props.filters.sort_dir || 'desc',
    per_page: props.filters.per_page || '10',
    page: props.filters.page || 1,
})

// Computed
const hasActiveFilters = computed(() => {
    return !!(filters.value.search || filters.value.status)
})

// Watch for filter changes and debounce search
let searchTimeout: number | null = null

const search = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }

    searchTimeout = setTimeout(() => {
        router.get(route('invoices.index'), filters.value, {
            preserveState: true,
            replace: true,
        })
    }, 300)
}

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        sort_by: 'created_at',
        sort_dir: 'desc',
        per_page: '10',
        page: 1,
    }
    search()
}

const onPageChange = (page: number) => {
    filters.value.page = page
    router.get(route('invoices.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'paid':
            return 'success'
        case 'quotation':
            return 'warning'
        case 'canceled':
            return 'destructive'
        default:
            return 'default'
    }
}

const getPaymentMethodIcon = (method: string) => {
    switch (method) {
        case 'cash':
            return 'Banknote'
        case 'card':
            return 'CreditCard'
        case 'transfer':
            return 'ArrowRightLeft'
        case 'other':
            return 'MoreHorizontal'
        default:
            return 'Banknote'
    }
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

/* Mejorar scroll en móvil */
@media (max-width: 767px) {
    .overflow-y-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

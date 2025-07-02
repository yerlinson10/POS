<template>
    <Head title="Invoices" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-4">

                <!-- Header with filters -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                    <!-- New Invoice Button -->
                    <div class="flex items-center">
                        <Button as="a" href="/invoices/create" class="w-full md:w-auto">
                            <Plus class="w-4 h-4 mr-2" />
                            New Invoice
                        </Button>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <Select v-model="filters.status" @update:modelValue="search">
                            <SelectTrigger>
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Status</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                                <SelectItem value="canceled">Canceled</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Date Range Filters -->
                    <div class="flex gap-2">
                        <Input
                            v-model="filters.date_from"
                            type="date"
                            placeholder="From date"
                            @change="search"
                        />
                        <Input
                            v-model="filters.date_to"
                            type="date"
                            placeholder="To date"
                            @change="search"
                        />
                    </div>

                    <!-- Search -->
                    <div class="relative">
                        <Input
                            v-model="filters.search"
                            placeholder="Search invoices..."
                            class="pl-10"
                            @keyup.enter="search"
                        />
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                        <Button
                            class="absolute right-0 top-0 h-full px-3 rounded-l-none"
                            @click="search"
                        >
                            Search
                        </Button>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div v-if="hasActiveFilters" class="mb-4">
                    <Button variant="destructive" size="sm" @click="resetFilters">
                        <RotateCcw class="w-4 h-4 mr-2" />
                        Clear Filters
                    </Button>
                </div>

                <!-- Invoices Table -->
                <div class="rounded-md border">
                    <Table>
                        <TableCaption>
                            {{ invoices.total ? `${invoices.total} invoice(s) found.` : 'No invoices found.' }}
                        </TableCaption>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="cursor-pointer" @click="sortBy('id')">
                                    ID
                                    <ArrowUpDown class="w-4 h-4 inline ml-1" />
                                </TableHead>
                                <TableHead class="cursor-pointer" @click="sortBy('date')">
                                    Date
                                    <ArrowUpDown class="w-4 h-4 inline ml-1" />
                                </TableHead>
                                <TableHead>Customer</TableHead>
                                <TableHead class="cursor-pointer" @click="sortBy('total_amount')">
                                    Total
                                    <ArrowUpDown class="w-4 h-4 inline ml-1" />
                                </TableHead>
                                <TableHead class="cursor-pointer" @click="sortBy('status')">
                                    Status
                                    <ArrowUpDown class="w-4 h-4 inline ml-1" />
                                </TableHead>
                                <TableHead>User</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="invoice in invoices.data" :key="invoice.id">
                                <TableCell class="font-medium">#{{ invoice.id }}</TableCell>
                                <TableCell>{{ formatDate(invoice.date) }}</TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-semibold">
                                            {{ invoice.customer?.first_name }} {{ invoice.customer?.last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ invoice.customer?.email }}</div>
                                    </div>
                                </TableCell>
                                <TableCell class="font-semibold">${{ Number(invoice.total_amount).toFixed(2) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(invoice.status)">
                                        {{ invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ invoice.user?.name || 'N/A' }}</TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as="a"
                                        :href="`/invoices/${invoice.id}`"
                                    >
                                        <Eye class="w-4 h-4 mr-1" />
                                        View
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as="a"
                                        :href="`/invoices/${invoice.id}/edit`"
                                    >
                                        <Edit class="w-4 h-4 mr-1" />
                                        Edit
                                    </Button>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="sm">
                                                <Trash2 class="w-4 h-4 mr-1" />
                                                Delete
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Delete Invoice</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Are you sure you want to delete invoice #{{ invoice.id }}?
                                                    This action cannot be undone and will restore the product stock.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction
                                                    variant="destructive"
                                                    @click="deleteInvoice(invoice.id)"
                                                >
                                                    Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    <Pagination
                        v-slot="{ page: internalPage }"
                        :items-per-page="filters.per_page || 10"
                        :total="invoices.last_page"
                        :page="filters.page"
                        @page-change="onPageChange"
                    >
                        <PaginationContent v-slot="{ items: pages }">
                            <PaginationPrevious @click="onPageChange(internalPage - 1)" />
                            <template v-for="(item, idx) in pages" :key="idx">
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="item.value === internalPage"
                                    @click="onPageChange(item.value)"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                            </template>
                            <PaginationEllipsis v-if="invoices.last_page >= 4" :index="4" />
                            <PaginationNext @click="onPageChange(internalPage + 1)" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import Badge from '@/components/ui/SimpleBadge.vue'
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
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
    Search,
    Plus,
    RotateCcw,
    ArrowUpDown,
    Eye,
    Edit,
    Trash2
} from 'lucide-vue-next'

import { useInvoiceStore } from '@/stores/invoiceStore'
import type { PaginatedInvoices, InvoiceFilters } from '@/stores/invoiceStore'
import { type BreadcrumbItem } from '@/types'
import useFlashMessage from '@/composables/useFlashMessages'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
]

interface Props {
    invoices: PaginatedInvoices
    filters: InvoiceFilters
}

const props = defineProps<Props>()

// Stores
const invoiceStore = useInvoiceStore()

// Initialize store
invoiceStore.setInvoices(props.invoices)
invoiceStore.updateFilters(props.filters)

// Reactive filters
const filters = ref({ ...props.filters })

// Computed
const hasActiveFilters = computed(() => {
    return !!(filters.value.search ||
             filters.value.status ||
             filters.value.date_from ||
             filters.value.date_to)
})

// Methods
const search = () => {
    router.get('/invoices', filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_dir: 'desc',
        per_page: 15,
        page: 1
    }
    search()
}

const sortBy = (field: string) => {
    const newDirection = filters.value.sort_by === field &&
                        filters.value.sort_dir === 'asc' ? 'desc' : 'asc'
    filters.value.sort_by = field
    filters.value.sort_dir = newDirection
    filters.value.page = 1
    search()
}

const onPageChange = (page: number) => {
    filters.value.page = page
    search()
}

const deleteInvoice = (invoiceId: number) => {
    router.delete(`/invoices/${invoiceId}`, {
        onSuccess: () => {
            toast.success('Invoice deleted successfully')
        },
        onError: () => {
            toast.error('Failed to delete invoice')
        }
    })
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'paid':
            return 'default'
        case 'pending':
            return 'secondary'
        case 'canceled':
            return 'destructive'
        default:
            return 'outline'
    }
}

// Flash messages
useFlashMessage()
</script>

<template>
    <Head title="Suppliers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header con Search -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="Building2" class="w-5 h-5 text-primary" />
                                Suppliers Management
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                Manage your suppliers and track their debts
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button as="a" href="/suppliers/create" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                New Supplier
                            </Button>
                            <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                                <Icon name="Building2" class="w-3 h-3 md:w-4 md:h-4" />
                                <span v-if="suppliers?.total">{{ suppliers.total }} suppliers</span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Icon name="Search"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 md:w-4 md:h-4 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search suppliers..."
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
                                    <SelectItem value="company_name">Company</SelectItem>
                                    <SelectItem value="contact_name">Contact</SelectItem>
                                    <SelectItem value="email">Email</SelectItem>
                                    <SelectItem value="total_debt">Debt</SelectItem>
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

                    <!-- Debt Status Filter -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm font-medium text-muted-foreground">Filter:</span>
                        <Select v-model="filters.debt_status" @update:modelValue="search">
                            <SelectTrigger class="w-full sm:w-32 md:w-40 h-7 md:h-9 text-xs md:text-sm">
                                <SelectValue placeholder="Debt Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="no_debt">No Debt</SelectItem>
                                <SelectItem value="with_debt">With Debt</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Suppliers Table -->
                <div class="flex-1 overflow-hidden">
                    <div class="h-full overflow-y-auto">
                        <table class="w-full">
                            <thead
                                class="sticky top-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b">
                                <tr class="h-12">
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Supplier Info</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Contact</th>
                                    <th class="text-right px-4 py-3 font-semibold text-sm">Total Debt</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Status</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="supplier in (suppliers?.data || [])" :key="supplier.id"
                                    class="border-b hover:bg-muted/50 transition-colors group">
                                    <!-- Supplier Info -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="font-medium text-sm group-hover:text-primary transition-colors">
                                                {{ supplier.company_name }}
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ supplier.tax_id }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-sm">{{ supplier.contact_name }}</div>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <Icon name="Mail" class="w-3 h-3" />
                                                {{ supplier.email }}
                                            </div>
                                            <div v-if="supplier.phone" class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <Icon name="Phone" class="w-3 h-3" />
                                                {{ supplier.phone }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Total Debt -->
                                    <td class="px-4 py-4 text-right">
                                        <div class="font-semibold text-lg"
                                            :class="{
                                                'text-green-600': supplier.total_debt === 0,
                                                'text-red-600': supplier.total_debt > 0
                                            }">
                                            ${{ Number(supplier.total_debt || 0).toFixed(2) }}
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': supplier.total_debt === 0,
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': supplier.total_debt > 0
                                            }">
                                            {{ supplier.total_debt > 0 ? 'With Debt' : 'No Debt' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Link :href="`/suppliers/${supplier.id}/edit`">
                                                <Button variant="ghost" class="h-8 w-8 p-0 cursor-pointer">
                                                    <Icon name="Edit" class="w-4 h-4" />
                                                </Button>
                                            </Link>

                                            <!-- Pay Debt Button -->
                                            <Button v-if="supplier.total_debt > 0"
                                                @click="openPayDebtDialog(supplier)"
                                                variant="ghost"
                                                class="h-8 w-8 p-0 text-green-600 hover:text-green-700 cursor-pointer">
                                                <Icon name="DollarSign" class="w-4 h-4" />
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
                                                            supplier and remove all associated data.
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter>
                                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                        <AlertDialogAction @click="destroy(supplier.id)"
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
                <div v-if="(suppliers?.data?.length || 0) > 0"
                    class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                    <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                        <div class="text-xs text-muted-foreground">
                            <span class="font-medium">{{ suppliers?.from || 0 }}-{{ suppliers?.to || 0 }}</span>
                            <span class="hidden xs:inline"> of </span>
                            <span class="xs:hidden">/</span>
                            <span class="font-medium">{{ suppliers?.total || 0 }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Page {{ suppliers?.current_page || 1 }}/{{ suppliers?.last_page || 1 }}
                        </div>
                    </div>

                    <Pagination v-slot="{ page: internalPage }" :items-per-page="filters.per_page"
                        :total="suppliers?.last_page || 1" :page="filters.page" @page-change="onPageChange" class="flex">
                        <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                            <PaginationPrevious @click="onPageChange(internalPage - 1)"
                                :disabled="(suppliers?.current_page || 1) <= 1" class="h-8 md:h-9" />
                            <template v-for="(item, idx) in pages" :key="idx">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === internalPage" @click="onPageChange(item.value)"
                                    class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                    {{ item.value }}
                                </PaginationItem>
                            </template>
                            <PaginationEllipsis :index="4" v-if="(suppliers?.last_page || 1) >= 4" class="h-8 md:h-9" />
                            <PaginationNext @click="onPageChange(internalPage + 1)"
                                :disabled="(suppliers?.current_page || 1) >= (suppliers?.last_page || 1)"
                                class="h-8 md:h-9" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>

        <!-- Pay Debt Dialog -->
        <PayDebtDialog
            v-model:open="showPayDebtDialog"
            :supplier="selectedSupplier"
            @debt-paid="handleDebtPaid"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
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
import PayDebtDialog from './components/PayDebtDialog.vue'
import { type BreadcrumbItem } from '@/types'
import useFlashMessage from '@/composables/useFlashMessages'
import { useSupplierFilters } from '@/composables/useSupplierFilters'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Suppliers', href: '/suppliers' },
]

interface Supplier {
    id: number
    company_name: string
    contact_name: string
    email: string
    phone?: string
    tax_id?: string
    total_debt: number
}

const props = defineProps<{
    suppliers: {
        data: Supplier[]
        total: number
        from: number
        to: number
        current_page: number
        last_page: number
    }
    filters: {
        search: string
        sort_by: string
        sort_dir: string
        debt_status: string
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
} = useSupplierFilters(props)

// Pay debt functionality
const showPayDebtDialog = ref(false)
const selectedSupplier = ref<Supplier | null>(null)

const openPayDebtDialog = (supplier: Supplier) => {
    if (supplier?.id && supplier?.total_debt > 0) {
        selectedSupplier.value = supplier
        showPayDebtDialog.value = true
    }
}

const handleDebtPaid = () => {
    showPayDebtDialog.value = false
    selectedSupplier.value = null
    search() // Refresh the list
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

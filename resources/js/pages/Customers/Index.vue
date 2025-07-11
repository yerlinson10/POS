<template>

    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header con Search -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold">Customers Management</h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                Manage your customer database and information
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button as="a" href="/customers/create" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                New Customer
                            </Button>
                            <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                                <Icon name="Users" class="w-3 h-3 md:w-4 md:h-4" />
                                <span v-if="customers.total">{{ customers.total }} customers</span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Icon name="Search"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 md:w-4 md:h-4 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search customers..."
                                class="pl-7 md:pl-10 h-8 md:h-11 text-sm" @keyup.enter="search" />
                        </div>
                        <Button @click="search" size="sm" class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                            <Icon name="Search" class="w-3 h-3 md:w-4 md:h-4" />
                        </Button>
                        <Button v-if="hasActiveFilters" @click="resetFilters" variant="outline" size="sm"
                            class="h-8 md:h-11 px-2 md:px-4 text-xs md:text-sm">
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
                                    <SelectItem value="first_name">First Name</SelectItem>
                                    <SelectItem value="last_name">Last Name</SelectItem>
                                    <SelectItem value="email">Email</SelectItem>
                                    <SelectItem value="phone">Phone</SelectItem>
                                    <SelectItem value="address">Address</SelectItem>
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
                <!-- Customers Table -->
                <div class="flex-1 overflow-hidden">
                    <div class="h-full overflow-y-auto">
                        <table class="w-full">
                            <thead
                                class="sticky top-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b">
                                <tr class="h-12">
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Customer Info</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Contact</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Address</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in customers.data" :key="p.id"
                                    class="border-b hover:bg-muted/50 transition-colors group">
                                    <!-- Customer Info -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2">
                                                <Icon name="User" class="w-4 h-4 text-primary" />
                                                <div
                                                    class="font-medium text-sm group-hover:text-primary transition-colors">
                                                    {{ p.first_name }} {{ p.last_name }}
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <span class="font-mono bg-muted px-1.5 py-0.5 rounded">#{{ p.id
                                                    }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2 text-sm">
                                                <Icon name="Mail" class="w-4 h-4 text-muted-foreground" />
                                                <span class="text-sm">{{ p.email }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-sm">
                                                <Icon name="Phone" class="w-4 h-4 text-muted-foreground" />
                                                <span class="text-sm">{{ p.phone }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Address -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="MapPin" class="w-4 h-4 text-muted-foreground" />
                                            <span class="text-sm text-muted-foreground truncate max-w-xs">
                                                {{ p.address }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-center gap-1 px-4 py-4">
                                        <Link :href="`/customers/${p.id}/edit`" prefetch :cacheFor="['30s', '1m']" >
                                        <Button variant="ghost" class="h-8 w-8 p-0 cursor-pointer">
                                            <Icon name="Edit" class="w-4 h-4" />
                                        </Button>
                                        </Link>
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
                                                        his action cannot be undone. It will permanently delete this
                                                        record and
                                                        remove your data from our servers.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction @click="destroy(p.id)"
                                                        class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]">
                                                        Continue
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Compact Pagination -->
                <div v-if="customers.data.length > 0"
                    class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                    <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                        <div class="text-xs text-muted-foreground">
                            <span class="font-medium">{{ customers.from || 0 }}-{{ customers.to || 0 }}</span>
                            <span class="hidden xs:inline"> of </span>
                            <span class="xs:hidden">/</span>
                            <span class="font-medium">{{ customers.total }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Page {{ customers.current_page }}/{{ customers.last_page }}
                        </div>
                    </div>

                    <Pagination v-slot="{ page: internalPage }" :items-per-page="filters.per_page"
                        :total="customers.last_page" :page="filters.page" @page-change="onPageChange" class="flex">
                        <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                            <PaginationPrevious @click="onPageChange(internalPage - 1)"
                                :disabled="customers.current_page <= 1" class="h-8 md:h-9" />
                            <template v-for="(item, idx) in pages" :key="idx">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === internalPage" @click="onPageChange(item.value)"
                                    class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                    {{ item.value }}
                                </PaginationItem>
                            </template>
                            <PaginationEllipsis :index="4" v-if="customers.last_page >= 4" class="h-8 md:h-9" />
                            <PaginationNext @click="onPageChange(internalPage + 1)"
                                :disabled="customers.current_page >= customers.last_page" class="h-8 md:h-9" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
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

import { type BreadcrumbItem } from '@/types'
import useFlashMessage from '@/composables/useFlashMessages'
import { useCustomerFilters } from '@/composables/useCustomerFilters'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customers', href: '/customers' },
]

const props = defineProps<{
    customers: import('@/composables/useCustomerFilters').Paginated<import('@/composables/useCustomerFilters').Customer>
    filters: import('@/composables/useCustomerFilters').CustomerFilters
}>()

const {
    filters,
    hasActiveFilters,
    resetFilters,
    // getList,
    search,
    onPageChange,
    destroy,
} = useCustomerFilters(props)

useFlashMessage();
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

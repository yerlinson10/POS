<template>

    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border  p-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-4">
                    <!-- Botón Nuevo -->
                    <div class="md:w-1/3 flex items-center">
                        <Button as="a" href="/products/create" class="md:w-auto">New</Button>
                    </div>
                    <!-- Selects de ordenamiento -->
                    <div class="md:w-full flex gap-2">
                        <Select v-model="filters.sort_by" @update:modelValue="search" class="px-2 py-1 w-1/2">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a field" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="created_at">
                                    Date
                                </SelectItem>
                                <SelectItem value="sku">
                                    SKU
                                </SelectItem>
                                <SelectItem value="name">
                                    Name
                                </SelectItem>
                                <SelectItem value="price">
                                    Price
                                </SelectItem>
                                <SelectItem value="stock">
                                    Stock
                                </SelectItem>
                                <SelectItem value="category.name">
                                    Category
                                </SelectItem>
                                <SelectItem value="unitMeasure.code">
                                    Unit
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="filters.sort_dir" @update:modelValue="search" class="px-2 py-1 w-1/2">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a sort" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="asc">
                                    Ascending
                                </SelectItem>
                                <SelectItem value="desc">
                                    Descending
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button v-if="hasActiveFilters" variant="destructive" class="p-1 cursor-pointer"
                            @click="resetFilters">
                            <RotateCcw />
                        </Button>
                    </div>

                    <!-- Buscador alineado a la derecha -->
                    <div
                        class="md:max-w-xs max-w-sm relative flex items-center justify-end lg:justify-end w-full lg:col-start-3 justify-self-end">
                        <Input id="search" type="text" placeholder="Search..." v-model="filters.search"
                            class="pl-10 w-full" @keyup.enter="search" />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                            <Search class="size-6 text-muted-foreground" />
                        </span>
                        <Button type="submit"
                            class="absolute end-0 inset-y-0 flex items-center justify-center px-2 cursor-pointer rounded-l-none"
                            @click="search">
                            Search
                        </Button>
                    </div>
                </div>
                <Table>
                    <TableCaption>{{ products.total ? 'List of Products.' : 'There are no products available' }}
                    </TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Sku</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Unit</TableHead>
                            <TableHead>Price</TableHead>
                            <TableHead>Stock</TableHead>
                            <TableHead class="text-right">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="p in products.data" :key="p.id">
                            <TableCell>{{ p.sku }}</TableCell>
                            <TableCell>{{ p.name }}</TableCell>
                            <TableCell>{{ p.category }}</TableCell>
                            <TableCell>{{ p.unit_measure }}</TableCell>
                            <TableCell>{{ p.price }}</TableCell>
                            <TableCell>{{ p.stock }}</TableCell>
                            <TableCell class="text-right space-x-1">
                                <Link :href="`/products/${p.id}/edit`" prefetch :cacheFor="['30s', '1m']">
                                <Button size="sm" variant="outline" class="cursor-pointer">
                                    Edit
                                    <SquarePen class="w-4 h-4" />
                                </Button>
                                </Link>
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button size="sm" variant="destructive" class="cursor-pointer">
                                            Delete
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. It will permanently delete this record and
                                                remove your data from our servers.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                                            <AlertDialogAction variant="destructive"
                                                class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]"
                                                @click="destroy(p.id)">Continue</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination v-slot="{ page: internalPage }" :items-per-page="filters.per_page"
                    :total="products.last_page" :page="filters.page" @page-change="onPageChange">
                    <PaginationContent v-slot="{ items: pages }">
                        <PaginationPrevious @click="onPageChange(internalPage - 1)" />
                        <template v-for="(item, idx) in pages" :key="idx">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                :is-active="item.value === internalPage" @click="onPageChange(item.value)">
                                {{ item.value }}
                            </PaginationItem>
                        </template>
                        <PaginationEllipsis :index="4" v-if="products.last_page >= 4" />
                        <PaginationNext @click="onPageChange(internalPage + 1)" />
                    </PaginationContent>
                </Pagination>
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
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
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
import { Trash2, SquarePen, Search, RotateCcw } from 'lucide-vue-next';

import { type BreadcrumbItem } from '@/types'
import useFlashMessage from '@/composables/useFlashMessages'
import { useProductFilters } from '@/composables/useProductFilters'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: '/products' },
]

const props = defineProps<{
    products: import('@/composables/useProductFilters').Paginated<import('@/composables/useProductFilters').Product>
    filters: import('@/composables/useProductFilters').ProductFilters
}>()

const {
    filters,
    hasActiveFilters,
    resetFilters,
    // getList,
    search,
    onPageChange,
    destroy,
} = useProductFilters(props)

useFlashMessage();
</script>

<template>

    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border  p-4">
                <div class="flex flex-col gap-2 md:flex-row md:justify-between mb-4">
                    <Button as="a" href="/products/create" class="w-full md:w-auto">New</Button>
                    <div class="relative flex-1 max-w-sm md:max-w-xs items-center">
                        <Input
                            id="search"
                            type="text"
                            placeholder="Search..."
                            v-model="filters.search"
                            class="pl-10 w-full"
                            @keyup.enter="search"
                        />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                            <Search class="size-6 text-muted-foreground" />
                        </span>
                        <Button
                            type="submit"
                            class="absolute end-0 inset-y-0 flex items-center justify-center px-2 cursor-pointer rounded-l-none"
                            @click="search"
                        >
                            Search
                        </Button>
                    </div>
                </div>
                <Table>
                    <TableCaption>{{ products.total ? 'List of Products.' : 'There are no products available'}}</TableCaption>
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
                                <Link :href="`/products/${p.id}/edit`" prefetch :cacheFor="['30s', '1m']"  >
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
                    :total="products.last_page" :default-page="filters.page" @page-change="onPageChange">
                    <PaginationContent v-slot="{ items: pages }">
                        <PaginationPrevious  @click="onPageChange(internalPage - 1)"/>
                        <template v-for="(item, idx) in pages" :key="idx">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                :is-active="item.value === internalPage" @click="onPageChange(item.value)">
                                {{ item.value }}
                            </PaginationItem>
                        </template>
                        <PaginationEllipsis :index="4" v-if="products.last_page >= 4" />
                        <PaginationNext @click="onPageChange(internalPage + 1)"/>
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
        <Toaster richColors  position="top-right" theme="system" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
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
import { Trash2, SquarePen, Search } from 'lucide-vue-next';

import { type BreadcrumbItem } from '@/types'
import { Toaster, toast } from 'vue-sonner'
import 'vue-sonner/style.css'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: '/products' },
]

interface Product {
    id: number
    sku: string
    name: string
    category: string
    unit_measure: string
    price: number
    stock: number
}

interface Paginated<T> {
    data: T[]
    current_page: number
    per_page: number
    total: number
    last_page: number
}

const props = defineProps<{
    products: Paginated<Product>
    filters: { per_page: number; search?: string; page?: number }
}>()

// Inicializa filtros, incluyendo page
const filters = ref({
    ...props.filters,
    page: props.filters.page ?? props.products.current_page,
})

interface FlashMessage {
    type?: string;
    text?: string;
}


const page = usePage();
const flash = computed(() => (page.props.flash as { message?: FlashMessage })?.message);

onMounted(() => {
    if (flash.value?.text) {
        toast.success(flash.value.text)
    }
})

// Función general para recargar lista
function getList() {
    router.get(
        '/products',
        // Preparamos query sin page si queremos resetearla
        filters.value,
        { preserveState: true, replace: true }
    )
}

// Al hacer búsqueda, forzamos page = 1
function search() {
    filters.value.page = 1
    getList()
}

// Cambio de página manteniendo búsqueda
function onPageChange(newPage: number) {
    filters.value.page = newPage
    getList()
}

function destroy(id: number) {
    router.delete(`/products/${id}`)
}
</script>

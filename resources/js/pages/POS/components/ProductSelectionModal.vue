<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="!max-w-none !w-[88vw] !h-[90vh] !max-h-none flex flex-col p-0">
            <!-- Header with Search -->
            <div class="flex flex-col gap-4 p-6 border-b bg-muted/30">
                <div class="flex items-center justify-between">
                    <div>
                        <DialogTitle class="text-xl font-semibold">Product Catalog</DialogTitle>
                        <DialogDescription class="text-sm text-muted-foreground">
                            Browse and add products to your cart
                        </DialogDescription>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Icon name="Package" class="w-4 h-4" />
                        <span v-if="pagination.total">{{ pagination.total }} products available</span>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <Icon name="Search"
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="filters.search" placeholder="Search by product name, SKU, or category..."
                            class="pl-10 h-11" @keyup.enter="search" />
                    </div>
                    <Button @click="search" :disabled="isLoading" class="h-11 px-6">
                        <Icon name="Search" class="w-4 h-4 mr-2" />
                        Search
                    </Button>
                    <Button v-if="filters.search || filters.sort_by !== 'name' || filters.sort_dir !== 'asc'"
                        @click="clearSearch" variant="outline" class="h-11">
                        <Icon name="RotateCcw" class="w-4 h-4 mr-2" />
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Filters Toolbar -->
            <div class="flex items-center justify-between gap-4 p-4 bg-background border-b">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-muted-foreground">Sort:</span>
                    <Select v-model="filters.sort_by" @update:modelValue="search">
                        <SelectTrigger class="w-48 h-9">
                            <SelectValue placeholder="Sort by..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="name">Name</SelectItem>
                            <SelectItem value="price">Price</SelectItem>
                            <SelectItem value="stock">Stock</SelectItem>
                            <SelectItem value="category.name">Category</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="filters.sort_dir" @update:modelValue="search">
                        <SelectTrigger class="w-32 h-9">
                            <SelectValue placeholder="Order..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="asc">A-Z / Low-High</SelectItem>
                            <SelectItem value="desc">Z-A / High-Low</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-muted-foreground">Show:</span>
                    <Select v-model="filters.per_page" @update:modelValue="changePageSize">
                        <SelectTrigger class="w-32 h-9">
                            <SelectValue placeholder="Per page..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="20">20 items</SelectItem>
                            <SelectItem :value="50">50 items</SelectItem>
                            <SelectItem :value="100">100 items</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Products Table -->
            <div class="flex-1 min-h-0 flex flex-col">
                <!-- Loading State -->
                <div v-if="isLoading" class="flex items-center justify-center h-64">
                    <div class="flex flex-col items-center gap-3">
                        <Icon name="Loader2" class="w-8 h-8 animate-spin text-primary" />
                        <p class="text-sm text-muted-foreground">Loading products...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center h-64">
                    <div class="flex flex-col items-center gap-3 text-center">
                        <Icon name="AlertCircle" class="w-8 h-8 text-destructive" />
                        <div>
                            <p class="font-medium text-destructive">Error loading products</p>
                            <p class="text-sm text-muted-foreground">{{ error }}</p>
                        </div>
                        <Button @click="search" variant="outline" size="sm">
                            <Icon name="RefreshCw" class="w-4 h-4 mr-2" />
                            Try Again
                        </Button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="products.length === 0" class="flex items-center justify-center h-64">
                    <div class="flex flex-col items-center gap-3 text-center">
                        <Icon name="Package" class="w-12 h-12 text-muted-foreground/50" />
                        <div>
                            <p class="font-medium text-muted-foreground">No products found</p>
                            <p class="text-sm text-muted-foreground">Try adjusting your search or filters</p>
                        </div>
                        <Button @click="clearSearch" variant="outline" size="sm">
                            <Icon name="RotateCcw" class="w-4 h-4 mr-2" />
                            Clear Filters
                        </Button>
                    </div>
                </div>

                <!-- Products Grid/Table -->
                <div v-else class="flex-1 overflow-hidden">
                    <div class="h-full overflow-y-auto">
                        <table class="w-full">
                            <thead
                                class="sticky top-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b">
                                <tr class="h-12">
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Product Info</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Category</th>
                                    <th class="text-right px-4 py-3 font-semibold text-sm">Price</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm">Stock</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-24">Qty</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-28">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products" :key="product.id"
                                    class="border-b hover:bg-muted/50 transition-colors group">
                                    <!-- Product Info -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="font-medium text-sm group-hover:text-primary transition-colors">
                                                {{ product.name }}
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <span class="font-mono bg-muted px-1.5 py-0.5 rounded">{{ product.sku
                                                    }}</span>
                                                <span>•</span>
                                                <span>{{ product.unit_measure }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="px-4 py-4">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-secondary text-secondary-foreground text-xs font-medium">
                                            {{ product.category }}
                                        </span>
                                    </td>

                                    <!-- Price -->
                                    <td class="px-4 py-4 text-right">
                                        <div class="font-semibold text-lg">
                                            ${{ Number(product.price).toFixed(2) }}
                                        </div>
                                    </td>

                                    <!-- Stock -->
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': product.stock > 10,
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': product.stock <= 10 && product.stock > 0,
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': product.stock === 0
                                            }">
                                            {{ product.stock }} {{ product.stock === 1 ? 'unit' : 'units' }}
                                        </span>
                                    </td>

                                    <!-- Quantity Input -->
                                    <td class="px-4 py-4 text-center">
                                        <Input v-model.number="productQuantities[product.id]" type="number" min="1"
                                            :max="product.stock" class="w-16 text-center h-9"
                                            @keyup.enter="addProduct(product)" :disabled="product.stock === 0" />
                                    </td>

                                    <!-- Action Button -->
                                    <td class="px-4 py-4 text-center">
                                        <Button @click="addProduct(product)" size="sm"
                                            :disabled="product.stock === 0 || !productQuantities[product.id] || productQuantities[product.id] <= 0"
                                            class="h-9 px-3">
                                            <Icon name="ShoppingCart" class="w-4 h-4 mr-1" />
                                            Add
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Enhanced Pagination -->
            <div v-if="products.length > 0" class="flex items-center justify-between p-4 border-t bg-muted/20">
                <div class="flex items-center gap-4">
                    <div class="text-sm text-muted-foreground">
                        Showing <span class="font-medium">{{ pagination.from || 0 }}</span> to
                        <span class="font-medium">{{ pagination.to || 0 }}</span> of
                        <span class="font-medium">{{ pagination.total }}</span> products
                    </div>
                    <div class="text-xs text-muted-foreground">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </div>
                </div>

                <Pagination v-slot="{ page: internalPage }" :items-per-page="pagination.per_page"
                    :total="pagination.last_page" :page="pagination.current_page" @page-change="onPageChange">
                    <PaginationContent v-slot="{ items: pages }">
                        <PaginationPrevious @click="onPageChange(internalPage - 1)"
                            :disabled="pagination.current_page <= 1" />
                        <template v-for="(item, idx) in pages" :key="idx">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                :is-active="item.value === internalPage" @click="onPageChange(item.value)">
                                {{ item.value }}
                            </PaginationItem>
                        </template>
                        <PaginationEllipsis :index="4" v-if="pagination.last_page >= 4" />
                        <PaginationNext @click="onPageChange(internalPage + 1)"
                            :disabled="pagination.current_page >= pagination.last_page" />
                    </PaginationContent>
                </Pagination>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between p-6 border-t bg-background">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Icon name="Info" class="w-4 h-4" />
                    <span>Use Enter key in quantity field to quickly add products</span>
                </div>
                <div class="flex items-center gap-3">
                    <Button @click="clearSearch" variant="ghost" size="sm">
                        <Icon name="RotateCcw" class="w-4 h-4 mr-2" />
                        Reset All
                    </Button>
                    <Button @click="closeModal" variant="outline">
                        <Icon name="X" class="w-4 h-4 mr-2" />
                        Close
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, reactive } from 'vue'
import { storeToRefs } from 'pinia'
import { useProductStore } from '../../../stores/products'
import type { Product } from '../../../types/pos'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '../../../components/ui/dialog'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../../../components/ui/select'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '../../../components/ui/pagination'
import { Input } from '../../../components/ui/input'
import { Button } from '../../../components/ui/button'
import Icon from '../../../components/Icon.vue'
import { toast } from 'vue-sonner'

interface Props {
    open: boolean
}

interface Emits {
    (e: 'update:open', value: boolean): void
    (e: 'product-selected', product: Product, quantity: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Store
const productStore = useProductStore()
const { products, isLoading, error, pagination, filters } = storeToRefs(productStore)

// Local state
const isOpen = ref(props.open)
const productQuantities = reactive<Record<number, number>>({})

// Initialize quantities for all products
const initializeQuantities = () => {
    products.value.forEach(product => {
        if (!(product.id in productQuantities)) {
            productQuantities[product.id] = 1
        }
    })
}

// Methods
const search = async () => {
    try {
        filters.value.current_page = 1
        await productStore.fetchProducts()
        initializeQuantities()
    } catch {
        toast.error('Error searching products')
    }
}

const clearSearch = async () => {
    try {
        await productStore.resetFilters()
        await productStore.fetchProducts()
        initializeQuantities()
    } catch {
        toast.error('Error loading products')
    }
}

const changePageSize = async () => {
    try {
        filters.value.current_page = 1
        await productStore.fetchProducts()
        initializeQuantities()
    } catch {
        toast.error('Error changing page size')
    }
}

const onPageChange = async (page: number) => {
    if (page < 1 || page > pagination.value.last_page) return

    try {
        await productStore.loadPage(page)
        initializeQuantities()
    } catch {
        toast.error('Error loading page')
    }
}

const addProduct = (product: Product) => {
    const quantity = productQuantities[product.id] || 1

    if (quantity > product.stock) {
        toast.error(`Cannot add ${quantity} items. Only ${product.stock} available.`)
        return
    }

    if (quantity <= 0) {
        toast.error('Quantity must be greater than 0')
        return
    }

    emit('product-selected', product, quantity)
    toast.success(`${product.name} added to cart`)

    // Reset quantity to 1
    productQuantities[product.id] = 1
}

const closeModal = () => {
    isOpen.value = false
    emit('update:open', false)
}

// Watch for prop changes
watch(() => props.open, (newValue) => {
    isOpen.value = newValue
    if (newValue && products.value.length === 0) {
        // Load initial products when modal opens
        productStore.fetchProducts()
    }
})

watch(isOpen, (newValue) => {
    if (!newValue) {
        emit('update:open', false)
    }
})

// Load products when component mounts
onMounted(() => {
    if (props.open && products.value.length === 0) {
        productStore.fetchProducts()
    }
})

// Watch for products changes to initialize quantities
watch(products, () => {
    initializeQuantities()
}, { immediate: true })
</script>

<style scoped>
/* Forzar el ancho completo del modal */
:deep([data-radix-dialog-content]) {
    max-width: 98vw !important;
    width: 98vw !important;
    height: 95vh !important;
    max-height: 95vh !important;
}
</style>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent
            class="!max-w-none !w-[98vw] md:!w-[90vw] lg:!w-[88vw] !h-[95vh] md:!h-[85vh] lg:!h-[90vh] !max-h-none flex flex-col p-0">
            <!-- Compact Header with Search -->
            <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                    <div>
                        <DialogTitle class="text-base md:text-xl font-semibold">Product Catalog</DialogTitle>
                        <DialogDescription class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                            Browse and add products to your cart
                        </DialogDescription>
                    </div>
                    <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                        <Icon name="Package" class="w-3 h-3 md:w-4 md:h-4" />
                        <span v-if="pagination.total">{{ pagination.total }} items</span>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <Icon name="Search"
                            class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 md:w-4 md:h-4 text-muted-foreground" />
                        <Input v-model="filters.search" placeholder="Search products..."
                            class="pl-7 md:pl-10 h-8 md:h-11 text-sm" @keyup.enter="search" />
                    </div>
                    <Button @click="search" :disabled="isLoading" size="sm"
                        class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                        <Icon name="Search" class="w-3 h-3 md:w-4 md:h-4" />
                    </Button>
                    <!-- Collapsible Filters Button for Mobile -->
                    <Button @click="showFilters = !showFilters" variant="outline" size="sm"
                        class="h-8 md:hidden px-2 text-xs">
                        <Icon name="Filter" class="w-3 h-3 mr-1" />
                        <span>{{ showFilters ? 'Hide' : 'Show' }}</span>
                    </Button>
                    <Button v-if="filters.search || filters.sort_by !== 'name' || filters.sort_dir !== 'asc'"
                        @click="clearSearch" variant="outline" size="sm"
                        class="h-8 md:h-11 px-2 md:px-4 text-xs md:text-sm">
                        <Icon name="RotateCcw" class="w-3 h-3 md:w-4 md:h-4" />
                    </Button>
                </div>
            </div>

            <!-- Collapsible Filters Toolbar -->
            <div v-show="showFilters || !isMobile"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 md:gap-4 p-2 md:p-4 bg-background border-b"
                :class="{ 'border-b-0': !showFilters && isMobile }">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 md:gap-3">
                    <span class="text-xs md:text-sm font-medium text-muted-foreground hidden sm:inline">Sort:</span>
                    <div class="flex gap-2">
                        <Select v-model="filters.sort_by" @update:modelValue="search">
                            <SelectTrigger class="w-full sm:w-36 md:w-48 h-7 md:h-9 text-xs md:text-sm">
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

                <div class="flex flex-col sm:flex-row sm:items-center gap-2 md:gap-3">
                    <span class="text-xs md:text-sm font-medium text-muted-foreground hidden sm:inline">Show:</span>
                    <Select v-model="filters.per_page" @update:modelValue="changePageSize">
                        <SelectTrigger class="w-full sm:w-24 md:w-32 h-7 md:h-9 text-xs md:text-sm">
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
                <div v-if="isLoading" class="flex items-center justify-center h-48 md:h-64">
                    <div class="flex flex-col items-center gap-2 md:gap-3">
                        <Icon name="Loader2" class="w-6 h-6 md:w-8 md:h-8 animate-spin text-primary" />
                        <p class="text-xs md:text-sm text-muted-foreground">Loading products...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center h-48 md:h-64">
                    <div class="flex flex-col items-center gap-2 md:gap-3 text-center">
                        <Icon name="AlertCircle" class="w-6 h-6 md:w-8 md:h-8 text-destructive" />
                        <div>
                            <p class="font-medium text-destructive text-sm md:text-base">Error loading products</p>
                            <p class="text-xs md:text-sm text-muted-foreground">{{ error }}</p>
                        </div>
                        <Button @click="search" variant="outline" size="sm" class="text-xs md:text-sm">
                            <Icon name="RefreshCw" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                            Try Again
                        </Button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="products.length === 0" class="flex items-center justify-center h-48 md:h-64">
                    <div class="flex flex-col items-center gap-2 md:gap-3 text-center">
                        <Icon name="Package" class="w-8 h-8 md:w-12 md:h-12 text-muted-foreground/50" />
                        <div>
                            <p class="font-medium text-muted-foreground text-sm md:text-base">No products found</p>
                            <p class="text-xs md:text-sm text-muted-foreground">Try adjusting your search or filters</p>
                        </div>
                        <Button @click="clearSearch" variant="outline" size="sm" class="text-xs md:text-sm">
                            <Icon name="RotateCcw" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                            Clear Filters
                        </Button>
                    </div>
                </div>

                <!-- Products Grid/Table -->
                <div v-else class="flex-1 overflow-hidden">
                    <!-- Mobile Cards View -->
                    <div class="block md:hidden h-full overflow-y-auto px-2 py-1">
                        <div class="space-y-2">
                            <div v-for="product in products" :key="product.id"
                                class="border rounded-lg p-3 bg-card hover:bg-accent/50 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-sm mb-1 line-clamp-2">{{ product.name }}</h4>
                                        <div class="flex items-center gap-2 text-xs text-muted-foreground mb-1">
                                            <span class="font-mono bg-muted px-1 py-0.5 rounded text-xs">{{ product.sku
                                                }}</span>
                                            <span class="text-xs">{{ product.unit_measure }}</span>
                                        </div>
                                        <div class="text-xs text-muted-foreground">{{ product.category }}</div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1 ml-2">
                                        <div class="text-base font-bold text-primary">${{
                                            Number(product.price).toFixed(2) }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            <span :class="[
                                                product.stock <= 10 ? 'text-destructive font-medium' :
                                                    product.stock <= 50 ? 'text-orange-600 font-medium' :
                                                        'text-green-600 font-medium'
                                            ]">{{ product.stock }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1 bg-muted rounded p-1">
                                        <Button size="sm" variant="ghost" @click="decreaseQuantity(product.id)"
                                            :disabled="(productQuantities[product.id] || 1) <= 1" class="h-5 w-5 p-0">
                                            <Icon name="Minus" class="w-3 h-3" />
                                        </Button>
                                        <span class="w-6 text-center text-xs font-medium">{{
                                            productQuantities[product.id] || 1 }}</span>
                                        <Button size="sm" variant="ghost"
                                            @click="increaseQuantity(product.id, product.stock)"
                                            :disabled="(productQuantities[product.id] || 1) >= product.stock"
                                            class="h-5 w-5 p-0">
                                            <Icon name="Plus" class="w-3 h-3" />
                                        </Button>
                                    </div>

                                    <Button @click="addProduct(product)"
                                        :disabled="product.stock === 0 || (productQuantities[product.id] || 1) > product.stock"
                                        size="sm" class="flex-1 text-xs h-7">
                                        <Icon name="ShoppingCart" class="w-3 h-3 mr-1" />
                                        Add
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block h-full overflow-y-auto">
                        <table class="w-full" ref="tableRef">
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
                                <tr v-for="(product, idx) in products" :key="product.id"
                                    class="border-b hover:bg-muted/50 transition-colors group" :tabindex="0"
                                    :class="{ 'ring-2 ring-primary ring-offset-2': selectedIndex === idx }"
                                    @click="selectedIndex = idx" @focus="selectedIndex = idx">
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

            <!-- Compact Pagination -->
            <div v-if="products.length > 0"
                class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                    <div class="text-xs text-muted-foreground">
                        <span class="font-medium">{{ pagination.from || 0 }}-{{ pagination.to || 0 }}</span>
                        <span class="hidden xs:inline"> of </span>
                        <span class="xs:hidden">/</span>
                        <span class="font-medium">{{ pagination.total }}</span>
                    </div>
                    <div class="text-xs text-muted-foreground">
                        Page {{ pagination.current_page }}/{{ pagination.last_page }}
                    </div>
                </div>

                <!-- Simplified Mobile Pagination -->
                <div class="flex md:hidden items-center justify-center gap-2">
                    <Button @click="onPageChange(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                        size="sm" variant="outline" class="h-7 w-7 p-0">
                        <Icon name="ChevronLeft" class="w-3 h-3" />
                    </Button>
                    <span class="text-xs font-medium px-2">
                        {{ pagination.current_page }}
                    </span>
                    <Button @click="onPageChange(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page" size="sm" variant="outline"
                        class="h-7 w-7 p-0">
                        <Icon name="ChevronRight" class="w-3 h-3" />
                    </Button>
                </div>

                <!-- Full Desktop Pagination -->
                <Pagination v-slot="{ page: internalPage }" :items-per-page="pagination.per_page"
                    :total="pagination.total" :page="pagination.current_page" @page-change="onPageChange"
                    class="hidden md:flex">
                    <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                        <PaginationPrevious @click="onPageChange(internalPage - 1)"
                            :disabled="pagination.current_page <= 1" class="h-8 md:h-9" />
                        <template v-for="(item, idx) in pages" :key="idx">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                :is-active="item.value === internalPage" @click="onPageChange(item.value)"
                                class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                {{ item.value }}
                            </PaginationItem>
                        </template>
                        <PaginationEllipsis :index="4" v-if="pagination.last_page >= 4" class="h-8 md:h-9" />
                        <PaginationNext @click="onPageChange(internalPage + 1)"
                            :disabled="pagination.current_page >= pagination.last_page" class="h-8 md:h-9" />
                    </PaginationContent>
                </Pagination>
            </div>

            <!-- Compact Footer -->
            <div
                class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0 p-2 md:p-6 border-t bg-background">
                <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                    <Icon name="Info" class="w-3 h-3 md:w-4 md:h-4" />
                    <span class="hidden sm:inline">Use Enter key in quantity field to quickly add products</span>
                    <span class="sm:hidden">Press Enter to add</span>
                </div>
                <div class="flex items-center gap-2">
                    <Button @click="clearSearch" variant="ghost" size="sm" class="text-xs md:text-sm h-7 ">
                        <Icon name="RotateCcw" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                        <span class="hidden xs:inline">Reset</span>
                    </Button>
                    <Button @click="closeModal" variant="outline" size="sm" class="text-xs md:text-sm h-7">
                        <Icon name="X" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                        Close
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, reactive, nextTick } from 'vue'
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
const showFilters = ref(false)
const isMobile = ref(window.innerWidth < 768)
const selectedIndex = ref(0) // index of the selected product
const tableRef = ref<HTMLElement | null>(null)

// Handle window resize for mobile detection
const handleResize = () => {
    isMobile.value = window.innerWidth < 768
    if (!isMobile.value) {
        showFilters.value = true // Always show filters on desktop
    }
}

// Initialize quantities for all products
const initializeQuantities = () => {
    products.value.forEach(product => {
        if (!(product.id in productQuantities)) {
            productQuantities[product.id] = 1
        }
    })
}

const focusRow = async (idx: number) => {
    await nextTick()
    const rows = tableRef.value?.querySelectorAll('tbody tr')
    if (rows && rows[idx]) {
        (rows[idx] as HTMLElement).focus()
    }
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

    // Reset quantity to 1
    productQuantities[product.id] = 1
}

const decreaseQuantity = (productId: number) => {
    if ((productQuantities[productId] || 1) > 1) {
        productQuantities[productId] = (productQuantities[productId] || 1) - 1
    }
}

const increaseQuantity = (productId: number, maxStock: number) => {
    const current = productQuantities[productId] || 1
    if (current < maxStock) {
        productQuantities[productId] = current + 1
    }
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

    // Setup resize listener
    window.addEventListener('resize', handleResize)
    handleResize() // Initial check

    window.addEventListener('keydown', handleKeydown)
})

// Cleanup on unmount
onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
    window.removeEventListener('keydown', handleKeydown)
})

// Watch for products changes to initialize quantities
watch(products, () => {
    initializeQuantities()
    selectedIndex.value = 0
}, { immediate: true })

const handleKeydown = async (e: KeyboardEvent) => {
    if (!isOpen.value || products.value.length === 0) return
    if (window.innerWidth < 768) return // Solo desktop
    const max = products.value.length - 1
    if (["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight", "Enter"].includes(e.key)) {
        e.preventDefault()
    }
    if (e.key === "ArrowUp") {
        if (selectedIndex.value > 0) {
            selectedIndex.value--
            await focusRow(selectedIndex.value)
        } else if (pagination.value.current_page > 1) {
            await onPageChange(pagination.value.current_page - 1)
            selectedIndex.value = products.value.length - 1
            await focusRow(selectedIndex.value)
        }
    } else if (e.key === "ArrowDown") {
        if (selectedIndex.value < max) {
            selectedIndex.value++
            await focusRow(selectedIndex.value)
        } else if (pagination.value.current_page < pagination.value.last_page) {
            await onPageChange(pagination.value.current_page + 1)
            selectedIndex.value = 0
            await focusRow(selectedIndex.value)
        }
    } else if (e.key === "ArrowLeft") {
        const prod = products.value[selectedIndex.value]
        decreaseQuantity(prod.id)
    } else if (e.key === "ArrowRight") {
        const prod = products.value[selectedIndex.value]
        increaseQuantity(prod.id, prod.stock)
    } else if (e.key === "Enter") {
        const prod = products.value[selectedIndex.value]
        addProduct(prod)
    }
}
</script>

<style scoped>
/* Forzar el ancho completo del modal */
:deep([data-radix-dialog-content]) {
    max-width: 98vw !important;
    width: 98vw !important;
    height: 95vh !important;
    max-height: 95vh !important;
}

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

/* Estados de filtros colapsables */
.filter-transition {
    transition: all 0.3s ease-in-out;
}
</style>

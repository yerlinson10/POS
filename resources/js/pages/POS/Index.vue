<template>
    <Head title="POS - Point of Sale" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">
                <!-- Product Selection Section -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Search Bar -->
                    <div class="relative">
                        <Input
                            v-model="productSearchQuery"
                            placeholder="Search products by name or SKU..."
                            class="pl-10"
                            @input="debouncedSearchProducts"
                        />
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                        <Card
                            v-for="product in filteredProducts"
                            :key="product.id"
                            class="cursor-pointer hover:shadow-lg transition-shadow"
                            @click="addToCart(product)"
                        >
                            <CardContent class="p-4">
                                <div class="space-y-2">
                                    <div class="font-semibold text-sm truncate">{{ product.name }}</div>
                                    <div class="text-xs text-gray-500">SKU: {{ product.sku }}</div>
                                    <div class="text-xs text-gray-500">Stock: {{ product.stock }}</div>
                                    <div class="text-lg font-bold text-green-600">${{ Number(product.price).toFixed(2) }}</div>
                                    <div class="text-xs text-gray-400">{{ product.category?.name }}</div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Cart and Checkout Section -->
                <div class="space-y-4">
                    <!-- Customer Selection -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Customer</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="selectedCustomer" class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-semibold">{{ selectedCustomer.first_name }} {{ selectedCustomer.last_name }}</div>
                                        <div class="text-sm text-gray-500">{{ selectedCustomer.email }}</div>
                                        <div class="text-sm text-gray-500" v-if="selectedCustomer.phone">{{ selectedCustomer.phone }}</div>
                                    </div>
                                    <Button variant="outline" size="sm" @click="clearCustomer">
                                        <X class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                            <div v-else>
                                <div class="relative">
                                    <Input
                                        v-model="customerSearchQuery"
                                        placeholder="Search customers..."
                                        class="pl-10"
                                        @input="debouncedSearchCustomers"
                                    />
                                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                                </div>
                                <div v-if="customerSearchResults.length > 0" class="mt-2 max-h-32 overflow-y-auto">
                                    <div
                                        v-for="customer in customerSearchResults"
                                        :key="customer.id"
                                        class="p-2 hover:bg-gray-100 cursor-pointer rounded"
                                        @click="selectCustomer(customer)"
                                    >
                                        <div class="font-semibold text-sm">{{ customer.first_name }} {{ customer.last_name }}</div>
                                        <div class="text-xs text-gray-500">{{ customer.email }}</div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Cart -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex justify-between items-center">
                                <span>Cart ({{ cartItemsCount }})</span>
                                <Button
                                    v-if="!cartIsEmpty"
                                    variant="outline"
                                    size="sm"
                                    @click="clearCart"
                                >
                                    Clear
                                </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="cartIsEmpty" class="text-center text-gray-500 py-8">
                                Cart is empty
                            </div>
                            <div v-else class="space-y-3 max-h-64 overflow-y-auto">
                                <div
                                    v-for="item in cart"
                                    :key="item.product.id"
                                    class="flex items-center justify-between p-2 border rounded"
                                >
                                    <div class="flex-1">
                                        <div class="font-semibold text-sm">{{ item.product.name }}</div>
                                        <div class="text-xs text-gray-500">${{ Number(item.unit_price).toFixed(2) }} each</div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="decrementQuantity(item.product.id)"
                                        >
                                            <Minus class="w-3 h-3" />
                                        </Button>
                                        <Input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            :max="item.product.stock"
                                            class="w-16 text-center"
                                            @change="updateItemQuantity(item.product.id, item.quantity)"
                                        />
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="incrementQuantity(item.product.id)"
                                        >
                                            <Plus class="w-3 h-3" />
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="removeFromCart(item.product.id)"
                                        >
                                            <Trash2 class="w-3 h-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Total and Checkout -->
                    <Card>
                        <CardContent class="p-4">
                            <div class="space-y-4">
                                <div class="text-2xl font-bold text-center">
                                    Total: ${{ Number(cartTotal).toFixed(2) }}
                                </div>

                                <div class="space-y-2">
                                    <Label for="amountPaid">Amount Paid</Label>
                                    <Input
                                        id="amountPaid"
                                        v-model.number="amountPaid"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                    />
                                </div>

                                <div v-if="amountPaid >= cartTotal && amountPaid > 0" class="text-center">
                                    <div class="text-lg font-semibold text-green-600">
                                        Change: ${{ Number(amountPaid - cartTotal).toFixed(2) }}
                                    </div>
                                </div>

                                <Button
                                    class="w-full"
                                    size="lg"
                                    :disabled="!canProcessSale || amountPaid < cartTotal"
                                    @click="processSale"
                                >
                                    <div v-if="isProcessingSale" class="flex items-center">
                                        <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                        Processing...
                                    </div>
                                    <div v-else>
                                        Process Sale
                                    </div>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <Dialog v-model:open="showSuccessModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Sale Completed Successfully!</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="text-center">
                        <CheckCircle class="w-16 h-16 text-green-500 mx-auto mb-4" />
                        <div class="text-lg font-semibold">Invoice #{{ lastInvoice?.id }}</div>
                        <div class="text-2xl font-bold">Total: ${{ Number(lastInvoice?.total_amount || 0).toFixed(2) }}</div>
                        <div class="text-lg text-green-600">Change: ${{ Number(lastChange || 0).toFixed(2) }}</div>
                    </div>
                    <div class="flex space-x-2">
                        <Button
                            variant="outline"
                            class="flex-1"
                            @click="closeSuccessModal"
                        >
                            New Sale
                        </Button>
                        <Button
                            class="flex-1"
                            @click="viewInvoice"
                        >
                            View Invoice
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'

import { Search, X, Minus, Plus, Trash2, Loader2, CheckCircle } from 'lucide-vue-next'

import { usePosStore } from '@/stores/posStore'
import type { Product, Customer } from '@/stores/posStore'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'POS', href: '/pos' },
]

interface Props {
    customers: Customer[]
    products: Product[]
    stats: {
        today_sales: number
        month_sales: number
        pending_invoices: number
        total_invoices: number
    }
}

const props = defineProps<Props>()

// Pinia store
const posStore = usePosStore()

// Reactive data
const productSearchQuery = ref('')
const customerSearchQuery = ref('')
const customerSearchResults = ref<Customer[]>([])
const amountPaid = ref(0)
const showSuccessModal = ref(false)
const lastInvoice = ref<any>(null)
const lastChange = ref(0)

// Computed properties from store
const {
    cart,
    selectedCustomer,
    cartTotal,
    cartItemsCount,
    cartIsEmpty,
    canProcessSale,
    isProcessingSale,
    products: storeProducts
} = posStore

// Utility functions
const debounce = <T extends (...args: any[]) => any>(func: T, wait: number): ((...args: Parameters<T>) => void) => {
    let timeout: number | null = null
    return (...args: Parameters<T>) => {
        if (timeout) clearTimeout(timeout)
        timeout = setTimeout(() => func(...args), wait) as unknown as number
    }
}

// Local computed
const filteredProducts = computed(() => {
    if (!productSearchQuery.value) {
        return props.products
    }
    return storeProducts.filter(product =>
        product.name.toLowerCase().includes(productSearchQuery.value.toLowerCase()) ||
        product.sku.toLowerCase().includes(productSearchQuery.value.toLowerCase())
    )
})

// Debounced search functions
const debouncedSearchProducts = debounce(async () => {
    if (productSearchQuery.value) {
        await posStore.searchProducts(productSearchQuery.value)
    }
}, 300)

const debouncedSearchCustomers = debounce(async () => {
    if (customerSearchQuery.value) {
        await posStore.searchCustomers(customerSearchQuery.value)
        customerSearchResults.value = posStore.customers
    } else {
        customerSearchResults.value = []
    }
}, 300)

// Methods
const addToCart = (product: Product) => {
    try {
        posStore.addToCart(product)
        toast.success(`${product.name} added to cart`)
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Failed to add product to cart')
    }
}

const removeFromCart = (productId: number) => {
    posStore.removeFromCart(productId)
    toast.success('Product removed from cart')
}

const incrementQuantity = (productId: number) => {
    const item = cart.find(item => item.product.id === productId)
    if (item) {
        try {
            posStore.updateQuantity(productId, item.quantity + 1)
        } catch (error) {
            toast.error(error instanceof Error ? error.message : 'Failed to update quantity')
        }
    }
}

const decrementQuantity = (productId: number) => {
    const item = cart.find(item => item.product.id === productId)
    if (item) {
        try {
            posStore.updateQuantity(productId, item.quantity - 1)
        } catch (error) {
            toast.error(error instanceof Error ? error.message : 'Failed to update quantity')
        }
    }
}

const updateItemQuantity = (productId: number, quantity: number) => {
    try {
        posStore.updateQuantity(productId, quantity)
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Failed to update quantity')
    }
}

const clearCart = () => {
    posStore.clearCart()
    amountPaid.value = 0
    toast.success('Cart cleared')
}

const selectCustomer = (customer: Customer) => {
    posStore.setCustomer(customer)
    customerSearchQuery.value = ''
    customerSearchResults.value = []
    toast.success(`Customer ${customer.first_name} ${customer.last_name} selected`)
}

const clearCustomer = () => {
    posStore.clearCustomer()
    toast.success('Customer cleared')
}

const processSale = async () => {
    try {
        const result = await posStore.processSale('cash', amountPaid.value)

        lastInvoice.value = result.invoice
        lastChange.value = result.change
        showSuccessModal.value = true
        amountPaid.value = 0

        toast.success('Sale processed successfully!')
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Failed to process sale')
    }
}

const closeSuccessModal = () => {
    showSuccessModal.value = false
    lastInvoice.value = null
    lastChange.value = 0
}

const viewInvoice = () => {
    if (lastInvoice.value) {
        window.open(`/invoices/${lastInvoice.value.id}`, '_blank')
    }
    closeSuccessModal()
}

// Initialize store with data
onMounted(() => {
    posStore.setProducts(props.products)
    posStore.setCustomers(props.customers)
})
</script>

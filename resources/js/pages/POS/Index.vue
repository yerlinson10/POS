<template>
    <AppLayout title="Point of Sale">
        <div class="flex flex-col lg:flex-row h-auto lg:h-[calc(100vh-4rem)] gap-4 p-2 md:p-4">
            <!-- Left Panel - Cart and Products -->
            <div class="w-full lg:w-2/3 flex flex-col gap-4">
                <!-- Cart Section -->
                <Card class="flex-1 p-3 md:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 md:mb-6 gap-3 sm:gap-0">
                        <div>
                            <h3 class="text-lg md:text-xl font-semibold">Shopping Cart</h3>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} selected
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button @click="showProductModal = true"
                                class="h-9 md:h-10 cursor-pointer text-xs md:text-sm">
                                <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span class="hidden sm:inline">Add Products</span>
                                <span class="sm:hidden">Add</span>
                            </Button>
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button variant="destructive" size="sm" :disabled="!cart.length"
                                        class="h-9 md:h-10 cursor-pointer text-xs md:text-sm">
                                        <Icon name="Trash2" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                        <span class="hidden sm:inline">Clear Cart</span>
                                        <span class="sm:hidden">Clear</span>
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Clear Shopping Cart?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action will remove all {{ itemCount }} item{{ itemCount !== 1 ? 's' :
                                                '' }} from your cart.
                                            This action cannot be undone.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                                        <AlertDialogAction variant="destructive"
                                            class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]"
                                            @click="clearCart">
                                            Clear Cart
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div class="space-y-2 md:space-y-3 mb-4 md:mb-6 flex-1 overflow-y-auto"
                        style="max-height: calc(100vh - 16rem); min-height: 200px;">
                        <!-- Empty Cart State -->
                        <div v-if="!cart.length" class="text-center py-8 md:py-16">
                            <div class="flex flex-col items-center gap-3 md:gap-4">
                                <Icon name="ShoppingCart" class="w-12 h-12 md:w-16 md:h-16 text-muted-foreground/50" />
                                <div>
                                    <h4 class="font-medium text-base md:text-lg mb-2">Your cart is empty</h4>
                                    <p class="text-muted-foreground mb-3 md:mb-4 text-sm">Add products to get started
                                    </p>
                                    <Button @click="showProductModal = true" class="cursor-pointer">
                                        <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                        Browse Products
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Items List -->
                        <div v-for="item in cart" :key="item.product_id"
                            class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-3 md:p-4 border rounded-lg bg-card hover:bg-accent/50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm md:text-base mb-1">{{ item.product_name }}</div>
                                <div class="text-xs md:text-sm text-muted-foreground mb-1 md:mb-2">{{ item.product_sku
                                    }}</div>
                                <div class="text-xs md:text-sm font-medium text-primary">
                                    ${{ Number(item.unit_price).toFixed(2) }} per unit
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3">
                                <div class="flex items-center gap-2 bg-muted rounded-lg p-1">
                                    <Button size="sm" variant="ghost"
                                        @click="updateQuantity(item.product_id, item.quantity - 1)"
                                        :disabled="item.quantity <= 1" class="h-6 w-6 md:h-8 md:w-8 p-0 cursor-pointer">
                                        <Icon name="Minus" class="w-2 h-2 md:w-3 md:h-3" />
                                    </Button>
                                    <span class="w-8 md:w-12 text-center text-xs md:text-sm font-medium">{{
                                        item.quantity }}</span>
                                    <Button size="sm" variant="ghost"
                                        @click="updateQuantity(item.product_id, item.quantity + 1)"
                                        :disabled="item.quantity >= item.available_stock"
                                        class="h-6 w-6 md:h-8 md:w-8 p-0 cursor-pointer">
                                        <Icon name="Plus" class="w-2 h-2 md:w-3 md:h-3" />
                                    </Button>
                                </div>

                                <div class="text-right min-w-[60px] md:min-w-[80px]">
                                    <div class="text-base md:text-lg font-semibold">${{
                                        Number(item.line_total).toFixed(2) }}</div>
                                </div>

                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button variant="ghost" size="sm"
                                            class="h-6 w-6 md:h-8 md:w-8 p-0 text-red-600 hover:text-red-700 cursor-pointer">
                                            <Icon name="Trash2" class="w-4 h-4" />
                                        </Button>
                                        <!-- <Button size="sm" variant="ghost"
                                            class="h-6 w-6 md:h-8 md:w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10 ">
                                            <Icon name="X" class="w-3 h-3 md:w-4 md:h-4" />
                                        </Button> -->
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Remove Product?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Are you sure you want to remove "{{ item.product_name }}" from your
                                                cart?
                                                This action cannot be undone.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                                            <AlertDialogAction variant="destructive"
                                                class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]"
                                                @click="removeFromCart(item.product_id)">
                                                Remove Product
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Search Section (Optional) -->
                    <div v-if="cart.length > 0" class="border-t pt-3 md:pt-4">
                        <div class="flex gap-2">
                            <Input v-model="quickSearchTerm" placeholder="Quick add products..." class="flex-1 text-sm"
                                @keyup.enter="performQuickSearch" />
                            <Button @click="performQuickSearch" variant="outline" size="sm" class="px-2 md:px-3">
                                <Icon name="Search" class="w-3 h-3 md:w-4 md:h-4" />
                            </Button>
                        </div>

                        <!-- Quick Search Results -->
                        <div v-if="quickSearchResults.length"
                            class="mt-2 md:mt-3 space-y-1 md:space-y-2 max-h-24 md:max-h-32 overflow-y-auto">
                            <div v-for="product in quickSearchResults" :key="product.id"
                                class="flex items-center justify-between p-2 border rounded hover:bg-accent/50 cursor-pointer text-xs md:text-sm"
                                @click="addToCart(product)">
                                <div class="flex-1">
                                    <div class="font-medium">{{ product.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ product.sku }} • ${{
                                        Number(product.price).toFixed(2) }}</div>
                                </div>
                                <Button size="sm" variant="ghost" class="h-5 w-5 md:h-6 md:w-6 p-0">
                                    <Icon name="Plus" class="w-2 h-2 md:w-3 md:h-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Right Panel - Customer and Checkout -->
            <div class="w-full lg:w-1/3 flex flex-col gap-4">
                <!-- Customer Selection -->
                <Card class="p-3 md:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 md:mb-4 gap-2 sm:gap-0">
                        <h3 class="text-base md:text-lg font-medium">Customer</h3>
                        <Button @click="showNewCustomerDialog = true" variant="outline" size="sm"
                            class="text-xs md:text-sm">
                            <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                            <span class="hidden sm:inline">New Customer</span>
                            <span class="sm:hidden">New</span>
                        </Button>
                    </div>
                    <CustomerSelector v-model="selectedCustomer" @customer-selected="handleCustomerSelected" />
                </Card>

                <!-- Checkout Section -->
                <Card class="flex-1 p-3 md:p-4">
                    <h3 class="text-base md:text-lg font-medium mb-3 md:mb-4">Order Summary</h3>

                    <!-- Invoice Status Selection -->
                    <div class="border rounded-lg p-3 md:p-4 mb-3 md:mb-4">
                        <InvoiceStatusSelector v-model="invoiceStatus" />
                    </div>

                    <!-- Discount Section -->
                    <div class="border rounded-lg p-3 md:p-4 mb-3 md:mb-4">
                        <div class="flex items-center justify-between mb-2 md:mb-3">
                            <span class="text-xs md:text-sm font-medium">Apply Discount</span>
                            <Button v-if="discountType" @click="clearDiscount" variant="ghost" size="sm"
                                class="cursor-pointer h-6 w-6 md:h-8 md:w-8 p-0">
                                <Icon name="X" class="w-3 h-3 md:w-4 md:h-4" />
                            </Button>
                        </div>

                        <div v-if="!discountType" class="flex flex-col sm:flex-row gap-2">
                            <Button @click="showDiscountDialog('percentage')" variant="outline" size="sm"
                                class="flex-1 cursor-pointer">
                                <Icon name="Percent" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Percentage</span>
                            </Button>
                            <Button @click="showDiscountDialog('fixed')" variant="outline" size="sm"
                                class="flex-1 cursor-pointer">
                                <Icon name="DollarSign" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Fixed Amount</span>
                            </Button>
                        </div>

                        <div v-else class="bg-green-50 dark:bg-green-900/20 p-2 md:p-3 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-xs md:text-sm font-medium text-green-800 dark:text-green-400">
                                    {{ discountType === 'percentage' ? `${Number(discountValue)}%` :
                                        `$${Number(discountValue).toFixed(2)}` }} Discount Applied
                                </span>
                                <span class="text-xs md:text-sm font-semibold text-green-800 dark:text-green-400">
                                    -${{ Number(discountAmount).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 md:space-y-3 border rounded-lg p-3 md:p-4 mb-4 md:mb-6">
                        <div class="flex justify-between text-xs md:text-sm">
                            <span>Subtotal:</span>
                            <span class="font-medium">${{ Number(subtotal).toFixed(2) }}</span>
                        </div>
                        <div v-if="discountAmount > 0"
                            class="flex justify-between text-xs md:text-sm text-green-600 dark:text-green-400">
                            <span>Discount:</span>
                            <span class="font-medium">-${{ Number(discountAmount).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg md:text-xl font-bold border-t pt-2 md:pt-3">
                            <span>Total:</span>
                            <span class="text-primary">${{ Number(total).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <Button @click="processCheckout" class="w-full h-10 md:h-12 cursor-pointer text-sm md:text-base"
                        size="lg" :disabled="!canProcessSale" :loading="isProcessingSale">
                        <Icon :name="getCheckoutIcon" class="w-4 h-4 md:w-5 md:h-5 mr-1 md:mr-2" />
                        <span class="hidden sm:inline">{{ isProcessingSale ? getProcessingText : getCheckoutText
                            }}</span>
                        <span class="sm:hidden">{{ isProcessingSale ? 'Processing...' : getCheckoutButtonShort }}</span>
                    </Button>

                    <!-- Additional Info -->
                    <div v-if="cart.length > 0" class="mt-3 md:mt-4 text-xs text-muted-foreground text-center">
                        <p class="hidden md:block">{{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} • Last updated:
                            {{ new
                                Date().toLocaleTimeString() }}</p>
                        <p class="md:hidden">{{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }}</p>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Product Selection Modal -->
        <ProductSelectionModal v-model:open="showProductModal" @product-selected="addToCart" />

        <!-- New Customer Dialog -->
        <NewCustomerDialog v-model:open="showNewCustomerDialog" @customer-created="handleCustomerCreated" />

        <!-- Discount Dialog -->
        <DiscountDialog v-model:open="showDiscountDialogRef" v-model:type="discountDialogType"
            @discount-applied="handleDiscountApplied" />

        <!-- Sale Success Dialog -->
        <SaleSuccessDialog v-model:open="showSaleSuccessDialog" :sale="lastSale" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePOSStore } from '../../stores/pos'
import { useProductStore } from '../../stores/products'
import type { Product, Customer } from '../../types/pos'
import AppLayout from '../../layouts/AppLayout.vue'
import { Card } from '../../components/ui/card'
import { Button } from '../../components/ui/button'
import { Input } from '../../components/ui/input'
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
} from '../../components/ui/alert-dialog'
import Icon from '../../components/Icon.vue'
// Make sure the file exists at this path, or update the path if needed
import CustomerSelector from './components/CustomerSelector.vue'
import ProductSelectionModal from './components/ProductSelectionModal.vue'
import NewCustomerDialog from './components/NewCustomerDialog.vue'
import DiscountDialog from './components/DiscountDialog.vue'
import SaleSuccessDialog from './components/SaleSuccessDialog.vue'
import InvoiceStatusSelector from './components/InvoiceStatusSelector.vue'
import { toast } from 'vue-sonner'

// Stores
const posStore = usePOSStore()
const productStore = useProductStore()

// Store refs
const {
    cart,
    selectedCustomer,
    discountType,
    discountValue,
    discountAmount,
    invoiceStatus,
    subtotal,
    total,
    itemCount,
    canProcessSale,
    isProcessingSale,
    lastSale
} = storeToRefs(posStore)

// Local state
const showProductModal = ref(false)
const showNewCustomerDialog = ref(false)
const showDiscountDialogRef = ref(false)
const discountDialogType = ref<'percentage' | 'fixed'>('percentage')
const showSaleSuccessDialog = ref(false)
const quickSearchTerm = ref('')
const quickSearchResults = ref<Product[]>([])

// Auto-refresh interval for stock updates
let stockUpdateInterval: number | null = null

// Computed properties for checkout button
const getCheckoutIcon = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'CreditCard'
        case 'pending':
            return 'Clock'
        default:
            return 'CreditCard'
    }
})

const getCheckoutText = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'Process Payment'
        case 'pending':
            return 'Create Pending Invoice'
        default:
            return 'Process Sale'
    }
})

const getProcessingText = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'Processing Payment...'
        case 'pending':
            return 'Creating Invoice...'
        default:
            return 'Processing Sale...'
    }
})

const getCheckoutButtonShort = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'Pay'
        case 'pending':
            return 'Pending'
        default:
            return 'Pay'
    }
})

// Methods
const handleCustomerSelected = (customer: Customer | null) => {
    posStore.setCustomer(customer)
}

const handleCustomerCreated = (customer: Customer) => {
    posStore.setCustomer(customer)
    toast.success('Customer created successfully')
}

const addToCart = (product: Product, quantity: number = 1) => {
    try {
        posStore.addToCart(product, quantity)
        toast.success(`${product.name} added to cart`)
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Error adding to cart')
    }
}

const updateQuantity = (productId: number, quantity: number) => {
    try {
        posStore.updateCartItemQuantity(productId, quantity)
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Error updating quantity')
    }
}

const removeFromCart = (productId: number) => {
    posStore.removeFromCart(productId)
    toast.success('Item removed from cart')
}

const clearCart = () => {
    posStore.clearCart()
    toast.success('Cart cleared')
}

const showDiscountDialog = (type: 'percentage' | 'fixed') => {
    discountDialogType.value = type
    showDiscountDialogRef.value = true
}

const handleDiscountApplied = (type: 'percentage' | 'fixed', value: number) => {
    posStore.setDiscount(type, value)
    toast.success('Discount applied')
}

const clearDiscount = () => {
    posStore.clearDiscount()
    toast.success('Discount removed')
}

const processCheckout = async () => {
    try {
        await posStore.processSale()
        showSaleSuccessDialog.value = true
        toast.success('Sale processed successfully!')
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Error processing sale')
    }
}

const performQuickSearch = async () => {
    if (!quickSearchTerm.value.trim()) {
        quickSearchResults.value = []
        return
    }

    try {
        await productStore.searchProducts(quickSearchTerm.value)
        quickSearchResults.value = productStore.products.slice(0, 5) // Show top 5 results
    } catch {
        toast.error('Error searching products')
    }
}

const updateStockPrices = async () => {
    if (cart.value.length === 0) return

    try {
        const productIds = cart.value.map((item: any) => item.product_id)
        await productStore.refreshProductUpdates(productIds)

        // Update cart with new stock info
        const updates: Record<number, { stock: number, price: number }> = {}
        productIds.forEach((id: number) => {
            const product = productStore.getProductById(id)
            if (product) {
                updates[id] = { stock: product.stock, price: product.price }
            }
        })

        posStore.updateProductStock(updates)
    } catch {
        console.error('Error updating stock prices')
    }
}

// Lifecycle
onMounted(() => {
    // Set up periodic stock updates every 30 seconds
    stockUpdateInterval = setInterval(updateStockPrices, 30000)
})

onUnmounted(() => {
    if (stockUpdateInterval) {
        clearInterval(stockUpdateInterval)
    }
})

// Clear quick search when search term is empty
watch(quickSearchTerm, (newValue) => {
    if (!newValue.trim()) {
        quickSearchResults.value = []
    }
})
</script>

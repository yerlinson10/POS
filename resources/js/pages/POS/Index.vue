<template>
    <AppLayout title="Point of Sale">
        <div class="flex h-[calc(100vh-4rem)] gap-4 p-4">
            <!-- Left Panel - Cart and Products -->
            <div class="w-2/3 flex flex-col gap-4">
                <!-- Cart Section -->
                <Card class="flex-1 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold">Shopping Cart</h3>
                            <p class="text-sm text-muted-foreground">
                                {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} selected
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button @click="showProductModal = true" class="h-10 cursor-pointer">
                                <Icon name="Plus" class="w-4 h-4 mr-2" />
                                Add Products
                            </Button>
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button variant="destructive" size="sm" :disabled="!cart.length"
                                        class="h-10 cursor-pointer">
                                        <Icon name="Trash2" class="w-4 h-4 mr-2" />
                                        Clear Cart
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
                    <div class="space-y-3 mb-6 flex-1 overflow-y-auto" style="max-height: calc(100vh - 20rem);">
                        <!-- Empty Cart State -->
                        <div v-if="!cart.length" class="text-center py-16">
                            <div class="flex flex-col items-center gap-4">
                                <Icon name="ShoppingCart" class="w-16 h-16 text-muted-foreground/50" />
                                <div>
                                    <h4 class="font-medium text-lg mb-2">Your cart is empty</h4>
                                    <p class="text-muted-foreground mb-4">Add products to get started</p>
                                    <Button @click="showProductModal = true" class="cursor-pointer">
                                        <Icon name="Plus" class="w-4 h-4 mr-2" />
                                        Browse Products
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Items List -->
                        <div v-for="item in cart" :key="item.product_id"
                            class="flex items-center gap-4 p-4 border rounded-lg bg-card hover:bg-accent/50 transition-colors">
                            <div class="flex-1 min-w-0">
                                {{ console.log(item) }}
                                <div class="font-medium text-base mb-1">{{ item.product_name }}</div>
                                <div class="text-sm text-muted-foreground mb-2">{{ item.product_sku }}</div>
                                <div class="text-sm font-medium text-primary">
                                    ${{ Number(item.unit_price).toFixed(2) }} per unit
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 bg-muted rounded-lg p-1">
                                    <Button size="sm" variant="ghost"
                                        @click="updateQuantity(item.product_id, item.quantity - 1)"
                                        :disabled="item.quantity <= 1" class="h-8 w-8 p-0 cursor-pointer">
                                        <Icon name="Minus" class="w-3 h-3" />
                                    </Button>
                                    <span class="w-12 text-center text-sm font-medium">{{ item.quantity }}</span>
                                    <Button size="sm" variant="ghost"
                                        @click="updateQuantity(item.product_id, item.quantity + 1)"
                                        :disabled="item.quantity >= item.available_stock"
                                        class="h-8 w-8 p-0 cursor-pointer">
                                        <Icon name="Plus" class="w-3 h-3" />
                                    </Button>
                                </div>

                                <div class="text-right min-w-[80px]">
                                    <div class="text-lg font-semibold">${{ Number(item.line_total).toFixed(2) }}</div>
                                </div>

                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button size="sm" variant="ghost"
                                            class="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10 cursor-pointer">
                                            <Icon name="X" class="w-4 h-4" />
                                        </Button>
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
                    <div v-if="cart.length > 0" class="border-t pt-4">
                        <div class="flex gap-2">
                            <Input v-model="quickSearchTerm" placeholder="Quick add products..." class="flex-1"
                                @keyup.enter="performQuickSearch" />
                            <Button @click="performQuickSearch" variant="outline" size="sm">
                                <Icon name="Search" class="w-4 h-4" />
                            </Button>
                        </div>

                        <!-- Quick Search Results -->
                        <div v-if="quickSearchResults.length" class="mt-3 space-y-2 max-h-32 overflow-y-auto">
                            <div v-for="product in quickSearchResults" :key="product.id"
                                class="flex items-center justify-between p-2 border rounded hover:bg-accent/50 cursor-pointer text-sm"
                                @click="addToCart(product)">
                                <div class="flex-1">
                                    <div class="font-medium">{{ product.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ product.sku }} • ${{
                                        Number(product.price).toFixed(2) }}</div>
                                </div>
                                <Button size="sm" variant="ghost" class="h-6 w-6 p-0">
                                    <Icon name="Plus" class="w-3 h-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Right Panel - Customer and Checkout -->
            <div class="w-1/3 flex flex-col gap-4">
                <!-- Customer Selection -->
                <Card class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">Customer</h3>
                        <Button @click="showNewCustomerDialog = true" variant="outline" size="sm">
                            <Icon name="Plus" class="w-4 h-4 mr-2" />
                            New Customer
                        </Button>
                    </div>
                    <CustomerSelector v-model="selectedCustomer" @customer-selected="handleCustomerSelected" />
                </Card>

                <!-- Checkout Section -->
                <Card class="flex-1 p-4">
                    <h3 class="text-lg font-medium mb-4">Order Summary</h3>

                    <!-- Discount Section -->
                    <div class="border rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium">Apply Discount</span>
                            <Button v-if="discountType" @click="clearDiscount" variant="ghost" size="sm"
                                class="cursor-pointer">
                                <Icon name="X" class="w-4 h-4" />
                            </Button>
                        </div>

                        <div v-if="!discountType" class="flex gap-2">
                            <Button @click="showDiscountDialog('percentage')" variant="outline" size="sm"
                                class="flex-1 cursor-pointer">
                                <Icon name="Percent" class="w-4 h-4 mr-2" />
                                % Discount
                            </Button>
                            <Button @click="showDiscountDialog('fixed')" variant="outline" size="sm"
                                class="flex-1 cursor-pointer">
                                <Icon name="DollarSign" class="w-4 h-4 mr-2" />
                                $ Discount
                            </Button>
                        </div>

                        <div v-else class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-green-800 dark:text-green-400">
                                    {{ discountType === 'percentage' ? `${Number(discountValue)}%` :
                                        `$${Number(discountValue).toFixed(2)}` }} Discount Applied
                                </span>
                                <span class="text-sm font-semibold text-green-800 dark:text-green-400">
                                    -${{ Number(discountAmount).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-3 border rounded-lg p-4 mb-6">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span class="font-medium">${{ Number(subtotal).toFixed(2) }}</span>
                        </div>
                        <div v-if="discountAmount > 0"
                            class="flex justify-between text-sm text-green-600 dark:text-green-400">
                            <span>Discount:</span>
                            <span class="font-medium">-${{ Number(discountAmount).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold border-t pt-3">
                            <span>Total:</span>
                            <span class="text-primary">${{ Number(total).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <Button @click="processCheckout" class="w-full h-12 cursor-pointer" size="lg"
                        :disabled="!canProcessSale" :loading="isProcessingSale">
                        <Icon name="CreditCard" class="w-5 h-5 mr-2" />
                        {{ isProcessingSale ? 'Processing Sale...' : 'Process Sale' }}
                    </Button>

                    <!-- Additional Info -->
                    <div v-if="cart.length > 0" class="mt-4 text-xs text-muted-foreground text-center">
                        <p>{{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} • Last updated: {{ new
                            Date().toLocaleTimeString() }}</p>
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
import { ref, watch, onMounted, onUnmounted } from 'vue'
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

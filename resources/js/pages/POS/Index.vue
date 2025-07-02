<template>
    <AppLayout title="Point of Sale">
        <div class="flex h-[calc(100vh-4rem)] gap-4 p-4">
            <!-- Left Panel - Products and Customer Selection -->
            <div class="w-2/3 flex flex-col gap-4">
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

                <!-- Products Section -->
                <Card class="flex-1 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">Products</h3>
                        <Button @click="showProductModal = true">
                            <Icon name="Plus" class="w-4 h-4 mr-2" />
                            Add Product
                        </Button>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex gap-2 mb-4">
                        <Input v-model="quickSearchTerm" placeholder="Quick search products..." class="flex-1"
                            @keyup.enter="performQuickSearch" />
                        <Button @click="performQuickSearch" variant="outline">
                            <Icon name="Search" class="w-4 h-4" />
                        </Button>
                    </div>

                    <!-- Recent/Quick Access Products -->
                    <div v-if="!quickSearchResults.length" class="text-sm text-muted-foreground">
                        Click "Add Product" to browse and add products to cart
                    </div>

                    <!-- Quick Search Results -->
                    <div v-if="quickSearchResults.length" class="space-y-2 max-h-60 overflow-y-auto">
                        <div v-for="product in quickSearchResults" :key="product.id"
                            class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent/50 cursor-pointer"
                            @click="addToCart(product)">
                            <div class="flex-1">
                                <div class="font-medium">{{ product.name }}</div>
                                <div class="text-sm text-muted-foreground">
                                    {{ product.sku }} | {{ product.category }} | Stock: {{ product.stock }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">${{ Number(product.price).toFixed(2) }}</div>
                                <div class="text-xs text-muted-foreground">{{ product.unit_measure }}</div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Right Panel - Cart and Checkout -->
            <div class="w-1/3 flex flex-col gap-4">
                <!-- Cart -->
                <Card class="flex-1 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">
                            Cart ({{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }})
                        </h3>
                        <Button @click="clearCart" variant="outline" size="sm" :disabled="!cart.length">
                            <Icon name="Trash2" class="w-4 h-4" />
                        </Button>
                    </div>

                    <!-- Cart Items -->
                    <div class="space-y-2 mb-4 flex-1 overflow-y-auto max-h-80">
                        <div v-for="item in cart" :key="item.product_id"
                            class="flex items-center gap-2 p-2 border rounded-lg">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate">{{ item.product_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ item.product_sku }}</div>
                            </div>
                            <div class="flex items-center gap-1">
                                <Button size="sm" variant="outline"
                                    @click="updateQuantity(item.product_id, item.quantity - 1)"
                                    :disabled="item.quantity <= 1">
                                    <Icon name="Minus" class="w-3 h-3" />
                                </Button>
                                <span class="w-8 text-center text-sm">{{ item.quantity }}</span>
                                <Button size="sm" variant="outline"
                                    @click="updateQuantity(item.product_id, item.quantity + 1)"
                                    :disabled="item.quantity >= item.available_stock">
                                    <Icon name="Plus" class="w-3 h-3" />
                                </Button>
                            </div>
                            <div class="text-right min-w-0">
                                <div class="text-sm font-medium">${{ Number(item.line_total).toFixed(2) }}</div>
                                <div class="text-xs text-muted-foreground">${{ Number(item.unit_price).toFixed(2) }} ea
                                </div>
                            </div>
                            <Button size="sm" variant="ghost" @click="removeFromCart(item.product_id)">
                                <Icon name="X" class="w-4 h-4" />
                            </Button>
                        </div>

                        <div v-if="!cart.length" class="text-center text-muted-foreground py-8">
                            No items in cart
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="border-t pt-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium">Discount</span>
                            <Button v-if="discountType" @click="clearDiscount" variant="ghost" size="sm">
                                <Icon name="X" class="w-4 h-4" />
                            </Button>
                        </div>

                        <div v-if="!discountType" class="flex gap-2">
                            <Button @click="showDiscountDialog('percentage')" variant="outline" size="sm"
                                class="flex-1">
                                % Discount
                            </Button>
                            <Button @click="showDiscountDialog('fixed')" variant="outline" size="sm" class="flex-1">
                                $ Discount
                            </Button>
                        </div>

                        <div v-else class="text-sm">
                            <div class="flex justify-between">
                                <span>
                                    {{ discountType === 'percentage' ? `${Number(discountValue)}%` :
                                        `$${Number(discountValue).toFixed(2)}` }}
                                    Discount
                                </span>
                                <span>-${{ Number(discountAmount).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 border-t pt-4">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span>${{ Number(subtotal).toFixed(2) }}</span>
                        </div>
                        <div v-if="discountAmount > 0" class="flex justify-between text-sm text-green-600">
                            <span>Discount:</span>
                            <span>-${{ Number(discountAmount).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total:</span>
                            <span>${{ Number(total).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <Button @click="processCheckout" class="w-full mt-4" size="lg" :disabled="!canProcessSale"
                        :loading="isProcessingSale">
                        <Icon name="CreditCard" class="w-5 h-5 mr-2" />
                        {{ isProcessingSale ? 'Processing...' : 'Process Sale' }}
                    </Button>
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

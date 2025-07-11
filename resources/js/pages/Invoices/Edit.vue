<template>

    <Head title="Edit Quotation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold">Edit Quotation #{{ invoice.id }}</h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                Created on {{ formatDate(invoice.created_at) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" :as="Link" :href="route('invoices.show', invoice.id)"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm flex items-center gap-2">
                                <Icon name="ArrowLeft" class="w-3 h-3 md:w-4 md:h-4" />
                                <span>Back</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 md:p-6">
                    <form @submit.prevent="submitForm">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Left Column - Invoice Items -->
                            <div class="lg:col-span-2 space-y-6">
                                <!-- Customer Information (Read-only) -->
                                <div class="border rounded-lg">
                                    <div class="p-4 border-b bg-muted/30">
                                        <h3 class="text-lg font-semibold">Customer Information</h3>
                                    </div>
                                    <div class="p-4">
                                        <div v-if="invoice.customer" class="space-y-3">
                                            <div class="p-3 bg-muted/20 rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <Icon name="User" class="w-5 h-5 text-muted-foreground" />
                                                    <div>
                                                        <p class="font-medium">{{ invoice.customer.full_name }}</p>
                                                        <p class="text-sm text-muted-foreground">{{
                                                            invoice.customer.email }}</p>
                                                        <p v-if="invoice.customer.phone"
                                                            class="text-sm text-muted-foreground">{{
                                                            invoice.customer.phone }}</p>
                                                    </div>
                                                </div>
                                                <p v-if="invoice.customer.address"
                                                    class="text-sm text-muted-foreground mt-2 ml-8">{{
                                                    invoice.customer.address }}</p>
                                            </div>
                                            <p class="text-xs text-muted-foreground">Customer cannot be changed for
                                                existing quotations</p>
                                        </div>
                                        <div v-else class="text-center py-4 text-muted-foreground">
                                            <Icon name="UserX" class="w-8 h-8 mx-auto mb-2" />
                                            <p>No customer assigned</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Items -->
                                <div class="border rounded-lg">
                                    <div class="p-4 border-b bg-muted/30 flex justify-between items-center">
                                        <h3 class="text-lg font-semibold">Items</h3>
                                        <Button type="button" @click="openProductModal" variant="outline" size="sm">
                                            <Icon name="Plus" class="w-4 h-4 mr-2 cursor-pointer" />
                                            Add Item
                                        </Button>
                                    </div>
                                    <div class="p-4">
                                        <div v-if="!form.items || form.items.length === 0"
                                            class="text-center py-8 text-muted-foreground">
                                            <Icon name="Package"
                                                class="w-12 h-12 mx-auto mb-4 text-muted-foreground/50" />
                                            <p class="text-lg font-medium mb-2">No items added yet</p>
                                            <p class="text-sm">Click "Add Item" to browse products from the catalog</p>
                                        </div>
                                        <div v-else-if="form.items && form.items.length > 0" class="space-y-4">
                                            <div v-for="(item, index) in form.items"
                                                :key="item.temp_id || `item-${index}`"
                                                class="p-4 border rounded-lg bg-muted/20">
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <!-- Product Information (Read-only) -->
                                                    <div class="md:col-span-2">
                                                        <label class="text-sm font-medium">Product</label>
                                                        <div class="p-3 bg-background border rounded-md">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center gap-3">
                                                                    <Icon name="Package" class="w-5 h-5 text-primary" />
                                                                    <div class="flex-1">
                                                                        <p class="font-medium text-sm">{{
                                                                            getProductName(item.product_id) }}</p>
                                                                        <p class="text-xs text-muted-foreground">SKU: {{
                                                                            getProductSKU(item.product_id) }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <p class="text-sm font-medium">${{
                                                                        formatCurrency(item.unit_price) }}</p>
                                                                    <p class="text-xs text-muted-foreground">per unit
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Quantity -->
                                                    <div>
                                                        <label class="text-sm font-medium">Quantity</label>
                                                        <Input v-model.number="item.quantity" type="number" step="0.01"
                                                            min="0.01" :max="getProductStock(item.product_id)"
                                                            @input="validateAndUpdateLineTotal(index)"
                                                            @change="validateAndUpdateLineTotal(index)" />
                                                        <p v-if="getProductStock(item.product_id)"
                                                            class="text-xs text-muted-foreground mt-1">
                                                            Available: {{ getProductStock(item.product_id) }} units
                                                        </p>
                                                        <p v-if="item.quantity > getProductStock(item.product_id)"
                                                            class="text-xs text-destructive mt-1">
                                                            ⚠️ Exceeds available stock ({{
                                                            getProductStock(item.product_id) }} units)
                                                        </p>
                                                    </div>

                                                    <!-- Unit Price -->
                                                    <div>
                                                        <label class="text-sm font-medium">Unit Price</label>
                                                        <Input v-model.number="item.unit_price" type="number"
                                                            step="0.01" min="0" @input="updateLineTotal(index)"
                                                            @change="updateLineTotal(index)" />
                                                    </div>
                                                </div>

                                                <div class="flex justify-between items-center mt-4">
                                                    <div class="text-sm font-medium">
                                                        Line Total: ${{ formatCurrency(item.line_total) }}
                                                    </div>
                                                    <Button type="button" @click="removeItem(index)"
                                                        variant="destructive" size="sm">
                                                        <Icon name="Trash2" class="w-4 h-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Summary and Actions -->
                            <div class="space-y-6">
                                <!-- Discount Section -->
                                <div class="border rounded-lg">
                                    <div class="p-4 border-b bg-muted/30">
                                        <h3 class="text-lg font-semibold">Discount</h3>
                                    </div>
                                    <div class="p-4 space-y-4">
                                        <div>
                                            <label class="text-sm font-medium">Discount Type</label>
                                            <Select :model-value="form.discount_type"
                                                @update:model-value="updateDiscountType">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="No discount" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="percentage">Percentage</SelectItem>
                                                    <SelectItem value="fixed">Fixed Amount</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div v-if="form.discount_type">
                                            <label class="text-sm font-medium">
                                                {{ form.discount_type === 'percentage' ? 'Percentage (%)' : 'Amount ($)'
                                                }}
                                            </label>
                                            <Input v-model.number="form.discount_value" type="number" step="0.01"
                                                min="0" :max="form.discount_type === 'percentage' ? 100 : undefined"
                                                @input="calculateTotals" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary -->
                                <div class="border rounded-lg">
                                    <div class="p-4 border-b bg-muted/30">
                                        <h3 class="text-lg font-semibold">Summary</h3>
                                    </div>
                                    <div class="p-4 space-y-3">
                                        <div class="flex justify-between">
                                            <span>Subtotal:</span>
                                            <span>${{ formatCurrency(form.subtotal) }}</span>
                                        </div>
                                        <div v-if="discountAmount > 0" class="flex justify-between text-green-600">
                                            <span>Discount:</span>
                                            <span>-${{ formatCurrency(discountAmount) }}</span>
                                        </div>
                                        <div class="border-t pt-3">
                                            <div class="flex justify-between font-semibold text-lg">
                                                <span>Total:</span>
                                                <span>${{ formatCurrency(form.total_amount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="border rounded-lg">
                                    <div class="p-4 border-b bg-muted/30">
                                        <h3 class="text-lg font-semibold">Actions</h3>
                                    </div>
                                    <div class="p-4 space-y-2">
                                        <div v-if="hasStockIssues"
                                            class="p-3 bg-destructive/10 border border-destructive/20 rounded-lg mb-3">
                                            <div class="flex items-center gap-2 text-destructive">
                                                <Icon name="AlertTriangle" class="w-4 h-4" />
                                                <span class="text-sm font-medium">Stock Issues Detected</span>
                                            </div>
                                            <p class="text-xs text-destructive/80 mt-1">
                                                Some items exceed available stock. Please adjust quantities before
                                                saving.
                                            </p>
                                        </div>
                                        <Button type="submit" :disabled="!canSubmit" class="w-full cursor-pointer">
                                            <Icon v-if="isSubmitting" name="Loader2"
                                                class="w-4 h-4 mr-2 animate-spin" />
                                            <Icon v-else name="Save" class="w-4 h-4 mr-2" />
                                            {{ isSubmitting ? 'Saving...' : 'Save Quotation' }}
                                        </Button>
                                        <Button type="button" variant="outline" :as="Link"
                                            :href="route('invoices.show', invoice.id)" class="w-full cursor-pointer">
                                            <Icon name="X" class="w-4 h-4 mr-2" />
                                            Cancel
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Selection Modal -->
        <ProductSelectionModal v-model:open="showProductModal" @product-selected="addProductToCart" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onUnmounted, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import Icon from '@/components/Icon.vue'
import ProductSelectionModal from '@/pages/POS/components/ProductSelectionModal.vue'
import { toast } from 'vue-sonner'
import { type BreadcrumbItem } from '@/types'
import type { Product as POSProduct } from '@/types/pos'
import axios from 'axios'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
    { title: 'Edit Quotation', href: '' },
]

interface Product {
    id: number
    name: string
    sku: string
    price: number
    stock: number
    category: string
    unit_measure: string
}

interface Customer {
    id: number
    full_name: string
    email: string
    phone?: string
    address?: string
}

interface InvoiceItem {
    id?: number
    product_id: string
    quantity: number
    unit_price: number
    line_total: number
    temp_id?: string // For unique identification during editing
}

interface Invoice {
    id: number
    date: string
    customer_id: number
    customer?: Customer
    subtotal: number
    discount_type?: 'percentage' | 'fixed'
    discount_value: number
    total_amount: number
    status: string
    payment_method: string
    items: InvoiceItem[]
    created_at: string
}

interface Props {
    invoice: Invoice
    customers: Customer[]
    products: Product[]
}

const props = defineProps<Props>()

// Form state
const form = ref({
    date: props.invoice?.date || '',
    items: (props.invoice?.items || []).map((item, index) => ({
        ...item,
        product_id: item.product_id?.toString() || '',
        temp_id: item.id ? `existing-${item.id}` : `new-${index}-${Date.now()}`
    })),
    subtotal: props.invoice?.subtotal || 0,
    discount_type: props.invoice?.discount_type || '',
    discount_value: props.invoice?.discount_value || 0,
    total_amount: props.invoice?.total_amount || 0,
})

const isSubmitting = ref(false)
const showProductModal = ref(false)

// Computed
const discountAmount = computed(() => {
    if (!form.value.discount_type || !form.value.discount_value) return 0

    if (form.value.discount_type === 'percentage') {
        return (form.value.subtotal * form.value.discount_value) / 100
    }

    return form.value.discount_value
})

const hasStockIssues = computed(() => {
    if (!form.value.items || form.value.items.length === 0) return false

    return form.value.items.some(item => {
        const availableStock = getProductStock(item.product_id)
        return item.quantity > availableStock
    })
})

const canSubmit = computed(() => {
    return !isSubmitting.value &&
        form.value.items.length > 0 &&
        !hasStockIssues.value
})

// Methods
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

const updateDiscountType = (discountType: any) => {
    nextTick(() => {
        form.value.discount_type = discountType || ''
        calculateTotals()
    })
}

const openProductModal = () => {
    showProductModal.value = true
}

const roundQuantity = (qty: number) => {
    // Si solo quieres enteros, usa Math.round(qty)
    // Si permites decimales, limita a dos decimales
    return Math.round((qty + Number.EPSILON) * 100) / 100
}

const addProductToCart = (product: POSProduct, quantity: number) => {
    // Validar stock disponible
    if (quantity > product.stock) {
        toast.error(`Cannot add ${quantity} units. Only ${product.stock} available for ${product.name}`)
        return
    }

    // Check if product already exists in items
    const existingItemIndex = form.value.items.findIndex(item =>
        parseInt(item.product_id) === product.id
    )

    if (existingItemIndex >= 0) {
        // Validar que la suma no exceda el stock
        const currentQuantity = Number(form.value.items[existingItemIndex].quantity) || 0
        let totalQuantity = currentQuantity + quantity
        totalQuantity = roundQuantity(totalQuantity)

        if (totalQuantity > product.stock) {
            toast.error(`Cannot add ${quantity} more units. Already have ${currentQuantity}, only ${product.stock} available for ${product.name}`)
            return
        }

        // Update existing item quantity
        form.value.items[existingItemIndex].quantity = totalQuantity
        updateLineTotal(existingItemIndex)
        toast.success(`Updated ${product.name} quantity to ${totalQuantity}`)
    } else {
        // Add new item
        const tempId = `new-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
        form.value.items.push({
            product_id: product.id.toString(),
            quantity: roundQuantity(quantity),
            unit_price: product.price,
            line_total: product.price * roundQuantity(quantity),
            temp_id: tempId
        })
        calculateTotals()
        toast.success(`Added ${product.name} to quotation`)
    }

    // No cerrar el modal automáticamente para permitir agregar más productos
    // showProductModal.value = false
}

const getProductName = (productId: string): string => {
    const product = props.products?.find(p => p.id.toString() === productId)
    return product?.name || 'Unknown Product'
}

const getProductSKU = (productId: string): string => {
    const product = props.products?.find(p => p.id.toString() === productId)
    return product?.sku || 'N/A'
}

const getProductStock = (productId: string): number => {
    const product = props.products?.find(p => p.id.toString() === productId)
    return product?.stock || 0
}

const validateAndUpdateLineTotal = (index: number) => {
    if (!form.value.items || index < 0 || index >= form.value.items.length) return

    const item = form.value.items[index]
    if (!item) return

    const quantity = Number(item.quantity) || 0
    const availableStock = getProductStock(item.product_id)

    // Validar stock disponible
    if (quantity > availableStock) {
        toast.error(`Cannot add ${quantity} units. Only ${availableStock} available for ${getProductName(item.product_id)}`)
        // Ajustar automáticamente a la cantidad máxima disponible
        item.quantity = availableStock
    }

    updateLineTotal(index)
}

const removeItem = (index: number) => {
    if (!form.value.items || index < 0 || index >= form.value.items.length) return

    form.value.items.splice(index, 1)
    calculateTotals()
}

const updateLineTotal = (index: number) => {
    if (!form.value.items || index < 0 || index >= form.value.items.length) return

    const item = form.value.items[index]
    if (!item) return

    const quantity = Number(item.quantity) || 0
    const unitPrice = Number(item.unit_price) || 0

    item.line_total = quantity * unitPrice
    calculateTotals()
}

const initializeLineTotals = () => {
    if (!form.value.items) return

    form.value.items.forEach((item) => {
        const quantity = Number(item.quantity) || 0
        const unitPrice = Number(item.unit_price) || 0
        item.line_total = quantity * unitPrice
    })
    calculateTotals()
}

const calculateTotals = () => {
    if (!form.value.items || form.value.items.length === 0) {
        form.value.subtotal = 0
        form.value.total_amount = 0
        return
    }

    // Calculate subtotal
    form.value.subtotal = form.value.items.reduce((sum, item) => {
        const lineTotal = Number(item.line_total) || 0
        return sum + lineTotal
    }, 0)

    // Calculate total with discount
    const discount = discountAmount.value || 0
    form.value.total_amount = Math.max(0, form.value.subtotal - discount)
}

const submitForm = async () => {
    if (isSubmitting.value) return

    if (!form.value.items || form.value.items.length === 0) {
        toast.error('Please add at least one item')
        return
    }

    // Validate all items have products selected
    const invalidItems = form.value.items.some(item => !item.product_id || (item.quantity || 0) <= 0)
    if (invalidItems) {
        toast.error('Please complete all item information')
        return
    }

    // Validate stock availability
    const stockIssues = []
    for (const item of form.value.items) {
        const availableStock = getProductStock(item.product_id)
        const productName = getProductName(item.product_id)

        if (item.quantity > availableStock) {
            stockIssues.push(`${productName}: requires ${item.quantity}, only ${availableStock} available`)
        }
    }

    if (stockIssues.length > 0) {
        toast.error(`Stock validation errors:\n${stockIssues.join('\n')}`)
        return
    }

    isSubmitting.value = true

    try {
        const data = {
            date: form.value.date,
            items: form.value.items.map(item => ({
                product_id: parseInt(item.product_id),
                quantity: item.quantity || 0,
                unit_price: item.unit_price || 0,
                line_total: item.line_total || 0
            })),
            subtotal: form.value.subtotal || 0,
            discount_type: form.value.discount_type || null,
            discount_value: form.value.discount_value || 0,
            total_amount: form.value.total_amount || 0
        }


        await axios.put(route('invoices.update', props.invoice.id), data)

        toast.success('Quotation updated successfully')
        router.visit(route('invoices.show', props.invoice.id))

    } catch (error: any) {
        console.error('Error updating quotation:', error)
        const errorMessage = error.response?.data?.message || error.message || 'Error updating quotation'
        toast.error(errorMessage)
    } finally {
        isSubmitting.value = false
    }
}

// Watch for changes to recalculate totals with debounce
let debounceTimer: number | null = null
const stopWatcher = watch(() => form.value.items, () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer)
    }
    debounceTimer = setTimeout(() => {
        calculateTotals()
    }, 100) // 100ms debounce
}, { deep: true })

// Watch for discount changes
watch(() => [form.value.discount_type, form.value.discount_value], () => {
    calculateTotals()
}, { deep: true })

// Initialize calculations on mount
onMounted(() => {
    initializeLineTotals()
})

// Cleanup on unmount
onUnmounted(() => {
    if (stopWatcher) {
        stopWatcher()
    }
    if (debounceTimer) {
        clearTimeout(debounceTimer)
    }
})
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
</style>

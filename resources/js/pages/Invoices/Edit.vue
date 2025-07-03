<template>
    <AppLayout title="Edit Invoice">
        <Head title="Edit Invoice" />

        <!-- Header -->
        <div class="flex items-center justify-between mb-6 p-4 bg-background border-b">
            <div>
                <h1 class="text-2xl font-bold">Edit Invoice #{{ invoice.id }}</h1>
                <p class="text-muted-foreground">{{ isReadOnly ? 'This invoice cannot be edited' : 'Make changes to your invoice' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button @click="router.get(route('invoices.show', invoice.id))" variant="outline" size="sm">
                    <Icon name="Eye" class="w-4 h-4 mr-2" />
                    View
                </Button>
                <Button @click="router.get(route('invoices.index'))" variant="outline" size="sm">
                    <Icon name="ArrowLeft" class="w-4 h-4 mr-2" />
                    Back to List
                </Button>
            </div>
        </div>

        <!-- Read-only notification -->
        <div v-if="isReadOnly" class="mx-4 mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2">
                <Icon name="Lock" class="w-5 h-5 text-red-600" />
                <h3 class="font-semibold text-red-800">Invoice is {{ form.status }}</h3>
            </div>
            <p class="text-sm text-red-700 mt-1">
                This invoice cannot be modified because it has been {{ form.status }}.
                No changes are allowed once an invoice is issued.
            </p>
        </div>

        <div class="flex flex-col lg:flex-row gap-4 p-4 min-h-[calc(100vh-10rem)]">
            <!-- Left Panel - Invoice Items -->
            <div class="w-full lg:w-2/3 space-y-4">
                <!-- Invoice Items Section -->
                <Card class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold">Invoice Items</h3>
                            <p class="text-sm text-muted-foreground">
                                {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} in invoice
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                @click="showProductModal = true"
                                :disabled="isReadOnly"
                                class="cursor-pointer"
                            >
                                <Icon name="Plus" class="w-4 h-4 mr-2" />
                                Add Products
                            </Button>
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        :disabled="!form.items.length || isReadOnly"
                                        class="cursor-pointer"
                                    >
                                        <Icon name="Trash2" class="w-4 h-4 mr-2" />
                                        Clear Items
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Clear Invoice Items?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action will remove all {{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} from your invoice.
                                            This action cannot be undone.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                                        <AlertDialogAction
                                            variant="destructive"
                                            class="cursor-pointer"
                                            @click="clearItems"
                                        >
                                            Clear Items
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>

                    <!-- Invoice Items List -->
                    <div class="space-y-3 min-h-[200px] max-h-[60vh] overflow-y-auto">
                        <!-- Empty Items State -->
                        <div v-if="!form.items.length" class="text-center py-16">
                            <div class="flex flex-col items-center gap-4">
                                <Icon name="Receipt" class="w-16 h-16 text-muted-foreground/50" />
                                <div>
                                    <h4 class="font-medium text-lg mb-2">No items in invoice</h4>
                                    <p class="text-muted-foreground mb-4">Add products to get started</p>
                                    <Button
                                        @click="showProductModal = true"
                                        :disabled="isReadOnly"
                                        class="cursor-pointer"
                                    >
                                        <Icon name="Plus" class="w-4 h-4 mr-2" />
                                        Browse Products
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div v-for="(item, index) in form.items" :key="index"
                            class="flex items-center gap-4 p-4 border rounded-lg bg-card hover:bg-accent/50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-base mb-1">{{ item.product_name }}</div>
                                <div class="text-sm text-muted-foreground mb-2">{{ item.product_sku }}</div>
                                <div class="text-sm font-medium text-primary">
                                    ${{ Number(item.unit_price).toFixed(2) }} per unit
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 bg-muted rounded-lg p-1">
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="updateQuantity(index, item.quantity - 1)"
                                        :disabled="item.quantity <= 1 || isReadOnly"
                                        class="h-8 w-8 p-0 cursor-pointer"
                                    >
                                        <Icon name="Minus" class="w-3 h-3" />
                                    </Button>
                                    <span class="w-12 text-center text-sm font-medium">{{ item.quantity }}</span>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="updateQuantity(index, item.quantity + 1)"
                                        :disabled="isReadOnly"
                                        class="h-8 w-8 p-0 cursor-pointer"
                                    >
                                        <Icon name="Plus" class="w-3 h-3" />
                                    </Button>
                                </div>

                                <div class="text-right min-w-[80px]">
                                    <div class="text-lg font-semibold">${{ Number(item.total_amount).toFixed(2) }}</div>
                                </div>

                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            :disabled="isReadOnly"
                                            class="h-8 w-8 p-0 cursor-pointer text-destructive hover:text-destructive hover:bg-destructive/10"
                                        >
                                            <Icon name="Trash2" class="w-3 h-3" />
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Remove Item</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Are you sure you want to remove "{{ item.product_name }}" from this invoice?
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                                            <AlertDialogAction
                                                variant="destructive"
                                                @click="removeItem(index)"
                                                class="cursor-pointer"
                                            >
                                                Remove
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Right Panel - Invoice Summary -->
            <div class="w-full lg:w-1/3 space-y-4">
                <!-- Customer Selection -->
                <Card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="User" class="w-5 h-5" />
                        <h4 class="font-medium text-base">Customer</h4>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <Select v-model="form.customer_id" :disabled="isReadOnly">
                                <SelectTrigger class="flex-1">
                                    <SelectValue placeholder="Select customer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="customer in customers" :key="customer.id" :value="customer.id.toString()">
                                        {{ customer.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button
                                @click="showCustomerModal = true"
                                variant="outline"
                                size="sm"
                                :disabled="isReadOnly"
                                class="h-10 w-10 p-0"
                            >
                                <Icon name="Plus" class="w-4 h-4" />
                            </Button>
                        </div>
                        <div v-if="selectedCustomer" class="text-sm text-muted-foreground space-y-1">
                            <div v-if="selectedCustomer.email">{{ selectedCustomer.email }}</div>
                            <div v-if="selectedCustomer.phone">{{ selectedCustomer.phone }}</div>
                        </div>
                    </div>
                </Card>

                <!-- Invoice Details -->
                <Card class="p-6">
                    <h4 class="font-medium text-base mb-4">Invoice Details</h4>
                    <div class="space-y-4">
                        <div>
                            <Label for="date" class="text-sm font-medium">Date</Label>
                            <Input
                                id="date"
                                type="date"
                                v-model="form.date"
                                :disabled="isReadOnly"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label for="status" class="text-sm font-medium">Status</Label>
                            <Select v-model="form.status" :disabled="isReadOnly">
                                <SelectTrigger class="mt-1">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="canceled">Canceled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </Card>

                <!-- Discount Section -->
                <Card class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="Percent" class="w-5 h-5" />
                        <h4 class="font-medium text-base">Discount</h4>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <Select v-model="form.discount_type" :disabled="isReadOnly">
                                <SelectTrigger class="flex-1">
                                    <SelectValue placeholder="Discount type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="percentage">Percentage (%)</SelectItem>
                                    <SelectItem value="fixed">Fixed amount ($)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div v-if="form.discount_type">
                            <Input
                                type="number"
                                v-model.number="form.discount_value"
                                :placeholder="form.discount_type === 'percentage' ? 'Enter percentage' : 'Enter amount'"
                                :disabled="isReadOnly"
                                step="0.01"
                                min="0"
                                :max="form.discount_type === 'percentage' ? 100 : undefined"
                            />
                        </div>
                    </div>
                </Card>

                <!-- Order Summary -->
                <Card class="p-6">
                    <h4 class="font-medium text-base mb-4">Order Summary</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span>${{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div v-if="discountAmount > 0" class="flex justify-between text-sm">
                            <span>Discount:</span>
                            <span class="text-red-600">-${{ discountAmount.toFixed(2) }}</span>
                        </div>
                        <div v-if="taxAmount > 0" class="flex justify-between text-sm">
                            <span>Tax:</span>
                            <span>${{ taxAmount.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold pt-3 border-t">
                            <span>Total:</span>
                            <span>${{ total.toFixed(2) }}</span>
                        </div>
                    </div>
                </Card>

                <!-- Action Buttons -->
                <Card class="p-6">
                    <h4 class="font-medium text-base mb-4">{{ isReadOnly ? 'Invoice Actions' : 'Save Changes' }}</h4>
                    <div class="space-y-3">
                        <Button
                            @click="saveInvoice"
                            :disabled="!form.items.length || form.processing || isReadOnly"
                            class="w-full h-11"
                            size="lg"
                            :variant="isReadOnly ? 'secondary' : 'default'"
                        >
                            <Icon :name="isReadOnly ? 'Lock' : 'Save'" class="w-4 h-4 mr-2" />
                            {{ isReadOnly ? 'Invoice Locked' : (form.processing ? 'Saving...' : 'Save Invoice') }}
                        </Button>
                        <p class="text-xs text-muted-foreground text-center">
                            {{ form.items.length }} item{{ form.items.length !== 1 ? 's' : '' }} • Total: ${{ total.toFixed(2) }}
                        </p>
                        <div v-if="isReadOnly" class="text-xs text-red-600 text-center">
                            This invoice cannot be modified
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Product Selection Modal -->
        <ProductModal
            v-model:open="showProductModal"
            :products="products"
            @add-product="addProduct"
            :existing-products="form.items.map(item => item.product_id)"
        />

        <!-- Customer Modal -->
        <CustomerModal
            v-model:open="showCustomerModal"
            @customer-created="onCustomerCreated"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
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
import Icon from '@/components/Icon.vue'
// Update the import path below if the actual location is different
import ProductModal from '@/pages/Invoices/components/ProductModal.vue'
import CustomerModal from '@/pages/Invoices/components/CustomerModal.vue'
import { toast } from 'vue-sonner'

interface Product {
    id: number
    name: string
    sku: string
    price: number
    stock: number
    category?: {
        id: number
        name: string
    }
    unit_measure?: {
        id: number
        name: string
        abbreviation: string
    }
}

interface Customer {
    id: number
    name: string
    email?: string
    phone?: string
    address?: string
}

interface InvoiceItem {
    product_id: number
    product_name: string
    product_sku: string
    quantity: number
    unit_price: number
    total_amount: number
}

interface Invoice {
    id: number
    date: string
    customer_id?: number
    customer?: Customer
    subtotal_amount: number
    discount_type?: string
    discount_value: number
    discount_amount: number
    tax_amount: number
    total_amount: number
    status: string
    items: {
        id: number
        product_id: number
        quantity: number
        unit_price: number
        total_amount: number
        product?: Product
    }[]
}

interface Props {
    invoice: Invoice
    products: Product[]
    customers: Customer[]
}

const props = defineProps<Props>()

// Form
const form = ref({
    date: props.invoice.date,
    customer_id: props.invoice.customer_id?.toString() || '',
    status: props.invoice.status,
    discount_type: props.invoice.discount_type || '',
    discount_value: Number(props.invoice.discount_value) || 0,
    items: props.invoice.items.map(item => ({
        product_id: item.product_id,
        product_name: item.product?.name || '',
        product_sku: item.product?.sku || '',
        quantity: Number(item.quantity),
        unit_price: Number(item.unit_price),
        total_amount: Number(item.total_amount)
    })) as InvoiceItem[],
    processing: false
})

// Refs
const showProductModal = ref(false)
const showCustomerModal = ref(false)

// Computed
const itemCount = computed(() => form.value.items.length)

const isReadOnly = computed(() => {
    return form.value.status === 'paid' || form.value.status === 'canceled'
})

const selectedCustomer = computed(() => {
    if (!form.value.customer_id) return null
    return props.customers.find(c => c.id.toString() === form.value.customer_id)
})

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + Number(item.total_amount), 0)
})

const discountAmount = computed(() => {
    if (!form.value.discount_type || !form.value.discount_value) return 0

    if (form.value.discount_type === 'percentage') {
        return subtotal.value * (Number(form.value.discount_value) / 100)
    } else {
        return Math.min(Number(form.value.discount_value), subtotal.value)
    }
})

const taxAmount = computed(() => {
    // For now, no tax calculation
    return 0
})

const total = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value + taxAmount.value)
})

// Methods
const updateQuantity = (index: number, newQuantity: number) => {
    if (newQuantity < 1) return

    const item = form.value.items[index]
    item.quantity = newQuantity
    item.total_amount = Number(item.unit_price) * newQuantity
}

const removeItem = (index: number) => {
    form.value.items.splice(index, 1)
}

const clearItems = () => {
    form.value.items = []
}

const addProduct = (product: Product, quantity: number = 1) => {
    const existingIndex = form.value.items.findIndex(item => item.product_id === product.id)

    if (existingIndex >= 0) {
        // Update existing item
        const existingItem = form.value.items[existingIndex]
        existingItem.quantity += quantity
        existingItem.total_amount = Number(existingItem.unit_price) * existingItem.quantity
    } else {
        // Add new item
        form.value.items.push({
            product_id: product.id,
            product_name: product.name,
            product_sku: product.sku,
            quantity: quantity,
            unit_price: Number(product.price),
            total_amount: Number(product.price) * quantity
        })
    }
}

const onCustomerCreated = (customer: Customer) => {
    form.value.customer_id = customer.id.toString()
    showCustomerModal.value = false
}

const saveInvoice = () => {
    if (!form.value.items.length) {
        toast.error('Please add at least one item to the invoice')
        return
    }

    if (isReadOnly.value) {
        toast.error(`Cannot modify invoice - it has been ${form.value.status}`)
        return
    }

    form.value.processing = true

    const data = {
        date: form.value.date,
        customer_id: form.value.customer_id || null,
        status: form.value.status,
        discount_type: form.value.discount_type || null,
        discount_value: form.value.discount_value || 0,
        items: form.value.items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price
        }))
    }

    router.put(route('invoices.update', props.invoice.id), data, {
        onSuccess: () => {
            toast.success('Invoice updated successfully')
            router.get(route('invoices.show', props.invoice.id))
        },
        onError: (errors) => {
            console.error('Validation errors:', errors)
            toast.error('Error updating invoice. Please check the form and try again.')
        },
        onFinish: () => {
            form.value.processing = false
        }
    })
}

// Watch for discount changes
watch([() => form.value.discount_type, () => form.value.discount_value], () => {
    if (!form.value.discount_type) {
        form.value.discount_value = 0
    }
})
</script>

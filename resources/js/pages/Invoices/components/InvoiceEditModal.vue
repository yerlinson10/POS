<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-w-6xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Edit Invoice #{{ invoice?.id }}</DialogTitle>
                <DialogDescription>
                    Update invoice details and items
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="updateInvoice" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Basic Information</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Customer *
                                </label>
                                <Select v-model="form.customer_id" required>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select customer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="customer in customers"
                                            :key="customer.id"
                                            :value="customer.id.toString()"
                                        >
                                            {{ customer.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.customer_id" class="text-sm text-red-600 mt-1">
                                    {{ errors.customer_id }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date *
                                </label>
                                <Input
                                    v-model="form.date"
                                    type="date"
                                    required
                                />
                                <p v-if="errors.date" class="text-sm text-red-600 mt-1">
                                    {{ errors.date }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status *
                                </label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="canceled">Canceled</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.status" class="text-sm text-red-600 mt-1">
                                    {{ errors.status }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Summary</h3>
                        <div class="space-y-3 p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Subtotal</span>
                                <span class="text-sm font-medium">${{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div v-if="discountAmount > 0" class="flex justify-between">
                                <span class="text-sm text-gray-600">Discount</span>
                                <span class="text-sm font-medium text-red-600">-${{ formatCurrency(discountAmount) }}</span>
                            </div>
                            <div class="border-t pt-2">
                                <div class="flex justify-between">
                                    <span class="text-base font-semibold">Total</span>
                                    <span class="text-base font-semibold">${{ formatCurrency(total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Discount</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Discount Type
                            </label>
                            <Select v-model="form.discount_type">
                                <SelectTrigger>
                                    <SelectValue placeholder="No discount" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">No discount</SelectItem>
                                    <SelectItem value="percentage">Percentage</SelectItem>
                                    <SelectItem value="fixed">Fixed Amount</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div v-if="form.discount_type">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Discount Value
                            </label>
                            <Input
                                v-model="form.discount_value"
                                type="number"
                                step="0.01"
                                min="0"
                                :placeholder="form.discount_type === 'percentage' ? 'Enter percentage' : 'Enter amount'"
                            />
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Invoice Items</h3>
                        <Button
                            type="button"
                            @click="addItem"
                            variant="outline"
                            size="sm"
                            class="flex items-center space-x-2"
                        >
                            <Icon name="Plus" class="w-4 h-4" />
                            <span>Add Item</span>
                        </Button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid grid-cols-1 md:grid-cols-6 gap-3 p-3 border rounded-lg"
                        >
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Product *
                                </label>
                                <Select v-model="item.product_id" @update:model-value="updateItemPrice(index)">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select product" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="product in products"
                                            :key="product.id"
                                            :value="product.id.toString()"
                                        >
                                            {{ product.name }} ({{ product.sku }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Quantity *
                                </label>
                                <Input
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    @input="updateItemTotal(index)"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Unit Price *
                                </label>
                                <Input
                                    v-model="item.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    @input="updateItemTotal(index)"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Total
                                </label>
                                <Input
                                    :value="formatCurrency(item.line_total)"
                                    readonly
                                    class="bg-gray-50"
                                />
                            </div>
                            <div class="flex items-end">
                                <Button
                                    type="button"
                                    @click="removeItem(index)"
                                    variant="destructive"
                                    size="sm"
                                    class="w-full"
                                >
                                    <Icon name="Trash2" class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <p v-if="errors.items" class="text-sm text-red-600">
                        {{ errors.items }}
                    </p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="open = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <Icon v-if="processing" name="Loader2" class="w-4 h-4 mr-2 animate-spin" />
                        {{ processing ? 'Updating...' : 'Update Invoice' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
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
import { toast } from 'vue-sonner'

interface InvoiceItem {
    id?: number
    product_id: string
    quantity: number
    unit_price: number
    line_total: number
}

interface Product {
    id: number
    name: string
    sku: string
    price: number
    stock: number
}

interface Customer {
    id: number
    name: string
    email: string
}

interface Invoice {
    id: number
    customer_id: number
    date: string
    status: string
    discount_type?: string
    discount_value: number
    items_count: number
    total_amount: number
    created_at: string
}

interface Props {
    open: boolean
    invoice: Invoice | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'invoice-updated': []
}>()

const processing = ref(false)
const customers = ref<Customer[]>([])
const products = ref<Product[]>([])
const errors = ref<Record<string, string>>({})

const form = ref({
    customer_id: '',
    date: '',
    status: '',
    discount_type: '',
    discount_value: 0,
    items: [] as InvoiceItem[],
})

const open = computed({
    get: () => props.open,
    set: (value: boolean) => emit('update:open', value)
})

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + item.line_total, 0)
})

const discountAmount = computed(() => {
    if (!form.value.discount_value || !form.value.discount_type) return 0

    if (form.value.discount_type === 'percentage') {
        return (subtotal.value * form.value.discount_value) / 100
    }

    return form.value.discount_value
})

const total = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value)
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

const loadData = async () => {
    if (!props.invoice) return

    try {
        // Load customers
        const customersResponse = await fetch('/api/customers')
        if (customersResponse.ok) {
            const customersData = await customersResponse.json()
            customers.value = customersData.customers?.data || customersData.data || []
        }

        // Load products
        const productsResponse = await fetch('/api/products')
        if (productsResponse.ok) {
            const productsData = await productsResponse.json()
            products.value = productsData.products?.data || productsData.data || []
        }

        // Load invoice details
        const invoiceResponse = await fetch(route('invoices.show', props.invoice.id))
        if (invoiceResponse.ok) {
            const invoiceData = await invoiceResponse.json()

            form.value = {
                customer_id: invoiceData.customer_id?.toString() || '',
                date: invoiceData.date || '',
                status: invoiceData.status || '',
                discount_type: invoiceData.discount_type || '',
                discount_value: invoiceData.discount_value || 0,
                items: invoiceData.items?.map((item: any) => ({
                    id: item.id,
                    product_id: item.product_id.toString(),
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    line_total: item.line_total,
                })) || [],
            }
        }
    } catch (error) {
        console.error('Error loading data:', error)
        toast.error('Error loading invoice data')
    }
}

const addItem = () => {
    form.value.items.push({
        product_id: '',
        quantity: 1,
        unit_price: 0,
        line_total: 0,
    })
}

const removeItem = (index: number) => {
    form.value.items.splice(index, 1)
}

const updateItemPrice = (index: number) => {
    const item = form.value.items[index]
    const product = products.value.find(p => p.id.toString() === item.product_id)

    if (product) {
        item.unit_price = product.price
        updateItemTotal(index)
    }
}

const updateItemTotal = (index: number) => {
    const item = form.value.items[index]
    item.line_total = item.quantity * item.unit_price
}

const updateInvoice = () => {
    if (!props.invoice) return

    processing.value = true
    errors.value = {}

    const formData = {
        ...form.value,
        customer_id: parseInt(form.value.customer_id),
        items: form.value.items.map(item => ({
            ...item,
            product_id: parseInt(item.product_id),
        })),
    }

    router.put(route('invoices.update', props.invoice.id), formData, {
        onSuccess: () => {
            toast.success('Invoice updated successfully')
            emit('invoice-updated')
        },
        onError: (pageErrors) => {
            errors.value = pageErrors
            toast.error('Please check the form for errors')
        },
        onFinish: () => {
            processing.value = false
        },
    })
}

// Watch for discount changes
watch([() => form.value.discount_type, () => form.value.discount_value], () => {
    if (!form.value.discount_type) {
        form.value.discount_value = 0
    }
})

// Watch for modal open/close
watch(() => props.open, (newValue) => {
    if (newValue && props.invoice) {
        loadData()
    }
})

onMounted(() => {
    if (props.open && props.invoice) {
        loadData()
    }
})
</script>

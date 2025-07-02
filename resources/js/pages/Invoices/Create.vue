<template>
    <Head title="Create Invoice" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-6">

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column - Basic Info -->
                        <div class="space-y-6">
                            <!-- Customer Selection -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Customer Information</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label for="customer">Customer *</Label>
                                        <Select v-model="form.customer_id" required>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a customer" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="customer in customers"
                                                    :key="customer.id"
                                                    :value="customer.id"
                                                >
                                                    {{ customer.first_name }} {{ customer.last_name }} - {{ customer.email }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError field="customer_id" :message="errors.customer_id" />
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Invoice Details -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Invoice Details</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <Label for="date">Date *</Label>
                                        <Input
                                            id="date"
                                            v-model="form.date"
                                            type="date"
                                            required
                                        />
                                        <InputError field="date" :message="errors.date" />
                                    </div>

                                    <div>
                                        <Label for="status">Status *</Label>
                                        <Select v-model="form.status" required>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="pending">Pending</SelectItem>
                                                <SelectItem value="paid">Paid</SelectItem>
                                                <SelectItem value="canceled">Canceled</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError field="status" :message="errors.status" />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Right Column - Items -->
                        <div class="space-y-6">
                            <!-- Items Section -->
                            <Card>
                                <CardHeader>
                                    <CardTitle class="flex justify-between items-center">
                                        <span>Invoice Items</span>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="addItem"
                                        >
                                            <Plus class="w-4 h-4 mr-2" />
                                            Add Item
                                        </Button>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
                                        No items added yet. Click "Add Item" to get started.
                                    </div>

                                    <div v-else class="space-y-4">
                                        <div
                                            v-for="(item, index) in form.items"
                                            :key="index"
                                            class="p-4 border rounded-lg space-y-4"
                                        >
                                            <div class="flex justify-between items-start">
                                                <h4 class="font-semibold">Item {{ index + 1 }}</h4>
                                                <Button
                                                    type="button"
                                                    variant="destructive"
                                                    size="sm"
                                                    @click="removeItem(index)"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <Label>Product *</Label>
                                                    <Select v-model="item.product_id" @update:modelValue="updateItemProduct(index, $event)">
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Select product" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem
                                                                v-for="product in products"
                                                                :key="product.id"
                                                                :value="product.id"
                                                            >
                                                                {{ product.name }} ({{ product.sku }}) - ${{ product.price }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError
                                                        :field="`items.${index}.product_id`"
                                                        :message="errors[`items.${index}.product_id`]"
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Quantity *</Label>
                                                    <Input
                                                        v-model.number="item.quantity"
                                                        type="number"
                                                        min="0.01"
                                                        step="0.01"
                                                    />
                                                    <InputError
                                                        :field="`items.${index}.quantity`"
                                                        :message="errors[`items.${index}.quantity`]"
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Unit Price *</Label>
                                                    <Input
                                                        v-model.number="item.unit_price"
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                    />
                                                    <InputError
                                                        :field="`items.${index}.unit_price`"
                                                        :message="errors[`items.${index}.unit_price`]"
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Line Total</Label>
                                                    <Input
                                                        :value="(item.quantity * item.unit_price).toFixed(2)"
                                                        readonly
                                                        class="bg-gray-50"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Total and Actions -->
                    <div class="mt-8 pt-6 border-t">
                        <div class="flex justify-between items-center mb-6">
                            <div class="text-2xl font-bold">
                                Total: ${{ totalAmount.toFixed(2) }}
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <Button
                                type="button"
                                variant="outline"
                                as="a"
                                href="/invoices"
                            >
                                Cancel
                            </Button>

                            <Button
                                type="submit"
                                :disabled="form.processing || form.items.length === 0"
                            >
                                <div v-if="form.processing" class="flex items-center">
                                    <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                    Creating...
                                </div>
                                <div v-else>
                                    Create Invoice
                                </div>
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import InputError from '@/components/InputError.vue'

import { Plus, Trash2, Loader2 } from 'lucide-vue-next'

import type { Customer, Product } from '@/stores/posStore'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
    { title: 'Create Invoice', href: '/invoices/create' },
]

interface Props {
    customers: Customer[]
    products: Product[]
}

const props = defineProps<Props>()

// Form interface
interface InvoiceItem {
    product_id: number | null
    quantity: number
    unit_price: number
}

interface InvoiceForm {
    customer_id: number | null
    date: string
    status: string
    items: InvoiceItem[]
    [key: string]: any
}

// Form initialization
const form = useForm<InvoiceForm>({
    customer_id: null,
    date: new Date().toISOString().split('T')[0],
    status: 'paid',
    items: []
})

// Form errors
const errors = ref<Record<string, string>>({})

// Computed
const totalAmount = computed(() => {
    return form.items.reduce((total, item) => {
        return total + (item.quantity * item.unit_price)
    }, 0)
})

// Methods
const addItem = () => {
    form.items.push({
        product_id: null,
        quantity: 1,
        unit_price: 0
    })
}

const removeItem = (index: number) => {
    form.items.splice(index, 1)
}

const updateItemProduct = (index: number, productId: any) => {
    if (!productId) return
    const numericProductId = Number(productId)
    const product = props.products.find(p => p.id === numericProductId)
    if (product) {
        form.items[index].unit_price = product.price
    }
}

const submit = () => {
    // Clear previous errors
    errors.value = {}

    // Basic validation
    if (!form.customer_id) {
        errors.value.customer_id = 'Customer is required'
        return
    }

    if (!form.date) {
        errors.value.date = 'Date is required'
        return
    }

    if (!form.status) {
        errors.value.status = 'Status is required'
        return
    }

    if (form.items.length === 0) {
        errors.value.items = 'At least one item is required'
        return
    }

    // Validate items
    let hasItemErrors = false
    form.items.forEach((item, index) => {
        if (!item.product_id) {
            errors.value[`items.${index}.product_id`] = 'Product is required'
            hasItemErrors = true
        }
        if (!item.quantity || item.quantity <= 0) {
            errors.value[`items.${index}.quantity`] = 'Quantity must be greater than 0'
            hasItemErrors = true
        }
        if (!item.unit_price || item.unit_price < 0) {
            errors.value[`items.${index}.unit_price`] = 'Unit price must be 0 or greater'
            hasItemErrors = true
        }
    })

    if (hasItemErrors) {
        return
    }

    form.post('/invoices', {
        onSuccess: () => {
            // Will be redirected by the controller
        },
        onError: (formErrors) => {
            errors.value = formErrors
        }
    })
}

// Initialize with one item
addItem()
</script>

<template>
    <Head title="Invoice Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold">Invoice #{{ invoice.id }}</h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                Created on {{ formatDate(invoice.created_at) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                :as="Link"
                                :href="route('invoices.index')"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm flex items-center gap-2"
                            >
                                <Icon name="ArrowLeft" class="w-3 h-3 md:w-4 md:h-4" />
                                <span>Back</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column - Invoice Items -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Invoice Items -->
                            <div class="border rounded-lg">
                                <div class="p-4 border-b bg-muted/30">
                                    <h3 class="text-lg font-semibold">Invoice Items</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-muted/20 border-b">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-sm font-medium">Product</th>
                                                <th class="px-4 py-3 text-left text-sm font-medium">Quantity</th>
                                                <th class="px-4 py-3 text-left text-sm font-medium">Unit Price</th>
                                                <th class="px-4 py-3 text-left text-sm font-medium">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in invoice.items" :key="item.id" class="border-b hover:bg-muted/20">
                                                <td class="px-4 py-4">
                                                    <div>
                                                        <div class="font-medium text-sm">{{ item.product.name }}</div>
                                                        <div class="text-xs text-muted-foreground">SKU: {{ item.product.sku }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-sm">{{ item.quantity }}</td>
                                                <td class="px-4 py-4 text-sm">${{ item.unit_price }}</td>
                                                <td class="px-4 py-4 text-sm font-medium">${{ item.total_amount }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Invoice Summary -->
                        <div class="space-y-6">
                            <!-- Customer Information -->
                            <div class="border rounded-lg">
                                <div class="p-4 border-b bg-muted/30">
                                    <h3 class="text-lg font-semibold">Customer Information</h3>
                                </div>
                                <div class="p-4">
                                    <div v-if="invoice.customer" class="space-y-3">
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Name</p>
                                            <p class="text-sm font-medium">{{ invoice.customer.name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                                            <p class="text-sm">{{ invoice.customer.email }}</p>
                                        </div>
                                        <div v-if="invoice.customer.phone">
                                            <p class="text-sm font-medium text-muted-foreground">Phone</p>
                                            <p class="text-sm">{{ invoice.customer.phone }}</p>
                                        </div>
                                        <div v-if="invoice.customer.address">
                                            <p class="text-sm font-medium text-muted-foreground">Address</p>
                                            <p class="text-sm">{{ invoice.customer.address }}</p>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <p class="text-sm text-muted-foreground">No customer information available</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Summary -->
                            <div class="border rounded-lg">
                                <div class="p-4 border-b bg-muted/30">
                                    <h3 class="text-lg font-semibold">Invoice Summary</h3>
                                </div>
                                <div class="p-4 space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Date</span>
                                        <span class="text-sm font-medium">{{ formatDate(invoice.date) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Status</span>
                                        <Badge :variant="getStatusVariant(invoice.status)" class="capitalize">
                                            {{ invoice.status }}
                                        </Badge>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Subtotal</span>
                                        <span class="text-sm font-medium">${{ invoice.subtotal_amount }}</span>
                                    </div>
                                    <div v-if="invoice.discount_value > 0" class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">
                                            Discount
                                            <span v-if="invoice.discount_type === 'percentage'">
                                                ({{ invoice.discount_value }}%)
                                            </span>
                                        </span>
                                        <span class="text-sm font-medium text-red-600">
                                            -${{ formatCurrency(calculateDiscountAmount()) }}
                                        </span>
                                    </div>
                                    <div class="border-t pt-3">
                                        <div class="flex justify-between">
                                            <span class="text-base font-semibold">Total</span>
                                            <span class="text-base font-semibold">${{ invoice.total_amount }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Actions -->
                            <div class="border rounded-lg">
                                <div class="p-4 border-b bg-muted/30">
                                    <h3 class="text-lg font-semibold">Status Actions</h3>
                                </div>
                                <div class="p-4 space-y-2">
                                    <Button
                                        v-if="invoice.status === 'pending'"
                                        @click="updateStatus('paid')"
                                        variant="outline"
                                        class="w-full text-green-600 border-green-600 hover:bg-green-50 cursor-pointer"
                                    >
                                        Mark as Paid
                                    </Button>
                                    <Button
                                        v-if="invoice.status === 'pending'"
                                        @click="updateStatus('canceled')"
                                        variant="outline"
                                        class="w-full text-red-600 border-red-600 hover:bg-red-50 cursor-pointer"
                                    >
                                        Cancel Invoice
                                    </Button>
                                    <Button
                                        v-if="invoice.status === 'canceled'"
                                        @click="updateStatus('pending')"
                                        variant="outline"
                                        class="w-full text-yellow-600 border-yellow-600 hover:bg-yellow-50 cursor-pointer"
                                    >
                                        Reactivate Invoice
                                    </Button>
                                    <div v-if="invoice.status === 'paid'" class="text-center py-4">
                                        <p class="text-sm text-muted-foreground">
                                            Invoice has been paid and cannot be modified
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import Icon from '@/components/Icon.vue'
import { toast } from 'vue-sonner'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
    { title: 'Details', href: '' },
]

interface InvoiceItem {
    id: number
    product: {
        id: number
        name: string
        sku: string
    }
    quantity: number
    unit_price: number
    total_amount: number
}

interface Invoice {
    id: number
    date: string
    customer?: {
        id: number
        name: string
        email: string
        phone?: string
        address?: string
    }
    subtotal_amount: number
    discount_type?: 'percentage' | 'fixed'
    discount_value: number
    total_amount: number
    status: 'pending' | 'paid' | 'canceled' | 'completed'
    items: InvoiceItem[]
    created_at: string
}

interface Props {
    invoice: Invoice
}

const props = defineProps<Props>()

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'paid':
            return 'success'
        case 'pending':
            return 'warning'
        case 'canceled':
            return 'destructive'
        default:
            return 'default'
    }
}

const calculateDiscountAmount = () => {
    if (!props.invoice.discount_value) return 0

    if (props.invoice.discount_type === 'percentage') {
        return (props.invoice.subtotal_amount * props.invoice.discount_value) / 100
    }

    return props.invoice.discount_value
}

const updateStatus = (status: string) => {
    router.patch(route('invoices.update-status', props.invoice.id), {
        status,
    }, {
        preserveState: false,
        onSuccess: () => {
            toast.success('Invoice status updated successfully')
        },
        onError: () => {
            toast.error('Error updating invoice status')
        },
    })
}
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

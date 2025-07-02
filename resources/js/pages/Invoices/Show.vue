<template>
    <Head :title="`Invoice #${invoice.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-6">

                <!-- Header -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Invoice #{{ invoice.id }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Created on {{ formatDate(invoice.created_at) }}
                        </p>
                    </div>

                    <div class="flex space-x-2">
                        <Button
                            variant="outline"
                            as="a"
                            :href="`/invoices/${invoice.id}/edit`"
                        >
                            <Edit class="w-4 h-4 mr-2" />
                            Edit
                        </Button>
                        <Button
                            variant="outline"
                            @click="printInvoice"
                        >
                            <Printer class="w-4 h-4 mr-2" />
                            Print
                        </Button>
                        <Button
                            variant="outline"
                            @click="downloadPDF"
                        >
                            <Download class="w-4 h-4 mr-2" />
                            PDF
                        </Button>
                    </div>
                </div>

                <!-- Invoice Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Customer Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Bill To</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div class="font-semibold text-lg" v-if="invoice.customer">
                                    {{ invoice.customer.first_name }} {{ invoice.customer.last_name }}
                                </div>
                                <div class="text-gray-600 dark:text-gray-400" v-if="invoice.customer">
                                    {{ invoice.customer.email }}
                                </div>
                                <div v-if="invoice.customer?.phone" class="text-gray-600 dark:text-gray-400">
                                    {{ invoice.customer.phone }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Invoice Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Invoice Number:</span>
                                    <span class="font-semibold">#{{ invoice.id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Date:</span>
                                    <span class="font-semibold">{{ formatDate(invoice.date) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                    <Badge :variant="getStatusVariant(invoice.status)">
                                        {{ getStatusText(invoice.status) }}
                                    </Badge>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Created by:</span>
                                    <span class="font-semibold">{{ invoice.user?.name || 'System' }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Items Table -->
                <Card class="mb-8">
                    <CardHeader>
                        <CardTitle>Items</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>SKU</TableHead>
                                        <TableHead class="text-center">Qty</TableHead>
                                        <TableHead class="text-right">Price</TableHead>
                                        <TableHead class="text-right">Total</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="item in invoice.items" :key="item.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-semibold">{{ item.product?.name }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell class="font-mono text-sm">
                                            {{ item.product?.sku }}
                                        </TableCell>
                                        <TableCell class="text-center font-semibold">
                                            {{ item.quantity }}
                                        </TableCell>
                                        <TableCell class="text-right font-semibold">
                                            ${{ Number(item.unit_price).toFixed(2) }}
                                        </TableCell>
                                        <TableCell class="text-right font-bold">
                                            ${{ Number(item.line_total).toFixed(2) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Summary -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex justify-end">
                            <div class="w-64 space-y-4">
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-lg font-semibold">Subtotal:</span>
                                    <span class="text-lg font-semibold">${{ Number(subtotal).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b">
                                    <span class="text-lg font-semibold">Tax:</span>
                                    <span class="text-lg font-semibold">${{ Number(tax).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300">
                                    <span class="text-2xl font-bold">Total:</span>
                                    <span class="text-2xl font-bold text-green-600">
                                        ${{ Number(invoice.total_amount).toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex justify-between items-center mt-8 pt-6 border-t">
                    <Button
                        variant="outline"
                        as="a"
                        href="/invoices"
                    >
                        Back to Invoices
                    </Button>

                    <div class="flex space-x-2">
                        <Button
                            v-if="invoice.status !== 'canceled'"
                            variant="destructive"
                            @click="cancelInvoice"
                        >
                            Cancel Invoice
                        </Button>

                        <Button
                            v-if="invoice.status === 'pending'"
                            @click="markAsPaid"
                        >
                            Mark as Paid
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import Badge from '@/components/ui/SimpleBadge.vue'

import { Edit, Printer, Download } from 'lucide-vue-next'

import type { Invoice } from '@/stores/invoiceStore'
import { type BreadcrumbItem } from '@/types'

interface Props {
    invoice: Invoice
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
    { title: `Invoice #${props.invoice.id}`, href: `/invoices/${props.invoice.id}` },
]

// Computed
const subtotal = computed(() => {
    return props.invoice.items?.reduce((sum, item) => sum + Number(item.line_total), 0) || 0
})

const tax = computed(() => {
    return subtotal.value * 0.0 // No tax for now
})

// Methods
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'paid': return 'default'
        case 'pending': return 'secondary'
        case 'canceled': return 'destructive'
        default: return 'outline'
    }
}

const getStatusText = (status: string) => {
    switch (status) {
        case 'paid': return 'Paid'
        case 'pending': return 'Pending'
        case 'canceled': return 'Cancelled'
        default: return status
    }
}

const printInvoice = () => {
    window.print()
}

const downloadPDF = () => {
    // TODO: Implement PDF download
    toast.info('PDF download will be implemented soon')
}

const markAsPaid = () => {
    router.put(`/invoices/${props.invoice.id}`, {
        customer_id: props.invoice.customer_id,
        date: props.invoice.date,
        status: 'paid'
    }, {
        onSuccess: () => {
            toast.success('Invoice marked as paid')
        },
        onError: () => {
            toast.error('Failed to update invoice')
        }
    })
}

const cancelInvoice = () => {
    if (confirm('Are you sure you want to cancel this invoice? This action cannot be undone.')) {
        router.put(`/invoices/${props.invoice.id}`, {
            customer_id: props.invoice.customer_id,
            date: props.invoice.date,
            status: 'canceled'
        }, {
            onSuccess: () => {
                toast.success('Invoice cancelled')
            },
            onError: () => {
                toast.error('Failed to cancel invoice')
            }
        })
    }
}
</script>

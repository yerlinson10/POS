<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Invoice #{{ invoice?.id }}</DialogTitle>
                <DialogDescription>
                    Created on {{ invoice ? formatDate(invoice.created_at) : '' }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="invoice" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Information -->
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold">Customer Information</h3>
                        <div v-if="invoice.customer" class="space-y-2">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Name</p>
                                <p class="text-sm text-gray-900">{{ invoice.customer.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Email</p>
                                <p class="text-sm text-gray-900">{{ invoice.customer.email }}</p>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-sm text-gray-500">No customer information available</p>
                        </div>
                    </div>

                    <!-- Invoice Summary -->
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold">Invoice Summary</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Date</span>
                                <span class="text-sm font-medium">{{ formatDate(invoice.date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Status</span>
                                <Badge :variant="getStatusVariant(invoice.status)">
                                    {{ invoice.status }}
                                </Badge>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Items</span>
                                <span class="text-sm font-medium">{{ invoice.items_count }} item{{ invoice.items_count !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="border-t pt-2">
                                <div class="flex justify-between">
                                    <span class="text-base font-semibold">Total</span>
                                    <span class="text-base font-semibold">${{ formatCurrency(invoice.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Actions -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Status Actions</h3>
                    <div class="flex gap-2">
                        <Button
                            v-if="invoice.status === 'pending'"
                            @click="updateStatus('paid')"
                            variant="outline"
                            class="text-green-600 border-green-600 hover:bg-green-50"
                        >
                            Mark as Paid
                        </Button>
                        <Button
                            v-if="invoice.status === 'paid'"
                            @click="updateStatus('canceled')"
                            variant="outline"
                            class="text-red-600 border-red-600 hover:bg-red-50"
                        >
                            Cancel Invoice
                        </Button>
                        <Button
                            v-if="invoice.status === 'canceled'"
                            @click="updateStatus('pending')"
                            variant="outline"
                            class="text-yellow-600 border-yellow-600 hover:bg-yellow-50"
                        >
                            Reactivate
                        </Button>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="open = false">
                    Close
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
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
import { Badge } from '@/components/ui/badge'
import { toast } from 'vue-sonner'

interface Invoice {
    id: number
    date: string
    customer?: {
        id: number
        name: string
        email: string
    }
    total_amount: number
    status: 'pending' | 'paid' | 'canceled'
    items_count: number
    created_at: string
}

interface Props {
    open: boolean
    invoice: Invoice | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:open': [value: boolean]
}>()

const open = computed({
    get: () => props.open,
    set: (value: boolean) => emit('update:open', value)
})

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

const updateStatus = (status: string) => {
    if (!props.invoice) return

    router.patch(route('invoices.update-status', props.invoice.id), {
        status,
    }, {
        onSuccess: () => {
            toast.success('Invoice status updated successfully')
            open.value = false
        },
        onError: () => {
            toast.error('Error updating invoice status')
        },
    })
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="w-[95vw] max-w-md sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg md:text-xl" :class="getTitleClass">
                    <Icon :name="getTitleIcon" class="w-4 h-4 md:w-5 md:h-5" />
                    {{ getTitleText }}
                </DialogTitle>            <DialogDescription class="text-sm">
                {{ getDescriptionText }}
                <br><span class="text-xs text-muted-foreground mt-1 block">Press F4 to open PDF for printing</span>
            </DialogDescription>
            </DialogHeader>

            <div v-if="sale" class="space-y-3 md:space-y-4">
                <!-- Sale Summary -->
                <div class="p-3 md:p-4 bg-accent/50 rounded-lg space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs md:text-sm font-medium">Invoice #:</span>
                        <span class="text-xs md:text-sm">{{ sale.id }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-xs md:text-sm font-medium">Date:</span>
                        <span class="text-xs md:text-sm">{{ formatDate(sale.date) }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-xs md:text-sm font-medium">Status:</span>
                        <span class="text-xs md:text-sm px-2 py-1 rounded-full" :class="getStatusBadgeClass">
                            {{ getStatusText }}
                        </span>
                    </div>

                    <div v-if="sale.customer" class="flex justify-between items-center">
                        <span class="text-xs md:text-sm font-medium">Customer:</span>
                        <span class="text-xs md:text-sm">{{ sale.customer.full_name }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-xs md:text-sm font-medium">Items:</span>
                        <span class="text-xs md:text-sm">{{ sale.items?.length || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs md:text-sm font-medium">Subtotal:</span>
                        <span class="text-xs md:text-sm">${{ Number(sale.subtotal).toFixed(2) }}</span>
                    </div>

                    <div v-if="sale.discount_amount > 0" class="flex justify-between items-center text-green-600">
                        <span class="text-xs md:text-sm font-medium">
                            Discount ({{ sale.discount_type === 'percentage' ? `${sale.discount_value}%` : 'Fixed' }}):
                        </span>
                        <span class="text-xs md:text-sm">-${{ Number(sale.discount_amount).toFixed(2) }}</span>
                    </div>

                    <div class="flex justify-between items-center text-base md:text-lg font-bold border-t pt-2">
                        <span>Total:</span>
                        <span>${{ Number(sale.total_amount).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Items List -->
                <div v-if="sale.items && sale.items.length > 0" class="space-y-2">
                    <h4 class="text-xs md:text-sm font-medium">Items Sold:</h4>
                    <div class="max-h-24 md:max-h-32 overflow-y-auto space-y-1">
                        <div v-for="item in sale.items" :key="item.id"
                            class="flex justify-between items-center text-xs md:text-sm p-2 bg-accent/30 rounded">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ item.product?.name }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ item.quantity }} × ${{ Number(item.unit_price).toFixed(2) }}
                                </div>
                            </div>
                            <div class="font-medium">
                                ${{ Number(item.line_total).toFixed(2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <Button @click="startNewSale" class="flex-1 cursor-pointer h-9 md:h-10 text-sm">
                        <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                        New Sale
                    </Button>
                    <Button @click="printReceipt" variant="outline" class="flex-1 cursor-pointer h-9 md:h-10 text-sm"
                        :disabled="isPrinting">
                        <Icon v-if="!isPrinting" name="Printer" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                        <Icon v-else name="Loader2" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2 animate-spin" />
                        {{ isPrinting ? 'Opening PDF...' : 'Open PDF to Print' }}
                        <span v-if="!isPrinting" class="ml-2 text-xs opacity-75">(F4)</span>
                    </Button>
                </div>
            </div>

            <DialogFooter>
                <Button @click="closeDialog" variant="outline"
                    class="cursor-pointer w-full sm:w-auto h-9 md:h-10 text-sm">
                    Close
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import type { Sale } from '../../../types/pos'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '../../../components/ui/dialog'
import { Button } from '../../../components/ui/button'
import Icon from '../../../components/Icon.vue'
import { toast } from 'vue-sonner'

interface Props {
    open: boolean
    sale?: Sale | null
    isPrinting?: boolean
}

interface Emits {
    (e: 'update:open', value: boolean): void
    (e: 'print-invoice', invoiceId: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Local state
const isOpen = ref(props.open)

// Computed properties for status-specific content
const getTitleClass = computed(() => {
    if (!props.sale) return 'text-green-600'

    switch (props.sale.status) {
        case 'paid':
            return 'text-green-600'
        case 'pending':
            return 'text-yellow-600'
        default:
            return 'text-green-600'
    }
})

const getTitleIcon = computed(() => {
    if (!props.sale) return 'CheckCircle'

    switch (props.sale.status) {
        case 'paid':
            return 'CheckCircle'
        case 'pending':
            return 'Clock'
        default:
            return 'CheckCircle'
    }
})

const getTitleText = computed(() => {
    if (!props.sale) return 'Sale Completed Successfully!'

    switch (props.sale.status) {
        case 'paid':
            return 'Payment Completed Successfully!'
        case 'pending':
            return 'Invoice Created - Payment Pending'
        default:
            return 'Sale Completed Successfully!'
    }
})

const getDescriptionText = computed(() => {
    if (!props.sale) return 'Your sale has been processed and recorded.'

    switch (props.sale.status) {
        case 'paid':
            return 'Payment has been processed and inventory has been updated.'
        case 'pending':
            return 'Invoice has been created and is waiting for payment. Inventory will be updated upon payment.'
        default:
            return 'Your sale has been processed and recorded.'
    }
})

const getStatusText = computed(() => {
    if (!props.sale) return 'Completed'

    switch (props.sale.status) {
        case 'paid':
            return 'Paid'
        case 'pending':
            return 'Pending Payment'
        default:
            return 'Completed'
    }
})

const getStatusBadgeClass = computed(() => {
    if (!props.sale) return 'bg-green-100 text-green-800'

    switch (props.sale.status) {
        case 'paid':
            return 'bg-green-100 text-green-800'
        case 'pending':
            return 'bg-yellow-100 text-yellow-800'
        default:
            return 'bg-green-100 text-green-800'
    }
})

// Methods
const formatDate = (dateString: string): string => {
    const date = new Date(dateString)
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
}

const startNewSale = () => {
    closeDialog()
    // The parent component will handle resetting the POS state
}

const printReceipt = () => {
    if (props.sale?.id) {
        emit('print-invoice', props.sale.id)
    } else {
        toast.error('No invoice available to print')
    }
}

const closeDialog = () => {
    isOpen.value = false
    emit('update:open', false)
}

// Watch for prop changes
watch(() => props.open, (newValue) => {
    isOpen.value = newValue
})

watch(isOpen, (newValue) => {
    if (!newValue) {
        emit('update:open', false)
    }
})
</script>

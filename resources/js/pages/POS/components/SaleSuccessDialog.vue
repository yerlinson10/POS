<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="w-[95vw] max-w-md sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-green-600 text-lg md:text-xl">
                    <Icon name="CheckCircle" class="w-4 h-4 md:w-5 md:h-5" />
                    Sale Completed Successfully!
                </DialogTitle>
                <DialogDescription class="text-sm">
                    Your sale has been processed and recorded.
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
                    <Button @click="printReceipt" variant="outline" class="flex-1 cursor-pointer h-9 md:h-10 text-sm">
                        <Icon name="Printer" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                        Print Receipt
                    </Button>
                </div>
            </div>

            <DialogFooter>
                <Button @click="closeDialog" variant="outline" class="cursor-pointer w-full sm:w-auto h-9 md:h-10 text-sm">
                    Close
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
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
}

interface Emits {
    (e: 'update:open', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Local state
const isOpen = ref(props.open)

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
    // In a real application, you would implement receipt printing here
    // This could involve opening a print dialog, sending to a receipt printer, etc.
    toast.success('Receipt printing feature would be implemented here')
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

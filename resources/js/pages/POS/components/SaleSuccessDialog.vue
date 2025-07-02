<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-green-600">
                    <Icon name="CheckCircle" class="w-5 h-5" />
                    Sale Completed Successfully!
                </DialogTitle>
                <DialogDescription>
                    Your sale has been processed and recorded.
                </DialogDescription>
            </DialogHeader>

            <div v-if="sale" class="space-y-4">
                <!-- Sale Summary -->
                <div class="p-4 bg-accent/50 rounded-lg space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Invoice #:</span>
                        <span class="text-sm">{{ sale.id }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Date:</span>
                        <span class="text-sm">{{ formatDate(sale.date) }}</span>
                    </div>

                    <div v-if="sale.customer" class="flex justify-between items-center">
                        <span class="text-sm font-medium">Customer:</span>
                        <span class="text-sm">{{ sale.customer.full_name }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Items:</span>
                        <span class="text-sm">{{ sale.items?.length || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Subtotal:</span>
                        <span class="text-sm">${{ Number(sale.subtotal).toFixed(2) }}</span>
                    </div>

                    <div v-if="sale.discount_amount > 0" class="flex justify-between items-center text-green-600">
                        <span class="text-sm font-medium">
                            Discount ({{ sale.discount_type === 'percentage' ? `${sale.discount_value}%` : 'Fixed' }}):
                        </span>
                        <span class="text-sm">-${{ Number(sale.discount_amount).toFixed(2) }}</span>
                    </div>

                    <div class="flex justify-between items-center text-lg font-bold border-t pt-2">
                        <span>Total:</span>
                        <span>${{ Number(sale.total_amount).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Items List -->
                <div v-if="sale.items && sale.items.length > 0" class="space-y-2">
                    <h4 class="text-sm font-medium">Items Sold:</h4>
                    <div class="max-h-32 overflow-y-auto space-y-1">
                        <div v-for="item in sale.items" :key="item.id"
                            class="flex justify-between items-center text-sm p-2 bg-accent/30 rounded">
                            <div class="flex-1">
                                <div class="font-medium">{{ item.product?.name }}</div>
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
                <div class="flex gap-2">
                    <Button @click="startNewSale" class="flex-1">
                        <Icon name="Plus" class="w-4 h-4 mr-2" />
                        New Sale
                    </Button>
                    <Button @click="printReceipt" variant="outline" class="flex-1">
                        <Icon name="Printer" class="w-4 h-4 mr-2" />
                        Print Receipt
                    </Button>
                </div>
            </div>

            <DialogFooter>
                <Button @click="closeDialog" variant="outline">
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

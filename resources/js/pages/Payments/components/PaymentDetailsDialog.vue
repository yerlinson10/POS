<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon name="Receipt" class="w-5 h-5 text-blue-600" />
                    Payment Details
                </DialogTitle>
                <DialogDescription>
                    Complete information for this payment record
                </DialogDescription>
            </DialogHeader>

            <div v-if="payment" class="space-y-6">
                <!-- Payment Type & Amount -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Payment Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-xs text-muted-foreground">Type</Label>
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': payment.payment_type === 'income',
                                        'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': payment.payment_type === 'expense'
                                    }">
                                    <Icon :name="payment.payment_type === 'income' ? 'ArrowDown' : 'ArrowUp'" class="w-3 h-3 mr-1" />
                                    {{ payment.payment_type === 'income' ? 'Income' : 'Expense' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Amount</Label>
                            <div class="text-2xl font-bold" :class="{
                                'text-green-600': payment.payment_type === 'income',
                                'text-red-600': payment.payment_type === 'expense'
                            }">
                                {{ payment.payment_type === 'income' ? '+' : '-' }}${{ Number(payment.amount).toFixed(2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method & Category -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Method & Category</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-xs text-muted-foreground">Payment Method</Label>
                            <div class="flex items-center gap-2 mt-1">
                                <Icon :name="getPaymentMethodIcon(payment.payment_method)" class="w-4 h-4 text-muted-foreground" />
                                <span class="text-sm font-medium capitalize">{{ payment.payment_method.replace('_', ' ') }}</span>
                            </div>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Category</Label>
                            <div class="text-sm font-medium capitalize mt-1">
                                {{ payment.category ? payment.category.replace('_', ' ') : 'General' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Information -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Date Information</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <Label class="text-xs text-muted-foreground">Created On</Label>
                            <div class="flex items-center gap-2 mt-1">
                                <Icon name="Calendar" class="w-4 h-4 text-muted-foreground" />
                                <span class="text-sm">{{ formatDate(payment.created_at) }}</span>
                                <span class="text-xs text-muted-foreground">at {{ formatTime(payment.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="payment.description" class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Description</h3>
                    <div class="p-3 bg-muted/50 rounded-lg text-sm">
                        {{ payment.description }}
                    </div>
                </div>

                <!-- Related Entity -->
                <div v-if="payment.related_entity" class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Related Entity</h3>
                    <div class="flex items-center gap-2">
                        <Icon name="Link" class="w-4 h-4 text-muted-foreground" />
                        <span class="text-sm">{{ payment.related_entity }}</span>
                    </div>
                </div>

                <!-- Financial Impact -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Financial Impact</h3>
                    <div class="p-4 rounded-lg" :class="{
                        'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800': payment.payment_type === 'income',
                        'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800': payment.payment_type === 'expense'
                    }">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium" :class="{
                                'text-green-800 dark:text-green-200': payment.payment_type === 'income',
                                'text-red-800 dark:text-red-200': payment.payment_type === 'expense'
                            }">
                                {{ payment.payment_type === 'income' ? 'Added to balance:' : 'Subtracted from balance:' }}
                            </span>
                            <span class="text-lg font-bold" :class="{
                                'text-green-600 dark:text-green-400': payment.payment_type === 'income',
                                'text-red-600 dark:text-red-400': payment.payment_type === 'expense'
                            }">
                                {{ payment.payment_type === 'income' ? '+' : '-' }}${{ Number(payment.amount).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                    <Button 
                        @click="openEditDialog"
                        variant="outline"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <Icon name="Edit" class="w-4 h-4" />
                        Edit Payment
                    </Button>
                    
                    <Button 
                        @click="duplicatePayment"
                        variant="outline"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <Icon name="Copy" class="w-4 h-4" />
                        Duplicate
                    </Button>
                </div>
            </div>

            <DialogFooter>
                <Button 
                    variant="outline" 
                    @click="closeDialog"
                    class="cursor-pointer"
                >
                    Close
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import Icon from '@/components/Icon.vue'
import { toast } from 'vue-sonner'

interface Payment {
    id: number
    payment_type: 'income' | 'expense'
    amount: number
    payment_method: string
    category?: string
    description?: string
    related_entity?: string
    created_at: string
}

const props = defineProps<{
    open: boolean
    payment: Payment | null
}>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'edit-payment': [payment: Payment]
    'duplicate-payment': [payment: Payment]
}>()

const closeDialog = () => {
    emit('update:open', false)
}

const openEditDialog = () => {
    if (props.payment) {
        emit('edit-payment', props.payment)
        closeDialog()
    }
}

const duplicatePayment = () => {
    if (props.payment) {
        emit('duplicate-payment', props.payment)
        toast.success('Payment ready to duplicate - edit and save')
        closeDialog()
    }
}

// Utility functions
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    })
}

const getPaymentMethodIcon = (method: string) => {
    const icons = {
        cash: 'Banknote',
        card: 'CreditCard',
        bank_transfer: 'Building2',
        check: 'FileText'
    }
    return icons[method as keyof typeof icons] || 'DollarSign'
}
</script>

<style scoped>
/* Custom styling for financial impact section */
.transition-all {
    transition: all 0.3s ease-in-out;
}
</style>

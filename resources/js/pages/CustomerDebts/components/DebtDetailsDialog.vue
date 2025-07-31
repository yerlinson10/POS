<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon name="Info" class="w-5 h-5 text-blue-600" />
                    Debt Details
                </DialogTitle>
                <DialogDescription>
                    Complete information for this customer debt
                </DialogDescription>
            </DialogHeader>

            <div v-if="debt" class="space-y-6">
                <!-- Customer & Invoice Info -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Customer & Invoice Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-xs text-muted-foreground">Customer</Label>
                            <div class="font-medium">{{ debt.customer_name }}</div>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Invoice</Label>
                            <div class="font-mono">#{{ debt.invoice_id }}</div>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Created Date</Label>
                            <div class="text-sm">{{ formatDate(debt.created_at) }}</div>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Due Date</Label>
                            <div class="text-sm" :class="{
                                'text-red-600 font-semibold': isOverdue(debt.due_date),
                                'text-yellow-600': isDueSoon(debt.due_date)
                            }">
                                {{ formatDate(debt.due_date) }}
                                <span v-if="isOverdue(debt.due_date)" class="block text-xs text-red-600">
                                    ({{ getDaysOverdue(debt.due_date) }} days overdue)
                                </span>
                                <span v-else-if="isDueSoon(debt.due_date)" class="block text-xs text-yellow-600">
                                    (Due in {{ getDaysUntilDue(debt.due_date) }} days)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Financial Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <span class="text-sm font-medium">Original Amount:</span>
                            <span class="text-lg font-bold text-blue-600">
                                ${{ Number(debt.total_amount).toFixed(2) }}
                            </span>
                        </div>
                        
                        <div v-if="debt.paid_amount > 0" class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <span class="text-sm font-medium">Amount Paid:</span>
                            <span class="text-lg font-bold text-green-600">
                                ${{ Number(debt.paid_amount).toFixed(2) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <span class="text-sm font-medium">Remaining Amount:</span>
                            <span class="text-lg font-bold text-red-600">
                                ${{ Number(debt.remaining_amount).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Status Information -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Status Information</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-sm">Current Status:</span>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
                            :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': debt.status === 'paid',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': debt.status === 'pending',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400': debt.status === 'partial',
                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': debt.status === 'overdue'
                            }">
                            {{ getStatusLabel(debt.status) }}
                        </span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs text-muted-foreground">
                            <span>Payment Progress</span>
                            <span>{{ Math.round(paymentPercentage) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="h-2 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-green-500': debt.status === 'paid',
                                    'bg-blue-500': debt.status === 'partial',
                                    'bg-gray-400': debt.status === 'pending',
                                    'bg-red-500': debt.status === 'overdue'
                                }"
                                :style="{ width: `${paymentPercentage}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="debt.description" class="space-y-4">
                    <h3 class="text-sm font-semibold text-foreground border-b pb-2">Description</h3>
                    <div class="p-3 bg-muted/50 rounded-lg text-sm">
                        {{ debt.description }}
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                    <Button 
                        v-if="debt.status !== 'paid'" 
                        @click="openPaymentDialog"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <Icon name="DollarSign" class="w-4 h-4" />
                        Make Payment
                    </Button>
                    
                    <Button 
                        variant="outline"
                        as="a"
                        :href="`/invoices/${debt.invoice_id}`"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <Icon name="FileText" class="w-4 h-4" />
                        View Invoice
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
import { computed } from 'vue'
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

interface CustomerDebt {
    id: number
    customer_name: string
    invoice_id: number
    total_amount: number
    paid_amount: number
    remaining_amount: number
    due_date: string
    status: string
    description?: string
    created_at: string
}

const props = defineProps<{
    open: boolean
    debt: CustomerDebt | null
}>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'open-payment-dialog': []
}>()

const paymentPercentage = computed(() => {
    if (!props.debt || props.debt.total_amount === 0) return 0
    return (props.debt.paid_amount / props.debt.total_amount) * 100
})

const closeDialog = () => {
    emit('update:open', false)
}

const openPaymentDialog = () => {
    emit('open-payment-dialog')
    closeDialog()
}

// Utility functions
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const isOverdue = (dueDateString: string) => {
    const dueDate = new Date(dueDateString)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return dueDate < today
}

const isDueSoon = (dueDateString: string) => {
    const dueDate = new Date(dueDateString)
    const today = new Date()
    const diffTime = dueDate.getTime() - today.getTime()
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays > 0 && diffDays <= 7
}

const getDaysOverdue = (dueDateString: string) => {
    const dueDate = new Date(dueDateString)
    const today = new Date()
    const diffTime = today.getTime() - dueDate.getTime()
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getDaysUntilDue = (dueDateString: string) => {
    const dueDate = new Date(dueDateString)
    const today = new Date()
    const diffTime = dueDate.getTime() - today.getTime()
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getStatusLabel = (status: string) => {
    const labels = {
        pending: 'Pending',
        partial: 'Partial Payment',
        paid: 'Fully Paid',
        overdue: 'Overdue'
    }
    return labels[status as keyof typeof labels] || status
}
</script>

<style scoped>
/* Custom styling for progress bar and status indicators */
.transition-all {
    transition: all 0.3s ease-in-out;
}
</style>

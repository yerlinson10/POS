<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon name="CreditCard" class="w-5 h-5 text-orange-600" />
                    Create Customer Debt
                </DialogTitle>
                <DialogDescription>
                    Customer cannot pay the full amount. Create a debt record for the remaining balance.
                </DialogDescription>
            </DialogHeader>

            <div v-if="customerName && totalAmount" class="space-y-4">
                <!-- Order Summary -->
                <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Customer:</span>
                            <span class="text-sm font-semibold">{{ customerName }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Amount:</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                ${{ Number(totalAmount).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form @submit.prevent="createDebt" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="paid_amount" class="text-sm font-medium flex items-center gap-1">
                            <Icon name="DollarSign" class="w-3 h-3" />
                            Amount Paid Now
                        </Label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">$</span>
                            <Input 
                                id="paid_amount" 
                                type="number" 
                                v-model.number="form.paid_amount" 
                                step="0.01" 
                                min="0" 
                                :max="totalAmount"
                                placeholder="0.00" 
                                class="h-10 pl-8"
                                :class="{ 'border-destructive focus:border-destructive': errors.paid_amount }" 
                            />
                        </div>
                        <InputError field="paid_amount" :message="errors.paid_amount" />
                        <div class="flex gap-2 mt-2">
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm"
                                @click="setNoPayment"
                                class="text-xs cursor-pointer"
                            >
                                No Payment
                            </Button>
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm"
                                @click="setPartialPayment"
                                class="text-xs cursor-pointer"
                            >
                                50%
                            </Button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="due_date" class="text-sm font-medium flex items-center gap-1">
                            <Icon name="Calendar" class="w-3 h-3" />
                            Due Date
                            <span class="text-destructive text-xs">*</span>
                        </Label>
                        <Input 
                            id="due_date" 
                            type="date" 
                            v-model="form.due_date" 
                            required
                            :min="today"
                            class="h-10"
                            :class="{ 'border-destructive focus:border-destructive': errors.due_date }" 
                        />
                        <InputError field="due_date" :message="errors.due_date" />
                        <div class="flex gap-2 mt-2">
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm"
                                @click="setDueDate(7)"
                                class="text-xs cursor-pointer"
                            >
                                7 days
                            </Button>
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm"
                                @click="setDueDate(15)"
                                class="text-xs cursor-pointer"
                            >
                                15 days
                            </Button>
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm"
                                @click="setDueDate(30)"
                                class="text-xs cursor-pointer"
                            >
                                30 days
                            </Button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description" class="text-sm font-medium">Description</Label>
                        <Textarea 
                            id="description" 
                            v-model="form.description"
                            placeholder="Add notes about this debt..."
                            class="min-h-[80px] resize-none"
                            :class="{ 'border-destructive focus:border-destructive': errors.description }" 
                        />
                        <InputError field="description" :message="errors.description" />
                    </div>

                    <!-- Debt Summary -->
                    <div class="p-4 rounded-lg bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-orange-800 dark:text-orange-200">Total Amount:</span>
                                <span class="font-semibold">
                                    ${{ Number(totalAmount).toFixed(2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-orange-800 dark:text-orange-200">Amount Paid:</span>
                                <span class="font-semibold text-green-600">
                                    ${{ Number(form.paid_amount || 0).toFixed(2) }}
                                </span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-orange-800 dark:text-orange-200 font-medium">Debt Amount:</span>
                                <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                    ${{ debtAmount.toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="flex flex-col sm:flex-row gap-2">
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="closeDialog"
                            :disabled="form.processing"
                            class="cursor-pointer"
                        >
                            Cancel
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="form.processing || !form.due_date || debtAmount <= 0"
                            class="cursor-pointer"
                        >
                            <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin mr-2" />
                            <Icon v-else name="CreditCard" class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Creating...' : 'Create Debt & Complete Sale' }}
                        </Button>
                    </DialogFooter>
                </form>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
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
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'
import { toast } from 'vue-sonner'

interface DebtForm {
    paid_amount: number
    due_date: string
    description: string
}

const props = defineProps<{
    open: boolean
    customerName?: string
    totalAmount?: number
}>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'debt-created': [debtData: { paid_amount: number; due_date: string; description: string }]
}>()

const form = useForm<DebtForm>({
    paid_amount: 0,
    due_date: '',
    description: '',
})

const errors = computed(() => form.errors)

const debtAmount = computed(() => {
    const total = props.totalAmount || 0
    const paid = form.paid_amount || 0
    return Math.max(0, total - paid)
})

const today = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const setNoPayment = () => {
    form.paid_amount = 0
}

const setPartialPayment = () => {
    if (props.totalAmount) {
        form.paid_amount = Math.round((props.totalAmount * 0.5) * 100) / 100
    }
}

const setDueDate = (days: number) => {
    const date = new Date()
    date.setDate(date.getDate() + days)
    form.due_date = date.toISOString().split('T')[0]
}

const closeDialog = () => {
    emit('update:open', false)
    resetForm()
}

const resetForm = () => {
    form.reset()
    form.clearErrors()
}

const createDebt = async () => {
    if (!props.customerName || !props.totalAmount) return

    try {
        // Don't actually submit the form, just emit the data
        const debtData = {
            paid_amount: form.paid_amount || 0,
            due_date: form.due_date,
            description: form.description || `Debt from sale to ${props.customerName}`,
        }

        emit('debt-created', debtData)
        toast.success('Debt will be created with the sale')
        closeDialog()
    } catch {
        toast.error('An unexpected error occurred')
    }
}

// Reset form when dialog opens/closes
watch([() => props.open], ([isOpen]) => {
    if (isOpen) {
        resetForm()
        // Set default due date to 15 days from now
        setDueDate(15)
    } else {
        resetForm()
    }
}, { immediate: false })
</script>

<style scoped>
/* Price input styling */
.pl-8 {
    padding-left: 2rem;
}

/* Form validation states */
.border-destructive {
    border-color: hsl(var(--destructive));
}

.border-destructive:focus {
    border-color: hsl(var(--destructive));
    box-shadow: 0 0 0 3px hsl(var(--destructive) / 0.1);
}
</style>

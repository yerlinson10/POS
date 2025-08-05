<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon name="DollarSign" class="w-5 h-5 text-green-600" />
                    Pay Customer Debt
                </DialogTitle>
                <DialogDescription>
                    Process payment for debt from <strong>{{ debt?.customer_name }}</strong>
                </DialogDescription>
            </DialogHeader>

            <div v-if="debt" class="space-y-4">
                <!-- Debt Info Display -->
                <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Invoice:</span>
                            <span class="text-sm font-mono">#{{ debt.invoice_id }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Amount:</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                ${{ Number(debt.original_amount).toFixed(2) }}
                            </span>
                        </div>
                        <div v-if="debt.paid_amount > 0" class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Already Paid:</span>
                            <span class="text-sm font-semibold text-green-600">
                                ${{ Number(debt.paid_amount).toFixed(2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-t pt-2">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Remaining:</span>
                            <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                ${{ Number(debt.remaining_amount).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form @submit.prevent="processPayment" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="payment_amount" class="text-sm font-medium flex items-center gap-1">
                            <Icon name="DollarSign" class="w-3 h-3" />
                            Payment Amount
                            <span class="text-destructive text-xs">*</span>
                        </Label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">$</span>
                            <Input
                                id="payment_amount"
                                type="number"
                                v-model.number="form.amount"
                                step="0.01"
                                min="0"
                                :max="debt.remaining_amount"
                                required
                                placeholder="0.00"
                                class="h-10 pl-8"
                                :class="{ 'border-destructive focus:border-destructive': errors.amount }"
                            />
                        </div>
                        <InputError field="amount" :message="errors.amount" />
                        <div class="flex gap-2 mt-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="setPartialPayment"
                                class="text-xs cursor-pointer"
                            >
                                50%
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="setFullPayment"
                                class="text-xs cursor-pointer"
                            >
                                Pay Full
                            </Button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="payment_method" class="text-sm font-medium flex items-center gap-1">
                            <Icon name="CreditCard" class="w-3 h-3" />
                            Payment Method
                            <span class="text-destructive text-xs">*</span>
                        </Label>
                        <Select v-model="form.payment_method" required>
                            <SelectTrigger class="w-full h-10">
                                <SelectValue placeholder="Select payment method" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">
                                    <div class="flex items-center gap-2">
                                        <Icon name="Banknote" class="w-4 h-4" />
                                        Cash
                                    </div>
                                </SelectItem>
                                <SelectItem value="card">
                                    <div class="flex items-center gap-2">
                                        <Icon name="CreditCard" class="w-4 h-4" />
                                        Credit/Debit Card
                                    </div>
                                </SelectItem>
                                <SelectItem value="bank_transfer">
                                    <div class="flex items-center gap-2">
                                        <Icon name="Building2" class="w-4 h-4" />
                                        Bank Transfer
                                    </div>
                                </SelectItem>
                                <SelectItem value="check">
                                    <div class="flex items-center gap-2">
                                        <Icon name="FileText" class="w-4 h-4" />
                                        Check
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError field="payment_method" :message="errors.payment_method" />
                    </div>

                    <div class="space-y-2">
                        <Label for="notes" class="text-sm font-medium">Payment Notes</Label>
                        <Textarea
                            id="notes"
                            v-model="form.notes"
                            placeholder="Add payment details or reference..."
                            class="min-h-[80px] resize-none"
                            :class="{ 'border-destructive focus:border-destructive': errors.notes }"
                        />
                        <InputError field="notes" :message="errors.notes" />
                    </div>

                    <!-- Payment Summary -->
                    <div v-if="form.amount > 0" class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-green-800 dark:text-green-200">Payment Amount:</span>
                                <span class="font-semibold text-green-600 dark:text-green-400">
                                    ${{ Number(form.amount).toFixed(2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-800 dark:text-green-200">New Remaining:</span>
                                <span class="font-semibold" :class="{
                                    'text-green-600 dark:text-green-400': newRemainingAmount === 0,
                                    'text-orange-600 dark:text-orange-400': newRemainingAmount > 0
                                }">
                                    ${{ newRemainingAmount.toFixed(2) }}
                                </span>
                            </div>
                            <div v-if="newRemainingAmount === 0" class="text-center pt-2 text-green-600 dark:text-green-400 font-semibold">
                                ✅ This will fully pay the debt!
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
                            :disabled="form.processing || !form.amount || form.amount <= 0 || form.amount > debt.remaining_amount"
                            class="cursor-pointer"
                        >
                            <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin mr-2" />
                            <Icon v-else name="DollarSign" class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Processing...' : 'Process Payment' }}
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'
import { toast } from 'vue-sonner'

interface CustomerDebt {
    id: number
    customer_id: number
    customer_name: string
    invoice_id: number
    original_amount: number
    paid_amount: number
    remaining_amount: number
    debt_date?: string
    due_date?: string
    status: 'pending' | 'partial' | 'paid' | 'overdue'
    days_overdue: number
    user?: string
    description?: string
    created_at: string
}

interface PaymentForm {
    amount: number
    payment_method: string
    notes: string
    [key: string]: any
}

interface PaymentErrors {
    amount?: string
    payment_method?: string
    notes?: string
}

const props = defineProps<{
    open: boolean
    debt: CustomerDebt | null
}>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'payment-processed': []
}>()

const form = useForm<PaymentForm>({
    amount: 0,
    payment_method: '',
    notes: '',
})

const errors = computed<PaymentErrors>(() => form.errors as PaymentErrors)

const newRemainingAmount = computed(() => {
    if (!props.debt || !form.amount) return props.debt?.remaining_amount || 0
    return Math.max(0, props.debt.remaining_amount - form.amount)
})

const setPartialPayment = () => {
    if (props.debt) {
        form.amount = Math.round((props.debt.remaining_amount * 0.5) * 100) / 100
    }
}

const setFullPayment = () => {
    if (props.debt) {
        form.amount = props.debt.remaining_amount
    }
}

const closeDialog = () => {
    emit('update:open', false)
    resetForm()
}

const resetForm = () => {
    form.reset()
    form.clearErrors()
}

const processPayment = async () => {
    if (!props.debt) return

    try {
        await form.post(`/customer-debts/${props.debt.id}/add-payment`, {
            onSuccess: () => {
                toast.success('Payment processed successfully')
                emit('payment-processed')
                closeDialog()
            },
            onError: () => {
                toast.error('Error processing payment. Please try again.')
            }
        })
    } catch {
        toast.error('An unexpected error occurred')
    }
}

// Reset form when dialog opens/closes or debt changes
watch([() => props.open, () => props.debt], ([isOpen, debt]) => {
    if (isOpen && debt) {
        resetForm()
        // Set default payment method if not set
        if (!form.payment_method) {
            form.payment_method = 'cash'
        }
    } else if (!isOpen) {
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

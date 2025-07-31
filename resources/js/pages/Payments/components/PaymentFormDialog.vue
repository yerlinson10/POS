<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon name="Wallet" class="w-5 h-5 text-primary" />
                    {{ isEdit ? 'Edit Payment' : 'New Payment' }}
                </DialogTitle>
                <DialogDescription>
                    {{ isEdit ? 'Update payment information' : 'Record a new income or expense payment' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="savePayment" class="space-y-4">
                <!-- Payment Type -->
                <div class="space-y-2">
                    <Label for="payment_type" class="text-sm font-medium flex items-center gap-1">
                        <Icon name="TrendingUp" class="w-3 h-3" />
                        Payment Type
                        <span class="text-destructive text-xs">*</span>
                    </Label>
                    <Select v-model="form.payment_type" required>
                        <SelectTrigger class="w-full h-10">
                            <SelectValue placeholder="Select payment type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="income">
                                <div class="flex items-center gap-2">
                                    <Icon name="ArrowDown" class="w-4 h-4 text-green-600" />
                                    <span>Income</span>
                                </div>
                            </SelectItem>
                            <SelectItem value="expense">
                                <div class="flex items-center gap-2">
                                    <Icon name="ArrowUp" class="w-4 h-4 text-red-600" />
                                    <span>Expense</span>
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError field="payment_type" :message="errors.payment_type" />
                </div>

                <!-- Amount -->
                <div class="space-y-2">
                    <Label for="amount" class="text-sm font-medium flex items-center gap-1">
                        <Icon name="DollarSign" class="w-3 h-3" />
                        Amount
                        <span class="text-destructive text-xs">*</span>
                    </Label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">$</span>
                        <Input 
                            id="amount" 
                            type="number" 
                            v-model.number="form.amount" 
                            step="0.01" 
                            min="0"
                            required 
                            placeholder="0.00" 
                            class="h-10 pl-8"
                            :class="{ 'border-destructive focus:border-destructive': errors.amount }" 
                        />
                    </div>
                    <InputError field="amount" :message="errors.amount" />
                </div>

                <!-- Payment Method -->
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

                <!-- Category -->
                <div class="space-y-2">
                    <Label for="category" class="text-sm font-medium flex items-center gap-1">
                        <Icon name="Tag" class="w-3 h-3" />
                        Category
                    </Label>
                    <Select v-model="form.category">
                        <SelectTrigger class="w-full h-10">
                            <SelectValue placeholder="Select category (optional)" />
                        </SelectTrigger>
                        <SelectContent>
                            <template v-if="form.payment_type === 'income'">
                                <SelectItem value="sales">Sales</SelectItem>
                                <SelectItem value="services">Services</SelectItem>
                                <SelectItem value="investments">Investments</SelectItem>
                                <SelectItem value="other_income">Other Income</SelectItem>
                            </template>
                            <template v-if="form.payment_type === 'expense'">
                                <SelectItem value="supplies">Supplies</SelectItem>
                                <SelectItem value="utilities">Utilities</SelectItem>
                                <SelectItem value="rent">Rent</SelectItem>
                                <SelectItem value="marketing">Marketing</SelectItem>
                                <SelectItem value="maintenance">Maintenance</SelectItem>
                                <SelectItem value="other_expense">Other Expense</SelectItem>
                            </template>
                        </SelectContent>
                    </Select>
                    <InputError field="category" :message="errors.category" />
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <Label for="description" class="text-sm font-medium">Description</Label>
                    <Textarea 
                        id="description" 
                        v-model="form.description"
                        placeholder="Add payment description or notes..."
                        class="min-h-[80px] resize-none"
                        :class="{ 'border-destructive focus:border-destructive': errors.description }" 
                    />
                    <InputError field="description" :message="errors.description" />
                </div>

                <!-- Payment Preview -->
                <div v-if="form.amount > 0 && form.payment_type" class="p-4 rounded-lg border" :class="{
                    'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800': form.payment_type === 'income',
                    'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800': form.payment_type === 'expense'
                }">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="font-medium" :class="{
                                'text-green-800 dark:text-green-200': form.payment_type === 'income',
                                'text-red-800 dark:text-red-200': form.payment_type === 'expense'
                            }">
                                {{ form.payment_type === 'income' ? 'Income' : 'Expense' }} Amount:
                            </span>
                            <span class="text-lg font-bold" :class="{
                                'text-green-600 dark:text-green-400': form.payment_type === 'income',
                                'text-red-600 dark:text-red-400': form.payment_type === 'expense'
                            }">
                                {{ form.payment_type === 'income' ? '+' : '-' }}${{ Number(form.amount).toFixed(2) }}
                            </span>
                        </div>
                        <div v-if="form.payment_method" class="flex justify-between">
                            <span class="text-muted-foreground">Method:</span>
                            <span class="capitalize">{{ form.payment_method.replace('_', ' ') }}</span>
                        </div>
                        <div v-if="form.category" class="flex justify-between">
                            <span class="text-muted-foreground">Category:</span>
                            <span class="capitalize">{{ form.category.replace('_', ' ') }}</span>
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
                        :disabled="form.processing || !form.amount || !form.payment_type || !form.payment_method"
                        class="cursor-pointer"
                    >
                        <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin mr-2" />
                        <Icon v-else name="Save" class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Update Payment' : 'Save Payment') }}
                    </Button>
                </DialogFooter>
            </form>
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

interface Payment {
    id: number
    payment_type: 'income' | 'expense'
    amount: number
    payment_method: string
    category?: string
    description?: string
}

interface PaymentForm {
    payment_type: 'income' | 'expense' | ''
    amount: number
    payment_method: string
    category: string
    description: string
}

const props = defineProps<{
    open: boolean
    payment: Payment | null
}>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'payment-saved': []
}>()

const isEdit = computed(() => Boolean(props.payment))

const form = useForm<PaymentForm>({
    payment_type: '',
    amount: 0,
    payment_method: '',
    category: '',
    description: '',
})

const errors = computed(() => form.errors)

const closeDialog = () => {
    emit('update:open', false)
    resetForm()
}

const resetForm = () => {
    form.reset()
    form.clearErrors()
}

const savePayment = async () => {
    try {
        if (isEdit.value && props.payment) {
            await form.put(`/payments/${props.payment.id}`, {
                onSuccess: () => {
                    toast.success('Payment updated successfully')
                    emit('payment-saved')
                    closeDialog()
                },
                onError: () => {
                    toast.error('Error updating payment. Please try again.')
                }
            })
        } else {
            await form.post('/payments', {
                onSuccess: () => {
                    toast.success('Payment created successfully')
                    emit('payment-saved')
                    closeDialog()
                },
                onError: () => {
                    toast.error('Error creating payment. Please try again.')
                }
            })
        }
    } catch {
        toast.error('An unexpected error occurred')
    }
}

// Reset form when dialog opens/closes or payment changes
watch([() => props.open, () => props.payment], ([isOpen, payment]) => {
    if (isOpen) {
        if (payment) {
            // Edit mode - populate form with payment data
            form.payment_type = payment.payment_type
            form.amount = payment.amount
            form.payment_method = payment.payment_method
            form.category = payment.category || ''
            form.description = payment.description || ''
        } else {
            // Create mode - reset form
            resetForm()
        }
    } else {
        resetForm()
    }
}, { immediate: false })

// Clear category when payment type changes
watch(() => form.payment_type, () => {
    form.category = ''
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

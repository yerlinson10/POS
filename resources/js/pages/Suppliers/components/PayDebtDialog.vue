<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon name="DollarSign" class="w-5 h-5 text-green-600" />
                    Pay Supplier Debt
                </DialogTitle>
                <DialogDescription>
                    Process payment for <strong>{{ supplier?.company_name }}</strong>
                </DialogDescription>
            </DialogHeader>

            <div v-if="supplier" class="space-y-4">
                <!-- Current Debt Display -->
                <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-red-800 dark:text-red-200">Current Debt:</span>
                        <span class="text-lg font-bold text-red-600 dark:text-red-400">
                            ${{ Number(supplier?.total_debt || 0).toFixed(2) }}
                        </span>
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
                                :max="supplier?.total_debt || 0"
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
                        <Label for="description" class="text-sm font-medium">Description</Label>
                        <Textarea 
                            id="description" 
                            v-model="form.description"
                            placeholder="Add payment details or reference..."
                            class="min-h-[80px] resize-none"
                            :class="{ 'border-destructive focus:border-destructive': errors.description }" 
                        />
                        <InputError field="description" :message="errors.description" />
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
                                <span class="text-green-800 dark:text-green-200">Remaining Debt:</span>
                                <span class="font-semibold" :class="{
                                    'text-green-600 dark:text-green-400': remainingDebt === 0,
                                    'text-orange-600 dark:text-orange-400': remainingDebt > 0
                                }">
                                    ${{ remainingDebt.toFixed(2) }}
                                </span>
                            </div>
                            <div v-if="remainingDebt === 0" class="text-center pt-2 text-green-600 dark:text-green-400 font-semibold">
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
                            :disabled="form.processing || !form.amount || form.amount <= 0 || !supplier?.total_debt || form.amount > supplier.total_debt"
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

interface Supplier {
    id: number
    company_name: string
    total_debt: number
}

interface PaymentForm {
    amount: number
    payment_method: string
    description: string
}

const props = defineProps<{
    open: boolean
    supplier: Supplier | null
}>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'debt-paid': []
}>()

const form = useForm<PaymentForm>({
    amount: 0,
    payment_method: '',
    description: '',
})

const errors = computed(() => form.errors)

const remainingDebt = computed(() => {
    if (!props.supplier?.total_debt || !form.amount) return props.supplier?.total_debt || 0
    return Math.max(0, props.supplier.total_debt - form.amount)
})

const setPartialPayment = () => {
    if (props.supplier?.total_debt) {
        form.amount = Math.round((props.supplier.total_debt * 0.5) * 100) / 100
    }
}

const setFullPayment = () => {
    if (props.supplier?.total_debt) {
        form.amount = props.supplier.total_debt
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
    if (!props.supplier?.id) {
        toast.error('Invalid supplier data')
        return
    }

    try {
        await form.post(`/suppliers/${props.supplier.id}/pay-debt`, {
            onSuccess: () => {
                toast.success('Payment processed successfully')
                emit('debt-paid')
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

// Reset form when dialog opens/closes or supplier changes
watch([() => props.open, () => props.supplier], ([isOpen, supplier]) => {
    if (isOpen && supplier) {
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

<template>
    <div class="space-y-4">
        <div class="p-4 bg-muted/50 rounded-lg border">
            <h4 class="font-medium text-sm mb-3 flex items-center gap-2">
                <Icon name="Calculator" class="w-4 h-4" />
                Cash Payment Calculator
            </h4>

            <div class="space-y-3">
                <!-- Total Amount Due -->
                <div class="flex justify-between items-center p-2 bg-background rounded border">
                    <span class="text-sm font-medium">Amount Due:</span>
                    <span class="text-lg font-bold text-primary">RD${{ formatCurrency(totalAmount) }}</span>
                </div>

                <!-- Cash Received Input -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Cash Received:</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">RD$</span>
                        <Input ref="cashInput" v-model="cashReceived" type="number" step="0.01" min="0"
                            class="pl-12 text-lg font-medium" :class="{
                                'border-red-500 focus:border-red-500': toCents(cashReceived) > 0 && toCents(cashReceived) < toCents(totalAmount),
                                'border-green-500 focus:border-green-500': toCents(cashReceived) > 0 && toCents(cashReceived) >= toCents(totalAmount)
                            }" placeholder="0.00" @keyup.enter="$emit('confirm')" @blur="onCashInputBlur" />
                    </div>
                </div>

                <!-- Quick Amount Buttons -->
                <div class="grid grid-cols-4 gap-2">
                    <Button v-for="amount in quickAmounts" :key="amount" @click="setCashReceived(amount)"
                        variant="outline" size="sm" class="text-xs cursor-pointer">
                        {{ Number(amount).toFixed(2) }}
                    </Button>
                </div>

                <!-- Exact Amount Button -->
                <div class="flex justify-center">
                    <Button @click="resetToTotalAmount" variant="secondary" size="sm" class="text-xs font-medium cursor-pointer"
                        title="Set exact amount (no change required)">
                        <Icon name="Target" class="w-3 h-3 mr-1" />
                        Exact Amount ({{ Number(totalAmount).toFixed(2) }})
                    </Button>
                </div>

                <!-- Change Calculation -->
                <div v-if="changeAmount !== null" class="space-y-2">
                    <div class="flex justify-between items-center p-3 rounded border" :class="{
                        'bg-green-50 border-green-200 dark:bg-green-900/20': changeAmount >= 0,
                        'bg-red-50 border-red-200 dark:bg-red-900/20': changeAmount < 0
                    }">
                        <span class="font-medium text-sm">
                            {{ changeAmount >= 0 ? 'Change to Return:' : 'Amount Still Due:' }}
                        </span>
                        <span class="text-lg font-bold" :class="{
                            'text-green-600 dark:text-green-400': changeAmount >= 0,
                            'text-red-600 dark:text-red-400': changeAmount < 0
                        }">
                            {{ changeAmount >= 0 ? 'RD$' : '-RD$' }}{{ formatCurrency(Math.abs(changeAmount)) }}
                        </span>
                    </div>

                    <!-- Change breakdown for large amounts -->
                    <div v-if="changeAmount > 0" class="text-xs text-muted-foreground">
                        <div class="grid grid-cols-2 gap-2">
                            <div v-for="(count, denomination) in changeBreakdown" :key="denomination">
                                {{ count }}x RD${{ Number(denomination).toFixed(2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                <div v-if="statusMessage" class="text-sm p-2 rounded" :class="{
                    'text-green-600 bg-green-50 dark:bg-green-900/20': statusType === 'success',
                    'text-red-600 bg-red-50 dark:bg-red-900/20': statusType === 'error',
                }">
                    {{ statusMessage }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'
import { formatCurrency as baseFormatCurrency } from '@/utils/format'

function formatCurrency(value: number | string): string {
    return baseFormatCurrency(Number(Number(value).toFixed(2)))
}

// Función para convertir a centavos (evita problemas de decimales)
function toCents(value: number | string): number {
    return Math.round(Number(value) * 100)
}

// Función para convertir de centavos a pesos
function fromCents(cents: number): number {
    return cents / 100
}

interface Props {
    totalAmount: number
    canConfirm?: boolean
}

interface Emits {
    (e: 'confirm'): void
    (e: 'cash-received-change', amount: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const cashInput = ref<HTMLInputElement | null>(null)
const cashReceived = ref('')

// Quick amount buttons (rounded up amounts)
const quickAmounts = computed(() => {
    const total = props.totalAmount
    const amounts = [
        Math.ceil(total),
        Math.ceil(total / 100) * 100,
        Math.ceil(total / 500) * 500,
        Math.ceil(total / 1000) * 1000
    ]
    // Redondear a dos decimales y devolver como string
    return [...new Set(amounts)].sort((a, b) => a - b).slice(0, 4).map(a => Number(a).toFixed(2))
})

const changeAmount = computed(() => {
    const receivedCents = toCents(cashReceived.value)
    const totalCents = toCents(props.totalAmount)
    const diffCents = receivedCents - totalCents

    return receivedCents > 0 ? fromCents(diffCents) : null
})

// Change breakdown in common Dominican peso denominations
const changeBreakdown = computed(() => {
    if (!changeAmount.value || changeAmount.value <= 0) return {}

    const denominations = [2000, 1000, 500, 200, 100, 50, 25, 10, 5, 1]
    let remaining = Math.round(changeAmount.value)
    const breakdown: Record<number, number> = {}

    for (const denom of denominations) {
        const count = Math.floor(remaining / denom)
        if (count > 0) {
            breakdown[denom] = count
            remaining -= count * denom
        }
    }

    return breakdown
})

const statusMessage = computed(() => {
    const receivedCents = toCents(cashReceived.value)
    const totalCents = toCents(props.totalAmount)
    const diffCents = receivedCents - totalCents

    if (receivedCents === 0) return ''
    if (diffCents < 0) return 'Insufficient amount - please add more cash'
    if (diffCents === 0) return 'Exact amount - no change required'
    return 'Payment accepted - change calculated above'
})

const statusType = computed(() => {
    const receivedCents = toCents(cashReceived.value)
    const totalCents = toCents(props.totalAmount)
    const diffCents = receivedCents - totalCents

    if (receivedCents === 0) return 'info'
    if (diffCents < 0) return 'error'
    return 'success'
})

const setCashReceived = (amount: number | string) => {
    cashReceived.value = Number(amount).toFixed(2)
    focusInput()
}

const resetToTotalAmount = () => {
    cashReceived.value = Number(props.totalAmount).toFixed(2)
    focusInput()
}

const focusInput = () => {
    nextTick(() => {
        if (cashInput.value) {
            cashInput.value.focus()
            cashInput.value.select()
        }
    })
}

// Emit changes to parent
watch(cashReceived, (newValue) => {
    const amount = Number(newValue) || 0
    emit('cash-received-change', amount)
})

// Update cash received when total amount changes
watch(() => props.totalAmount, (newTotal) => {
    cashReceived.value = newTotal.toString()
})

// Auto-focus on mount y set default value
onMounted(() => {
    // Set default value to total amount
    cashReceived.value = Number(props.totalAmount).toFixed(2)
    focusInput()
})

// Expose focus method
defineExpose({
    focus: focusInput,
    resetToTotalAmount
})

const onCashInputBlur = () => {
    cashReceived.value = Number(cashReceived.value).toFixed(2)
}
</script>

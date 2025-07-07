<template>
    <div class="space-y-6">
        <!-- Bills -->
        <div>
            <h3 class="text-lg font-semibold text-foreground mb-4">Bills</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div v-for="bill in billDenominations" :key="bill" class="space-y-2">
                    <Label class="text-sm font-medium">
                        RD${{ formatDenomination(bill) }}
                    </Label>
                    <Input
                        v-model.number="billQuantities[bill]"
                        type="number"
                        min="0"
                        placeholder="0"
                        @input="updateBreakdown"
                        class="text-center"
                    />
                    <p class="text-xs text-muted-foreground text-center">
                        RD${{ formatCurrency(bill * (billQuantities[bill] || 0)) }}
                    </p>
                </div>
            </div>
            <div class="mt-4 p-3 bg-muted/50 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-foreground">Bills Total:</span>
                    <span class="text-sm font-semibold text-primary">RD${{ formatCurrency(billsTotal) }}</span>
                </div>
            </div>
        </div>

        <!-- Coins -->
        <div>
            <h3 class="text-lg font-semibold text-foreground mb-4">Coins</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="coin in coinDenominations" :key="coin" class="space-y-2">
                    <Label class="text-sm font-medium">
                        RD${{ formatDenomination(coin) }}
                    </Label>
                    <Input
                        v-model.number="coinQuantities[coin]"
                        type="number"
                        min="0"
                        placeholder="0"
                        @input="updateBreakdown"
                        class="text-center"
                    />
                    <p class="text-xs text-muted-foreground text-center">
                        RD${{ formatCurrency(coin * (coinQuantities[coin] || 0)) }}
                    </p>
                </div>
            </div>
            <div class="mt-4 p-3 bg-muted/50 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-foreground">Coins Total:</span>
                    <span class="text-sm font-semibold text-primary">RD${{ formatCurrency(coinsTotal) }}</span>
                </div>
            </div>
        </div>

        <!-- Grand Total -->
        <div class="border-t pt-4">
            <div class="p-4 bg-primary/10 rounded-lg border border-primary/20">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-foreground">Grand Total:</span>
                    <span class="text-xl font-bold text-primary">
                        RD${{ formatCurrency(totalAmount) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Input } from '../../../components/ui/input'
import { Label } from '../../../components/ui/label'
import type { CashBreakdown } from '../../../types/pos'

interface Props {
    modelValue?: CashBreakdown
}

interface Emits {
    (e: 'update:modelValue', value: CashBreakdown): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Dominican Republic currency denominations
const billDenominations = [2000, 1000, 500, 200, 100, 50]
const coinDenominations = [25, 10, 5, 1]

// Estado local para cantidades
const billQuantities = ref<Record<number, number>>({})
const coinQuantities = ref<Record<number, number>>({})

// Inicializar cantidades desde modelValue
if (props.modelValue) {
    props.modelValue.bills?.forEach(bill => {
        billQuantities.value[bill.denomination] = bill.quantity
    })
    props.modelValue.coins?.forEach(coin => {
        coinQuantities.value[coin.denomination] = coin.quantity
    })
}

// Computed totals
const billsTotal = computed(() => {
    return Object.entries(billQuantities.value).reduce((total, [denomination, quantity]) => {
        return total + (parseFloat(denomination) * (quantity || 0))
    }, 0)
})

const coinsTotal = computed(() => {
    return Object.entries(coinQuantities.value).reduce((total, [denomination, quantity]) => {
        return total + (parseFloat(denomination) * (quantity || 0))
    }, 0)
})

const totalAmount = computed(() => {
    return billsTotal.value + coinsTotal.value
})

// Methods
const updateBreakdown = () => {
    const bills = Object.entries(billQuantities.value)
        .filter(([_, quantity]) => quantity > 0)
        .map(([denomination, quantity]) => ({
            denomination: parseFloat(denomination),
            quantity: quantity
        }))

    const coins = Object.entries(coinQuantities.value)
        .filter(([_, quantity]) => quantity > 0)
        .map(([denomination, quantity]) => ({
            denomination: parseFloat(denomination),
            quantity: quantity
        }))

    emit('update:modelValue', { bills, coins })
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-DO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const formatDenomination = (amount: number) => {
    return new Intl.NumberFormat('es-DO').format(amount)
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        // Reset quantities
        billQuantities.value = {}
        coinQuantities.value = {}

        // Set new values
        newValue.bills?.forEach(bill => {
            billQuantities.value[bill.denomination] = bill.quantity
        })
        newValue.coins?.forEach(coin => {
            coinQuantities.value[coin.denomination] = coin.quantity
        })
    }
}, { deep: true })
</script>

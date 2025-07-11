<template>
    <AppLayout title="Close POS Session">
        <div class="max-w-4xl mx-auto p-6">
            <div
                class="rounded-xl border bg-card text-card-foreground shadow-sm dark:bg-[#18181b] dark:border-[#27272a]">
                <div class="p-6 border-b border-border dark:border-[#27272a]">
                    <h1 class="text-2xl font-bold text-foreground dark:text-white">Close POS Session</h1>
                    <p class="text-muted-foreground mt-2 dark:text-gray-400">
                        Review the sales summary and confirm the final cash to close the session.
                    </p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Session summary -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-950 p-4">
                            <h3 class="font-semibold text-blue-900 dark:text-blue-200">Session Information</h3>
                            <div class="mt-2 space-y-1 text-sm text-blue-700 dark:text-blue-300">
                                <p><strong>Cashier:</strong> {{ session.user_name }}</p>
                                <p><strong>Started:</strong> {{ formatDateTime(session.opened_at) }}</p>
                                <p><strong>Duration:</strong> {{ summary.duration }}</p>
                            </div>
                        </div>

                        <div class="rounded-lg bg-green-50 dark:bg-green-950 p-4">
                            <h3 class="font-semibold text-green-900 dark:text-green-200">Sales Summary</h3>
                            <div class="mt-2 space-y-1 text-sm text-green-700 dark:text-green-300">
                                <p><strong>Total sales:</strong> ${{ formatCurrency(summary.total_sales) }}</p>
                                <p><strong>Number of sales:</strong> {{ summary.sales_count }}</p>
                                <p><strong>Cash sales:</strong> ${{ formatCurrency(summary.cash_sales) }}</p>
                            </div>
                        </div>

                        <div class="rounded-lg bg-orange-50 dark:bg-orange-950 p-4">
                            <h3 class="font-semibold text-orange-900 dark:text-orange-200">Cash</h3>
                            <div class="mt-2 space-y-1 text-sm text-orange-700 dark:text-orange-300">
                                <p><strong>Initial cash:</strong> ${{ formatCurrency(session.initial_cash) }}</p>
                                <p><strong>Cash sales:</strong> +${{ formatCurrency(summary.cash_sales) }}</p>
                                <p><strong>Expected cash:</strong> ${{ formatCurrency(summary.expected_cash) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Closing form -->
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Final cash -->
                        <div>
                            <label for="final_cash"
                                class="block text-sm font-medium text-foreground dark:text-gray-200 mb-2">
                                Final Counted Cash *
                            </label>
                            <div class="relative">
                                <span
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">$</span>
                                <Input id="final_cash" v-model="form.final_cash" type="number" step="0.01" min="0"
                                    placeholder="0.00" class="pl-8" :class="{ 'border-destructive': errors.final_cash }"
                                    required />
                            </div>
                            <p v-if="errors.final_cash" class="text-destructive text-sm mt-1">
                                {{ errors.final_cash }}
                            </p>

                            <!-- Cash difference -->
                            <div v-if="cashDifference !== 0" class="mt-2 p-3 rounded-lg" :class="cashDifference > 0
                                ? 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300'
                                : 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300'">
                                <div class="flex items-center">
                                    <AlertTriangle v-if="cashDifference < 0" class="h-4 w-4 mr-2" />
                                    <CheckCircle v-else class="h-4 w-4 mr-2" />
                                    <span class="font-medium">
                                        {{ cashDifference > 0 ? 'Surplus' : 'Shortage' }}:
                                        ${{ formatCurrency(Math.abs(cashDifference)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Closing notes -->
                        <div>
                            <label for="closing_notes"
                                class="block text-sm font-medium text-foreground dark:text-gray-200 mb-2">
                                Closing Notes
                            </label>
                            <Textarea id="closing_notes" v-model="form.closing_notes"
                                placeholder="Additional notes about the session closing..." rows="3"
                                :class="{ 'border-destructive': errors.closing_notes }" />
                            <p v-if="errors.closing_notes" class="text-destructive text-sm mt-1">
                                {{ errors.closing_notes }}
                            </p>
                        </div>

                        <!-- Final cash breakdown (optional) -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-sm font-medium text-foreground dark:text-gray-200">
                                    Final Cash Breakdown (Optional)
                                </label>
                                <Button type="button" @click="showBreakdown = !showBreakdown" variant="outline"
                                    size="sm">
                                    {{ showBreakdown ? 'Hide' : 'Show' }} Breakdown
                                </Button>
                            </div>

                            <div v-if="showBreakdown" class="border rounded-lg p-4 bg-muted/50">
                                <CashBreakdown v-model="form.cash_breakdown" />
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div
                            class="border rounded-lg p-4 bg-yellow-50 dark:bg-yellow-950 border-yellow-200 dark:border-yellow-900">
                            <div class="flex items-start">
                                <AlertTriangle
                                    class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" />
                                <div>
                                    <h3 class="font-medium text-yellow-800 dark:text-yellow-200">Confirm Session Closing
                                    </h3>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                        Once the session is closed, you will not be able to process more sales until a
                                        new session is opened.
                                        Make sure all information is correct.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-border dark:border-[#27272a]">
                            <Button class="cursor-pointer" type="button" @click="goBack" variant="outline"
                                :disabled="isSubmitting">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="isSubmitting"
                                class="min-w-[120px] bg-destructive hover:bg-destructive/90 text-white cursor-pointer">
                                <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin mr-2" />
                                {{ isSubmitting ? 'Closing...' : 'Close Session' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '../../layouts/AppLayout.vue'
import { Button } from '../../components/ui/button'
import { Input } from '../../components/ui/input'
import { Textarea } from '../../components/ui/textarea'
import CashBreakdown from '@/pages/PosSession/components/CashBreakdown.vue'
import { Loader2, AlertTriangle, CheckCircle } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import type { PosSession, PosSessionSummary, CloseSessionRequest } from '../../types/pos'

interface Props {
    session: PosSession
    summary: PosSessionSummary
}

const props = defineProps<Props>()

// State
const isSubmitting = ref(false)
const showBreakdown = ref(false)
const errors = ref<Record<string, string>>({})

const form = reactive<CloseSessionRequest>({
    final_cash: props.summary.expected_cash,
    closing_notes: '',
    cash_breakdown: {
        bills: [],
        coins: []
    }
})

// Computed
const cashDifference = computed(() => {
    if (!form.final_cash) return 0
    return form.final_cash - props.summary.expected_cash
})

// Methods
const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const submitForm = async () => {
    if (isSubmitting.value) return

    isSubmitting.value = true
    errors.value = {}

    try {
        const formData = {
            ...form,
            cash_breakdown: showBreakdown.value ? form.cash_breakdown : undefined
        }

        await router.patch(route('sessions.update', props.session.id), formData, {
            onSuccess: () => {
                toast.success('POS session closed successfully')
                // Permitir que Inertia maneje la navegación automáticamente
            },
            onError: (responseErrors) => {
                errors.value = responseErrors
                toast.error('Error closing POS session')
            }
        })
    } catch (error) {
        console.error('Error submitting form:', error)
        toast.error('Unexpected error closing session')
    } finally {
        isSubmitting.value = false
    }
}

const goBack = () => {
    router.visit(route('pos.index'))
}
</script>

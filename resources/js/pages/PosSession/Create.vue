<template>
    <AppLayout title="Open POS Session">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">
                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-xl md:text-2xl font-semibold">Open New POS Session</h2>
                            <p class="text-sm text-muted-foreground">
                                Start a new point of sale session to begin processing sales
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 md:p-6">
                    <div class="max-w-2xl mx-auto">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Initial Cash Amount -->
                            <div class="space-y-2">
                                <Label for="initial_cash" class="text-sm font-medium block mb-1">
                                    Initial Cash Amount *
                                </Label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">RD$</span>
                                    <Input
                                        id="initial_cash"
                                        v-model="form.initial_cash"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="pl-12"
                                        :class="{ 'border-destructive': errors.initial_cash }"
                                        required
                                    />
                                </div>
                                <p v-if="errors.initial_cash" class="text-destructive text-sm">
                                    {{ errors.initial_cash }}
                                </p>
                            </div>

                            <!-- Opening Notes -->
                            <div class="space-y-2">
                                <Label for="opening_notes" class="text-sm font-medium">
                                    Opening Notes
                                </Label>
                                <Textarea
                                    id="opening_notes"
                                    v-model="form.opening_notes"
                                    placeholder="Additional notes about session opening..."
                                    rows="3"
                                    :class="{ 'border-destructive': errors.opening_notes }"
                                />
                                <p v-if="errors.opening_notes" class="text-destructive text-sm">
                                    {{ errors.opening_notes }}
                                </p>
                            </div>

                            <!-- Cash Breakdown (Optional) -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <Label class="text-sm font-medium">
                                        Cash Breakdown (Optional)
                                    </Label>
                                    <Button
                                        type="button"
                                        @click="showBreakdown = !showBreakdown"
                                        variant="outline"
                                        size="sm"
                                    >
                                        {{ showBreakdown ? 'Hide' : 'Show' }} Breakdown
                                    </Button>
                                </div>

                                <div v-if="showBreakdown" class="border rounded-lg p-4 bg-muted/50">
                                    <CashBreakdown v-model="form.cash_breakdown" />
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end space-x-3 pt-6 border-t">
                                <Button
                                    class="cursor-pointer"
                                    type="button"
                                    @click="goBack"
                                    variant="outline"
                                    :disabled="isSubmitting"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="min-w-[120px] cursor-pointer"
                                >
                                    <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin mr-2" />
                                    {{ isSubmitting ? 'Opening...' : 'Open Session' }}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '../../layouts/AppLayout.vue'
import { Button } from '../../components/ui/button'
import { Input } from '../../components/ui/input'
import { Label } from '../../components/ui/label'
import { Textarea } from '../../components/ui/textarea'
import CashBreakdown from '@/pages/PosSession/components/CashBreakdown.vue'
import { Loader2 } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import type { OpenSessionRequest, CashBreakdown as CashBreakdownType } from '../../types/pos'

// State
const isSubmitting = ref(false)
const showBreakdown = ref(false)
const errors = ref<Record<string, string>>({})

const form = reactive<OpenSessionRequest>({
    initial_cash: 0,
    opening_notes: '',
    cash_breakdown: {
        bills: [],
        coins: []
    }
})

// Methods
const submitForm = async () => {
    if (isSubmitting.value) return

    isSubmitting.value = true
    errors.value = {}

    try {
        const formData = {
            ...form,
            cash_breakdown: showBreakdown.value ? form.cash_breakdown : undefined
        }

        await router.post(route('sessions.store'), formData, {
            onSuccess: () => {
                toast.success('POS session opened successfully')
            },
            onError: (responseErrors) => {
                errors.value = responseErrors
                toast.error('Error opening POS session')
            }
        })
    } catch (error) {
        console.error('Error submitting form:', error)
        toast.error('Unexpected error opening session')
    } finally {
        isSubmitting.value = false
    }
}

const goBack = () => {
    router.visit(route('pos.index'))
}
</script>

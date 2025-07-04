<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="w-[95vw] max-w-md sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg md:text-xl">
                    Apply {{ type === 'percentage' ? 'Percentage' : 'Fixed Amount' }} Discount
                </DialogTitle>
                <DialogDescription class="text-sm">
                    {{ type === 'percentage'
                        ? 'Enter the discount percentage (0-100) and press Enter to apply'
                        : 'Enter the fixed discount amount and press Enter to apply'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-3 md:space-y-4">
                <div class="space-y-2">
                    <Label for="discount_value" class="text-sm">
                        {{ type === 'percentage' ? 'Discount Percentage (%)' : 'Discount Amount ($)' }}
                    </Label>
                    <div class="relative">
                        <Input id="discount_value" v-model.number="discountValue" type="number" :min="0"
                            :max="type === 'percentage' ? 100 : undefined" step="any"
                            :placeholder="type === 'percentage' ? 'Enter percentage (0-100)' : 'Enter amount'"
                            class="discount-dialog pr-8 h-9 md:h-10 text-sm" required @input="calculatePreview" />
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">
                            {{ type === 'percentage' ? '%' : '$' }}
                        </div>
                    </div>
                    <div v-if="error" class="text-xs text-red-500">
                        {{ error }}
                    </div>
                </div>

                <!-- Preview -->
                <div v-if="previewAmount > 0" class="p-3 bg-accent/50 rounded-lg space-y-1">
                    <div class="text-sm font-medium">Discount Preview</div>
                    <div class="text-xs md:text-sm text-muted-foreground">
                        Subtotal: ${{ Number(subtotal).toFixed(2) }}
                    </div>
                    <div class="text-xs md:text-sm text-green-600">
                        Discount: -${{ Number(previewAmount).toFixed(2) }}
                    </div>
                    <div class="text-xs md:text-sm font-medium border-t pt-1">
                        New Total: ${{ Math.max(0, Number(subtotal) - Number(previewAmount)).toFixed(2) }}
                    </div>
                </div>

                <DialogFooter class="flex flex-col sm:flex-row gap-2 sm:gap-0">
                    <Button type="button" @click="closeDialog" variant="outline"
                        class="w-full sm:w-auto h-9 md:h-10 text-sm mr-2">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="!canApply" class="w-full sm:w-auto h-9 md:h-10 text-sm">
                        <Icon name="Percent" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                        Apply Discount (Enter)
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { storeToRefs } from 'pinia'
import { usePOSStore } from '../../../stores/pos'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '../../../components/ui/dialog'
import { Input } from '../../../components/ui/input'
import { Button } from '../../../components/ui/button'
import { Label } from '../../../components/ui/label'
import Icon from '../../../components/Icon.vue'

interface Props {
    open: boolean
    type: 'percentage' | 'fixed'
}

interface Emits {
    (e: 'update:open', value: boolean): void
    (e: 'update:type', value: 'percentage' | 'fixed'): void
    (e: 'discount-applied', type: 'percentage' | 'fixed', value: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Store
const posStore = usePOSStore()
const { subtotal } = storeToRefs(posStore)

// Local state
const isOpen = ref(props.open)
const discountValue = ref<number>(0)
const previewAmount = ref<number>(0)
const error = ref<string>('')

// Computed
const canApply = computed(() => {
    return discountValue.value > 0 && !error.value && subtotal.value > 0
})

// Methods
const calculatePreview = () => {
    error.value = ''

    if (discountValue.value <= 0) {
        previewAmount.value = 0
        return
    }

    if (props.type === 'percentage') {
        if (discountValue.value > 100) {
            error.value = 'Percentage cannot exceed 100%'
            previewAmount.value = 0
            return
        }
        previewAmount.value = (subtotal.value * discountValue.value) / 100
    } else {
        if (discountValue.value > subtotal.value) {
            error.value = 'Discount cannot exceed subtotal'
            previewAmount.value = 0
            return
        }
        previewAmount.value = discountValue.value
    }
}

const handleSubmit = () => {
    if (!canApply.value) return

    emit('discount-applied', props.type, discountValue.value)
    closeDialog()
}

const resetForm = () => {
    discountValue.value = 0
    previewAmount.value = 0
    error.value = ''
}

const closeDialog = () => {
    isOpen.value = false
    emit('update:open', false)
    resetForm()
}

// Watch for prop changes
watch(() => props.open, (newValue) => {
    isOpen.value = newValue
    if (newValue) {
        resetForm()
        // Auto-focus input when dialog opens
        nextTick(() => {
            const input = document.querySelector('#discount_value') as HTMLInputElement
            if (input) {
                input.focus()
                input.select()
            }
        })
    }
})

watch(isOpen, (newValue) => {
    if (!newValue) {
        emit('update:open', false)
    }
})

watch(() => props.type, () => {
    calculatePreview()
})

watch(discountValue, () => {
    calculatePreview()
})

watch(subtotal, () => {
    calculatePreview()
})
</script>

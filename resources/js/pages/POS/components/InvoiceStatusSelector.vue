<template>
    <div class="space-y-3">
        <label class="text-sm font-medium">Invoice Status (F8 to toggle)</label>

        <!-- Select dropdown -->
        <Select v-model="selectedStatus">
            <SelectTrigger class="w-full">
                <SelectValue>
                    <div class="flex items-center gap-2">
                        <Icon :name="statusIcon" :class="statusIconClass" class="w-4 h-4" />
                        <span>{{ getStatusLabel(selectedStatus) }}</span>
                    </div>
                </SelectValue>
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="paid">
                    <div class="flex items-center gap-2">
                        <Icon name="CheckCircle" class="w-4 h-4 text-green-600" />
                        <span>Paid</span>
                        <span class="text-xs text-muted-foreground ml-2">(Invoice fully paid)</span>
                    </div>
                </SelectItem>
                <SelectItem value="pending">
                    <div class="flex items-center gap-2">
                        <Icon name="Clock" class="w-4 h-4 text-yellow-600" />
                        <span>Pending Payment</span>
                        <span class="text-xs text-muted-foreground ml-2">(Payment pending)</span>
                    </div>
                </SelectItem>
            </SelectContent>
        </Select>

        <!-- Status Description -->
        <div class="p-3 bg-muted rounded-lg">
            <div class="flex items-start gap-2">
                <Icon :name="statusIcon" :class="statusIconClass" class="w-4 h-4 mt-0.5" />
                <div class="text-sm">
                    <p class="font-medium">{{ statusTitle }}</p>
                    <p class="text-muted-foreground text-xs mt-1">{{ statusDescription }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import Icon from '@/components/Icon.vue'
import type { InvoiceStatus } from '@/types/pos'

interface Props {
    modelValue: InvoiceStatus
}

interface Emits {
    (e: 'update:modelValue', value: InvoiceStatus): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const selectedStatus = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const getStatusLabel = (status: InvoiceStatus) => {
    switch (status) {
        case 'paid':
            return 'Paid'
        case 'pending':
            return 'Pending Payment'
        default:
            return 'Paid'
    }
}

const statusIcon = computed(() => {
    switch (selectedStatus.value) {
        case 'paid':
            return 'CheckCircle'
        case 'pending':
            return 'Clock'
        default:
            return 'CheckCircle'
    }
})

const statusIconClass = computed(() => {
    switch (selectedStatus.value) {
        case 'paid':
            return 'text-green-600'
        case 'pending':
            return 'text-yellow-600'
        default:
            return 'text-green-600'
    }
})

const statusTitle = computed(() => {
    switch (selectedStatus.value) {
        case 'paid':
            return 'Invoice Paid'
        case 'pending':
            return 'Pending Payment'
        default:
            return 'Invoice Paid'
    }
})

const statusDescription = computed(() => {
    switch (selectedStatus.value) {
        case 'paid':
            return 'The sale will be registered as fully paid. Stock will be updated immediately.'
        case 'pending':
            return 'The sale will be registered but will remain pending payment. Stock will be reserved but not updated until payment.'
        default:
            return 'The sale will be registered as fully paid.'
    }
})
</script>

<template>
    <div class="space-y-3">
        <label class="text-sm font-medium">Payment Method (F10/F11/F12, Ctrl+1-4, Alt+P)</label>
        <Select v-model="selectedMethod">
            <SelectTrigger class="w-full">
                <SelectValue>
                    <div class="flex items-center gap-2">
                        <Icon :name="methodIcon" :class="methodIconClass" class="w-4 h-4" />
                        <span>{{ getMethodLabel(selectedMethod) }}</span>
                    </div>
                </SelectValue>
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-for="method in methods" :key="method.value" :value="method.value">
                    <div class="flex items-center gap-2">
                        <Icon :name="method.icon" :class="method.class" class="w-4 h-4" />
                        <span>{{ method.label }}</span>
                    </div>
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import Icon from '@/components/Icon.vue'

interface Props {
    modelValue: 'cash' | 'card' | 'transfer' | 'other'
}
interface Emits {
    (e: 'update:modelValue', value: 'cash' | 'card' | 'transfer' | 'other'): void
}
const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const methods = [
    { value: 'cash', label: 'Cash', icon: 'Banknote', class: 'text-green-600' },
    { value: 'card', label: 'Card', icon: 'CreditCard', class: 'text-purple-600' },
    { value: 'transfer', label: 'Transfer', icon: 'ArrowRightLeft', class: 'text-blue-600' },
    { value: 'other', label: 'Other', icon: 'MoreHorizontal', class: 'text-gray-500' },
]

const selectedMethod = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const getMethodLabel = (method: string) => {
    const found = methods.find(m => m.value === method)
    return found ? found.label : method
}

const methodIcon = computed(() => {
    const found = methods.find(m => m.value === selectedMethod.value)
    return found ? found.icon : 'CreditCard'
})
const methodIconClass = computed(() => {
    const found = methods.find(m => m.value === selectedMethod.value)
    return found ? found.class : 'text-purple-600'
})
</script>

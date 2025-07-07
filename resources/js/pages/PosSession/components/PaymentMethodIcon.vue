<template>
    <div class="inline-flex items-center justify-center w-6 h-6 rounded-full">
        <Banknote v-if="method === 'cash'" class="h-4 w-4 text-green-600" />
        <CreditCard v-else-if="method === 'card'" class="h-4 w-4 text-blue-600" />
        <ArrowRightLeft v-else-if="method === 'transfer'" class="h-4 w-4 text-purple-600" />
        <MoreHorizontal v-else class="h-4 w-4 text-gray-600" />
    </div>
</template>

<script setup lang="ts">
import { Banknote, CreditCard, ArrowRightLeft, MoreHorizontal } from 'lucide-vue-next'

interface Props {
    method: string
}

defineProps<Props>()
</script>

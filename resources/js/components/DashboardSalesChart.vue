<template>
    <div class="h-64">
        <div v-if="data.length === 0" class="flex items-center justify-center h-full text-gray-500">
            <div class="text-center">
                <BarChart3 class="h-12 w-12 mx-auto mb-2 text-gray-400" />
                <p>No hay datos de ventas disponibles</p>
            </div>
        </div>
        <div v-else class="h-full">
            <!-- Simple bar chart representation -->
            <div class="flex items-end justify-between h-full space-x-1 px-4 py-6">
                <div v-for="item in data" :key="item.date" class="flex flex-col items-center flex-1">
                    <div class="bg-blue-500 rounded-t-sm transition-all duration-300 hover:bg-blue-600 w-full min-h-[2px]"
                        :style="{ height: `${(item.sales / maxValue) * 100}%` }"
                        :title="`${item.day}: ${formatCurrency(item.sales)}`">
                    </div>
                    <span class="text-xs text-gray-600 mt-1">
                        {{ item.day }}
                    </span>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">
                    Ventas de los últimos 7 días (Total: {{ formatCurrency(totalSales) }})
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { BarChart3 } from 'lucide-vue-next'

interface Props {
    data: Array<{
        date: string;
        day: string;
        sales: number;
    }>
}

const props = defineProps<Props>()

const maxValue = computed(() => {
    if (props.data.length === 0) return 0
    return Math.max(...props.data.map(item => item.sales))
})

const totalSales = computed(() => {
    return props.data.reduce((sum, item) => sum + item.sales, 0)
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    }).format(amount)
}
</script>

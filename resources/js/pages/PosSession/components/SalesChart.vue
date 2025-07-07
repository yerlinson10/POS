<template>
    <div class="h-64">
        <div v-if="chartData.length === 0" class="flex items-center justify-center h-full text-gray-500">
            <div class="text-center">
                <BarChart3 class="h-12 w-12 mx-auto mb-2 text-gray-400" />
                <p>No hay datos de ventas disponibles</p>
            </div>
        </div>
        <div v-else class="h-full">
            <!-- Simple bar chart representation -->
            <div class="flex items-end justify-between h-full space-x-1 px-4 py-6">
                <div
                    v-for="item in chartData"
                    :key="item.hour"
                    class="flex flex-col items-center flex-1"
                >
                    <div
                        class="bg-blue-500 rounded-t-sm transition-all duration-300 hover:bg-blue-600 w-full min-h-[2px]"
                        :style="{ height: `${(item.total / maxValue) * 100}%` }"
                        :title="`${item.hour}: $${formatCurrency(item.total)} (${item.count} ventas)`"
                    ></div>
                    <span class="text-xs text-gray-600 mt-1 transform -rotate-45 origin-left">
                        {{ item.hour }}
                    </span>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">
                    Ventas por hora (Total: ${{ formatCurrency(totalSales) }})
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { BarChart3 } from 'lucide-vue-next'

interface Props {
    data: Record<string, { count: number; total: number }>
}

const props = defineProps<Props>()

const chartData = computed(() => {
    const entries = Object.entries(props.data)
        .map(([hour, values]) => ({
            hour,
            count: values.count,
            total: values.total
        }))
        .sort((a, b) => {
            // Sort by hour (assuming format like "14:00")
            const hourA = parseInt(a.hour.split(':')[0])
            const hourB = parseInt(b.hour.split(':')[0])
            return hourA - hourB
        })

    return entries
})

const maxValue = computed(() => {
    if (chartData.value.length === 0) return 0
    return Math.max(...chartData.value.map(item => item.total))
})

const totalSales = computed(() => {
    return chartData.value.reduce((sum, item) => sum + item.total, 0)
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}
</script>

<template>
    <div class="rounded-lg border p-6 bg-white dark:bg-muted">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-muted-foreground">{{ title }}</p>
                <p :class="valueClasses">{{ value }}</p>
            </div>
            <div :class="iconContainerClasses">
                <component :is="iconComponent" class="h-6 w-6" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
    DollarSign,
    Receipt,
    Banknote,
    CreditCard,
    TrendingUp,
    Users,
    ShoppingBag,
    Clock
} from 'lucide-vue-next'

interface Props {
    title: string
    value: string
    icon: string
    color?: 'green' | 'blue' | 'purple' | 'emerald' | 'orange' | 'red'
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue'
})

const iconComponents = {
    DollarSign,
    Receipt,
    Banknote,
    CreditCard,
    TrendingUp,
    Users,
    ShoppingBag,
    Clock
}

const iconComponent = computed(() => {
    return iconComponents[props.icon as keyof typeof iconComponents] || DollarSign
})

const colorClasses = {
    green: {
        value: 'text-green-600',
        container: 'bg-green-100 text-green-600'
    },
    blue: {
        value: 'text-blue-600',
        container: 'bg-blue-100 text-blue-600'
    },
    purple: {
        value: 'text-purple-600',
        container: 'bg-purple-100 text-purple-600'
    },
    emerald: {
        value: 'text-emerald-600',
        container: 'bg-emerald-100 text-emerald-600'
    },
    orange: {
        value: 'text-orange-600',
        container: 'bg-orange-100 text-orange-600'
    },
    red: {
        value: 'text-red-600',
        container: 'bg-red-100 text-red-600'
    }
}

const valueClasses = computed(() => {
    return `text-2xl font-bold ${colorClasses[props.color].value}`
})

const iconContainerClasses = computed(() => {
    return `p-3 rounded-full ${colorClasses[props.color].container}`
})
</script>

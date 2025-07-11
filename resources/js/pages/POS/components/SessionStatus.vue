<template>
    <div v-if="showSessionAlert">
        <!-- No Active POS Session -->
        <Alert v-if="!hasActiveSession" variant="destructive"
            class="flex items-center gap-3 px-3 py-2 rounded-md border">
            <AlertCircle class="h-5 w-5 text-destructive shrink-0" />
            <div class="flex-1 min-w-0">
                <AlertTitle class="text-sm font-semibold">No Active POS Session</AlertTitle>
                <AlertDescription class="text-xs text-muted-foreground">
                    You must open a POS session before processing sales.
                </AlertDescription>
            </div>
            <Button @click="openSession" size="icon" variant="outline" class="ml-2 h-7 w-7" title="Open Session">
                <Plus class="h-5 w-5" />
            </Button>
        </Alert>

        <!-- Active POS Session -->
        <Alert v-else-if="session" variant="default" class="flex items-center gap-3 px-3 py-2 rounded-md border">
            <CheckCircle class="h-5 w-5 text-green-600 shrink-0" />
            <div class="flex-1 min-w-0">
                <AlertTitle class="text-sm font-semibold text-green-700">Active POS Session</AlertTitle>
                <AlertDescription class="text-xs text-muted-foreground">
                    <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-0.5">
                        <span><strong>Cashier:</strong> {{ session?.user_name }}</span>
                        <span><strong>Opened:</strong> {{ formatDateTime(session?.opened_at) }}</span>
                        <span><strong>Cash:</strong> ${{ formatCurrency(session?.initial_cash ?? 0) }}</span>
                    </div>
                </AlertDescription>
            </div>
            <Button @click="viewSession" size="icon" variant="outline"
                class="ml-2 h-7 w-7 text-green-600 border-green-200 hover:bg-green-50" title="View Details">
                <Eye class="h-5 w-5" />
            </Button>
            <Button @click="closeSession" size="icon" variant="outline"
                class="ml-1 h-7 w-7 text-destructive border-destructive/30 hover:bg-destructive/10 cursor-pointer"
                title="Close Session">
                <X class="h-5 w-5" />
            </Button>
        </Alert>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, readonly } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { PosSession } from '../../../types/pos'
import { Alert, AlertDescription, AlertTitle } from '../../../components/ui/alert'
import { Button } from '../../../components/ui/button'
import { AlertCircle, CheckCircle, Plus, Eye, X } from 'lucide-vue-next'

interface Props {
    autoCheck?: boolean
    showIfActive?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    autoCheck: true,
    showIfActive: true
})

// State
const hasActiveSession = ref(false)
const session = ref<PosSession | null>(null)
const isLoading = ref(false)

// Computed
const showSessionAlert = computed(() => {
    return !hasActiveSession.value || (hasActiveSession.value && props.showIfActive)
})

// Methods
const checkSessionStatus = async () => {
    if (!props.autoCheck) return

    isLoading.value = true
    try {
        const response = await fetch(route('pos.session-status'))
        const data = await response.json()

        hasActiveSession.value = data.has_active_session
        session.value = data.session
    } catch (error) {
        console.error('Error checking session status:', error)
    } finally {
        isLoading.value = false
    }
}

const openSession = () => {
    router.visit(route('sessions.create'))
}

const viewSession = () => {
    if (session.value) {
        router.visit(route('sessions.current'))
    }
}

const closeSession = () => {
    if (session.value) {
        router.visit(route('sessions.edit', session.value.id))
    }
}

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

// Lifecycle
onMounted(() => {
    checkSessionStatus()
})

// Expose methods for parent component
defineExpose({
    checkSessionStatus,
    hasActiveSession: readonly(hasActiveSession),
    session: readonly(session)
})
</script>

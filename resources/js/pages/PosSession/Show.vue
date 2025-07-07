<template>
    <Head title="POS Session Details" />

    <AppLayout title="POS Session Details">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-xl md:text-2xl font-semibold">POS Session Details</h2>
                            <p class="text-sm text-muted-foreground">
                                Review session information and sales summary
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button @click="goBack" variant="outline" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <ArrowLeft class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Back to Sessions
                            </Button>
                            <Button v-if="session?.status === 'open'" @click="goToPOS" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <ShoppingCart class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Go to POS
                            </Button>
                            <Button v-if="session?.status === 'open'" @click="closeSession" variant="destructive" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <X class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Close Session
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Session Information -->
                <div class="p-4 md:p-6 border-b bg-background">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Cashier</Label>
                            <div class="text-base font-semibold">{{ session?.user?.name || 'N/A' }}</div>
                            <div class="text-sm text-muted-foreground">ID: {{ session?.user?.id }}</div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                            <Badge :variant="session?.status === 'open' ? 'default' : 'secondary'">
                                {{ session?.status === 'open' ? 'Open' : 'Closed' }}
                            </Badge>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Duration</Label>
                            <div class="text-base font-semibold">{{ getDuration() }}</div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Initial Cash</Label>
                            <div class="text-base font-semibold">RD${{ formatCurrency(session?.initial_cash || 0) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Sales Statistics -->
                <div class="p-4 md:p-6 border-b bg-muted/30">
                    <h3 class="text-lg font-semibold mb-4">Sales Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <StatsCard
                            title="Total Sales"
                            :value="`RD$${formatCurrency(summary?.total_sales || 0)}`"
                            icon="DollarSign"
                            color="green"
                        />
                        <StatsCard
                            title="Number of Sales"
                            :value="(summary?.sales_count || 0).toString()"
                            icon="Receipt"
                            color="blue"
                        />
                        <StatsCard
                            title="Cash Sales"
                            :value="`RD$${formatCurrency(summary?.cash_sales || 0)}`"
                            icon="Banknote"
                            color="emerald"
                        />
                        <StatsCard
                            title="Card Sales"
                            :value="`RD$${formatCurrency(summary?.card_sales || 0)}`"
                            icon="CreditCard"
                            color="purple"
                        />
                    </div>
                </div>

                <!-- Cash Summary (for closed sessions) -->
                <div v-if="session?.status === 'closed'" class="p-4 md:p-6 border-b bg-background">
                    <h3 class="text-lg font-semibold mb-4">Cash Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Expected Cash</Label>
                            <div class="text-lg font-semibold">RD${{ formatCurrency(summary?.expected_cash || 0) }}</div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Final Cash</Label>
                            <div class="text-lg font-semibold">RD${{ formatCurrency(summary?.final_cash || 0) }}</div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Cash Difference</Label>
                            <div class="text-lg font-semibold"
                                :class="{
                                    'text-green-600': (summary?.cash_difference || 0) >= 0,
                                    'text-red-600': (summary?.cash_difference || 0) < 0
                                }">
                                {{ (summary?.cash_difference || 0) >= 0 ? '+' : '' }}RD${{ formatCurrency(Math.abs(summary?.cash_difference || 0)) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods Breakdown -->
                <div v-if="summary?.payment_methods_breakdown && Object.keys(summary.payment_methods_breakdown).length > 0"
                    class="p-4 md:p-6 border-b bg-muted/30">
                    <h3 class="text-lg font-semibold mb-4">Payment Methods Breakdown</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(method, key) in summary.payment_methods_breakdown" :key="key"
                            class="p-4 bg-background rounded-lg border">
                            <div class="flex items-center gap-2 mb-2">
                                <PaymentMethodIcon :method="key" />
                                <h4 class="font-medium capitalize">{{ key }}</h4>
                            </div>
                            <div class="space-y-1">
                                <div class="text-lg font-semibold">RD${{ formatCurrency(method.total) }}</div>
                                <div class="text-sm text-muted-foreground">{{ method.count }} transaction{{ method.count !== 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Notes -->
                <div v-if="session?.opening_notes || session?.closing_notes" class="p-4 md:p-6 bg-background">
                    <h3 class="text-lg font-semibold mb-4">Session Notes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="session?.opening_notes" class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Opening Notes</Label>
                            <div class="p-3 bg-muted/50 rounded-lg text-sm">
                                {{ session.opening_notes }}
                            </div>
                        </div>
                        <div v-if="session?.closing_notes" class="space-y-2">
                            <Label class="text-sm font-medium text-muted-foreground">Closing Notes</Label>
                            <div class="p-3 bg-muted/50 rounded-lg text-sm">
                                {{ session.closing_notes }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '../../layouts/AppLayout.vue'
import { Button } from '../../components/ui/button'
import { Badge } from '../../components/ui/badge'
import { Label } from '../../components/ui/label'
import StatsCard from '../../pages/PosSession/components/StatsCard.vue'
import PaymentMethodIcon from '../../pages/PosSession/components/PaymentMethodIcon.vue'
import {
    ArrowLeft,
    ShoppingCart,
    X,
    DollarSign,
    Receipt,
    Banknote,
    CreditCard
} from 'lucide-vue-next'
import type { PosSession, PosSessionSummary } from '../../types/pos'

interface Props {
    session: PosSession
    summary: PosSessionSummary
}

const props = defineProps<Props>()

// Methods
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-DO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getDuration = () => {
    const start = new Date(props.session.opened_at)
    const end = props.session.closed_at ? new Date(props.session.closed_at) : new Date()
    const diffMs = end.getTime() - start.getTime()
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60))
    return `${diffHours}h ${diffMinutes}m`
}

const goBack = () => {
    router.visit(route('sessions.index'))
}

const goToPOS = () => {
    router.visit(route('pos.index'))
}

const closeSession = () => {
    if (props.session?.id) {
        router.visit(route('sessions.edit', props.session.id))
    }
}
</script>

<style scoped>
/* Custom breakpoint para pantallas extra pequeñas */
@media (min-width: 480px) {
    .xs\:flex-row {
        flex-direction: row;
    }

    .xs\:items-center {
        align-items: center;
    }

    .xs\:gap-0 {
        gap: 0;
    }

    .xs\:gap-4 {
        gap: 1rem;
    }
}
</style>

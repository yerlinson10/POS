<template>
    <Head title="POS Sessions" />

    <AppLayout title="POS Sessions">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-xl md:text-2xl font-semibold">POS Sessions</h2>
                            <p class="text-sm text-muted-foreground">
                                Manage point of sale sessions and cash register operations
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button @click="refreshData" variant="outline" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <RefreshCw class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Refresh
                            </Button>
                            <Button @click="createSession" v-if="!stats?.open_sessions" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Plus class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                New Session
                            </Button>
                            <Button @click="viewCurrentSession" v-else variant="outline" size="sm"
                                class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Monitor class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Current Session
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="p-4 md:p-6 border-b bg-background">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <StatsCard title="Total Sessions" :value="(stats?.total_sessions || 0).toString()" icon="Monitor"
                            color="blue" />
                        <StatsCard title="Open Sessions" :value="(stats?.open_sessions || 0).toString()" icon="PlayCircle"
                            color="green" />
                        <StatsCard title="Closed Sessions" :value="(stats?.closed_sessions || 0).toString()" icon="StopCircle"
                            color="purple" />
                        <StatsCard title="Total Sales" :value="formatCurrency(stats?.total_sales || 0)" icon="DollarSign"
                            color="emerald" />
                        <StatsCard title="Cash Difference" :value="formatCurrency(stats?.total_cash_difference || 0)"
                            icon="TrendingUp" :color="(stats?.total_cash_difference || 0) >= 0 ? 'green' : 'red'" />
                    </div>
                </div>

                <!-- Filters Toolbar -->
                <div class="flex flex-col gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Search</Label>
                            <Input v-model="filters.search" placeholder="Search by cashier..."
                                class="h-8 md:h-9 text-sm" @input="debouncedSearch" />
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Status</Label>
                            <Select v-model="filters.status" @update:modelValue="applyFilters">
                                <SelectTrigger class="h-8 md:h-9 text-sm">
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem value="open">Open</SelectItem>
                                    <SelectItem value="closed">Closed</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">From Date</Label>
                            <Input v-model="filters.date_from" type="date"
                                class="h-8 md:h-9 text-sm" @change="applyFilters" />
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">To Date</Label>
                            <Input v-model="filters.date_to" type="date"
                                class="h-8 md:h-9 text-sm" @change="applyFilters" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button @click="clearFilters" variant="outline" size="sm"
                            class="h-8 md:h-9 px-2 md:px-4 text-xs md:text-sm">
                            Clear Filters
                        </Button>
                        <Button @click="applyFilters" size="sm"
                            class="h-8 md:h-9 px-2 md:px-4 text-xs md:text-sm">
                            Apply Filters
                        </Button>
                    </div>
                </div>

                <!-- Sessions Table -->
                <div class="flex-1 overflow-hidden">
                    <div class="h-full overflow-y-auto">
                        <table class="w-full">
                            <thead class="sticky top-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b">
                                <tr class="h-12">
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Cashier</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Opening Date/Time</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Initial Cash</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Status</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Duration</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Sales</th>
                                    <th class="text-left px-4 py-3 font-semibold text-sm">Difference</th>
                                    <th class="text-center px-4 py-3 font-semibold text-sm w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="session in (sessions?.data || [])" :key="session.id"
                                    class="border-b hover:bg-muted/50 transition-colors group">
                                    <!-- Cashier Info -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="font-medium text-sm group-hover:text-primary transition-colors">
                                                {{ session.user?.name || session.user_name || 'N/A' }}
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ session.user?.email || 'N/A' }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Opening Date/Time -->
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-foreground">
                                            {{ formatDateTime(session.opened_at) }}
                                        </span>
                                    </td>

                                    <!-- Initial Cash -->
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-sm">
                                            RD${{ formatCurrency(session.initial_cash) }}
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-4">
                                        <Badge :variant="session.status === 'open' ? 'default' : 'secondary'">
                                            {{ session.status === 'open' ? 'Open' : 'Closed' }}
                                        </Badge>
                                    </td>

                                    <!-- Duration -->
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-muted-foreground">
                                            {{ getDuration(session) }}
                                        </span>
                                    </td>

                                    <!-- Sales -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm">
                                            <div class="font-semibold">RD${{ formatCurrency(session.total_sales || 0) }}</div>
                                            <div class="text-xs text-muted-foreground">{{ session.sales_count || 0 }} sales</div>
                                        </div>
                                    </td>

                                    <!-- Cash Difference -->
                                    <td class="px-4 py-4">
                                        <span v-if="session.cash_difference !== null"
                                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': (session.cash_difference ?? 0) >= 0,
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': (session.cash_difference ?? 0) < 0
                                            }">
                                            {{ (session.cash_difference ?? 0) >= 0 ? '+' : '' }}RD${{
                                                formatCurrency(Math.abs(session.cash_difference ?? 0)) }}
                                        </span>
                                        <span v-else class="text-muted-foreground text-sm">-</span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button @click="viewSession(session)" variant="ghost" size="sm"
                                                class="h-8 w-8 p-0">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            <Button v-if="session.status === 'open'" @click="closeSession(session)"
                                                variant="ghost" size="sm"
                                                class="h-8 w-8 p-0 text-red-600 hover:text-red-700">
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="(sessions?.data?.length || 0) === 0">
                                    <td colspan="8" class="py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <Monitor class="h-12 w-12 text-muted-foreground/50" />
                                            <div>
                                                <h4 class="font-medium text-base mb-2">No POS sessions found</h4>
                                                <p class="text-muted-foreground text-sm">
                                                    No sessions match your current filters
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="(sessions?.data?.length || 0) > 0"
                    class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-4 md:p-6 border-t bg-muted/20">
                    <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                        <div class="text-xs text-muted-foreground">
                            <span class="font-medium">{{ sessions?.pagination?.from || 0 }}-{{ sessions?.pagination?.to || 0 }}</span>
                            <span class="hidden xs:inline"> of </span>
                            <span class="xs:hidden">/</span>
                            <span class="font-medium">{{ sessions?.pagination?.total || 0 }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Page {{ sessions?.pagination?.current_page || 1 }}/{{ sessions?.pagination?.last_page || 1 }}
                        </div>
                    </div>

                    <Pagination v-slot="{ page: internalPage }"
                        :items-per-page="sessions?.pagination?.per_page || 15"
                        :total="sessions?.pagination?.last_page || 1"
                        :page="sessions?.pagination?.current_page || 1"
                        @page-change="changePage"
                        class="flex">
                        <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                            <PaginationPrevious @click="changePage(internalPage - 1)"
                                :disabled="(sessions?.pagination?.current_page || 1) <= 1"
                                class="h-8 md:h-9" />
                            <template v-for="(item, idx) in pages" :key="idx">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === internalPage"
                                    @click="changePage(item.value)"
                                    class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                    {{ item.value }}
                                </PaginationItem>
                            </template>
                            <PaginationEllipsis :index="4"
                                v-if="(sessions?.pagination?.last_page || 1) >= 4"
                                class="h-8 md:h-9" />
                            <PaginationNext @click="changePage(internalPage + 1)"
                                :disabled="(sessions?.pagination?.current_page || 1) >= (sessions?.pagination?.last_page || 1)"
                                class="h-8 md:h-9" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '../../layouts/AppLayout.vue'
import { Button } from '../../components/ui/button'
import { Input } from '../../components/ui/input'
import { Label } from '../../components/ui/label'
import { Badge } from '../../components/ui/badge'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '../../components/ui/select'
import StatsCard from '../PosSession/components/StatsCard.vue'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import {
    RefreshCw,
    Plus,
    Monitor,
    Eye,
    X,
    PlayCircle,
    StopCircle,
    DollarSign,
    TrendingUp
} from 'lucide-vue-next'
import { debounce } from 'lodash-es'
import type { PosSession } from '../../types/pos'

interface Props {
    sessions?: {
        data: PosSession[]
        pagination: {
            current_page: number
            last_page: number
            per_page: number
            total: number
            from: number | null
            to: number | null
        }
    }
    stats?: {
        total_sessions: number
        open_sessions: number
        closed_sessions: number
        total_sales: number
        total_cash_difference: number
    }
    filters?: Record<string, any>
}

const props = withDefaults(defineProps<Props>(), {
    sessions: () => ({
        data: [],
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: null,
            to: null
        }
    }),
    stats: () => ({
        total_sessions: 0,
        open_sessions: 0,
        closed_sessions: 0,
        total_sales: 0,
        total_cash_difference: 0
    }),
    filters: () => ({})
})

// Local state
const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || 'all',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    per_page: props.filters?.per_page || 15
})

// Methods
const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-DO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const getDuration = (session: PosSession) => {
    const start = new Date(session.opened_at)
    const end = session.closed_at ? new Date(session.closed_at) : new Date()
    const diffMs = end.getTime() - start.getTime()
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60))
    return `${diffHours}h ${diffMinutes}m`
}

const debouncedSearch = debounce(() => {
    applyFilters()
}, 500)

const applyFilters = () => {
    // Convertir "all" a cadena vacía para el backend
    const filtersToSend = { ...filters }
    if (filtersToSend.status === 'all') {
        filtersToSend.status = ''
    }

    router.get(route('sessions.index'), filtersToSend, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    Object.keys(filters).forEach(key => {
        if (key !== 'per_page') {
            filters[key as keyof typeof filters] = key === 'status' ? 'all' : ''
        }
    })
    applyFilters()
}

const changePage = (page: number) => {
    router.get(route('sessions.index'), { ...filters, page }, {
        preserveState: true,
        preserveScroll: true
    })
}

const refreshData = () => {
    router.reload({ only: ['sessions', 'stats'] })
}

const createSession = () => {
    router.visit(route('sessions.create'))
}

const viewCurrentSession = () => {
    router.visit(route('sessions.current'))
}

const viewSession = (session: PosSession) => {
    router.visit(route('sessions.show', session.id))
}

const closeSession = (session: PosSession) => {
    router.visit(route('sessions.edit', session.id))
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

    .xs\:block {
        display: block;
    }

    .xs\:inline {
        display: inline;
    }

    .xs\:hidden {
        display: none;
    }
}

/* Mejorar scroll en móvil */
@media (max-width: 767px) {
    .overflow-y-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

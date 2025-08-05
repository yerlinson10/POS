import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

export interface CustomerDebtFilters {
    search: string
    sort_by: string
    sort_dir: string
    status: string
    page: number
    per_page: number
}

export interface CustomerDebt {
    id: number
    customer_id: number
    customer_name: string
    invoice_id: number
    original_amount: number
    paid_amount: number
    remaining_amount: number
    debt_date?: string
    due_date?: string
    status: 'pending' | 'partial' | 'paid' | 'overdue'
    days_overdue: number
    user?: string
    description?: string
    created_at: string
}

export interface Paginated<T> {
    data: T[]
    total: number
    from: number
    to: number
    current_page: number
    last_page: number
}

interface CustomerDebtFiltersProps {
    debts: Paginated<CustomerDebt>
    filters: CustomerDebtFilters
}

export function useCustomerDebtFilters(props: CustomerDebtFiltersProps) {
    const filters = reactive<CustomerDebtFilters>({
        search: props.filters.search || '',
        sort_by: props.filters.sort_by || 'created_at',
        sort_dir: props.filters.sort_dir || 'desc',
        status: props.filters.status || '',
        page: props.filters.page || 1,
        per_page: props.filters.per_page || 15,
    })

    const hasActiveFilters = ref(
        Boolean(filters.search || filters.status || filters.sort_by !== 'created_at' || filters.sort_dir !== 'desc')
    )

    const search = () => {
        const params = new URLSearchParams()
        
        if (filters.search) params.append('search', filters.search)
        if (filters.sort_by) params.append('sort_by', filters.sort_by)
        if (filters.sort_dir) params.append('sort_dir', filters.sort_dir)
        if (filters.status) params.append('status', filters.status)
        if (filters.page > 1) params.append('page', filters.page.toString())
        if (filters.per_page !== 15) params.append('per_page', filters.per_page.toString())

        const url = `/customer-debts${params.toString() ? '?' + params.toString() : ''}`
        
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        })
    }

    const resetFilters = () => {
        filters.search = ''
        filters.sort_by = 'created_at'
        filters.sort_dir = 'desc'
        filters.status = ''
        filters.page = 1
        filters.per_page = 15
        hasActiveFilters.value = false
        search()
    }

    const onPageChange = (page: number) => {
        filters.page = page
        search()
    }

    return {
        filters,
        hasActiveFilters,
        search,
        resetFilters,
        onPageChange,
    }
}

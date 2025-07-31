import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

export interface PaymentFilters {
    search: string
    sort_by: string
    sort_dir: string
    payment_type: string
    payment_method: string
    page: number
    per_page: number
}

export interface Payment {
    id: number
    payment_type: 'income' | 'expense'
    amount: number
    payment_method: string
    category?: string
    description?: string
    related_entity?: string
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

interface PaymentFiltersProps {
    payments: Paginated<Payment>
    filters: PaymentFilters
}

export function usePaymentFilters(props: PaymentFiltersProps) {
    const filters = reactive<PaymentFilters>({
        search: props.filters.search || '',
        sort_by: props.filters.sort_by || 'created_at',
        sort_dir: props.filters.sort_dir || 'desc',
        payment_type: props.filters.payment_type || '',
        payment_method: props.filters.payment_method || '',
        page: props.filters.page || 1,
        per_page: props.filters.per_page || 15,
    })

    const hasActiveFilters = ref(
        Boolean(filters.search || filters.payment_type || filters.payment_method || filters.sort_by !== 'created_at' || filters.sort_dir !== 'desc')
    )

    const search = () => {
        const params = new URLSearchParams()
        
        if (filters.search) params.append('search', filters.search)
        if (filters.sort_by) params.append('sort_by', filters.sort_by)
        if (filters.sort_dir) params.append('sort_dir', filters.sort_dir)
        if (filters.payment_type) params.append('payment_type', filters.payment_type)
        if (filters.payment_method) params.append('payment_method', filters.payment_method)
        if (filters.page > 1) params.append('page', filters.page.toString())
        if (filters.per_page !== 15) params.append('per_page', filters.per_page.toString())

        const url = `/payments${params.toString() ? '?' + params.toString() : ''}`
        
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        })
    }

    const resetFilters = () => {
        filters.search = ''
        filters.sort_by = 'created_at'
        filters.sort_dir = 'desc'
        filters.payment_type = ''
        filters.payment_method = ''
        filters.page = 1
        filters.per_page = 15
        hasActiveFilters.value = false
        search()
    }

    const onPageChange = (page: number) => {
        filters.page = page
        search()
    }

    const destroy = async (id: number) => {
        try {
            router.delete(`/payments/${id}`, {
                onSuccess: () => {
                    toast.success('Payment deleted successfully')
                },
                onError: () => {
                    toast.error('Error deleting payment')
                }
            })
        } catch {
            toast.error('An unexpected error occurred')
        }
    }

    return {
        filters,
        hasActiveFilters,
        search,
        resetFilters,
        onPageChange,
        destroy,
    }
}

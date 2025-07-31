import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

export interface SupplierFilters {
    search: string
    sort_by: string
    sort_dir: string
    debt_status: string
    page: number
    per_page: number
}

export interface Supplier {
    id: number
    company_name: string
    contact_name: string
    email: string
    phone?: string
    tax_id?: string
    total_debt: number
}

export interface Paginated<T> {
    data: T[]
    total: number
    from: number
    to: number
    current_page: number
    last_page: number
}

interface SupplierFiltersProps {
    suppliers: Paginated<Supplier>
    filters: SupplierFilters
}

export function useSupplierFilters(props: SupplierFiltersProps) {
    const filters = reactive<SupplierFilters>({
        search: props.filters.search || '',
        sort_by: props.filters.sort_by || 'created_at',
        sort_dir: props.filters.sort_dir || 'desc',
        debt_status: props.filters.debt_status || '',
        page: props.filters.page || 1,
        per_page: props.filters.per_page || 15,
    })

    const hasActiveFilters = ref(
        Boolean(filters.search || filters.debt_status || filters.sort_by !== 'created_at' || filters.sort_dir !== 'desc')
    )

    const search = () => {
        const params = new URLSearchParams()
        
        if (filters.search) params.append('search', filters.search)
        if (filters.sort_by) params.append('sort_by', filters.sort_by)
        if (filters.sort_dir) params.append('sort_dir', filters.sort_dir)
        if (filters.debt_status) params.append('debt_status', filters.debt_status)
        if (filters.page > 1) params.append('page', filters.page.toString())
        if (filters.per_page !== 15) params.append('per_page', filters.per_page.toString())

        const url = `/suppliers${params.toString() ? '?' + params.toString() : ''}`
        
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        })
    }

    const resetFilters = () => {
        filters.search = ''
        filters.sort_by = 'created_at'
        filters.sort_dir = 'desc'
        filters.debt_status = ''
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
            router.delete(`/suppliers/${id}`, {
                onSuccess: () => {
                    toast.success('Supplier deleted successfully')
                },
                onError: () => {
                    toast.error('Error deleting supplier')
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

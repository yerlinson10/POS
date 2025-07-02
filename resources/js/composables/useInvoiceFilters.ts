import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export interface Invoice {
    id: number
    customer_id: number
    user_id: number
    date: string
    total_amount: number
    status: 'pending' | 'paid' | 'canceled'
    created_at: string
    updated_at: string
    customer?: {
        id: number
        first_name: string
        last_name: string
        email: string
        phone?: string
    }
    user?: {
        id: number
        name: string
        email: string
    }
    items?: InvoiceItem[]
}

export interface InvoiceItem {
    id: number
    invoice_id: number
    product_id: number
    quantity: number
    unit_price: number
    line_total: number
    product?: {
        id: number
        sku: string
        name: string
        price: number
        category?: {
            id: number
            name: string
        }
        unitMeasure?: {
            id: number
            name: string
            code: string
        }
    }
}

export interface InvoiceFilters {
    search?: string
    status?: string
    date_from?: string
    date_to?: string
    sort_by?: string
    sort_dir?: string
    per_page?: number
    page?: number
}

export interface Paginated<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
}

interface UseInvoiceFiltersProps {
    invoices: Paginated<Invoice>
    filters: InvoiceFilters
}

export function useInvoiceFilters(props: UseInvoiceFiltersProps) {
    // Reactive filters state
    const filters = ref<InvoiceFilters>({ ...props.filters })

    // Computed properties
    const hasActiveFilters = computed(() => {
        return !!(
            filters.value.search ||
            filters.value.status ||
            filters.value.date_from ||
            filters.value.date_to
        )
    })

    // Methods
    const search = () => {
        router.get('/invoices', filters.value, {
            preserveState: true,
            preserveScroll: true,
        })
    }

    const resetFilters = () => {
        filters.value = {
            search: '',
            status: '',
            date_from: '',
            date_to: '',
            sort_by: 'created_at',
            sort_dir: 'desc',
            per_page: 15,
            page: 1
        }
        search()
    }

    const onPageChange = (page: number) => {
        filters.value.page = page
        search()
    }

    const sortBy = (field: string) => {
        const newDirection = filters.value.sort_by === field &&
                            filters.value.sort_dir === 'asc' ? 'desc' : 'asc'
        filters.value.sort_by = field
        filters.value.sort_dir = newDirection
        filters.value.page = 1
        search()
    }

    const destroy = (invoiceId: number) => {
        if (confirm('Are you sure you want to delete this invoice? This action cannot be undone and will restore the product stock.')) {
            router.delete(`/invoices/${invoiceId}`, {
                onSuccess: () => {
                    // Handle success in component
                },
                onError: () => {
                    // Handle error in component
                }
            })
        }
    }

    const updateStatus = (invoiceId: number, status: string) => {
        router.patch(`/invoices/${invoiceId}`, { status }, {
            onSuccess: () => {
                // Handle success in component
            },
            onError: () => {
                // Handle error in component
            }
        })
    }

    const filterByStatus = (status: string) => {
        filters.value.status = status
        filters.value.page = 1
        search()
    }

    const filterByDateRange = (dateFrom: string, dateTo: string) => {
        filters.value.date_from = dateFrom
        filters.value.date_to = dateTo
        filters.value.page = 1
        search()
    }

    const searchInvoices = (query: string) => {
        filters.value.search = query
        filters.value.page = 1
        search()
    }

    // Utility functions
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString()
    }

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount)
    }

    const getStatusVariant = (status: string) => {
        switch (status) {
            case 'paid':
                return 'default'
            case 'pending':
                return 'secondary'
            case 'canceled':
                return 'destructive'
            default:
                return 'outline'
        }
    }

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'paid':
                return 'text-green-600'
            case 'pending':
                return 'text-yellow-600'
            case 'canceled':
                return 'text-red-600'
            default:
                return 'text-gray-600'
        }
    }

    const getTotalsByStatus = (invoices: Invoice[]) => {
        return {
            paid: invoices
                .filter(inv => inv.status === 'paid')
                .reduce((sum, inv) => sum + inv.total_amount, 0),
            pending: invoices
                .filter(inv => inv.status === 'pending')
                .reduce((sum, inv) => sum + inv.total_amount, 0),
            canceled: invoices
                .filter(inv => inv.status === 'canceled')
                .reduce((sum, inv) => sum + inv.total_amount, 0),
        }
    }

    return {
        // State
        filters,

        // Computed
        hasActiveFilters,

        // Methods
        search,
        resetFilters,
        onPageChange,
        sortBy,
        destroy,
        updateStatus,
        filterByStatus,
        filterByDateRange,
        searchInvoices,

        // Utilities
        formatDate,
        formatCurrency,
        getStatusVariant,
        getStatusColor,
        getTotalsByStatus
    }
}

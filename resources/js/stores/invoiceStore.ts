import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

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

export interface PaginatedInvoices {
    data: Invoice[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
}

export const useInvoiceStore = defineStore('invoice', () => {
    // State
    const invoices = ref<PaginatedInvoices | null>(null)
    const currentInvoice = ref<Invoice | null>(null)
    const filters = ref<InvoiceFilters>({
        search: '',
        status: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_dir: 'desc',
        per_page: 15,
        page: 1
    })
    const loading = ref(false)
    const error = ref<string | null>(null)

    // Computed
    const hasInvoices = computed(() => {
        return invoices.value && invoices.value.data.length > 0
    })

    const totalInvoices = computed(() => {
        return invoices.value?.total || 0
    })

    const currentPageInvoices = computed(() => {
        return invoices.value?.data || []
    })

    const hasActiveFilters = computed(() => {
        return !!(
            filters.value.search ||
            filters.value.status ||
            filters.value.date_from ||
            filters.value.date_to
        )
    })

    // Actions
    const setInvoices = (data: PaginatedInvoices) => {
        invoices.value = data
    }

    const setCurrentInvoice = (invoice: Invoice) => {
        currentInvoice.value = invoice
    }

    const updateFilters = (newFilters: Partial<InvoiceFilters>) => {
        filters.value = { ...filters.value, ...newFilters }
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
    }

    const setLoading = (isLoading: boolean) => {
        loading.value = isLoading
    }

    const setError = (errorMessage: string | null) => {
        error.value = errorMessage
    }

    const addInvoice = (invoice: Invoice) => {
        if (invoices.value) {
            invoices.value.data.unshift(invoice)
            invoices.value.total += 1
        }
    }

    const updateInvoice = (updatedInvoice: Invoice) => {
        if (invoices.value) {
            const index = invoices.value.data.findIndex(inv => inv.id === updatedInvoice.id)
            if (index > -1) {
                invoices.value.data[index] = updatedInvoice
            }
        }

        if (currentInvoice.value && currentInvoice.value.id === updatedInvoice.id) {
            currentInvoice.value = updatedInvoice
        }
    }

    const removeInvoice = (invoiceId: number) => {
        if (invoices.value) {
            const index = invoices.value.data.findIndex(inv => inv.id === invoiceId)
            if (index > -1) {
                invoices.value.data.splice(index, 1)
                invoices.value.total -= 1
            }
        }

        if (currentInvoice.value && currentInvoice.value.id === invoiceId) {
            currentInvoice.value = null
        }
    }

    const getInvoiceById = (id: number) => {
        return invoices.value?.data.find(invoice => invoice.id === id) || null
    }

    const getInvoicesByStatus = (status: string) => {
        return invoices.value?.data.filter(invoice => invoice.status === status) || []
    }

    const getTotalAmountByStatus = (status: string) => {
        return getInvoicesByStatus(status).reduce((total, invoice) => total + invoice.total_amount, 0)
    }

    const searchInvoices = async (searchQuery: string) => {
        updateFilters({ search: searchQuery, page: 1 })
        // This would trigger a new request in the component
    }

    const changePage = (page: number) => {
        updateFilters({ page })
        // This would trigger a new request in the component
    }

    const changePerPage = (perPage: number) => {
        updateFilters({ per_page: perPage, page: 1 })
        // This would trigger a new request in the component
    }

    const sortBy = (field: string) => {
        const newDirection = filters.value.sort_by === field && filters.value.sort_dir === 'asc' ? 'desc' : 'asc'
        updateFilters({ sort_by: field, sort_dir: newDirection, page: 1 })
        // This would trigger a new request in the component
    }

    return {
        // State
        invoices,
        currentInvoice,
        filters,
        loading,
        error,

        // Computed
        hasInvoices,
        totalInvoices,
        currentPageInvoices,
        hasActiveFilters,

        // Actions
        setInvoices,
        setCurrentInvoice,
        updateFilters,
        resetFilters,
        setLoading,
        setError,
        addInvoice,
        updateInvoice,
        removeInvoice,
        getInvoiceById,
        getInvoicesByStatus,
        getTotalAmountByStatus,
        searchInvoices,
        changePage,
        changePerPage,
        sortBy
    }
})

import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export interface Paginated<T> {
    data: T[]
    current_page: number
    per_page: number
    total: number
    last_page: number
}

export interface BaseFilters {
    per_page: number;
    search?: string;
    page?: number;
    sort_by?: string;
    sort_dir?: string;
}

export interface UseFiltersOptions {
    endpoint: string;
    defaultSortBy?: string;
    defaultSortDir?: string;
}

export function useFilters<T, F extends BaseFilters>(
    props: { data: Paginated<T>, filters: F },
    options: UseFiltersOptions
) {
    const {
        endpoint,
        defaultSortBy = 'created_at',
        defaultSortDir = 'desc'
    } = options

    const filters = ref({
        ...props.filters,
        page: props.filters.page ?? props.data.current_page,
        sort_by: props.filters.sort_by ?? defaultSortBy,
        sort_dir: props.filters.sort_dir ?? defaultSortDir,
    })

    const hasActiveFilters = computed(() => {
        return (
            (filters.value.search && filters.value.search.length > 0) ||
            filters.value.sort_by !== defaultSortBy ||
            filters.value.sort_dir !== defaultSortDir ||
            filters.value.page !== 1
        )
    })

    function resetFilters() {
        filters.value = {
            ...filters.value,
            search: '',
            page: 1,
            sort_by: defaultSortBy,
            sort_dir: defaultSortDir,
        }
        getList()
    }

    function getList() {
        router.get(
            endpoint,
            filters.value,
            { preserveState: true, replace: true }
        )
    }

    function search() {
        filters.value.page = 1
        getList()
    }

    function onPageChange(newPage: number) {
        filters.value.page = newPage
        getList()
    }

    function destroy(id: number) {
        router.delete(`${endpoint}/${id}`)
    }

    return {
        filters,
        hasActiveFilters,
        resetFilters,
        getList,
        search,
        onPageChange,
        destroy,
    }
}

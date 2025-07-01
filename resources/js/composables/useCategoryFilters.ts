import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export interface Category {
    id: number
    name: string
}

export interface Paginated<T> {
    data: T[]
    current_page: number
    per_page: number
    total: number
    last_page: number
}

export interface CategoryFilters {
    per_page: number;
    search?: string;
    page?: number;
    sort_by?: string;
    sort_dir?: string;
}

export function useCategoryFilters(props: { categories: Paginated<Category>, filters: CategoryFilters }) {
    const filters = ref({
        ...props.filters,
        page: props.filters.page ?? props.categories.current_page,
        sort_by: props.filters.sort_by ?? 'created_at',
        sort_dir: props.filters.sort_dir ?? 'desc',
    })

    const hasActiveFilters = computed(() => {
        return (
            (filters.value.search && filters.value.search.length > 0) ||
            filters.value.sort_by !== 'created_at' ||
            filters.value.sort_dir !== 'desc' ||
            filters.value.page !== 1
        )
    })

    function resetFilters() {
        filters.value = {
            per_page: props.filters.per_page,
            search: '',
            page: 1,
            sort_by: 'created_at',
            sort_dir: 'desc',
        }
        getList()
    }

    function getList() {
        router.get(
            '/categories',
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
        router.delete(`/categories/${id}`)
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

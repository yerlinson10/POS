import { useFilters, type BaseFilters, type Paginated } from './useFilters'

export interface Category {
    id: number
    name: string
}

export function useCategoryFilters(props: { categories: Paginated<Category>, filters: BaseFilters }) {
    return useFilters(
        { data: props.categories, filters: props.filters },
        { endpoint: '/categories' }
    )
}

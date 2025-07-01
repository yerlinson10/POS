import { useFilters, type BaseFilters, type Paginated } from './useFilters'

export interface Product {
    id: number
    sku: string
    name: string
    category: string
    unit_measure: string
    price: number
    stock: number
}

export function useProductFilters(props: { products: Paginated<Product>, filters: BaseFilters }) {
    return useFilters(
        { data: props.products, filters: props.filters },
        { endpoint: '/products' }
    )
}

import { useFilters, type BaseFilters, type Paginated } from './useFilters'

export interface Customer {
    id :number
    first_name :string
    last_name :string
    email :string
    phone :string
    address :string
}

export function useCustomerFilters(props: { customers: Paginated<Customer>, filters: BaseFilters }) {
    return useFilters(
        { data: props.customers, filters: props.filters },
        { endpoint: '/customers' }
    )
}

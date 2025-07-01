import { useFilters, type BaseFilters, type Paginated } from './useFilters'

export interface Unit {
    id: number
    name: string,
    code: string
}

export function useUnitFilters(props: { units: Paginated<Unit>, filters: BaseFilters }) {
    return useFilters(
        { data: props.units, filters: props.filters },
        { endpoint: '/unit-measures' }
    )
}

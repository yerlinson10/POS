import { ref, computed } from 'vue';
import type { FilterOptions } from '@/pages/types/dashboard';

export function useFilterOptions() {
    const filterOptions = ref<FilterOptions>({
        payment_methods: [],
        users: [],
        categories: [],
        group_by_options: [],
        available_fields: []
    });
    const isLoading = ref(false);

    const loadFilterOptions = async (widgetType?: string) => {
        isLoading.value = true;

        try {
            // Construir la URL con el parámetro widget_type si existe
            const url = new URL(route('dynamic-dashboard.filter-options'), window.location.origin);
            if (widgetType) {
                url.searchParams.set('widget_type', widgetType);
            }

            const response = await fetch(url.toString());

            if (!response.ok) {
                throw new Error('Error al cargar opciones de filtros');
            }

            const data = await response.json();
            filterOptions.value = data;
        } catch (error) {
            console.error('Error loading filter options:', error);
            // Opciones por defecto en caso de error
            filterOptions.value = {
                available_fields: [],
                payment_methods: [],
                users: [],
                categories: [],
                group_by_options: []
            };
        } finally {
            isLoading.value = false;
        }
    };

    const availableFields = computed(() => filterOptions.value.available_fields || []);

    return {
        filterOptions,
        availableFields,
        isLoading,
        loadFilterOptions
    };
}

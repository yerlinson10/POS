import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { Product, ProductFilters } from '../types/pos';

export const useProductStore = defineStore('products', () => {
    // State
    const products = ref<Product[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
        from: null as number | null,
        to: null as number | null,
    });
    const filters = ref<ProductFilters>({
        per_page: 20,
        search: '',
        sort_by: 'name',
        sort_dir: 'asc',
        current_page: 1,
    });

    // Actions
    const fetchProducts = async (newFilters?: Partial<ProductFilters>) => {
        isLoading.value = true;
        error.value = null;

        try {
            // Update filters if provided
            if (newFilters) {
                filters.value = { ...filters.value, ...newFilters };
            }

            const response = await axios.get<{
                products: any;
                pagination: typeof pagination.value;
                filters: ProductFilters;
            }>('/pos/products', {
                params: filters.value,
            });

            // Handle Laravel paginated response structure
            products.value = response.data.products.data || response.data.products;
            pagination.value = response.data.pagination;
            filters.value = response.data.filters;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Error fetching products';
            products.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    const searchProducts = async (searchTerm: string) => {
        await fetchProducts({ search: searchTerm, current_page: 1 });
    };

    const loadPage = async (page: number) => {
        await fetchProducts({ current_page: page });
    };

    const changePerPage = async (perPage: number) => {
        await fetchProducts({ per_page: perPage, current_page: 1 });
    };

    const sortProducts = async (sortBy: string, sortDir: 'asc' | 'desc' = 'asc') => {
        await fetchProducts({ sort_by: sortBy, sort_dir: sortDir, current_page: 1 });
    };

    const getProductById = (id: number): Product | undefined => {
        return products.value.find((product) => product.id === id);
    };

    const updateProductStock = (productId: number, newStock: number) => {
        const product = products.value.find((p) => p.id === productId);
        if (product) {
            product.stock = newStock;
        }
    };

    // Real-time stock updates
    const refreshProductUpdates = async (productIds: number[]) => {
        if (productIds.length === 0) return;

        try {
            const response = await axios.get('/pos/product-updates', {
                params: { product_ids: productIds },
            });

            const updates = response.data.products;

            Object.entries(updates).forEach(([id, data]: [string, any]) => {
                const productId = parseInt(id);
                const product = products.value.find((p) => p.id === productId);
                if (product) {
                    product.stock = data.stock;
                    product.price = data.price;
                }
            });
        } catch (err) {
            console.error('Error refreshing product updates:', err);
        }
    };

    const resetFilters = () => {
        filters.value = {
            per_page: 20,
            search: '',
            sort_by: 'name',
            sort_dir: 'asc',
            current_page: 1,
        };
    };

    return {
        // State
        products,
        isLoading,
        error,
        pagination,
        filters,

        // Actions
        fetchProducts,
        searchProducts,
        loadPage,
        changePerPage,
        sortProducts,
        getProductById,
        updateProductStock,
        refreshProductUpdates,
        resetFilters,
    };
});

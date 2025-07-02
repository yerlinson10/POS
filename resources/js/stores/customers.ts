import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { Customer, CustomerCreateRequest } from '../types/pos';

export const useCustomerStore = defineStore('customers', () => {
    // State
    const customers = ref<Customer[]>([]);
    const isLoading = ref(false);
    const isCreating = ref(false);
    const error = ref<string | null>(null);
    const searchTerm = ref('');

    // Actions
    const searchCustomers = async (search: string = '') => {
        isLoading.value = true;
        error.value = null;
        searchTerm.value = search;

        try {
            const response = await axios.get<{ customers: Customer[] }>('/pos/customers', {
                params: {
                    search: search.trim(),
                    limit: 10,
                },
            });

            customers.value = response.data.customers;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Error searching customers';
            customers.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    const createCustomer = async (customerData: CustomerCreateRequest): Promise<Customer> => {
        isCreating.value = true;
        error.value = null;

        try {
            const response = await axios.post<{
                customer: Customer;
                message: string;
            }>('/pos/customers', customerData);

            const newCustomer = response.data.customer;

            // Add to current list if not already there
            const exists = customers.value.find((c) => c.id === newCustomer.id);
            if (!exists) {
                customers.value.unshift(newCustomer);
            }

            return newCustomer;
        } catch (err: any) {
            const errorMessage = err.response?.data?.message || err.message || 'Error creating customer';
            error.value = errorMessage;
            throw new Error(errorMessage);
        } finally {
            isCreating.value = false;
        }
    };

    const getCustomerById = (id: number): Customer | undefined => {
        return customers.value.find((customer) => customer.id === id);
    };

    const clearSearch = () => {
        searchTerm.value = '';
        customers.value = [];
    };

    const clearError = () => {
        error.value = null;
    };

    return {
        // State
        customers,
        isLoading,
        isCreating,
        error,
        searchTerm,

        // Actions
        searchCustomers,
        createCustomer,
        getCustomerById,
        clearSearch,
        clearError,
    };
});

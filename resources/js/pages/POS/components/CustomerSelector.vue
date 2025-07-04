<template>
    <div class="space-y-2 customer-selector">
        <div class="relative">
            <Input v-model="searchTerm" placeholder="Search customers... (F5, ↑↓ to navigate, Enter to select)"
                class="pr-10" data-customer-selector-input @input="handleSearch" @focus="showDropdown = true"
                @keydown="handleKeyDown" />
            <Button v-if="searchTerm" @click="clearSearch" variant="ghost" size="sm"
                class="absolute right-1 top-1 h-8 w-8 p-0">
                <Icon name="X" class="h-4 w-4" />
            </Button>
        </div>

        <!-- Selected Customer Display -->
        <div v-if="selectedCustomer" class="flex items-center justify-between p-3 bg-accent rounded-lg">
            <div class="flex-1">
                <div class="font-medium">{{ selectedCustomer.full_name }}</div>
                <div class="text-sm text-muted-foreground">
                    {{ selectedCustomer.email || 'No email' }} • {{ selectedCustomer.phone || 'No phone' }}
                </div>
            </div>
            <Button @click="clearSelection" variant="ghost" size="sm" class="cursor-pointer">
                <Icon name="X" class="h-4 w-4" />
            </Button>
        </div>

        <!-- Customer Dropdown -->
        <div v-if="showDropdown && !selectedCustomer && (customers.length > 0 || isLoading)"
            class="customer-dropdown absolute z-50 mt-1 w-full bg-background border rounded-lg shadow-lg max-h-60 overflow-y-auto">
            <div v-if="isLoading" class="p-4 text-center text-muted-foreground">
                <Icon name="Loader2" class="h-4 w-4 animate-spin mx-auto mb-2" />
                Searching customers...
            </div>

            <div v-for="(customer, index) in customers" :key="customer.id" :data-customer-index="index"
                @click="selectCustomer(customer)" :class="[
                    'p-3 cursor-pointer border-b last:border-b-0',
                    index === highlightedIndex ? 'bg-accent' : 'hover:bg-accent'
                ]">
                <div class="font-medium">{{ customer.full_name }}</div>
                <div class="text-sm text-muted-foreground">
                    {{ customer.email || 'No email' }} • {{ customer.phone || 'No phone' }}
                </div>
                <div v-if="customer.address" class="text-xs text-muted-foreground">
                    {{ customer.address }}
                </div>
            </div>

            <div v-if="!isLoading && customers.length === 0 && searchTerm"
                class="p-4 text-center text-muted-foreground">
                No customers found
            </div>
        </div>

        <!-- Walk-in Customer Option -->
        <div v-if="!selectedCustomer" class="flex gap-2">
            <Button @click="selectWalkInCustomer" variant="outline" class="flex-1">
                <Icon name="User" class="h-4 w-4 mr-2" />
                Walk-in Customer
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useCustomerStore } from '../../../stores/customers'
import type { Customer } from '../../../types/pos'
import { Input } from '../../../components/ui/input'
import { Button } from '../../../components/ui/button'
import Icon from '../../../components/Icon.vue'

// Simple debounce function
function debounce<T extends (...args: any[]) => any>(func: T, wait: number): T {
    let timeout: number | null = null
    return ((...args: any[]) => {
        if (timeout !== null) {
            clearTimeout(timeout)
        }
        timeout = window.setTimeout(() => func(...args), wait)
    }) as T
}

interface Props {
    modelValue?: Customer | null
}

interface Emits {
    (e: 'update:modelValue', value: Customer | null): void
    (e: 'customer-selected', customer: Customer | null): void
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null
})

const emit = defineEmits<Emits>()

// Store
const customerStore = useCustomerStore()
const { customers, isLoading } = storeToRefs(customerStore)

// Local state
const searchTerm = ref('')
const showDropdown = ref(false)
const selectedCustomer = ref<Customer | null>(props.modelValue)
const highlightedIndex = ref(-1)

// Debounced search function
const debouncedSearch = debounce(async (term: string) => {
    if (term.trim()) {
        await customerStore.searchCustomers(term)
    } else {
        customerStore.clearSearch()
    }
}, 300)

// Methods
const handleKeyDown = (event: KeyboardEvent) => {
    if (!showDropdown.value || customers.value.length === 0) return

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault()
            highlightedIndex.value = Math.min(highlightedIndex.value + 1, customers.value.length - 1)
            scrollToHighlighted()
            break
        case 'ArrowUp':
            event.preventDefault()
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
            scrollToHighlighted()
            break
        case 'Enter':
            event.preventDefault()
            if (highlightedIndex.value >= 0 && highlightedIndex.value < customers.value.length) {
                selectCustomer(customers.value[highlightedIndex.value])
            }
            break
        case 'Escape':
            event.preventDefault()
            showDropdown.value = false
            highlightedIndex.value = -1
            break
    }
}

const scrollToHighlighted = () => {
    if (highlightedIndex.value >= 0) {
        const dropdown = document.querySelector('.customer-dropdown')
        const highlightedElement = dropdown?.querySelector(`[data-customer-index="${highlightedIndex.value}"]`)
        if (highlightedElement) {
            highlightedElement.scrollIntoView({ block: 'nearest' })
        }
    }
}

const handleSearch = () => {
    if (!selectedCustomer.value) {
        highlightedIndex.value = -1 // Reset highlighted index when searching
        debouncedSearch(searchTerm.value)
    }
}

const selectCustomer = (customer: Customer) => {
    selectedCustomer.value = customer
    showDropdown.value = false
    searchTerm.value = customer.full_name
    highlightedIndex.value = -1
    emit('update:modelValue', customer)
    emit('customer-selected', customer)
}

const selectWalkInCustomer = () => {
    const walkInCustomer: Customer = {
        id: 0,
        full_name: 'Walk-in Customer',
        email: undefined,
        phone: undefined,
        address: undefined
    }
    selectCustomer(walkInCustomer)
}

const clearSelection = () => {
    selectedCustomer.value = null
    searchTerm.value = ''
    showDropdown.value = false
    highlightedIndex.value = -1
    customerStore.clearSearch()
    emit('update:modelValue', null)
    emit('customer-selected', null)
}

const clearSearch = () => {
    searchTerm.value = ''
    highlightedIndex.value = -1
    customerStore.clearSearch()
    if (!selectedCustomer.value) {
        showDropdown.value = false
    }
}

// Handle clicks outside to close dropdown
const handleClickOutside = (event: Event) => {
    const target = event.target as Element
    if (!target.closest('.customer-selector')) {
        showDropdown.value = false
        highlightedIndex.value = -1
    }
}

// Watch for prop changes
watch(() => props.modelValue, (newValue) => {
    selectedCustomer.value = newValue
    if (newValue) {
        searchTerm.value = newValue.full_name
        showDropdown.value = false
        highlightedIndex.value = -1
    } else {
        searchTerm.value = ''
        highlightedIndex.value = -1
    }
})

// Watch for customers list changes to reset highlighted index
watch(customers, () => {
    highlightedIndex.value = -1
})

// Lifecycle
onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.customer-selector {
    position: relative;
}
</style>

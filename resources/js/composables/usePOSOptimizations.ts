// usePOSOptimizations.ts - Optimizaciones específicas para POS
import { computed, ref, shallowRef, watch, onUnmounted } from 'vue'
import { useDebouncedWatch, useThrottledWatch } from './useOptimizedWatchers'
import { useBusinessLogic } from './useBusinessLogic'
import type { Product, CartItem } from '../types/pos'

export function usePOSOptimizations() {
    // Optimized refs for better performance
    const quickSearchResults = shallowRef<Product[]>([])
    const selectedQuickSearchIndex = ref(-1)
    const quickSearchTerm = ref('')

    // Business logic integration
    const businessLogic = useBusinessLogic()

    // Optimized icon maps with better performance
    const iconMap = new Map([
        ['paid', 'CreditCard'],
        ['quotation', 'Clock']
    ])

    const paymentMethodIconMap = new Map([
        ['cash', 'Banknote'],
        ['card', 'CreditCard'],
        ['transfer', 'ArrowRightLeft'],
        ['other', 'MoreHorizontal']
    ])

    // Optimized computed properties
    const getCheckoutIcon = computed(() => {
        return (invoiceStatus: string) => iconMap.get(invoiceStatus) || 'CreditCard'
    })

    const getPaymentMethodIcon = computed(() => {
        return (method: string) => paymentMethodIconMap.get(method) || 'CreditCard'
    })

    // Optimized search function with debouncing
    const setupQuickSearch = (productStore: any) => {
        // Clear results when search term is empty
        watch(quickSearchTerm, (newValue) => {
            if (!newValue.trim()) {
                quickSearchResults.value = []
                selectedQuickSearchIndex.value = -1
            }
        })

        // Debounced search
        useDebouncedWatch(
            () => quickSearchTerm.value,
            async (newValue) => {
                if (!newValue.trim() || newValue.length < 2) {
                    quickSearchResults.value = []
                    selectedQuickSearchIndex.value = -1
                    return
                }

                try {
                    await productStore.searchProducts(newValue)
                    quickSearchResults.value = productStore.products.slice(0, 5)
                    selectedQuickSearchIndex.value = quickSearchResults.value.length > 0 ? 0 : -1
                } catch (error) {
                    console.error('Error searching products:', error)
                    quickSearchResults.value = []
                    selectedQuickSearchIndex.value = -1
                }
            },
            300
        )
    }

    // Optimized stock updates with throttling
    const setupStockUpdates = (productStore: any, posStore: any, cart: any) => {
        useThrottledWatch(
            () => cart.value.length,
            async () => {
                if (cart.value.length === 0) return

                try {
                    const productIds = cart.value.map((item: CartItem) => item.product_id)
                    await productStore.refreshProductUpdates(productIds)

                    const updates: Record<number, { stock: number, price: number }> = {}
                    productIds.forEach((id: number) => {
                        const product = productStore.getProductById(id)
                        if (product) {
                            updates[id] = { stock: product.stock, price: product.price }
                        }
                    })

                    posStore.updateProductStock(updates)
                } catch (error) {
                    console.error('Error updating stock prices:', error)
                }
            },
            2000
        )
    }

    // Optimized format currency function with memoization
    const formatCurrencyCache = new Map<number, string>()
    const formatCurrency = (amount: number): string => {
        if (formatCurrencyCache.has(amount)) {
            return formatCurrencyCache.get(amount)!
        }

        const formatted = new Intl.NumberFormat('es-DO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount)

        // Limit cache size
        if (formatCurrencyCache.size >= 100) {
            const firstKey = formatCurrencyCache.keys().next().value
            if (firstKey !== undefined) {
                formatCurrencyCache.delete(firstKey)
            }
        }

        formatCurrencyCache.set(amount, formatted)
        return formatted
    }

    // Quick search navigation
    const navigateQuickSearch = (direction: 'up' | 'down') => {
        if (quickSearchResults.value.length === 0) return

        if (direction === 'down') {
            selectedQuickSearchIndex.value = Math.min(
                selectedQuickSearchIndex.value + 1,
                quickSearchResults.value.length - 1
            )
        } else {
            selectedQuickSearchIndex.value = Math.max(
                selectedQuickSearchIndex.value - 1,
                0
            )
        }
    }

    // Clear quick search
    const clearQuickSearch = () => {
        quickSearchTerm.value = ''
        quickSearchResults.value = []
        selectedQuickSearchIndex.value = -1
    }

    // Add first search result
    const addFirstSearchResult = (addToCartFn: (product: Product) => void) => {
        if (quickSearchResults.value.length > 0) {
            const product = quickSearchResults.value[selectedQuickSearchIndex.value] || quickSearchResults.value[0]
            addToCartFn(product)
            clearQuickSearch()
        }
    }

    // Cleanup
    onUnmounted(() => {
        formatCurrencyCache.clear()
    })

    return {
        // Refs
        quickSearchResults,
        selectedQuickSearchIndex,
        quickSearchTerm,

        // Business logic
        businessLogic,

        // Computed
        getCheckoutIcon,
        getPaymentMethodIcon,

        // Functions
        setupQuickSearch,
        setupStockUpdates,
        formatCurrency,
        navigateQuickSearch,
        clearQuickSearch,
        addFirstSearchResult
    }
}

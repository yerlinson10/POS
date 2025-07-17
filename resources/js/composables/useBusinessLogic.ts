// useBusinessLogic.ts - Composable para lógica de negocio optimizada
import { ref, computed, onUnmounted } from 'vue'
import { PriceCalculator, InventoryManager, CartManager } from '../utils/business-logic-optimized'
import type { Product } from '../types/pos'

export function useBusinessLogic() {
    // Singleton instances
    const priceCalculator = PriceCalculator.getInstance()
    const inventoryManager = new InventoryManager()
    const cartManager = new CartManager()

    // Reactive state
    const discountType = ref<'percentage' | 'fixed' | null>(null)
    const discountValue = ref(0)
    const taxRate = ref(0)

    // Computed values
    const subtotal = computed(() => {
        return priceCalculator.calculateSubtotal(cartManager.cartItems)
    })

    const discountAmount = computed(() => {
        if (!discountType.value || !discountValue.value) return 0
        return priceCalculator.calculateDiscount(subtotal.value, discountType.value, discountValue.value)
    })

    const taxAmount = computed(() => {
        const afterDiscount = subtotal.value - discountAmount.value
        return priceCalculator.calculateTax(afterDiscount, taxRate.value)
    })

    const total = computed(() => {
        return priceCalculator.calculateTotal(subtotal.value - discountAmount.value, 0) + taxAmount.value
    })

    const itemCount = computed(() => {
        return cartManager.itemCount.value
    })

    const isEmpty = computed(() => {
        return cartManager.isEmpty.value
    })

    // Cart operations
    const addToCart = (product: Product, quantity: number = 1) => {
        return cartManager.addItem(product, quantity)
    }

    const updateCartQuantity = (productId: number, quantity: number) => {
        return cartManager.updateQuantity(productId, quantity)
    }

    const removeFromCart = (productId: number) => {
        return cartManager.removeItem(productId)
    }

    const clearCart = () => {
        cartManager.clear()
        clearDiscount()
    }

    const getCartItem = (productId: number) => {
        return cartManager.getItemByProductId(productId)
    }

    const hasCartItem = (productId: number) => {
        return cartManager.hasItem(productId)
    }

    // Inventory operations
    const updateStock = (productId: number, stock: number) => {
        inventoryManager.updateStock(productId, stock)
    }

    const getAvailableStock = (productId: number) => {
        return inventoryManager.getAvailableStock(productId)
    }

    const batchUpdateStock = (updates: Array<{id: number, stock: number}>) => {
        inventoryManager.batchUpdateStock(updates)
    }

    const getStockHistory = (productId: number) => {
        return inventoryManager.getStockHistory(productId)
    }

    // Discount operations
    const applyDiscount = (type: 'percentage' | 'fixed', value: number) => {
        discountType.value = type
        discountValue.value = value
    }

    const clearDiscount = () => {
        discountType.value = null
        discountValue.value = 0
    }

    // Tax operations
    const setTaxRate = (rate: number) => {
        taxRate.value = rate
    }

    // Validation
    const validateCart = () => {
        return cartManager.validateCart()
    }

    // Summary
    const getCartSummary = () => {
        return cartManager.getCartSummary()
    }

    const getStockSummary = () => {
        return inventoryManager.getStockSummary()
    }

    // Performance monitoring
    const getCacheStats = () => {
        return {
            priceCalculatorCacheSize: priceCalculator.getCacheSize(),
            inventoryProducts: inventoryManager.getStockSummary().total
        }
    }

    // Cleanup on unmount
    onUnmounted(() => {
        priceCalculator.clearCache()
        inventoryManager.clearHistory()
    })

    return {
        // State
        discountType,
        discountValue,
        taxRate,

        // Computed
        subtotal,
        discountAmount,
        taxAmount,
        total,
        itemCount,
        isEmpty,

        // Cart operations
        addToCart,
        updateCartQuantity,
        removeFromCart,
        clearCart,
        getCartItem,
        hasCartItem,

        // Inventory operations
        updateStock,
        getAvailableStock,
        batchUpdateStock,
        getStockHistory,

        // Discount operations
        applyDiscount,
        clearDiscount,

        // Tax operations
        setTaxRate,

        // Validation
        validateCart,

        // Summary
        getCartSummary,
        getStockSummary,

        // Performance
        getCacheStats,

        // Direct access to managers (for advanced use)
        cartManager,
        inventoryManager,
        priceCalculator
    }
}

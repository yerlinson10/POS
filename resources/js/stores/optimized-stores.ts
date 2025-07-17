// optimized-stores.ts - Optimización de stores con lazy loading
import axios from 'axios'
import { defineStore } from 'pinia'
import { ref, computed, shallowRef } from 'vue'
import type { Product, CartItem, Customer, ProductFilters } from '../types/pos'

export const useOptimizedProductStore = defineStore('optimizedProducts', () => {
    // Use shallowRef for large arrays to avoid deep reactivity
    const products = shallowRef<Product[]>([])
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // Cache for frequently accessed data
    const productCache = new Map<number, Product>()

    // Debounced search
    let searchTimeout: number | null = null

    const fetchProducts = async (filters?: ProductFilters) => {
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout)
        }

        searchTimeout = setTimeout(async () => {
            isLoading.value = true
            try {
                const response = await axios.get('/pos/products', { params: filters })
                products.value = response.data.products.data

                // Update cache
                response.data.products.data.forEach((product: Product) => {
                    productCache.set(product.id, product)
                })
            } catch (err) {
                error.value = err instanceof Error ? err.message : 'Error fetching products'
            } finally {
                isLoading.value = false
            }
        }, 300)
    }

    // Optimized product getter with cache
    const getProductById = (id: number) => {
        return productCache.get(id) || products.value.find(p => p.id === id)
    }

    // Batch update for better performance
    const updateProductsStock = (updates: Array<{id: number, stock: number}>) => {
        updates.forEach(update => {
            const product = productCache.get(update.id)
            if (product) {
                product.stock = update.stock
            }
        })
    }

    return {
        products,
        isLoading,
        error,
        fetchProducts,
        getProductById,
        updateProductsStock
    }
})

export const useOptimizedPOSStore = defineStore('optimizedPOS', () => {
    // Optimized cart with better memory management
    const cart = shallowRef<CartItem[]>([])
    const selectedCustomer = ref<Customer | null>(null)

    // Cached computed values
    const cartMap = computed(() => {
        return cart.value.reduce((map, item) => {
            map.set(item.product_id, item)
            return map
        }, new Map<number, CartItem>())
    })

    const subtotal = computed(() => {
        return cart.value.reduce((sum, item) => sum + item.line_total, 0)
    })

    const addToCart = (product: Product, quantity: number = 1) => {
        const existingItem = cartMap.value.get(product.id)

        if (existingItem) {
            const newQuantity = existingItem.quantity + quantity
            if (newQuantity <= product.stock) {
                existingItem.quantity = newQuantity
                existingItem.line_total = newQuantity * existingItem.unit_price
            }
        } else {
            cart.value.push({
                product_id: product.id,
                product_name: product.name,
                product_sku: product.sku,
                quantity,
                unit_price: product.price,
                line_total: quantity * product.price,
                available_stock: product.stock
            })
        }
    }

    return {
        cart,
        selectedCustomer,
        cartMap,
        subtotal,
        addToCart
    }
})

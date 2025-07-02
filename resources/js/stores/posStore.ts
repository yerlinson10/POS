import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export interface Product {
    id: number
    sku: string
    name: string
    price: number
    stock: number
    category_id: number
    unit_measure_id: number
    category?: {
        id: number
        name: string
    }
    unitMeasure?: {
        id: number
        name: string
        code: string
    }
}

export interface Customer {
    id: number
    first_name: string
    last_name: string
    email: string
    phone?: string
}

export interface CartItem {
    product: Product
    quantity: number
    unit_price: number
    line_total: number
}

export const usePosStore = defineStore('pos', () => {
    // State
    const cart = ref<CartItem[]>([])
    const selectedCustomer = ref<Customer | null>(null)
    const isProcessingSale = ref(false)
    const products = ref<Product[]>([])
    const customers = ref<Customer[]>([])
    const searchQuery = ref('')
    const customerSearchQuery = ref('')

    // Computed
    const cartTotal = computed(() => {
        return cart.value.reduce((total, item) => total + item.line_total, 0)
    })

    const cartItemsCount = computed(() => {
        return cart.value.reduce((count, item) => count + item.quantity, 0)
    })

    const cartIsEmpty = computed(() => {
        return cart.value.length === 0
    })

    const canProcessSale = computed(() => {
        return !cartIsEmpty.value && selectedCustomer.value !== null && !isProcessingSale.value
    })

    // Actions
    const addToCart = (product: Product, quantity: number = 1) => {
        const existingItem = cart.value.find(item => item.product.id === product.id)

        if (existingItem) {
            updateQuantity(product.id, existingItem.quantity + quantity)
        } else {
            const cartItem: CartItem = {
                product,
                quantity,
                unit_price: product.price,
                line_total: product.price * quantity
            }
            cart.value.push(cartItem)
        }
    }

    const removeFromCart = (productId: number) => {
        const index = cart.value.findIndex(item => item.product.id === productId)
        if (index > -1) {
            cart.value.splice(index, 1)
        }
    }

    const updateQuantity = (productId: number, quantity: number) => {
        const item = cart.value.find(item => item.product.id === productId)
        if (item) {
            if (quantity <= 0) {
                removeFromCart(productId)
            } else {
                // Check stock availability
                if (quantity > item.product.stock) {
                    throw new Error(`Not enough stock. Available: ${item.product.stock}`)
                }
                item.quantity = quantity
                item.line_total = item.unit_price * quantity
            }
        }
    }

    const updateUnitPrice = (productId: number, unitPrice: number) => {
        const item = cart.value.find(item => item.product.id === productId)
        if (item) {
            item.unit_price = unitPrice
            item.line_total = unitPrice * item.quantity
        }
    }

    const clearCart = () => {
        cart.value = []
        selectedCustomer.value = null
    }

    const setCustomer = (customer: Customer) => {
        selectedCustomer.value = customer
    }

    const clearCustomer = () => {
        selectedCustomer.value = null
    }

    const setProducts = (productList: Product[]) => {
        products.value = productList
    }

    const setCustomers = (customerList: Customer[]) => {
        customers.value = customerList
    }

    const searchProducts = async (query: string) => {
        searchQuery.value = query
        try {
            const response = await fetch(`/pos/products/search?search=${encodeURIComponent(query)}`)
            const data = await response.json()
            products.value = data
        } catch (error) {
            console.error('Error searching products:', error)
        }
    }

    const searchCustomers = async (query: string) => {
        customerSearchQuery.value = query
        try {
            const response = await fetch(`/pos/customers/search?search=${encodeURIComponent(query)}`)
            const data = await response.json()
            customers.value = data
        } catch (error) {
            console.error('Error searching customers:', error)
        }
    }

    const processSale = async (paymentMethod: string, amountPaid: number) => {
        if (!canProcessSale.value) {
            throw new Error('Cannot process sale: invalid state')
        }

        isProcessingSale.value = true

        try {
            const saleData = {
                customer_id: selectedCustomer.value!.id,
                items: cart.value.map(item => ({
                    product_id: item.product.id,
                    quantity: item.quantity,
                    unit_price: item.unit_price
                })),
                payment_method: paymentMethod,
                amount_paid: amountPaid
            }

            const response = await fetch('/pos/sale', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(saleData)
            })

            const result = await response.json()

            if (result.success) {
                // Clear cart after successful sale
                clearCart()
                return result
            } else {
                throw new Error(result.message || 'Sale processing failed')
            }
        } catch (error) {
            console.error('Error processing sale:', error)
            throw error
        } finally {
            isProcessingSale.value = false
        }
    }

    const getProductById = (id: number) => {
        return products.value.find(product => product.id === id)
    }

    const getCustomerById = (id: number) => {
        return customers.value.find(customer => customer.id === id)
    }

    return {
        // State
        cart,
        selectedCustomer,
        isProcessingSale,
        products,
        customers,
        searchQuery,
        customerSearchQuery,

        // Computed
        cartTotal,
        cartItemsCount,
        cartIsEmpty,
        canProcessSale,

        // Actions
        addToCart,
        removeFromCart,
        updateQuantity,
        updateUnitPrice,
        clearCart,
        setCustomer,
        clearCustomer,
        setProducts,
        setCustomers,
        searchProducts,
        searchCustomers,
        processSale,
        getProductById,
        getCustomerById
    }
})

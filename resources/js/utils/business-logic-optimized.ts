// business-logic-optimized.ts - Lógica de negocio optimizada
import { computed, ref } from 'vue'
import type { CartItem, Product } from '../types/pos'

// Utilidades para cálculos optimizados con mejor rendimiento
export class PriceCalculator {
    private static instance: PriceCalculator
    private cache = new Map<string, number>()
    private maxCacheSize = 100

    static getInstance(): PriceCalculator {
        if (!PriceCalculator.instance) {
            PriceCalculator.instance = new PriceCalculator()
        }
        return PriceCalculator.instance
    }

    calculateSubtotal(items: CartItem[]): number {
        if (items.length === 0) return 0

        const cacheKey = this.generateCacheKey(items)

        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey)!
        }

        const subtotal = items.reduce((sum, item) => sum + item.line_total, 0)

        // Manage cache size
        if (this.cache.size >= this.maxCacheSize) {
            const firstKey = this.cache.keys().next().value
            if (firstKey) {
                this.cache.delete(firstKey)
            }
        }

        this.cache.set(cacheKey, subtotal)
        return subtotal
    }

    calculateDiscount(subtotal: number, type: 'percentage' | 'fixed', value: number): number {
        if (subtotal <= 0 || value <= 0) return 0

        if (type === 'percentage') {
            return Math.min((subtotal * value) / 100, subtotal)
        }
        return Math.min(value, subtotal)
    }

    calculateTotal(subtotal: number, discount: number): number {
        return Math.max(0, subtotal - discount)
    }

    calculateTax(subtotal: number, taxRate: number): number {
        return subtotal * (taxRate / 100)
    }

    private generateCacheKey(items: CartItem[]): string {
        return items
            .map(item => `${item.product_id}:${item.quantity}:${item.unit_price}`)
            .sort()
            .join('|')
    }

    clearCache(): void {
        this.cache.clear()
    }

    getCacheSize(): number {
        return this.cache.size
    }
}

// Gestor de inventario optimizado con mejor funcionalidad
export class InventoryManager {
    private productStock = new Map<number, number>()
    private reservedStock = new Map<number, number>()
    private stockHistory = new Map<number, Array<{timestamp: Date, change: number, reason: string}>>()

    updateStock(productId: number, stock: number): void {
        const previousStock = this.productStock.get(productId) || 0
        this.productStock.set(productId, stock)

        // Track stock changes
        this.addStockHistory(productId, stock - previousStock, 'Manual update')
    }

    getAvailableStock(productId: number): number {
        const total = this.productStock.get(productId) || 0
        const reserved = this.reservedStock.get(productId) || 0
        return Math.max(0, total - reserved)
    }

    getTotalStock(productId: number): number {
        return this.productStock.get(productId) || 0
    }

    getReservedStock(productId: number): number {
        return this.reservedStock.get(productId) || 0
    }

    reserveStock(productId: number, quantity: number): boolean {
        const available = this.getAvailableStock(productId)
        if (available >= quantity) {
            const current = this.reservedStock.get(productId) || 0
            this.reservedStock.set(productId, current + quantity)
            this.addStockHistory(productId, -quantity, 'Reserved')
            return true
        }
        return false
    }

    releaseStock(productId: number, quantity: number): void {
        const current = this.reservedStock.get(productId) || 0
        const released = Math.min(quantity, current)
        this.reservedStock.set(productId, Math.max(0, current - released))
        this.addStockHistory(productId, released, 'Released')
    }

    batchUpdateStock(updates: Array<{id: number, stock: number}>): void {
        updates.forEach(update => {
            this.updateStock(update.id, update.stock)
        })
    }

    private addStockHistory(productId: number, change: number, reason: string): void {
        if (!this.stockHistory.has(productId)) {
            this.stockHistory.set(productId, [])
        }

        const history = this.stockHistory.get(productId)!
        history.push({
            timestamp: new Date(),
            change,
            reason
        })

        // Keep only last 10 entries
        if (history.length > 10) {
            history.shift()
        }
    }

    getStockHistory(productId: number): Array<{timestamp: Date, change: number, reason: string}> {
        return this.stockHistory.get(productId) || []
    }

    clearHistory(): void {
        this.stockHistory.clear()
    }

    getStockSummary(): {total: number, reserved: number, available: number} {
        let total = 0
        let reserved = 0

        for (const stock of this.productStock.values()) {
            total += stock
        }

        for (const reservedAmount of this.reservedStock.values()) {
            reserved += reservedAmount
        }

        return {
            total,
            reserved,
            available: total - reserved
        }
    }
}

// Optimized cart manager with enhanced functionality
export class CartManager {
    private items = ref<CartItem[]>([])
    private calculator = PriceCalculator.getInstance()
    private inventory = new InventoryManager()
    private maxItems = 100

    get cartItems() {
        return this.items.value
    }

    get subtotal() {
        return computed(() => this.calculator.calculateSubtotal(this.items.value))
    }

    get itemCount() {
        return computed(() => this.items.value.reduce((sum, item) => sum + item.quantity, 0))
    }

    get isEmpty() {
        return computed(() => this.items.value.length === 0)
    }

    addItem(product: Product, quantity: number = 1): boolean {
        if (this.items.value.length >= this.maxItems) {
            return false
        }

        const existingIndex = this.items.value.findIndex(item => item.product_id === product.id)

        if (existingIndex !== -1) {
            const existingItem = this.items.value[existingIndex]
            const newQuantity = existingItem.quantity + quantity

            if (this.inventory.getAvailableStock(product.id) >= newQuantity) {
                existingItem.quantity = newQuantity
                existingItem.line_total = newQuantity * existingItem.unit_price
                this.calculator.clearCache()
                return true
            }
            return false
        }

        if (this.inventory.reserveStock(product.id, quantity)) {
            this.items.value.push({
                product_id: product.id,
                product_name: product.name,
                product_sku: product.sku,
                quantity,
                unit_price: product.price,
                line_total: quantity * product.price,
                available_stock: product.stock
            })
            this.calculator.clearCache()
            return true
        }

        return false
    }

    updateQuantity(productId: number, quantity: number): boolean {
        const item = this.items.value.find(item => item.product_id === productId)
        if (!item) return false

        const difference = quantity - item.quantity

        if (difference > 0) {
            if (!this.inventory.reserveStock(productId, difference)) {
                return false
            }
        } else if (difference < 0) {
            this.inventory.releaseStock(productId, Math.abs(difference))
        }

        item.quantity = quantity
        item.line_total = quantity * item.unit_price
        this.calculator.clearCache()
        return true
    }

    removeItem(productId: number): boolean {
        const index = this.items.value.findIndex(item => item.product_id === productId)
        if (index !== -1) {
            const item = this.items.value[index]
            this.inventory.releaseStock(productId, item.quantity)
            this.items.value.splice(index, 1)
            this.calculator.clearCache()
            return true
        }
        return false
    }

    clear(): void {
        this.items.value.forEach(item => {
            this.inventory.releaseStock(item.product_id, item.quantity)
        })
        this.items.value = []
        this.calculator.clearCache()
    }

    getItemByProductId(productId: number): CartItem | undefined {
        return this.items.value.find(item => item.product_id === productId)
    }

    hasItem(productId: number): boolean {
        return this.items.value.some(item => item.product_id === productId)
    }

    getCartSummary(): {
        itemCount: number,
        subtotal: number,
        uniqueItems: number,
        totalWeight: number
    } {
        const itemCount = this.itemCount.value
        const subtotal = this.subtotal.value
        const uniqueItems = this.items.value.length
        const totalWeight = this.items.value.reduce((sum, item) => sum + item.quantity, 0)

        return {
            itemCount,
            subtotal,
            uniqueItems,
            totalWeight
        }
    }

    validateCart(): {isValid: boolean, errors: string[]} {
        const errors: string[] = []

        if (this.items.value.length === 0) {
            errors.push('Cart is empty')
        }

        this.items.value.forEach(item => {
            if (item.quantity <= 0) {
                errors.push(`Invalid quantity for ${item.product_name}`)
            }

            if (item.unit_price <= 0) {
                errors.push(`Invalid price for ${item.product_name}`)
            }

            if (item.quantity > item.available_stock) {
                errors.push(`Insufficient stock for ${item.product_name}`)
            }
        })

        return {
            isValid: errors.length === 0,
            errors
        }
    }
}

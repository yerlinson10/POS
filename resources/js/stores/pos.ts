import axios from 'axios';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { CartItem, Customer, Product, Sale } from '../types/pos';

export const usePOSStore = defineStore('pos', () => {
    // State
    const cart = ref<CartItem[]>([]);
    const selectedCustomer = ref<Customer | null>(null);
    const discountType = ref<'percentage' | 'fixed' | null>(null);
    const discountValue = ref<number>(0);
    const isProcessingSale = ref(false);
    const lastSale = ref<Sale | null>(null);

    // Computed
    const subtotal = computed(() => {
        return cart.value.reduce((sum: number, item: CartItem) => sum + item.line_total, 0);
    });

    const discountAmount = computed(() => {
        if (!discountType.value || discountValue.value <= 0) return 0;

        if (discountType.value === 'percentage') {
            return (subtotal.value * discountValue.value) / 100;
        } else {
            return Math.min(discountValue.value, subtotal.value);
        }
    });

    const total = computed(() => {
        return Math.max(0, subtotal.value - discountAmount.value);
    });

    const itemCount = computed(() => {
        return cart.value.reduce((sum: number, item: CartItem) => sum + item.quantity, 0);
    });

    const canProcessSale = computed(() => {
        return cart.value.length > 0 && !isProcessingSale.value;
    });

    // Actions
    const addToCart = (product: Product, quantity: number = 1) => {
        const existingItem = cart.value.find((item: CartItem) => item.product_id === product.id);

        if (existingItem) {
            const newQuantity = existingItem.quantity + quantity;
            if (newQuantity <= product.stock) {
                existingItem.quantity = newQuantity;
                existingItem.line_total = newQuantity * product.price;
            } else {
                throw new Error(`Cannot add more items. Stock available: ${product.stock}`);
            }
        } else {
            if (quantity <= product.stock) {
                cart.value.push({
                    product_id: product.id,
                    product_name: product.name,
                    product_sku: product.sku,
                    unit_price: product.price,
                    quantity: quantity,
                    line_total: product.price * quantity,
                    available_stock: product.stock,
                });
            } else {
                throw new Error(`Cannot add ${quantity} items. Stock available: ${product.stock}`);
            }
        }
    };

    const updateCartItemQuantity = (productId: number, quantity: number) => {
        const item = cart.value.find((item: CartItem) => item.product_id === productId);
        if (item) {
            if (quantity <= 0) {
                removeFromCart(productId);
            } else if (quantity <= item.available_stock) {
                item.quantity = quantity;
                item.line_total = quantity * item.unit_price;
            } else {
                throw new Error(`Cannot set quantity to ${quantity}. Stock available: ${item.available_stock}`);
            }
        }
    };

    const removeFromCart = (productId: number) => {
        const index = cart.value.findIndex((item: CartItem) => item.product_id === productId);
        if (index !== -1) {
            cart.value.splice(index, 1);
        }
    };

    const clearCart = () => {
        cart.value = [];
    };

    const setCustomer = (customer: Customer | null) => {
        selectedCustomer.value = customer;
    };

    const setDiscount = (type: 'percentage' | 'fixed' | null, value: number = 0) => {
        discountType.value = type;
        discountValue.value = value;
    };

    const clearDiscount = () => {
        discountType.value = null;
        discountValue.value = 0;
    };

    const processSale = async (): Promise<Sale> => {
        if (!canProcessSale.value) {
            throw new Error('Cannot process sale');
        }

        isProcessingSale.value = true;

        try {
            const saleData = {
                customer_id: selectedCustomer.value?.id || null,
                items: cart.value.map((item: CartItem) => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    line_total: item.line_total,
                })),
                subtotal: subtotal.value,
                discount_type: discountType.value,
                discount_value: discountValue.value,
                discount_amount: discountAmount.value,
                total_amount: total.value,
            };

            const response = await axios.post('/pos/sales', saleData);
            const sale = response.data.invoice;

            lastSale.value = sale;

            // Clear cart and reset state
            clearCart();
            setCustomer(null);
            clearDiscount();

            return sale;
        } finally {
            isProcessingSale.value = false;
        }
    };

    const updateProductStock = (updates: Record<number, { stock: number; price: number }>) => {
        cart.value.forEach((item: CartItem) => {
            const update = updates[item.product_id];
            if (update) {
                item.available_stock = update.stock;
                // Optionally update price if needed
                // item.unit_price = update.price
                // item.line_total = item.quantity * update.price
            }
        });
    };

    return {
        // State
        cart,
        selectedCustomer,
        discountType,
        discountValue,
        isProcessingSale,
        lastSale,

        // Computed
        subtotal,
        discountAmount,
        total,
        itemCount,
        canProcessSale,

        // Actions
        addToCart,
        updateCartItemQuantity,
        removeFromCart,
        clearCart,
        setCustomer,
        setDiscount,
        clearDiscount,
        processSale,
        updateProductStock,
    };
});

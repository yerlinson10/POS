<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Add Products</DialogTitle>
                <DialogDescription>
                    Select products to add to the invoice
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- Search -->
                <div class="flex items-center space-x-2">
                    <Input
                        v-model="searchTerm"
                        placeholder="Search products..."
                        class="flex-1"
                    />
                    <Button @click="searchTerm = ''" variant="outline" size="icon">
                        <Icon name="X" class="w-4 h-4" />
                    </Button>
                </div>

                <!-- Products List -->
                <div class="grid gap-4 max-h-96 overflow-y-auto">
                    <div
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="flex items-center justify-between p-4 border rounded-lg hover:bg-accent/50 transition-colors"
                        :class="{ 'opacity-50': existingProducts.includes(product.id) }"
                    >
                        <div class="flex-1">
                            <div class="font-medium">{{ product.name }}</div>
                            <div class="text-sm text-muted-foreground">{{ product.sku }}</div>
                            <div class="text-sm font-medium text-primary mt-1">
                                ${{ Number(product.price).toFixed(2) }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Input
                                v-model.number="quantities[product.id]"
                                type="number"
                                min="1"
                                :max="product.stock"
                                class="w-20"
                                placeholder="Qty"
                            />
                            <Button
                                @click="addProduct(product)"
                                :disabled="existingProducts.includes(product.id) || !quantities[product.id]"
                                size="sm"
                            >
                                <Icon name="Plus" class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredProducts.length === 0" class="text-center py-8">
                    <p class="text-muted-foreground">No products found</p>
                </div>
            </div>

            <DialogFooter>
                <Button @click="emit('update:open', false)" variant="outline">
                    Close
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import Icon from '@/components/Icon.vue'

interface Product {
    id: number
    name: string
    sku: string
    price: number
    stock: number
    category?: {
        id: number
        name: string
    }
    unit_measure?: {
        id: number
        name: string
        abbreviation: string
    }
}

interface Props {
    open: boolean
    products: Product[]
    existingProducts: number[]
}

interface Emits {
    (e: 'update:open', value: boolean): void
    (e: 'add-product', product: Product, quantity: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const searchTerm = ref('')
const quantities = ref<Record<number, number>>({})

const filteredProducts = computed(() => {
    if (!searchTerm.value) return props.products

    const term = searchTerm.value.toLowerCase()
    return props.products.filter(product =>
        product.name.toLowerCase().includes(term) ||
        product.sku.toLowerCase().includes(term)
    )
})

const addProduct = (product: Product) => {
    const quantity = quantities.value[product.id] || 1
    emit('add-product', product, quantity)
    quantities.value[product.id] = 1
}

// Initialize quantities
watch(() => props.products, (products) => {
    products.forEach(product => {
        if (!quantities.value[product.id]) {
            quantities.value[product.id] = 1
        }
    })
}, { immediate: true })

watch(() => props.open, (newValue) => {
    if (!newValue) {
        searchTerm.value = ''
    }
})
</script>

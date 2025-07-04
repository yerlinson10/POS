<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-red-600">
                    <Icon name="AlertTriangle" class="w-5 h-5" />
                    Insufficient Stock
                </DialogTitle>
                <DialogDescription>
                    Cannot change the invoice status because some products do not have sufficient stock.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- Invoice and Customer Info -->
                <div class="bg-muted p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-muted-foreground">Invoice:</span>
                            <span class="ml-2 font-semibold">#{{ errorData?.invoice_id }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-muted-foreground">Customer:</span>
                            <span class="ml-2 font-semibold">{{ errorData?.customer }}</span>
                        </div>
                    </div>
                </div>

                <!-- Products with insufficient stock -->
                <div class="space-y-3">
                    <h4 class="font-medium text-sm">Products with insufficient stock:</h4>
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="text-left p-3 font-medium">Product</th>
                                    <th class="text-left p-3 font-medium">SKU</th>
                                    <th class="text-left p-3 font-medium">Required</th>
                                    <th class="text-left p-3 font-medium">Available</th>
                                    <th class="text-left p-3 font-medium">Missing</th>
                                    <th class="text-left p-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in errorData?.unavailable_products" :key="product.product_id"
                                    class="border-t hover:bg-muted/20">
                                    <td class="p-3">
                                        <div class="font-medium">{{ product.product_name }}</div>
                                    </td>
                                    <td class="p-3">
                                        <code
                                            class="text-xs bg-muted px-1 py-0.5 rounded">{{ product.product_sku || 'N/A' }}</code>
                                    </td>
                                    <td class="p-3">{{ product.required_quantity }}</td>
                                    <td class="p-3">
                                        <span class="text-red-600 font-medium">{{ product.available_stock }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="text-red-600 font-medium">{{ product.missing_stock }}</span>
                                    </td>
                                    <td class="p-3">
                                        <Button v-if="product.product_id" @click="goToProduct(product.product_id)"
                                            variant="outline" size="sm" class="h-7 px-2 text-xs cursor-pointer">
                                            <Icon name="ExternalLink" class="w-3 h-3 mr-1" />
                                            View
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg dark:bg-blue-950 dark:border-blue-800">
                    <div class="flex items-start gap-2">
                        <Icon name="Info" class="w-4 h-4 text-blue-600 mt-0.5" />
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <p class="font-medium">To continue, you can:</p>
                            <ul class="mt-1 space-y-1 ml-4 list-disc">
                                <li>Update the stock for the missing products</li>
                                <!-- <li>Modify the quantities in the invoice</li> -->
                                <li>Cancel the operation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="isOpen = false" class="cursor-pointer">
                    Understood
                </Button>
                <Button @click="goToProducts" variant="default">
                    View Products
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'

interface StockError {
    error: string
    message: string
    invoice_id: number
    customer: string
    unavailable_products: Array<{
        product_id: number
        product_name: string
        product_sku?: string | null
        required_quantity: number
        available_stock: number
        missing_stock: number
    }>
}

interface Props {
    open: boolean
    errorData?: StockError | null
}

interface Emits {
    (e: 'update:open', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isOpen = ref(props.open)

watch(() => props.open, (newValue) => {
    isOpen.value = newValue
})

watch(isOpen, (newValue) => {
    emit('update:open', newValue)
})

const goToProduct = (productId: number) => {
    isOpen.value = false
    router.visit(`/products/${productId}`)
}

const goToProducts = () => {
    isOpen.value = false
    router.visit('/products')
}
</script>

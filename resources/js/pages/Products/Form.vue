<template>

    <Head :title="isEdit ? 'Edit Product' : 'Create Product'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="PackagePlus" class="w-5 h-5 text-primary" />
                                {{ isEdit ? 'Edit Product' : 'Create New Product' }}
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ isEdit ? 'Update product information and inventory' : 'Add a new product to your inventory system' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Package" class="w-4 h-4" />
                            <span>Product Form</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 md:p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Info" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Basic Information</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="sku" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Hash" class="w-3 h-3" />
                                        SKU (Stock Keeping Unit)
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="sku" v-model="form.sku" required placeholder="e.g., PROD-001, ELC-TV-55"
                                        class="h-10 font-mono"
                                        :class="{ 'border-destructive focus:border-destructive': errors.sku }" />
                                    <InputError field="sku" :message="errors.sku" />
                                    <p class="text-xs text-muted-foreground">
                                        Unique identifier for inventory tracking
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium flex items-center gap-1">
                                        Product Name
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" required placeholder="Enter product name"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.name }" />
                                    <InputError field="name" :message="errors.name" />
                                </div>
                            </div>
                        </div>

                        <!-- Classification Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Tags" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Classification</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="category" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="FolderOpen" class="w-3 h-3" />
                                        Category
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Select id="category" v-model="form.category_id"
                                        :class="{ 'border-destructive': errors.category_id }">
                                        <SelectTrigger class="w-full h-10">
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="n of categories" :key="n.id" :value="n.id!">
                                                {{ n.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError field="category_id" :message="errors.category_id" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="unit" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Ruler" class="w-3 h-3" />
                                        Unit of Measurement
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Select id="unit" v-model="form.unit_measure_id"
                                        :class="{ 'border-destructive': errors.unit_measure_id }">
                                        <SelectTrigger class="w-full h-10">
                                            <SelectValue placeholder="Select a unit" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="n of unit_measures" :key="n.id" :value="n.id!">
                                                <span class="font-mono">{{ n.code }}</span> - {{ n.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError field="unit_measure_id" :message="errors.unit_measure_id" />
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Inventory Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="DollarSign" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Pricing & Inventory</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="price" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Tag" class="w-3 h-3" />
                                        Unit Price
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">$</span>
                                        <Input id="price" type="number" v-model.number="form.price" step="0.01" min="0"
                                            required placeholder="0.00" class="h-10 pl-8 price-input"
                                            :class="{ 'border-destructive focus:border-destructive': errors.price }" />
                                    </div>
                                    <InputError field="price" :message="errors.price" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="stock" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Package2" class="w-3 h-3" />
                                        Stock Quantity
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="stock" type="number" v-model.number="form.stock" min="0" required
                                        placeholder="0" class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.stock }" />
                                    <InputError field="stock" :message="errors.stock" />
                                    <p class="text-xs text-muted-foreground">
                                        Current inventory quantity
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="FileText" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Additional Details</h3>
                            </div>

                            <div class="space-y-2">
                                <Label for="description" class="text-sm font-medium">Product Description</Label>
                                <Textarea id="description" v-model="form.description"
                                    placeholder="Describe the product features, specifications, or other details..."
                                    class="min-h-[100px] resize-none"
                                    :class="{ 'border-destructive focus:border-destructive': errors.description }" />
                                <InputError field="description" :message="errors.description" />
                                <p class="text-xs text-muted-foreground">
                                    Optional: Additional information about the product
                                </p>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div v-if="form.name" class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Eye" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Product Preview</h3>
                            </div>

                            <div class="p-4 rounded-lg border bg-muted/20 preview-card">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <Icon name="Package" class="w-4 h-4 text-primary" />
                                            <span class="font-medium">{{ form.name }}</span>
                                        </div>
                                        <span v-if="form.price" class="font-semibold text-lg text-primary">
                                            ${{ Number(form.price).toFixed(2) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <span v-if="form.sku" class="font-mono bg-muted px-1.5 py-0.5 rounded">{{
                                            form.sku }}</span>
                                        <span v-if="form.stock !== undefined">Stock: {{ form.stock }}</span>
                                    </div>

                                    <p v-if="form.description" class="text-sm text-muted-foreground mt-2">
                                        {{ form.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-muted">
                            <Button type="submit" :disabled="form.processing || !form.name || !form.sku"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer">
                                <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin" />
                                <Icon v-else name="Save" class="w-4 h-4" />
                                {{ form.processing ? 'Saving...' : 'Save Product' }}
                            </Button>

                            <Button as="a" variant="outline" :href="route('products.index')"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer">
                                <Icon name="X" class="w-4 h-4" />
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">

import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { type BreadcrumbItem } from '@/types';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'


interface DataItem {
    id?: number
    name: string
}


interface ProductForm extends DataItem {
    sku: string
    description?: string
    category_id: number | null
    unit_measure_id: number | null
    price: number
    stock: number
}


interface UnitMeasure extends DataItem {
    code: string
}

// interface Category extends DataItem {
//     // Additional properties can be added here if needed
// }

const props = defineProps<{
    product: ProductForm | null
    categories: DataItem[]
    unit_measures: UnitMeasure[]
    errors: Record<string, string>
}>();

const isEdit = Boolean(props.product)

const form = useForm<ProductForm>({
    id: props.product?.id,
    sku: props.product?.sku || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    category_id: props.product?.category_id || null,
    unit_measure_id: props.product?.unit_measure_id || null,
    price: props.product?.price || 0,
    stock: props.product?.stock || 0,
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
    {
        title: isEdit ? 'Edit Product' : 'Create Product',
        href: isEdit ? `/products/${props.product?.id}/edit` : '/products/create',
    },
];

function submit() {
    if (isEdit) {
        form.put(`/products/${form.id}`)
    } else {
        form.post('/products')
    }
}
</script>

<style scoped>
/* Custom breakpoint para pantallas extra pequeñas */
@media (min-width: 480px) {
    .xs\:flex-row {
        flex-direction: row;
    }

    .xs\:items-center {
        align-items: center;
    }

    .xs\:gap-0 {
        gap: 0;
    }

    .xs\:block {
        display: block;
    }
}

/* Mejorar transiciones y hover effects */
.group:hover .group-hover\:text-primary {
    color: hsl(var(--primary));
}

/* Efecto focus mejorado para inputs */
input:focus,
textarea:focus,
select:focus {
    box-shadow: 0 0 0 3px hsl(var(--primary) / 0.1);
}

/* Loading state para botones */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Price input styling */
.price-input {
    padding-left: 2rem;
}

/* Preview card animation */
.preview-card {
    transition: all 0.2s ease-in-out;
}

.preview-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Section headers */
.section-header {
    font-weight: 600;
    color: hsl(var(--foreground));
}

/* Form validation states */
.input-error {
    border-color: hsl(var(--destructive));
}

.input-error:focus {
    border-color: hsl(var(--destructive));
    box-shadow: 0 0 0 3px hsl(var(--destructive) / 0.1);
}
</style>

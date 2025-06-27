<template>

    <Head :title="isEdit ? 'Edit Product' : 'Create Product'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-4">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="sku">SKU</Label>
                            <Input id="sku" v-model="form.sku" />
                            <InputError field="sku" :message="errors.sku" />
                        </div>
                        <div>
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                            <InputError field="name" :message="errors.name" />
                        </div>
                        <div>
                            <Label for="category">Category</Label>
                            <Select id="category" v-model="form.category_id" >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a category" />
                                </SelectTrigger>
                                <SelectContent >
                                    <SelectItem v-for="n of categories" :key="n.id" :value="n.id">
                                        {{ n.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError field="category_id" :message="errors.category_id" />
                        </div>
                        <div>
                            <Label for="unit">Unit</Label>
                            <Select id="unit" v-model="form.unit_measure_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a unit" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="n of unit_measures" :key="n.id" :value="n.id">
                                        {{ n.code }} - {{ n.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError field="unit_measure_id" :message="errors.unit_measure_id" />
                        </div>
                        <div>
                            <Label for="price">Price</Label>
                            <Input id="price" type="number" v-model.number="form.price" step="0.01" />
                            <InputError field="price" :message="errors.price" />
                        </div>
                        <div>
                            <Label for="stock">Stock</Label>
                            <Input id="stock" type="number" v-model.number="form.stock" />
                            <InputError field="stock" :message="errors.stock" />
                        </div>
                        <div class="md:col-span-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" />
                            <InputError field="description" :message="errors.description" />
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col md:flex-row md:space-x-2 space-y-2 md:space-y-0">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                        <Button as="a" variant="ghost" :href="route('products.index')">Cancel</Button>
                    </div>
                </form>
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


interface DataItem {
    id: number
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

interface Category extends DataItem {
    // Additional properties can be added here if needed
}

const props = defineProps<{
    product: ProductForm | null
    categories: Category[]
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

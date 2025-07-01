<template>

    <Head :title="isEdit ? 'Edit Category' : 'Create Category'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-4">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                            <InputError field="name" :message="errors.name" />
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
                        <Button as="a" variant="ghost" :href="route('categories.index')">Cancel</Button>
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
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'




interface CategoryForm {
    id: number
    name: string,
    description: string
}


const props = defineProps<{
    category: CategoryForm | null
    errors: Record<string, string>
}>();

const isEdit = Boolean(props.category)

const form = useForm<CategoryForm>({
    id: props.category?.id,
    name: props.category?.name || '',
    description: props.category?.description || '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: '/categories',
    },
    {
        title: isEdit ? 'Edit Category' : 'Create Category',
        href: isEdit ? `/categories/${props.category?.id}/edit` : '/categories/create',
    },
];

function submit() {
    if (isEdit) {
        form.put(`/categories/${form.id}`)
    } else {
        form.post('/categories')
    }
}
</script>

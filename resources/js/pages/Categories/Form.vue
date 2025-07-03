<template>

    <Head :title="isEdit ? 'Edit Category' : 'Create Category'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="FolderPlus" class="w-5 h-5 text-primary" />
                                {{ isEdit ? 'Edit Category' : 'Create New Category' }}
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ isEdit ? 'Update category information' : 'Add a new category to organize your products' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="FolderOpen" class="w-4 h-4" />
                            <span>Category Form</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 md:p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Category Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Tag" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Category Information</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium flex items-center gap-1">
                                        Category Name
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        required
                                        placeholder="Enter category name (e.g., Electronics, Food, Clothing)"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.name }"
                                    />
                                    <InputError field="name" :message="errors.name" />
                                    <p class="text-xs text-muted-foreground">
                                        Choose a clear, descriptive name for easy product organization
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="description" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="FileText" class="w-3 h-3" />
                                        Description
                                    </Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Describe what products belong to this category..."
                                        class="min-h-[100px] resize-none"
                                        :class="{ 'border-destructive focus:border-destructive': errors.description }"
                                    />
                                    <InputError field="description" :message="errors.description" />
                                    <p class="text-xs text-muted-foreground">
                                        Optional: Add details to help identify what products belong here
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div v-if="form.name" class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Eye" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Preview</h3>
                            </div>

                            <div class="p-4 rounded-lg border bg-muted/20 preview-card">
                                <div class="flex items-center gap-2">
                                    <Icon name="FolderOpen" class="w-4 h-4 text-primary" />
                                    <span class="font-medium">{{ form.name }}</span>
                                </div>
                                <p v-if="form.description" class="text-sm text-muted-foreground mt-1">
                                    {{ form.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-muted">
                            <Button
                                type="submit"
                                :disabled="form.processing || !form.name"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer"
                            >
                                <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin" />
                                <Icon v-else name="Save" class="w-4 h-4" />
                                {{ form.processing ? 'Saving...' : 'Save Category' }}
                            </Button>

                            <Button
                                as="a"
                                variant="outline"
                                :href="route('categories.index')"
                                class="flex items-center gap-2 h-10 px-6"
                            >
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
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'




interface CategoryForm {
    id?: number
    name: string,
    description: string
    [key: string]: any
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

/* Preview card animation */
.preview-card {
    transition: all 0.2s ease-in-out;
}

.preview-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>

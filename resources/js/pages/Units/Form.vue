<template>

    <Head :title="isEdit ? 'Edit Units Measurement' : 'Create Units Measurement'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="Ruler" class="w-5 h-5 text-primary" />
                                {{ isEdit ? 'Edit Unit Measurement' : 'Create New Unit Measurement' }}
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ isEdit ? 'Update unit measurement information' : 'Add a new unit of measurement for products' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Scale" class="w-4 h-4" />
                            <span>Unit Form</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 md:p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Unit Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Hash" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Unit Information</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium flex items-center gap-1">
                                        Unit Name
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" required
                                        placeholder="e.g., Kilogram, Meter, Piece" class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.name }" />
                                    <InputError field="name" :message="errors.name" />
                                    <p class="text-xs text-muted-foreground">
                                        Full name of the unit of measurement
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium flex items-center gap-1">
                                        Unit Code
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="code" v-model="form.code" required placeholder="e.g., kg, m, pcs"
                                        class="h-10 font-mono"
                                        :class="{ 'border-destructive focus:border-destructive': errors.code }" />
                                    <InputError field="code" :message="errors.code" />
                                    <p class="text-xs text-muted-foreground">
                                        Short abbreviation used in displays and reports
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Examples Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Lightbulb" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Common Examples</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="p-3 rounded-lg border bg-muted/20 text-center example-card">
                                    <div class="font-mono text-sm font-semibold text-primary">kg</div>
                                    <div class="text-xs text-muted-foreground">Kilogram</div>
                                </div>
                                <div class="p-3 rounded-lg border bg-muted/20 text-center example-card">
                                    <div class="font-mono text-sm font-semibold text-primary">pcs</div>
                                    <div class="text-xs text-muted-foreground">Pieces</div>
                                </div>
                                <div class="p-3 rounded-lg border bg-muted/20 text-center example-card">
                                    <div class="font-mono text-sm font-semibold text-primary">L</div>
                                    <div class="text-xs text-muted-foreground">Liter</div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div v-if="form.name && form.code" class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Eye" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Preview</h3>
                            </div>

                            <div class="p-4 rounded-lg border bg-muted/20">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-mono bg-primary/10 text-primary px-2 py-1 rounded-md text-sm font-medium">
                                            {{ form.code }}
                                        </span>
                                        <span class="font-medium">{{ form.name }}</span>
                                    </div>
                                    <Icon name="Ruler" class="w-4 h-4 text-muted-foreground" />
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-muted">
                            <Button type="submit" :disabled="form.processing || !form.name || !form.code"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer">
                                <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin" />
                                <Icon v-else name="Save" class="w-4 h-4" />
                                {{ form.processing ? 'Saving...' : 'Save Unit' }}
                            </Button>

                            <Button as="a" variant="outline" :href="route('unit-measures.index')"
                                class="flex items-center gap-2 h-10 px-6">
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
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'




interface UnitForm {
    id?: number
    name: string,
    code: string
    [key: string]: any
}


const props = defineProps<{
    unit: UnitForm | null
    errors: Record<string, string>
}>();

const isEdit = Boolean(props.unit)

const form = useForm<UnitForm>({
    id: props.unit?.id,
    name: props.unit?.name || '',
    code: props.unit?.code || '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Units Measurement',
        href: '/unit-measures',
    },
    {
        title: isEdit ? 'Edit Unit Measurement' : 'Create Units Measurement',
        href: isEdit ? `/unit-measures/${props.unit?.id}/edit` : '/unit-measures/create',
    },
];

function submit() {
    if (isEdit) {
        form.put(`/unit-measures/${form.id}`)
    } else {
        form.post('/unit-measures')
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

/* Example cards animation */
.example-card {
    transition: all 0.2s ease-in-out;
}

.example-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Code input styling */
.font-mono {
    letter-spacing: 0.05em;
}
</style>

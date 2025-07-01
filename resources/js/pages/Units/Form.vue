<template>

    <Head :title="isEdit ? 'Edit Units Measurement' : 'Create Units Measurement'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-4">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                            <InputError field="name" :message="errors.name" />
                        </div>
                        <div>
                            <Label for="code">Code</Label>
                            <Input id="code" v-model="form.code" />
                            <InputError field="code" :message="errors.code" />
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col md:flex-row md:space-x-2 space-y-2 md:space-y-0">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                        <Button as="a" variant="ghost" :href="route('unit-measures.index')">Cancel</Button>
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
import InputError from '@/components/InputError.vue'




interface UnitForm {
    id: number
    name: string,
    code: string
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

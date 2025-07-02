<template>

    <Head :title="isEdit ? 'Edit Customer' : 'Create Customer'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-4">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="first_name">First Name</Label>
                            <Input id="first_name" v-model="form.first_name" required="true" />
                            <InputError field="first_name" :message="errors.first_name" />
                        </div>
                        <div>
                            <Label for="last_name">Last Name</Label>
                            <Input id="last_name" v-model="form.last_name" />
                            <InputError field="last_name" :message="errors.last_name" />
                        </div>
                        <div>
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" />
                            <InputError field="email" :message="errors.email" />
                        </div>
                        <div>
                            <Label for="phone">Phone</Label>
                            <Input type="tel" id="phone" v-model="form.phone" />
                            <InputError field="phone" :message="errors.phone" />
                        </div>
                        <div class="md:col-span-2">
                            <Label for="address">Address</Label>
                            <Textarea id="address" v-model="form.address" />
                            <InputError field="address" :message="errors.address" />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col md:flex-row md:space-x-2 space-y-2 md:space-y-0">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                        <Button as="a" variant="ghost" :href="route('customers.index')">Cancel</Button>
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




interface CustomerForm {
    id: number
    first_name: string,
    last_name: string,
    email?: string,
    phone?: string,
    address?: string,
}


const props = defineProps<{
    customer: CustomerForm | null
    errors: Record<string, string>
}>();

const isEdit = Boolean(props.customer)

const form = useForm<CustomerForm>({
    id: props.customer?.id,
    first_name: props.customer?.first_name || '',
    last_name: props.customer?.last_name || '',
    email: props.customer?.email || '',
    phone: props.customer?.phone || '',
    address: props.customer?.address || '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Customers',
        href: '/customers',
    },
    {
        title: isEdit ? 'Edit Customer' : 'Create Customer',
        href: isEdit ? `/customers/${props.customer?.id}/edit` : '/customers/create',
    },
];

function submit() {
    if (isEdit) {
        form.put(`/customers/${form.id}`)
    } else {
        form.post('/customers')
    }
}
</script>

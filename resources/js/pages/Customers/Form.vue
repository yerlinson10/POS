<template>

    <Head :title="isEdit ? 'Edit Customer' : 'Create Customer'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="UserPlus" class="w-5 h-5 text-primary" />
                                {{ isEdit ? 'Edit Customer' : 'Create New Customer' }}
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ isEdit ? 'Update customer information' : 'Add a new customer to your database' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Users" class="w-4 h-4" />
                            <span>Customer Form</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 md:p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Personal Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="User" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Personal Information</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="first_name" class="text-sm font-medium flex items-center gap-1">
                                        First Name
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input
                                        id="first_name"
                                        v-model="form.first_name"
                                        required
                                        placeholder="Enter first name"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.first_name }"
                                    />
                                    <InputError field="first_name" :message="errors.first_name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="last_name" class="text-sm font-medium">Last Name</Label>
                                    <Input
                                        id="last_name"
                                        v-model="form.last_name"
                                        placeholder="Enter last name"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.last_name }"
                                    />
                                    <InputError field="last_name" :message="errors.last_name" />
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Contact" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Contact Information</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="email" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Mail" class="w-3 h-3" />
                                        Email Address
                                    </Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="customer@example.com"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.email }"
                                    />
                                    <InputError field="email" :message="errors.email" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Phone" class="w-3 h-3" />
                                        Phone Number
                                    </Label>
                                    <Input
                                        type="tel"
                                        id="phone"
                                        v-model="form.phone"
                                        placeholder="+1 (555) 123-4567"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.phone }"
                                    />
                                    <InputError field="phone" :message="errors.phone" />
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="MapPin" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Address Information</h3>
                            </div>

                            <div class="space-y-2">
                                <Label for="address" class="text-sm font-medium">Full Address</Label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Enter complete address..."
                                    class="min-h-[80px] resize-none"
                                    :class="{ 'border-destructive focus:border-destructive': errors.address }"
                                />
                                <InputError field="address" :message="errors.address" />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-muted">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer"
                            >
                                <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin" />
                                <Icon v-else name="Save" class="w-4 h-4" />
                                {{ form.processing ? 'Saving...' : 'Save Customer' }}
                            </Button>

                            <Button
                                as="a"
                                variant="outline"
                                :href="route('customers.index')"
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




interface CustomerForm {
    id?: number
    first_name: string,
    last_name: string,
    email?: string,
    phone?: string,
    address?: string,
    [key: string]: any
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
</style>

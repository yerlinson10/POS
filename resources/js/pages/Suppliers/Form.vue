<template>
    <Head :title="isEdit ? 'Edit Supplier' : 'Create Supplier'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="Building2" class="w-5 h-5 text-primary" />
                                {{ isEdit ? 'Edit Supplier' : 'Create New Supplier' }}
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ isEdit ? 'Update supplier information and details' : 'Add a new supplier to your system' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Building2" class="w-4 h-4" />
                            <span>Supplier Form</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 md:p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Company Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Building" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Company Information</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="company_name" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Building2" class="w-3 h-3" />
                                        Company Name
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="company_name" v-model="form.company_name" required 
                                        placeholder="Enter company name"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.company_name }" />
                                    <InputError field="company_name" :message="errors.company_name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="tax_id" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Hash" class="w-3 h-3" />
                                        Tax ID / RUC
                                    </Label>
                                    <Input id="tax_id" v-model="form.tax_id" 
                                        placeholder="Enter tax identification number"
                                        class="h-10 font-mono"
                                        :class="{ 'border-destructive focus:border-destructive': errors.tax_id }" />
                                    <InputError field="tax_id" :message="errors.tax_id" />
                                    <p class="text-xs text-muted-foreground">
                                        Optional: Tax identification number
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="User" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Contact Information</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="contact_name" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="UserCheck" class="w-3 h-3" />
                                        Contact Name
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="contact_name" v-model="form.contact_name" required 
                                        placeholder="Enter contact person name"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.contact_name }" />
                                    <InputError field="contact_name" :message="errors.contact_name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Mail" class="w-3 h-3" />
                                        Email Address
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input id="email" type="email" v-model="form.email" required 
                                        placeholder="Enter email address"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.email }" />
                                    <InputError field="email" :message="errors.email" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="phone" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Phone" class="w-3 h-3" />
                                        Phone Number
                                    </Label>
                                    <Input id="phone" v-model="form.phone" 
                                        placeholder="Enter phone number"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.phone }" />
                                    <InputError field="phone" :message="errors.phone" />
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="MapPin" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Address Information</h3>
                            </div>

                            <div class="space-y-2">
                                <Label for="address" class="text-sm font-medium">Address</Label>
                                <Textarea id="address" v-model="form.address"
                                    placeholder="Enter complete address..."
                                    class="min-h-[80px] resize-none"
                                    :class="{ 'border-destructive focus:border-destructive': errors.address }" />
                                <InputError field="address" :message="errors.address" />
                                <p class="text-xs text-muted-foreground">
                                    Optional: Complete address for the supplier
                                </p>
                            </div>
                        </div>

                        <!-- Additional Notes Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="FileText" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Additional Information</h3>
                            </div>

                            <div class="space-y-2">
                                <Label for="notes" class="text-sm font-medium">Notes</Label>
                                <Textarea id="notes" v-model="form.notes"
                                    placeholder="Add any additional notes about this supplier..."
                                    class="min-h-[100px] resize-none"
                                    :class="{ 'border-destructive focus:border-destructive': errors.notes }" />
                                <InputError field="notes" :message="errors.notes" />
                                <p class="text-xs text-muted-foreground">
                                    Optional: Additional information or special notes
                                </p>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div v-if="form.company_name || form.contact_name" class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Eye" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Supplier Preview</h3>
                            </div>

                            <div class="p-4 rounded-lg border bg-muted/20 preview-card">
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center gap-2">
                                        <Icon name="Building2" class="w-5 h-5 text-primary" />
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-lg">{{ form.company_name || 'Company Name' }}</span>
                                            <span v-if="form.tax_id" class="text-sm text-muted-foreground font-mono">{{ form.tax_id }}</span>
                                        </div>
                                    </div>

                                    <div v-if="form.contact_name" class="flex items-center gap-2 text-sm">
                                        <Icon name="User" class="w-4 h-4 text-muted-foreground" />
                                        <span>{{ form.contact_name }}</span>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <div v-if="form.email" class="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Icon name="Mail" class="w-4 h-4" />
                                            <span>{{ form.email }}</span>
                                        </div>
                                        <div v-if="form.phone" class="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Icon name="Phone" class="w-4 h-4" />
                                            <span>{{ form.phone }}</span>
                                        </div>
                                    </div>

                                    <div v-if="form.address" class="flex items-start gap-2 text-sm text-muted-foreground">
                                        <Icon name="MapPin" class="w-4 h-4 mt-0.5" />
                                        <span>{{ form.address }}</span>
                                    </div>

                                    <div v-if="form.notes" class="p-3 bg-muted rounded-md border-l-2 border-primary">
                                        <p class="text-sm text-muted-foreground">
                                            <Icon name="StickyNote" class="w-4 h-4 inline mr-1" />
                                            {{ form.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-muted">
                            <Button type="submit" :disabled="form.processing || !form.company_name || !form.contact_name || !form.email"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer">
                                <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin" />
                                <Icon v-else name="Save" class="w-4 h-4" />
                                {{ form.processing ? 'Saving...' : 'Save Supplier' }}
                            </Button>

                            <Button as="a" variant="outline" :href="route('suppliers.index')"
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
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'
import { type BreadcrumbItem } from '@/types'

interface SupplierForm {
    id?: number
    company_name: string
    contact_name: string
    email: string
    phone?: string
    tax_id?: string
    address?: string
    notes?: string
}

const props = defineProps<{
    supplier: SupplierForm | null
    errors: Record<string, string>
}>()

const isEdit = Boolean(props.supplier)

const form = useForm<SupplierForm>({
    id: props.supplier?.id,
    company_name: props.supplier?.company_name || '',
    contact_name: props.supplier?.contact_name || '',
    email: props.supplier?.email || '',
    phone: props.supplier?.phone || '',
    tax_id: props.supplier?.tax_id || '',
    address: props.supplier?.address || '',
    notes: props.supplier?.notes || '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Suppliers',
        href: '/suppliers',
    },
    {
        title: isEdit ? 'Edit Supplier' : 'Create Supplier',
        href: isEdit ? `/suppliers/${props.supplier?.id}/edit` : '/suppliers/create',
    },
]

function submit() {
    if (isEdit) {
        form.put(`/suppliers/${form.id}`)
    } else {
        form.post('/suppliers')
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

/* Form validation states */
.input-error {
    border-color: hsl(var(--destructive));
}

.input-error:focus {
    border-color: hsl(var(--destructive));
    box-shadow: 0 0 0 3px hsl(var(--destructive) / 0.1);
}
</style>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>New Customer</DialogTitle>
                <DialogDescription>
                    Create a new customer account
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="first_name">First Name *</Label>
                        <Input id="first_name" v-model="form.first_name" placeholder="Enter first name" required
                            :disabled="isCreating" />
                        <div v-if="errors.first_name" class="text-sm text-red-500">
                            {{ errors.first_name }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="last_name">Last Name *</Label>
                        <Input id="last_name" v-model="form.last_name" placeholder="Enter last name" required
                            :disabled="isCreating" />
                        <div v-if="errors.last_name" class="text-sm text-red-500">
                            {{ errors.last_name }}
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" placeholder="Enter email address"
                        :disabled="isCreating" />
                    <div v-if="errors.email" class="text-sm text-red-500">
                        {{ errors.email }}
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="phone">Phone</Label>
                    <Input id="phone" v-model="form.phone" placeholder="Enter phone number" :disabled="isCreating" />
                    <div v-if="errors.phone" class="text-sm text-red-500">
                        {{ errors.phone }}
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="address">Address</Label>
                    <Textarea id="address" v-model="form.address" placeholder="Enter address" rows="3"
                        :disabled="isCreating" />
                    <div v-if="errors.address" class="text-sm text-red-500">
                        {{ errors.address }}
                    </div>
                </div>

                <div v-if="error" class="text-sm text-red-500">
                    {{ error }}
                </div>

                <DialogFooter>
                    <Button type="button" @click="closeDialog" variant="outline" :disabled="isCreating">
                        Cancel
                    </Button>
                    <Button type="submit" :loading="isCreating" :disabled="!canSubmit">
                        <Icon name="UserPlus" class="w-4 h-4 mr-2" />
                        {{ isCreating ? 'Creating...' : 'Create Customer' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive } from 'vue'
import { storeToRefs } from 'pinia'
import { useCustomerStore } from '../../../stores/customers'
import type { Customer, CustomerCreateRequest } from '../../../types/pos'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '../../../components/ui/dialog'
import { Input } from '../../../components/ui/input'
import { Textarea } from '../../../components/ui/textarea'
import { Button } from '../../../components/ui/button'
import { Label } from '../../../components/ui/label'
import Icon from '../../../components/Icon.vue'
import { toast } from 'vue-sonner'

interface Props {
    open: boolean
}

interface Emits {
    (e: 'update:open', value: boolean): void
    (e: 'customer-created', customer: Customer): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Store
const customerStore = useCustomerStore()
const { isCreating, error } = storeToRefs(customerStore)

// Local state
const isOpen = ref(props.open)
const form = reactive<CustomerCreateRequest>({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: ''
})

const errors = reactive<Record<string, string>>({})

// Computed
const canSubmit = computed(() => {
    return form.first_name.trim() && form.last_name.trim() && !isCreating.value
})

// Methods
const validateForm = (): boolean => {
    // Clear previous errors
    Object.keys(errors).forEach(key => {
        errors[key] = ''
    })

    let isValid = true

    if (!form.first_name.trim()) {
        errors.first_name = 'First name is required'
        isValid = false
    }

    if (!form.last_name.trim()) {
        errors.last_name = 'Last name is required'
        isValid = false
    }

    if (form.email && !isValidEmail(form.email)) {
        errors.email = 'Please enter a valid email address'
        isValid = false
    }

    return isValid
}

const isValidEmail = (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
}

const handleSubmit = async () => {
    if (!validateForm()) {
        return
    }

    try {
        const customer = await customerStore.createCustomer({
            ...form,
            email: (form.email ?? '').trim() || undefined,
            phone: (form.phone ?? '').trim() || undefined,
            address: (form.address ?? '').trim() || undefined
        })

        emit('customer-created', customer)
        closeDialog()
        resetForm()
        toast.success('Customer created successfully!')
    } catch (err) {
        // Error is handled by the store
        console.error('Error creating customer:', err)
    }
}

const resetForm = () => {
    form.first_name = ''
    form.last_name = ''
    form.email = ''
    form.phone = ''
    form.address = ''

    Object.keys(errors).forEach(key => {
        errors[key] = ''
    })

    customerStore.clearError()
}

const closeDialog = () => {
    isOpen.value = false
    emit('update:open', false)
}

// Watch for prop changes
watch(() => props.open, (newValue) => {
    isOpen.value = newValue
    if (newValue) {
        resetForm()
    }
})

watch(isOpen, (newValue) => {
    if (!newValue) {
        emit('update:open', false)
        resetForm()
    }
})
</script>

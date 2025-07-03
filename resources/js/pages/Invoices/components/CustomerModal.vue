<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Add Customer</DialogTitle>
                <DialogDescription>
                    Create a new customer
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="createCustomer" class="space-y-4">
                <div>
                    <Label for="name">Name *</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Enter customer name"
                        class="mt-1"
                    />
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Enter email address"
                        class="mt-1"
                    />
                </div>

                <div>
                    <Label for="phone">Phone</Label>
                    <Input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="Enter phone number"
                        class="mt-1"
                    />
                </div>

                <div>
                    <Label for="address">Address</Label>
                    <Input
                        id="address"
                        v-model="form.address"
                        type="text"
                        placeholder="Enter address"
                        class="mt-1"
                    />
                </div>

                <DialogFooter>
                    <Button @click="emit('update:open', false)" variant="outline" type="button">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="!form.name || isLoading">
                        {{ isLoading ? 'Creating...' : 'Create Customer' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { toast } from 'vue-sonner'

interface Customer {
    id: number
    name: string
    email?: string
    phone?: string
    address?: string
}

interface Props {
    open: boolean
}

interface Emits {
    (e: 'update:open', value: boolean): void
    (e: 'customer-created', customer: Customer): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: ''
})

const isLoading = ref(false)

const createCustomer = async () => {
    if (!form.value.name) {
        toast.error('Customer name is required')
        return
    }

    isLoading.value = true

    try {
        const response = await fetch(route('customers.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                first_name: form.value.name.split(' ')[0] || '',
                last_name: form.value.name.split(' ').slice(1).join(' ') || '',
                email: form.value.email || null,
                phone: form.value.phone || null,
                address: form.value.address || null,
            })
        })

        if (!response.ok) {
            throw new Error('Network response was not ok')
        }

        const data = await response.json()
        emit('customer-created', data.customer)
        emit('update:open', false)
        toast.success('Customer created successfully')
        resetForm()
    } catch (error) {
        console.error('Error creating customer:', error)
        toast.error('Error creating customer')
    } finally {
        isLoading.value = false
    }
}

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        phone: '',
        address: ''
    }
}

watch(() => props.open, (newValue) => {
    if (!newValue) {
        resetForm()
    }
})
</script>

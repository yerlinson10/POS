<template>
    <Head title="User Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold">User Details</h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                View user information and permissions
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button as="a" :href="`/users/${user.id}/edit`" size="sm" class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Icon name="Edit" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Edit User
                            </Button>
                            <Button as="a" href="/users" variant="outline" size="sm" class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Icon name="ArrowLeft" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Back to Users
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- User Information -->
                <div class="p-6 space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-card rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <Icon name="User" class="w-5 h-5 mr-2" />
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Full Name</label>
                                <div class="mt-1 text-base font-medium">{{ user.name }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Email</label>
                                <div class="mt-1 text-base">{{ user.email }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Created At</label>
                                <div class="mt-1 text-base">{{ formatDate(user.created_at) }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Email Verified</label>
                                <div class="mt-1">
                                    <span v-if="user.email_verified_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                        <Icon name="CheckCircle" class="w-3 h-3 mr-1" />
                                        Verified
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                        <Icon name="AlertCircle" class="w-3 h-3 mr-1" />
                                        Not Verified
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles Card -->
                    <div class="bg-card rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <Icon name="Shield" class="w-5 h-5 mr-2" />
                            Assigned Roles
                        </h3>
                        <div v-if="user.roles && user.roles.length > 0" class="flex flex-wrap gap-2">
                            <span v-for="role in user.roles" :key="role.id"
                                class="inline-flex items-center px-3 py-1.5 rounded-md bg-primary/10 text-primary text-sm font-medium border border-primary/20">
                                <Icon name="Shield" class="w-3 h-3 mr-1" />
                                {{ role.name }}
                            </span>
                        </div>
                        <div v-else class="text-muted-foreground text-sm">
                            No roles assigned
                        </div>
                    </div>

                    <!-- Permissions Card -->
                    <div class="bg-card rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <Icon name="Key" class="w-5 h-5 mr-2" />
                            Effective Permissions
                            <span class="ml-2 text-sm font-normal text-muted-foreground">({{ totalPermissions }} permissions)</span>
                        </h3>

                        <div v-if="groupedPermissions && Object.keys(groupedPermissions).length > 0" class="space-y-4">
                            <div v-for="(modulePermissions, module) in groupedPermissions" :key="module" class="border rounded-lg p-4">
                                <h4 class="font-medium text-sm mb-3 flex items-center capitalize">
                                    <Icon name="Package" class="w-4 h-4 mr-2" />
                                    {{ module }}
                                    <span class="ml-2 text-xs text-muted-foreground">({{ modulePermissions.length }} permissions)</span>
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="permission in modulePermissions" :key="permission"
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-secondary text-secondary-foreground">
                                        {{ permission.split(':')[1] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground text-sm">
                            No permissions assigned
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'
import type { BreadcrumbItem } from '@/types'

interface User {
    id: number
    name: string
    email: string
    email_verified_at?: string
    created_at: string
    roles?: Array<{
        id: number
        name: string
    }>
    permissions?: string[]
}

const props = defineProps<{
    user: User
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: '/users' },
    { title: props.user.name, href: `/users/${props.user.id}` },
]

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const groupedPermissions = computed(() => {
    if (!props.user.permissions) return {}

    const grouped: Record<string, string[]> = {}

    props.user.permissions.forEach(permission => {
        const [module] = permission.split(':')
        if (!grouped[module]) {
            grouped[module] = []
        }
        grouped[module].push(permission)
    })

    return grouped
})

const totalPermissions = computed(() => {
    return props.user.permissions?.length || 0
})
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
</style>

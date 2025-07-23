<template>
    <Head title="Role Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-2 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold">Role Details</h2>
                            <p class="text-xs md:text-sm text-muted-foreground hidden xs:block">
                                View role information and permissions
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button as="a" :href="`/roles/${role.id}/edit`" size="sm" class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Icon name="Edit" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Edit Role
                            </Button>
                            <Button as="a" href="/roles" variant="outline" size="sm" class="h-8 md:h-11 px-2 md:px-6 text-xs md:text-sm">
                                <Icon name="ArrowLeft" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Back to Roles
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Role Information -->
                <div class="p-6 space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-card rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <Icon name="Shield" class="w-5 h-5 mr-2" />
                            Role Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Role Name</label>
                                <div class="mt-1 text-base font-medium">{{ role.name }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Guard Name</label>
                                <div class="mt-1 text-base">{{ role.guard_name }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Created At</label>
                                <div class="mt-1 text-base">{{ formatDate(role.created_at) }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Users Count</label>
                                <div class="mt-1 text-base font-medium">{{ role.users_count || 0 }} users</div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Card -->
                    <div class="bg-card rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <Icon name="Key" class="w-5 h-5 mr-2" />
                            Role Permissions
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
                                    <span v-for="permission in modulePermissions" :key="permission.name"
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                        {{ permission.name.split(':')[1] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground text-sm">
                            No permissions assigned to this role
                        </div>
                    </div>

                    <!-- Users with this Role -->
                    <div v-if="role.users && role.users.length > 0" class="bg-card rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <Icon name="Users" class="w-5 h-5 mr-2" />
                            Users with this Role
                            <span class="ml-2 text-sm font-normal text-muted-foreground">({{ role.users.length }} users)</span>
                        </h3>

                        <div class="space-y-3">
                            <div v-for="user in role.users" :key="user.id"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-muted/50 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                                        <Icon name="User" class="w-4 h-4 text-primary" />
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ user.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ user.email }}</div>
                                    </div>
                                </div>
                                <Button as="a" :href="`/users/${user.id}`" variant="outline" size="sm">
                                    <Icon name="Eye" class="w-3 h-3 mr-1" />
                                    View
                                </Button>
                            </div>
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

interface Permission {
    id: number
    name: string
    guard_name: string
}

interface User {
    id: number
    name: string
    email: string
}

interface Role {
    id: number
    name: string
    guard_name: string
    created_at: string
    users_count?: number
    permissions?: Permission[]
    users?: User[]
}

const props = defineProps<{
    role: Role
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/roles' },
    { title: props.role.name, href: `/roles/${props.role.id}` },
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
    if (!props.role.permissions) return {}

    const grouped: Record<string, Permission[]> = {}

    props.role.permissions.forEach(permission => {
        const [module] = permission.name.split(':')
        if (!grouped[module]) {
            grouped[module] = []
        }
        grouped[module].push(permission)
    })

    return grouped
})

const totalPermissions = computed(() => {
    return props.role.permissions?.length || 0
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

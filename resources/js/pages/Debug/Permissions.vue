<template>
    <Head title="Debug Permissions" />
    <AppLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Debug Permissions</h1>

            <div class="grid gap-6">
                <!-- User Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">User Information</h2>
                    <pre class="bg-gray-100 p-4 rounded">{{ JSON.stringify(user_info, null, 2) }}</pre>
                </div>

                <!-- Roles -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Roles ({{ roles.length }})</h2>
                    <pre class="bg-gray-100 p-4 rounded">{{ JSON.stringify(roles, null, 2) }}</pre>
                    <p class="mt-2">Has Super Admin: {{ has_super_admin ? 'Yes' : 'No' }}</p>
                </div>

                <!-- Permissions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Permissions ({{ permission_count }})</h2>
                    <div class="max-h-96 overflow-y-auto">
                        <pre class="bg-gray-100 p-4 rounded">{{ JSON.stringify(permissions, null, 2) }}</pre>
                    </div>
                </div>

                <!-- Frontend Data -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Frontend Auth Data</h2>
                    <pre class="bg-gray-100 p-4 rounded">{{ JSON.stringify($page.props.auth, null, 2) }}</pre>
                </div>

                <!-- Composable Test -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Composable Tests</h2>
                    <div class="space-y-2">
                        <p>Has 'users:view': {{ hasPermission('users:view') ? 'Yes' : 'No' }}</p>
                        <p>Has 'products:create': {{ hasPermission('products:create') ? 'Yes' : 'No' }}</p>
                        <p>Has 'products:view': {{ hasPermission('products:view') ? 'Yes' : 'No' }}</p>
                        <p>Has 'categories:view': {{ hasPermission('categories:view') ? 'Yes' : 'No' }}</p>
                        <p>Is Super Admin: {{ isSuperAdmin() ? 'Yes' : 'No' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { usePermissions } from '@/composables/usePermissions'

const { hasPermission, isSuperAdmin } = usePermissions()

defineProps<{
    user_info: {
        id: number
        name: string
        email: string
    }
    roles: string[]
    permissions: string[]
    has_super_admin: boolean
    permission_count: number
    middleware_data: Record<string, any>
}>()
</script>

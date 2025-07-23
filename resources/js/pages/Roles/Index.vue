<template>
    <Head title="Gestión de Roles" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="Shield" class="w-5 h-5 text-primary" />
                                Gestión de Roles
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                Administra roles y permisos del sistema
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Settings" class="w-4 h-4" />
                            <span>Control de Acceso</span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 md:p-6">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gradient-to-r from-blue-500/10 to-blue-600/10 border border-blue-200/50 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-500/10 flex items-center justify-center">
                                    <Icon name="Shield" class="w-5 h-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-blue-600">{{ stats.total_roles }}</p>
                                    <p class="text-xs text-muted-foreground">Total Roles</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-500/10 to-green-600/10 border border-green-200/50 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-500/10 flex items-center justify-center">
                                    <Icon name="Settings" class="w-5 h-5 text-green-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-green-600">{{ stats.total_permissions }}</p>
                                    <p class="text-xs text-muted-foreground">Total Permisos</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-purple-500/10 to-purple-600/10 border border-purple-200/50 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-500/10 flex items-center justify-center">
                                    <Icon name="Users" class="w-5 h-5 text-purple-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-purple-600">{{ stats.users_with_roles }}</p>
                                    <p class="text-xs text-muted-foreground">Usuarios con Roles</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-orange-500/10 to-orange-600/10 border border-orange-200/50 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-500/10 flex items-center justify-center">
                                    <Icon name="UserX" class="w-5 h-5 text-orange-600" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-orange-600">{{ stats.users_without_roles }}</p>
                                    <p class="text-xs text-muted-foreground">Sin Roles</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions and Filters -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="relative flex-1 sm:flex-none sm:w-64">
                            <Icon name="Search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                            <Input
                                v-model="searchTerm"
                                placeholder="Buscar roles..."
                                class="pl-10 h-9"
                                @input="search"
                            />
                        </div>

                        <Button @click="$inertia.visit(route('roles.create'))" class="w-full sm:w-auto cursor-pointer">
                            <Icon name="Plus" class="w-4 h-4 mr-2" />
                            Nuevo Rol
                        </Button>
                    </div>

                    <!-- Roles Table -->
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-muted/50 border-b">
                                <tr>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm">Rol</th>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm hidden md:table-cell">Permisos</th>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm hidden lg:table-cell">Usuarios</th>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm hidden lg:table-cell">Creado</th>
                                    <th class="text-center p-3 md:p-4 font-medium text-sm w-24">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="role in roles.data" :key="role.id" class="border-b hover:bg-muted/30 transition-colors">
                                    <td class="p-3 md:p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                <Icon name="Shield" class="w-4 h-4 text-primary" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-sm">{{ role.name }}</div>
                                                <div class="text-xs text-muted-foreground md:hidden">
                                                    {{ role.permissions_count }} permisos, {{ role.users_count }} usuarios
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 md:p-4 text-sm text-muted-foreground hidden md:table-cell">
                                        {{ role.permissions_count }} permisos
                                    </td>
                                    <td class="p-3 md:p-4 text-sm text-muted-foreground hidden lg:table-cell">
                                        {{ role.users_count }} usuarios
                                    </td>
                                    <td class="p-3 md:p-4 text-sm text-muted-foreground hidden lg:table-cell">
                                        {{ formatDate(role.created_at) }}
                                    </td>
                                    <td class="p-3 md:p-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button
                                                @click="$inertia.visit(route('roles.show', role.id))"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 cursor-pointer"
                                                title="Ver detalles"
                                            >
                                                <Icon name="Eye" class="w-4 h-4" />
                                            </Button>

                                            <Button
                                                @click="$inertia.visit(route('roles.edit', role.id))"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 cursor-pointer"
                                                title="Editar rol"
                                            >
                                                <Icon name="Edit" class="w-4 h-4" />
                                            </Button>

                                            <AlertDialog v-if="!['Super Admin', 'Admin'].includes(role.name)">
                                                <AlertDialogTrigger as-child>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="h-8 w-8 p-0 text-red-600 hover:text-red-700 cursor-pointer"
                                                        title="Eliminar rol"
                                                    >
                                                        <Icon name="Trash2" class="w-4 h-4" />
                                                    </Button>
                                                </AlertDialogTrigger>
                                                <AlertDialogContent>
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>¿Eliminar rol?</AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            Esta acción no se puede deshacer. Se eliminará permanentemente el rol "{{ role.name }}" y se removerá de todos los usuarios que lo tengan asignado.
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter>
                                                        <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                                        <AlertDialogAction
                                                            @click="destroy(role.id)"
                                                            class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700"
                                                        >
                                                            Eliminar Rol
                                                        </AlertDialogAction>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>

                                            <div v-else class="w-8 h-8 flex items-center justify-center">
                                                <Icon name="Lock" class="w-4 h-4 text-muted-foreground" title="Rol del sistema protegido" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="roles.data.length === 0" class="text-center py-12">
                            <Icon name="Shield" class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold text-muted-foreground mb-2">No hay roles</h3>
                            <p class="text-sm text-muted-foreground mb-4">
                                {{ searchTerm ? 'No se encontraron roles con ese criterio de búsqueda.' : 'Comienza creando tu primer rol personalizado.' }}
                            </p>
                            <Button v-if="!searchTerm" @click="$inertia.visit(route('roles.create'))" class="cursor-pointer">
                                <Icon name="Plus" class="w-4 h-4 mr-2" />
                                Crear Rol
                            </Button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="roles.data.length > 0" class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                        <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                            <div class="text-xs text-muted-foreground">
                                <span class="font-medium">{{ roles.from || 0 }}-{{ roles.to || 0 }}</span>
                                <span class="hidden xs:inline"> of </span>
                                <span class="xs:hidden">/</span>
                                <span class="font-medium">{{ roles.total || 0 }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Page {{ roles.current_page || 1 }}/{{ roles.last_page || 1 }}
                            </div>
                        </div>

                        <Pagination v-slot="{ page: internalPage }" :items-per-page="perPage" :total="roles.last_page || 1" :page="currentPage" @page-change="onPageChange" class="flex">
                            <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                                <PaginationPrevious @click="onPageChange(internalPage - 1)" :disabled="(roles.current_page || 1) <= 1" class="h-8 md:h-9" />
                                <template v-for="(item, idx) in pages" :key="idx">
                                    <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === internalPage" @click="onPageChange(item.value)" class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                        {{ item.value }}
                                    </PaginationItem>
                                </template>
                                <PaginationEllipsis :index="4" v-if="(roles.last_page || 1) >= 4" class="h-8 md:h-9" />
                                <PaginationNext @click="onPageChange(internalPage + 1)" :disabled="(roles.current_page || 1) >= (roles.last_page || 1)" class="h-8 md:h-9" />
                            </PaginationContent>
                        </Pagination>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import Icon from '@/components/Icon.vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/roles' },
]

interface Role {
    id: number
    name: string
    permissions_count: number
    users_count: number
    created_at: string
}

interface Stats {
    total_roles: number
    total_permissions: number
    users_with_roles: number
    users_without_roles: number
}

interface Props {
    roles: {
        data: Role[]
        from: number
        to: number
        total: number
        current_page: number
        last_page: number
        per_page: number
        links: Array<{
            url: string | null
            label: string
            active: boolean
        }>
    }
    filters: Record<string, any>
    stats: Stats
}

const props = defineProps<Props>()

// State
const searchTerm = ref(props.filters.search || '')

// Computed
const currentPage = computed(() => props.roles.current_page || 1)
const perPage = computed(() => props.roles.per_page || 15)

// Methods
const search = () => {
    const filters: Record<string, any> = {}

    if (searchTerm.value) {
        filters.search = searchTerm.value
    }

    router.get(route('roles.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    })
}

const onPageChange = (page: number) => {
    const filters: Record<string, any> = { page }

    if (searchTerm.value) {
        filters.search = searchTerm.value
    }

    router.get(route('roles.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    })
}

const destroy = (id: number) => {
    router.delete(route('roles.destroy', id), {
        preserveScroll: true,
    })
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>

<style scoped>
/* Custom styles if needed */
</style>

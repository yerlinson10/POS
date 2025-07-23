<template>
    <Head title="Gestión de Usuarios" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="Users" class="w-5 h-5 text-primary" />
                                Gestión de Usuarios
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                Administra usuarios, roles y permisos del sistema
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Shield" class="w-4 h-4" />
                            <span>Sistema de Roles</span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 md:p-6">
                    <!-- Actions and Filters -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 w-full sm:w-auto">
                            <!-- Search -->
                            <div class="relative flex-1 sm:flex-none sm:w-64">
                                <Icon name="Search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                <Input
                                    v-model="searchTerm"
                                    placeholder="Buscar usuarios..."
                                    class="pl-10 h-9"
                                    @input="search"
                                />
                            </div>

                            <!-- Role Filter -->
                            <Select v-model="selectedRole" @update:model-value="applyFilters">
                                <SelectTrigger class="w-full sm:w-48 h-9">
                                    <SelectValue placeholder="Filtrar por rol" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in roles" :key="value" :value="value">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <Button @click="$inertia.visit(route('users.create'))" class="w-full sm:w-auto cursor-pointer">
                            <Icon name="Plus" class="w-4 h-4 mr-2" />
                            Nuevo Usuario
                        </Button>
                    </div>

                    <!-- Users Table -->
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-muted/50 border-b">
                                <tr>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm">Usuario</th>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm hidden md:table-cell">Email</th>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm">Roles</th>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm hidden lg:table-cell">Permisos</th>
                                    <th class="text-left p-3 md:p-4 font-medium text-sm hidden lg:table-cell">Creado</th>
                                    <th class="text-center p-3 md:p-4 font-medium text-sm w-24">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-muted/30 transition-colors">
                                    <td class="p-3 md:p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                <Icon name="User" class="w-4 h-4 text-primary" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-sm">{{ user.name }}</div>
                                                <div class="text-xs text-muted-foreground md:hidden">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 md:p-4 text-sm text-muted-foreground hidden md:table-cell">
                                        {{ user.email }}
                                    </td>
                                    <td class="p-3 md:p-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role"
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-primary/10 text-primary border border-primary/20"
                                            >
                                                {{ role }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="p-3 md:p-4 text-sm hidden lg:table-cell">
                                        <span class="text-muted-foreground">{{ user.permissions_count }} permisos</span>
                                    </td>
                                    <td class="p-3 md:p-4 text-sm text-muted-foreground hidden lg:table-cell">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="p-3 md:p-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button
                                                @click="$inertia.visit(route('users.show', user.id))"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 cursor-pointer"
                                                title="Ver detalles"
                                            >
                                                <Icon name="Eye" class="w-4 h-4" />
                                            </Button>

                                            <Button
                                                @click="$inertia.visit(route('users.edit', user.id))"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 cursor-pointer"
                                                title="Editar usuario"
                                            >
                                                <Icon name="Edit" class="w-4 h-4" />
                                            </Button>

                                            <AlertDialog>
                                                <AlertDialogTrigger as-child>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="h-8 w-8 p-0 text-red-600 hover:text-red-700 cursor-pointer"
                                                        title="Eliminar usuario"
                                                    >
                                                        <Icon name="Trash2" class="w-4 h-4" />
                                                    </Button>
                                                </AlertDialogTrigger>
                                                <AlertDialogContent>
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>¿Eliminar usuario?</AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            Esta acción no se puede deshacer. Se eliminará permanentemente la cuenta de "{{ user.name }}" y todos sus datos asociados.
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter>
                                                        <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                                        <AlertDialogAction
                                                            @click="destroy(user.id)"
                                                            class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700"
                                                        >
                                                            Eliminar Usuario
                                                        </AlertDialogAction>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="users.data.length === 0" class="text-center py-12">
                            <Icon name="Users" class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold text-muted-foreground mb-2">No hay usuarios</h3>
                            <p class="text-sm text-muted-foreground mb-4">
                                {{ searchTerm ? 'No se encontraron usuarios con ese criterio de búsqueda.' : 'Comienza creando tu primer usuario.' }}
                            </p>
                            <Button v-if="!searchTerm" @click="$inertia.visit(route('users.create'))" class="cursor-pointer">
                                <Icon name="Plus" class="w-4 h-4 mr-2" />
                                Crear Usuario
                            </Button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.data.length > 0" class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-4 p-2 md:p-4 border-t bg-muted/20">
                        <div class="flex flex-col xs:flex-row xs:items-center gap-1 xs:gap-4">
                            <div class="text-xs text-muted-foreground">
                                <span class="font-medium">{{ users.from || 0 }}-{{ users.to || 0 }}</span>
                                <span class="hidden xs:inline"> of </span>
                                <span class="xs:hidden">/</span>
                                <span class="font-medium">{{ users.total || 0 }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Page {{ users.current_page || 1 }}/{{ users.last_page || 1 }}
                            </div>
                        </div>

                        <Pagination v-slot="{ page: internalPage }" :items-per-page="perPage" :total="users.last_page || 1" :page="currentPage" @page-change="onPageChange" class="flex">
                            <PaginationContent v-slot="{ items: pages }" class="justify-center sm:justify-end">
                                <PaginationPrevious @click="onPageChange(internalPage - 1)" :disabled="(users.current_page || 1) <= 1" class="h-8 md:h-9" />
                                <template v-for="(item, idx) in pages" :key="idx">
                                    <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === internalPage" @click="onPageChange(item.value)" class="h-8 md:h-9 w-8 md:w-9 text-xs md:text-sm">
                                        {{ item.value }}
                                    </PaginationItem>
                                </template>
                                <PaginationEllipsis :index="4" v-if="(users.last_page || 1) >= 4" class="h-8 md:h-9" />
                                <PaginationNext @click="onPageChange(internalPage + 1)" :disabled="(users.current_page || 1) >= (users.last_page || 1)" class="h-8 md:h-9" />
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
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
    { title: 'Usuarios', href: '/users' },
]

interface User {
    id: number
    name: string
    email: string
    roles: string[]
    permissions_count: number
    created_at: string
}

interface Props {
    users: {
        data: User[]
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
    roles: Record<string, string>
}

const props = defineProps<Props>()

// State
const searchTerm = ref(props.filters.search || '')
const selectedRole = ref(props.filters.role || '')

// Computed
const currentPage = computed(() => props.users.current_page || 1)
const perPage = computed(() => props.users.per_page || 15)

// Methods
const search = () => {
    applyFilters()
}

const applyFilters = () => {
    const filters: Record<string, any> = {}

    if (searchTerm.value) {
        filters.search = searchTerm.value
    }

    if (selectedRole.value) {
        filters.role = selectedRole.value
    }

    router.get(route('users.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    })
}

const onPageChange = (page: number) => {
    const filters: Record<string, any> = { page }

    if (searchTerm.value) {
        filters.search = searchTerm.value
    }

    if (selectedRole.value) {
        filters.role = selectedRole.value
    }

    router.get(route('users.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    })
}

const destroy = (id: number) => {
    router.delete(route('users.destroy', id), {
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

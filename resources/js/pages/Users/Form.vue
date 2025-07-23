<template>
    <Head :title="isEdit ? 'Editar Usuario' : 'Crear Usuario'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="UserPlus" class="w-5 h-5 text-primary" />
                                {{ isEdit ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ isEdit ? 'Actualiza la información del usuario y sus permisos' : 'Agrega un nuevo usuario al sistema con roles y permisos específicos' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Shield" class="w-4 h-4" />
                            <span>Gestión de Acceso</span>
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
                                <h3 class="text-sm font-semibold text-foreground">Información Personal</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="User" class="w-3 h-3" />
                                        Nombre Completo
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        required
                                        placeholder="Ingrese el nombre completo"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.name }"
                                    />
                                    <InputError field="name" :message="errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Mail" class="w-3 h-3" />
                                        Correo Electrónico
                                        <span class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        placeholder="usuario@ejemplo.com"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.email }"
                                    />
                                    <InputError field="email" :message="errors.email" />
                                </div>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Lock" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">
                                    {{ isEdit ? 'Cambiar Contraseña (Opcional)' : 'Contraseña' }}
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="password" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="Key" class="w-3 h-3" />
                                        Contraseña
                                        <span v-if="!isEdit" class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        :required="!isEdit"
                                        placeholder="Mínimo 8 caracteres"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.password }"
                                    />
                                    <InputError field="password" :message="errors.password" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="password_confirmation" class="text-sm font-medium flex items-center gap-1">
                                        <Icon name="KeyRound" class="w-3 h-3" />
                                        Confirmar Contraseña
                                        <span v-if="!isEdit" class="text-destructive text-xs">*</span>
                                    </Label>
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        :required="!isEdit"
                                        placeholder="Repite la contraseña"
                                        class="h-10"
                                        :class="{ 'border-destructive focus:border-destructive': errors.password_confirmation }"
                                    />
                                    <InputError field="password_confirmation" :message="errors.password_confirmation" />
                                </div>
                            </div>
                        </div>

                        <!-- Roles Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Shield" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Roles</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="flex items-center space-x-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors"
                                >
                                    <input
                                        :id="`role-${role.id}`"
                                        v-model="form.roles"
                                        :value="role.name"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <label
                                        :for="`role-${role.id}`"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                                    >
                                        {{ role.name }}
                                    </label>
                                </div>
                            </div>
                            <InputError field="roles" :message="errors.roles" />
                        </div>

                        <!-- Direct Permissions Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Settings" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Permisos del Usuario</h3>
                                <span class="text-xs text-muted-foreground">(Incluye permisos del rol + permisos específicos)</span>
                            </div>

                            <div v-if="selectedRolePermissions.length > 0" class="bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <Icon name="Info" class="w-4 h-4 text-blue-600" />
                                    <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Permisos heredados del rol</span>
                                </div>
                                <p class="text-xs text-blue-600 dark:text-blue-300">Los permisos marcados en azul provienen de los roles asignados y no se pueden desmarcar.</p>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="(modulePermissions, module) in permissions"
                                    :key="module"
                                    class="border rounded-lg p-4"
                                >
                                    <h4 class="font-medium text-sm mb-3 capitalize flex items-center gap-2">
                                        <Icon name="Package" class="w-4 h-4" />
                                        {{ module.replace('-', ' ') }}
                                        <span class="text-xs text-muted-foreground">({{ getModulePermissionCounts(module).total }} permisos, {{ getModulePermissionCounts(module).inherited }} del rol)</span>
                                    </h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                                        <div
                                            v-for="permission in modulePermissions"
                                            :key="permission.id"
                                            class="flex items-center space-x-2 p-2 rounded border transition-colors"
                                            :class="{
                                                'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800': isPermissionFromRole(permission.name),
                                                'bg-background hover:bg-muted/50': !isPermissionFromRole(permission.name)
                                            }"
                                        >
                                            <input
                                                :id="`permission-${permission.id}`"
                                                v-model="form.permissions"
                                                :value="permission.name"
                                                type="checkbox"
                                                :disabled="isPermissionFromRole(permission.name)"
                                                class="rounded border-gray-300 text-primary focus:ring-primary text-xs"
                                                :class="{
                                                    'text-blue-600 border-blue-300': isPermissionFromRole(permission.name)
                                                }"
                                            />
                                            <label
                                                :for="`permission-${permission.id}`"
                                                class="text-xs leading-none cursor-pointer capitalize flex items-center gap-1"
                                                :class="{
                                                    'text-blue-700 dark:text-blue-300 font-medium': isPermissionFromRole(permission.name),
                                                    'peer-disabled:cursor-not-allowed peer-disabled:opacity-70': isPermissionFromRole(permission.name)
                                                }"
                                            >
                                                {{ permission.action }}
                                                <Icon v-if="isPermissionFromRole(permission.name)" name="Shield" class="w-3 h-3 text-blue-600" />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <InputError field="permissions" :message="errors.permissions" />
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
                                {{ form.processing ? 'Guardando...' : 'Guardar Usuario' }}
                            </Button>

                            <Button
                                as="a"
                                variant="outline"
                                :href="route('users.index')"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer"
                            >
                                <Icon name="X" class="w-4 h-4" />
                                Cancelar
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { type BreadcrumbItem } from '@/types'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'

interface UserForm {
    id?: number
    name: string
    email: string
    password?: string
    password_confirmation?: string
    roles: string[]
    permissions: string[]
    [key: string]: any
}

interface Role {
    id: number
    name: string
    permissions?: Permission[]
}

interface Permission {
    id: number
    name: string
    action: string
}

interface Props {
    user: UserForm | null
    roles: Role[]
    permissions: Record<string, Permission[]>
    errors: Record<string, string>
}

const props = defineProps<Props>()

const isEdit = Boolean(props.user)

const form = useForm<UserForm>({
    id: props.user?.id,
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles || [],
    permissions: props.user?.permissions || [],
})

// Computed para obtener permisos de los roles seleccionados
const selectedRolePermissions = computed(() => {
    const rolePermissions: string[] = []

    props.roles.forEach(role => {
        if (form.roles.includes(role.name) && role.permissions) {
            role.permissions.forEach(permission => {
                if (!rolePermissions.includes(permission.name)) {
                    rolePermissions.push(permission.name)
                }
            })
        }
    })

    return rolePermissions
})

// Función para verificar si un permiso viene del rol
const isPermissionFromRole = (permissionName: string): boolean => {
    return selectedRolePermissions.value.includes(permissionName)
}

// Función para obtener conteos de permisos por módulo
const getModulePermissionCounts = (module: string) => {
    const modulePermissions = props.permissions[module] || []
    const total = modulePermissions.length
    const inherited = modulePermissions.filter(p => isPermissionFromRole(p.name)).length

    return { total, inherited }
}

// Watcher para agregar automáticamente permisos del rol
watch(
    () => form.roles,
    (newRoles) => {
        // Mantener solo permisos que no vienen de roles
        const directPermissions = form.permissions.filter(p => !selectedRolePermissions.value.includes(p))

        // Agregar permisos de los nuevos roles
        const allRolePermissions: string[] = []
        props.roles.forEach(role => {
            if (newRoles.includes(role.name) && role.permissions) {
                role.permissions.forEach(permission => {
                    if (!allRolePermissions.includes(permission.name)) {
                        allRolePermissions.push(permission.name)
                    }
                })
            }
        })

        // Combinar permisos directos con permisos de roles
        form.permissions = [...new Set([...directPermissions, ...allRolePermissions])]
    },
    { deep: true }
)

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: '/users' },
    {
        title: isEdit ? 'Editar Usuario' : 'Crear Usuario',
        href: isEdit ? `/users/${props.user?.id}/edit` : '/users/create',
    },
]

function submit() {
    if (isEdit) {
        form.put(`/users/${form.id}`)
    } else {
        form.post('/users')
    }
}
</script>

<style scoped>
/* Custom styles if needed */
</style>

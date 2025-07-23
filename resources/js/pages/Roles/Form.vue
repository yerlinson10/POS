<template>
    <Head :title="isEdit ? 'Editar Rol' : 'Crear Rol'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-0">

                <!-- Header -->
                <div class="flex flex-col gap-2 md:gap-4 p-4 md:p-6 border-b bg-muted/30">
                    <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 xs:gap-0">
                        <div>
                            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2">
                                <Icon name="ShieldPlus" class="w-5 h-5 text-primary" />
                                {{ isEdit ? 'Editar Rol' : 'Crear Nuevo Rol' }}
                            </h2>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ isEdit ? 'Actualiza el nombre del rol y sus permisos' : 'Define un nuevo rol con permisos específicos para el sistema' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs md:text-sm text-muted-foreground">
                            <Icon name="Settings" class="w-4 h-4" />
                            <span>Control de Acceso</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 md:p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Tag" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Información del Rol</h3>
                            </div>

                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-medium flex items-center gap-1">
                                    <Icon name="Shield" class="w-3 h-3" />
                                    Nombre del Rol
                                    <span class="text-destructive text-xs">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    required
                                    placeholder="Ej: Cajero, Supervisor, Gerente"
                                    class="h-10 max-w-md"
                                    :class="{ 'border-destructive focus:border-destructive': errors.name }"
                                />
                                <InputError field="name" :message="errors.name" />
                                <p class="text-xs text-muted-foreground">
                                    Usa un nombre descriptivo y único para el rol
                                </p>
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Settings" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Permisos del Rol</h3>
                            </div>

                            <!-- Select All Controls -->
                            <div class="flex flex-wrap gap-2 p-3 bg-muted/30 rounded-lg">
                                <Button
                                    type="button"
                                    @click="selectAllPermissions"
                                    variant="outline"
                                    size="sm"
                                    class="cursor-pointer"
                                >
                                    <Icon name="CheckSquare" class="w-4 h-4 mr-1" />
                                    Seleccionar Todo
                                </Button>
                                <Button
                                    type="button"
                                    @click="clearAllPermissions"
                                    variant="outline"
                                    size="sm"
                                    class="cursor-pointer"
                                >
                                    <Icon name="Square" class="w-4 h-4 mr-1" />
                                    Limpiar Todo
                                </Button>
                                <span class="text-sm text-muted-foreground ml-auto">
                                    {{ form.permissions.length }} permisos seleccionados
                                </span>
                            </div>

                            <!-- Permissions by Module -->
                            <div class="space-y-4">
                                <div
                                    v-for="(modulePermissions, module) in permissions"
                                    :key="module"
                                    class="border rounded-lg overflow-hidden"
                                >
                                    <!-- Module Header -->
                                    <div class="bg-muted/50 p-4 border-b">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <Icon :name="getModuleIcon(module)" class="w-5 h-5 text-primary" />
                                                <h4 class="font-medium text-sm capitalize">
                                                    {{ getModuleName(module) }}
                                                </h4>
                                                <span class="text-xs text-muted-foreground">
                                                    {{ modulePermissions.length }} permisos
                                                </span>
                                            </div>
                                            <div class="flex gap-2">
                                                <Button
                                                    type="button"
                                                    @click="selectModulePermissions(module)"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="cursor-pointer text-xs"
                                                >
                                                    Seleccionar Todo
                                                </Button>
                                                <Button
                                                    type="button"
                                                    @click="clearModulePermissions(module)"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="cursor-pointer text-xs"
                                                >
                                                    Limpiar
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Permissions Grid -->
                                    <div class="p-4">
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                                            <div
                                                v-for="permission in modulePermissions"
                                                :key="permission.id"
                                                class="flex items-center space-x-2 p-2 rounded border hover:bg-muted/30 transition-colors"
                                            >
                                                <input
                                                    :id="`permission-${permission.id}`"
                                                    v-model="form.permissions"
                                                    :value="permission.name"
                                                    type="checkbox"
                                                    class="rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                                <label
                                                    :for="`permission-${permission.id}`"
                                                    class="text-xs leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer capitalize flex-1"
                                                >
                                                    {{ permission.action }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <InputError field="permissions" :message="errors.permissions" />
                        </div>

                        <!-- Preview Section -->
                        <div v-if="form.name && form.permissions.length > 0" class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-muted">
                                <Icon name="Eye" class="w-4 h-4 text-primary" />
                                <h3 class="text-sm font-semibold text-foreground">Vista Previa del Rol</h3>
                            </div>

                            <div class="p-4 rounded-lg border bg-muted/20">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <Icon name="Shield" class="w-5 h-5 text-primary" />
                                    </div>
                                    <div>
                                        <h4 class="font-medium">{{ form.name }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ form.permissions.length }} permisos asignados</p>
                                    </div>
                                </div>

                                <div class="text-xs text-muted-foreground">
                                    <strong>Permisos incluidos:</strong>
                                    {{ form.permissions.slice(0, 5).join(', ') }}
                                    <span v-if="form.permissions.length > 5">
                                        y {{ form.permissions.length - 5 }} más...
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-muted">
                            <Button
                                type="submit"
                                :disabled="form.processing || !form.name"
                                class="flex items-center gap-2 h-10 px-6 cursor-pointer"
                            >
                                <Icon v-if="form.processing" name="Loader2" class="w-4 h-4 animate-spin" />
                                <Icon v-else name="Save" class="w-4 h-4" />
                                {{ form.processing ? 'Guardando...' : 'Guardar Rol' }}
                            </Button>

                            <Button
                                as="a"
                                variant="outline"
                                :href="route('roles.index')"
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
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { type BreadcrumbItem } from '@/types'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'

interface RoleForm {
    id?: number
    name: string
    permissions: string[]
    [key: string]: any
}

interface Permission {
    id: number
    name: string
    action: string
}

interface Props {
    role: RoleForm | null
    permissions: Record<string, Permission[]>
    errors: Record<string, string>
}

const props = defineProps<Props>()

const isEdit = Boolean(props.role)

const form = useForm<RoleForm>({
    id: props.role?.id,
    name: props.role?.name || '',
    permissions: props.role?.permissions || [],
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/roles' },
    {
        title: isEdit ? 'Editar Rol' : 'Crear Rol',
        href: isEdit ? `/roles/${props.role?.id}/edit` : '/roles/create',
    },
]

// Methods
function submit() {
    if (isEdit) {
        form.put(`/roles/${form.id}`)
    } else {
        form.post('/roles')
    }
}

function selectAllPermissions() {
    const allPermissions: string[] = []
    Object.values(props.permissions).forEach(modulePermissions => {
        modulePermissions.forEach(permission => {
            allPermissions.push(permission.name)
        })
    })
    form.permissions = allPermissions
}

function clearAllPermissions() {
    form.permissions = []
}

function selectModulePermissions(module: string) {
    const modulePermissions = props.permissions[module] || []
    const permissionNames = modulePermissions.map(p => p.name)

    // Add permissions that aren't already selected
    permissionNames.forEach(permission => {
        if (!form.permissions.includes(permission)) {
            form.permissions.push(permission)
        }
    })
}

function clearModulePermissions(module: string) {
    const modulePermissions = props.permissions[module] || []
    const permissionNames = modulePermissions.map(p => p.name)

    form.permissions = form.permissions.filter(permission =>
        !permissionNames.includes(permission)
    )
}

function getModuleIcon(module: string): string {
    const icons: Record<string, string> = {
        'dashboard': 'BarChart3',
        'products': 'Package',
        'categories': 'FolderOpen',
        'unit-measures': 'Ruler',
        'customers': 'Users',
        'invoices': 'FileText',
        'pos': 'CreditCard',
        'pos-sessions': 'Clock',
        'reports': 'PieChart',
        'settings': 'Settings',
        'users': 'User',
        'roles': 'Shield',
        'permissions': 'Key',
    }
    return icons[module] || 'Settings'
}

function getModuleName(module: string): string {
    const names: Record<string, string> = {
        'dashboard': 'Dashboard',
        'products': 'Productos',
        'categories': 'Categorías',
        'unit-measures': 'Unidades de Medida',
        'customers': 'Clientes',
        'invoices': 'Facturas',
        'pos': 'Punto de Venta',
        'pos-sessions': 'Sesiones POS',
        'reports': 'Reportes',
        'settings': 'Configuración',
        'users': 'Usuarios',
        'roles': 'Roles',
        'permissions': 'Permisos',
    }
    return names[module] || module.replace('-', ' ')
}
</script>

<style scoped>
/* Custom styles if needed */
</style>

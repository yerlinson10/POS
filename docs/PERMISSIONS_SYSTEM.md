# Sistema de Permisos y Roles

Este documento describe la implementación completa del sistema de permisos y roles en el POS.

## 🔧 Implementación Completada

### Backend
- ✅ **Spatie Laravel Permission** instalado y configurado
- ✅ **Seeder** con roles y permisos predefinidos
- ✅ **Middleware** de permisos aplicado a rutas
- ✅ **Controladores** para CRUD de usuarios y roles
- ✅ **Servicios** para lógica de negocio
- ✅ **Políticas** de autorización

### Frontend
- ✅ **Composable `usePermissions`** para verificación de permisos
- ✅ **Filtrado de menú** basado en permisos
- ✅ **Páginas de gestión** de usuarios y roles
- ✅ **Verificaciones en componentes** (botones, acciones)

## 🚀 Cómo Usar

### 1. En el Menú (Sidebar)
El menú se filtra automáticamente. Solo aparecen los módulos a los que el usuario tiene acceso.

```typescript
// navItems.ts
{
    title: 'Products',
    href: '/products',
    icon: PackageIcon,
    permission: 'products.view', // ← Se filtra automáticamente
}
```

### 2. En Componentes Vue
```vue
<script setup>
import { usePermissions } from '@/composables/usePermissions'

const { hasPermission } = usePermissions()
</script>

<template>
    <!-- Botón solo se muestra si tiene permiso -->
    <Button v-if="hasPermission('users.create')" @click="createUser">
        Crear Usuario
    </Button>
    
    <!-- Verificar múltiples permisos -->
    <div v-if="hasAnyPermission(['users.view', 'roles.view'])">
        Gestión de usuarios
    </div>
</template>
```

### 3. En Rutas (Backend)
```php
// web.php
Route::resource('users', UserController::class)
    ->middleware('permission:users.view|users.create|users.edit|users.delete');

Route::prefix('pos')->middleware('permission:pos.access')->group(function () {
    // Rutas del POS
});
```

### 4. En Controladores
```php
// UserController.php
public function index()
{
    $this->authorize('viewAny', User::class);
    // Lógica del controlador
}

public function store(Request $request)
{
    $this->authorize('create', User::class);
    // Lógica del controlador
}
```

## 📋 Permisos Disponibles

### Gestión de Usuarios
- `users.view` - Ver usuarios
- `users.create` - Crear usuarios
- `users.edit` - Editar usuarios
- `users.delete` - Eliminar usuarios

### Gestión de Roles
- `roles.view` - Ver roles
- `roles.create` - Crear roles
- `roles.edit` - Editar roles
- `roles.delete` - Eliminar roles

### Productos
- `products.view` - Ver productos
- `products.create` - Crear productos
- `products.edit` - Editar productos
- `products.delete` - Eliminar productos

### Categorías
- `categories.view` - Ver categorías
- `categories.create` - Crear categorías
- `categories.edit` - Editar categorías
- `categories.delete` - Eliminar categorías

### Clientes
- `customers.view` - Ver clientes
- `customers.create` - Crear clientes
- `customers.edit` - Editar clientes
- `customers.delete` - Eliminar clientes

### Unidades de Medida
- `unit_measures.view` - Ver unidades
- `unit_measures.create` - Crear unidades
- `unit_measures.edit` - Editar unidades
- `unit_measures.delete` - Eliminar unidades

### Punto de Venta
- `pos.access` - Acceder al POS
- `pos.sessions.view` - Ver sesiones
- `pos.sessions.create` - Crear sesiones
- `pos.sessions.edit` - Editar sesiones

### Facturas
- `invoices.view` - Ver facturas
- `invoices.edit` - Editar facturas

## 👥 Roles Predefinidos

### Super Admin
- **Descripción**: Administrador con todos los permisos
- **Permisos**: Todos los permisos del sistema
- **Notas**: No se puede eliminar ni editar

### Admin
- **Descripción**: Administrador con permisos limitados
- **Permisos**: Gestión completa excepto usuarios y roles
- **Puede gestionar**: Productos, categorías, clientes, POS, facturas

### Manager
- **Descripción**: Gerente con permisos de visualización y POS
- **Permisos**: Ver reportes, usar POS, gestionar sesiones
- **Puede gestionar**: POS, ver productos, ver clientes

### Cashier
- **Descripción**: Cajero con acceso básico al POS
- **Permisos**: Solo acceso al POS
- **Puede gestionar**: Ventas básicas

## 🔄 Flujo de Autorización

1. **Usuario ingresa** → Middleware verifica autenticación
2. **Accede a ruta** → Middleware verifica permisos específicos
3. **Ve el menú** → Se filtran elementos según permisos
4. **Interactúa con UI** → Componentes verifican permisos
5. **Ejecuta acción** → Controlador verifica autorización

## 🛠 Mantenimiento

### Agregar Nuevos Permisos
1. Actualizar el seeder: `database/seeders/RolesAndPermissionsSeeder.php`
2. Ejecutar: `php artisan db:seed --class=RolesAndPermissionsSeeder`
3. Actualizar navItems.ts si es necesario
4. Agregar verificaciones en componentes

### Crear Nuevos Roles
```php
// En el seeder
$newRole = Role::create(['name' => 'New Role']);
$newRole->givePermissionTo(['permission1', 'permission2']);
```

### Asignar Permisos a Usuario
```php
$user = User::find(1);
$user->assignRole('Admin');
// O permisos directos
$user->givePermissionTo('products.create');
```

## 🔍 Debugging

### Verificar Permisos de Usuario
```php
$user = auth()->user();
dd($user->getAllPermissions()->pluck('name'));
dd($user->getRoleNames());
```

### En Frontend
```javascript
// En cualquier componente
console.log('Permisos:', usePage().props.auth.permissions)
console.log('Roles:', usePage().props.auth.roles)
```

## ⚠️ Consideraciones Importantes

1. **Super Admin** siempre tiene todos los permisos
2. Los permisos se cachean automáticamente por Spatie
3. Los cambios de permisos requieren logout/login para aplicarse
4. Las rutas están protegidas tanto en frontend como backend
5. El middleware verifica permisos antes de llegar al controlador

## 🔄 Sincronización de Permisos

Si se actualiza el seeder con nuevos permisos:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan permission:cache-reset
```

## 📝 Ejemplo Completo de Implementación

Ver `resources/js/pages/Users/Index.vue` como ejemplo de página con verificaciones completas de permisos en:
- Filtrado de menú
- Botones de acción
- Verificaciones condicionales
- Manejo de errores de autorización

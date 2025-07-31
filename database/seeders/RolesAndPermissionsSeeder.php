<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar cache de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir permisos por módulos con formato módulo:acción
        $permissions = [
            // Dashboard
            'dashboard:view',
            'dashboard:configure',

            // Productos
            'products:view',
            'products:show',
            'products:create',
            'products:edit',
            'products:delete',

            // Categorías
            'categories:view',
            'categories:show',
            'categories:create',
            'categories:edit',
            'categories:delete',

            // Unidades de medida
            'unit-measures:view',
            'unit-measures:show',
            'unit-measures:create',
            'unit-measures:edit',
            'unit-measures:delete',

            // Clientes
            'customers:view',
            'customers:show',
            'customers:create',
            'customers:edit',
            'customers:delete',

            // Facturas
            'invoices:view',
            'invoices:show',
            'invoices:create',
            'invoices:edit',
            'invoices:delete',
            'invoices:print',

            // POS (Punto de Venta)
            'pos:access',
            'pos:sell',
            'pos:refund',

            // Sesiones POS
            'pos-sessions:view',
            'pos-sessions:show',
            'pos-sessions:create',
            'pos-sessions:edit',
            'pos-sessions:delete',
            'pos-sessions:force-close',

            // Proveedores
            'suppliers:view',
            'suppliers:show',
            'suppliers:create',
            'suppliers:edit',
            'suppliers:delete',
            'suppliers:pay_debt',

            // Deudas de clientes
            'customer_debts:view',
            'customer_debts:show',
            'customer_debts:delete',
            'customer_debts:add_payment',

            // Pagos (ingresos y egresos)
            'payments:view',
            'payments:show',
            'payments:create',
            'payments:edit',
            'payments:delete',

            // Reportes
            'reports:view',
            'reports:generate',
            'reports:export',

            // Configuración del sistema
            'settings:view',
            'settings:edit',

            // Gestión de usuarios
            'users:view',
            'users:show',
            'users:create',
            'users:edit',
            'users:delete',

            // Gestión de roles
            'roles:view',
            'roles:show',
            'roles:create',
            'roles:edit',
            'roles:delete',
            'roles:assign',

            // Gestión de permisos
            'permissions:view',
            'permissions:assign',
        ];

        // Crear permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Definir roles con sus permisos
        $roles = [
            'Super Admin' => $permissions, // Super Admin tiene todos los permisos

            'Admin' => [
                'dashboard:view',
                'dashboard:configure',
                'products:view', 'products:show', 'products:create', 'products:edit', 'products:delete',
                'categories:view', 'categories:show', 'categories:create', 'categories:edit', 'categories:delete',
                'unit-measures:view', 'unit-measures:show', 'unit-measures:create', 'unit-measures:edit', 'unit-measures:delete',
                'customers:view', 'customers:show', 'customers:create', 'customers:edit', 'customers:delete',
                'suppliers:view', 'suppliers:show', 'suppliers:create', 'suppliers:edit', 'suppliers:delete', 'suppliers:pay_debt',
                'customer_debts:view', 'customer_debts:show', 'customer_debts:delete', 'customer_debts:add_payment',
                'payments:view', 'payments:show', 'payments:create', 'payments:edit', 'payments:delete',
                'invoices:view', 'invoices:show', 'invoices:create', 'invoices:edit', 'invoices:print',
                'pos:access', 'pos:sell',
                'pos-sessions:view', 'pos-sessions:show', 'pos-sessions:create', 'pos-sessions:edit', 'pos-sessions:force-close',
                'reports:view', 'reports:generate', 'reports:export',
                'settings:view', 'settings:edit',
                'users:view', 'users:show', 'users:create', 'users:edit', 'users:delete',
                'roles:view', 'roles:assign',
            ],

            'Gerente' => [
                'dashboard:view',
                'products:view', 'products:show', 'products:create', 'products:edit',
                'categories:view', 'categories:show', 'categories:create', 'categories:edit',
                'unit-measures:view', 'unit-measures:show', 'unit-measures:create', 'unit-measures:edit',
                'customers:view', 'customers:show', 'customers:create', 'customers:edit',
                'suppliers:view', 'suppliers:show', 'suppliers:create', 'suppliers:edit', 'suppliers:pay_debt',
                'customer_debts:view', 'customer_debts:show', 'customer_debts:add_payment',
                'payments:view', 'payments:show', 'payments:create', 'payments:edit',
                'invoices:view', 'invoices:show', 'invoices:create', 'invoices:edit', 'invoices:print',
                'pos:access', 'pos:sell', 'pos:refund',
                'pos-sessions:view', 'pos-sessions:show', 'pos-sessions:create', 'pos-sessions:edit',
                'reports:view', 'reports:generate', 'reports:export',
            ],

            'Cajero' => [
                'dashboard:view',
                'products:view', 'products:show',
                'customers:view', 'customers:show', 'customers:create',
                'customer_debts:view', 'customer_debts:show', 'customer_debts:add_payment',
                'payments:view', 'payments:show', 'payments:create',
                'invoices:view', 'invoices:show', 'invoices:create', 'invoices:print',
                'pos:access', 'pos:sell',
                'pos-sessions:view', 'pos-sessions:show', 'pos-sessions:create',
            ],

            'Vendedor' => [
                'dashboard:view',
                'products:view', 'products:show',
                'customers:view', 'customers:show', 'customers:create', 'customers:edit',
                'customer_debts:view', 'customer_debts:show', 'customer_debts:add_payment',
                'payments:view', 'payments:show',
                'invoices:view', 'invoices:show', 'invoices:create', 'invoices:print',
                'pos:access', 'pos:sell',
            ],
        ];

        // Crear roles y asignar permisos
        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        // Asignar rol de Super Admin al primer usuario
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('Super Admin');
            echo "Rol 'Super Admin' asignado al usuario: {$firstUser->name} ({$firstUser->email})\n";
        }

        echo "Roles y permisos creados exitosamente.\n";
        echo "Roles creados: " . implode(', ', array_keys($roles)) . "\n";
        echo "Total de permisos: " . count($permissions) . "\n";
    }
}

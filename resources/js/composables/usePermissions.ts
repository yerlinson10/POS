import { usePage } from '@inertiajs/vue3';
import type { NavItem } from '@/types';
import type { AppPageProps } from '@/types';

export function usePermissions() {
    const page = usePage<AppPageProps>();

    const hasPermission = (permission: string): boolean => {
        if (!permission) return true;

        const userPermissions = page.props.auth.permissions || [];
        const userRoles = page.props.auth.roles || [];

        // Super Admin tiene todos los permisos
        if (userRoles.includes('Super Admin')) {
            return true;
        }

        return userPermissions.includes(permission);
    };

    const filterNavItems = (items: NavItem[]): NavItem[] => {
        return items.filter(item => {
            if (!item.permission) return true;
            return hasPermission(item.permission);
        });
    };

    const hasAnyPermission = (permissions: string[]): boolean => {
        if (!permissions.length) return true;

        const userRoles = page.props.auth.roles || [];

        // Super Admin tiene todos los permisos
        if (userRoles.includes('Super Admin')) {
            return true;
        }

        const userPermissions = page.props.auth.permissions || [];
        return permissions.some(permission => userPermissions.includes(permission));
    };

    const hasRole = (role: string): boolean => {
        const userRoles = page.props.auth.roles || [];
        return userRoles.includes(role);
    };

    const isSuperAdmin = (): boolean => {
        return hasRole('Super Admin');
    };

    return {
        hasPermission,
        filterNavItems,
        hasAnyPermission,
        hasRole,
        isSuperAdmin,
    };
}

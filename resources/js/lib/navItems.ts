import { type NavItem } from '@/types';
import { BookOpen, FolderOpenIcon, Folder, LayoutGrid, RulerIcon, PackageIcon, Users, ShoppingCartIcon, FileText, Monitor, Grid3x3, UserCog, Shield, Truck, CreditCard, Banknote } from 'lucide-vue-next';

export const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Dynamic Dashboard',
        href: '/dynamic-dashboard',
        icon: Grid3x3,
    },
    {
        title: 'Point of Sale',
        href: '/pos',
        icon: ShoppingCartIcon,
        permission: 'pos:access',
    },
    {
        title: 'Sessions',
        href: '/sessions',
        icon: Monitor,
        permission: 'pos-sessions:view',
    },
    {
        title: 'Invoices',
        href: '/invoices',
        icon: FileText,
        permission: 'invoices:view',
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: FolderOpenIcon,
        permission: 'categories:view',
    },
    {
        title: 'Units Measure',
        href: '/unit-measures',
        icon: RulerIcon,
        permission: 'unit-measures:view',
    },
    {
        title: 'Products',
        href: '/products',
        icon: PackageIcon,
        permission: 'products:view',
    },
    {
        title: 'Customers',
        href: '/customers',
        icon: Users,
        permission: 'customers:view',
    },
    {
        title: 'Suppliers',
        href: '/suppliers',
        icon: Truck,
        permission: 'suppliers:view',
    },
    {
        title: 'Customer Debts',
        href: '/customer-debts',
        icon: CreditCard,
        permission: 'customer-debts:view',
    },
    {
        title: 'Payments',
        href: '/payments',
        icon: Banknote,
        permission: 'payments:view',
    },
    {
        title: 'Users',
        href: '/users',
        icon: UserCog,
        permission: 'users:view',
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: Shield,
        permission: 'roles:view',
    },
];

export const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

import { type NavItem } from '@/types';
import { BookOpen, FolderOpenIcon, Folder, LayoutGrid, RulerIcon, PackageIcon, Users, ShoppingCartIcon, FileText, Monitor, Grid3x3 } from 'lucide-vue-next';

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
    },
    {
        title: 'Sessions',
        href: '/sessions',
        icon: Monitor,
    },
    {
        title: 'Invoices',
        href: '/invoices',
        icon: FileText,
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: FolderOpenIcon,
    },
    {
        title: 'Units Measure',
        href: '/unit-measures',
        icon: RulerIcon,
    },
    {
        title: 'Products',
        href: '/products',
        icon: PackageIcon,
    },
    {
        title: 'Customers',
        href: '/customers',
        icon: Users,
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

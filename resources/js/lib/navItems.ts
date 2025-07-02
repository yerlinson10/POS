import { type NavItem } from '@/types';
import { BookOpen, Box, Folder, LayoutGrid, PencilRuler, ShoppingBasket, BookUser, Receipt, ShoppingCart } from 'lucide-vue-next';

export const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'POS',
        href: '/pos',
        icon: ShoppingCart,
    },
    {
        title: 'Invoices',
        href: '/invoices',
        icon: Receipt,
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: Box,
    },
    {
        title: 'Units Measure',
        href: '/unit-measures',
        icon: PencilRuler,
    },
    {
        title: 'Products',
        href: '/products',
        icon: ShoppingBasket,
    },
    {
        title: 'Customers',
        href: '/customers',
        icon: BookUser,
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

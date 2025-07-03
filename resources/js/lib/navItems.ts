import { type NavItem } from '@/types';
import { BookOpen, Box, Folder, LayoutGrid, PencilRuler, ShoppingBasket, Users, CreditCard  } from 'lucide-vue-next';

export const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Point of Sale',
        href: '/pos',
        icon: CreditCard,
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

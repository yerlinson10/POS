export interface Product {
    id: number;
    sku: string;
    name: string;
    category: string;
    unit_measure: string;
    price: number;
    stock: number;
    image?: string;
}

export interface Customer {
    id: number;
    full_name: string;
    email?: string;
    phone?: string;
    address?: string;
}

export interface CartItem {
    product_id: number;
    product_name: string;
    product_sku: string;
    unit_price: number;
    quantity: number;
    line_total: number;
    available_stock: number;
}

export interface Sale {
    id: number;
    customer_id?: number;
    pos_user_id: number;
    date: string;
    total_amount: number;
    status: string;
    subtotal: number;
    discount_type?: 'percentage' | 'fixed';
    discount_value?: number;
    discount_amount: number;
    customer?: Customer;
    items?: SaleItem[];
}

export interface SaleItem {
    id: number;
    invoice_id: number;
    product_id: number;
    quantity: number;
    unit_price: number;
    line_total: number;
    product?: Product;
}

export interface ProductFilters {
    per_page?: number;
    search?: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
    current_page?: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: ProductFilters;
}

export interface CustomerCreateRequest {
    first_name: string;
    last_name: string;
    email?: string;
    phone?: string;
    address?: string;
}

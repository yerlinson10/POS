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

export type InvoiceStatus = 'quotation' | 'paid';

export interface Sale {
    id: number;
    customer_id?: number;
    pos_user_id: number;
    date: string;
    total_amount: number;
    status: InvoiceStatus;
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

export interface PosSession {
    id: number;
    user_id: number;
    opened_at: string;
    closed_at?: string;
    initial_cash: number;
    final_cash?: number;
    expected_cash?: number;
    cash_difference?: number;
    opening_notes?: string;
    closing_notes?: string;
    status: 'open' | 'closed';
    cash_breakdown?: CashBreakdown;
    user_name?: string;
    user?: User; // Relación opcional con usuario
    created_at: string;
    updated_at: string;
    // Campos calculados (accessors)
    total_sales?: number;
    sales_count?: number;
    cash_sales?: number;
    card_sales?: number;
    duration?: string;
}

export interface CashBreakdown {
    bills: CashDenomination[];
    coins: CashDenomination[];
}

export interface CashDenomination {
    denomination: number;
    quantity: number;
}

export interface PosSessionSummary {
    session: PosSession;
    total_sales: number;
    sales_count: number;
    cash_sales: number;
    card_sales: number;
    expected_cash: number;
    duration: string;
    invoices_by_hour: Record<string, { count: number; total: number }>;
    payment_methods_breakdown: Record<string, { count: number; total: number }>;
    cash_difference?: number;
    final_cash?: number;
}

export interface OpenSessionRequest {
    initial_cash: number;
    opening_notes?: string;
    cash_breakdown?: CashBreakdown;
}

export interface CloseSessionRequest {
    final_cash: number;
    closing_notes?: string;
    cash_breakdown?: CashBreakdown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
}

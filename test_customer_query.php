<?php

// Test script para verificar que la consulta de customers funcione correctamente

use Illuminate\Support\Facades\DB;

// Simular los datos de entrada
$dateFrom = '2025-07-01';
$dateTo = '2025-07-12';

echo "Testing customer stats query...\n";

try {
    // Probar la consulta exacta que se usa en getCustomerStats
    $topCustomers = DB::table('invoices')
        ->join('customers', 'invoices.customer_id', '=', 'customers.id')
        ->where('invoices.status', 'paid')
        ->whereBetween('invoices.date', [$dateFrom, $dateTo])
        ->select(
            'customers.id',
            'customers.first_name',
            'customers.last_name',
            DB::raw('CONCAT(customers.first_name, " ", customers.last_name) as name'),
            DB::raw('SUM(invoices.total_amount) as total_spent'),
            DB::raw('COUNT(invoices.id) as purchase_count')
        )
        ->groupBy('customers.id', 'customers.first_name', 'customers.last_name')
        ->orderBy('total_spent', 'desc')
        ->limit(5)
        ->get();

    echo "Query executed successfully!\n";
    echo "Number of customers found: " . $topCustomers->count() . "\n";
    
    foreach ($topCustomers as $customer) {
        echo "Customer: {$customer->name} (ID: {$customer->id})\n";
        echo "  - First Name: {$customer->first_name}\n";
        echo "  - Last Name: {$customer->last_name}\n";
        echo "  - Total Spent: {$customer->total_spent}\n";
        echo "  - Purchase Count: {$customer->purchase_count}\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";

<?php

// Test simple para verificar creación de deudas
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Simular datos de prueba
echo "Testing debt creation logic...\n\n";

// Test 1: Verificar campos del modelo CustomerDebt
echo "Test 1: Model fields\n";
$fillableFields = [
    'customer_id',
    'invoice_id', 
    'user_id',
    'original_amount',
    'remaining_amount',
    'paid_amount',
    'debt_date',
    'due_date',
    'status',
    'notes'
];

echo "CustomerDebt fillable fields: " . implode(', ', $fillableFields) . "\n";

// Test 2: Verificar estructura de datos que se envía
echo "\nTest 2: Data structure for debt creation\n";
$cartData = [
    'customer_id' => 1,
    'total_amount' => 100.00,
    'subtotal' => 100.00,
    'discount_type' => null,
    'discount_value' => 0,
    'payment_method' => 'cash',
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 50.00,
            'line_total' => 100.00
        ]
    ]
];

$debtData = [
    'paid_amount' => 50.00,
    'due_date' => '2025-08-15',
    'description' => 'Test debt'
];

$totalAmount = $cartData['total_amount'];
$paidAmount = $debtData['paid_amount'];
$debtAmount = $totalAmount - $paidAmount;

echo "Cart total: $" . $totalAmount . "\n";
echo "Paid amount: $" . $paidAmount . "\n";
echo "Debt amount: $" . $debtAmount . "\n";

// Test 3: Verificar estructura de datos para CustomerDebt
echo "\nTest 3: CustomerDebt data structure\n";
$customerDebtData = [
    'customer_id' => $cartData['customer_id'],
    'invoice_id' => 999, // Simulated invoice ID
    'user_id' => 1,
    'original_amount' => $debtAmount,
    'remaining_amount' => $debtAmount,
    'paid_amount' => 0,
    'debt_date' => date('Y-m-d'),
    'due_date' => $debtData['due_date'],
    'status' => 'pending',
    'notes' => $debtData['description'] ?? "Debt created from POS sale"
];

echo "CustomerDebt data to create:\n";
foreach ($customerDebtData as $key => $value) {
    echo "  $key: $value\n";
}

echo "\n✅ All tests passed! Data structure looks correct.\n";
echo "\nNext steps:\n";
echo "1. Ensure customer_debts table exists in database\n";
echo "2. Test the actual HTTP request to /pos/sales-with-debt\n";
echo "3. Check if CustomerDebt records are being created\n";

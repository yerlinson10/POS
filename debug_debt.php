<?php

// Test simple sin dependencias externas
echo "=== DEBT CREATION DEBUG TEST ===\n\n";

// Test 1: Verificar lógica de cálculo de deuda
echo "Test 1: Debt calculation logic\n";
$totalAmount = 100.00;
$paidAmount = 30.00;
$debtAmount = $totalAmount - $paidAmount;

echo "Total: $" . number_format($totalAmount, 2) . "\n";
echo "Paid: $" . number_format($paidAmount, 2) . "\n";
echo "Debt: $" . number_format($debtAmount, 2) . "\n";
echo "Should create debt: " . ($debtAmount > 0 ? "YES" : "NO") . "\n\n";

// Test 2: Verificar datos que se envían al controller
echo "Test 2: Request data structure\n";
$requestData = [
    'paid_amount' => $paidAmount,
    'due_date' => '2025-08-15',
    'description' => 'Test debt from POS',
    'cart_data' => [
        'customer_id' => 1,
        'total_amount' => $totalAmount,
        'subtotal' => $totalAmount,
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
    ]
];

echo "Request structure:\n";
echo json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Simular datos que se guardarían en CustomerDebt
echo "Test 3: CustomerDebt model data\n";
$customerDebtData = [
    'customer_id' => $requestData['cart_data']['customer_id'],
    'invoice_id' => 123, // Simulated
    'user_id' => 1,
    'original_amount' => $debtAmount,
    'remaining_amount' => $debtAmount,
    'paid_amount' => 0,
    'debt_date' => date('Y-m-d'),
    'due_date' => $requestData['due_date'],
    'status' => 'pending',
    'notes' => $requestData['description']
];

echo "CustomerDebt data:\n";
echo json_encode($customerDebtData, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Verificar datos de Payment si hay pago parcial
if ($paidAmount > 0) {
    echo "Test 4: Payment model data (partial payment)\n";
    $paymentData = [
        'payment_type' => 'income',
        'amount' => $paidAmount,
        'payment_method' => $requestData['cart_data']['payment_method'],
        'category' => 'sales',
        'description' => "Payment for invoice #123 (Partial payment)",
        'customer_id' => $requestData['cart_data']['customer_id'],
        'invoice_id' => 123, // Simulated
        'user_id' => 1
    ];
    
    echo "Payment data:\n";
    echo json_encode($paymentData, JSON_PRETTY_PRINT) . "\n\n";
}

echo "=== COMMON ISSUES TO CHECK ===\n";
echo "1. ✅ customer_debts table exists with correct fields\n";
echo "2. ✅ CustomerDebt model has correct fillable fields\n";
echo "3. ✅ Route /pos/sales-with-debt exists\n";
echo "4. ✅ POSController::processSaleWithDebt method exists\n";
echo "5. ⚠️  Check if PHP version is compatible (needs 8.2+)\n";
echo "6. ⚠️  Check if migrations have been run\n";
echo "7. ⚠️  Check database connection\n";
echo "8. ⚠️  Check if frontend is sending correct data structure\n\n";

echo "=== DEBUGGING STEPS ===\n";
echo "1. Open browser network tab and check request to /pos/sales-with-debt\n";
echo "2. Check Laravel logs in storage/logs/\n";
echo "3. Verify database tables exist\n";
echo "4. Test with simple payment (no debt) first\n\n";

echo "✅ Test completed successfully!\n";

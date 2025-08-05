<?php

// Script de prueba para verificar la funcionalidad de debt creation
// Ejecutar con: php test_debt_system.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\CustomerDebt;
use App\Models\InvoiceDebt;
use App\Models\Payment;

echo "=== Testing POS Debt System ===\n\n";

// 1. Verificar que tenemos datos de prueba
$customerCount = Customer::count();
$productCount = Product::count();

echo "Customers in DB: $customerCount\n";
echo "Products in DB: $productCount\n\n";

if ($customerCount == 0 || $productCount == 0) {
    echo "❌ No test data available. Please seed the database first.\n";
    exit;
}

// 2. Verificar estructura de tabla customer_debts
echo "=== Checking customer_debts table structure ===\n";
try {
    $columns = DB::select("DESCRIBE customer_debts");
    echo "✅ customer_debts table exists with columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking customer_debts table: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Verificar estructura de tabla invoice_debts
echo "=== Checking invoice_debts table structure ===\n";
try {
    $columns = DB::select("DESCRIBE invoice_debts");
    echo "✅ invoice_debts pivot table exists with columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking invoice_debts table: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Verificar ENUM values para status
echo "=== Checking customer_debts status ENUM values ===\n";
try {
    $result = DB::select("SHOW COLUMNS FROM customer_debts LIKE 'status'");
    if (!empty($result)) {
        echo "✅ Status ENUM: " . $result[0]->Type . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking status ENUM: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Test debt creation
echo "=== Testing CustomerDebt creation ===\n";
try {
    $testDebt = CustomerDebt::create([
        'customer_id' => 1,
        'invoice_id' => 1,
        'user_id' => 1,
        'original_amount' => 100.50,
        'remaining_amount' => 100.50,
        'paid_amount' => 0.00,
        'debt_date' => now()->toDateString(),
        'status' => 'pending',
        'due_date' => now()->addDays(30)->toDateString(),
        'notes' => 'Test debt creation'
    ]);

    echo "✅ CustomerDebt created successfully with ID: {$testDebt->id}\n";

    // Clean up test
    $testDebt->delete();
    echo "✅ Test debt cleaned up\n";

} catch (Exception $e) {
    echo "❌ Error creating CustomerDebt: " . $e->getMessage() . "\n";
}

echo "\n=== Test completed ===\n";

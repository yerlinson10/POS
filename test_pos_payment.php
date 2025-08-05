<?php

require_once 'vendor/autoload.php';

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\CustomerDebt;
use Illuminate\Database\Capsule\Manager as DB;

// Crear configuración de base de datos
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'pos',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Testing POS Payment Creation ===\n\n";

// Buscar una factura reciente creada desde POS
$invoice = Invoice::with(['customer', 'payments', 'debts'])->latest()->first();

if (!$invoice) {
    echo "No invoices found in the database.\n";
    exit;
}

echo "Invoice ID: {$invoice->id}\n";
echo "Customer: {$invoice->customer->name}\n";
echo "Total Amount: \${$invoice->total_amount}\n";
echo "Paid Amount: \${$invoice->paid_amount}\n";
echo "Debt Amount: \${$invoice->debt_amount}\n";
echo "Status: {$invoice->status}\n\n";

// Verificar pagos relacionados
echo "=== PAYMENTS ===\n";
$payments = Payment::where('customer_id', $invoice->customer_id)
    ->where('description', 'like', "%Invoice ID #{$invoice->id}%")
    ->get();

if ($payments->count() > 0) {
    foreach ($payments as $payment) {
        echo "Payment ID: {$payment->id}\n";
        echo "Amount: \${$payment->amount}\n";
        echo "Method: {$payment->payment_method}\n";
        echo "Category: {$payment->category}\n";
        echo "Customer Debt ID: " . ($payment->customer_debt_id ?? 'NULL') . "\n";
        echo "Description: {$payment->description}\n";
        echo "Date: {$payment->payment_date}\n\n";
    }
} else {
    echo "No payments found for this invoice!\n\n";
}

// Verificar deudas relacionadas
echo "=== CUSTOMER DEBTS ===\n";
$debts = CustomerDebt::where('invoice_id', $invoice->id)->get();

if ($debts->count() > 0) {
    foreach ($debts as $debt) {
        echo "Debt ID: {$debt->id}\n";
        echo "Original Amount: \${$debt->original_amount}\n";
        echo "Paid Amount: \${$debt->paid_amount}\n";
        echo "Remaining Amount: \${$debt->remaining_amount}\n";
        echo "Status: {$debt->status}\n";
        echo "Notes: {$debt->notes}\n\n";

        // Verificar pagos asociados a esta deuda
        $debtPayments = Payment::where('customer_debt_id', $debt->id)->get();
        echo "Payments for this debt: {$debtPayments->count()}\n";
        foreach ($debtPayments as $payment) {
            echo "  - Payment ID {$payment->id}: \${$payment->amount} ({$payment->payment_method})\n";
        }
        echo "\n";
    }
} else {
    echo "No debts found for this invoice!\n\n";
}

// Verificar pagos sin relación a deuda
echo "=== PAYMENTS WITHOUT DEBT RELATION ===\n";
$orphanPayments = Payment::where('customer_id', $invoice->customer_id)
    ->whereNull('customer_debt_id')
    ->where('description', 'like', "%Invoice ID #{$invoice->id}%")
    ->get();

if ($orphanPayments->count() > 0) {
    echo "Found {$orphanPayments->count()} payments without debt relation:\n";
    foreach ($orphanPayments as $payment) {
        echo "  - Payment ID {$payment->id}: \${$payment->amount}\n";
    }
} else {
    echo "All payments are properly related to debts.\n";
}

echo "\n=== Test Complete ===\n";

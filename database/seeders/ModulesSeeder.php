<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\CustomerDebt;
use App\Models\Payment;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear proveedores de ejemplo
        $suppliers = [
            [
                'company_name' => 'Distribuidora ABC S.A.',
                'contact_name' => 'Juan Pérez',
                'email' => 'contacto@distribuidoraabc.com',
                'phone' => '123-456-7890',
                'address' => 'Av. Principal 123',
                'city' => 'Bogotá',
                'country' => 'Colombia',
                'tax_id' => '900123456-1',
                'current_debt' => 2500.00,
                'is_active' => true,
            ],
            [
                'company_name' => 'Importadora XYZ Ltda.',
                'contact_name' => 'María García',
                'email' => 'ventas@importadoraxyz.com',
                'phone' => '098-765-4321',
                'address' => 'Calle Comercial 456',
                'city' => 'Medellín',
                'country' => 'Colombia',
                'tax_id' => '800987654-2',
                'current_debt' => 0,
                'is_active' => true,
            ],
            [
                'company_name' => 'Mayorista Global Corp.',
                'contact_name' => 'Carlos López',
                'email' => 'info@mayoristaglobal.com',
                'phone' => '555-123-4567',
                'address' => 'Zona Industrial 789',
                'city' => 'Cali',
                'country' => 'Colombia',
                'tax_id' => '901234567-3',
                'current_debt' => 1800.50,
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }

        // Crear algunas deudas de clientes de ejemplo
        $customers = Customer::take(3)->get();
        $invoices = Invoice::take(5)->get();

        if ($customers->count() > 0 && $invoices->count() > 0) {
            $debts = [
                [
                    'customer_id' => $customers[0]->id,
                    'invoice_id' => $invoices[0]->id,
                    'user_id' => 1,
                    'original_amount' => 500.00,
                    'remaining_amount' => 300.00,
                    'paid_amount' => 200.00,
                    'debt_date' => now()->subDays(15),
                    'due_date' => now()->addDays(15),
                    'status' => 'partial',
                    'notes' => 'Cliente pidió facilidades de pago',
                ],
                [
                    'customer_id' => $customers[1]->id,
                    'invoice_id' => $invoices[1]->id,
                    'user_id' => 1,
                    'original_amount' => 750.00,
                    'remaining_amount' => 750.00,
                    'paid_amount' => 0,
                    'debt_date' => now()->subDays(30),
                    'due_date' => now()->subDays(5), // Vencida
                    'status' => 'overdue',
                    'notes' => 'Deuda vencida, contactar cliente',
                ],
                [
                    'customer_id' => $customers[2]->id,
                    'invoice_id' => $invoices[2]->id,
                    'user_id' => 1,
                    'original_amount' => 1200.00,
                    'remaining_amount' => 1200.00,
                    'paid_amount' => 0,
                    'debt_date' => now()->subDays(5),
                    'due_date' => now()->addDays(25),
                    'status' => 'pending',
                    'notes' => 'Deuda reciente, seguimiento programado',
                ],
            ];

            foreach ($debts as $debtData) {
                CustomerDebt::create($debtData);
            }
        }

        // Crear algunos pagos de ejemplo
        $payments = [
            [
                'type' => 'income',
                'category' => 'debt_payment',
                'amount' => 200.00,
                'payment_method' => 'cash',
                'payment_date' => now()->subDays(3),
                'description' => 'Pago parcial de deuda - Cliente A',
                'customer_id' => $customers[0]->id ?? null,
                'user_id' => 1,
            ],
            [
                'type' => 'expense',
                'category' => 'supplier_payment',
                'amount' => 500.00,
                'payment_method' => 'bank_transfer',
                'payment_date' => now()->subDays(1),
                'description' => 'Pago a proveedor - Distribuidora ABC',
                'supplier_id' => 1,
                'reference_number' => 'TRF-001',
                'user_id' => 1,
            ],
            [
                'type' => 'income',
                'category' => 'other_income',
                'amount' => 150.00,
                'payment_method' => 'cash',
                'payment_date' => now(),
                'description' => 'Venta de material reciclable',
                'user_id' => 1,
            ],
            [
                'type' => 'expense',
                'category' => 'other_expense',
                'amount' => 80.00,
                'payment_method' => 'card',
                'payment_date' => now()->subDays(2),
                'description' => 'Compra de suministros de oficina',
                'reference_number' => 'CARD-002',
                'user_id' => 1,
            ],
        ];

        foreach ($payments as $paymentData) {
            Payment::create($paymentData);
        }

        echo "✅ Datos de ejemplo creados:\n";
    }
}

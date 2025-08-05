<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerDetailController extends Controller
{
    /**
     * Display customer details with all related data
     */
    public function show(Customer $customer)
    {
        // Load all relationships with detailed information
        $customer->load([
            'invoices' => function($query) {
                $query->with(['customerDebts', 'items.product', 'user'])
                      ->orderBy('created_at', 'desc');
            },
            'customerDebts' => function($query) {
                $query->with(['invoice', 'payments', 'user'])
                      ->orderBy('created_at', 'desc');
            },
            'payments' => function($query) {
                $query->with(['customerDebt', 'user'])
                      ->orderBy('payment_date', 'desc');
            }
        ]);

        // Calculate additional statistics
        $totalInvoiceAmount = $customer->invoices->sum('total_amount');
        $totalPaidAmount = $customer->payments->where('type', 'income')->sum('amount');
        $totalDebtAmount = $customer->customerDebts->where('status', '!=', 'paid')->sum('remaining_amount');
        $overdueDebts = $customer->customerDebts->filter(function($debt) {
            return $debt->status === 'pending' && $debt->due_date && $debt->due_date < now();
        });

        return Inertia::render('CustomerDebts/CustomerDetails', [
            'customer' => $customer,
            'statistics' => [
                'total_invoice_amount' => $totalInvoiceAmount,
                'total_paid_amount' => $totalPaidAmount,
                'total_debt_amount' => $totalDebtAmount,
                'overdue_debts_count' => $overdueDebts->count(),
                'overdue_debts_amount' => $overdueDebts->sum('remaining_amount')
            ]
        ]);
    }
}

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
        // Load all relationships
        $customer->load([
            'invoices.customerDebts',
            'customerDebts.invoices',
            'payments'
        ]);

        return Inertia::render('CustomerDebts/CustomerDetails', [
            'customer' => $customer
        ]);
    }
}

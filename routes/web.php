<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UnitMeasureController;
use App\Http\Controllers\PosSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerDebtController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerDetailController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dynamic Dashboard routes
    Route::prefix('dynamic-dashboard')->name('dynamic-dashboard.')->group(function () {
        Route::get('/', [DashboardWidgetController::class, 'index'])->name('dynamic');
        Route::get('/widgets', [DashboardWidgetController::class, 'getWidgets'])->name('widgets.index');
        Route::post('/widgets', [DashboardWidgetController::class, 'store'])->name('widgets.store');
        Route::put('/widgets/{id}', [DashboardWidgetController::class, 'update'])->name('widgets.update');
        Route::delete('/widgets/{id}', [DashboardWidgetController::class, 'destroy'])->name('widgets.destroy');
        Route::post('/widgets/positions', [DashboardWidgetController::class, 'updatePositions'])->name('widgets.positions');
        Route::get('/widgets/{id}/refresh', [DashboardWidgetController::class, 'refreshWidget'])->name('widgets.refresh');
        Route::get('/filter-options', [DashboardWidgetController::class, 'getFilterOptions'])->name('filter-options');
    });

    Route::get('/invoice/{id}', [PdfController::class, 'Invoice'])->name('invoice.pdf');

    // POS routes
    Route::prefix('pos')->name('pos.')->middleware('permission:pos:access')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/products', [POSController::class, 'getProducts'])->name('products');
        Route::get('/customers', [POSController::class, 'getCustomers'])->name('customers');
        Route::post('/customers', [POSController::class, 'createCustomer'])->name('customers.store');
        Route::post('/sales', [POSController::class, 'processSale'])->name('sales.store');
        Route::post('/sales-with-debt', [POSController::class, 'processSaleWithDebt'])->name('sales.store-with-debt');
        Route::get('/product-updates', [POSController::class, 'getProductUpdates'])->name('product-updates');
        Route::get('/session-status', [POSController::class, 'getSessionStatus'])->name('session-status');
    });

    // API routes for POS (needed for frontend)
    Route::prefix('api/pos')->name('api.pos.')->middleware('permission:pos:access')->group(function () {
        Route::post('/process-sale-with-debt', [POSController::class, 'processSaleWithDebt'])->name('process-sale-with-debt');
    });

    // POS Sessions routes
    Route::prefix('sessions')->name('sessions.')->middleware('permission:pos-sessions:view')->group(function () {
        Route::get('/', [PosSessionController::class, 'index'])->name('index');
        Route::get('/create', [PosSessionController::class, 'create'])->middleware('permission:pos-sessions:create')->name('create');
        Route::post('/', [PosSessionController::class, 'store'])->middleware('permission:pos-sessions:create')->name('store');
        Route::get('/current', [PosSessionController::class, 'current'])->name('current');
        Route::get('/{posSession}', [PosSessionController::class, 'show'])->name('show');
        Route::get('/{posSession}/edit', [PosSessionController::class, 'edit'])->middleware('permission:pos-sessions:edit')->name('edit');
        Route::patch('/{posSession}', [PosSessionController::class, 'update'])->middleware('permission:pos-sessions:edit')->name('update');
        Route::post('/{posSession}/force-close', [PosSessionController::class, 'forceClose'])->middleware('permission:pos-sessions:edit')->name('force-close');

        // API routes for POS sessions
        Route::get('/api/active', [PosSessionController::class, 'getActiveSession'])->name('api.active');
        Route::get('/api/check', [PosSessionController::class, 'checkActiveSession'])->name('api.check');
        Route::get('/api/stats', [PosSessionController::class, 'getStats'])->name('api.stats');
    });

    // Products routes
    Route::resource('products', ProductController::class);

    // Unit Measures routes
    Route::resource('unit-measures', UnitMeasureController::class);

    // Categories routes
    Route::resource('categories', CategoryController::class);

    // Customers routes
    Route::resource('customers', CustomerController::class);
    Route::get('api/customers', [CustomerController::class, 'index'])->name('api.customers.index');

    // Suppliers routes
    Route::resource('suppliers', SupplierController::class)->middleware('permission:suppliers:view');
    Route::get('api/suppliers', [SupplierController::class, 'apiIndex'])->name('api.suppliers.index')->middleware('permission:suppliers:view');
    Route::post('suppliers/{supplier}/pay-debt', [SupplierController::class, 'payDebt'])->name('suppliers.pay-debt')->middleware('permission:suppliers:pay_debt');

    // Customer Debts routes
    Route::resource('customer-debts', CustomerDebtController::class)->except(['create', 'store', 'edit', 'update'])->middleware('permission:customer_debts:view');
    Route::post('customer-debts/{customerDebt}/add-payment', [CustomerDebtController::class, 'addPayment'])->name('customer-debts.add-payment')->middleware('permission:customer_debts:add_payment');
    Route::get('customer-debts/customer/{customer}/summary', [CustomerDebtController::class, 'customerSummary'])->name('customer-debts.customer-summary')->middleware('permission:customer_debts:view');
    Route::get('api/customer-debts/customer/{customer}', [CustomerDebtController::class, 'apiByCustomer'])->name('api.customer-debts.by-customer')->middleware('permission:customer_debts:view');
    Route::get('customer-debts-overdue', [CustomerDebtController::class, 'overdue'])->name('customer-debts.overdue')->middleware('permission:customer_debts:view');

    // Payments routes
    Route::resource('payments', PaymentController::class)->middleware('permission:payments:view');
    Route::get('payments-dashboard', [PaymentController::class, 'dashboard'])->name('payments.dashboard')->middleware('permission:payments:view');
    Route::post('payments/debt-payment', [PaymentController::class, 'recordDebtPayment'])->name('payments.debt-payment')->middleware('permission:payments:create');
    Route::post('payments/supplier-payment', [PaymentController::class, 'recordSupplierPayment'])->name('payments.supplier-payment')->middleware('permission:payments:create');

    // Users routes
    Route::resource('users', UserController::class);

    // Roles routes
    Route::resource('roles', RoleController::class);

    // Products routes (API endpoint)
    Route::get('api/products', [ProductController::class, 'index'])->name('api.products.index');

    // Customer Details routes
    Route::get('/customers/{customer}/details', [CustomerDetailController::class, 'show'])
        ->name('customers.details')
        ->middleware('permission:customers:view');

    // Invoices routes (only index and show - no edit/delete for legal compliance)
    Route::prefix('invoices')->name('invoices.')->middleware('permission:invoices:view')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->middleware('permission:invoices:edit')->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->middleware('permission:invoices:edit')->name('update');
        Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->middleware('permission:invoices:edit')->name('update-status');
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

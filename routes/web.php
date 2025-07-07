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

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/invoice/{id}', [PdfController::class, 'Invoice'])->name('invoice.pdf');

    // POS routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/products', [POSController::class, 'getProducts'])->name('products');
        Route::get('/customers', [POSController::class, 'getCustomers'])->name('customers');
        Route::post('/customers', [POSController::class, 'createCustomer'])->name('customers.store');
        Route::post('/sales', [POSController::class, 'processSale'])->name('sales.store');
        Route::get('/product-updates', [POSController::class, 'getProductUpdates'])->name('product-updates');
        Route::get('/session-status', [POSController::class, 'getSessionStatus'])->name('session-status');
    });

    // POS Sessions routes
    Route::prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/', [PosSessionController::class, 'index'])->name('index');
        Route::get('/create', [PosSessionController::class, 'create'])->name('create');
        Route::post('/', [PosSessionController::class, 'store'])->name('store');
        Route::get('/current', [PosSessionController::class, 'current'])->name('current');
        Route::get('/{posSession}', [PosSessionController::class, 'show'])->name('show');
        Route::get('/{posSession}/edit', [PosSessionController::class, 'edit'])->name('edit');
        Route::patch('/{posSession}', [PosSessionController::class, 'update'])->name('update');
        Route::post('/{posSession}/force-close', [PosSessionController::class, 'forceClose'])->name('force-close');

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

    // Products routes
    Route::resource('products', ProductController::class);
    Route::get('api/products', [ProductController::class, 'index'])->name('api.products.index');

    // Invoices routes (only index and show - no edit/delete for legal compliance)
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.update-status');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

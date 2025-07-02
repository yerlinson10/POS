<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UnitMeasureController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products routes
    Route::resource('products', ProductController::class);

    // Unit Measures routes
    Route::resource('unit-measures', UnitMeasureController::class);

    // Categories routes
    Route::resource('categories', CategoryController::class);

    // Sales routes
    Route::resource('customers', CustomerController::class);

    // Invoices routes
    Route::resource('invoices', InvoiceController::class);

    // POS routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/sale', [PosController::class, 'processSale'])->name('sale');
        Route::get('/products/search', [PosController::class, 'searchProducts'])->name('products.search');
        Route::get('/customers/search', [PosController::class, 'searchCustomers'])->name('customers.search');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

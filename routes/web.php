<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UnitMeasureController;
use App\Http\Controllers\POSController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // POS routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/products', [POSController::class, 'getProducts'])->name('products');
        Route::get('/customers', [POSController::class, 'getCustomers'])->name('customers');
        Route::post('/customers', [POSController::class, 'createCustomer'])->name('customers.store');
        Route::post('/sales', [POSController::class, 'processSale'])->name('sales.store');
        Route::get('/product-updates', [POSController::class, 'getProductUpdates'])->name('product-updates');
    });

    // Products routes
    Route::resource('products', ProductController::class);

    // Unit Measures routes
    Route::resource('unit-measures', UnitMeasureController::class);

    // Categories routes
    Route::resource('categories', CategoryController::class);

    // Sales routes
    Route::resource('customers', CustomerController::class);

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

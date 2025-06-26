<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Products routes
    Route::resource('products', 'ProductController');

    // Unit Measures routes
    Route::resource('unit-measures', 'UnitMeasureController');

    // Categories routes
    Route::resource('categories', 'CategoryController');

    // Sales routes
    Route::resource('customers', 'CustomerController');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

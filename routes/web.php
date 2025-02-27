<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;









Route::get('/', function () {
    return Inertia::render('Dashboard', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard',[CustomerController::class,'index'])->name('customers.index');
    Route::get('/', function () {
        return redirect()->route('customers.index');
    })->name('dashboard');

    // Route::get('/dashboard', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/test', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::post('/transactions/store', [TransactionController::class, 'store']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');




});

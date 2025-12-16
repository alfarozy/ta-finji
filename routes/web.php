<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialInsightController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;


//> done
Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginProccess'])->name('login.proccess');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerProccess'])->name('register.proccess');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'getDashboardSummary'])->name('dashboard.index'); //> done
    Route::resource('transactions', TransactionController::class);
    Route::resource('transactions-categories', TransactionCategoryController::class)->except('show'); //> done
    Route::resource('budgets', BudgetController::class); //> done
    Route::resource('bank-account', BankAccountController::class);

    // Financial Insight routes
    Route::get('/financial-insight', [FinancialInsightController::class, 'index'])
        ->name('financial.insight');
    Route::post('/financial-insight/analyze', [FinancialInsightController::class, 'analyze'])
        ->name('financial.insight.analyze');
});

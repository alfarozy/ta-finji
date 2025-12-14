<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginProccess'])->name('login.proccess');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerProccess'])->name('register.proccess');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'getDashboardSummary'])->name('dashboard.index');
    Route::resource('transactions', TransactionController::class);
    Route::resource('transactions-categories', TransactionCategoryController::class);
    Route::resource('budgets', BudgetController::class);
    Route::resource('bank-account', BankAccountController::class);

    Route::get('financial-insight', [DashboardController::class, 'financialInsight'])->name('dashboard.financial-insight');
});

//> cllback whatsapp asisten

//> callback moota API (sinc transaction)
Route::post('/callback/moota', [TransactionController::class, 'callbackMoota'])->name('callback.moota');

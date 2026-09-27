<?php

use App\Http\Controllers\Api\V1\ApiRootController;
use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CategoryTransactionController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::pattern('category', '[0-9]+');
Route::pattern('budget', '[0-9]+');
Route::pattern('transaction', '[0-9]+');

Route::prefix('v1')->group(function () {
    Route::get('/', ApiRootController::class)->name('api.root');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::scopeBindings()->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::get('categories/{category}/transactions', CategoryTransactionController::class)
            ->name('categories.transactions.index');
        Route::apiResource('categories.budgets', BudgetController::class);
        Route::apiResource('categories.budgets.transactions', TransactionController::class);
    });
});

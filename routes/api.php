<?php

use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\ApiRootController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CategoryTransactionController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::pattern('category', '[0-9]+');
Route::pattern('budget', '[0-9]+');
Route::pattern('transaction', '[0-9]+');
Route::pattern('user', '[0-9]+');

Route::prefix('v1')->group(function () {
    // Guest (no token)
    Route::get('/', ApiRootController::class)->name('api.root');

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::middleware('throttle:auth')->group(function () {
            Route::post('register', [AuthController::class, 'register'])->name('register');
            Route::post('login', [AuthController::class, 'login'])->name('login');
            Route::post('two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
        });

        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');

        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('two-factor', [TwoFactorController::class, 'store'])->name('two-factor.store');
            Route::post('two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
            Route::delete('two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.destroy');
        });
    });

    // Member and admin (valid access token); ownership is checked by CategoryPolicy
    Route::middleware('auth:api')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::scopeBindings()->group(function () {
            Route::apiResource('categories', CategoryController::class);
            Route::get('categories/{category}/transactions', CategoryTransactionController::class)
                ->name('categories.transactions.index');
            Route::apiResource('categories.budgets', BudgetController::class);
            Route::apiResource('categories.budgets.transactions', TransactionController::class);
        });

        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('users', UserController::class)->only(['index', 'show', 'destroy']);
        });
    });
});

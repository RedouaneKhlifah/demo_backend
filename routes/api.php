<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FactureServiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'SetLocale'])->group(function () {
    Route::put('/sign-in', [AuthController::class, 'signIn']);

    // Protected routes requiring JWT authentication
    Route::middleware('auth:api')->group(function () {
        // Get authenticated user
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Verify token
        Route::get('/verify-token', function (Request $request) {
            return response()->json(['message' => 'Token is valid']);
        });

        // Client routes
        Route::get('/clients', [ClientController::class, 'index']);
        Route::put('/clients', [ClientController::class, 'store']);
        Route::patch('/clients/{id}/restore', [ClientController::class, 'restore']);
        Route::get('/clients/archived', [ClientController::class, 'archived']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
        Route::patch('/clients/{client}', [ClientController::class, 'update']);
        Route::delete('/clients/bulk', [ClientController::class, 'bulkDestroy']);
        Route::delete('/clients/{client}', [ClientController::class, 'destroy']);

        // Product routes
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/archived', [ProductController::class, 'archived']);
        Route::put('/products', [ProductController::class, 'store']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::patch('/products/{id}/restore', [ProductController::class, 'restore']);
        Route::delete('/products/bulk', [ProductController::class, 'bulkDestroy']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // service routes
        Route::get('/services', [ServiceController::class, 'index']);
        Route::put('/services', [ServiceController::class, 'store']);
        Route::get('/services/{service}', [ServiceController::class, 'show']);
        Route::patch('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);



        // Order routes
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        Route::put('/orders/send-order-email/{order}', [OrderController::class, 'sendOrderToEmail']);
        Route::post('/orders/publish/{order}', [OrderController::class, 'publishToFacture']);

        // Facture products routes
        Route::get('/factures', [FactureController::class, 'index']);
        Route::put('/factures', [FactureController::class, 'store']);
        Route::get('/factures/{facture}', [FactureController::class, 'show']);
        Route::patch('/factures/{facture}', [FactureController::class, 'update']);
        // Route::delete('/factures/{facture}', [FactureController::class, 'destroy']);
        Route::patch('/factures/{facture}/cancel', [FactureController::class, 'cancel']);
        // Route::put('/factures/send-factures-email/{facture}', [FactureController::class, 'sendFactureToEmail'])

        // Employee routes
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/employees/archived', [EmployeeController::class, 'archived']);
        Route::put('/employees', [EmployeeController::class, 'store']);
        Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
        Route::patch('/employees/{id}/restore', [EmployeeController::class, 'restore']);
        Route::patch('/employees/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/employees/bulk', [EmployeeController::class, 'bulkDestroy']);
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
        Route::put('/employees/history-of-pay/{employee}', [EmployeeController::class, 'StoreHistoryOfPay']);
        Route::get('/employees/history-of-pay/{employee}', [EmployeeController::class, 'getEmployeeHistoryOfPay']);
        Route::delete('/employees/history-of-pay/{historyOfPay}', [EmployeeController::class, 'deleteHistoryOfPay']);

        // Transaction routes
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::put('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::patch('/transactions/{transaction}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

        // Dashboard routes
        Route::get('/dashboard/{start_date?}/{end_date?}', [DashboardController::class, 'index']);
    });
});
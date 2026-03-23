<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * USER ROUTES
 */

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return UserResource::make($request->user());
});

Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index'])->name('users.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/user', UserController::class)->only('update', 'destroy');
});

/*
 * PRODUCT ROUTES
 */

Route::middleware('auth:sanctum')->group(function () {
    Route::get("/products/{product}", [ProductController::class, 'show'])->name('product.show');
    Route::apiResource('/products', ProductController::class)->only('index', 'store', 'update', 'destroy');
});

/*
 * GATEWAY ROUTES
 */

Route::patch('gateways/{gateway}/toggle', [GatewayController::class, 'toggle']);
Route::patch('gateways/{gateway}/priority', [GatewayController::class, 'updatePriority']);

/*
 * TRANSACTION ROUTES
 */

Route::apiResource('/transactions', TransactionController::class)->only('index', 'store', 'show');
Route::middleware('auth:sanctum')->post("/transactions/{id}/refund", [TransactionController::class, 'refund']);

/*
 * CLIENT ROUTES
 */
Route::middleware('auth:sanctum')->get('/clients', [ClientController::class, 'index'])->name('clients.index');
Route::middleware('auth:sanctum')->group(function () {
    Route::get("/clients/{client}", [ClientController::class, 'show'])->name('client.show');
    Route::apiResource('/clients', ClientController::class)->only('store', 'update', 'destroy');
});

require __DIR__ . '/auth.php';

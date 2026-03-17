<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
/ CLIENT ROUTES
*/

Route::get('/clients', ClientController::class);

/*
/ USER ROUTES
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return UserResource::make($request->user());
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/user', UserController::class)->only('update', 'destroy');
});

/*
/ PRODUCT ROUTES
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get("/products/{product}", [ProductController::class, 'show'])->name('product.show');
    Route::apiResource('/products', ProductController::class)->only('index', 'show', 'store', 'update', 'destroy');
});

require __DIR__ . '/auth.php';

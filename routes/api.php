<?php

use App\Http\Controllers\ProductController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return UserResource::make($request->user());
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get("/products/{product}", [ProductController::class, 'show'])->name('product.show');
    Route::apiResource('/products', ProductController::class)->only('index', 'show', 'store', 'update', 'destroy');
});

require __DIR__ . '/auth.php';

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\DeliveryOrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('categories', CategoryController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('stock-movements', StockMovementController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('customer-addresses', CustomerAddressController::class);
Route::patch('delivery-orders/{deliveryOrder}/advance-status', [DeliveryOrderController::class, 'advanceStatus']);
Route::apiResource('delivery-orders', DeliveryOrderController::class);

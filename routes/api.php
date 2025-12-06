<?php

use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShopController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.shopify'])->prefix('v1')->group(function () {
    Route::get('/shop', [ShopController::class, 'show'])->name('api.shop.show');

    Route::apiResource('products', ProductController::class);
    Route::post('/products/{product}/sync', [ProductController::class, 'syncWithShopify'])->name('api.products.sync');

    Route::get('/billing/plans', [BillingController::class, 'plans'])->name('api.billing.plans');
    Route::get('/billing/status', [BillingController::class, 'status'])->name('api.billing.status');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('api.billing.subscribe');
});

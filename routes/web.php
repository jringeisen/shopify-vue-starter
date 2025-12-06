<?php

use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.shopify'])->group(function () {
    Route::get('/', [ShopifyController::class, 'index'])->name('home');
});

Route::post('/webhook/{type}', [WebhookController::class, 'handle'])->name('webhook.handle');

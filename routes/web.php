<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/doar/{ong}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/doar/{ong}', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/doar/{ong}/pagamento/{payment}', [CheckoutController::class, 'pay'])->name('checkout.pay');

Route::post('/webhooks/{provider}', [WebhookController::class, 'handle'])->name('webhooks.handle');

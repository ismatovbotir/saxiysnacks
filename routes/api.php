<?php

use App\Http\Controllers\Telegram\WebhookController;
use App\Http\Controllers\WebApp\CatalogController;
use App\Http\Controllers\WebApp\OrderController;
use App\Http\Controllers\WebApp\ProfileController;
use App\Http\Controllers\WebApp\SavedProductController;
use App\Http\Middleware\AuthenticateTelegramWebApp;
use Illuminate\Support\Facades\Route;

Route::post('telegram/webhook', WebhookController::class)->name('telegram.webhook');

Route::prefix('webapp')
    ->name('webapp.')
    ->middleware(AuthenticateTelegramWebApp::class)
    ->group(function () {
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('profile', [ProfileController::class, 'store'])->middleware('throttle:webapp-writes')->name('profile.store');
        Route::get('catalog', CatalogController::class)->name('catalog');
        Route::post('saved', [SavedProductController::class, 'store'])->middleware('throttle:webapp-list')->name('saved.store');
        Route::delete('saved/{product}', [SavedProductController::class, 'destroy'])->middleware('throttle:webapp-list')->name('saved.destroy');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('orders', [OrderController::class, 'store'])->middleware('throttle:webapp-writes')->name('orders.store');
    });

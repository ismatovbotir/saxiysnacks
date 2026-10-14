<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductPageController;
use App\Http\Controllers\Storefront\SeoController;
use App\Http\Middleware\SetStorefrontLocale;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('llms.txt', [SeoController::class, 'llms'])->name('llms');

Route::view('/webapp', 'webapp')->name('webapp');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');

        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::patch('clients/{client}/type', [ClientController::class, 'updateType'])->name('clients.type');
        Route::patch('clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.status');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});

$storefront = function () {
    Route::get('/', HomeController::class)->name('home');
    Route::view('contact', 'storefront.contact')->name('contact');
    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog');
    Route::get('catalog/{category:slug}', [CatalogController::class, 'show'])->name('category');
    Route::get('catalog/{category:slug}/{product:slug}', ProductPageController::class)->name('product');
};

Route::middleware(SetStorefrontLocale::class)->group($storefront);

Route::prefix('{locale}')
    ->where(['locale' => 'ru|en'])
    ->name('localized.')
    ->middleware(SetStorefrontLocale::class)
    ->group($storefront);

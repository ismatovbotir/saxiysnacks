<?php

use App\Enums\StockDocumentType;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DebtController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FinanceReportController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StockDocumentController;
use App\Http\Controllers\Admin\TelegramController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\OrderController as StorefrontOrderController;
use App\Http\Controllers\Storefront\ProductPageController;
use App\Http\Controllers\Storefront\SeoController;
use App\Http\Middleware\EnsureUserIsStaff;
use App\Http\Middleware\LoginAdminInDevelopment;
use App\Http\Middleware\SetAdminLocale;
use App\Http\Middleware\SetStorefrontLocale;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('llms.txt', [SeoController::class, 'llms'])->name('llms');

Route::view('/webapp', 'webapp')->name('webapp');

Route::prefix('admin')->name('admin.')->middleware([LoginAdminInDevelopment::class, SetAdminLocale::class])->group(function () {
    Route::put('locale', LocaleController::class)->name('locale');

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
        Route::post('login/code', [AuthController::class, 'sendCode'])->middleware('throttle:admin-login-code')->name('login.code');
        Route::post('login/verify', [AuthController::class, 'verifyCode'])->middleware('throttle:admin-login')->name('login.verify');
        Route::post('login/phone/reset', [AuthController::class, 'resetPhone'])->name('login.phone.reset');
    });

    Route::middleware(['auth', EnsureUserIsStaff::class])->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('products', ProductController::class)->except('show')->middleware('can:access-products');
        Route::resource('categories', CategoryController::class)->except('show')->middleware('can:access-categories');

        Route::middleware('can:access-customers')->group(function () {
            Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
            Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
            Route::patch('clients/{client}/type', [ClientController::class, 'updateType'])->name('clients.type');
            Route::patch('clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.status');
        });

        Route::middleware('can:access-orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        });

        Route::get('stock', [StockController::class, 'index'])->middleware('can:access-stock')->name('stock.index');

        foreach (StockDocumentType::cases() as $documentType) {
            Route::middleware('can:access-'.$documentType->permission()->value)
                ->prefix('stock/'.$documentType->slug())
                ->name('stock.'.$documentType->slug().'.')
                ->group(function () use ($documentType) {
                    Route::get('/', [StockDocumentController::class, 'index'])->defaults('documentType', $documentType->value)->name('index');
                    Route::get('create', [StockDocumentController::class, 'create'])->defaults('documentType', $documentType->value)->name('create');
                    Route::post('/', [StockDocumentController::class, 'store'])->defaults('documentType', $documentType->value)->name('store');
                });
        }

        // Single documents: access is checked against the document's own type in the controller.
        Route::prefix('stock/documents/{document}')->name('stock.documents.')->group(function () {
            Route::get('/', [StockDocumentController::class, 'show'])->name('show');
            Route::put('/', [StockDocumentController::class, 'update'])->name('update');
            Route::delete('/', [StockDocumentController::class, 'destroy'])->name('destroy');
            Route::post('post', [StockDocumentController::class, 'post'])->name('post');
            Route::post('unpost', [StockDocumentController::class, 'unpost'])->name('unpost');
        });
        Route::get('stock/movements', [StockController::class, 'movements'])->middleware('can:access-stock_movements')->name('stock.movements');

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::middleware('can:access-payments')->group(function () {
                Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
                Route::post('orders/{order}/payments', [PaymentController::class, 'store'])->name('payments.store');
                Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
            });
            Route::get('debts', DebtController::class)->middleware('can:access-debts')->name('debts');
            Route::middleware('can:access-expenses')->group(function () {
                Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
                Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
                Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
            });
            Route::get('report', FinanceReportController::class)->middleware('can:access-report')->name('report');
        });

        Route::middleware('can:access-contacts')->group(function () {
            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        });

        Route::middleware('can:access-telegram')->group(function () {
            Route::get('telegram', [TelegramController::class, 'edit'])->name('telegram.edit');
            Route::put('telegram/bot', [TelegramController::class, 'updateBot'])->name('telegram.bot');
            Route::post('telegram/webhook', [TelegramController::class, 'connectWebhook'])->name('telegram.webhook');
            Route::put('telegram/admin-chat', [TelegramController::class, 'updateAdminChat'])->name('telegram.admin-chat');
            Route::post('telegram/test', [TelegramController::class, 'testAdminChat'])->name('telegram.test');
        });

        Route::resource('users', UserController::class)->except('show')->middleware('can:access-staff');

        Route::middleware('can:access-roles')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });
    });
});

$storefront = function () {
    Route::get('/', HomeController::class)->name('home');
    Route::view('contact', 'storefront.contact')->name('contact');
    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog');
    Route::get('catalog/{category:slug}', [CatalogController::class, 'show'])->name('category');
    Route::get('catalog/{category:slug}/{product:slug}', ProductPageController::class)->name('product');
    Route::post('order', StorefrontOrderController::class)->middleware('throttle:website-orders')->name('order');
};

Route::middleware(SetStorefrontLocale::class)->group($storefront);

Route::prefix('{locale}')
    ->where(['locale' => 'ru|en'])
    ->name('localized.')
    ->middleware(SetStorefrontLocale::class)
    ->group($storefront);

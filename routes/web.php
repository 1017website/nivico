<?php

use App\Http\Controllers\AccountController;
// Frontend
use App\Http\Controllers\Admin\ActivityController as AdminActivity;
use App\Http\Controllers\Admin\BankAccountController as AdminBank;
use App\Http\Controllers\Admin\CategoryController as AdminCategory;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\IntegrationLogController as AdminIntegrationLog;
use App\Http\Controllers\Admin\MessageController as AdminMessage;
use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\Admin\ProductController as AdminProduct;
// Auth
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\Admin\PromoController as AdminPromo;
// Admin
use App\Http\Controllers\Admin\RoleController as AdminRole;
use App\Http\Controllers\Admin\SeoController as AdminSeo;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FRONTEND
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/promo', [PromoController::class, 'index'])->name('promo');
Route::get('/tentang', [PageController::class, 'about'])->name('about');
Route::middleware('auth')->group(function () {
    Route::get('/kontak', [PageController::class, 'contact'])->name('contact');
    Route::post('/kontak', [ContactController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('contact.store');
});

// Keranjang
Route::prefix('keranjang')->name('cart.')->controller(CartController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/add', 'add')->name('add');
    Route::patch('/{item}', 'update')->name('update');
    Route::delete('/{item}', 'remove')->name('remove');
    Route::post('/clear', 'clear')->name('clear');
    Route::post('/promo', 'applyPromo')->name('promo');
    Route::delete('/promo/remove', 'removePromo')->name('promo.remove');
});

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/pesanan/{orderNumber}/sukses', [CheckoutController::class, 'success'])->name('order.success');

// Checkout AJAX (ongkir real-time)
Route::post('/checkout/destination', [CheckoutController::class, 'searchDestination'])->name('checkout.destination');
Route::post('/checkout/shipping', [CheckoutController::class, 'calculateShipping'])->name('checkout.shipping');

// Pembayaran
Route::get('/pembayaran/{orderNumber}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/pembayaran/{orderNumber}/bukti', [PaymentController::class, 'uploadProof'])->name('payment.proof');
Route::post('/pembayaran/{orderNumber}/duitku', [PaymentController::class, 'duitkuPay'])->name('payment.duitku');
Route::post('/pembayaran/{orderNumber}/ganti-metode', [PaymentController::class, 'changeMethod'])
    ->middleware('throttle:3,1')
    ->name('payment.change-method');
// alias agar finish-redirect Midtrans punya nama order.show
Route::get('/pesanan/{orderNumber}', [PaymentController::class, 'show'])->name('order.show');

// Webhook Midtrans (tanpa CSRF — dikecualikan di bootstrap bila perlu)
Route::post('/midtrans/notify', [PaymentController::class, 'midtransNotify'])->name('midtrans.notify')->withoutMiddleware([ValidateCsrfToken::class]);
Route::get('/midtrans/finish', [PaymentController::class, 'midtransFinish'])->name('midtrans.finish');

// Callback server-to-server dan return URL Duitku.
Route::post('/duitku/callback', [PaymentController::class, 'duitkuCallback'])->name('duitku.callback')->withoutMiddleware([ValidateCsrfToken::class]);
Route::get('/duitku/kembali/{orderNumber}', [PaymentController::class, 'duitkuReturn'])->name('duitku.return');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.store');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/akun', [AccountController::class, 'index'])->middleware('auth')->name('account.dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('statistik', [StatisticController::class, 'index'])->name('statistics.index');
    });

    Route::middleware('permission:products.manage')->group(function () {
        // Import produk dari Shopee (CSV) — letakkan SEBELUM resource agar
        // 'products/import' tidak tertangkap route show/{product}.
        Route::get('products/import', [ProductImportController::class, 'form'])->name('products.import');
        Route::post('products/import/preview', [ProductImportController::class, 'preview'])->name('products.import.preview');
        Route::post('products/import/execute', [ProductImportController::class, 'execute'])->name('products.import.execute');

        Route::resource('products', AdminProduct::class)->except('show');
    });

    Route::middleware('permission:stock.manage')->group(function () {
        Route::get('stock', [StockController::class, 'index'])->name('stock.index');
        Route::post('stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        Route::get('stock/opname', [StockController::class, 'opname'])->name('stock.opname');
        Route::post('stock/opname', [StockController::class, 'opnameStore'])->name('stock.opname.store');
        Route::get('stock/movements', [StockController::class, 'movements'])->name('stock.movements');
    });
    Route::middleware('permission:promos.manage')->group(function () {
        Route::resource('promos', AdminPromo::class)->except('show');

        Route::get('flashsale', [FlashSaleController::class, 'index'])->name('flashsale.index');
        Route::post('flashsale/settings', [FlashSaleController::class, 'updateSettings'])->name('flashsale.settings');
        Route::patch('flashsale/{product}/toggle', [FlashSaleController::class, 'toggle'])->name('flashsale.toggle');
        Route::patch('flashsale/bulk/update', [FlashSaleController::class, 'bulkUpdate'])->name('flashsale.bulk');
        Route::post('flashsale/clear', [FlashSaleController::class, 'clearAll'])->name('flashsale.clear');
    });

    Route::middleware('permission:categories.manage')->group(function () {
        Route::get('categories', [AdminCategory::class, 'index'])->name('categories.index');
        Route::post('categories', [AdminCategory::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [AdminCategory::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [AdminCategory::class, 'destroy'])->name('categories.destroy');
    });

    Route::middleware('permission:orders.manage')->group(function () {
        Route::get('orders', [AdminOrder::class, 'index'])->name('orders.index');
        Route::get('orders-notifications', [AdminOrder::class, 'notifications'])->name('orders.notifications');
        Route::get('orders/{order}', [AdminOrder::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [AdminOrder::class, 'updateStatus'])->name('orders.status');
        Route::patch('orders/{order}/verify', [AdminOrder::class, 'verifyPayment'])->name('orders.verify');
    });

    Route::middleware('permission:banks.manage')->group(function () {
        Route::get('banks', [AdminBank::class, 'index'])->name('banks.index');
        Route::post('banks', [AdminBank::class, 'store'])->name('banks.store');
        Route::put('banks/{bank}', [AdminBank::class, 'update'])->name('banks.update');
        Route::delete('banks/{bank}', [AdminBank::class, 'destroy'])->name('banks.destroy');
    });

    Route::middleware('permission:messages.manage')->group(function () {
        Route::get('messages', [AdminMessage::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [AdminMessage::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}/read', [AdminMessage::class, 'read'])->name('messages.read');
        Route::delete('messages/{message}', [AdminMessage::class, 'destroy'])->name('messages.destroy');
    });

    // ── Pengaturan ──
    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', AdminUser::class)->except('show');
    });
    Route::middleware('permission:roles.manage')->group(function () {
        Route::resource('roles', AdminRole::class)->except('show');
    });
    Route::middleware('permission:seo.manage')->group(function () {
        Route::get('seo', [AdminSeo::class, 'index'])->name('seo.index');
        Route::get('seo/{pageKey}', [AdminSeo::class, 'edit'])->name('seo.edit');
        Route::put('seo/{pageKey}', [AdminSeo::class, 'update'])->name('seo.update');
    });
    Route::middleware('permission:activity.view')->group(function () {
        Route::get('activity', [AdminActivity::class, 'index'])->name('activity.index');
        Route::get('integration-logs', [AdminIntegrationLog::class, 'index'])->name('integration-logs.index');
        Route::get('integration-logs/{integrationLog}', [AdminIntegrationLog::class, 'show'])->name('integration-logs.show');
    });

    // ── Konten Web (frontend dinamis) ──
    Route::middleware('permission:content.manage')->group(function () {
        Route::get('content', [ContentController::class, 'index'])->name('content.index');
        Route::put('content/{tab}', [ContentController::class, 'update'])->name('content.update');
    });

    // ── Sistem (artisan) — khusus Super Admin ──
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('system', [SystemController::class, 'index'])->name('system.index');
        Route::post('system/run', [SystemController::class, 'run'])->name('system.run');
    });
});

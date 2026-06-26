<?php

use Illuminate\Support\Facades\Route;

// Frontend
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProduct;
use App\Http\Controllers\Admin\CategoryController as AdminCategory;
use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\Admin\PromoController as AdminPromo;
use App\Http\Controllers\Admin\MessageController as AdminMessage;
use App\Http\Controllers\Admin\BankAccountController as AdminBank;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\Admin\RoleController as AdminRole;
use App\Http\Controllers\Admin\SeoController as AdminSeo;
use App\Http\Controllers\Admin\ActivityController as AdminActivity;

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
Route::get('/kontak', [PageController::class, 'contact'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

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
// alias agar finish-redirect Midtrans punya nama order.show
Route::get('/pesanan/{orderNumber}', [PaymentController::class, 'show'])->name('order.show');

// Webhook Midtrans (tanpa CSRF — dikecualikan di bootstrap bila perlu)
Route::post('/midtrans/notify', [PaymentController::class, 'midtransNotify'])->name('midtrans.notify')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

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

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('statistik', [\App\Http\Controllers\Admin\StatisticController::class, 'index'])->name('statistics.index');
    });

    Route::middleware('permission:products.manage')->group(function () {
        // Import produk dari Shopee (CSV) — letakkan SEBELUM resource agar
        // 'products/import' tidak tertangkap route show/{product}.
        Route::get('products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'form'])->name('products.import');
        Route::post('products/import/preview', [\App\Http\Controllers\Admin\ProductImportController::class, 'preview'])->name('products.import.preview');
        Route::post('products/import/execute', [\App\Http\Controllers\Admin\ProductImportController::class, 'execute'])->name('products.import.execute');

        Route::resource('products', AdminProduct::class)->except('show');
    });

    Route::middleware('permission:stock.manage')->group(function () {
        Route::get('stock', [\App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
        Route::post('stock/adjust', [\App\Http\Controllers\Admin\StockController::class, 'adjust'])->name('stock.adjust');
        Route::get('stock/opname', [\App\Http\Controllers\Admin\StockController::class, 'opname'])->name('stock.opname');
        Route::post('stock/opname', [\App\Http\Controllers\Admin\StockController::class, 'opnameStore'])->name('stock.opname.store');
        Route::get('stock/movements', [\App\Http\Controllers\Admin\StockController::class, 'movements'])->name('stock.movements');
    });
    Route::middleware('permission:promos.manage')->group(function () {
        Route::resource('promos', AdminPromo::class)->except('show');

        Route::get('flashsale', [\App\Http\Controllers\Admin\FlashSaleController::class, 'index'])->name('flashsale.index');
        Route::post('flashsale/settings', [\App\Http\Controllers\Admin\FlashSaleController::class, 'updateSettings'])->name('flashsale.settings');
        Route::patch('flashsale/{product}/toggle', [\App\Http\Controllers\Admin\FlashSaleController::class, 'toggle'])->name('flashsale.toggle');
        Route::post('flashsale/clear', [\App\Http\Controllers\Admin\FlashSaleController::class, 'clearAll'])->name('flashsale.clear');
    });

    Route::middleware('permission:categories.manage')->group(function () {
        Route::get('categories', [AdminCategory::class, 'index'])->name('categories.index');
        Route::post('categories', [AdminCategory::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [AdminCategory::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [AdminCategory::class, 'destroy'])->name('categories.destroy');
    });

    Route::middleware('permission:orders.manage')->group(function () {
        Route::get('orders', [AdminOrder::class, 'index'])->name('orders.index');
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
    });

    // ── Konten Web (frontend dinamis) ──
    Route::middleware('permission:content.manage')->group(function () {
        Route::get('content', [\App\Http\Controllers\Admin\ContentController::class, 'index'])->name('content.index');
        Route::put('content/{tab}', [\App\Http\Controllers\Admin\ContentController::class, 'update'])->name('content.update');
    });

    // ── Sistem (artisan) — khusus Super Admin ──
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('system', [\App\Http\Controllers\Admin\SystemController::class, 'index'])->name('system.index');
        Route::post('system/run', [\App\Http\Controllers\Admin\SystemController::class, 'run'])->name('system.run');
    });
});

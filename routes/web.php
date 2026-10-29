<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Product\CustomizedProductController;
use App\Http\Controllers\Customer\CustomizedController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\Kitchen\KitchenController;
use App\Http\Controllers\Admin\ProductSoldController;


Route::get('/', function () {
    return view('welcome');
});


// Staff accounts are created by the Admin (Users page), so public registration is disabled
Route::controller(AuthController::class)->group(function () {
    Route::get('login', 'login')->name('login');
    Route::post('login', 'loginAction')->middleware('throttle:10,1')->name('login.action');

    Route::get('logout', 'logout')->middleware('auth');
});


// Customer kiosk (public)
Route::get('landing-page', [CustomerController::class, 'CustomerLandingPage'])->name('customer.landing');
Route::get('customer/menu', [CustomerController::class, 'showCustomerDashboard'])->name('customer.dashboard');
Route::post('customer/save-order', [CustomerController::class, 'save_order'])->name('save.order.for.customer');
Route::get('customized', [CustomizedController::class, 'showCustomized'])->name('customer.customized');
Route::get('/order/place', [OrderController::class, 'placeOrder'])->name('order.place');
Route::get('/make-your-own-salad', [CustomizedController::class, 'showMakeYourOwnSalad'])->name('customer.make_your_own_salad');
Route::get('show-category', [CustomizedController::class, 'showCategory'])->name('customer.category_products');


// Admin
Route::middleware(['auth', 'check.disabled', 'role:Admin'])->group(function () {
    Route::get('admin/dashboard', function () {
        return view('adminDashboard')->with('layouts', 'admin');
    })->name('adminDashboard');

    Route::get('/admin/profile', [AuthController::class, 'adminProfile'])->name('admin.profile');

    // Products
    Route::controller(ProductController::class)->prefix('products')->group(function () {
        Route::get('', 'index')->name('products');
        Route::get('create', 'create')->name('products.create');
        Route::post('store', 'store')->name('products.store');
        Route::get('show/{id}', 'show')->name('products.show');
        Route::get('edit/{id}', 'edit')->name('products.edit');
        Route::put('edit/{id}', 'update')->name('products.update');
        // Enable / Disable Button
        Route::put('/products/{rs}/disable', 'disableProduct')->name('products.disable');
        Route::put('/products/{rs}/enable', 'enableProduct')->name('products.enable');
        Route::post('/toggle-status', 'toggleStatus')->name('products.toggleStatus');
    });

    // Customized Products
    Route::resource('customized_products', CustomizedProductController::class)->only([
        'index', 'create', 'store', 'show', 'edit', 'update'
    ]);
    Route::put('/customized_products/products/{rs}/disable', [CustomizedProductController::class, 'disableProduct'])->name('customized_products.disable');
    Route::put('/customized_products/products/{rs}/enable', [CustomizedProductController::class, 'enableProduct'])->name('customized_products.enable');
    Route::post('/customized_products/toggle-status', [CustomizedProductController::class, 'toggleStatus'])->name('customized_products.toggleStatus');

    // Users
    Route::controller(AdminController::class)->prefix('users')->group(function () {
        Route::get('', 'index')->name('users');
        Route::post('create', 'create')->name('admin.create');
        Route::post('store', 'store')->name('admin.store');
        Route::get('show/{id}', 'show')->name('admin.show');
        Route::get('edit/{id}', 'edit')->name('admin.edit');
        Route::put('update/{id}', 'update')->name('admin.update');
        Route::put('/admin/{user}/disable', 'disableUser')->name('admin.disable');
        Route::put('/admin/{user}/enable', 'enableUser')->name('admin.enable');
        Route::post('/toggle-status', 'toggleStatus')->name('admin.toggleStatus');
    });

    // Reports
    Route::get('/admin-daily-sales-report', [AdminController::class, 'adminDailySalesReport'])->name('admin.daily_sales_report');
    Route::get('/monthly-product-sold-report', [ProductSoldController::class, 'monthlyProductSoldReport'])->name('admin.product_sold_report');
});


// Cashier
Route::middleware(['auth', 'check.disabled', 'role:Admin,Cashier'])->group(function () {
    Route::get('cashier/dashboard', [CashierController::class, 'cashierDashboard'])->name('cashierDashboard');
    Route::get('/cashier/profile', [AuthController::class, 'cashierProfile'])->name('cashier.profile');
    Route::post('order/payment/{id}', [CashierController::class, 'order_payment'])->name('order.payment');
    Route::get('/customer/view-order/{orderId}', [CashierController::class, 'show'])->name('customer.view_order');
    Route::get('cashier/order-receipt/{orderId}', [OrderController::class, 'showOrderReceipt'])->name('customer.order_receipt');
    Route::get('/cashier-daily-sales-report', [CashierController::class, 'cashierDailySalesReport'])->name('cashier.daily_sales_report');
});


// Kitchen
Route::middleware(['auth', 'check.disabled', 'role:Admin,Kitchen'])->group(function () {
    Route::get('/kitchenDashboard', [KitchenController::class, 'kitchenDashboard'])->name('kitchenDashboard');
    Route::get('kitchen/doneOrder/{id}', [KitchenController::class, 'doneOrder'])->name('done.order');
});

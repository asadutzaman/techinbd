<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
// Pages addressed by slug: route('shop.category', $category) and route('product.detail', $product) take the model.
// The controllers look the slug up themselves, so old addresses (ids, changed slugs) can redirect.
Route::get('/category/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.detail');
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');

// Authentication Routes (signed-in visitors are sent home from these)
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

    // Password reset by email
    Route::get('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'update'])->name('password.update');
});
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');


// Cart Routes
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Checkout Routes
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/success/{id}', [CheckoutController::class, 'success'])->name('order.success');

// Search Routes
Route::get('/search/suggestions', [ShopController::class, 'searchSuggestions'])->name('search.suggestions');

// Customer Dashboard Routes (Protected)
Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\Customer\ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [App\Http\Controllers\Customer\ProfileController::class, 'changePassword'])->name('profile.change-password');
    
    // Address Routes
    Route::resource('addresses', App\Http\Controllers\Customer\AddressController::class);
    Route::post('/addresses/{address}/set-default', [App\Http\Controllers\Customer\AddressController::class, 'setDefault'])->name('addresses.set-default');
    
    // Order Routes
    Route::get('/orders', [App\Http\Controllers\Customer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Customer\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/reorder', [App\Http\Controllers\Customer\OrderController::class, 'reorder'])->name('orders.reorder');
    Route::get('/orders/{order}/download-invoice', [App\Http\Controllers\Customer\OrderController::class, 'downloadInvoice'])->name('orders.download-invoice');
    
    // Wishlist Routes
    Route::get('/wishlist', [App\Http\Controllers\Customer\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add', [App\Http\Controllers\Customer\WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/wishlist/remove', [App\Http\Controllers\Customer\WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::post('/wishlist/toggle', [App\Http\Controllers\Customer\WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/move-to-cart', [App\Http\Controllers\Customer\WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');
});

// Admin Routes (grant access with `php artisan admin:grant {email}`)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // Products Routes (Optimized)
    Route::get('/products', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'index'])->name('products.index');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'show'])->name('products.show');
    Route::get('/products/{id}/edit', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'destroy'])->name('products.destroy');
    
    // AJAX endpoints
    Route::get('/products/categories/{categoryId}/attributes', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'getCategoryAttributes']);
    Route::delete('/products/images/{imageId}', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'deleteImage'])->name('products.images.delete');
    Route::post('/products/images/set-main', [App\Http\Controllers\Admin\ProductOptimizedController::class, 'setMainImage'])->name('products.images.set-main');

    Route::get('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
    
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{id}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::put('/orders/{id}/payment', [App\Http\Controllers\Admin\OrderController::class, 'updatePaymentStatus'])->name('orders.updatePayment');
    Route::post('/orders/{id}/emails', [App\Http\Controllers\Admin\OrderController::class, 'resendEmail'])->name('orders.resendEmail');
    
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/test-email', [App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('settings.test-email');
    
    // Brands
    Route::resource('brands', App\Http\Controllers\Admin\BrandController::class);

    // Home page banner slider
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class)->except('show');
    
    // Attributes (Optimized)
    Route::resource('attributes', App\Http\Controllers\Admin\AttributeOptimizedController::class);
    Route::get('attributes/{id}/values', [App\Http\Controllers\Admin\AttributeOptimizedController::class, 'manageValues'])->name('attributes.values');
    Route::post('attributes/{id}/values', [App\Http\Controllers\Admin\AttributeOptimizedController::class, 'storeValue'])->name('attributes.values.store');
    Route::put('attributes/{attributeId}/values/{valueId}', [App\Http\Controllers\Admin\AttributeOptimizedController::class, 'updateValue'])->name('attributes.values.update');
    Route::delete('attributes/{attributeId}/values/{valueId}', [App\Http\Controllers\Admin\AttributeOptimizedController::class, 'destroyValue'])->name('attributes.values.destroy');
    

});

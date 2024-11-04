<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShippingMethodController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketSubjectController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:otp')->group(function () {
    Route::post('/send-otp', [UserController::class, 'send']);
    Route::post('/verify-otp', [UserController::class, 'verify']);
    Route::prefix('admin')->group(function () {
        Route::post('/send-otp', [AdminController::class, 'send']);
        Route::post('/verify-otp', [AdminController::class, 'verify']);
    });
});

Route::get('search', [ProductController::class, 'search']);
Route::get('related/{productId}', [ProductController::class, 'related']);
Route::get('best-sellers', [ProductController::class, 'bestSellerProducts']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

Route::get('brands', [BrandController::class, 'index']);
Route::get('brands/{id}', [BrandController::class, 'show']);

Route::get('regions', [LocationController::class, 'indexRegions']);
Route::get('regions/{id}', [LocationController::class, 'showRegion']);

Route::get('cities', [LocationController::class, 'indexCities']);
Route::get('cities/{id}', [LocationController::class, 'showCity']);


Route::middleware('userAuth')->group(function () {
    Route::get('users', [UserController::class, 'show']);
    Route::put('users', [UserController::class, 'update']);

    Route::get('user-addresses', [UserAddressController::class, 'index']);
    Route::get('user-addresses/{id}', [UserAddressController::class, 'show']);
    Route::post('user-addresses', [UserAddressController::class, 'store']);
    Route::put('user-addresses/{id}', [UserAddressController::class, 'update']);
    Route::delete('user-addresses/{id}', [UserAddressController::class, 'destroy']);

    Route::get('shipping-methods/{user_address}', [ShippingMethodController::class, 'index']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::get('open-orders', [OrderController::class, 'getOpenOrder']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::put('orders/{id}', [OrderController::class, 'update']);

    Route::get('payment/methods', [OrderController::class, 'getPaymentMethods']);

    Route::get('payment/saman/{token}/{orderId}', [PaymentController::class, 'saman'])->name('payment.saman');
    Route::get('payment/callback/{orderId}', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::post('payment/callback/{orderId}', [PaymentController::class, 'callback'])->name('payment.callback');

    Route::get('payments', [PaymentController::class, 'index']);
    Route::get('payments/{id}', [PaymentController::class, 'show']);

    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites', [FavoriteController::class, 'store']);
    Route::delete('favorites', [FavoriteController::class, 'destroy']);

    Route::get('ticket-subjects', [TicketSubjectController::class, 'index']);

    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{id}', [TicketController::class, 'show']);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::put('tickets/{id}', [TicketController::class, 'userRespond']);

    Route::get('comments', [CommentController::class, 'userIndex']);
    Route::get('comments/{productId}', [CommentController::class, 'index']);
    Route::post('comments', [CommentController::class, 'store']);
    Route::put('comments/{id}', [CommentController::class, 'update']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);
});

Route::middleware('adminAuth:support|admin|super_admin')->prefix('admin')->group(function () {
    Route::get('ticket-subjects', [TicketSubjectController::class, 'index']);

    Route::get('tickets', [TicketController::class, 'adminIndex']);
    Route::get('tickets/{id}', [TicketController::class, 'show']);
    Route::put('tickets/{id}', [TicketController::class, 'adminRespond']);

    Route::get('comments/{productId}', [CommentController::class, 'index']);
    Route::put('comments/{id}', [CommentController::class, 'update']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);
});

Route::middleware('adminAuth:admin|super_admin')->prefix('admin')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/search', [UserController::class, 'search']);
    Route::post('users', [UserController::class, 'createUserByAdmin']);
    Route::get('users/{id}', [UserController::class, 'adminGet']);
    Route::put('users/{id}', [UserController::class, 'adminUpdate']);

    Route::get('users/{id}/user-addresses', [UserAddressController::class, 'adminGet']);
    Route::post('users/{userId}/user-addresses', [UserAddressController::class, 'adminCreateUserAddress']);
    Route::put('users/{userId}/user-addresses/{id}', [UserAddressController::class, 'adminUpdate']);
    Route::delete('user-addresses/{id}', [UserAddressController::class, 'adminDelete']);

    Route::post('categories/upload-image', [CategoryController::class, 'upload']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);

    Route::post('brands', [BrandController::class, 'store']);
    Route::put('brands/{id}', [BrandController::class, 'update']);
    Route::delete('brands/{id}', [BrandController::class, 'destroy']);

    Route::post('products/upload-image', [ProductController::class, 'upload']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    Route::post('regions', [LocationController::class, 'storeRegion']);
    Route::put('regions/{id}', [LocationController::class, 'updateRegion']);
    Route::delete('regions/{id}', [LocationController::class, 'destroyRegion']);

    Route::post('cities', [LocationController::class, 'storeCity']);
    Route::put('cities/{id}', [LocationController::class, 'updateCity']);
    Route::delete('cities/{id}', [LocationController::class, 'destroyCity']);

    Route::post('shipping-methods', [ShippingMethodController::class, 'store']);
    Route::put('shipping-methods/{id}', [ShippingMethodController::class, 'update']);

    Route::post('ticket-subjects', [TicketSubjectController::class, 'store']);
    Route::put('ticket-subjects/{id}', [TicketSubjectController::class, 'update']);
    Route::delete('ticket-subjects/{id}', [TicketSubjectController::class, 'destroy']);
});

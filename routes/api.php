<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\List\ListController;
use App\Http\Controllers\Api\Slider\SliderController;
use App\Http\Controllers\Api\Products\OrderController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Products\ProductLocalController;

Route::middleware('throttle:1200,60')->group(function () {
    // Auth & Registration
    Route::post('register', [AuthController::class, 'register']);
    Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('check-email', [AuthController::class, 'checkEmail']);
    Route::post('verify-otp', [AuthController::class, 'verifyOTP']);
    Route::post('google-login', [AuthController::class, 'googleLoginViaAccessToken']);
    Route::post('personal-data', [AuthController::class, 'personalData']);

    // OTP with Origin validation
    Route::middleware([\App\Http\Middleware\ValidateOrigin::class])->group(function () {
        Route::post('send-otp', [AuthController::class, 'sendOTP']);
    });

    // Password reset
    Route::post('password/forgot', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('password/reset', [ResetPasswordController::class, 'resetPassword']);

    // Public Lists & Info
    Route::get('socials', [ListController::class, 'socials']);
    Route::get('questions', [ListController::class, 'questions']);
    Route::get('get-comments', [ListController::class, 'getComments']);

    // Sliders
    Route::get('sliders', [SliderController::class, 'index']);

    // Product Information
    Route::get('extract-id', [ProductController::class, 'extractId']);
    Route::post('translate', [ProductController::class, 'translate']);
    Route::post('image-search', [ProductController::class, 'imageSearch']);

    // China Product APIs (Open)
    Route::get('products-from-taobao', [ProductController::class, 'productsFromTaobao']);
    Route::get('categories-from-taobao', [ProductController::class, 'categoriesFromTaoBao']);
    Route::get('sub-categories-from-taobao', [ProductController::class, 'subCategoriesFromTaoBao']);
    Route::get('search-products-from-taobao', [ProductController::class, 'searchProductsFromTaobao']);
    Route::get('search-products-from-tmapi', [ProductController::class, 'searchProductsTmapi']);
    Route::get('product-detail-from-taobao', [ProductController::class, 'productDetailFromTaoBao']);
    Route::get('product-detail', [ProductController::class, 'productDetail']);
    Route::get('product-detail-otapi', [ProductController::class, 'productDetailOtapi']);
    Route::get('product-detail-onbound', [ProductController::class, 'productDetailOnbound']);
    Route::get('product-detail-tmapi', [ProductController::class, 'productDetailTmapi']);
    Route::get('product-detail-desc', [ProductController::class, 'productDetailDesc']);
    Route::get('product-info/{productId}', [ProductController::class, 'getChinaProductInfo']);
    Route::get('product-fee', [ProductController::class, 'getItemFee']);

    // Additional Products
    Route::get('additional-categories', [ProductController::class, 'categories']);
    Route::get('get-one-additional-category', [ProductController::class, 'getOneCategory']);
    Route::get('additional-products', [ProductController::class, 'additionalProducts']);

    // Local Products
    Route::apiResource('local-products', ProductLocalController::class);
    Route::post('local-products/skus', [ProductLocalController::class, 'storeSkuProps']);

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);

        // Orders
        Route::post('orders', [OrderController::class, 'create']);
        Route::post('user-basket', [OrderController::class, 'userBasket']);
        Route::get('get-user-basket', [OrderController::class, 'getUserBasket']);
        Route::get('orders', [OrderController::class, 'getUserOrders']);
        Route::get('get-orders', [OrderController::class, 'getOrders']);
        Route::get('get-order-items', [OrderController::class, 'getOrderItems']);
        Route::get('get-order', [OrderController::class, 'getOrder']);
        Route::get('has-items', [OrderController::class, 'hasItems']);
        Route::put('edit-order', [OrderController::class, 'editOrder']);
        Route::get('get-cargo', [OrderController::class, 'getCargo']);
        Route::put('add', [OrderController::class, 'updateBasketItemSizeQuantity']);
        Route::delete('remove', [OrderController::class, 'removeBasketItems']);
        Route::post('add-check', [OrderController::class, 'addCheck']);
        Route::get('success-orders', [OrderController::class, 'getSuccessOrders']);
        Route::get('user-products', [OrderController::class, 'getUserProducts']);
        Route::patch('update-order', [OrderController::class, 'updateOrder']);

        // User & Profile
        Route::get('user-get-data', [UserController::class, 'user']);
        Route::post('location-form', [ProfileController::class, 'locationForm']);

        // Profile updates
        Route::prefix('profile')->group(function () {
            Route::post('update-email', [ProfileController::class, 'updateEmail']);
            Route::post('update-name', [ProfileController::class, 'updateName']);
            Route::post('update-phone', [ProfileController::class, 'updatePhone']);
            Route::post('update-password', [ProfileController::class, 'updatePassword']);
        });

        // Liked Products
        Route::post('liked-product', [ProductController::class, 'addLikedProduct']);
        Route::get('liked-products', [ProductController::class, 'getLikedProducts']);
        Route::get('liked-products-id', [ProductController::class, 'getLikedProductsId']);

        // Token-based product endpoints
        Route::get('product-detail-from-taobao-token', [ProductController::class, 'productDetailFromTaoBaoToken']);
        Route::get('additional-products-token', [ProductController::class, 'additionalProductsToken']);
        Route::get('additional-categories-token', [ProductController::class, 'categoriesToken']);
        Route::get('products-token', [ProductController::class, 'productsToken']);
        Route::get('products-from-taobao-token', [ProductController::class, 'productsFromTaobaoToken']);
        Route::get('search-products-from-taobao-token', [ProductController::class, 'searchProductsFromTaobaoToken']);

        // Comments
        Route::post('post-comment', [ListController::class, 'postComment']);

        // Messages
        Route::get('user/messages', [ListController::class, 'getUserMessages']);
    });
});

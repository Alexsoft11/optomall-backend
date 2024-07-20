<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\List\ListController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Slider\SliderController;
use App\Http\Controllers\Api\Products\OrderController;
use App\Http\Controllers\Api\Products\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/personal-data', [AuthController::class, 'personalData']);

Route::post('login', [AuthController::class, 'login']);
Route::post('check-email', [AuthController::class, 'checkEmail']);
Route::post('/google-login', [AuthController::class, 'googleLoginViaAccessToken']);

Route::post('password/forgot', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('password/reset', [ResetPasswordController::class, 'resetPassword']);
Route::get('socials', [ListController::class, 'socials']);
Route::get('/products-from-taobao', [ProductController::class, 'productsFromTaobao']);
Route::get('/categories-from-taobao', [ProductController::class, 'categoriesFromTaoBao']);
Route::get('/sub-categories-from-taobao', [ProductController::class, 'subCategoriesFromTaoBao']);
Route::get('/search-products-from-taobao', [ProductController::class, 'searchProductsFromTaobao']);
Route::post('/orders', [OrderController::class, 'create']);
Route::get('questions', [ListController::class, 'questions']);
Route::get('/product-detail-from-taobao', [ProductController::class, 'productDetailFromTaoBao']);
Route::get('/get-comments', [ListController::class, 'getComments']);
Route::get('additional-categories', [ProductController::class, 'categories']);
Route::get('additional-products/{id}', [ProductController::class, 'show']);


Route::get('/top-products', [ProductController::class, 'topProducts']);
Route::get('/recommend-products', [ProductController::class, 'recommendedProducts']);
Route::get('/discounted-products', [ProductController::class, 'discountedProducts']);


Route::get('/products', [ProductController::class, 'products']);
Route::get('/get-prices', [ProductController::class, 'getPrices']);
Route::get('/sliders', [SliderController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders', [OrderController::class, 'create']);
    Route::middleware('auth:sanctum')->post('/user-basket', [OrderController::class, 'userBasket']);
    Route::middleware('auth:sanctum')->get('/get-user-basket', [OrderController::class, 'getUserBasket']);
    Route::get('/user-get-data', [UserController::class, 'user']);
    Route::post('/post-comment', [ListController::class, 'postComment']);
    Route::get('/orders', [OrderController::class, 'getUserOrders']);
    Route::get('/get-orders', [OrderController::class, 'getOrders']);
    Route::get('/get-order', [OrderController::class, 'getOrder']);
    Route::get('/get-cargo', [OrderController::class, 'getCargo']);
    Route::put('/add', [OrderController::class, 'updateBasketItemSizeQuantity']);
    Route::delete('/remove', [OrderController::class, 'removeBasketItem']);
    Route::post('/add-check', [OrderController::class, 'addCheck']);
    Route::get('/success-orders', [OrderController::class, 'getSuccessOrders']);
    Route::post('/liked-product', [ProductController::class, 'addLikedProduct']);
    Route::get('/liked-products', [ProductController::class, 'getLikedProducts']);
    Route::get('/product-detail-from-taobao-token', [ProductController::class, 'productDetailFromTaoBaoToken']);
    Route::get('/products-token', [ProductController::class, 'productsToken']);
});


Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::post('/update-email', [ProfileController::class, 'updateEmail']);
    Route::post('/update-name', [ProfileController::class, 'updateName']);
    Route::post('/update-phone', [ProfileController::class, 'updatePhone']);
    Route::post('/update-password', [ProfileController::class, 'updatePassword']);
});

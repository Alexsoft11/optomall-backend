<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\List\ListController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Products\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('password/forgot', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('password/reset', [ResetPasswordController::class, 'resetPassword']);
Route::middleware('auth:api')->post('/profile/update', [ProfileController::class, 'update']);
Route::get('socials', [ListController::class, 'socials']);
Route::get('/products-from-taobao', [ProductController::class, 'productsFromTaobao']);
Route::get('/product-detail-from-taobao', [ProductController::class, 'productDetailFromTaoBao']);
Route::get('/categories-from-taobao', [ProductController::class, 'categoriesFromTaoBao']);
Route::get('/sub-categories-from-taobao', [ProductController::class, 'subCategoriesFromTaoBao']);
Route::get('/search-products-from-taobao', [ProductController::class, 'searchProductsFromTaobao']);

Route::middleware('auth:sanctum')->get('/user-get-data', [UserController::class, 'user']);


<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\List\ListController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('password/forgot', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('password/reset', [ResetPasswordController::class, 'resetPassword']);
Route::middleware('auth:api')->post('/profile/update', [ProfileController::class, 'update']);
Route::get('socials', [ListController::class, 'socials']);

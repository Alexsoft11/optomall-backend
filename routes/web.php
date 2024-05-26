<?php

use App\Http\Resources\ProductResource;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/1688', function () {
    return view('searchByImage');
});

Route::get('/taobao', function () {
    return view('taobao');
});

Route::get('/1688', function () {
    return view('products');
});

Route::get('/socials', function () {
    return view('socials');
});

Route::get('/users', function () {
    return view('users');
});

<?php

use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/1688', function() { 
    return view('searchByImage');
});

Route::get('/taobao', function() {
    return view('taobao');
});

Route::get('/1688', function() { 
    return view('products');
});

Route::get('/socials', function() { 
    return view('socials');
});

Route::get('/products', function() {
    $response = Http::get('https://fakestoreapi.com/products');
    $products = $response->json();
    return ProductResource::collection($products);
});

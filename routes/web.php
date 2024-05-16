<?php

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
<?php

use App\Http\Resources\ProductResource;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/socials', function () {
    return view('socials');
});

Route::get('/users', function () {
    return view('users');
});

Route::get('/questions', function() {
    return view('questions');
});

Route::get('/answers', function() {
    return view('answers');
});
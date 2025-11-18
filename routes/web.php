<?php

use App\Http\Resources\ProductResource;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

Route::get('/test-email', function () {
    try {
        Mail::raw('Test email body', function ($message) {
            $message->to('kingdev5809@gmail.com')->subject('Test Email');
        });
        Log::info('Email sent successfully.');
        return 'Email sent successfully. Check logs for details.';
    } catch (\Exception $e) {
        Log::error('Email sending failed: ' . $e->getMessage());
        return 'Email sending failed: ' . $e->getMessage();
    }
});


Route::get('/image-proxy', function (\Illuminate\Http\Request $request) {
    $url = $request->query('url');
    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        return response('Invalid URL', 400);
    }

    try {
        $response = Http::withOptions([
            'verify' => false, // very important for China SSL
        ])
        ->withHeaders([
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => '*/*',
            'Referer' => $request->query('referer') ?? null,
        ])
        ->timeout(20)
        ->get($url);

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type') ?? 'image/jpeg');
    } catch (\Exception $e) {
        return response('Failed to fetch image: '.$e->getMessage(), 500);
    }
});

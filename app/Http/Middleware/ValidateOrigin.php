<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateOrigin
{
    /**
     * Allowed request origins.
     */
    protected $allowedOrigins = [
        'https://optomall.uz',
        'https://taobao-optom-uz.vercel.app',
        'https://optomol.uz',
        'http://localhost:3001'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the request is POST
        if ($request->isMethod('post')) {
            $origin = $request->headers->get('origin'); // Get the request origin
            // Validate the origin
            if (!in_array($origin, $this->allowedOrigins)) {
                Log::info('Unauthorized Origin:' . $request->header('Origin'));
                return response()->json(['error' => 'Unauthorized request origin.'], 403);
            }
        }

        return $next($request);
    }
}

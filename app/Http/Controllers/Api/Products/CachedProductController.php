<?php

namespace App\Http\Controllers\Api\Products;

use App\Models\CachedProduct;
use App\Services\ProductCacheService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseHelpers;

class CachedProductController extends Controller
{
    use ApiResponseHelpers;

    protected ProductCacheService $cacheService;

    public function __construct(ProductCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get cached product - Returns ONLY from local database (fast)
     * Frontend calls this first to show data immediately
     */
    public function getCachedProduct(Request $request)
    {
        $productId = str_replace('abb-', '', $request->input('productId', ''));
        $provider = $request->input('provider', '1688');
        $lang = $request->input('lang', 'en');

        if (empty($productId)) {
            return response()->json(['error' => 'product_id_required'], 400);
        }

        $cachedProduct = $this->cacheService->getCachedProduct($productId, $provider, $lang);

        if (!$cachedProduct) {
            return response()->json([
                'success' => false,
                'exists' => false,
                'needs_fetch' => true,
            ]);
        }

        $cachedProduct->increment('views');

        $isExpired = $cachedProduct->isExpired();

        return $this->respondWithSuccess([
            'exists' => true,
            'detail' => $cachedProduct->toDetailArray(),
            'score' => [
                'rating' => $cachedProduct->rating ?? 4.6,
                'sold' => $cachedProduct->sales ?? 1000,
            ],
            'is_expired' => $isExpired,
            'needs_sync' => $isExpired,
            'is_available' => $cachedProduct->is_available,
            'synced_at' => $cachedProduct->synced_at?->toISOString(),
        ]);
    }

    /**
     * Sync product - Fetches fresh data from TMAPI and updates cache
     * Frontend calls this in background if cache is expired
     */
    public function syncProduct(Request $request)
    {
        $productId = str_replace('abb-', '', $request->input('productId', ''));
        $provider = $request->input('provider', '1688');
        $lang = $request->input('lang', 'en');

        if (empty($productId)) {
            return response()->json(['error' => 'product_id_required'], 400);
        }

        // Call the main product detail endpoint to fetch and cache
        $productController = app(\App\Http\Controllers\Api\Products\ProductController::class);
        
        $fakeRequest = new Request([
            'productId' => $productId,
            'provider' => $provider,
            'lang' => $lang,
            'force_refresh' => true, // Flag to force TMAPI call
        ]);

        return $productController->productDetailTmapi($fakeRequest);
    }

    /**
     * Check product availability - For basket operations
     * Returns real-time availability status
     */
    public function checkAvailability(Request $request)
    {
        $productId = str_replace('abb-', '', $request->input('productId', ''));
        $provider = $request->input('provider', '1688');
        $lang = $request->input('lang', 'en');

        if (empty($productId)) {
            return response()->json(['error' => 'product_id_required'], 400);
        }

        $cachedProduct = $this->cacheService->getCachedProduct($productId, $provider, $lang);

        // If cache is fresh and available, trust it
        if ($cachedProduct && !$cachedProduct->isExpired() && $cachedProduct->is_available) {
            return $this->respondWithSuccess([
                'is_available' => true,
                'can_add_to_basket' => true,
                'detail' => $cachedProduct->toDetailArray(),
                'source' => 'cache',
            ]);
        }

        // If expired or unavailable, do a real-time check
        $productController = app(\App\Http\Controllers\Api\Products\ProductController::class);
        
        $fakeRequest = new Request([
            'productId' => $productId,
            'provider' => $provider,
            'lang' => $lang,
            'force_refresh' => true,
        ]);

        $response = $productController->productDetailTmapi($fakeRequest);
        $data = json_decode($response->getContent(), true);

        if (isset($data['success']) && $data['success']) {
            return $this->respondWithSuccess([
                'is_available' => true,
                'can_add_to_basket' => true,
                'detail' => $data['detail'],
                'source' => 'api',
            ]);
        }

        // Product not available
        return $this->respondWithSuccess([
            'is_available' => false,
            'can_add_to_basket' => false,
            'detail' => $cachedProduct ? $cachedProduct->toDetailArray() : null,
            'source' => 'unavailable',
        ]);
    }
}

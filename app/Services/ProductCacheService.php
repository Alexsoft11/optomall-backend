<?php

namespace App\Services;

use App\Models\CachedProduct;
use Illuminate\Support\Facades\Log;

class ProductCacheService
{
    public function getCachedProduct(string $externalId, string $provider, string $language): ?CachedProduct
    {
        $externalId = $this->cleanProductId($externalId);

        return CachedProduct::findByExternalId($externalId, $provider, $language);
    }

    public function isCacheExpired(string $externalId, string $provider, string $language): bool
    {
        $cached = $this->getCachedProduct($externalId, $provider, $language);

        if (!$cached) {
            return true;
        }

        return $cached->isExpired();
    }

    public function saveFromTmapiResponse(
        string $externalId,
        string $provider,
        string $language,
        array $rawApiData,
        array $transformedDetail,
        ?array $score = null
    ): ?CachedProduct {
        try {
            $externalId = $this->cleanProductId($externalId);

            $images = [];
            if (isset($rawApiData['main_imgs']) && is_array($rawApiData['main_imgs'])) {
                $images = array_map(function ($img) {
                    return preg_replace('/^\/\//', 'https://', $img);
                }, $rawApiData['main_imgs']);
            }

            $skus = [];
            if (isset($rawApiData['skus']) && is_array($rawApiData['skus'])) {
                foreach ($rawApiData['skus'] as $sku) {
                    $skus[] = [
                        'sku_id' => $sku['skuid'] ?? '',
                        'price' => $sku['sale_price'] ?? 0,
                        'original_price' => $sku['orginal_price'] ?? $sku['sale_price'] ?? 0,
                        'stock' => $sku['stock'] ?? 0,
                        'props_ids' => $sku['props_ids'] ?? '',
                        'props_names' => $sku['props_names'] ?? '',
                    ];
                }
            }

            $skuProps = [];
            if (isset($rawApiData['sku_props']) && is_array($rawApiData['sku_props'])) {
                $skuProps = $rawApiData['sku_props'];
            }

            $tierPrices = [];
            if (isset($rawApiData['tiered_price_info']['prices']) && is_array($rawApiData['tiered_price_info']['prices'])) {
                $tierPrices = $rawApiData['tiered_price_info']['prices'];
            }

            $mainImage = '';
            if (isset($rawApiData['main_imgs'][0])) {
                $mainImage = preg_replace('/^\/\//', 'https://', $rawApiData['main_imgs'][0]);
            }

            return CachedProduct::updateOrCreate(
                [
                    'external_id' => $externalId,
                    'provider' => $provider,
                    'language' => $language,
                ],
                [
                    'title' => $transformedDetail['title'] ?? $rawApiData['title'] ?? '',
                    'description' => $rawApiData['desc'] ?? '',
                    'price' => $rawApiData['price_info']['price'] ?? 0,
                    'image' => $mainImage,
                    'images' => $images,
                    'skus' => $skus,
                    'sku_props' => $skuProps,
                    'props' => $transformedDetail['composition'] ?? $rawApiData['product_props'] ?? [],
                    'tier_prices' => $tierPrices,
                    'seller_info' => $rawApiData['shop_info'] ?? [],
                    'sales' => $score['sold'] ?? $rawApiData['sale_count'] ?? 0,
                    'rating' => $score['rating'] ?? $rawApiData['shop_info']['item_score'] ?? 4.5,
                    'raw_data' => $rawApiData,
                    'is_available' => true,
                    'synced_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to save product to cache', [
                'external_id' => $externalId,
                'provider' => $provider,
                'language' => $language,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function markAsUnavailable(string $externalId, string $provider): void
    {
        $externalId = $this->cleanProductId($externalId);

        CachedProduct::where('external_id', $externalId)
            ->where('provider', $provider)
            ->update(['is_available' => false]);
    }

    public function isProductCached(string $externalId, string $provider, string $language): bool
    {
        return $this->getCachedProduct($externalId, $provider, $language) !== null;
    }

    private function cleanProductId(string $productId): string
    {
        return str_replace('abb-', '', $productId);
    }
}

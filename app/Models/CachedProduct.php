<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CachedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'provider',
        'language',
        'title',
        'description',
        'price',
        'image',
        'images',
        'skus',
        'sku_props',
        'props',
        'tier_prices',
        'seller_info',
        'raw_data',
        'sales',
        'rating',
        'is_available',
        'synced_at',
        'views',
    ];

    protected $casts = [
        'images' => 'array',
        'skus' => 'array',
        'sku_props' => 'array',
        'props' => 'array',
        'tier_prices' => 'array',
        'seller_info' => 'array',
        'raw_data' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_available' => 'boolean',
        'synced_at' => 'datetime',
        'views' => 'integer',
    ];

    const CACHE_TTL_HOURS = 6;

    public static function findByExternalId(string $externalId, string $provider, string $language): ?self
    {
        return self::where('external_id', $externalId)
            ->where('provider', $provider)
            ->where('language', $language)
            ->first();
    }

    public function isExpired(): bool
    {
        if (!$this->synced_at) {
            return true;
        }

        return $this->synced_at->diffInHours(now()) >= self::CACHE_TTL_HOURS;
    }

    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    public function isFresh(): bool
    {
        return $this->synced_at && $this->synced_at->diffInMinutes(now()) < 5;
    }

    public function markAsSynced(): void
    {
        $this->update(['synced_at' => now()]);
    }

    public function markAsUnavailable(): void
    {
        $this->update(['is_available' => false]);
    }

    public function markAsAvailable(): void
    {
        $this->update(['is_available' => true]);
    }

    public function scopeExpired($query)
    {
        return $query->where('synced_at', '<', now()->subHours(self::CACHE_TTL_HOURS));
    }

    public function scopeValid($query)
    {
        return $query->where('synced_at', '>=', now()->subHours(self::CACHE_TTL_HOURS));
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function getCacheStatus(): string
    {
        if (!$this->synced_at) {
            return 'never_synced';
        }

        if ($this->isValid()) {
            return 'fresh';
        }

        return 'expired';
    }

    public function getHoursUntilExpiry(): float
    {
        if (!$this->synced_at) {
            return 0;
        }

        $hoursElapsed = $this->synced_at->diffInHours(now());
        return max(0, self::CACHE_TTL_HOURS - $hoursElapsed);
    }

    public function toDetailArray(): array
    {
        $amount = \App\Models\Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;
        $percent = \App\Models\Exchanges::where('currency', 'product_percent')->first()->amount ?? 0;

        $priceInSum = round($this->addPercentToPrice($this->price, $amount, $percent));

        $pictures = [];
        if (is_array($this->images)) {
            foreach ($this->images as $img) {
                $pictures[] = [
                    'image_url' => $img,
                    'is_main' => 0,
                ];
            }
        }

        $skus = [];
        if (is_array($this->skus)) {
            foreach ($this->skus as $sku) {
                $skuPrice = $sku['price'] ?? $this->price;
                $skus[] = [
                    'price' => round($this->addPercentToPrice($skuPrice, $amount, $percent)),
                    'total_price' => round($this->addPercentToPrice($skuPrice, $amount, $percent)),
                    'orginal_price' => round($this->addPercentToPrice($sku['original_price'] ?? $skuPrice, $amount, $percent)),
                    'properties' => $sku['props_ids'] ?? '',
                    'properties_name' => $sku['props_names'] ?? '',
                    'quantity' => $sku['stock'] ?? 0,
                    'sku_id' => $sku['sku_id'] ?? '',
                ];
            }
        }

        $quantityRanges = [];
        if (is_array($this->tier_prices)) {
            foreach ($this->tier_prices as $tier) {
                $tierPrice = $tier['price'] ?? $this->price;
                $quantityRanges[] = [
                    'minQuantity' => $tier['beginAmount'] ?? 1,
                    'price' => round($this->addPercentToPrice($tierPrice, $amount, $percent)),
                ];
            }
            usort($quantityRanges, fn($a, $b) => $a['minQuantity'] <=> $b['minQuantity']);
        }

        return [
            'id' => $this->external_id,
            'title' => $this->title,
            'price' => $priceInSum,
            'discounted_price' => 0,
            'discount_percentage' => 0,
            'quantityRanges' => $quantityRanges,
            'image' => $this->image,
            'quantity' => $this->sales ?? 0,
            'seller_info' => $this->seller_info ?? [],
            'delivery_info' => $this->raw_data['delivery_info'] ?? [],
            'provider' => $this->provider,
            'description' => str_replace(["\n", "\\"], '', $this->description ?? ''),
            'pictures' => $pictures,
            'location' => $this->raw_data['location'] ?? 'China',
            'product_url' => $this->raw_data['product_url'] ?? '',
            'videos' => $this->raw_data['video_url'] ?? null,
            'sku_price_range' => $this->raw_data['sku_price_range'] ?? [],
            'skus' => $skus,
            'prop_imgs' => [],
            'props_list' => $this->sku_props ?? [],
            'props' => $this->props ?? [],
            'composition' => $this->props ?? [],
            'is_liked' => false,
        ];
    }

    private function addPercentToPrice($priceInYuan, $amount, $percent = 0)
    {
        $priceInSum = floatval($priceInYuan) * floatval($amount);
        $additionalAmount = $priceInSum * ($percent / 100);
        return $priceInSum + $additionalAmount;
    }
}

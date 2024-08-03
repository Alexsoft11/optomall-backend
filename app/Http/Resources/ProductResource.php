<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $amount = Exchanges::where('currency', 'usd')->first()->amount ?? 12000;

        $originalPrice = $this->resource['orginal_price'] ?? null;
        $price = $this->resource['price'] * $amount;
        $promotionPrice = array_key_exists('promotion_price', $this->resource) ? $this->resource['promotion_price'] : null;

        return [
            'id' => $this->resource['num_iid'],
            'title' => $this->resource['title'],
            'price' => isset($originalPrice) ? round($originalPrice * $amount) : round($price),
            'discounted_price' => round(isset($promotionPrice) ? $promotionPrice * $amount : $price),
            'discount_percent' => isset($originalPrice)
                ? round((($originalPrice - ($promotionPrice ?? $this->resource['price'])) / $originalPrice) * 100)
                : round((($this->resource['price'] - ($promotionPrice ?? $this->resource['price'])) / $this->resource['price']) * 100),
            'image' => $this->resource['pic_url'],
            'is_liked' => false,
        ];
    }
}

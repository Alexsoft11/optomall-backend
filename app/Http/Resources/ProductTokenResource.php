<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTokenResource extends JsonResource
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
        $isLiked = auth()->check() && auth()->user()->likedProducts()->where('product_id', $this['Id'] ?? $productId)->exists();
        $promotionPriceExists = isset($this->resource['PromotionPrice']);
        $promotionPercentExists = isset($this->resource['PromotionPricePercent']);
        // return $promotionPercentExists;
        // $this['Price']['ConvertedPriceList']['Internal']['Price'] * $amount ??
        return [
            'id' => $this->resource['Id'] ?? $this['id'],
            'title' => $this->resource['Title'] ?? ['uz' => $this->title_uz, 'ru' => $this->title_ru, 'en' => $this->title_en],
            'price' =>  $this->price,
            'image' => strpos($this->resource['image'], 'https') === 0 ? $this->resource['image'] : 'https://backend.cjsaam.site/storage/' . ($this->resource['image'] ?? $this['image']),
            'discounted_price' => $promotionPriceExists ? round($this->resource['PromotionPrice']['ConvertedPriceList']['Internal']['Price']) * $amount : null,
            'discount_percent' => $promotionPercentExists ? $this->resource['PromotionPricePercent'][0]['Percent'] : null,
            'is_liked' => $isLiked,
        ];
    }
}

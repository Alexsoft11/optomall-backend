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

        $promotionPriceExists = isset($this->resource['PromotionPrice']);
        $promotionPercentExists = isset($this->resource['PromotionPricePercent']);
        // return $promotionPercentExists;

        return [
            'id' => $this->resource['Id'] ?? $this['id'],
            'title' => $this->resource['Title'] ?? $this['title'],
            'price' => $this['price'],
            'image' => $this->resource['MainPictureUrl'] ?? $this['image'],
            'discounted_price' => $promotionPriceExists ? round($this->resource['PromotionPrice']['ConvertedPriceList']['Internal']['Price']) * $amount : null ?? $this['discounted_price'],
            'discount_percent' => $promotionPercentExists ? $this->resource['PromotionPricePercent'][0]['Percent'] : null ?? $this['discount_percent'],
        ];
    }
}
